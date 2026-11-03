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

    private const WEBSITE_QUERY_BATCH_SIZE = 200;

    private const PLACEHOLDERS = [
        'logo_big' => 'images/com_sportsmanagement/database/placeholders/placeholder_150.png',
        'projectteam_picture' => 'images/com_sportsmanagement/database/placeholders/placeholder_450_2.png',
        'team_picture' => 'images/com_sportsmanagement/database/placeholders/placeholder_450_2.png',
    ];

    /**
     * Build the club icon, team link and ranking data for the module layout.
     *
     * Selects the configured project and division, calculates the ranking,
     * validates optional ranking rows and team identifiers, then resolves
     * team links and image URLs. Missing projects yield empty collections.
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
        $result = is_array($result) ? $result : [];
        $project = is_object($result['project'] ?? null) ? $result['project'] : null;
        $ranking = is_array($result['ranking'] ?? null) ? $result['ranking'] : [];

        if (empty($project->id) || !$ranking) {
            return ['project' => $project ?: null, 'ranking' => [], 'teams' => []];
        }

        // Fetch club websites only for the configured external-link mode.
        $websites = self::nonNegativeInteger($params->get('teamlink', 0)) === 5
            ? $this->websiteMap($db, $ranking)
            : [];
        $teams = [];
        foreach ($ranking as $row) {
            if (!is_object($row) || !is_object($row->team ?? null)) {
                continue;
            }

            $team = $row->team;
            $projectTeamId = $this->firstId($row->projectteamid ?? null);
            if ($projectTeamId <= 0) {
                continue;
            }

            $name = $team->name ?? '';
            $teams[$projectTeamId] = [
                'name' => is_string($name) ? $name : '',
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
     * Prefers a strictly validated project ID from the current component
     * request when enabled; otherwise uses the configured project identifier.
     *
     * @param Registry $params Project selection settings.
     * @param CMSApplicationInterface $app Application request source.
     * @return int Positive project ID, or zero for no valid project.
     */
    private function projectId(Registry $params, CMSApplicationInterface $app): int
    {
        $input = $app->getInput();
        if (
            self::nonNegativeInteger($params->get('usepfromcomponent', 0)) === 1
            && $input->getCmd('option', '') === 'com_sportsmanagement'
            && $this->firstId($input->get('p', null, 'RAW')) > 0
        ) {
            return $this->firstId($input->get('p', null, 'RAW'));
        }

        return $this->firstId($params->get('project_ids', 0));
    }

    /**
     * Extract the first valid positive identifier from a module selector.
     *
     * Scans up to 100 configured values instead of discarding a valid project
     * when a previous legacy value is empty or malformed. Accepts positive
     * integers, zero-padded IDs and safe Joomla ID:alias slugs; nested arrays,
     * partial numbers and overflowing IDs never produce a valid result.
     *
     * @param mixed $value Scalar or list of configured identifiers.
     * @return int First valid positive ID, or zero if none is valid.
     */
    private function firstId(mixed $value): int
    {
        $values = is_array($value) ? array_slice($value, 0, 100) : [$value];

        foreach ($values as $candidate) {
            if (!is_int($candidate) && !is_string($candidate)) {
                continue;
            }

            $candidate = trim((string) $candidate);

            if (strlen($candidate) > 255
                || preg_match('/^([0-9]+)(?::[A-Za-z0-9_-]+)?$/D', $candidate, $matches) !== 1) {
                continue;
            }

            $digits = ltrim($matches[1], '0');
            $id = $digits === '' ? 0 : self::nonNegativeInteger($digits);

            if ($id > 0) {
                return $id;
            }
        }

        return 0;
    }

    /**
     * Parse a nonnegative module selector without PHP's partial number casts.
     *
     * Allows integers and zero-padded decimal strings within the PHP integer
     * range. Booleans, arrays, negative numbers, whitespace and trailing
     * characters are rejected, preventing invalid route and database values.
     *
     * @param mixed $value Untrusted configured selector or database value.
     * @return int Valid nonnegative selector, or zero for invalid input.
     */
    private static function nonNegativeInteger(mixed $value): int
    {
        if (!is_int($value) && (!is_string($value) || preg_match('/^[0-9]+$/D', $value) !== 1)) {
            return 0;
        }

        if (is_string($value)) {
            $digits = ltrim($value, '0');
            $value = $digits === '' ? '0' : $digits;
        }

        $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);

        return $id === false ? 0 : $id;
    }

    /**
     * Construct the configured destination URL for a club icon.
     *
     * Routes internal team and club views through the component router only
     * with verified project/team/club identifiers and safe matching slugs;
     * external website mode retains its separate validated URL handling and
     * ignores malformed non-string values in the website map.
     *
     * @param Registry $params Link mode and database settings.
     * @param object $project Current project record.
     * @param object $team Team record.
     * @param array<int, string> $websites Validated external websites.
     * @return string Route URL, or an empty string when disabled.
     */
    private function teamLink(Registry $params, object $project, object $team, array $websites): string
    {
        $teamLink = self::nonNegativeInteger($params->get('teamlink', 0));
        if ($teamLink === 0) {
            return '';
        }

        if ($teamLink === 5) {
            $teamId = $this->firstId($team->id ?? 0);
            $website = $websites[$teamId] ?? null;

            return is_string($website) ? self::externalWebsiteUrl($website) : '';
        }

        $projectSlug = self::validatedRouteSlug($project->slug ?? null, $project->id ?? null);
        $teamSlug = self::validatedRouteSlug($team->team_slug ?? null, $team->id ?? null);
        $clubSlug = self::validatedRouteSlug($team->club_slug ?? null, $team->club_id ?? null);

        if ($projectSlug === '' || ($teamLink >= 1 && $teamLink <= 3 && $teamSlug === '')
            || ($teamLink === 4 && $clubSlug === '')) {
            return '';
        }

        $common = [
            'cfg_which_database' => self::nonNegativeInteger($params->get('cfg_which_database', 0)),
            's' => self::nonNegativeInteger($params->get('s', 0)),
            'p' => $projectSlug,
        ];

        return match ($teamLink) {
            1 => SiteRouteHelper::view('teaminfo', $common + [
                'tid' => $teamSlug,
                'ptid' => 0,
            ]),
            2 => SiteRouteHelper::view('roster', $common + [
                'tid' => $teamSlug,
                'ptid' => 0,
            ]),
            3 => SiteRouteHelper::view('teamplan', $common + [
                'tid' => $teamSlug,
                'division' => 0,
                'mode' => 0,
                'ptid' => 0,
            ]),
            4 => SiteRouteHelper::view('clubinfo', $common + [
                'cid' => $clubSlug,
            ]),
            default => '',
        };
    }

    /**
     * Validate a Joomla ID:alias route slug against its source record ID.
     *
     * Accepts positive integer IDs or a matching numeric ID and a safe
     * Unicode alias. Invalid, mismatched or oversized aliases fall back to
     * the verified numeric record ID; missing or malformed IDs produce no
     * route so arrays and objects never trigger PHP 8 conversion warnings.
     *
     * @param mixed $slug Untrusted route slug from a ranking record.
     * @param mixed $recordId Identifier of the referenced Joomla record.
     * @return string Verified ID:alias slug, numeric ID, or empty string.
     */
    private static function validatedRouteSlug(mixed $slug, mixed $recordId): string
    {
        $id = self::nonNegativeInteger($recordId);

        if ($id <= 0) {
            return '';
        }

        if (is_string($slug) && strlen($slug) <= 255
            && preg_match('/^([0-9]+):([\\p{L}\\p{N}_-]+)$/uD', $slug, $matches) === 1
            && self::nonNegativeInteger($matches[1]) === $id) {
            return $id . ':' . $matches[2];
        }

        return (string) $id;
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
    private static function externalWebsiteUrl(string $url): string
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
     * Relative paths must remain within the media base and are URL-encoded
     * per segment; invalid paths use a trusted local placeholder. A remote
     * image server must not contain URL query strings or fragments.
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
            $url = self::externalWebsiteUrl($path);

            return $url !== '' ? $url : rtrim((string) Uri::root(), '/') . '/' . self::PLACEHOLDERS[$type];
        }

        // An untrusted database path must never escape the media base or
        // introduce URL parameters through a local or external image server.
        $safePath = self::safeRelativeLogoPath($path);

        if ($safePath === '') {
            return rtrim((string) Uri::root(), '/') . '/' . self::PLACEHOLDERS[$type];
        }

        $encodedPath = implode('/', array_map('rawurlencode', explode('/', $safePath)));

        if (self::nonNegativeInteger($params->get('cfg_which_database', 0)) === 1) {
            $configuredServer = ComponentHelper::getParams('com_sportsmanagement')->get('cfg_which_database_server', '');
            $server = self::safeImageServerBase($configuredServer);
            if ($server !== '') {
                return $server . '/' . $encodedPath;
            }
        }

        return rtrim((string) Uri::root(), '/') . '/' . $encodedPath;
    }

    /**
     * Validate an external image server base before appending logo paths.
     *
     * An otherwise valid website URL containing a query or fragment cannot
     * act as an image directory: appending a path would change the query or
     * fragment instead of the requested resource. Rejects malformed values,
     * credentials and unsafe schemes through the website URL validator.
     *
     * @param mixed $value Configured remote image server URL.
     * @return string Safe HTTP(S) base URL without a trailing slash, or empty.
     */
    private static function safeImageServerBase(mixed $value): string
    {
        if (!is_string($value)) {
            return '';
        }

        $server = rtrim(trim($value), '/');

        if ($server === '' || parse_url($server, PHP_URL_QUERY) !== null
            || parse_url($server, PHP_URL_FRAGMENT) !== null) {
            return '';
        }

        return self::externalWebsiteUrl($server) !== '' ? $server : '';
    }

    /**
     * Validate a relative team logo file path before joining it to a URL.
     *
     * Rejects absolute paths, traversal segments, encoded traversal, URI
     * schemes, backslashes, query strings and control characters. Safe
     * nested paths and Unicode or spaced filenames are retained unchanged
     * for per-segment URL encoding by the calling method.
     *
     * @param mixed $path Raw image filename or relative media path.
     * @return string Validated relative path, or empty string when unsafe.
     */
    private static function safeRelativeLogoPath(mixed $path): string
    {
        if (!is_string($path)) {
            return '';
        }

        $path = trim($path);

        if ($path === '' || str_starts_with($path, '/')
            || str_contains($path, '\\')
            || str_contains($path, ':')
            || str_contains($path, '%')
            || strpbrk($path, '?#') !== false
            || preg_match('/[\\x00-\\x1F\\x7F]/', $path) === 1) {
            return '';
        }

        foreach (explode('/', $path) as $segment) {
            if ($segment === '' || $segment === '.' || $segment === '..') {
                return '';
            }
        }

        return $path;
    }

    /**
     * Retrieve validated external website URLs for ranked teams.
     *
     * Queries only valid positive IDs from the ranking, then prefers a
     * validated club website over a team website. Malformed ranking entries
     * and external URLs are excluded from the resulting map. Requests use
     * bounded groups of team IDs instead of one unbounded SQL IN clause.
     *
     * @param DatabaseInterface $db Database used for team lookup.
     * @param array $ranking Calculated ranking rows.
     * @return array<int, string> Safe URLs indexed by team ID.
     */
    private function websiteMap(DatabaseInterface $db, array $ranking): array
    {
        $teamIds = $this->rankingTeamIds($ranking);

        if ($teamIds === []) {
            return [];
        }

        $map = [];
        foreach (array_chunk($teamIds, self::WEBSITE_QUERY_BATCH_SIZE) as $batchIds) {
            $query = $db->createQuery()
                ->select([
                    $db->quoteName('t.id'),
                    $db->quoteName('t.website', 'team_www'),
                    $db->quoteName('c.website', 'club_www'),
                ])
                ->from($db->quoteName('#__sportsmanagement_team', 't'))
                ->join('LEFT', $db->quoteName('#__sportsmanagement_club', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('t.club_id'))
                ->whereIn($db->quoteName('t.id'), $batchIds, ParameterType::INTEGER);
            $db->setQuery($query);

            $websiteRows = $db->loadObjectList() ?: [];

            foreach (is_array($websiteRows) ? $websiteRows : [] as $row) {
                if (!is_object($row)) {
                    continue;
                }

                $teamId = $this->firstId($row->id ?? null);

                if ($teamId <= 0) {
                    continue;
                }

                // Malformed database fields must not trigger array-to-string warnings.
                // A rejected club URL must not hide a valid team website.
                foreach ([$row->club_www ?? '', $row->team_www ?? ''] as $candidate) {
                    if (!is_string($candidate)) {
                        continue;
                    }

                    $url = self::externalWebsiteUrl($candidate);
                    if ($url !== '') {
                        $map[$teamId] = $url;
                        break;
                    }
                }
        }
        }

        return $map;
    }

    /**
     * Collect unique valid team IDs from ranking rows for website lookups.
     *
     * Discards malformed ranking entries, canonicalises old zero-padded IDs
     * and preserves their first-seen order. The caller queries the resulting
     * IDs in batches to avoid excessively large bound SQL IN expressions.
     *
     * @param array<int, mixed> $ranking Raw calculated ranking rows.
     * @return array<int, int> Valid distinct positive team identifiers.
     */
    private function rankingTeamIds(array $ranking): array
    {
        $ids = [];

        foreach ($ranking as $row) {
            if (!is_object($row)) {
                continue;
            }

            $id = $this->firstId($row->teamid ?? null);

            if ($id > 0) {
                $ids[$id] = $id;
            }
        }

        return array_values($ids);
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
            self::nonNegativeInteger($params->get('cfg_which_database', 0))
        );
    }
}
