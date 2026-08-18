<?php
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

    public function __construct(int $projectTeamId = 0)
    {
        $this->projectteamid = $projectTeamId;
    }

    public function getPtid(): int
    {
        return $this->projectteamid;
    }

    public function getTeamid(): int
    {
        return $this->teamid;
    }

    public function getDivisionid(): int
    {
        return $this->divisionid;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPoints(bool $includeStart = true): float
    {
        return $this->sum_points + ($includeStart ? $this->start_points : 0.0);
    }

    public function winPct(): float
    {
        $games = $this->cnt_won + $this->cnt_lost + $this->cnt_draw;
        return $games > 0 ? ($this->cnt_won / $games) * 100.0 : 0.0;
    }

    public function scoreAvg(): float
    {
        return $this->sum_team1_result / ($this->sum_team2_result == 0.0 ? 1.0 : $this->sum_team2_result);
    }

    public function scorePct(): float
    {
        return $this->scoreAvg() * 100.0;
    }

    public function legsRatio(): float
    {
        return $this->sum_team1_legs / ($this->sum_team2_legs == 0.0 ? 1.0 : $this->sum_team2_legs);
    }

    public function pointsRatio(): float
    {
        return $this->getPoints(false) / ($this->neg_points == 0.0 ? 1.0 : $this->neg_points);
    }

    public function getGFA(): float
    {
        return $this->sum_team1_result / ($this->cnt_matches > 0 ? $this->cnt_matches : 1);
    }

    public function getGAA(): float
    {
        return $this->sum_team2_result / ($this->cnt_matches > 0 ? $this->cnt_matches : 1);
    }

    public function getPPG(): float
    {
        return $this->getPoints(false) / ($this->cnt_matches > 0 ? $this->cnt_matches : 1);
    }

    public function getPPP(): float
    {
        $maximum = $this->cnt_matches * $this->winpoints;
        return $maximum > 0.0 ? ($this->getPoints(false) / $maximum) * 100.0 : $this->getPoints(false);
    }
}
