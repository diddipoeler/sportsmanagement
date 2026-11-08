<?php
/**
 * Native Joomla 5/6 ranking row value object.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Service;

\defined('_JEXEC') or die;

final class RankingRow
{
    public int $projectteamid = 0;
    public int $teamid = 0;
    public int $divisionid = 0;
    public int $rank = 0;
    public float $start_points = 0.0;
    public float $sum_points = 0.0;
    public float $neg_points = 0.0;
    public float $bonus_points = 0.0;
    public float $penalty_points = 0.0;
    public int $cnt_matches = 0;
    public int $cnt_won = 0;
    public int $cnt_draw = 0;
    public int $cnt_lost = 0;
    public int $cnt_won_home = 0;
    public int $cnt_draw_home = 0;
    public int $cnt_lost_home = 0;
    public int $cnt_won_away = 0;
    public int $cnt_draw_away = 0;
    public int $cnt_lost_away = 0;
    public int $cnt_wot = 0;
    public int $cnt_wso = 0;
    public int $cnt_lot = 0;
    public int $cnt_lso = 0;
    public int $cnt_wot_home = 0;
    public int $cnt_wso_home = 0;
    public int $cnt_lot_home = 0;
    public int $cnt_lso_home = 0;
    public int $cnt_wot_away = 0;
    public int $cnt_wso_away = 0;
    public int $cnt_lot_away = 0;
    public int $cnt_lso_away = 0;
    public float $sum_team1_result = 0.0;
    public float $sum_team2_result = 0.0;
    public float $sum_away_for = 0.0;
    public float $sum_team1_legs = 0.0;
    public float $sum_team2_legs = 0.0;
    public float $diff_team_results = 0.0;
    public float $diff_team_legs = 0.0;
    public float $sum_team1_matchpoint = 0.0;
    public float $sum_team2_matchpoint = 0.0;
    public float $diff_team_matchpoint = 0.0;
    public float $sum_team1_sets = 0.0;
    public float $sum_team2_sets = 0.0;
    public float $diff_team_sets = 0.0;
    public float $sum_team1_games = 0.0;
    public float $sum_team2_games = 0.0;
    public float $diff_team_games = 0.0;
    public float $sum_team1_balls = 0.0;
    public float $sum_team2_balls = 0.0;
    public float $diff_team_balls = 0.0;
    public float $shooterrings = 0.0;
    public array $shooterringsperround = [];
    public float $scorefor = 0.0;
    public float $scoreagainst = 0.0;
    public float $goalsfor = 0.0;
    public float $goalsagainst = 0.0;
    public float $points = 0.0;
    public float $winpoints = 0.0;
    public bool $is_in_score = true;
    public bool $use_finally = false;
    public int $finaltablerank = 0;
    public string $name = '';
    public ?object $team = null;
    public string $display_team_name = '';
    public string $logo_url = '';
    public string $team_url = '';
    public array $column_values = [];

    /**
     * Initialise a ranking row for one project-team relation.
     * Stores the selected project-team identifier while leaving all match, point and comparison counters at their declared zero defaults.
     *
     * @param int $projectTeamId Project-team record identifier, or zero for an unassigned row.
     * @return void
     */
    public function __construct(int $projectTeamId = 0)
    {
        $this->projectteamid = $projectTeamId;
    }

    /**
     * Return the identifier of the project team represented by this ranking row.
     * Provides the legacy ptid accessor expected by templates and ranking calculators.
     *
     * @return int Project-team relation identifier.
     */
    public function getPtid(): int
    {
        return $this->projectteamid;
    }

    /**
     * Return the underlying team identifier from this ranking row.
     * Exposes the team ID without confusing it with the project-team relation ID.
     *
     * @return int Underlying SportsManagement team identifier.
     */
    public function getTeamid(): int
    {
        return $this->teamid;
    }

    /**
     * Return the division associated with this project's ranking row.
     * Keeps division-aware ranking views compatible with their previous public getter.
     *
     * @return int Division identifier, or zero when not set.
     */
    public function getDivisionid(): int
    {
        return $this->divisionid;
    }

    /**
     * Return the display name assigned to this ranking team.
     * Reads the row's explicitly populated name field without changing its formatting.
     *
     * @return string Team name used in ranking presentations.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Calculate the team's points including optional starting points.
     * Adds the configured start-points adjustment when requested, otherwise returns points accumulated from played matches only.
     *
     * @param bool $includeStart Whether the starting-point adjustment should be included.
     * @return float Effective ranking points under the selected policy.
     */
    public function getPoints(bool $includeStart = true): float
    {
        return $this->sum_points + ($includeStart ? $this->start_points : 0.0);
    }

    /**
     * Calculate the percentage of played matches that the team won.
     * Uses wins divided by all completed wins, losses and draws, returning zero for teams with no results.
     *
     * @return float Win percentage in the range from zero to one hundred.
     */
    public function winPct(): float
    {
        $games = $this->cnt_won + $this->cnt_lost + $this->cnt_draw;
        return $games > 0 ? ($this->cnt_won / $games) * 100.0 : 0.0;
    }

    /**
     * Compute the ratio between points scored and points conceded.
     * Divides the two aggregate team-score counters and uses a denominator of one if the opposing total is zero.
     *
     * @return float Scored-to-conceded result ratio.
     */
    public function scoreAvg(): float
    {
        return $this->sum_team1_result / ($this->sum_team2_result == 0.0 ? 1.0 : $this->sum_team2_result);
    }

    /**
     * Express the team-score ratio as a percentage.
     * Multiplies scoreAvg by one hundred to preserve the established ranking percentage convention.
     *
     * @return float Score ratio expressed as a percentage.
     */
    public function scorePct(): float
    {
        return $this->scoreAvg() * 100.0;
    }

    /**
     * Compute the ratio of legs won to legs conceded.
     * Uses the sum of recorded leg scores and substitutes one as the denominator for zero conceded legs.
     *
     * @return float Ratio of legs for and against.
     */
    public function legsRatio(): float
    {
        return $this->sum_team1_legs / ($this->sum_team2_legs == 0.0 ? 1.0 : $this->sum_team2_legs);
    }

    /**
     * Compute the ratio of match points to negative-point adjustments.
     * Excludes starting points and divides by the negative-points total, treating a zero denominator as one.
     *
     * @return float Ratio of scored points to negative points.
     */
    public function pointsRatio(): float
    {
        return $this->getPoints(false) / ($this->neg_points == 0.0 ? 1.0 : $this->neg_points);
    }

    /**
     * Return the average number of goals or scores made per match.
     * Divides the aggregate team result by the match count and yields the aggregate unchanged when no matches have been recorded.
     *
     * @return float Goals-for or score-for average per match.
     */
    public function getGFA(): float
    {
        return $this->sum_team1_result / ($this->cnt_matches > 0 ? $this->cnt_matches : 1);
    }

    /**
     * Return the average number of goals or scores conceded per match.
     * Uses the opponent result total divided by played matches and avoids division by zero for empty standings.
     *
     * @return float Goals-against or score-against average per match.
     */
    public function getGAA(): float
    {
        return $this->sum_team2_result / ($this->cnt_matches > 0 ? $this->cnt_matches : 1);
    }

    /**
     * Calculate ranking points gained per recorded match.
     * Uses earned points without starting-point adjustments and substitutes one for an empty match count.
     *
     * @return float Points per game.
     */
    public function getPPG(): float
    {
        return $this->getPoints(false) / ($this->cnt_matches > 0 ? $this->cnt_matches : 1);
    }

    /**
     * Calculate earned points as a percentage of the maximum available.
     * Divides earned points by played matches times winpoints where positive, otherwise preserves the raw earned-points value.
     *
     * @return float Percentage of maximum points, or raw points when the maximum is zero.
     */
    public function getPPP(): float
    {
        $maximum = $this->cnt_matches * $this->winpoints;
        return $maximum > 0.0 ? ($this->getPoints(false) / $maximum) * 100.0 : $this->getPoints(false);
    }
}
