<?php
namespace Diddipoeler\Module\SportsManagementMatches\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

require_once dirname(__DIR__, 2) . '/connectors/native/QueryTrait.php';
require_once dirname(__DIR__, 2) . '/connectors/native/FormatTrait.php';
require_once dirname(__DIR__, 2) . '/connectors/native/LinkTrait.php';

final class MatchesHelper
{
    use NativeQueryTrait;
    use NativeFormatTrait;
    use NativeLinkTrait;

    /** @return array{matches:array<int,array<string,mixed>>,legacy_update_requested:bool} */
    public function getData(Registry $params, CMSApplicationInterface $app, object $module): array
    {
        $projects = $this->ids($params->get('p', []));
        if (!$projects) {
            return ['matches' => [], 'legacy_update_requested' => (bool) $params->get('ishd_update', 0)];
        }

        try {
            /** @var DatabaseInterface $joomlaDatabase */
            $joomlaDatabase = Factory::getContainer()->get(DatabaseInterface::class);
            $db = $this->database($params, $joomlaDatabase);
            $matches = $this->loadMatches($db, $params, $projects);
            $showReferees = (int) $params->get('show_referee', 1) === 1;
            $refereesByMatch = $showReferees
                ? $this->refereesByMatch(
                    $db,
                    array_map(static fn (object $match): int => (int) ($match->match_id ?? 0), $matches),
                    (int) $params->get('referee_name_format', 0)
                )
                : [];
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
                    'date' => HTMLHelper::_('date', $match->match_date, (string) $params->get('dateformat', 'DATE_FORMAT_LC4'), null),
                    'time' => HTMLHelper::_('date', $match->match_date, (string) $params->get('timeformat', 'H:i'), null),
                    'home' => $this->team($match, true, $params),
                    'away' => $this->team($match, false, $params),
                    'cancel' => (bool) $match->cancel,
                    'notice' => (int) $params->get('show_match_notice', 1) === 1 ? (string) ($match->match_result_detail ?? '') : '',
                    'venue' => $this->venue($match, $params),
                    'referees' => $showReferees ? ($refereesByMatch[$matchId] ?? []) : [],
                    'spectators' => (int) $params->get('show_spectators', 0) === 1 ? (int) ($match->crowd ?? 0) : 0,
                    'links' => $this->matchLinks($match, $params),
                    'navigation' => (int) $params->get('next_last', 0) > 0
                        ? $this->neighborLinks($db, $match, $params, $projects)
                        : [],
                ];
                $this->result($row, $match, $params);
                $rows[] = $row;
            }

            if ((int) $params->get('upcoming_first', 1) === 1) {
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
            $app->enqueueMessage($e->getMessage(), 'error');
            return ['matches' => [], 'legacy_update_requested' => (bool) $params->get('ishd_update', 0)];
        }
    }
}
