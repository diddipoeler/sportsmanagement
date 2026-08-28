<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Throwable;

/**
 * Native Joomla 5/6 data reader for the all-time ranking view.
 *
 * The legacy model still owns the all-time points and sorting rules. This
 * class replaces its direct database access so the selected SportsManagement
 * database is resolved consistently and connections are never disconnected
 * from frontend code.
 */
final class RankingalltimeModel extends SportsManagementProjectModel
{
    public function getProjectIds(int $useLeagueChampion = 0): array
    {
        $leagueId = $this->resolveLeagueId();
        if ($leagueId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__sportsmanagement_project'))
            ->where($db->quoteName('league_id') . ' = ' . $leagueId)
            ->where($db->quoteName('published') . ' != -2')
            ->order($db->quoteName('name') . ' ASC');

        if ($useLeagueChampion > 0) {
            $query->where($db->quoteName('use_leaguechampion') . ' = ' . $useLeagueChampion);
        }

        try {
            $db->setQuery($query);
            return array_values(array_map('intval', $db->loadColumn() ?: []));
        } catch (Throwable $e) {
            $this->reportDatabaseError($e);
            return [];
        }
    }

    public function getProjectNames(int $useLeagueChampion = 0): array
    {
        $leagueId = $this->resolveLeagueId();
        if ($leagueId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('p.id'),
                $db->quoteName('p.name'),
                $db->quoteName('s.name', 'seasonname'),
                $db->quoteName('p.projectinfo'),
                $db->quoteName('p.league_id'),
                $db->quoteName('p.season_id'),
                $db->quoteName('p.published'),
                "CONCAT_WS(':', p.id, p.alias) AS project_slug",
            ])
            ->from($db->quoteName('#__sportsmanagement_project', 'p'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season', 's') . ' ON ' . $db->quoteName('p.season_id') . ' = ' . $db->quoteName('s.id'))
            ->where($db->quoteName('p.league_id') . ' = ' . $leagueId)
            ->where($db->quoteName('p.published') . ' != -2')
            ->order($db->quoteName('s.name') . ' DESC');

        if ($useLeagueChampion > 0) {
            $query->where($db->quoteName('p.use_leaguechampion') . ' = ' . $useLeagueChampion);
        }

        try {
            $db->setQuery($query);
            return $db->loadObjectList() ?: [];
        } catch (Throwable $e) {
            $this->reportDatabaseError($e);
            return [];
        }
    }

    public function getAllTeams(array $projectIds): array
    {
        $projectIds = $this->normaliseIds($projectIds);
        if (!$projectIds) {
            return [];
        }

        $forceCache = (bool) ComponentHelper::getParams('com_sportsmanagement')->get('force_ranking_cache', 0);
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('pt.id', 'projectteamid'),
                $db->quoteName('pt.division_id'),
                $db->quoteName('pt.standard_playground'),
                $db->quoteName('pt.admin'),
                $db->quoteName('pt.start_points'),
                $db->quoteName('pt.use_finally'),
                $db->quoteName('pt.is_in_score'),
                $db->quoteName('pt.info'),
                $db->quoteName('st.team_id'),
                $db->quoteName('pt.checked_out'),
                $db->quoteName('pt.checked_out_time'),
                $db->quoteName('pt.picture'),
                $db->quoteName('pt.project_id'),
                $db->quoteName('t.id'),
                $db->quoteName('t.name'),
                $db->quoteName('t.short_name'),
                $db->quoteName('t.middle_name'),
                $db->quoteName('t.notes'),
                $db->quoteName('t.club_id'),
                $db->quoteName('c.unique_id'),
                $db->quoteName('c.email', 'club_email'),
                $db->quoteName('c.logo_small'),
                $db->quoteName('c.logo_middle'),
                $db->quoteName('c.logo_big'),
                $db->quoteName('c.country'),
                $db->quoteName('c.website'),
                $db->quoteName('d.name', 'division_name'),
                $db->quoteName('d.shortname', 'division_shortname'),
                $db->quoteName('d.parent_id', 'parent_division_id'),
                $db->quoteName('plg.name', 'playground_name'),
                $db->quoteName('plg.short_name', 'playground_short_name'),
                "CONCAT_WS(':', p.id, p.alias) AS project_slug",
                "CONCAT_WS(':', t.id, t.alias) AS team_slug",
                "CONCAT_WS(':', d.id, d.alias) AS division_slug",
                "CONCAT_WS(':', c.id, c.alias) AS club_slug",
            ])
            ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_team', 't') . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('t.id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_club', 'c') . ' ON ' . $db->quoteName('t.club_id') . ' = ' . $db->quoteName('c.id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_division', 'd') . ' ON ' . $db->quoteName('d.id') . ' = ' . $db->quoteName('pt.division_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_playground', 'plg') . ' ON ' . $db->quoteName('plg.id') . ' = ' . $db->quoteName('pt.standard_playground'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('pt.project_id'))
            ->where($db->quoteName('pt.project_id') . ' IN (' . implode(',', $projectIds) . ')');

        if ($forceCache) {
            $query->select([
                $db->quoteName('pt.cache_points_finally', 'points_finally'),
                $db->quoteName('pt.cache_neg_points_finally', 'neg_points_finally'),
                $db->quoteName('pt.cache_matches_finally', 'matches_finally'),
                $db->quoteName('pt.cache_won_finally', 'won_finally'),
                $db->quoteName('pt.cache_draws_finally', 'draws_finally'),
                $db->quoteName('pt.cache_lost_finally', 'lost_finally'),
                $db->quoteName('pt.cache_homegoals_finally', 'homegoals_finally'),
                $db->quoteName('pt.cache_guestgoals_finally', 'guestgoals_finally'),
                $db->quoteName('pt.cache_diffgoals_finally', 'diffgoals_finally'),
            ]);
        } else {
            $query->select([
                $db->quoteName('pt.points_finally'),
                $db->quoteName('pt.neg_points_finally'),
                $db->quoteName('pt.matches_finally'),
                $db->quoteName('pt.won_finally'),
                $db->quoteName('pt.draws_finally'),
                $db->quoteName('pt.lost_finally'),
                $db->quoteName('pt.homegoals_finally'),
                $db->quoteName('pt.guestgoals_finally'),
                $db->quoteName('pt.diffgoals_finally'),
            ]);
        }

        try {
            $db->setQuery($query);
            return $db->loadObjectList() ?: [];
        } catch (Throwable $e) {
            $this->reportDatabaseError($e);
            return [];
        }
    }

