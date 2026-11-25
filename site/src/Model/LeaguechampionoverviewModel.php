<?php
/**
 * Native Joomla 5/6 league champion overview model.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\Database\ParameterType;

final class LeaguechampionoverviewModel extends SportsManagementProjectModel
{
    public static array $rankingalltimenotes = [];
    public static array $rankingalltimewarnings = [];
    public static array $rankingalltimetips = [];

    protected array $_params = ['ranking_order' => 'points'];
    protected array $_criteria = [];
    public bool $debug_info = false;

    /**
     * Resolve the selected league from the frontend request or derive it from the active nontrashed project when no explicit league is supplied.
     * @return int League identifier or zero when no context exists.
     */
    public function getLeagueId(): int
    {
        $input = $this->siteApplication()->getInput();
        $leagueId = max(0, $input->getInt('l', 0));

        if ($leagueId > 0) {
            return $leagueId;
        }

        $projectId = $this->getProjectId();
        if ($projectId <= 0) {
            return 0;
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select($db->quoteName('league_id'))
            ->from($db->quoteName('#__sportsmanagement_project'))
            ->where($db->quoteName('id') . ' = :projectId')
            ->where($db->quoteName('published') . ' <> -2')
            ->bind(':projectId', $projectId, ParameterType::INTEGER);
        $db->setQuery($query, 0, 1);

        return (int) $db->loadResult();
    }

    /**
     * Load nontrashed projects in the selected league with season, league and routing metadata. Optionally restrict the query by the league-champion setting and order the projects by descending season and identifier.
     * @param mixed $use_leaguechampion Optional champion-overview filter; zero disables it.
     * @return array<int,object> Project records.
     */
    public function getAllProjectNames($use_leaguechampion = 0): array
    {
        $leagueId = $this->getLeagueId();
        if ($leagueId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                'p.*',
                $db->quoteName('s.name', 'seasonname'),
                $db->quoteName('l.name', 'league_name'),
                $db->quoteName('l.champions_complete'),
                "CONCAT_WS(':', p.id, p.alias) AS project_slug",
            ])
            ->from($db->quoteName('#__sportsmanagement_project', 'p'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season', 's') . ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('p.season_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_league', 'l') . ' ON ' . $db->quoteName('l.id') . ' = ' . $db->quoteName('p.league_id'))
            ->where($db->quoteName('p.league_id') . ' = :leagueId')
            ->where($db->quoteName('p.published') . ' <> -2')
            ->bind(':leagueId', $leagueId, ParameterType::INTEGER)
            ->order([
                $db->quoteName('s.name') . ' DESC',
                $db->quoteName('p.id') . ' DESC',
            ]);

        $useLeagueChampion = (int) $use_leaguechampion;
        if ($useLeagueChampion !== 0) {
            $query
                ->where($db->quoteName('p.use_leaguechampion') . ' = :useLeagueChampion')
                ->bind(':useLeagueChampion', $useLeagueChampion, ParameterType::INTEGER);
        }

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    /**
     * Extract positive project identifiers from the league project list and update the legacy ranking progress message with the number of projects to process.
     * @param mixed $use_leaguechampion Optional champion-overview filter.
     * @return array<int,int> Project identifiers.
     */
    public function getAllProject($use_leaguechampion = 0): array
    {
        $ids = [];
        foreach ($this->getAllProjectNames($use_leaguechampion) as $project) {
            $id = (int) ($project->id ?? 0);
            if ($id > 0) {
                $ids[] = $id;
            }
        }

        self::$rankingalltimetips = [
            Text::_('Wir verarbeiten ' . count($ids) . ' Projekte/Saisons !'),
        ];

        return $ids;
    }

    /**
     * Read the teams explicitly marked as champions for the requested project, returning an empty list when no valid project identifier is supplied.
     * @param mixed $project_id Project identifier.
     * @return array<int,object> Explicit champion records.
     */
    public function getProjectWinner($project_id = 0): array
    {
        $projectId = max(0, (int) $project_id);
        if ($projectId <= 0) {
            return [];
        }

        return $this->loadProjectTeams($projectId, true);
    }

    /**
     * Count distinct matches in the requested project or across projects belonging to a specified league and season when aggregate counting is enabled.
     * @param mixed $project_id Project identifier.
     * @param bool $alloverleagueid Whether to count across the league and season.
     * @param mixed $league_id League identifier for aggregate counting.
     * @param mixed $season_id Season identifier for aggregate counting.
     * @return int Number of matches, or zero without a valid scope.
     */
    public function getProjectCountMatches($project_id = 0, $alloverleagueid = false, $league_id = 0, $season_id = 0): int
    {
        $projectId = max(0, (int) $project_id);
        $leagueId = max(0, (int) $league_id);
        $seasonId = max(0, (int) $season_id);

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select('COUNT(DISTINCT ' . $db->quoteName('m.id') . ')')
            ->from($db->quoteName('#__sportsmanagement_match', 'm'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_round', 'r') . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id'));

        if ($alloverleagueid && $leagueId > 0 && $seasonId > 0) {
            $query->join('INNER', $db->quoteName('#__sportsmanagement_project', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('r.project_id'))
                ->where($db->quoteName('p.league_id') . ' = :leagueId')
                ->where($db->quoteName('p.season_id') . ' = :seasonId')
                ->bind(':leagueId', $leagueId, ParameterType::INTEGER)
                ->bind(':seasonId', $seasonId, ParameterType::INTEGER);
        } elseif ($projectId > 0) {
            $query
                ->where($db->quoteName('r.project_id') . ' = :projectId')
                ->bind(':projectId', $projectId, ParameterType::INTEGER);
        } else {
            return 0;
        }

        $db->setQuery($query);

        return (int) $db->loadResult();
    }

    /**
     * Resolve the project providing context for the champion overview, preferring the active project and otherwise the first project enabled for the overview.
     * @return object|null Context project or null.
     */
    public function getOverviewContextProject(): ?object
    {
        $project = $this->getProject();
        if ($project) {
            return $project;
        }

        $projects = $this->getAllProjectNames(1);

        return $projects[0] ?? null;
    }

    /**
     * Assemble league champion records by season and project, count titles for each winning team and create placeholders for projects without a resolved winner. Prepare the aggregated team totals for the overview.
     * @return array Champion overview data and team title totals.
     */
    public function getOverviewData(): array
    {
        $projects = $this->getAllProjectNames(1);
        $projectIds = [];
        $leaguechampions = [];
        $leaguechampionsDetail = [];
        $teamseason = [];
        $leagueteamchampions = [];

        foreach ($projects as $project) {
            $projectId = (int) ($project->id ?? 0);
            if ($projectId <= 0) {
                continue;
            }

            $projectIds[] = $projectId;
            $seasonName = (string) ($project->seasonname ?? '');
            $winner = $this->resolveProjectWinner($projectId);

            if ($winner) {
                $champion = $this->buildChampionObject($project, $winner);
                $teamId = (int) $champion->teamid;

                if ($teamId > 0) {
                    $teamseason[$teamId]['season'][] = $seasonName;
                    $teamseason[$teamId]['title'] = (int) ($teamseason[$teamId]['title'] ?? 0) + 1;

                    if (!isset($leagueteamchampions[$teamId])) {
                        $leagueteamchampions[$teamId] = $champion;
                    }
                }

                $leaguechampions[$seasonName] = $champion;
                $leaguechampionsDetail[$seasonName][$projectId] = $champion;
                continue;
            }

            $placeholder = $this->buildPlaceholderObject($project);

            if (!array_key_exists($seasonName, $leaguechampions)) {
                $leaguechampions[$seasonName] = $placeholder;
            }
            $leaguechampionsDetail[$seasonName][$projectId] = $placeholder;
        }

        $teamstotal = [];
        foreach ($teamseason as $teamId => $value) {
            $teamstotal[] = [
                'team_id' => (int) $teamId,
                'total' => (int) ($value['title'] ?? 0),
            ];
        }

        usort(
            $teamstotal,
            static fn(array $a, array $b): int =>
                ($b['total'] <=> $a['total']) ?: ($a['team_id'] <=> $b['team_id'])
        );

        krsort($leaguechampions);
        krsort($leaguechampionsDetail);

        self::$rankingalltimetips = [
            Text::_('Wir verarbeiten ' . count($projectIds) . ' Projekte/Saisons !'),
        ];

        return [
            'projectids' => $projectIds,
            'projectnames' => $projects,
            'leaguechampions' => $leaguechampions,
            'leaguechampions_detail' => $leaguechampionsDetail,
            'teamseason' => $teamseason,
            'leagueteamchampions' => $leagueteamchampions,
            'teamstotal' => $teamstotal,
            'notes' => self::$rankingalltimenotes,
            'tips' => self::$rankingalltimetips,
            'warnings' => self::$rankingalltimewarnings,
        ];
    }

    /**
     * Return the configured ranking criteria, initialising the legacy points comparator when no criteria have been set.
     * @return array Ranking comparator identifiers.
     */
    public function _getRankingCriteria(): array
    {
        if (!$this->_criteria) {
            $this->_criteria[] = '_cmpPoints';
        }

        return $this->_criteria;
    }

    /**
     * Sort the supplied ranking by a recognised statistical or name column in the requested direction, preserving keys. Reassign sequential ranks on object rows unless the existing rank order was requested.
     * @param mixed $ranking Ranking rows passed by reference.
     * @param string $order Ranking column selector.
     * @param string $order_dir ASC or DESC sorting direction.
     * @return mixed Sorted ranking or unchanged input.
     */
    public function _sortRanking(&$ranking, $order = 'points', $order_dir = 'DESC')
    {
        if (!is_array($ranking) || !$ranking || $order === 'rank') {
            return $ranking;
        }

        $columns = [
            'played' => 'cnt_matches',
            'name' => '_name',
            'won' => 'cnt_won',
            'draw' => 'cnt_draw',
            'loss' => 'cnt_lost',
            'goalsp' => 'sum_team1_result',
            'goalsfor' => 'sum_team1_result',
            'goalsagainst' => 'sum_team2_result',
            'legsdiff' => 'diff_team_legs',
            'legsratio' => 'legsRatio',
            'diff' => 'diff_team_results',
            'points' => 'sum_points',
            'start' => 'start_points',
            'bonus' => 'bonus_points',
            'negpoints' => 'neg_points',
            'pointsratio' => 'pointsRatio',
        ];

        $field = $columns[(string) $order] ?? null;
        if ($field !== null) {
            $descending = strtoupper((string) $order_dir) !== 'ASC';
            uasort($ranking, static function ($a, $b) use ($field, $descending): int {
                $left = is_object($a) ? ($a->{$field} ?? null) : ($a[$field] ?? null);
                $right = is_object($b) ? ($b->{$field} ?? null) : ($b[$field] ?? null);
                $cmp = is_numeric($left) && is_numeric($right)
                    ? ((float) $left <=> (float) $right)
                    : strcasecmp((string) $left, (string) $right);

                return $descending ? -$cmp : $cmp;
            });
        }

        $rank = 1;
        foreach ($ranking as $row) {
            if (is_object($row)) {
                $row->rank = $rank++;
            }
        }

        return $ranking;
    }

    /**
     * Sort array or object rows by multiple columns in priority order, comparing numeric values numerically and other values case-insensitively while preserving array keys.
     * @param mixed $array Rows to sort.
     * @param mixed $cols Column names mapped to sort directions.
     * @return array Sorted rows or an empty array for invalid input.
     */
    public function array_msort($array, $cols): array
    {
        if (!is_array($array) || !$array || !is_array($cols) || !$cols) {
            return is_array($array) ? $array : [];
        }

        uasort($array, static function ($left, $right) use ($cols): int {
            foreach ($cols as $column => $direction) {
                $a = is_object($left) ? ($left->{$column} ?? null) : ($left[$column] ?? null);
                $b = is_object($right) ? ($right->{$column} ?? null) : ($right[$column] ?? null);
                $cmp = is_numeric($a) && is_numeric($b)
                    ? ((float) $a <=> (float) $b)
                    : strcasecmp((string) $a, (string) $b);

                if ($cmp !== 0) {
                    return $direction === SORT_DESC ? -$cmp : $cmp;
                }
            }

            return 0;
        });

        return $array;
    }

    /**
     * Resolve a project champion using the explicit champion marker first, then final table rank and available cached or stored final standings. Return null when no winner can be resolved.
     * @param int $projectId Project identifier.
     * @return object|null Resolved champion team or null.
     */
    private function resolveProjectWinner(int $projectId): ?object
    {
        $champions = $this->loadProjectTeams($projectId, true);
        if ($champions) {
            return $champions[0];
        }

        $teams = $this->loadProjectTeams($projectId, false);
        if (!$teams) {
            return null;
        }

        foreach ($teams as $team) {
            if ((int) ($team->finaltablerank ?? 0) === 1) {
                return $team;
            }
        }

        foreach ($teams as $team) {
            if ((int) ($team->cache_matches_finally ?? 0) > 0) {
                usort($teams, static fn(object $a, object $b): int =>
                    ((float) ($b->cache_points_finally ?? 0) <=> (float) ($a->cache_points_finally ?? 0))
                    ?: ((float) ($b->cache_diffgoals_finally ?? 0) <=> (float) ($a->cache_diffgoals_finally ?? 0))
                    ?: ((float) ($b->cache_homegoals_finally ?? 0) <=> (float) ($a->cache_homegoals_finally ?? 0))
                    ?: strcasecmp((string) ($a->_name ?? ''), (string) ($b->_name ?? ''))
                );
                return $teams[0] ?? null;
            }
        }

        foreach ($teams as $team) {
            if ((int) ($team->matches_finally ?? 0) > 0) {
                usort($teams, static fn(object $a, object $b): int =>
                    (((float) ($b->points_finally ?? 0) - (float) ($b->neg_points_finally ?? 0))
                        <=> ((float) ($a->points_finally ?? 0) - (float) ($a->neg_points_finally ?? 0)))
                    ?: ((float) ($b->diffgoals_finally ?? 0) <=> (float) ($a->diffgoals_finally ?? 0))
                    ?: ((float) ($b->homegoals_finally ?? 0) <=> (float) ($a->homegoals_finally ?? 0))
                    ?: strcasecmp((string) ($a->_name ?? ''), (string) ($b->_name ?? ''))
                );
                return $teams[0] ?? null;
            }
        }

        return null;
    }

    /**
     * Load project-team records with final ranking statistics, team routing information and club logos through the season-team relation. Optionally restrict the result to explicitly marked champions.
     * @param int $projectId Project identifier.
     * @param bool $championsOnly Whether to return only champion teams.
     * @return array<int,object> Project-team records.
     */
    private function loadProjectTeams(int $projectId, bool $championsOnly): array
    {
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('pt.id', '_ptid'),
                $db->quoteName('pt.is_in_score'),
                $db->quoteName('pt.division_id'),
                $db->quoteName('pt.finaltablerank'),
                $db->quoteName('pt.champion', 'rank'),
                $db->quoteName('pt.champion'),
                $db->quoteName('pt.cache_points_finally'),
                $db->quoteName('pt.cache_neg_points_finally'),
                $db->quoteName('pt.cache_matches_finally'),
                $db->quoteName('pt.cache_homegoals_finally'),
                $db->quoteName('pt.cache_guestgoals_finally'),
                $db->quoteName('pt.cache_diffgoals_finally'),
                $db->quoteName('pt.points_finally'),
                $db->quoteName('pt.neg_points_finally'),
                $db->quoteName('pt.matches_finally'),
                $db->quoteName('pt.homegoals_finally'),
                $db->quoteName('pt.guestgoals_finally'),
                $db->quoteName('pt.diffgoals_finally'),
                "CONCAT_WS(':', pt.id, t.alias) AS ptid_slug",
                $db->quoteName('t.name', '_name'),
                $db->quoteName('t.id', '_teamid'),
                $db->quoteName('t.club_id'),
                $db->quoteName('c.logo_big'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't') . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_club', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('t.club_id'))
            ->where($db->quoteName('pt.project_id') . ' = :projectId')
            ->where($db->quoteName('pt.is_in_score') . ' = 1')
            ->bind(':projectId', $projectId, ParameterType::INTEGER)
            ->order([
                $db->quoteName('pt.division_id') . ' ASC',
                $db->quoteName('pt.id') . ' ASC',
            ]);

        if ($championsOnly) {
            $query->where($db->quoteName('pt.champion') . ' = 1');
        }

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    /**
     * Convert a winning team and its project into the champion overview record, including team and club identifiers, logo, routing metadata and the project's match count.
     * @param object $project Project metadata.
     * @param object $winner Resolved champion team.
     * @return object Champion overview record.
     */
    private function buildChampionObject(object $project, object $winner): object
    {
        $object = new \stdClass();
        $object->teamname = (string) ($winner->_name ?? '');
        $object->ptid_slug = (string) ($winner->ptid_slug ?? '');
        $object->ptid = (int) ($winner->_ptid ?? 0);
        $object->teamid = (int) ($winner->_teamid ?? 0);
        $object->clubid = (int) ($winner->club_id ?? 0);
        $object->logo_big = (string) ($winner->logo_big ?? '');
        $object->project_id = (string) ($project->project_slug ?? $project->id ?? '');
        $object->published = (int) ($project->published ?? 0);
        $object->project_name = (string) ($project->name ?? '');
        $object->project_count_matches = $this->getProjectCountMatches((int) $project->id);

        return $object;
    }

    /**
     * Build an overview record for a project without a resolved champion, using the project information as the display label and counting matches in the associated league and season.
     * @param object $project Project metadata.
     * @return object Placeholder overview record.
     */
    private function buildPlaceholderObject(object $project): object
    {
        $object = new \stdClass();
        $object->teamname = (string) ($project->projectinfo ?? '');
        $object->ptid_slug = '';
        $object->ptid = 0;
        $object->teamid = 0;
        $object->clubid = 0;
        $object->logo_big = '';
        $object->published = (int) ($project->published ?? 0);
        $object->project_id = (string) ($project->project_slug ?? $project->id ?? '');
        $object->project_name = (string) ($project->name ?? '');
        $object->project_count_matches = $this->getProjectCountMatches(
            (int) ($project->id ?? 0),
            true,
            (int) ($project->league_id ?? 0),
            (int) ($project->season_id ?? 0)
        );

        return $object;
    }
}
