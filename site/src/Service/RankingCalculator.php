<?php
/**
 * Pure in-memory Joomla 5/6 ranking calculator.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Service;

\defined('_JEXEC') or die;

/**
 * Pure in-memory ranking calculator. No database, request, or write access.
 */
final class RankingCalculator
{
    private const DEFAULT_RANKING_ORDER = 'POINTS, PLAYEDASC, DIFF, FOR';
    private const DEFAULT_SORT_ORDER = 'DESC, ASC, DESC, DESC';

    /** @var list<string> Criteria the ranking comparator actually supports. */
    private const SUPPORTED_RANKING_CRITERIA = [
        'finaltablerank', 'points', 'points_no_start', 'shooterrings',
        'penaltypoints', 'bonus', 'against', 'scoreavg', 'scorepct',
        'winpct', 'gb', 'diff', 'for', 'scorefor', 'scoreagainst',
        'goalsfor', 'goalsagainst', 'awayfor', 'legs_diff',
        'legs_ratio', 'legs_win', 'wins', 'playedasc', 'played',
        'points_ratio', 'wot', 'wso', 'h2h', 'h2h_diff', 'h2h_for',
        'h2h_away',
    ];

    /**
     * Calculate and sort a full project ranking without altering its input rows.
     * Clones seeded teams, applies countable fixtures and resolves ranking
     * criteria, head-to-head tie-breakers or stored final standings. Saved
     * final ranks are used only when at least one valid position exists.
     *
     * @param array<int,RankingRow> $seedRows Original team rows indexed by project-team ID.
     * @param array<int,object> $matches Published fixtures with scores and result metadata.
     * @param object $project Project sport type and point settings.
     * @param array $config Ranking and tie-break configuration values.
     * @return array<int,RankingRow> Newly calculated rows ordered by effective rank.
     */
    public function calculate(array $seedRows, array $matches, object $project, array $config): array
    {
        $rows = $this->cloneRows($seedRows);
        $sportType = (string) ($project->sport_type_name ?? '');
        $this->applyMatches($rows, $matches, $project, $sportType);

        // Stored final ranks are meaningful only when no fixtures are being
        // calculated and at least one team has a valid saved position.
        // An empty set of final ranks must not override starting points.
        $useFinalTableRank = $matches === [];
        $hasSavedFinalRank = false;

        foreach ($seedRows as $seed) {
            if ($seed->sum_points != 0.0) {
                $useFinalTableRank = false;
                break;
            }

            if ($seed->finaltablerank > 0) {
                $hasSavedFinalRank = true;
            }
        }

        $useFinalTableRank = $useFinalTableRank && $hasSavedFinalRank;

        return $this->sortRanking($rows, $config, $seedRows, $matches, $project, $useFinalTableRank);
    }

