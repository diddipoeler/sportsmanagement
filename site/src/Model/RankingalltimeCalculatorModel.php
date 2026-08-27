<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

/**
 * Native Joomla 5/6 all-time ranking calculator and legacy compatibility model.
 *
 * Database reads are delegated to RankingalltimeModel. This class owns only
 * the in-memory aggregation/sorting state historically kept in
 * sportsmanagementModelRankingAllTime.
 */
final class RankingalltimeCalculatorModel extends SportsManagementModel
{
    public array $teams = [];
    public array $_teams = [];
    public array $_matches = [];
    public string $alltimepoints = '3,1,0';
    public bool $debug_info = false;
    public int $projectid = 0;
    public int $leagueid = 0;
    public string $project_ids = '';
    public array $project_ids_array = [];

    public static array $rankingalltimenotes = [];
    public static array $rankingalltimewarnings = [];
    public static array $rankingalltimetips = [];

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);

        $this->alltimepoints = Factory::getApplication()->getInput()->getString('points', '3,1,0');
    }

    /**
     * Legacy-compatible project list reader.
     */
    public function getAllProjectNames($useLeagueChampion = 0): array
    {
        return $this->reader()->getProjectNames((int) $useLeagueChampion);
    }

    /**
     * Legacy-compatible project id reader.
     */
    public function getAllProject($useLeagueChampion = 0): array
    {
        $ids = $this->reader()->getProjectIds((int) $useLeagueChampion);
        $this->project_ids_array = $ids;
        $this->project_ids = implode(',', $ids);

        return $ids;
    }

    /**
     * Legacy-compatible team reader. Accept both the historic CSV string and
     * the native array form.
     */
    public function getAllTeams(array|string $projectIds): array
    {
        $this->_teams = $this->reader()->getAllTeams($this->normaliseProjectIds($projectIds));

        return $this->_teams;
    }

    /**
     * Legacy-compatible team accumulator builder.
     */
    public function getAllTeamsIndexedByPtid(array|string $projectIds): array
    {
        $reader = $this->reader();
        $this->_teams = $reader->getAllTeams($this->normaliseProjectIds($projectIds));
        $this->teams = $reader->initialiseTeams($this->_teams);
        self::$rankingalltimetips[] = 'Wir verarbeiten ' . count($this->teams) . ' Vereine !';

        return $this->teams;
    }

    /**
     * Legacy-compatible match reader.
     */
    public function getAllMatches(array|string $projectIds): array
    {
        $this->_matches = $this->reader()->getAllMatches($this->normaliseProjectIds($projectIds));

        return $this->_matches;
    }

    public function getColors(string $configColors = ''): array
    {
        return $this->reader()->parseColors($configColors);
    }

    public function getAllTimeParams(bool $comeFromMenu = false, array $config = []): array
    {
        return $config;
    }

    /**
     * Apply the established all-time match aggregation rules to the prepared
     * team accumulator objects.
     */
    public function getAllTimeRanking($useNegPointsRankingAllTime = 0): array
    {
        [$winPoints, $drawPoints, $lossPoints] = $this->pointValues();
        $showNegPoints = $lossPoints != 0.0;

        foreach ($this->_matches as $match) {
            $homeId = (int) ($match->team1_id ?? 0);
            $awayId = (int) ($match->team2_id ?? 0);

            if ($homeId <= 0 || $awayId <= 0 || !isset($this->teams[$homeId], $this->teams[$awayId])) {
                continue;
            }

            $resultType = (int) ($match->match_result_type ?? 0);
            $decision = (int) ($match->decision ?? 0);

            if ($decision === 0) {
                $homeScore = (float) ($match->home_score ?? 0);
                $awayScore = (float) ($match->away_score ?? 0);
                $leg1 = (float) ($match->l1 ?? 0);
                $leg2 = (float) ($match->l2 ?? 0);
            } else {
                $homeScore = (float) ($match->home_score_decision ?? 0);
                $awayScore = (float) ($match->away_score_decision ?? 0);
                $leg1 = 0.0;
                $leg2 = 0.0;
            }

            $home = $this->teams[$homeId];
            $away = $this->teams[$awayId];

            $home->cnt_matches++;
            $away->cnt_matches++;

            $homeOt = (float) ($match->home_score_ot ?? 0);
            $awayOt = (float) ($match->away_score_ot ?? 0);
            $homeSo = (float) ($match->home_score_so ?? 0);
            $awaySo = (float) ($match->away_score_so ?? 0);

            if ($decision !== 1) {
                if ($homeScore > $awayScore) {
                    switch ($resultType) {
                        case 0:
                            $home->cnt_won++;
                            $home->cnt_won_home++;
                            $away->cnt_lost++;
                            $away->cnt_lost_away++;
                            break;

                        case 1:
                            $home->cnt_wot++;
                            $home->cnt_wot_home++;
                            $home->cnt_won++;
                            $home->cnt_won_home++;
                            $away->cnt_lot++;
                            $away->cnt_lot_away++;
                            break;

                        case 2:
                            $home->cnt_wso++;
                            $home->cnt_wso_home++;
                            $home->cnt_won++;
                            $home->cnt_won_home++;
                            $away->cnt_lso++;
                            $away->cnt_lso_away++;
                            $away->cnt_lot++;
                            $away->cnt_lot_away++;
                            break;
                    }

                    $home->sum_points += $winPoints;
                    $away->sum_points += $lossPoints;

                    if ($showNegPoints) {
                        $home->neg_points += $lossPoints;
                        $away->neg_points += $winPoints;
                    }
                } elseif ($homeScore == $awayScore) {
                    switch ($resultType) {
                        case 0:
                            $home->cnt_draw++;
                            $home->cnt_draw_home++;
                            $away->cnt_draw++;
                            $away->cnt_draw_away++;
                            break;

                        case 1:
                            if ($homeOt > $awayOt) {
                                $home->cnt_won++;
                                $home->cnt_won_home++;
                                $home->cnt_wot++;
                                $home->cnt_wot_home++;
                                $away->cnt_lost++;
                                $away->cnt_lost_away++;
                                $away->cnt_lot++;
                                $away->cnt_lot_away++;
                            } elseif ($homeOt < $awayOt) {
                                // Preserve the historic all-time counter behaviour.
                                $away->cnt_won++;
                                $away->cnt_won_home++;
                                $away->cnt_wot++;
                                $away->cnt_wot_home++;
                                $home->cnt_lost++;
                                $home->cnt_lost_away++;
                                $home->cnt_lot++;
                                $home->cnt_lot_away++;
                            }
                            break;

                        case 2:
                            if ($homeSo > $awaySo) {
                                $home->cnt_won++;
                                $home->cnt_won_home++;
                                $home->cnt_wso++;
                                $home->cnt_wso_home++;
                                $away->cnt_lost++;
                                $away->cnt_lost_away++;
                                $away->cnt_lso++;
                                $away->cnt_lso_away++;
                            } elseif ($homeSo < $awaySo) {
                                // Preserve the historic all-time counter behaviour.
                                $away->cnt_won++;
                                $away->cnt_won_home++;
                                $away->cnt_wso++;
                                $away->cnt_wso_home++;
                                $home->cnt_lost++;
                                $home->cnt_lost_away++;
                                $home->cnt_lso++;
                                $home->cnt_lso_away++;
                            }
                            break;
                    }

                    $home->sum_points += $drawPoints;
                    $away->sum_points += $drawPoints;

                    if ($showNegPoints) {
                        $home->neg_points += $winPoints - $drawPoints;
                        $away->neg_points += $winPoints - $drawPoints;
                    }
                } else {
                    switch ($resultType) {
                        case 0:
                            $home->cnt_lost++;
                            $home->cnt_lost_home++;
                            $away->cnt_won++;
                            $away->cnt_won_away++;
                            break;

                        case 1:
                            $home->cnt_lot++;
                            $home->cnt_lot_home++;
                            $away->cnt_wot++;
                            $away->cnt_wot_away++;
                            $away->cnt_won++;
                            $away->cnt_won_away++;
                            break;

                        case 2:
                            $home->cnt_lso++;
                            $home->cnt_lso_home++;
                            $home->cnt_lot++;
                            $home->cnt_lot_home++;
                            $away->cnt_wso++;
                            $away->cnt_wso_away++;
                            $away->cnt_won++;
                            $away->cnt_won_away++;
                            break;
                    }

                    $home->sum_points += $lossPoints;
                    $away->sum_points += $winPoints;

                    if ($showNegPoints) {
                        $home->neg_points += $winPoints;
                        $away->neg_points += $lossPoints;
                    }
                }
            } else {
                if ($showNegPoints) {
                    $home->neg_points += $lossPoints;
                    $away->neg_points += $lossPoints;
                }

                switch ((int) ($match->team_won ?? 0)) {
                    case 0:
                        $home->cnt_lost++;
                        $away->cnt_lost++;
                        break;

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
                        $away->cnt_lost_home++;
                        $home->cnt_lost_home++;
                        break;

                    case 4:
                        $home->cnt_won++;
                        $away->cnt_won++;
                        $home->sum_points += $winPoints;
                        $away->sum_points += $winPoints;
                        break;
                }
            }

            $home->winpoints = $winPoints;

            $homeBonus = (float) ($match->home_bonus ?? 0);
            $awayBonus = (float) ($match->away_bonus ?? 0);
            $home->sum_points += $homeBonus;
            $home->bonus_points += $homeBonus;
            $away->sum_points += $awayBonus;
            $away->bonus_points += $awayBonus;

            $home->sum_team1_result += $homeScore;
            $home->sum_team2_result += $awayScore;
            $home->diff_team_results = $home->sum_team1_result - $home->sum_team2_result;
            $home->sum_team1_legs += $leg1;
            $home->sum_team2_legs += $leg2;
            $home->diff_team_legs = $home->sum_team1_legs - $home->sum_team2_legs;

            $away->sum_team1_result += $awayScore;
            $away->sum_team2_result += $homeScore;
            $away->diff_team_results = $away->sum_team1_result - $away->sum_team2_result;
            $away->sum_team1_legs += $leg2;
            $away->sum_team2_legs += $leg1;
            $away->diff_team_legs = $away->sum_team1_legs - $away->sum_team2_legs;
            $away->sum_away_for += $awayScore;
        }

        if ((int) $useNegPointsRankingAllTime === 1) {
            foreach ($this->_teams as $team) {
                $teamId = (int) ($team->team_id ?? 0);
                if ($teamId > 0 && isset($this->teams[$teamId])) {
                    $this->teams[$teamId]->sum_points += (float) ($team->start_points ?? 0);
                }
            }
        }

        return $this->teams;
    }

    /**
     * Build the property-based ranking objects expected by the existing
     * templates and assign dense ranks after sorting.
     */
    public function getCurrentRanking(array $rankingOrder = []): array
    {
        $this->ensureRankingTeamClass();
        $ranking = [];

        foreach ($this->teams as $team) {
            $item = new \JSMRankingTeamClass(0);
            $item->cnt_matches = (int) ($team->cnt_matches ?? 0);
            $item->sum_points = (float) ($team->sum_points ?? 0);
            $item->neg_points = (float) ($team->neg_points ?? 0);
            $item->bonus_points = (float) ($team->bonus_points ?? 0);
            $item->cnt_won_home = (int) ($team->cnt_won_home ?? 0);
            $item->cnt_draw_home = (int) ($team->cnt_draw_home ?? 0);
            $item->cnt_lost_home = (int) ($team->cnt_lost_home ?? 0);
            $item->cnt_won_away = (int) ($team->cnt_won_away ?? 0);
            $item->cnt_draw_away = (int) ($team->cnt_draw_away ?? 0);
            $item->cnt_lost_away = (int) ($team->cnt_lost_away ?? 0);
            $item->cnt_won = (int) ($team->cnt_won ?? 0);
            $item->cnt_draw = (int) ($team->cnt_draw ?? 0);
            $item->cnt_lost = (int) ($team->cnt_lost ?? 0);
            $item->cnt_wot = (int) ($team->cnt_wot ?? 0);
            $item->cnt_lot = (int) ($team->cnt_lot ?? 0);
            $item->cnt_wso = (int) ($team->cnt_wso ?? 0);
            $item->cnt_lso = (int) ($team->cnt_lso ?? 0);
            $item->sum_team1_result = (float) ($team->sum_team1_result ?? 0);
            $item->sum_team2_result = (float) ($team->sum_team2_result ?? 0);
            $item->sum_away_for = (float) ($team->sum_away_for ?? 0);
            $item->diff_team_results = (float) ($team->diff_team_results ?? 0);
            $item->sum_team1_legs = (float) ($team->sum_team1_legs ?? 0);
            $item->sum_team2_legs = (float) ($team->sum_team2_legs ?? 0);
            $item->diff_team_legs = (float) ($team->diff_team_legs ?? 0);
            $item->_is_in_score = (int) ($team->is_in_score ?? 0);
            $item->_teamid = (int) ($team->team_id ?? 0);
            $item->_name = (string) ($team->name ?? '');
            $item->_ptid = (int) ($team->projectteamid ?? 0);
            $item->_pid = (int) ($team->project_id ?? 0);
            $item->_startpoints = (float) ($team->start_points ?? 0);
            $item->start_points = (float) ($team->start_points ?? 0);

            if ($item->_teamid > 0) {
                $ranking[$item->_teamid] = $item;
            }
        }

        $ranking = $this->sortRanking($ranking, $rankingOrder);

        $rank = 0;
        $previousPoints = null;
        foreach ($ranking as $row) {
            if ($previousPoints === null || (float) $row->sum_points != $previousPoints) {
                $rank++;
                $previousPoints = (float) $row->sum_points;
            }
            $row->rank = $rank;
        }

        return [0 => $ranking];
    }

    private function sortRanking(array $ranking, array $rankingOrder): array
    {
        $input = Factory::getApplication()->getInput();
        $requestedOrder = $this->normaliseCriterion($input->getString('order', ''));
        $requestedDirection = strtoupper($input->getCmd('dir', 'DESC')) === 'ASC' ? 1 : -1;

        $criteria = [];
        if ($requestedOrder !== '') {
            $criteria[] = [$requestedOrder, $requestedDirection];
        } else {
            foreach ($rankingOrder as $criterion) {
                $criterion = $this->normaliseCriterion((string) $criterion);
                if ($criterion === '') {
                    continue;
                }

                $direction = -1;
                if (str_ends_with($criterion, 'asc')) {
                    $criterion = substr($criterion, 0, -3);
                    $direction = 1;
                } elseif (str_ends_with($criterion, 'desc')) {
                    $criterion = substr($criterion, 0, -4);
                    $direction = -1;
                }
                $criteria[] = [$criterion, $direction];
            }
        }

        if (!$criteria) {
            $criteria = [['points', -1]];
        }

        uasort($ranking, function (object $first, object $second) use ($criteria): int {
            foreach ($criteria as [$criterion, $direction]) {
                $comparison = $this->compareCriterion($first, $second, $criterion);
                if ($comparison !== 0) {
                    return $comparison * $direction;
                }
            }

            return strcasecmp((string) ($first->_name ?? ''), (string) ($second->_name ?? ''));
        });

        return $ranking;
    }

    private function compareCriterion(object $first, object $second, string $criterion): int
    {
        $field = match ($criterion) {
            'played' => 'cnt_matches',
            'won', 'wins' => 'cnt_won',
            'draw', 'draws', 'tie', 'ties' => 'cnt_draw',
            'loss', 'losses' => 'cnt_lost',
            'goalsp', 'goalsfor', 'scorefor', 'for' => 'sum_team1_result',
            'goalsagainst', 'scoreagainst', 'against' => 'sum_team2_result',
            'diff' => 'diff_team_results',
            'legsdiff' => 'diff_team_legs',
            'points' => 'sum_points',
            'start' => 'start_points',
            'bonus' => 'bonus_points',
            'negpoints' => 'neg_points',
            'name' => '_name',
            default => '',
        };

        if ($field !== '') {
            $left = $first->{$field} ?? 0;
            $right = $second->{$field} ?? 0;

            if ($criterion === 'name') {
                return strcasecmp((string) $left, (string) $right);
            }

            return (float) $left <=> (float) $right;
        }

        $left = match ($criterion) {
            'winpct' => $this->winPercentage($first),
            'quot' => $this->ratio((float) ($first->sum_team1_result ?? 0), (float) ($first->sum_team2_result ?? 0)),
            'legsratio' => $this->ratio((float) ($first->sum_team1_legs ?? 0), (float) ($first->sum_team2_legs ?? 0)),
            'pointsratio' => $this->ratio((float) ($first->sum_points ?? 0), (float) ($first->cnt_matches ?? 0)),
            default => 0.0,
        };
        $right = match ($criterion) {
            'winpct' => $this->winPercentage($second),
            'quot' => $this->ratio((float) ($second->sum_team1_result ?? 0), (float) ($second->sum_team2_result ?? 0)),
            'legsratio' => $this->ratio((float) ($second->sum_team1_legs ?? 0), (float) ($second->sum_team2_legs ?? 0)),
            'pointsratio' => $this->ratio((float) ($second->sum_points ?? 0), (float) ($second->cnt_matches ?? 0)),
            default => 0.0,
        };

        return $left <=> $right;
    }

    private function winPercentage(object $team): float
    {
        $games = (int) ($team->cnt_won ?? 0) + (int) ($team->cnt_draw ?? 0) + (int) ($team->cnt_lost ?? 0);

        return $games > 0 ? ((float) ($team->cnt_won ?? 0) / $games) * 100.0 : 0.0;
    }

    private function ratio(float $numerator, float $denominator): float
    {
        return $denominator != 0.0 ? $numerator / $denominator : $numerator;
    }

    private function normaliseCriterion(string $criterion): string
    {
        return strtolower(trim(str_replace('jl_', '', $criterion)));
    }

    private function pointValues(): array
    {
        $values = array_map('trim', explode(',', $this->alltimepoints));

        return [
            isset($values[0]) && is_numeric($values[0]) ? (float) $values[0] : 3.0,
            isset($values[1]) && is_numeric($values[1]) ? (float) $values[1] : 1.0,
            isset($values[2]) && is_numeric($values[2]) ? (float) $values[2] : 0.0,
        ];
    }

    private function reader(): RankingalltimeModel
    {
        if (!class_exists(RankingalltimeModel::class)) {
            require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/RankingalltimeModel.php';
        }

        $reader = new RankingalltimeModel();
        $reader->setDatabaseSelector(Factory::getApplication()->getInput()->getInt('cfg_which_database', 0));

        return $reader;
    }

    private function normaliseProjectIds(array|string $projectIds): array
    {
        if (is_string($projectIds)) {
            $projectIds = explode(',', $projectIds);
        }

        return array_values(array_unique(array_filter(
            array_map('intval', $projectIds),
            static fn (int $id): bool => $id > 0
        )));
    }

    private function ensureRankingTeamClass(): void
    {
        if (class_exists('JSMRankingTeamClass', false)) {
            return;
        }

        $helper = JPATH_SITE . '/components/com_sportsmanagement/helpers/ranking.php';
        if (is_file($helper)) {
            require_once $helper;
        }

        if (!class_exists('JSMRankingTeamClass', false)) {
            throw new \RuntimeException('SportsManagement ranking helper is unavailable.', 500);
        }
    }
}
