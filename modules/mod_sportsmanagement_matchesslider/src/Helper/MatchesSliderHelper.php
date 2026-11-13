<?php
/**
 * Joomla 5/6 native helper for the SportsManagement matches slider module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementMatchesSlider\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;

use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

final class MatchesSliderHelper
{
    private const MAX_FILTER_IDS = 100;
    private const MAX_FILTER_VALUES = 200;
    private const MAX_FILTER_VALUE_BYTES = 4096;
    private const MAX_MATCH_ROWS = 500;

    /**
     * Obtain the primary Joomla database used by the matches slider.
     * Returns an injected DatabaseInterface when available; otherwise
     * loads the database from Joomla's DI container and rejects invalid services.
     * @param DatabaseInterface|null $database Optional injected Joomla connection.
     * @return DatabaseInterface Resolved primary database.
     * @throws \RuntimeException If the database service is unavailable.
     */
    private static function joomlaDatabase(?DatabaseInterface $database = null): DatabaseInterface
    {
        if ($database !== null) {
            return $database;
        }

        $database = Factory::getContainer()->get(DatabaseInterface::class);

        if (!$database instanceof DatabaseInterface) {
            throw new \RuntimeException('SportsManagement Matches Slider database service is unavailable.', 500);
        }

        return $database;
    }

    /**
     * Load a bounded list of published fixtures for the matches slider.
     *
     * Validates database, season and project selectors before constructing
     * queries, applies project-specific favorite-team filters and prepares
     * Joomla routes and safely normalised team-logo URLs.
     *
     * @param Registry $params Joomla module options.
     * @param object $module Current Joomla module record.
     * @param CMSApplicationInterface $app Active Joomla site application.
     * @param DatabaseInterface|null $joomlaDatabase Optional default connection.
     * @return array<int,object> Up to 500 prepared fixtures, or an empty list.
     */
    public function getData(
        Registry $params,
        object $module,
        CMSApplicationInterface $app,
        ?DatabaseInterface $joomlaDatabase = null
    ): array {
        if (!$app->isClient('site')) {
            throw new \RuntimeException('SportsManagement Matches Slider requires the Joomla site application.', 500);
        }

        $input = $app->getInput();
        $databaseValue = $input->get('cfg_which_database', null, 'raw');
        $seasonValue = $input->get('s', null, 'raw');
        $databaseMode = self::nonNegativeOption(
            $databaseValue ?? $params->get('cfg_which_database', 0)
        );
        $seasonId = self::nonNegativeOption($seasonValue ?? $params->get('s', 0));

        // Invalid request or module settings must not silently switch to
        // database zero or a different season.
        if ($databaseMode === null || $seasonId === null) {
            return [];
        }
        $requestProject = $input->get('p', null, 'raw');
        $projectIds = $this->ids($params->get('p', []));

        if ($requestProject !== null && $requestProject !== '' && $requestProject !== '0') {
            // A request may override the module's project list with one Joomla
            // ID:alias. Never silently accept malformed or multiple IDs.
            if (!is_string($requestProject) && !is_int($requestProject)) {
                return [];
            }

            $requestedIds = $this->ids($requestProject);

            if (count($requestedIds) !== 1) {
                return [];
            }

            $projectIds = $requestedIds;
        }

        if (!$projectIds) {
            return [];
        }

        $joomlaDatabase = self::joomlaDatabase($joomlaDatabase);
        $db = $this->database($databaseMode, $joomlaDatabase);
        $rawNameColumn = $params->get('team_names', 'short_name');
        $nameColumn = is_string($rawNameColumn) ? $rawNameColumn : 'short_name';

        if (!in_array($nameColumn, ['name', 'short_name', 'middle_name'], true)) {
            $nameColumn = 'short_name';
        }

        $rawPictureType = $params->get('picture_type', 'logo_big');
        $pictureType = is_string($rawPictureType) ? $rawPictureType : 'logo_big';
        $logoSelect = match ($pictureType) {
            'logo_small' => [
                $db->quoteName('c1.logo_small', 'logohome'),
                $db->quoteName('c2.logo_small', 'logoaway'),
            ],
            'logo_middle' => [
                $db->quoteName('c1.logo_middle', 'logohome'),
                $db->quoteName('c2.logo_middle', 'logoaway'),
            ],
            'team_picture' => [
                $db->quoteName('pt1.picture', 'logohome'),
                $db->quoteName('pt2.picture', 'logoaway'),
            ],
            'country' => [
                $db->quoteName('co1.picture', 'logohome'),
                $db->quoteName('co2.picture', 'logoaway'),
            ],
            default => [
                $db->quoteName('c1.logo_big', 'logohome'),
                $db->quoteName('c2.logo_big', 'logoaway'),
            ],
        };

        $query = $db->createQuery()
            ->select([
                $db->quoteName('m.id', 'match_id'),
                $db->quoteName('m.match_date'),
                $db->quoteName('m.match_timestamp'),
                $db->quoteName('m.match_number'),
                $db->quoteName('m.team1_result'),
                $db->quoteName('m.team2_result'),
                $db->quoteName('p.id', 'project_id'),
                $db->quoteName('p.season_id'),
                $db->quoteName('r.id', 'round_id'),
                $db->quoteName('st1.team_id', 'team1_id'),
                $db->quoteName('st2.team_id', 'team2_id'),
                $db->quoteName('t1.' . $nameColumn, 'teamhome'),
                $db->quoteName('t2.' . $nameColumn, 'teamaway'),
                $db->quoteName('p.alias', 'project_alias'),
                $db->quoteName('r.alias', 'round_alias'),
                $logoSelect[0],
                $logoSelect[1],
            ])
            ->from($db->quoteName('#__sportsmanagement_match', 'm'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_round', 'r')
                . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project', 'p')
                . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('r.project_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_project_team', 'pt1')
                . ' ON ' . $db->quoteName('pt1.id') . ' = ' . $db->quoteName('m.projectteam1_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_project_team', 'pt2')
                . ' ON ' . $db->quoteName('pt2.id') . ' = ' . $db->quoteName('m.projectteam2_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st1')
                . ' ON ' . $db->quoteName('st1.id') . ' = ' . $db->quoteName('pt1.team_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st2')
                . ' ON ' . $db->quoteName('st2.id') . ' = ' . $db->quoteName('pt2.team_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_team', 't1')
                . ' ON ' . $db->quoteName('t1.id') . ' = ' . $db->quoteName('st1.team_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_team', 't2')
                . ' ON ' . $db->quoteName('t2.id') . ' = ' . $db->quoteName('st2.team_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_club', 'c1')
                . ' ON ' . $db->quoteName('c1.id') . ' = ' . $db->quoteName('t1.club_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_club', 'c2')
                . ' ON ' . $db->quoteName('c2.id') . ' = ' . $db->quoteName('t2.club_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_countries', 'co1')
                . ' ON ' . $db->quoteName('co1.alpha3') . ' = ' . $db->quoteName('c1.country')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_countries', 'co2')
                . ' ON ' . $db->quoteName('co2.alpha3') . ' = ' . $db->quoteName('c2.country')
            )
            ->where($db->quoteName('m.published') . ' = 1')
            ->where($db->quoteName('p.published') . ' = 1')
            ->whereIn($db->quoteName('p.id'), $projectIds, ParameterType::INTEGER);

        if (self::nonNegativeOption($params->get('project_season', 1)) === 0) {
            $query->where($db->quoteName('r.id') . ' = ' . $db->quoteName('p.current_round'));
        }

        $selectedTeams = $params->get('teams', []);
        $teams = $this->ids($selectedTeams);
        $selectionRequested = array_filter(
            is_array($selectedTeams) ? $selectedTeams : [$selectedTeams],
            static fn (mixed $value): bool => $value !== null
                && trim((string) (is_scalar($value) ? $value : '')) !== ''
                && trim((string) (is_scalar($value) ? $value : '')) !== '0'
        );

        // An explicitly selected but invalid team must not disable the filter
        // and accidentally show every team's fixtures.
        if ($selectionRequested !== [] && $teams === []) {
            return [];
        }

        if ($teams) {
            $homeTeamPlaceholders = $query->bindArray($teams, ParameterType::INTEGER);
            $awayTeamPlaceholders = $query->bindArray($teams, ParameterType::INTEGER);
            $query->where(
                '(' . $db->quoteName('st1.team_id') . ' IN (' . implode(',', $homeTeamPlaceholders) . ')'
                . ' OR ' . $db->quoteName('st2.team_id') . ' IN (' . implode(',', $awayTeamPlaceholders) . '))'
            );
        }

        if (self::nonNegativeOption($params->get('use_fav', 0)) === 1
            && !$this->applyFavoriteTeamFilter($db, $query, $projectIds)
        ) {
            return [];
        }

        $query->order([
            $db->quoteName('m.match_date') . ' ASC',
            $db->quoteName('m.match_number') . ' ASC',
        ]);
        // An old slider configuration can reference years of matches. Bound
        // each module render to a reasonable number of current rows.
        $db->setQuery($query, 0, self::MAX_MATCH_ROWS);
        $rows = $db->loadObjectList() ?: [];
        $pictureServer = $this->pictureServer($databaseMode);

        if (!is_array($rows)) {
            return [];
        }

        $validRows = [];
        foreach ($rows as $match) {
            if (!is_object($match)) {
                continue;
            }

            // Joomla ID:alias slugs are portable when assembled in PHP rather
            // than via the MySQL-specific CONCAT_WS expression.
            $projectId = self::nonNegativeOption($match->project_id ?? null);
            $roundId = self::nonNegativeOption($match->round_id ?? null);
            if ($projectId === null || $projectId <= 0 || $roundId === null || $roundId <= 0) {
                continue;
            }

            $projectAlias = $match->project_alias ?? '';
            $roundAlias = $match->round_alias ?? '';
            $match->project_slug = $projectId . ':'
                . (is_string($projectAlias) ? $projectAlias : '');
            $match->round_slug = $roundId . ':'
                . (is_string($roundAlias) ? $roundAlias : '');
            $match->link = $this->matchLink($match, $params, $databaseMode, $seasonId);
            $homeLogo = $match->logohome ?? '';
            $awayLogo = $match->logoaway ?? '';
            $match->home_logo_url = $this->mediaUrl(is_string($homeLogo) ? $homeLogo : '', $pictureServer);
            $match->away_logo_url = $this->mediaUrl(is_string($awayLogo) ? $awayLogo : '', $pictureServer);
            $validRows[] = $match;
        }

        return $validRows;
    }

    /**
     * Apply project-specific favorite teams without MySQL FIND_IN_SET.
     * Each project must match only its own favorite team IDs. Filters
     * malformed database rows before constructing the bound SQL conditions.
     *
     * @param DatabaseInterface $db Selected SportsManagement connection.
     * @param object $query Current Joomla match-list query.
     * @param array<int,int> $projectIds Validated project IDs.
     * @return bool Whether at least one scoped favorite-team condition exists.
     */
    private function applyFavoriteTeamFilter(DatabaseInterface $db, object $query, array $projectIds): bool
    {
        if ($projectIds === []) {
            return false;
        }

        $favoritesQuery = $db->createQuery()
            ->select([
                $db->quoteName('p.id'),
                $db->quoteName('p.fav_team'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project', 'p'))
            ->where($db->quoteName('p.published') . ' = 1')
            ->whereIn($db->quoteName('p.id'), $projectIds, ParameterType::INTEGER);
        $db->setQuery($favoritesQuery, 0, self::MAX_FILTER_IDS);

        $conditions = [];
        $number = 0;

        $projectRows = $db->loadObjectList();
        if (!is_array($projectRows)) {
            return false;
        }

        foreach ($projectRows as $project) {
            if (!is_object($project)) {
                continue;
            }

            $projectId = self::nonNegativeOption($project->id ?? null) ?? 0;
            $teams = $this->ids($project->fav_team ?? '');

            if ($projectId <= 0 || !in_array($projectId, $projectIds, true) || $teams === []) {
                continue;
            }

            $projectBinding = ':favoriteProject' . $number++;
            $query->bind($projectBinding, $projectId, ParameterType::INTEGER);
            $homeBindings = $query->bindArray($teams, ParameterType::INTEGER);
            $awayBindings = $query->bindArray($teams, ParameterType::INTEGER);

            $conditions[] = '(' . $db->quoteName('p.id') . ' = ' . $projectBinding
                . ' AND (' . $db->quoteName('st1.team_id') . ' IN (' . implode(',', $homeBindings) . ')'
                . ' OR ' . $db->quoteName('st2.team_id') . ' IN (' . implode(',', $awayBindings) . ')))';
        }

        if ($conditions === []) {
            return false;
        }

        $query->where('(' . implode(' OR ', $conditions) . ')');

        return true;
    }

    /**
     * Build a Joomla results or ranking link for a slider fixture.
     * Maps configured link modes to supported site views and preserves
     * the season, project and database selection in the route.
     * @param object $match Fixture and project context.
     * @param Registry $params Slider link configuration.
     * @param int $databaseMode Selected SportsManagement database.
     * @param int $seasonId Active season identifier.
     * @return string Joomla route to fixture results or ranking.
     */
    private function matchLink(object $match, Registry $params, int $databaseMode, int $seasonId): string
    {
        $rawLinkMode = $params->get('p_link_func', 'results');
        $view = match (is_string($rawLinkMode) ? $rawLinkMode : 'results') {
            'ranking' => 'ranking',
            'resultsrank' => 'resultsranking',
            default => 'results',
        };

        $query = [
            'cfg_which_database' => $databaseMode,
            's' => $seasonId > 0 ? $seasonId : (self::nonNegativeOption($match->season_id ?? 0) ?? 0),
            'p' => (string) ($match->project_slug ?? $match->project_id),
            'r' => (string) ($match->round_slug ?? $match->round_id),
            'division' => 0,
        ];

        if ($view === 'ranking') {
            $query += ['type' => 0, 'from' => 0, 'to' => 0];
        } else {
            $query += ['mode' => 0, 'order' => '', 'layout' => ''];
        }

        return SiteRouteHelper::view($view, $query);
    }

    /**
     * Find and validate the configured base URL for fixture images.
     * Uses the external database picture server when selected, checks
     * the URL for unsafe credentials and invalid components, and falls
     * back to Joomla's site root for local images. An invalid external
     * image server returns an empty base to avoid misleading image URLs.
     * @param int $databaseMode Primary or external database selection.
     * @return string Sanitised image base URL or an empty invalid external base.
     */
    private function pictureServer(int $databaseMode): string
    {
        $componentParams = ComponentHelper::getParams('com_sportsmanagement');
        $configuredServer = $componentParams->get('cfg_which_database_server', '');
        $server = $databaseMode > 0
            ? (is_string($configuredServer) && strlen($configuredServer) <= 2048 ? trim($configuredServer) : '')
            : (string) Uri::root();

        // External image bases must not expose credentials or place images
        // behind a query string, fragment or a different URL scheme.
        $parts = parse_url($server);
        if (!preg_match('~^https?://~i', $server)
            || preg_match('/[\x00-\x20\x7F]/', $server)
            || str_contains($server, '\\')
            || filter_var($server, FILTER_VALIDATE_URL) === false
            || !is_array($parts)
            || !in_array(strtolower((string) ($parts['scheme'] ?? '')), ['http', 'https'], true)
            || empty($parts['host'])
            || isset($parts['user'])
            || isset($parts['pass'])
            || isset($parts['query'])
            || isset($parts['fragment'])
        ) {
            // A broken external image host must not silently become local;
            // the corresponding images may not exist on this Joomla site.
            return $databaseMode > 0 ? '' : rtrim((string) Uri::root(), '/') . '/';
        }

        return rtrim($server, '/') . '/';
    }

    /**
     * Produce a safe browser URL for the selected fixture image.
     * Rejects control characters, backslashes, malformed external URLs
     * and unsafe paths before combining the image with the base server.
     * Missing or invalid external server settings suppress relative image URLs.
     * @param string $path Stored image path or absolute external image URL.
     * @param string $pictureServer Validated module image server.
     * @return string Safe image URL or an empty string.
     */
    private function mediaUrl(string $path, string $pictureServer): string
    {
        if ($pictureServer === '') {
            return '';
        }

        $path = trim($path);

        if ($path === ''
            || preg_match('/[\x00-\x1F\x7F]/', $path)
            || str_contains($path, '\\')
        ) {
            return '';
        }

        if (preg_match('~^https?://~i', $path)) {
            if (preg_match('/[\x00-\x20\x7F]/', $path)
                || filter_var($path, FILTER_VALIDATE_URL) === false
            ) {
                return '';
            }

            $parts = parse_url($path);

            return is_array($parts)
                && !empty($parts['host'])
                && !isset($parts['user'])
                && !isset($parts['pass'])
                ? $path
                : '';
        }

        if (str_starts_with($path, '//')
            || preg_match('~^[a-z][a-z0-9+.-]*:~i', $path)
            || preg_match('~(?:^|/)\.\.?(?:/|$)~', $path)
            || str_contains($path, '%')
            || strpbrk($path, '?#') !== false
        ) {
            return '';
        }

        $relative = ltrim($path, '/');
        $encoded = implode('/', array_map('rawurlencode', explode('/', $relative)));

        return rtrim($pictureServer, '/') . '/' . $encoded;
    }

    /**
     * Normalise configured project or team IDs into a bounded filter list.
     *
     * Accepts positive decimal IDs and legacy Joomla ID:alias slugs,
     * rejecting invalid tokens and integer overflows. Caps the number
     * of inspected values, token lengths and returned unique identifiers.
     *
     * @param mixed $value Raw scalar or list of module identifiers.
     * @return array<int,int> Up to 100 validated distinct positive IDs.
     */
    private function ids(mixed $value): array
    {
        $values = is_array($value) ? $value : [$value];
        $ids = [];

        foreach (array_slice($values, 0, self::MAX_FILTER_VALUES) as $candidate) {
            if ((!is_int($candidate) && !is_string($candidate))
                || (is_string($candidate) && strlen($candidate) > self::MAX_FILTER_VALUE_BYTES)) {
                continue;
            }

            foreach (preg_split('/[\s,;]+/', (string) $candidate, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $part) {
                // Project-team selectors may return Joomla ID:alias slugs.
                // Reject partial matches, malformed IDs and integer overflow.
                if (!preg_match('/^([0-9]+)(?::[A-Za-z0-9_-]+)?$/D', $part, $match)) {
                    continue;
                }

                $id = filter_var(ltrim($match[1], '0'), FILTER_VALIDATE_INT, [
                    'options' => ['min_range' => 1],
                ]);

                if ($id !== false) {
                    $ids[$id] = $id;

                    if (count($ids) >= self::MAX_FILTER_IDS) {
                        return array_values($ids);
                    }
                }
            }
        }

        return array_values($ids);
    }

    /**
     * Parse a nonnegative Joomla request or module option without coercion.
     *
     * Accepts nonnegative integers and zero-padded decimal strings, but
     * rejects booleans, arrays, fractional numbers and integer overflow.
     *
     * @param mixed $value Requested or configured numeric option.
     * @return int|null Valid nonnegative integer or null for malformed input.
     */
    private static function nonNegativeOption(mixed $value): ?int
    {
        if (!is_int($value) && (!is_string($value) || strlen($value) > 256
            || preg_match('/^[0-9]+$/D', $value) !== 1)) {
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
     * Resolve the active SportsManagement connection for slider queries.
     * Delegates primary or external connection selection to the native
     * Joomla 5/6 database resolver and its configured selector.
     * @param int $databaseMode Configured SportsManagement database mode.
     * @param DatabaseInterface $fallbackDatabase Joomla primary connection.
     * @return DatabaseInterface Database used for match slider data.
     */
    private function database(int $databaseMode, DatabaseInterface $fallbackDatabase): DatabaseInterface
    {
        return SportsManagementDatabaseResolver::resolve(
            $fallbackDatabase,
            $databaseMode
        );
    }
}
