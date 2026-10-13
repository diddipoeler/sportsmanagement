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

    /** @return array<int,object> */
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
        $databaseMode = $input->getInt('cfg_which_database', (int) $params->get('cfg_which_database', 0));
        $seasonId = $input->getInt('s', (int) $params->get('s', 0));
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
        $nameColumn = (string) $params->get('team_names', 'short_name');

        if (!in_array($nameColumn, ['name', 'short_name', 'middle_name'], true)) {
            $nameColumn = 'short_name';
        }

        $pictureType = (string) $params->get('picture_type', 'logo_big');
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

        if (!(int) $params->get('project_season', 1)) {
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

        if ((int) $params->get('use_fav', 0) === 1
            && !$this->applyFavoriteTeamFilter($db, $query, $projectIds)
        ) {
            return [];
        }

        $query->order([
            $db->quoteName('m.match_date') . ' ASC',
            $db->quoteName('m.match_number') . ' ASC',
        ]);
        $db->setQuery($query);
        $rows = $db->loadObjectList() ?: [];
        $pictureServer = $this->pictureServer($databaseMode);

        foreach ($rows as $match) {
            // Joomla ID:alias slugs are portable when assembled in PHP rather
            // than via the MySQL-specific CONCAT_WS expression.
            $match->project_slug = (int) ($match->project_id ?? 0)
                . ':' . (string) ($match->project_alias ?? '');
            $match->round_slug = (int) ($match->round_id ?? 0)
                . ':' . (string) ($match->round_alias ?? '');
            $match->link = $this->matchLink($match, $params, $databaseMode, $seasonId);
            $match->home_logo_url = $this->mediaUrl((string) ($match->logohome ?? ''), $pictureServer);
            $match->away_logo_url = $this->mediaUrl((string) ($match->logoaway ?? ''), $pictureServer);
        }

        return $rows;
    }

    /**
     * Apply project-specific favorite teams without MySQL FIND_IN_SET.
     * Each project must match only its own favorite team IDs.
     *
     * @param array<int,int> $projectIds
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
        $db->setQuery($favoritesQuery);

        $conditions = [];
        $number = 0;

        foreach ($db->loadObjectList() ?: [] as $project) {
            $projectId = (int) ($project->id ?? 0);
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

    private function matchLink(object $match, Registry $params, int $databaseMode, int $seasonId): string
    {
        $view = match ((string) $params->get('p_link_func', 'results')) {
            'ranking' => 'ranking',
            'resultsrank' => 'resultsranking',
            default => 'results',
        };

        $query = [
            'cfg_which_database' => $databaseMode,
            's' => $seasonId > 0 ? $seasonId : (int) ($match->season_id ?? 0),
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

    private function pictureServer(int $databaseMode): string
    {
        $componentParams = ComponentHelper::getParams('com_sportsmanagement');
        $server = $databaseMode > 0
            ? trim((string) $componentParams->get('cfg_which_database_server', ''))
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
            $server = (string) Uri::root();
        }

        return rtrim($server, '/') . '/';
    }

    private function mediaUrl(string $path, string $pictureServer): string
    {
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

    /** @return array<int,int> */
    private function ids(mixed $value): array
    {
        $values = is_array($value) ? $value : [$value];
        $ids = [];

        foreach ($values as $candidate) {
            if (!is_scalar($candidate)) {
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
                }
            }
        }

        return array_values($ids);
    }

    private function database(int $databaseMode, DatabaseInterface $fallbackDatabase): DatabaseInterface
    {
        return SportsManagementDatabaseResolver::resolve(
            $fallbackDatabase,
            $databaseMode
        );
    }
}