    /**
     * Build the accumulator objects expected by the legacy all-time ranking
     * calculation. Initialising all overtime/shootout counters also avoids
     * PHP 8 dynamic-property arithmetic warnings in that legacy code.
     */
    public function initialiseTeams(array $rows): array
    {
        $teams = [];
        $forceCache = (bool) ComponentHelper::getParams('com_sportsmanagement')->get('force_ranking_cache', 0);

        foreach ($rows as $row) {
            $teamId = (int) ($row->team_id ?? 0);
            if ($teamId <= 0) {
                continue;
            }

            if (!isset($teams[$teamId])) {
                $teams[$teamId] = $row;
                foreach ([
                    'cnt_matches', 'sum_points', 'neg_points',
                    'cnt_won_home', 'cnt_draw_home', 'cnt_lost_home',
                    'cnt_won_away', 'cnt_draw_away', 'cnt_lost_away',
                    'cnt_won', 'cnt_draw', 'cnt_lost',
                    'cnt_wot', 'cnt_wot_home', 'cnt_wot_away',
                    'cnt_lot', 'cnt_lot_home', 'cnt_lot_away',
                    'cnt_wso', 'cnt_wso_home', 'cnt_wso_away',
                    'cnt_lso', 'cnt_lso_home', 'cnt_lso_away',
                    'sum_team1_result', 'sum_team2_result', 'sum_away_for',
                    'diff_team_results', 'sum_team1_legs', 'sum_team2_legs',
                    'bonus_points', 'round', 'rank',
                ] as $property) {
                    $teams[$teamId]->{$property} = 0;
                }
                $teams[$teamId]->projectteam_slug = '';
                $teams[$teamId]->previousRanking = '';
            }

            if ((int) ($row->use_finally ?? 0) === 1 || $forceCache) {
                $teams[$teamId]->sum_points += (float) ($row->points_finally ?? 0);
                $teams[$teamId]->neg_points += (float) ($row->neg_points_finally ?? 0);
                $teams[$teamId]->cnt_matches += (int) ($row->matches_finally ?? 0);
                $teams[$teamId]->cnt_won += (int) ($row->won_finally ?? 0);
                $teams[$teamId]->cnt_draw += (int) ($row->draws_finally ?? 0);
                $teams[$teamId]->cnt_lost += (int) ($row->lost_finally ?? 0);
                $teams[$teamId]->sum_team1_result += (int) ($row->homegoals_finally ?? 0);
                $teams[$teamId]->sum_team2_result += (int) ($row->guestgoals_finally ?? 0);
                $teams[$teamId]->diff_team_results += (int) ($row->diffgoals_finally ?? 0);
            } else {
                foreach ([
                    'cnt_matches', 'sum_points', 'neg_points',
                    'cnt_won_home', 'cnt_draw_home', 'cnt_lost_home',
                    'cnt_won', 'cnt_draw', 'cnt_lost',
                    'sum_team1_result', 'sum_team2_result', 'sum_away_for',
                    'diff_team_results',
                ] as $property) {
                    $teams[$teamId]->{$property} = 0;
                }
            }
        }

        return $teams;
    }

