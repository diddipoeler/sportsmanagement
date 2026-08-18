<?php
namespace Diddipoeler\Component\SportsManagement\Site\Service;

\defined('_JEXEC') or die;

/**
 * Pure in-memory ranking calculator. No database, request, or write access.
 */
final class RankingCalculator
{
    private const DEFAULT_RANKING_ORDER = 'POINTS, PLAYEDASC, DIFF, FOR';
    private const DEFAULT_SORT_ORDER = 'DESC, ASC, DESC, DESC';

    /**
     * @param array<int, RankingRow> $seedRows
     * @param array<int, object> $matches
     * @return array<int, RankingRow>
     */
    public function calculate(array $seedRows, array $matches, object $project, array $config): array
    {
        $rows = $this->cloneRows($seedRows);
        $sportType = (string) ($project->sport_type_name ?? '');
        $this->applyMatches($rows, $matches, $project, $sportType);

        $useFinalTableRank = count($matches) === 0;
        foreach ($seedRows as $seed) {
            if ($seed->sum_points != 0.0) {
                $useFinalTableRank = false;
                break;
            }
        }

        return $this->sortRanking($rows, $config, $seedRows, $matches, $project, $useFinalTableRank);
    }

    /** @param array<int, RankingRow> $rows @param array<int, object> $matches */
    public function applyMatches(array &$rows, array $matches, object $project, string $sportType, ?array $allowedProjectTeams = null): void
    {
        if ($sportType === 'COM_SPORTSMANAGEMENT_ST_SMALL_BORE_RIFLE_ASSOCIATION') {
            $this->applySmallBoreMatches($rows, $matches, $allowedProjectTeams);
            return;
        }

        foreach ($matches as $match) {
            $homeId = (int) $match->projectteam1_id;
            $awayId = (int) $match->projectteam2_id;
            if (!isset($rows[$homeId], $rows[$awayId]) || !$rows[$homeId]->is_in_score || !$rows[$awayId]->is_in_score) {
                continue;
            }
            if ($allowedProjectTeams && (!in_array($homeId, $allowedProjectTeams, true) || !in_array($awayId, $allowedProjectTeams, true))) {
                continue;
            }

            $home = $rows[$homeId];
            $away = $rows[$awayId];
            $decision = (int) ($match->decision ?? 0);
            if ($decision === 0) {
                $homeScore = (float) ($match->home_score ?? 0);
                $awayScore = (float) ($match->away_score ?? 0);
                $leg1 = (float) ($match->l1 ?? 0);
                $leg2 = (float) ($match->l2 ?? 0);
                $mp1 = (float) ($match->mp1 ?? 0);
                $mp2 = (float) ($match->mp2 ?? 0);
                $se1 = (float) ($match->se1 ?? 0);
                $se2 = (float) ($match->se2 ?? 0);
                $ga1 = (float) ($match->ga1 ?? 0);
                $ga2 = (float) ($match->ga2 ?? 0);
            } else {
                $homeScore = (float) ($match->home_score_decision ?? 0);
                $awayScore = (float) ($match->away_score_decision ?? 0);
                $leg1 = $leg2 = $mp1 = $mp2 = $se1 = $se2 = $ga1 = $ga2 = 0.0;
            }

            $balls1 = $balls2 = 0.0;
            if ($sportType === 'COM_SPORTSMANAGEMENT_ST_FAUSTBALL') {
                $balls1 = array_sum(array_map('floatval', explode(';', (string) ($match->ls1 ?? ''))));
                $balls2 = array_sum(array_map('floatval', explode(';', (string) ($match->ls2 ?? ''))));
            }

            $home->cnt_matches++;
            $away->cnt_matches++;
            $resultType = (int) ($project->allow_add_time ?? 0) ? (int) ($match->match_result_type ?? 0) : 0;
            [$winPoints, $drawPoints, $lossPoints] = $this->pointsForResultType($project, $resultType);
            $home->winpoints = $winPoints;
            $away->winpoints = $winPoints;

            if ($decision !== 1) {
                if ($homeScore > $awayScore) {
                    $this->recordHomeWin($home, $away, $resultType);
                    $home->sum_points += $winPoints;
                    $away->sum_points += $lossPoints;
                    $home->neg_points += $lossPoints;
                    $away->neg_points += $winPoints;
                } elseif ($homeScore < $awayScore) {
                    $this->recordAwayWin($home, $away, $resultType);
                    $home->sum_points += $lossPoints;
                    $away->sum_points += $winPoints;
                    $home->neg_points += $winPoints;
                    $away->neg_points += $lossPoints;
                } else {
                    $this->recordDraw(
                        $home,
                        $away,
                        $resultType,
                        (float) ($match->home_score_ot ?? 0),
                        (float) ($match->away_score_ot ?? 0),
                        (float) ($match->home_score_so ?? 0),
                        (float) ($match->away_score_so ?? 0)
                    );
                    $home->sum_points += $drawPoints;
                    $away->sum_points += $drawPoints;
                    $home->neg_points += ($winPoints - $drawPoints);
                    $away->neg_points += ($winPoints - $drawPoints);
                }
            } else {
                $home->neg_points += $lossPoints;
                $away->neg_points += $lossPoints;
                $this->recordDecision((int) ($match->team_won ?? 0), $home, $away, $winPoints);
            }

            $homeBonus = (float) ($match->home_bonus ?? 0);
            $awayBonus = (float) ($match->away_bonus ?? 0);
            $home->sum_points += $homeBonus;
            $home->bonus_points += $homeBonus;
            $away->sum_points += $awayBonus;
            $away->bonus_points += $awayBonus;

            $this->addResultStats($home, $homeScore, $awayScore, $leg1, $leg2, $mp1, $mp2, $se1, $se2, $ga1, $ga2, $balls1, $balls2, $sportType);
            $this->addResultStats($away, $awayScore, $homeScore, $leg2, $leg1, $mp2, $mp1, $se2, $se1, $ga2, $ga1, $balls2, $balls1, $sportType);

            if ($sportType === 'COM_SPORTSMANAGEMENT_ST_SOCCER') {
                $home->sum_points = ($home->cnt_won * $winPoints) + ($home->cnt_draw * $drawPoints);
                $away->sum_points = ($away->cnt_won * $winPoints) + ($away->cnt_draw * $drawPoints);
                $home->neg_points = ($home->cnt_lost * $winPoints) + ($home->cnt_draw * ($winPoints - $drawPoints));
                $away->neg_points = ($away->cnt_lost * $winPoints) + ($away->cnt_draw * ($winPoints - $drawPoints));
            }

            $home->points = $home->sum_points;
            $away->points = $away->sum_points;
            $away->sum_away_for += $awayScore;
        }
    }

