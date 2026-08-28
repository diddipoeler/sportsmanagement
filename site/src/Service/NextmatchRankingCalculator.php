<?php
namespace Diddipoeler\Component\SportsManagement\Site\Service;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;

/**
 * Calculate the project table needed by the next-match view without loading
 * the historical JSMRanking/sportsmanagementModelProject stack.
 *
 * This intentionally mirrors the legacy Nextmatch call to JSMRanking:
 * - matches count only when count_result is enabled
 * - current-round and division limits are honoured
 * - project point rules, alternative decisions, bonus/start/final values are
 *   retained
 * - ranking_order / ranking_sort_order drive rank assignment
 *
 * @since  Joomla 5/6 migration
 */
final class NextmatchRankingCalculator
{
    /** @return array<int, object> keyed by project-team id */
    public static function calculate(
        DatabaseInterface $db,
        object $project,
        array $rankingConfig,
        int $currentRoundId = 0,
        int $divisionId = 0
    ): array {
        $projectId = (int) ($project->id ?? 0);
        if ($projectId <= 0) {
            return [];
        }

        $divisionIds = self::divisionIds($db, $projectId, $divisionId);
        $teams = self::loadTeams($db, $projectId, $divisionId, $divisionIds);
        if (!$teams) {
            return [];
        }

        $initialTeams = self::cloneTeams($teams);
        $roundCode = self::roundCode($db, $projectId, $currentRoundId);
        $matches = self::loadMatches($db, $projectId, $roundCode, $divisionId);
        self::collect($teams, $matches, $project);

        $useFinalTableRank = !$matches;
        foreach ($initialTeams as $team) {
            if ((float) ($team->_points_finally ?? 0) != 0.0) {
                $useFinalTableRank = false;
                break;
            }
        }

        return self::buildRanking(
            $teams,
            $initialTeams,
            $matches,
            $project,
            $rankingConfig,
            $useFinalTableRank
        );
    }

    /** @return array<int, int> */
    private static function divisionIds(
        DatabaseInterface $db,
        int $projectId,
        int $divisionId
    ): array {
        if ($divisionId <= 0) {
            return [];
        }

        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__sportsmanagement_division'))
            ->where($db->quoteName('project_id') . ' = ' . $projectId)
            ->where($db->quoteName('published') . ' = 1')
            ->where(
                '(' . $db->quoteName('id') . ' = ' . $divisionId
                . ' OR ' . $db->quoteName('parent_id') . ' = ' . $divisionId . ')'
            );
        $db->setQuery($query);
        $ids = array_map('intval', $db->loadColumn() ?: []);
        $ids[$divisionId] = $divisionId;

        return array_values(array_unique(array_filter($ids)));
    }

