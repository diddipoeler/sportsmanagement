<?php
/**
 * Joomla 5/6 SportsManagement matches module PHP implementation.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementMatches\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

$nativeTraits = [
    NativeQueryTrait::class => dirname(__DIR__, 2) . '/connectors/native/QueryTrait.php',
    NativeFormatTrait::class => dirname(__DIR__, 2) . '/connectors/native/FormatTrait.php',
    NativeLinkTrait::class => dirname(__DIR__, 2) . '/connectors/native/LinkTrait.php',
];

foreach ($nativeTraits as $trait => $file) {
    if (!trait_exists($trait, false) && is_file($file)) {
        require_once $file;
    }
}

foreach (array_keys($nativeTraits) as $requiredTrait) {
    if (!trait_exists($requiredTrait)) {
        throw new \RuntimeException(
            'SportsManagement Matches trait could not be loaded: ' . $requiredTrait,
            500
        );
    }
}

final class MatchesHelper
{
    use NativeQueryTrait;
    use NativeFormatTrait;
    use NativeLinkTrait;

    /**
     * Resolve the primary Joomla database for the matches module.
     * Uses an injected connection or obtains DatabaseInterface from
     * Joomla's container, failing explicitly when unavailable.
     * @param DatabaseInterface|null $database Optional injected connection.
     * @return DatabaseInterface Current Joomla database.
     * @throws \RuntimeException When no database service is available.
     */
    private static function joomlaDatabase(?DatabaseInterface $database = null): DatabaseInterface
    {
        if ($database !== null) {
            return $database;
        }

        $database = Factory::getContainer()->get(DatabaseInterface::class);

        if (!$database instanceof DatabaseInterface) {
            throw new \RuntimeException('SportsManagement Matches database service is unavailable.', 500);
        }

        return $database;
    }

    /**
     * Load and format upcoming, live and completed fixtures for the module.
     *
     * Validates the Joomla site client and resolves the selected database,
     * retrieves matches for configured projects, prepares team names,
     * results, venues, optional referees and navigation links, and sorts
     * rows by configured live/upcoming priority. Logs unexpected database
     * errors rather than exposing connection details to site visitors.
     * Rejects non-site applications before entering the recoverable query
     * error handler so administrator execution is never silently accepted.
     * Validates legacy display options and avoids logging sensitive SQL text.
     *
     * @param Registry $params Joomla Matches module configuration.
     * @param CMSApplicationInterface $app Current Joomla application.
     * @param object $module Joomla module record for template compatibility.
     * @param DatabaseInterface|null $joomlaDatabase Optional database override.
     * @return array{matches:array<int,array<string,mixed>>,legacy_update_requested:bool} Prepared fixture rows and update flag.
     */
    public function getData(
        Registry $params,
        CMSApplicationInterface $app,
        object $module,
        ?DatabaseInterface $joomlaDatabase = null
    ): array {
        // Reject administrator execution before accessing project selectors
        // or returning an empty module result; the module is site-only.
        if (!$app->isClient('site')) {
            throw new \RuntimeException('SportsManagement Matches requires the Joomla site application.', 500);
        }

        $projects = $this->ids($params->get('p', []));
        if (!$projects) {
            return ['matches' => [], 'legacy_update_requested' => (bool) $params->get('ishd_update', 0)];
        }

        try {
            $joomlaDatabase = self::joomlaDatabase($joomlaDatabase);
            $db = $this->database($params, $joomlaDatabase);
            $matches = $this->loadMatches($db, $params, $projects);
            $showReferees = self::boundedMatchesOption($params->get('show_referee', 1), 1, 1) === 1;
            $refereesByMatch = $showReferees
                ? $this->refereesByMatch(
                    $db,
                    array_map(static fn (object $match): int => (int) ($match->match_id ?? 0), $matches),
                    self::boundedMatchesOption($params->get('referee_name_format', 0), 0, 20)
                )
                : [];
            $rawDateFormat = $params->get('dateformat', 'DATE_FORMAT_LC4');
            $dateFormat = is_string($rawDateFormat) && strlen($rawDateFormat) <= 100
                ? $rawDateFormat : 'DATE_FORMAT_LC4';
            $rawTimeFormat = $params->get('timeformat', 'H:i');
            $timeFormat = is_string($rawTimeFormat) && strlen($rawTimeFormat) <= 100
                ? $rawTimeFormat : 'H:i';
            $showNotice = self::boundedMatchesOption($params->get('show_match_notice', 1), 1, 1) === 1;
            $showSpectators = self::boundedMatchesOption($params->get('show_spectators', 0), 0, 1) === 1;
            $showNavigation = self::boundedMatchesOption($params->get('next_last', 0), 0, 1) > 0;
            $rows = [];

            foreach ($matches as $match) {
                $this->applyStatus($match);
                $matchId = (int) $match->match_id;
                $row = [
                    'id' => $matchId,
                    'project_id' => (int) $match->project_id,
                    'round_id' => (int) $match->round_id,
                    'type' => $this->statusType($match, $params),
                    'status_heading' => $this->statusHeading($match, $params),
                    'heading' => $this->heading($match, $params),
                    'date' => HTMLHelper::_('date', $match->match_date, $dateFormat, null),
                    'time' => HTMLHelper::_('date', $match->match_date, $timeFormat, null),
                    'home' => $this->team($match, true, $params),
                    'away' => $this->team($match, false, $params),
                    'cancel' => (bool) $match->cancel,
                    'notice' => $showNotice && is_string($match->match_result_detail ?? null)
                        ? $match->match_result_detail : '',
                    'venue' => $this->venue($match, $params),
                    'referees' => $showReferees ? ($refereesByMatch[$matchId] ?? []) : [],
                    'spectators' => $showSpectators && is_numeric($match->crowd ?? null) ? max(0, (int) $match->crowd) : 0,
                    'links' => $this->matchLinks($match, $params),
                    'navigation' => $showNavigation
                        ? $this->neighborLinks($db, $match, $params, $projects)
                        : [],
                ];
                $this->result($row, $match, $params);
                $rows[] = $row;
            }

            if (self::boundedMatchesOption($params->get('upcoming_first', 1), 1, 1) === 1) {
                usort($rows, static function (array $a, array $b): int {
                    $weight = static fn (string $type): int => match ($type) {
                        'live', 'actplaying' => 0,
                        'upcoming' => 1,
                        'alreadyplayed' => 2,
                        default => 3,
                    };

                    return $weight((string) $a['type']) <=> $weight((string) $b['type']);
                });
            }

            return ['matches' => $rows, 'legacy_update_requested' => (bool) $params->get('ishd_update', 0)];
        } catch (\Throwable $e) {
            // Database errors must be logged server-side, not exposed to site visitors.
            Log::add(__METHOD__ . ' failed (' . get_class($e) . ').', Log::ERROR, 'jsmerror');
            $app->enqueueMessage('SportsManagement matches could not be loaded.', 'error');
            return ['matches' => [], 'legacy_update_requested' => (bool) $params->get('ishd_update', 0)];
        }
    }
}