    private function applySmallBoreMatches(array &$rows, array $matches, ?array $allowedProjectTeams): void
    {
        foreach ($matches as $match) {
            $homeId = (int) $match->projectteam1_id;
            $awayId = (int) $match->projectteam2_id;
            if ($allowedProjectTeams && (!in_array($homeId, $allowedProjectTeams, true) || !in_array($awayId, $allowedProjectTeams, true))) {
                continue;
            }
            if (isset($rows[$homeId])) {
                $homeScore = (float) ($match->home_score ?? 0);
                $awayScore = (float) ($match->away_score ?? 0);
                $rows[$homeId]->sum_team1_result += $homeScore;
                $rows[$homeId]->sum_team2_result += $awayScore;
                $rows[$homeId]->shooterrings += $homeScore;
                $rows[$homeId]->shooterringsperround[(string) ($match->roundcode ?? '')] = $homeScore;
            }
            if (isset($rows[$awayId])) {
                $awayScore = (float) ($match->away_score ?? 0);
                $homeScore = (float) ($match->home_score ?? 0);
                $rows[$awayId]->sum_team1_result += $awayScore;
                $rows[$awayId]->sum_team2_result += $homeScore;
                $rows[$awayId]->shooterrings += $awayScore;
            }
        }
    }

    /** @return array{0:float,1:float,2:float} */
    private function pointsForResultType(object $project, int $resultType): array
    {
        $source = match ($resultType) {
            1 => (string) ($project->points_after_add_time ?? ''),
            2 => (string) ($project->points_after_penalty ?? ''),
            default => (string) ($project->points_after_regular_time ?? ''),
        };
        $parts = array_map('trim', explode(',', $source));
        return [
            isset($parts[0]) && $parts[0] !== '' ? (float) $parts[0] : 3.0,
            isset($parts[1]) && $parts[1] !== '' ? (float) $parts[1] : 1.0,
            isset($parts[2]) && $parts[2] !== '' ? (float) $parts[2] : 0.0,
        ];
    }

