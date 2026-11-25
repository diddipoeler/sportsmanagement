<?php
/**
 * Joomla 5/6 native data helper for the SportsManagement random player module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementRandomPlayer\Site\Helper;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

final class RandomPlayerHelper
{
    /**
     * Select a random eligible player for the Joomla module.
     *
     * Validates the season, project and database selectors, refuses malformed explicit team filters, chooses a project team with players, and enriches the selected person with safe links, display names and images. Missing or non-string optional names and image fields are treated as empty values to support legacy database rows without PHP 8 property warnings. Invalid display-format settings fall back to the standard name order.
     *
     * Optional database failures return an empty result and log only the
     * exception type so the surrounding frontend page remains available.
     *
     * @param Registry $params Module filters and display options.
     * @param DatabaseInterface $fallbackDatabase Default Joomla connection.
     * @return array<string, object> Selected player context, or an empty result.
     */
    public function getData(Registry $params, DatabaseInterface $fallbackDatabase): array
    {
        $projectIds = $this->normaliseIds($params->get('p'));
        $teamSelection = $params->get('teams');
        $teamIds = $this->normaliseIds($teamSelection);
        $seasonId = self::positiveId($params->get('s', 0));

        // Invalid selectors must not open an optional/external database.
        if (!$projectIds || $seasonId <= 0
            || ($teamIds === [] && self::hasTeamSelection($teamSelection))) {
            return [];
        }

        $databaseSelector = self::databaseSelector($params->get('cfg_which_database', 0));

        // Invalid external database selectors must not silently select a
        // different connection or coerce arrays to integers under PHP 8.
        if ($databaseSelector === null) {
            return [];
        }

        try {
            $db = SportsManagementDatabaseResolver::resolve(
                $fallbackDatabase,
                $databaseSelector
            );
            $projectTeams = $this->getEligibleProjectTeams($db, $projectIds, $teamIds, $seasonId);

            if (!$projectTeams) {
                return [];
            }

            // A selected project team can have no eligible players. Try the
            // remaining teams before displaying an empty module.
            shuffle($projectTeams);
            $players = [];

            foreach ($projectTeams as $projectTeam) {
                $players = $this->getPlayersForProjectTeam(
                    $db,
                    (int) $projectTeam->projectteam_id,
                    (int) $projectTeam->project_id,
                    $seasonId
                );

                if ($players) {
                    break;
                }
            }

            if (!$players) {
                return [];
            }
        } catch (\Throwable $e) {
            Log::add(__METHOD__ . ' failed (' . get_class($e) . ').', Log::ERROR, 'com_sportsmanagement');

            return [];
        }

        $row = $players[random_int(0, count($players) - 1)];
        $nameFormat = self::nonNegativeSelector($params->get('name_format', 0)) ?? 0;
        $row->display_name = $this->formatName(
            self::textValue($row->firstname ?? null),
            self::textValue($row->nickname ?? null),
            self::textValue($row->lastname ?? null),
            $nameFormat
        );
        $row->project_slug = $row->project_id . ':' . self::textValue($row->project_alias ?? null);
        $row->person_slug = $row->person_id . ':' . self::textValue($row->person_alias ?? null);
        $row->team_slug = $row->team_id . ':' . self::textValue($row->team_alias ?? null);
        // LEFT JOINs and older databases may omit optional images. A null
        // team-player picture falls back to the person's own portrait rather
        // than triggering undefined-property notices under PHP 8.
        $row->picture_url = $this->picture(self::textValue($row->teamplayer_picture ?? null) ?: self::textValue($row->person_picture ?? null));
        $row->team_picture_url = $this->mediaUrl(self::textValue($row->team_picture ?? null));
        $row->flag_url = $this->mediaUrl(self::textValue($row->country_picture ?? null));

        $base = [
            'cfg_which_database' => $databaseSelector,
            's' => (int) $row->season_id,
            'p' => $row->project_slug,
        ];
        $row->player_url = $this->route(
            'player',
            $base + ['tid' => $row->team_slug, 'pid' => $row->person_slug]
        );
        $row->team_url = $this->route(
            'teaminfo',
            $base + ['tid' => $row->team_slug, 'ptid' => (int) $row->projectteam_id]
        );

        return ['player' => $row];
    }

    /**
     * Find eligible project teams in the requested season.
     *
     * Uses bound project and season IDs, optional team filters and published roster joins to select project teams with player records.
     *
     * @param DatabaseInterface $db Resolved SportsManagement database.
     * @param array<int, int> $projectIds Allowed project IDs.
     * @param array<int, int> $teamIds Optional allowed team IDs.
     * @param int $seasonId Positive season identifier.
     * @return array<int, object> Eligible project team records.
     */
    private function getEligibleProjectTeams(
        DatabaseInterface $db,
        array $projectIds,
        array $teamIds,
        int $seasonId
    ): array {
        $query = $db->createQuery()
            ->select([
                $db->quoteName('pt.id', 'projectteam_id'),
                $db->quoteName('pt.project_id'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_team_person_id', 'tp')
                . ' ON ' . $db->quoteName('tp.team_id') . ' = ' . $db->quoteName('st.team_id')
                . ' AND ' . $db->quoteName('tp.season_id') . ' = ' . $db->quoteName('st.season_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project', 'p')
                . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('pt.project_id')
                . ' AND ' . $db->quoteName('p.season_id') . ' = ' . $db->quoteName('st.season_id')
            )
            ->whereIn($db->quoteName('pt.project_id'), $projectIds, ParameterType::INTEGER)
            ->where($db->quoteName('st.season_id') . ' = :eligibleSeason')
            ->where($db->quoteName('tp.season_id') . ' = :eligiblePlayerSeason')
            ->where($db->quoteName('tp.persontype') . ' = 1')
            ->where($db->quoteName('tp.published') . ' = 1')
            ->where($db->quoteName('p.published') . ' = 1')
            ->group([
                $db->quoteName('pt.id'),
                $db->quoteName('pt.project_id'),
            ])
            ->bind(':eligibleSeason', $seasonId, ParameterType::INTEGER)
            ->bind(':eligiblePlayerSeason', $seasonId, ParameterType::INTEGER);

        if ($teamIds) {
            $query->whereIn($db->quoteName('st.team_id'), $teamIds, ParameterType::INTEGER);
        }

        $db->setQuery($query);

        return self::validRows($db->loadObjectList(), ['projectteam_id', 'project_id']);
    }

    /**
     * Load eligible published players of a project team.
     *
     * Joins person, season roster, position, team and country metadata using bound project-team, project and season identifiers.
     *
     * @param DatabaseInterface $db Resolved SportsManagement database.
     * @param int $projectTeamId Positive project-team identifier.
     * @param int $projectId Positive project identifier.
     * @param int $seasonId Positive season identifier.
     * @return array<int, object> Matching player records.
     */
    private function getPlayersForProjectTeam(
        DatabaseInterface $db,
        int $projectTeamId,
        int $projectId,
        int $seasonId
    ): array {
        $query = $db->createQuery()
            ->select([
                $db->quoteName('p.id', 'project_id'),
                $db->quoteName('p.name', 'project_name'),
                $db->quoteName('p.alias', 'project_alias'),
                $db->quoteName('p.season_id'),
                $db->quoteName('ps.id', 'person_id'),
                $db->quoteName('ps.firstname'),
                $db->quoteName('ps.nickname'),
                $db->quoteName('ps.lastname'),
                $db->quoteName('ps.alias', 'person_alias'),
                $db->quoteName('ps.country'),
                $db->quoteName('ps.picture', 'person_picture'),
                $db->quoteName('tp.picture', 'teamplayer_picture'),
                $db->quoteName('pos.name', 'position_name'),
                $db->quoteName('t.id', 'team_id'),
                $db->quoteName('t.name', 'team_name'),
                $db->quoteName('t.short_name'),
                $db->quoteName('t.middle_name'),
                $db->quoteName('t.alias', 'team_alias'),
                $db->quoteName('t.picture', 'team_picture'),
                $db->quoteName('pt.id', 'projectteam_id'),
                $db->quoteName('co.picture', 'country_picture'),
            ])
            ->from($db->quoteName('#__sportsmanagement_season_team_person_id', 'tp'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('tp.team_id')
                . ' AND ' . $db->quoteName('st.season_id') . ' = ' . $db->quoteName('tp.season_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project_team', 'pt')
                . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st.id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project', 'p')
                . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('pt.project_id')
                . ' AND ' . $db->quoteName('p.season_id') . ' = ' . $db->quoteName('st.season_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_person', 'ps')
                . ' ON ' . $db->quoteName('ps.id') . ' = ' . $db->quoteName('tp.person_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_team', 't')
                . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_project_position', 'ppos')
                . ' ON ' . $db->quoteName('ppos.id') . ' = ' . $db->quoteName('tp.project_position_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_position', 'pos')
                . ' ON ' . $db->quoteName('pos.id') . ' = ' . $db->quoteName('ppos.position_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_countries', 'co')
                . ' ON ' . $db->quoteName('co.alpha3') . ' = ' . $db->quoteName('ps.country')
            )
            ->where($db->quoteName('p.published') . ' = 1')
            ->where($db->quoteName('ps.published') . ' = 1')
            ->where($db->quoteName('pt.id') . ' = :projectTeamId')
            ->where($db->quoteName('pt.project_id') . ' = :projectId')
            ->where($db->quoteName('tp.season_id') . ' = :playerSeasonId')
            ->where($db->quoteName('st.season_id') . ' = :teamSeasonId')
            ->where($db->quoteName('tp.persontype') . ' = 1')
            ->where($db->quoteName('tp.published') . ' = 1')
            ->bind(':projectTeamId', $projectTeamId, ParameterType::INTEGER)
            ->bind(':projectId', $projectId, ParameterType::INTEGER)
            ->bind(':playerSeasonId', $seasonId, ParameterType::INTEGER)
            ->bind(':teamSeasonId', $seasonId, ParameterType::INTEGER);
        $db->setQuery($query);

        return self::validRows($db->loadObjectList(), ['projectteam_id', 'project_id', 'person_id', 'team_id', 'season_id']);
    }

    /**
     * Filter database results before random selection and route construction.
     * Discards non-object rows and rows without positive integer references,
     * preventing malformed legacy data from producing unrelated player URLs.
     * @param mixed $rows Database result list.
     * @param array<int,string> $requiredIds Required record identifier fields.
     * @return array<int,object> Validated rows with contiguous numeric indexes.
     */
    private static function validRows(mixed $rows, array $requiredIds): array
    {
        if (!is_array($rows)) {
            return [];
        }

        $valid = [];
        foreach ($rows as $row) {
            if (!is_object($row)) {
                continue;
            }
            foreach ($requiredIds as $field) {
                $id = $row->{$field} ?? null;
                if ((!is_int($id) && !is_string($id))
                    || filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false) {
                    continue 2;
                }
            }
            $valid[] = $row;
        }

        return $valid;
    }

    /**
     * Normalise optional player names and media references from database rows.
     *
     * Only string values are accepted. Missing fields, arrays and objects
     * become empty strings so legacy imports cannot trigger PHP 8 conversion
     * warnings or accidentally produce invalid image paths.
     *
     * @param mixed $value Raw optional player field.
     * @return string Original text or an empty string for invalid values.
     */
    private static function textValue(mixed $value): string
    {
        return is_string($value) ? $value : '';
    }

    /**
     * Build a native SportsManagement Joomla view URL.
     *
     * Delegates route generation to the component's site routing helper.
     *
     * @param string $view Component view to display.
     * @param array<string, mixed> $parameters Route query parameters.
     * @return string Joomla-routed relative or absolute URL.
     */
    private function route(string $view, array $parameters): string
    {
        return SiteRouteHelper::view($view, $parameters);
    }

    /**
     * Select a safe player image or configured fallback.
     *
     * Checks that the player image resolves to a valid media URL and, for local paths, exists inside the Joomla site root after resolving symbolic links; rejects malformed placeholder settings.
     *
     * @param string $path Player or roster picture path.
     * @return string Valid player image URL or safe placeholder.
     */
    private function picture(string $path): string
    {
        $url = $this->mediaUrl($path);

        if ($url !== '') {
            if (preg_match('#^https?://#i', trim($path))) {
                return $url;
            }

            // Resolve symlinks before accepting a local portrait.
            $root = realpath(JPATH_ROOT);
            $absolute = $root === false ? false : realpath($root . '/' . ltrim(trim($path), '/'));

            if ($absolute !== false && str_starts_with($absolute, $root . DIRECTORY_SEPARATOR)
                && is_file($absolute)) {
                return $url;
            }
        }

        $placeholder = ComponentHelper::getParams('com_sportsmanagement')->get('ph_player', '');

        return $this->mediaUrl(is_string($placeholder) ? $placeholder : '');
    }

    /**
     * Build a validated URL for a Random Player image.
     *
     * Accepts credential-free HTTP(S) URLs or local media paths without traversal, encoded escapes or query components. Encodes each local path segment under Joomla's installation root.
     *
     * @param string $path Relative image path or absolute HTTP(S) URL.
     * @return string Safe media URL or an empty string.
     */
    private function mediaUrl(string $path): string
    {
        if (preg_match('/[\x00-\x1F\x7F]/', $path)) {
            return '';
        }

        $path = trim($path);

        if ($path === ''
            || preg_match('/[\\x00-\\x1F\\x7F]/', $path)
            || str_contains($path, '\\')
            || str_starts_with($path, '//')) {
            return '';
        }

        if (preg_match('#^https?://#i', $path)) {
            $parts = parse_url($path);

            return filter_var($path, FILTER_VALIDATE_URL) !== false
                && is_array($parts)
                && !empty($parts['host'])
                && !isset($parts['user'])
                && !isset($parts['pass'])
                ? $path : '';
        }

        // Reject alternate URI schemes, encoded traversal and query strings.
        // Encode local path segments under the Joomla installation root.
        $relativePath = ltrim($path, '/');
        if (preg_match('~^[a-z][a-z0-9+.-]*:~i', $relativePath)
            || str_contains($relativePath, ':')
            || str_contains($relativePath, '%')
            || strpbrk($relativePath, '?#') !== false
            || preg_match('~(?:^|/)\\.\\.?(?:/|$)~', $relativePath)) {
            return '';
        }

        $encoded = implode('/', array_map('rawurlencode', explode('/', $relativePath)));

        return rtrim((string) Uri::root(), '/') . '/' . $encoded;
    }

    /**
     * Format a player's first, last and nickname for display.
     *
     * Respects the configured name arrangement and removes empty name fragments from the result.
     *
     * @param string $firstName Player's first name.
     * @param string $nickName Optional nickname.
     * @param string $lastName Player's last name.
     * @param int $format Configured name format selector.
     * @return string Formatted player name.
     */
    private function formatName(string $firstName, string $nickName, string $lastName, int $format): string
    {
        $nick = $nickName !== '' ? "'{$nickName}'" : '';
        $parts = match ($format) {
            1 => [$lastName !== '' ? $lastName . ',' : '', $nick, $firstName],
            2 => [$lastName !== '' ? $lastName . ',' : '', $firstName, $nick],
            3 => [$firstName, $lastName],
            4 => [$lastName !== '' ? $lastName . ',' : '', $firstName],
            5 => [$nick !== '' ? $nick . ' -' : '', $firstName, $lastName],
            6 => [$nick !== '' ? $nick . ' -' : '', $lastName !== '' ? $lastName . ',' : '', $firstName],
            7 => [$firstName, $lastName, $nick !== '' ? '(' . $nickName . ')' : ''],
            default => [$firstName, $nick, $lastName],
        };

        return trim(implode(' ', array_values(array_filter($parts, static fn($value) => $value !== ''))));
    }

    /**
     * Validate the two database modes supported by the Joomla 5/6 resolver.
     *
     * Accepts only zero (Joomla) and one (external), including zero-padded
     * legacy choices. Rejects other values rather than routing them to an
     * unintended connection or coercing malformed module configuration.
     *
     * @param mixed $value Raw Random Player database configuration.
     * @return int|null Supported mode, or null for an invalid selection.
     */
    private static function databaseSelector(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value === 0 || $value === 1 ? $value : null;
        }

        if (!is_string($value) || strlen($value) > 64
            || preg_match('/^0*([01])$/D', $value, $matches) !== 1) {
            return null;
        }

        return (int) $matches[1];
    }

    /**
     * Validate nonnegative module options without lossy integer casts.
     *
     * Rejects booleans, arrays, partial numbers and overflows while supporting
     * zero-padded legacy decimal settings and the default database zero.
     *
     * @param mixed $value Name format or other nonnegative option.
     * @return int|null Nonnegative option, or null if malformed.
     */
    private static function nonNegativeSelector(mixed $value): ?int
    {
        if (!is_int($value) && (!is_string($value) || !preg_match('/^[0-9]+$/D', $value))) {
            return null;
        }

        if (is_string($value)) {
            $digits = ltrim($value, '0');
            $value = $digits === '' ? '0' : $digits;
        }

        $number = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);

        return $number === false ? null : $number;
    }

    /**
     * Parse a positive Joomla identifier or ID:alias selector.
     *
     * Allows Unicode aliases and zero-padded decimal IDs while rejecting unsupported types, malformed strings and overflowing numbers.
     *
     * @param mixed $value Raw ID or ID:alias value.
     * @return int Positive ID or zero for invalid input.
     */
    private static function positiveId(mixed $value): int
    {
        if (!is_int($value) && !is_string($value)) {
            return 0;
        }

        $raw = trim((string) $value);

        if (!preg_match('/^([0-9]+)(?::[\pL\pN_-]+)?$/Du', $raw, $parts)) {
            return 0;
        }

        $digits = ltrim($parts[1], '0');
        $digits = $digits === '' ? '0' : $digits;
        $max = (string) PHP_INT_MAX;

        if ($digits === '0'
            || strlen($digits) > strlen($max)
            || (strlen($digits) === strlen($max) && strcmp($digits, $max) > 0)) {
            return 0;
        }

        return (int) $digits;
    }

    /**
     * Distinguish an unconfigured team filter from a malformed selection.
     *
     * Empty, null and zero-valued legacy choices mean all teams. If an
     * explicitly configured filter contains no valid team identifiers,
     * the caller must return no player rather than broaden the SQL query.
     *
     * @param mixed $value Raw team multiselect module setting.
     * @return bool Whether a nonempty team filter was explicitly supplied.
     */
    private static function hasTeamSelection(mixed $value): bool
    {
        if ($value === null) {
            return false;
        }

        foreach (is_array($value) ? $value : [$value] as $entry) {
            if (!is_string($entry) && !is_int($entry)) {
                return true;
            }

            $text = trim((string) $entry);

            if ($text !== '' && preg_match('/^0+$/D', $text) !== 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * Collect unique positive project or team IDs from module settings.
     *
     * Accepts scalar or multiselect inputs, splits comma/semicolon/whitespace-separated lists, and ignores invalid or overflowing IDs. Each candidate uses the same validated positive-ID parser as season selection, including Unicode aliases. Limits the number of values and IDs passed to SQL queries.
     *
     * @param mixed $value Raw Joomla module selector.
     * @return array<int, int> Unique validated positive identifiers.
     */
    private function normaliseIds(mixed $value): array
    {
        $values = is_array($value) ? $value : [$value];
        $ids = [];

        // Both scalar and multiselect parameters may contain delimited IDs.
        $candidates = [];
        foreach (array_slice($values, 0, 200) as $entry) {
            if ((!is_string($entry) && !is_int($entry))
                || (is_string($entry) && strlen($entry) > 4096)) {
                continue;
            }
            foreach (preg_split('/[\s,;]+/', (string) $entry, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $candidate) {
                $candidates[] = $candidate;
                if (count($candidates) >= 500) {
                    break 2;
                }
            }
        }

        foreach ($candidates as $candidate) {
            // Project/team selectors use the same ID:alias syntax as seasons.
            // Reuse the validated parser so Unicode aliases, zero padding and
            // overflow handling are consistent across all module filters.
            $id = self::positiveId($candidate);

            if ($id > 0) {
                $ids[$id] = $id;
                if (count($ids) >= 100) {
                    break;
                }
            }
        }

        return array_values($ids);
    }
}