    /** @return array<int, object> */
    private static function loadTeams(
        DatabaseInterface $db,
        int $projectId,
        int $divisionId,
        array $divisionIds
    ): array {
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('pt.id', 'ptid'),
                $db->quoteName('pt.is_in_score'),
                $db->quoteName('pt.start_points'),
                $db->quoteName('pt.division_id'),
                $db->quoteName('pt.neg_points_finally'),
                $db->quoteName('pt.use_finally'),
                $db->quoteName('pt.points_finally'),
                $db->quoteName('pt.matches_finally'),
                $db->quoteName('pt.won_finally'),
                $db->quoteName('pt.draws_finally'),
                $db->quoteName('pt.lost_finally'),
                $db->quoteName('pt.homegoals_finally'),
                $db->quoteName('pt.guestgoals_finally'),
                $db->quoteName('pt.diffgoals_finally'),
                $db->quoteName('pt.penalty_points'),
                $db->quoteName('pt.finaltablerank'),
                $db->quoteName('t.name'),
                $db->quoteName('t.id', 'teamid'),
                $db->quoteName('st.teamname'),
                $db->quoteName('st.season_teamname'),
                $db->quoteName('c.name', 'clubname'),
                "CONCAT_WS(':', pt.id, t.alias) AS ptid_slug",
            ])
            ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't') . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_club', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('t.club_id'))
            ->where($db->quoteName('pt.project_id') . ' = ' . $projectId)
            ->where($db->quoteName('pt.is_in_score') . ' = 1');

        if ($divisionId > 0) {
            $divisionSql = $divisionIds ?: [$divisionId];
            $ids = implode(',', array_map('intval', $divisionSql));
            $query->where(
                '(' . $db->quoteName('pt.division_id') . ' IN (' . $ids . ')'
                . ' OR EXISTS ('
                . 'SELECT 1 FROM ' . $db->quoteName('#__sportsmanagement_match', 'md')
                . ' WHERE (' . $db->quoteName('md.projectteam1_id') . ' = ' . $db->quoteName('pt.id')
                . ' OR ' . $db->quoteName('md.projectteam2_id') . ' = ' . $db->quoteName('pt.id') . ')'
                . ' AND ' . $db->quoteName('md.division_id') . ' = ' . $divisionId
                . '))'
            );
        }

        $db->setQuery($query);
        $rows = $db->loadObjectList() ?: [];

        if (!$rows && $divisionId > 0) {
            $rows = self::loadFinalDivisionTeams($db, $projectId, $divisionId);
        }

        $teams = [];
        foreach ($rows as $row) {
            $team = self::newTeam($row, $divisionId);
            $teams[(int) $team->_ptid] = $team;
        }

        return $teams;
    }

    /** @return array<int, object> */
    private static function loadFinalDivisionTeams(
        DatabaseInterface $db,
        int $projectId,
        int $divisionId
    ): array {
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('pt.id', 'ptid'),
                $db->quoteName('pt.is_in_score'),
                $db->quoteName('pt.neg_points_finally'),
                $db->quoteName('t.name'),
                $db->quoteName('t.id', 'teamid'),
                $db->quoteName('st.teamname'),
                $db->quoteName('st.season_teamname'),
                $db->quoteName('c.name', 'clubname'),
                $db->quoteName('ptd.start_points'),
                $db->quoteName('ptd.division_id'),
                $db->quoteName('ptd.use_finally'),
                $db->quoteName('ptd.points_finally'),
                $db->quoteName('ptd.matches_finally'),
                $db->quoteName('ptd.won_finally'),
                $db->quoteName('ptd.draws_finally'),
                $db->quoteName('ptd.lost_finally'),
                $db->quoteName('ptd.homegoals_finally'),
                $db->quoteName('ptd.guestgoals_finally'),
                $db->quoteName('ptd.diffgoals_finally'),
                $db->quoteName('ptd.penalty_points'),
                $db->quoteName('ptd.finaltablerank'),
                "CONCAT_WS(':', pt.id, t.alias) AS ptid_slug",
            ])
            ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team_division', 'ptd')
                . ' ON ' . $db->quoteName('ptd.team_id') . ' = ' . $db->quoteName('pt.id')
                . ' AND ' . $db->quoteName('ptd.project_id') . ' = ' . $db->quoteName('pt.project_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't') . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_club', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('t.club_id'))
            ->where($db->quoteName('pt.project_id') . ' = ' . $projectId)
            ->where($db->quoteName('ptd.division_id') . ' = ' . $divisionId)
            ->where($db->quoteName('ptd.is_in_score') . ' = 1')
            ->where($db->quoteName('ptd.use_finally') . ' = 1');
        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    private static function newTeam(object $row, int $divisionId): object
    {
        $team = (object) [
            '_ptid' => (int) ($row->ptid ?? 0),
            '_teamid' => (int) ($row->teamid ?? 0),
            '_divisionid' => (int) (($row->division_id ?? 0) ?: $divisionId),
            '_startpoints' => (float) ($row->start_points ?? 0),
            '_name' => (string) ($row->name ?? ''),
            '_is_in_score' => (int) ($row->is_in_score ?? 1),
            '_use_finally' => (int) ($row->use_finally ?? 0),
            '_finaltablerank' => (int) ($row->finaltablerank ?? 0),
            '_points_finally' => (float) ($row->points_finally ?? 0),
            '_neg_points_finally' => (float) ($row->neg_points_finally ?? 0),
            'ptid_slug' => (string) ($row->ptid_slug ?? ''),
            'teamname' => (string) ($row->teamname ?? ''),
            'season_teamname' => (string) ($row->season_teamname ?? ''),
            'clubname' => (string) ($row->clubname ?? ''),
            'penalty_points' => (float) ($row->penalty_points ?? 0),
            'cnt_matches' => 0,
            'cnt_won' => 0,
            'cnt_draw' => 0,
            'cnt_lost' => 0,
            'cnt_won_home' => 0,
            'cnt_draw_home' => 0,
            'cnt_lost_home' => 0,
            'cnt_won_away' => 0,
            'cnt_draw_away' => 0,
            'cnt_lost_away' => 0,
            'cnt_wot' => 0,
            'cnt_wso' => 0,
            'cnt_lot' => 0,
            'cnt_lso' => 0,
            'cnt_wot_home' => 0,
            'cnt_wso_home' => 0,
            'cnt_lot_home' => 0,
            'cnt_lso_home' => 0,
            'cnt_wot_away' => 0,
            'cnt_wso_away' => 0,
            'cnt_lot_away' => 0,
            'cnt_lso_away' => 0,
            'sum_points' => 0.0,
            'neg_points' => (float) ($row->neg_points_finally ?? 0),
            'bonus_points' => 0.0,
            'sum_team1_result' => 0.0,
            'sum_team2_result' => 0.0,
            'sum_away_for' => 0.0,
            'sum_team1_legs' => 0.0,
            'sum_team2_legs' => 0.0,
            'sum_team1_matchpoint' => 0.0,
            'sum_team2_matchpoint' => 0.0,
            'sum_team1_sets' => 0.0,
            'sum_team2_sets' => 0.0,
            'sum_team1_games' => 0.0,
            'sum_team2_games' => 0.0,
            'diff_team_results' => 0.0,
            'diff_team_legs' => 0.0,
            'diff_team_matchpoint' => 0.0,
            'diff_team_sets' => 0.0,
            'diff_team_games' => 0.0,
            'scorefor' => 0.0,
            'scoreagainst' => 0.0,
            'goalsfor' => 0.0,
            'goalsagainst' => 0.0,
            'winpoints' => 0.0,
            'rank' => 0,
        ];

        if ($team->_use_finally) {
            $team->sum_points = (float) ($row->points_finally ?? 0);
            $team->neg_points = (float) ($row->neg_points_finally ?? 0);
            $team->cnt_matches = (int) ($row->matches_finally ?? 0);
            $team->cnt_won = (int) ($row->won_finally ?? 0);
            $team->cnt_draw = (int) ($row->draws_finally ?? 0);
            $team->cnt_lost = (int) ($row->lost_finally ?? 0);
            $team->sum_team1_result = (float) ($row->homegoals_finally ?? 0);
            $team->sum_team2_result = (float) ($row->guestgoals_finally ?? 0);
            $team->diff_team_results = (float) ($row->diffgoals_finally ?? 0);
            $team->scorefor = $team->goalsfor = $team->sum_team1_result;
            $team->scoreagainst = $team->goalsagainst = $team->sum_team2_result;
        }

        return $team;
    }

    private static function roundCode(DatabaseInterface $db, int $projectId, int $roundId): int
    {
        if ($roundId <= 0) {
            return PHP_INT_MAX;
        }

        $query = $db->getQuery(true)
            ->select($db->quoteName('roundcode'))
            ->from($db->quoteName('#__sportsmanagement_round'))
            ->where($db->quoteName('id') . ' = ' . $roundId)
            ->where($db->quoteName('project_id') . ' = ' . $projectId);
        $db->setQuery($query, 0, 1);
        $roundCode = $db->loadResult();

        return $roundCode !== null ? (int) $roundCode : PHP_INT_MAX;
    }

    /** @return array<int, object> */
    private static function loadMatches(
        DatabaseInterface $db,
        int $projectId,
        int $roundCode,
        int $divisionId
    ): array {
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
                $db->quoteName('m.team1_single_matchpoint', 'mp1'),
                $db->quoteName('m.team2_single_matchpoint', 'mp2'),
                $db->quoteName('m.team1_single_sets', 'se1'),
                $db->quoteName('m.team2_single_sets', 'se2'),
                $db->quoteName('m.team1_single_games', 'ga1'),
                $db->quoteName('m.team2_single_games', 'ga2'),
                $db->quoteName('m.match_result_type'),
                $db->quoteName('m.alt_decision', 'decision'),
                $db->quoteName('m.team1_result_decision', 'home_score_decision'),
                $db->quoteName('m.team2_result_decision', 'away_score_decision'),
                $db->quoteName('m.team1_result_ot', 'home_score_ot'),
                $db->quoteName('m.team2_result_ot', 'away_score_ot'),
                $db->quoteName('m.team1_result_so', 'home_score_so'),
                $db->quoteName('m.team2_result_so', 'away_score_so'),
                $db->quoteName('m.team_won'),
                $db->quoteName('r.id', 'roundid'),
                $db->quoteName('r.roundcode'),
            ])
            ->from($db->quoteName('#__sportsmanagement_match', 'm'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt1') . ' ON ' . $db->quoteName('pt1.id') . ' = ' . $db->quoteName('m.projectteam1_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_round', 'r') . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id'))
            ->where($db->quoteName('pt1.project_id') . ' = ' . $projectId)
            ->where($db->quoteName('m.published') . ' = 1')
            ->where($db->quoteName('r.published') . ' = 1')
            ->where($db->quoteName('m.count_result') . ' = 1')
            ->where('(' . $db->quoteName('m.cancel') . ' IS NULL OR ' . $db->quoteName('m.cancel') . ' = 0)')
            ->where($db->quoteName('m.projectteam1_id') . ' > 0')
            ->where($db->quoteName('m.projectteam2_id') . ' > 0')
            ->where('((' . $db->quoteName('m.team1_result') . ' IS NOT NULL AND ' . $db->quoteName('m.team2_result') . ' IS NOT NULL) OR ' . $db->quoteName('m.alt_decision') . ' = 1)')
            ->where($db->quoteName('r.roundcode') . ' <= ' . $roundCode);

        if ($divisionId > 0) {
            $query->where($db->quoteName('m.division_id') . ' = ' . $divisionId);
        }

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    /** @param array<int, object> $teams @param array<int, object> $matches */
    private static function collect(array &$teams, array $matches, object $project): void
    {
        foreach ($matches as $match) {
            $homeId = (int) ($match->projectteam1_id ?? 0);
            $awayId = (int) ($match->projectteam2_id ?? 0);
            if (!isset($teams[$homeId], $teams[$awayId])) {
                continue;
            }

            $home = $teams[$homeId];
            $away = $teams[$awayId];
            $decision = (int) ($match->decision ?? 0);
            $homeScore = (float) ($decision === 0 ? ($match->home_score ?? 0) : ($match->home_score_decision ?? 0));
            $awayScore = (float) ($decision === 0 ? ($match->away_score ?? 0) : ($match->away_score_decision ?? 0));
            $leg1 = $decision === 0 ? (float) ($match->l1 ?? 0) : 0.0;
            $leg2 = $decision === 0 ? (float) ($match->l2 ?? 0) : 0.0;
            $mp1 = $decision === 0 ? (float) ($match->mp1 ?? 0) : 0.0;
            $mp2 = $decision === 0 ? (float) ($match->mp2 ?? 0) : 0.0;
            $se1 = $decision === 0 ? (float) ($match->se1 ?? 0) : 0.0;
            $se2 = $decision === 0 ? (float) ($match->se2 ?? 0) : 0.0;
            $ga1 = $decision === 0 ? (float) ($match->ga1 ?? 0) : 0.0;
            $ga2 = $decision === 0 ? (float) ($match->ga2 ?? 0) : 0.0;

            $home->cnt_matches++;
            $away->cnt_matches++;

            $resultType = !empty($project->allow_add_time) ? (int) ($match->match_result_type ?? 0) : 0;
            [$winPoints, $drawPoints, $lossPoints] = self::pointRule($project, $resultType);
            $home->winpoints = $away->winpoints = $winPoints;

            if ($decision !== 1) {
                self::collectSportingResult(
                    $home,
                    $away,
                    $homeScore,
                    $awayScore,
                    $resultType,
                    (float) ($match->home_score_ot ?? 0),
                    (float) ($match->away_score_ot ?? 0),
                    (float) ($match->home_score_so ?? 0),
                    (float) ($match->away_score_so ?? 0),
                    $winPoints,
                    $drawPoints,
                    $lossPoints
                );
            } else {
                self::collectDecisionResult(
                    $home,
                    $away,
                    (int) ($match->team_won ?? 0),
                    $winPoints,
                    $lossPoints
                );
            }

            $homeBonus = (float) ($match->home_bonus ?? 0);
            $awayBonus = (float) ($match->away_bonus ?? 0);
            $home->sum_points += $homeBonus;
            $home->bonus_points += $homeBonus;
            $away->sum_points += $awayBonus;
            $away->bonus_points += $awayBonus;

            self::collectScore($home, $homeScore, $awayScore, $leg1, $leg2, $mp1, $mp2, $se1, $se2, $ga1, $ga2);
            self::collectScore($away, $awayScore, $homeScore, $leg2, $leg1, $mp2, $mp1, $se2, $se1, $ga2, $ga1);
            $away->sum_away_for += $awayScore;
        }
    }

    private static function collectSportingResult(
        object $home,
        object $away,
        float $homeScore,
        float $awayScore,
        int $resultType,
        float $homeOt,
        float $awayOt,
        float $homeSo,
        float $awaySo,
        float $winPoints,
        float $drawPoints,
        float $lossPoints
    ): void {
        if ($homeScore > $awayScore) {
            if ($resultType === 0) {
                $home->cnt_won++;
                $home->cnt_won_home++;
                $away->cnt_lost++;
                $away->cnt_lost_away++;
            } elseif ($resultType === 1) {
                $home->cnt_wot++;
                $home->cnt_wot_home++;
                $away->cnt_lot++;
                $away->cnt_lot_away++;
            } else {
                $home->cnt_wso++;
                $home->cnt_wso_home++;
                $away->cnt_lso++;
                $away->cnt_lso_away++;
                $away->cnt_lot++;
                $away->cnt_lot_away++;
            }

            $home->sum_points += $winPoints;
            $away->sum_points += $lossPoints;
            $home->neg_points += $lossPoints;
            $away->neg_points += $winPoints;

            return;
        }

        if ($homeScore < $awayScore) {
            if ($resultType === 0) {
                $home->cnt_lost++;
                $home->cnt_lost_home++;
                $away->cnt_won++;
                $away->cnt_won_away++;
            } elseif ($resultType === 1) {
                $home->cnt_lot++;
                $home->cnt_lot_home++;
                $away->cnt_wot++;
                $away->cnt_wot_away++;
            } else {
                $home->cnt_lso++;
                $home->cnt_lso_home++;
                $home->cnt_lot++;
                $home->cnt_lot_home++;
                $away->cnt_wso++;
                $away->cnt_wso_away++;
            }

            $home->sum_points += $lossPoints;
            $away->sum_points += $winPoints;
            $home->neg_points += $winPoints;
            $away->neg_points += $lossPoints;

            return;
        }

        if ($resultType === 0) {
            $home->cnt_draw++;
            $home->cnt_draw_home++;
            $away->cnt_draw++;
            $away->cnt_draw_away++;
        } elseif ($resultType === 1) {
            if ($homeOt > $awayOt) {
                $home->cnt_wot++;
                $home->cnt_wot_home++;
                $away->cnt_lot++;
                $away->cnt_lot_away++;
            } elseif ($homeOt < $awayOt) {
                $away->cnt_wot++;
                $away->cnt_wot_home++;
                $home->cnt_lot++;
                $home->cnt_lot_away++;
            }
        } else {
            if ($homeSo > $awaySo) {
                $home->cnt_wso++;
                $home->cnt_wso_home++;
                $away->cnt_lso++;
                $away->cnt_lso_away++;
            } elseif ($homeSo < $awaySo) {
                $away->cnt_wso++;
                $away->cnt_wso_home++;
                $home->cnt_lso++;
                $home->cnt_lso_away++;
            }
        }

        $home->sum_points += $drawPoints;
        $away->sum_points += $drawPoints;
        $home->neg_points += $winPoints - $drawPoints;
        $away->neg_points += $winPoints - $drawPoints;
    }

    private static function collectDecisionResult(
        object $home,
        object $away,
        int $teamWon,
        float $winPoints,
        float $lossPoints
    ): void {
        $home->neg_points += $lossPoints;
        $away->neg_points += $lossPoints;

        switch ($teamWon) {
            case 1:
                $home->cnt_won++;
                $away->cnt_lost++;
                $home->sum_points += $winPoints;
                $away->cnt_lost_home++;
                break;
            case 2:
                $away->cnt_won++;
                $home->cnt_lost++;
                $away->sum_points += $winPoints;
                $home->cnt_lost_home++;
                break;
            case 3:
                $home->cnt_lost++;
                $away->cnt_lost++;
                $home->cnt_lost_home++;
                $away->cnt_lost_home++;
                break;
            case 4:
                $home->cnt_won++;
                $away->cnt_won++;
                $home->sum_points += $winPoints;
                $away->sum_points += $winPoints;
                break;
            default:
                $home->cnt_lost++;
                $away->cnt_lost++;
                break;
        }
    }

    private static function collectScore(
        object $team,
        float $for,
        float $against,
        float $legsFor,
        float $legsAgainst,
        float $matchpointsFor,
        float $matchpointsAgainst,
        float $setsFor,
        float $setsAgainst,
        float $gamesFor,
        float $gamesAgainst
    ): void {
        $team->sum_team1_result += $for;
        $team->sum_team2_result += $against;
        $team->scorefor += $for;
        $team->scoreagainst += $against;
        $team->goalsfor += $for;
        $team->goalsagainst += $against;
        $team->diff_team_results = $team->sum_team1_result - $team->sum_team2_result;
        $team->sum_team1_legs += $legsFor;
        $team->sum_team2_legs += $legsAgainst;
        $team->diff_team_legs = $team->sum_team1_legs - $team->sum_team2_legs;
        $team->sum_team1_matchpoint += $matchpointsFor;
        $team->sum_team2_matchpoint += $matchpointsAgainst;
        $team->diff_team_matchpoint = $team->sum_team1_matchpoint - $team->sum_team2_matchpoint;
        $team->sum_team1_sets += $setsFor;
        $team->sum_team2_sets += $setsAgainst;
        $team->diff_team_sets = $team->sum_team1_sets - $team->sum_team2_sets;
        $team->sum_team1_games += $gamesFor;
        $team->sum_team2_games += $gamesAgainst;
        $team->diff_team_games = $team->sum_team1_games - $team->sum_team2_games;
        $team->points = $team->sum_points;
    }

    /** @return array{0: float, 1: float, 2: float} */
    private static function pointRule(object $project, int $resultType): array
    {
        $property = match ($resultType) {
            1 => 'points_after_add_time',
            2 => 'points_after_penalty',
            default => 'points_after_regular_time',
        };
        $values = array_map('trim', explode(',', (string) ($project->{$property} ?? '')));
        $defaults = [3.0, 1.0, 0.0];

        foreach ($defaults as $index => $default) {
            if (!isset($values[$index]) || $values[$index] === '' || !is_numeric($values[$index])) {
                $values[$index] = $default;
            }
        }

        return [(float) $values[0], (float) $values[1], (float) $values[2]];
    }

    /** @return array<int, object> */
    private static function buildRanking(
        array $teams,
        array $initialTeams,
        array $matches,
        object $project,
        array $config,
        bool $useFinalTableRank
    ): array {
        $criteria = $useFinalTableRank
            ? ['FINALTABLERANK']
            : array_values(array_filter(array_map(
                static fn (string $value): string => strtoupper(trim($value)),
                explode(',', (string) ($config['ranking_order'] ?? 'POINTS, PLAYEDASC, DIFF, FOR'))
            )));
        if (!$criteria) {
            $criteria = ['POINTS'];
        }

        $orders = array_values(array_map(
            static fn (string $value): string => strtoupper(trim($value)) === 'ASC' ? 'ASC' : 'DESC',
            explode(',', (string) ($config['ranking_sort_order'] ?? 'DESC, ASC, DESC, DESC'))
        ));

        $groups = [1 => $teams];

        foreach ($criteria as $criterionIndex => $criterion) {
            if (!self::supportsCriterion($criterion)) {
                continue;
            }

            $newGroups = [];
            foreach ($groups as $rank => $groupTeams) {
                $h2h = str_starts_with($criterion, 'H2H')
                    ? self::headToHead($groupTeams, $initialTeams, $matches, $project)
                    : [];
                $order = $orders[$criterionIndex] ?? 'DESC';

                uasort(
                    $groupTeams,
                    static fn (object $a, object $b): int => self::compare($criterion, $order, $a, $b, $h2h)
                );

                $newRank = (int) $rank;
                $currentRank = (int) $rank;
                $previous = null;

                foreach ($groupTeams as $projectTeamId => $team) {
                    if ($previous === null || self::compare($criterion, $order, $team, $previous, $h2h) !== 0) {
                        $newGroups[$newRank] = [$projectTeamId => $team];
                        $currentRank = $newRank;
                    } else {
                        $newGroups[$currentRank][$projectTeamId] = $team;
                    }

                    $previous = $team;
                    $newRank++;
                }
            }

            $groups = $newGroups ?: $groups;
        }

        $result = [];
        foreach ($groups as $rank => $groupTeams) {
            uasort(
                $groupTeams,
                static fn (object $a, object $b): int => strcasecmp((string) ($a->_name ?? ''), (string) ($b->_name ?? ''))
            );

            foreach ($groupTeams as $projectTeamId => $team) {
                $team->rank = (int) $rank;
                $result[(int) $projectTeamId] = $team;
            }
        }

        return $result;
    }

    private static function supportsCriterion(string $criterion): bool
    {
        return in_array($criterion, [
            'FINALTABLERANK', 'SCOREFOR', 'SCOREAGAINST', 'GOALSFOR', 'GOALSAGAINST',
            'POINTS', 'PENALTYPOINTS', 'BONUS', 'AGAINST', 'SCOREAVG', 'SCOREPCT',
            'WINPCT', 'GB', 'H2H', 'H2H_DIFF', 'DIFF', 'H2H_FOR', 'FOR',
            'H2H_AWAY', 'AWAYFOR', 'LEGS_DIFF', 'LEGS_RATIO', 'LEGS_WIN', 'WINS',
            'PLAYEDASC', 'PLAYED', 'POINTS_RATIO', 'WOT', 'WSO',
        ], true);
    }

    /** @param array<int, object> $h2h */
    private static function compare(
        string $criterion,
        string $order,
        object $a,
        object $b,
        array $h2h = []
    ): int {
        $aH2h = $h2h[(int) ($a->_ptid ?? 0)] ?? self::blankTeam();
        $bH2h = $h2h[(int) ($b->_ptid ?? 0)] ?? self::blankTeam();

        return match ($criterion) {
            'FINALTABLERANK' => self::numericAsc($a->_finaltablerank ?? 0, $b->_finaltablerank ?? 0),
            'SCOREFOR' => self::numericDesc($a->sum_team1_result ?? 0, $b->sum_team1_result ?? 0),
            'SCOREAGAINST' => self::numericDesc($a->sum_team2_result ?? 0, $b->sum_team2_result ?? 0),
            'GOALSFOR' => self::numericByOrder($a->goalsfor ?? 0, $b->goalsfor ?? 0, $order),
            'GOALSAGAINST' => self::numericByOrder($a->goalsagainst ?? 0, $b->goalsagainst ?? 0, $order),
            'POINTS' => self::numericByOrder(self::points($a), self::points($b), $order),
            'PENALTYPOINTS' => self::numericAsc($a->penalty_points ?? 0, $b->penalty_points ?? 0),
            'BONUS' => self::numericDesc($a->bonus_points ?? 0, $b->bonus_points ?? 0),
            'AGAINST' => self::numericAsc($a->sum_team2_result ?? 0, $b->sum_team2_result ?? 0),
            'SCOREAVG', 'SCOREPCT' => self::numericDesc(self::scoreRatio($a), self::scoreRatio($b)),
            'WINPCT' => self::numericDesc(self::winPct($a), self::winPct($b)),
            'GB' => self::numericDesc(
                ($a->cnt_won ?? 0) - ($a->cnt_lost ?? 0),
                ($b->cnt_won ?? 0) - ($b->cnt_lost ?? 0)
            ),
            'H2H' => self::numericDesc($aH2h->sum_points ?? 0, $bH2h->sum_points ?? 0),
            'H2H_DIFF' => self::numericDesc($aH2h->diff_team_results ?? 0, $bH2h->diff_team_results ?? 0),
            'DIFF' => self::numericDesc($a->diff_team_results ?? 0, $b->diff_team_results ?? 0),
            'H2H_FOR' => self::numericDesc($aH2h->sum_team1_result ?? 0, $bH2h->sum_team1_result ?? 0),
            'FOR' => self::numericDesc($a->sum_team1_result ?? 0, $b->sum_team1_result ?? 0),
            'H2H_AWAY' => self::numericDesc($aH2h->sum_away_for ?? 0, $bH2h->sum_away_for ?? 0),
            'AWAYFOR' => self::numericDesc($a->sum_away_for ?? 0, $b->sum_away_for ?? 0),
            'LEGS_DIFF' => self::numericDesc($a->diff_team_legs ?? 0, $b->diff_team_legs ?? 0),
            'LEGS_RATIO' => self::numericDesc(self::legsRatio($a), self::legsRatio($b)),
            'LEGS_WIN' => self::numericDesc($a->sum_team1_legs ?? 0, $b->sum_team1_legs ?? 0),
            'WINS' => self::numericDesc($a->cnt_won ?? 0, $b->cnt_won ?? 0),
            'PLAYED' => self::numericByOrder($a->cnt_matches ?? 0, $b->cnt_matches ?? 0, $order),
            'PLAYEDASC' => -self::numericByOrder($a->cnt_matches ?? 0, $b->cnt_matches ?? 0, $order),
            'POINTS_RATIO' => self::numericDesc(self::pointsRatio($a), self::pointsRatio($b)),
            'WOT' => self::numericDesc($a->cnt_wot ?? 0, $b->cnt_wot ?? 0),
            'WSO' => self::numericDesc($a->cnt_wso ?? 0, $b->cnt_wso ?? 0),
            default => 0,
        };
    }

    /** @return array<int, object> */
    private static function headToHead(
        array $groupTeams,
        array $initialTeams,
        array $matches,
        object $project
    ): array {
        if (count($groupTeams) < 2) {
            return $groupTeams;
        }

        $ids = array_map('intval', array_keys($groupTeams));
        $h2hTeams = [];
        foreach ($ids as $id) {
            if (isset($initialTeams[$id])) {
                $h2hTeams[$id] = clone $initialTeams[$id];
            }
        }

        $h2hMatches = array_values(array_filter(
            $matches,
            static fn (object $match): bool => in_array((int) ($match->projectteam1_id ?? 0), $ids, true)
                && in_array((int) ($match->projectteam2_id ?? 0), $ids, true)
        ));
        self::collect($h2hTeams, $h2hMatches, $project);

        return $h2hTeams;
    }

    /** @return array<int, object> */
    private static function cloneTeams(array $teams): array
    {
        $copy = [];
        foreach ($teams as $id => $team) {
            $copy[(int) $id] = clone $team;
        }

        return $copy;
    }

    private static function blankTeam(): object
    {
        return (object) [
            'sum_points' => 0.0,
            'diff_team_results' => 0.0,
            'sum_team1_result' => 0.0,
            'sum_away_for' => 0.0,
        ];
    }

    private static function points(object $team): float
    {
        return (float) ($team->sum_points ?? 0) + (float) ($team->_startpoints ?? 0);
    }

    private static function pointsRatio(object $team): float
    {
        $negative = (float) ($team->neg_points ?? 0);

        return (float) ($team->sum_points ?? 0) / ($negative == 0.0 ? 1.0 : $negative);
    }

    private static function scoreRatio(object $team): float
    {
        $against = (float) ($team->sum_team2_result ?? 0);

        return (float) ($team->sum_team1_result ?? 0) / ($against == 0.0 ? 1.0 : $against);
    }

    private static function legsRatio(object $team): float
    {
        $against = (float) ($team->sum_team2_legs ?? 0);

        return (float) ($team->sum_team1_legs ?? 0) / ($against == 0.0 ? 1.0 : $against);
    }

    private static function winPct(object $team): float
    {
        $games = (int) ($team->cnt_won ?? 0) + (int) ($team->cnt_draw ?? 0) + (int) ($team->cnt_lost ?? 0);

        return $games > 0 ? ((float) ($team->cnt_won ?? 0) / $games) * 100 : 0.0;
    }

    private static function numericByOrder(mixed $a, mixed $b, string $order): int
    {
        return $order === 'ASC' ? self::numericAsc($a, $b) : self::numericDesc($a, $b);
    }

    private static function numericAsc(mixed $a, mixed $b): int
    {
        return (float) $a <=> (float) $b;
    }

    private static function numericDesc(mixed $a, mixed $b): int
    {
        return (float) $b <=> (float) $a;
    }
}