    private function recordHomeWin(RankingRow $home, RankingRow $away, int $resultType): void
    {
        if ($resultType === 0) {
            $home->cnt_won++; $home->cnt_won_home++; $away->cnt_lost++; $away->cnt_lost_away++;
        } elseif ($resultType === 1) {
            $home->cnt_wot++; $home->cnt_wot_home++; $away->cnt_lot++; $away->cnt_lot_away++;
        } else {
            $home->cnt_wso++; $home->cnt_wso_home++; $away->cnt_lso++; $away->cnt_lso_away++; $away->cnt_lot++; $away->cnt_lot_away++;
        }
    }

    private function recordAwayWin(RankingRow $home, RankingRow $away, int $resultType): void
    {
        if ($resultType === 0) {
            $home->cnt_lost++; $home->cnt_lost_home++; $away->cnt_won++; $away->cnt_won_away++;
        } elseif ($resultType === 1) {
            $home->cnt_lot++; $home->cnt_lot_home++; $away->cnt_wot++; $away->cnt_wot_away++;
        } else {
            $home->cnt_lso++; $home->cnt_lso_home++; $home->cnt_lot++; $home->cnt_lot_home++; $away->cnt_wso++; $away->cnt_wso_away++;
        }
    }

    private function recordDraw(RankingRow $home, RankingRow $away, int $resultType, float $homeOt, float $awayOt, float $homeSo, float $awaySo): void
    {
        if ($resultType === 0) {
            $home->cnt_draw++; $home->cnt_draw_home++; $away->cnt_draw++; $away->cnt_draw_away++;
            return;
        }
        if ($resultType === 1) {
            if ($homeOt > $awayOt) { $home->cnt_wot++; $home->cnt_wot_home++; $away->cnt_lot++; $away->cnt_lot_away++; }
            if ($homeOt < $awayOt) { $away->cnt_wot++; $away->cnt_wot_away++; $home->cnt_lot++; $home->cnt_lot_home++; }
            return;
        }
        if ($homeSo > $awaySo) { $home->cnt_wso++; $home->cnt_wso_home++; $away->cnt_lso++; $away->cnt_lso_away++; }
        if ($homeSo < $awaySo) { $away->cnt_wso++; $away->cnt_wso_away++; $home->cnt_lso++; $home->cnt_lso_home++; }
    }

    private function recordDecision(int $winner, RankingRow $home, RankingRow $away, float $winPoints): void
    {
        match ($winner) {
            1 => $this->decisionHome($home, $away, $winPoints),
            2 => $this->decisionAway($home, $away, $winPoints),
            4 => $this->decisionBothWin($home, $away, $winPoints),
            default => $this->decisionBothLose($home, $away),
        };
    }

    private function decisionHome(RankingRow $home, RankingRow $away, float $points): void
    {
        $home->cnt_won++; $away->cnt_lost++; $home->sum_points += $points; $away->cnt_lost_home++;
    }

    private function decisionAway(RankingRow $home, RankingRow $away, float $points): void
    {
        $away->cnt_won++; $home->cnt_lost++; $away->sum_points += $points; $home->cnt_lost_home++;
    }

    private function decisionBothWin(RankingRow $home, RankingRow $away, float $points): void
    {
        $home->cnt_won++; $away->cnt_won++; $home->sum_points += $points; $away->sum_points += $points;
    }

    private function decisionBothLose(RankingRow $home, RankingRow $away): void
    {
        $home->cnt_lost++; $away->cnt_lost++; $home->cnt_lost_home++; $away->cnt_lost_home++;
    }