    public function getAllMatches(array $projectIds): array
    {
        $projectIds = $this->normaliseIds($projectIds);
        if (!$projectIds) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('m.id'),
                $db->quoteName('m.projectteam1_id'),
                $db->quoteName('m.projectteam2_id'),
                $db->quoteName('m.team1_result', 'home_score'),
                $db->quoteName('m.team2_result', 'away_score'),
                $db->quoteName('m.team1_bonus', 'home_bonus'),
                $db->quoteName('m.team2_bonus', 'away_bonus'),
                $db->quoteName('m.team1_legs', 'l1'),
                $db->quoteName('m.team2_legs', 'l2'),
                $db->quoteName('m.match_result_type'),
                $db->quoteName('m.alt_decision', 'decision'),
                $db->quoteName('m.team1_result_decision', 'home_score_decision'),
                $db->quoteName('m.team2_result_decision', 'away_score_decision'),
                $db->quoteName('m.team1_result_ot', 'home_score_ot'),
                $db->quoteName('m.team2_result_ot', 'away_score_ot'),
                $db->quoteName('m.team1_result_so', 'home_score_so'),
                $db->quoteName('m.team2_result_so', 'away_score_so'),
                $db->quoteName('t1.id', 'team1_id'),
                $db->quoteName('t2.id', 'team2_id'),
                $db->quoteName('r.id', 'roundid'),
                $db->quoteName('m.team_won'),
                $db->quoteName('r.roundcode'),
            ])
            ->from($db->quoteName('#__sportsmanagement_match', 'm'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt1') . ' ON ' . $db->quoteName('m.projectteam1_id') . ' = ' . $db->quoteName('pt1.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st1') . ' ON ' . $db->quoteName('st1.id') . ' = ' . $db->quoteName('pt1.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't1') . ' ON ' . $db->quoteName('st1.team_id') . ' = ' . $db->quoteName('t1.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt2') . ' ON ' . $db->quoteName('m.projectteam2_id') . ' = ' . $db->quoteName('pt2.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st2') . ' ON ' . $db->quoteName('st2.id') . ' = ' . $db->quoteName('pt2.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't2') . ' ON ' . $db->quoteName('st2.team_id') . ' = ' . $db->quoteName('t2.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_round', 'r') . ' ON ' . $db->quoteName('m.round_id') . ' = ' . $db->quoteName('r.id'))
            ->where('((' . $db->quoteName('m.team1_result') . ' IS NOT NULL AND ' . $db->quoteName('m.team2_result') . ' IS NOT NULL) OR ' . $db->quoteName('m.alt_decision') . ' = 1)')
            ->where($db->quoteName('m.published') . ' = 1')
            ->where($db->quoteName('r.published') . ' = 1')
            ->where($db->quoteName('pt1.project_id') . ' IN (' . implode(',', $projectIds) . ')')
            ->where('(' . $db->quoteName('m.cancel') . ' IS NULL OR ' . $db->quoteName('m.cancel') . ' = 0)')
            ->where($db->quoteName('m.projectteam1_id') . ' > 0')
            ->where($db->quoteName('m.projectteam2_id') . ' > 0');

        try {
            $db->setQuery($query);
            return $db->loadObjectList() ?: [];
        } catch (Throwable $e) {
            $this->reportDatabaseError($e);
            return [];
        }
    }

    /** Preserve the legacy all-time color format, including a trailing ';'. */
    public function parseColors(string $configColors = ''): array
    {
        $trimmed = substr($configColors, 0, -1);
        $entries = trim($trimmed) !== '' ? explode(';', $trimmed) : [];
        $colors = [[
            'from' => '',
            'to' => '',
            'color' => '',
            'description' => '',
        ]];

        foreach ($entries as $index => $entry) {
            $parts = explode(',', $entry);
            if (count($parts) !== 4) {
                break;
            }
            $colors[$index] = [
                'from' => $parts[0],
                'to' => $parts[1],
                'color' => $parts[2],
                'description' => $parts[3],
            ];
        }

        return $colors;
    }

    private function resolveLeagueId(): int
    {
        $input = Factory::getApplication()->getInput();
        $leagueId = $input->getInt('l', 0);
        if ($leagueId > 0) {
            return $leagueId;
        }

        $projectId = $input->getInt('p', 0);
        if ($projectId <= 0) {
            return 0;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('league_id'))
            ->from($db->quoteName('#__sportsmanagement_project'))
            ->where($db->quoteName('id') . ' = ' . $projectId)
            ->where($db->quoteName('published') . ' != -2');

        try {
            $db->setQuery($query, 0, 1);
            return (int) ($db->loadResult() ?: 0);
        } catch (Throwable $e) {
            $this->reportDatabaseError($e);
            return 0;
        }
    }

    private function normaliseIds(array $ids): array
    {
        return array_values(array_unique(array_filter(array_map('intval', $ids), static fn (int $id): bool => $id > 0)));
    }

    private function reportDatabaseError(Throwable $e): void
    {
        Factory::getApplication()->enqueueMessage(
            Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()),
            'error'
        );
    }
}