    /**
     * Update ranking totals and match statistics from the selected fixtures.
     * Applies sport-specific results to eligible teams, including small-bore
     * and Faustball scoring. Malformed team identifiers and required scores
     * are ignored; optional bonuses, game statistics and tie-break scores
     * default to zero when malformed. Result points are accumulated
     * per fixture, retaining sport-specific overtime or shootout values,
     * loss-point settings and bonuses. Invalid administrative winner codes
     * and self-matches are ignored.
     *
     * @param array<int,RankingRow> $rows Ranking rows modified in place.
     * @param array<int,object> $matches Matches whose results contribute to standings.
     * @param object $project Project score settings and overtime policy.
     * @param string $sportType SportsManagement sport-type constant.
     * @param array<int,int>|null $allowedProjectTeams Optional head-to-head subset;
     *     an empty list explicitly excludes all matches.
     * @return void
     */
    public function applyMatches(array &$rows, array $matches, object $project, string $sportType, ?array $allowedProjectTeams = null): void
    {
        if ($sportType === 'COM_SPORTSMANAGEMENT_ST_SMALL_BORE_RIFLE_ASSOCIATION') {
            $this->applySmallBoreMatches($rows, $matches, $allowedProjectTeams);
            return;
        }

        foreach ($matches as $match) {
            if (!is_object($match)) {
                continue;
            }

            $homeId = self::validProjectTeamId($match->projectteam1_id ?? null);
            $awayId = self::validProjectTeamId($match->projectteam2_id ?? null);

            // A fixture cannot be played against the same project team.
            // Otherwise both row references point to one object and wins,
            // losses, matches and scores get counted twice.
            if ($homeId === null || $awayId === null || $homeId === $awayId) {
                continue;
            }

            if (!isset($rows[$homeId], $rows[$awayId]) || !$rows[$homeId]->is_in_score || !$rows[$awayId]->is_in_score) {
                continue;
            }
            if ($allowedProjectTeams !== null && (!in_array($homeId, $allowedProjectTeams, true) || !in_array($awayId, $allowedProjectTeams, true))) {
                continue;
            }

            $home = $rows[$homeId];
            $away = $rows[$awayId];
            $decisionValue = self::finiteNumber($match->decision ?? 0);
            if ($decisionValue === null || !in_array($decisionValue, [0.0, 1.0], true)) {
                continue;
            }

            $decision = (int) $decisionValue;

            if ($decision === 1) {
                $winner = $match->team_won ?? null;

                // Administrative winner codes are discrete integers. A
                // fractional or nested legacy value must not be coerced to a
                // home/away win or a two-sided loss.
                if ((!is_int($winner) && !is_string($winner))
                    || filter_var($winner, FILTER_VALIDATE_INT) === false) {
                    continue;
                }
            }

            if ($decision === 0) {
                // Incomplete or corrupted score cells must never be counted
                // as a draw or an administrative result.
                $homeScore = self::finiteNumber($match->home_score ?? null);
                $awayScore = self::finiteNumber($match->away_score ?? null);
                if ($homeScore === null || $awayScore === null) {
                    continue;
                }
                $leg1 = self::finiteNumber($match->l1 ?? 0) ?? 0.0;
                $leg2 = self::finiteNumber($match->l2 ?? 0) ?? 0.0;
                $mp1 = self::finiteNumber($match->mp1 ?? 0) ?? 0.0;
                $mp2 = self::finiteNumber($match->mp2 ?? 0) ?? 0.0;
                $se1 = self::finiteNumber($match->se1 ?? 0) ?? 0.0;
                $se2 = self::finiteNumber($match->se2 ?? 0) ?? 0.0;
                $ga1 = self::finiteNumber($match->ga1 ?? 0) ?? 0.0;
                $ga2 = self::finiteNumber($match->ga2 ?? 0) ?? 0.0;
            } else {
                $homeScore = self::finiteNumber($match->home_score_decision ?? 0) ?? 0.0;
                $awayScore = self::finiteNumber($match->away_score_decision ?? 0) ?? 0.0;
                $leg1 = $leg2 = $mp1 = $mp2 = $se1 = $se2 = $ga1 = $ga2 = 0.0;
            }

            $balls1 = $balls2 = 0.0;
            if ($sportType === 'COM_SPORTSMANAGEMENT_ST_FAUSTBALL') {
                $balls1 = self::splitResultTotal($match->ls1 ?? '');
                $balls2 = self::splitResultTotal($match->ls2 ?? '');
            }

            $home->cnt_matches++;
            $away->cnt_matches++;
            $allowExtraTime = self::finiteNumber($project->allow_add_time ?? 0) ?? 0.0;
            $resultTypeValue = self::finiteNumber($match->match_result_type ?? 0) ?? 0.0;
            $resultType = $allowExtraTime != 0.0 && in_array($resultTypeValue, [1.0, 2.0], true)
                ? (int) $resultTypeValue
                : 0;
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
                        (self::finiteNumber($match->home_score_ot ?? 0) ?? 0.0),
                        (self::finiteNumber($match->away_score_ot ?? 0) ?? 0.0),
                        (self::finiteNumber($match->home_score_so ?? 0) ?? 0.0),
                        (self::finiteNumber($match->away_score_so ?? 0) ?? 0.0)
                    );
                    $home->sum_points += $drawPoints;
                    $away->sum_points += $drawPoints;
                    $home->neg_points += ($winPoints - $drawPoints);
                    $away->neg_points += ($winPoints - $drawPoints);
                }
            } else {
                $home->neg_points += $lossPoints;
                $away->neg_points += $lossPoints;
                $this->recordDecision((int) $winner, $home, $away, $winPoints);
            }

            $homeBonus = self::finiteNumber($match->home_bonus ?? 0) ?? 0.0;
            $awayBonus = self::finiteNumber($match->away_bonus ?? 0) ?? 0.0;
            $home->sum_points += $homeBonus;
            $home->bonus_points += $homeBonus;
            $away->sum_points += $awayBonus;
            $away->bonus_points += $awayBonus;

            $this->addResultStats($home, $homeScore, $awayScore, $leg1, $leg2, $mp1, $mp2, $se1, $se2, $ga1, $ga2, $balls1, $balls2, $sportType);
            $this->addResultStats($away, $awayScore, $homeScore, $leg2, $leg1, $mp2, $mp1, $se2, $se1, $ga2, $ga1, $balls2, $balls1, $sportType);

            // Each fixture has already credited its configured points and
            // bonuses above. Reconstructing soccer points from cumulative
            // win/draw counters here loses overtime and shootout victories,
            // match-specific point rules and negative points for losses.

            $home->points = $home->sum_points;
            $away->points = $away->sum_points;
            $away->sum_away_for += $awayScore;
        }
    }

    /**
     * Parse a positive project-team identifier without coercing nested values.
     *
     * Database rows can be corrupted by legacy imports. A malformed ID must
     * not emit PHP warnings or silently select an unrelated ranking row.
     *
     * @param mixed $value Raw project-team ID from a fixture.
     * @return int|null Positive numeric identifier, or null if invalid.
     */
    private static function validProjectTeamId(mixed $value): ?int
    {
        if (!is_int($value) && !is_string($value)) {
            return null;
        }

        $id = filter_var($value, FILTER_VALIDATE_INT);

        return $id !== false && $id > 0 ? $id : null;
    }

    /**
     * Convert a stored match value to a finite number without PHP warnings.
     *
     * Missing, nested and non-numeric legacy values are invalid. Optional
     * statistics can then choose a safe default at the call site.
     *
     * @param mixed $value Raw result or point field.
     * @return float|null Finite numeric value, or null if invalid.
     */
    private static function finiteNumber(mixed $value): ?float
    {
        if (!is_int($value) && !is_float($value) && !is_string($value)) {
            return null;
        }

        if (!is_numeric($value)) {
            return null;
        }

        $number = (float) $value;

        return is_finite($number) ? $number : null;
    }

    /**
     * Sum semicolon-separated finite match segments without coercion warnings.
     *
     * Legacy Faustball results may contain empty, nonnumeric or nested
     * values. Such segments do not contribute to the total.
     *
     * @param mixed $value Legacy semicolon-separated segment scores.
     * @return float Sum of valid finite score segments.
     */
    private static function splitResultTotal(mixed $value): float
    {
        if (!is_string($value) && !is_int($value) && !is_float($value)) {
            return 0.0;
        }

        $total = 0.0;

        foreach (explode(';', (string) $value) as $part) {
            $number = self::finiteNumber(trim($part));

            if ($number !== null) {
                $total += $number;
            }
        }

        return is_finite($total) ? $total : 0.0;
    }

    /**
     * Accumulate individual small-bore shooting scores and per-round rings.
     * Uses specialist ring totals rather than win/draw points. Preserves
     * valid one-sided results, but discards corrupt self-matches and
     * ignores team rows excluded from the scoring table. Multiple results
     * in one round contribute to each team's per-round ring total, whether
     * the team appears as the home or away side.
     *
     * @param array<int,RankingRow> $rows Team rows receiving shooting scores.
     * @param array<int,object> $matches Scored rifle fixtures.
     * @param array<int,int>|null $allowedProjectTeams Optional subset;
     *     an empty list excludes all shooting matches.
     * @return void
     */
    private function applySmallBoreMatches(array &$rows, array $matches, ?array $allowedProjectTeams): void
    {
        foreach ($matches as $match) {
            if (!is_object($match)) {
                continue;
            }

            // Small-bore results may legitimately contain only one team;
            // ignore malformed IDs without discarding the other valid side.
            $homeId = self::validProjectTeamId($match->projectteam1_id ?? null) ?? 0;
            $awayId = self::validProjectTeamId($match->projectteam2_id ?? null) ?? 0;

            // Preserve legitimate single-sided shooting results, but never
            // apply two sides of one fixture to the same ranking row.
            if (($homeId === 0 && $awayId === 0)
                || ($homeId > 0 && $homeId === $awayId)) {
                continue;
            }

            if ($allowedProjectTeams !== null && (!in_array($homeId, $allowedProjectTeams, true) || !in_array($awayId, $allowedProjectTeams, true))) {
                continue;
            }
            if (isset($rows[$homeId]) && $rows[$homeId]->is_in_score) {
                $homeScore = self::finiteNumber($match->home_score ?? null);
                $awayScore = self::finiteNumber($match->away_score ?? 0);
                if ($homeScore !== null && $awayScore !== null) {
                    $rows[$homeId]->sum_team1_result += $homeScore;
                    $rows[$homeId]->sum_team2_result += $awayScore;
                    $rows[$homeId]->shooterrings += $homeScore;
                    $round = $match->roundcode ?? '';
                    $roundKey = is_scalar($round) ? (string) $round : '';
                    // More than one fixture may belong to the same round.
                    // Keep the per-round total in sync with shooterrings.
                    $rows[$homeId]->shooterringsperround[$roundKey] =
                        ($rows[$homeId]->shooterringsperround[$roundKey] ?? 0.0) + $homeScore;
                }
            }
            if (isset($rows[$awayId]) && $rows[$awayId]->is_in_score) {
                $awayScore = self::finiteNumber($match->away_score ?? null);
                $homeScore = self::finiteNumber($match->home_score ?? 0);
                if ($awayScore !== null && $homeScore !== null) {
                    $rows[$awayId]->sum_team1_result += $awayScore;
                    $rows[$awayId]->sum_team2_result += $homeScore;
                    $rows[$awayId]->shooterrings += $awayScore;
                    $round = $match->roundcode ?? '';
                    $roundKey = is_scalar($round) ? (string) $round : '';
                    $rows[$awayId]->shooterringsperround[$roundKey] =
                        ($rows[$awayId]->shooterringsperround[$roundKey] ?? 0.0) + $awayScore;
                }
            }
        }
    }

    /**
     * Choose the win, draw and loss point values for a result category.
     * Parses the project's regular-time, extra-time or penalty point list.
     * Rejects non-numeric tokens, non-finite numbers and nested legacy
     * settings, retaining the standard 3/1/0 fallback for invalid entries.
     *
     * @param object $project Project scoring configuration.
     * @param int $resultType Regular, overtime or penalty result type.
     * @return array{0:float,1:float,2:float} Points for a win, draw and loss.
     */
    private function pointsForResultType(object $project, int $resultType): array
    {
        $setting = match ($resultType) {
            1 => $project->points_after_add_time ?? '',
            2 => $project->points_after_penalty ?? '',
            default => $project->points_after_regular_time ?? '',
        };

        // Legacy configuration may contain nested values after an import.
        // Keep the normal scoring defaults rather than casting arrays or
        // objects, or treating a non-numeric token as zero points.
        $source = is_string($setting) || is_int($setting) || is_float($setting)
            ? (string) $setting
            : '';
        $parts = array_map('trim', explode(',', $source));
        $defaults = [3.0, 1.0, 0.0];

        foreach ($defaults as $index => $default) {
            if (isset($parts[$index]) && is_numeric($parts[$index])) {
                $candidate = (float) $parts[$index];
                if (is_finite($candidate)) {
                    $defaults[$index] = $candidate;
                }
            }
        }

        return $defaults;
    }

    /**
     * Record the outcome counters for a home team's ordinary or overtime win.
     * Updates the matching home-win and away-loss counters for regular time, extra time or a shootout decision.
     *
     * @param RankingRow $home Home team standing row.
     * @param RankingRow $away Away team standing row.
     * @param int $resultType Regular, overtime or shootout result category.
     * @return void
     */
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

    /**
     * Record the outcome counters for an away team's ordinary or overtime win.
     * Increments the home-loss and away-win statistics according to the match's regular, overtime or shootout result type.
     *
     * @param RankingRow $home Home team standing row.
     * @param RankingRow $away Away team standing row.
     * @param int $resultType Regular, overtime or shootout result category.
     * @return void
     */
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

    /**
     * Record drawn regular-time matches and their later overtime or shootout winners.
     * Counts ordinary draws immediately, or examines overtime and shootout scores to update the appropriate win/loss counters.
     *
     * @param RankingRow $home Home team standing row.
     * @param RankingRow $away Away team standing row.
     * @param int $resultType Regular-time, overtime or shootout category.
     * @param float $homeOt Home overtime score.
     * @param float $awayOt Away overtime score.
     * @param float $homeSo Home shootout score.
     * @param float $awaySo Away shootout score.
     * @return void
     */
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

    /**
     * Apply an administrative match decision to both teams' standings.
     * Dispatches the recorded winner code to home-win, away-win, both-win or both-loss accounting and awards configured win points as appropriate.
     *
     * @param int $winner Administrative result code for the winning side or both sides.
     * @param RankingRow $home Home team standing row.
     * @param RankingRow $away Away team standing row.
     * @param float $winPoints Points awarded for an administrative win.
     * @return void
     */
    private function recordDecision(int $winner, RankingRow $home, RankingRow $away, float $winPoints): void
    {
        match ($winner) {
            1 => $this->decisionHome($home, $away, $winPoints),
            2 => $this->decisionAway($home, $away, $winPoints),
            4 => $this->decisionBothWin($home, $away, $winPoints),
            default => $this->decisionBothLose($home, $away),
        };
    }

    /**
     * Award an administratively decided win to the home team.
     * Increments the home win and away loss counters and credits the home row with the configured win points.
     *
     * @param RankingRow $home Home ranking row.
     * @param RankingRow $away Away ranking row.
     * @param float $points Win points to award.
     * @return void
     */
    private function decisionHome(RankingRow $home, RankingRow $away, float $points): void
    {
        $home->cnt_won++; $away->cnt_lost++; $home->sum_points += $points; $away->cnt_lost_home++;
    }

    /**
     * Award an administratively decided win to the away team.
     * Increments the away win and home loss counters and credits the away row with the configured win points.
     *
     * @param RankingRow $home Home ranking row.
     * @param RankingRow $away Away ranking row.
     * @param float $points Win points to award.
     * @return void
     */
    private function decisionAway(RankingRow $home, RankingRow $away, float $points): void
    {
        $away->cnt_won++; $home->cnt_lost++; $away->sum_points += $points; $home->cnt_lost_home++;
    }

    /**
     * Record an administrative result granting a win to both teams.
     * Credits the two participants with the configured number of win points and increments their win totals.
     *
     * @param RankingRow $home Home ranking row.
     * @param RankingRow $away Away ranking row.
     * @param float $points Win points awarded to each side.
     * @return void
     */
    private function decisionBothWin(RankingRow $home, RankingRow $away, float $points): void
    {
        $home->cnt_won++; $away->cnt_won++; $home->sum_points += $points; $away->sum_points += $points;
    }

    /**
     * Record an administrative result that counts as a loss for both teams.
     * Increments the loss counters for each participant without awarding result points.
     *
     * @param RankingRow $home Home ranking row.
     * @param RankingRow $away Away ranking row.
     * @return void
     */
    private function decisionBothLose(RankingRow $home, RankingRow $away): void
    {
        $home->cnt_lost++; $away->cnt_lost++; $home->cnt_lost_home++; $away->cnt_lost_home++;
    }

    /**
     * Accumulate team scores and sport-specific ranking comparison statistics.
     * Updates score totals and differences for goals, legs, match points, sets and games, including Faustball ball totals when applicable.
     *
     * @param RankingRow $row Team row receiving the updated totals.
     * @param float $for Scores achieved by the team.
     * @param float $against Scores conceded by the team.
     * @param float $legsFor Legs won.
     * @param float $legsAgainst Legs conceded.
     * @param float $matchPointsFor Individual match points won.
     * @param float $matchPointsAgainst Individual match points conceded.
     * @param float $setsFor Sets won.
     * @param float $setsAgainst Sets conceded.
     * @param float $gamesFor Games won.
     * @param float $gamesAgainst Games conceded.
     * @param float $ballsFor Faustball balls won.
     * @param float $ballsAgainst Faustball balls conceded.
     * @param string $sportType Current sport-type identifier.
     * @return void
     */
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

    /**
     * Apply ordered ranking criteria while keeping equal teams grouped for tie-breaking.
     * Evaluates each configured criterion in sequence, recalculates head-to-head
     * subtables and assigns ranks. Malformed nested sort settings revert to
     * the ordinary defaults without PHP array-to-string warnings. Unsupported
     * criteria are ignored rather than forcing every team into one tied rank.
     *
     * @param array<int,RankingRow> $rows Newly calculated standings.
     * @param array $config Ranking criteria and sort directions.
     * @param array<int,RankingRow> $seedRows Original unmodified ranking rows.
     * @param array<int,object> $matches Match records used for head-to-head comparisons.
     * @param object $project Project sport and scoring configuration.
     * @param bool $useFinalTableRank Whether saved final positions override match tie-breakers.
     * @return array<int,RankingRow> Standings ordered and annotated with ranks.
     */
    private function sortRanking(array $rows, array $config, array $seedRows, array $matches, object $project, bool $useFinalTableRank): array
    {
        // Malformed registry settings can be arrays or objects after a legacy
        // import. Avoid implicit string casts and preserve the normal criteria.
        $rankingOrder = $config['ranking_order'] ?? self::DEFAULT_RANKING_ORDER;
        if (!is_string($rankingOrder) || trim($rankingOrder) === '') {
            $rankingOrder = self::DEFAULT_RANKING_ORDER;
        }

        $sortOrder = $config['ranking_sort_order'] ?? self::DEFAULT_SORT_ORDER;
        if (!is_string($sortOrder) || trim($sortOrder) === '') {
            $sortOrder = self::DEFAULT_SORT_ORDER;
        }

        // Keep original token indexes for the matching ASC/DESC settings.
        $criteria = $useFinalTableRank
            ? ['finaltablerank']
            : array_filter(array_map(
                static fn(string $value): string => strtolower(trim($value)),
                explode(',', $rankingOrder)
            ), static fn(string $value): bool => $value !== '');
        // The comparator treats unknown criteria as an equality (0 == 0).
        // Drop unsupported tokens so malformed-only settings do not make
        // every team share the same rank. Preserve indexes to keep each
        // surviving criterion paired with its configured sort direction.
        $criteria = array_filter(
            $criteria,
            static fn(string $value): bool => in_array($value, self::SUPPORTED_RANKING_CRITERIA, true)
        );

        if ($criteria === []) {
            $criteria = ['points'];
        }

        $orders = array_values(array_map(
            static fn(string $value): string => strtoupper(trim($value)),
            explode(',', $sortOrder)
        ));
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

    /**
     * Compare two ranking rows according to a configured tie-break criterion.
     * Resolves head-to-head values and compares points, scores, ratios or
     * saved final positions. Missing final ranks (zero) sort after valid ones.
     *
     * @param string $criterion Criterion name from the ranking template.
     * @param RankingRow $a First team standing.
     * @param RankingRow $b Second team standing.
     * @param string $order Requested ordering direction.
     * @param array<int,RankingRow>|null $h2h Optional head-to-head standings for tied teams.
     * @return int Signed comparator result for uasort and tie detection.
     */
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
            // Zero means "not ranked" in legacy final-table records.
            // Unranked teams must appear after all saved positive positions.
            'finaltablerank' => [
                $a->finaltablerank > 0 ? $a->finaltablerank : PHP_INT_MAX,
                $b->finaltablerank > 0 ? $b->finaltablerank : PHP_INT_MAX,
                'ASC',
            ],
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

    /**
     * Copy ranking rows and attached team records before calculating a new table.
     * Prevents a ranking request from modifying the caller's cached seed rows or nested team objects while updating match results.
     *
     * @param array<int,RankingRow> $rows Original ranking team objects.
     * @return array<int,RankingRow> Independent copies with cloned team metadata.
     */
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