    private function addResultStats(
        RankingRow $row,
        float $for,
        float $against,
        float $legsFor,
        float $legsAgainst,
        float $matchPointsFor,
        float $matchPointsAgainst,
        float $setsFor,
        float $setsAgainst,
        float $gamesFor,
        float $gamesAgainst,
        float $ballsFor,
        float $ballsAgainst,
        string $sportType
    ): void {
        $row->sum_team1_result += $for;
        $row->sum_team2_result += $against;
        $row->scorefor += $for;
        $row->scoreagainst += $against;
        $row->goalsfor += $for;
        $row->goalsagainst += $against;
        $row->diff_team_results = $row->sum_team1_result - $row->sum_team2_result;
        $row->sum_team1_legs += $legsFor;
        $row->sum_team2_legs += $legsAgainst;
        $row->diff_team_legs = $row->sum_team1_legs - $row->sum_team2_legs;
        $row->sum_team1_matchpoint += $matchPointsFor;
        $row->sum_team2_matchpoint += $matchPointsAgainst;
        $row->diff_team_matchpoint = $row->sum_team1_matchpoint - $row->sum_team2_matchpoint;
        $row->sum_team1_sets += $setsFor;
        $row->sum_team2_sets += $setsAgainst;
        $row->diff_team_sets = $row->sum_team1_sets - $row->sum_team2_sets;
        $row->sum_team1_games += $gamesFor;
        $row->sum_team2_games += $gamesAgainst;
        $row->diff_team_games = $row->sum_team1_games - $row->sum_team2_games;
        if ($sportType === 'COM_SPORTSMANAGEMENT_ST_FAUSTBALL') {
            $row->sum_team1_balls += $ballsFor;
            $row->sum_team2_balls += $ballsAgainst;
            $row->diff_team_balls = $row->sum_team1_balls - $row->sum_team2_balls;
        }
    }

    /** @param array<int, RankingRow> $rows @return array<int, RankingRow> */
    private function sortRanking(array $rows, array $config, array $seedRows, array $matches, object $project, bool $useFinalTableRank): array
    {
        $criteria = $useFinalTableRank
            ? ['finaltablerank']
            : array_values(array_filter(array_map(static fn($v) => strtolower(trim($v)), explode(',', (string) ($config['ranking_order'] ?? self::DEFAULT_RANKING_ORDER)))));
        if (!$criteria) {
            $criteria = ['points'];
        }
        $orders = array_values(array_map(static fn($v) => strtoupper(trim($v)), explode(',', (string) ($config['ranking_sort_order'] ?? self::DEFAULT_SORT_ORDER))));
        $groups = [1 => $rows];

        foreach ($criteria as $index => $criterion) {
            $newGroups = [];
            foreach ($groups as $rank => $group) {
                $h2h = null;
                if (str_starts_with($criterion, 'h2h') && count($group) > 1) {
                    $ptids = array_keys($group);
                    $h2h = $this->cloneRows(array_intersect_key($seedRows, array_flip($ptids)));
                    $this->applyMatches($h2h, $matches, $project, (string) ($project->sport_type_name ?? ''), $ptids);
                }
                $order = $orders[$index] ?? 'DESC';
                uasort($group, fn(RankingRow $a, RankingRow $b): int => $this->compare($criterion, $a, $b, $order, $h2h));
                $newRank = (int) $rank;
                $current = (int) $rank;
                $previous = null;
                foreach ($group as $key => $team) {
                    if ($previous === null || $this->compare($criterion, $team, $previous, $order, $h2h) !== 0) {
                        $newGroups[$newRank] = [$key => $team];
                        $current = $newRank;
                    } else {
                        $newGroups[$current][$key] = $team;
                    }
                    $previous = $team;
                    $newRank++;
                }
            }
            $groups = $newGroups;
        }

        $result = [];
        foreach ($groups as $rank => $group) {
            uasort($group, static fn(RankingRow $a, RankingRow $b): int => strcasecmp($a->getName(), $b->getName()));
            foreach ($group as $ptid => $team) {
                $team->rank = (int) $rank;
                $result[(int) $ptid] = $team;
            }
        }
        return $result;
    }

