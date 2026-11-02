<?php
/**
 * Joomla 5/6 helper for the SportsManagement clubicons module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementClubicons\Site\Helper;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Diddipoeler\Component\SportsManagement\Site\Service\RankingEngine;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

final class ClubiconsHelper
{
    /**
     * Obtain the typed Joomla database service for club information queries.
     *
     * Resolves the dependency from Joomla's service container and rejects invalid services.
     *
     * @return DatabaseInterface Active Joomla database connection.
     * @throws \RuntimeException If the database service is unavailable.
     */
    private static function joomlaDatabase(): DatabaseInterface
    {
        $database = Factory::getContainer()->get(DatabaseInterface::class);

        if (!$database instanceof DatabaseInterface) {
            throw new \RuntimeException('SportsManagement Clubicons database service is unavailable.', 500);
        }

        return $database;
    }

    private const PLACEHOLDERS = [
        'logo_big' => 'images/com_sportsmanagement/database/placeholders/placeholder_150.png',
        'projectteam_picture' => 'images/com_sportsmanagement/database/placeholders/placeholder_450_2.png',
        'team_picture' => 'images/com_sportsmanagement/database/placeholders/placeholder_450_2.png',
    ];

    /**
     * Build the club icon, team link and ranking data for the module layout.
     *
     * Selects the configured project and division, calculates the ranking, resolves team links and image URLs, and returns empty collections when there is no suitable project.
     *
     * @param Registry $params Module configuration.
     * @param object $module Joomla module record.
     * @param CMSApplicationInterface $app Active site application.
     * @return array<string, mixed> Project, ranking and teams for the template.
     */
    public function getData(Registry $params, object $module, CMSApplicationInterface $app): array
    {
        if (!$app->isClient('site')) {
            throw new \RuntimeException('SportsManagement Clubicons requires the Joomla site application.', 500);
        }

        $projectId = $this->projectId($params, $app);
        if ($projectId <= 0) {
            return ['project' => null, 'ranking' => [], 'teams' => []];
        }

        $db = $this->database($params, self::joomlaDatabase());
        $divisionId = $this->firstId($params->get('division_id', 0));
        $result = (new RankingEngine($db))->calculate($projectId, $divisionId);
        $project = $result['project'];
        $ranking = $result['ranking'];

        if (empty($project->id) || !$ranking) {
            return ['project' => $project ?: null, 'ranking' => [], 'teams' => []];
        }

        // Fetch club websites only for the configured external-link mode.
        $websites = (int) $params->get('teamlink', 0) === 5
            ? $this->websiteMap($db, $ranking)
            : [];
        $teams = [];
        foreach ($ranking as $row) {
            $team = $row->team;
            if (!$team || $row->projectteamid <= 0) {
                continue;
            }

            $teams[$row->projectteamid] = [
                'name' => (string) ($team->name ?? ''),
                'link' => $this->teamLink($params, $project, $team, $websites),
                'logo_url' => $this->logoUrl($params, $team),
            ];
        }

        return [
            'project' => $project,
            'ranking' => $ranking,
            'teams' => $teams,
        ];
    }

    /**
     * Determine the project used to render club icons.
     *
     * Prefers the current component project when configured and valid; otherwise selects the first configured project identifier.
     *
     * @param Registry $params Project selection settings.
     * @param CMSApplicationInterface $app Application request source.
     * @return int Positive project ID, or zero for no valid project.
     */
    private function projectId(Registry $params, CMSApplicationInterface $app): int
    {
        $input = $app->getInput();
        if (
            (int) $params->get('usepfromcomponent', 0) === 1
            && $input->getCmd('option', '') === 'com_sportsmanagement'
            && $input->getInt('p', 0) > 0
        ) {
            return $input->getInt('p', 0);
        }

        return $this->firstId($params->get('project_ids', 0));
    }

    /**
     * Extract the first valid positive identifier from a module selector.
     *
     * Accepts an ID or Joomla ID:alias slug, and rejects malformed or overflowing values instead of relying on partial integer conversion.
     *
     * @param mixed $value Scalar or list of configured identifiers.
     * @return int Valid positive ID, or zero for invalid input.
     */
    private function firstId(mixed $value): int
    {
        if (is_array($value)) {
            $value = reset($value);
        }

        if (!is_int($value) && !is_string($value)) {
            return 0;
        }

        $value = trim((string) $value);

        // Accept a Joomla ID or an ID:alias slug, not an arbitrary prefix
        // such as "12evil" or an overflowing decimal number.
        if (!preg_match('/^([1-9][0-9]*)(?::[^\s:]+)?$/D', $value, $matches)) {
            return 0;
        }

        $id = filter_var($matches[1], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        return $id === false ? 0 : $id;
    }

    /**
     * Construct the configured destination URL for a club icon.
     *
     * Routes internal team and club views through the component router, or uses a validated external club URL for the website-link mode.
     *
     * @param Registry $params Link mode and database settings.
     * @param object $project Current project record.
     * @param object $team Team record.
     * @param array<int, string> $websites Validated external websites.
     * @return string Route URL, or an empty string when disabled.
     */
    private function teamLink(Registry $params, object $project, object $team, array $websites): string
    {
        $teamLink = (int) $params->get('teamlink', 0);
        if ($teamLink === 0) {
            return '';
        }

        if ($teamLink === 5) {
            $teamId = (int) ($team->id ?? 0);
            return $this->externalWebsiteUrl((string) ($websites[$teamId] ?? ''));
        }

        $common = [
            'cfg_which_database' => (int) $params->get('cfg_which_database', 0),
            's' => (int) $params->get('s', 0),
            'p' => (string) ($project->slug ?? $project->id ?? ''),
        ];

        return match ($teamLink) {
            1 => SiteRouteHelper::view('teaminfo', $common + [
                'tid' => (string) ($team->team_slug ?? $team->id ?? ''),
                'ptid' => 0,
            ]),
            2 => SiteRouteHelper::view('roster', $common + [
                'tid' => (string) ($team->team_slug ?? $team->id ?? ''),
                'ptid' => 0,
            ]),
            3 => SiteRouteHelper::view('teamplan', $common + [
                'tid' => (string) ($team->team_slug ?? $team->id ?? ''),
                'division' => 0,
                'mode' => 0,
                'ptid' => 0,
            ]),
            4 => SiteRouteHelper::view('clubinfo', $common + [
                'cid' => (string) ($team->club_slug ?? $team->club_id ?? ''),
            ]),
            default => '',
        };
    }

    /**
     * Validate an external team or club website before placing it in a link.
     *
     * Permits HTTP(S) URLs with a host, but rejects dangerous schemes,
     * embedded credentials, control characters and protocol-relative URLs.
     * This validator is also used for configured external image servers.
     *
     * @param string $url Candidate external web address.
     * @return string Validated URL, or an empty string if unsafe.
     */
    private function externalWebsiteUrl(string $url): string
    {
        $url = trim($url);

        if ($url === '' || preg_match('/[\x00-\x20\x7F\\\\]/', $url)) {
            return '';
        }

        if (!preg_match('#^https?://#i', $url) || filter_var($url, FILTER_VALIDATE_URL) === false) {
            return '';
        }

        $parts = parse_url($url);

        return is_array($parts)
            && !empty($parts['host'])
            && !isset($parts['user'])
            && !isset($parts['pass'])
            ? $url
            : '';
    }

    /**
     * Resolve the configured club logo or its placeholder image.
     *
     * Accepts only known image fields and string-based image paths. Invalid
     * configuration and missing images use the matching local placeholder;
     * external image URLs are validated before inclusion in the layout.
     *
     * @param Registry $params Logo and database options.
     * @param object $team Team image fields.
     * @return string Absolute image URL.
     */
    private function logoUrl(Registry $params, object $team): string
    {
        $configuredType = $params->get('logotype', 'logo_big');
        $type = is_string($configuredType) ? $configuredType : 'logo_big';
        if (!array_key_exists($type, self::PLACEHOLDERS)) {
            $type = 'logo_big';
        }

        $configuredPath = $team->{$type} ?? '';
        $path = is_string($configuredPath) ? trim($configuredPath) : '';
        if ($path === '') {
            $path = self::PLACEHOLDERS[$type];
        }

        if (preg_match('#^https?://#i', $path)) {
            $url = $this->externalWebsiteUrl($path);

            return $url !== '' ? $url : rtrim((string) Uri::root(), '/') . '/' . self::PLACEHOLDERS[$type];
        }

        if ((int) $params->get('cfg_which_database', 0) === 1) {
            $configuredServer = ComponentHelper::getParams('com_sportsmanagement')->get('cfg_which_database_server', '');
            $server = is_string($configuredServer) ? rtrim(trim($configuredServer), '/') : '';
            if ($this->externalWebsiteUrl($server) !== '') {
                return $server . '/' . ltrim($path, '/');
            }
        }

        return rtrim((string) Uri::root(), '/') . '/' . ltrim($path, '/');
    }

    /**
     * Retrieve validated external website URLs for ranked teams.
     *
     * Queries only the participating team IDs and prefers an accepted club website over a team website, ignoring invalid destinations.
     *
     * @param DatabaseInterface $db Database used for team lookup.
     * @param array $ranking Calculated ranking rows.
     * @return array<int, string> Safe URLs indexed by team ID.
     */
    private function websiteMap(DatabaseInterface $db, array $ranking): array
    {
        $teamIds = [];
        foreach ($ranking as $row) {
            if ((int) ($row->teamid ?? 0) > 0) {
                $teamIds[] = (int) $row->teamid;
            }
        }
        $teamIds = array_values(array_unique($teamIds));
        if (!$teamIds) {
            return [];
        }

        $query = $db->createQuery()
            ->select([
                $db->quoteName('t.id'),
                $db->quoteName('t.website', 'team_www'),
                $db->quoteName('c.website', 'club_www'),
            ])
            ->from($db->quoteName('#__sportsmanagement_team', 't'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_club', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('t.club_id'))
            ->whereIn($db->quoteName('t.id'), $teamIds, ParameterType::INTEGER);
        $db->setQuery($query);

        $map = [];
        foreach ($db->loadObjectList() ?: [] as $row) {
            // A malformed club URL must not hide a valid team website.
            foreach ([(string) ($row->club_www ?? ''), (string) ($row->team_www ?? '')] as $candidate) {
                $url = $this->externalWebsiteUrl($candidate);
                if ($url !== '') {
                    $map[(int) $row->id] = $url;
                    break;
                }
            }
        }

        return $map;
    }

    /**
     * Resolve the configured local or external SportsManagement database.
     *
     * Delegates selection to the database resolver, retaining the Joomla connection as the fallback for supported configurations.
     *
     * @param Registry $params Database selection setting.
     * @param DatabaseInterface $fallbackDatabase Default Joomla connection.
     * @return DatabaseInterface Selected database connection.
     */
    private function database(Registry $params, DatabaseInterface $fallbackDatabase): DatabaseInterface
    {
        return SportsManagementDatabaseResolver::resolve(
            $fallbackDatabase,
            (int) $params->get('cfg_which_database', 0)
        );
    }
}