    /** @param array<int, RankingRow>|null $h2h */
    private function compare(string $criterion, RankingRow $a, RankingRow $b, string $order, ?array $h2h): int
    {
        $criterion = strtolower($criterion);
        if (str_starts_with($criterion, 'h2h') && $h2h) {
            $a = $h2h[$a->projectteamid] ?? $a;
            $b = $h2h[$b->projectteamid] ?? $b;
            $criterion = match ($criterion) {
                'h2h' => 'points_no_start',
                'h2h_diff' => 'diff',
                'h2h_for' => 'for',
                'h2h_away' => 'awayfor',
                default => $criterion,
            };
        }

        [$av, $bv, $defaultDirection] = match ($criterion) {
            'finaltablerank' => [$a->finaltablerank, $b->finaltablerank, 'ASC'],
            'points' => [$a->getPoints(), $b->getPoints(), 'DESC'],
            'points_no_start' => [$a->getPoints(false), $b->getPoints(false), 'DESC'],
            'shooterrings' => [$a->shooterrings, $b->shooterrings, 'DESC'],
            'penaltypoints' => [$a->penalty_points, $b->penalty_points, 'ASC'],
            'bonus' => [$a->bonus_points, $b->bonus_points, 'DESC'],
            'against' => [$a->sum_team2_result, $b->sum_team2_result, 'ASC'],
            'scoreavg' => [$a->scoreAvg(), $b->scoreAvg(), 'DESC'],
            'scorepct' => [$a->scorePct(), $b->scorePct(), 'DESC'],
            'winpct' => [$a->winPct(), $b->winPct(), 'DESC'],
            'gb' => [($a->cnt_won - $a->cnt_lost), ($b->cnt_won - $b->cnt_lost), 'DESC'],
            'diff' => [$a->diff_team_results, $b->diff_team_results, 'DESC'],
            'for', 'scorefor' => [$a->sum_team1_result, $b->sum_team1_result, 'DESC'],
            'scoreagainst' => [$a->sum_team2_result, $b->sum_team2_result, 'DESC'],
            'goalsfor' => [$a->goalsfor, $b->goalsfor, 'DESC'],
            'goalsagainst' => [$a->goalsagainst, $b->goalsagainst, 'DESC'],
            'awayfor' => [$a->sum_away_for, $b->sum_away_for, 'DESC'],
            'legs_diff' => [$a->diff_team_legs, $b->diff_team_legs, 'DESC'],
            'legs_ratio' => [$a->legsRatio(), $b->legsRatio(), 'DESC'],
            'legs_win' => [$a->sum_team1_legs, $b->sum_team1_legs, 'DESC'],
            'wins' => [$a->cnt_won, $b->cnt_won, 'DESC'],
            'playedasc' => [$a->cnt_matches, $b->cnt_matches, 'ASC'],
            'played' => [$a->cnt_matches, $b->cnt_matches, 'DESC'],
            'points_ratio' => [$a->pointsRatio(), $b->pointsRatio(), 'DESC'],
            'wot' => [$a->cnt_wot, $b->cnt_wot, 'DESC'],
            'wso' => [$a->cnt_wso, $b->cnt_wso, 'DESC'],
            default => [0, 0, 'DESC'],
        };

        $direction = in_array($criterion, ['points', 'played', 'goalsfor', 'goalsagainst'], true)
            ? (in_array($order, ['ASC', 'DESC'], true) ? $order : $defaultDirection)
            : $defaultDirection;
        $comparison = $av <=> $bv;
        return $direction === 'DESC' ? -$comparison : $comparison;
    }

    /** @param array<int, RankingRow> $rows @return array<int, RankingRow> */
    private function cloneRows(array $rows): array
    {
        $copy = [];
        foreach ($rows as $key => $row) {
            $copy[$key] = clone $row;
            if ($row->team) {
                $copy[$key]->team = clone $row->team;
            }
        }
        return $copy;
    }
}
