<?php
/**
 * Read-model base for native Joomla 5/6 SportsManagement predictions.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

abstract class SportsManagementPredictionReadModel extends SportsManagementPredictionModel
{
    protected int $groupRanking = 0;
    protected int $rankingType = 0;

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);
        $this->groupRanking = $this->groupRank;
        $this->rankingType = $this->type;
    }

    public function getGroupRanking(): int
    {
        return $this->groupRanking;
    }

    public function getRankingType(): int
    {
        return $this->rankingType;
    }

    public function getPredictionProject(int $projectId = 0): ?object
    {
        $projectId = $projectId ?: $this->projectId;
        if ($projectId <= 0) {
            return null;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_project'))
            ->where($db->quoteName('id') . ' = ' . $projectId);
        $db->setQuery($query, 0, 1);
        $project = $db->loadObject();

        if (!$project) {
            return null;
        }

        if (($project->start_date ?? '') === '0000-00-00') {
            $query = $db->getQuery(true)
                ->select('MIN(' . $db->quoteName('round_date_first') . ')')
                ->from($db->quoteName('#__sportsmanagement_round'))
                ->where($db->quoteName('project_id') . ' = ' . $projectId);
            $db->setQuery($query);
            $project->start_date = $db->loadResult();
        }

        return $project;
    }

    public function getRoundNames(int $projectId = 0, string $ordering = 'ASC', ?array $roundIds = null): array
    {
        $projectId = $projectId ?: $this->projectId;
        if ($projectId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $ordering = strtoupper($ordering) === 'DESC' ? 'DESC' : 'ASC';
        $query = $db->getQuery(true)
            ->select("CONCAT_WS(':',id,alias) AS value")
            ->select($db->quoteName('name', 'text'))
            ->from($db->quoteName('#__sportsmanagement_round'))
            ->where($db->quoteName('project_id') . ' = ' . $projectId)
            ->order($db->quoteName('id') . ' ' . $ordering);

        $ids = array_values(array_filter(array_map('intval', (array) $roundIds)));
        if ($ids) {
            $query->where($db->quoteName('id') . ' IN (' . implode(',', $ids) . ')');
        }

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    public function getPredictionGroupList(): array
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([$db->quoteName('id', 'value'), $db->quoteName('name', 'text')])
            ->from($db->quoteName('#__sportsmanagement_prediction_groups'))
            ->order($db->quoteName('name') . ' ASC');
        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    public function getPredictionMembersList(array $config = [], array $avatarConfig = []): array
    {
        if ($this->predictionGameId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $name = empty($config['show_full_name']) ? 'username' : 'name';
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('pm.id', 'pmID'),
                $db->quoteName('pm.user_id'),
                $db->quoteName('pm.picture', 'avatar'),
                $db->quoteName('pm.show_profile'),
                $db->quoteName('pm.champ_tipp'),
                $db->quoteName('pm.final4_tipp'),
                $db->quoteName('pm.aliasName'),
                $db->quoteName('u.' . $name, 'name'),
                $db->quoteName('pg.id', 'pg_group_id'),
                $db->quoteName('pg.name', 'pg_group_name'),
            ])
            ->from($db->quoteName('#__sportsmanagement_prediction_member', 'pm'))
            ->join('INNER', $db->quoteName('#__users', 'u') . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('pm.user_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_prediction_groups', 'pg') . ' ON ' . $db->quoteName('pg.id') . ' = ' . $db->quoteName('pm.group_id'))
            ->where($db->quoteName('pm.prediction_id') . ' = ' . $this->predictionGameId)
            ->order($db->quoteName('pm.id') . ' ASC');

        if ($this->groupId > 0) {
            $query->where($db->quoteName('pm.group_id') . ' = ' . $this->groupId);
        }

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    public function getPredictionMembersResultsList(int $projectId, int $roundFrom, int $roundTo = 0, int $userId = 0): array
    {
        if ($this->predictionGameId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $roundFrom = max(1, $roundFrom);
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('m.id', 'matchID'),
                $db->quoteName('m.match_date'),
                $db->quoteName('m.team1_result', 'homeResult'),
                $db->quoteName('m.team2_result', 'awayResult'),
                $db->quoteName('m.team1_result_decision', 'homeDecision'),
                $db->quoteName('m.team2_result_decision', 'awayDecision'),
                $db->quoteName('m.team1_result_split', 'homeResultSplit'),
                $db->quoteName('m.team2_result_split', 'awayResultSplit'),
                $db->quoteName('m.team1_result_ot', 'homeResultOT'),
                $db->quoteName('m.team2_result_ot', 'awayResultOT'),
                $db->quoteName('m.team1_result_so', 'homeResultSO'),
                $db->quoteName('m.team2_result_so', 'awayResultSO'),
                $db->quoteName('pr.id', 'prID'),
                $db->quoteName('pr.user_id', 'prUserID'),
                $db->quoteName('pr.tipp', 'prTipp'),
                $db->quoteName('pr.tipp_home', 'prHomeTipp'),
                $db->quoteName('pr.tipp_away', 'prAwayTipp'),
                $db->quoteName('pr.joker', 'prJoker'),
                $db->quoteName('pr.points', 'prPoints'),
                $db->quoteName('pr.top', 'prTop'),
                $db->quoteName('pr.diff', 'prDiff'),
                $db->quoteName('pr.tend', 'prTend'),
                $db->quoteName('pm.id', 'pmID'),
                $db->quoteName('m.round_id', 'matchRoundId'),
            ])
            ->from($db->quoteName('#__sportsmanagement_match', 'm'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_round', 'r') . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_prediction_result', 'pr') . ' ON ' . $db->quoteName('pr.match_id') . ' = ' . $db->quoteName('m.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_prediction_member', 'pm') . ' ON ' . $db->quoteName('pm.user_id') . ' = ' . $db->quoteName('pr.user_id'))
            ->where($db->quoteName('r.id') . ' >= ' . $roundFrom)
            ->where($db->quoteName('pm.prediction_id') . ' = ' . $this->predictionGameId)
            ->where($db->quoteName('pr.prediction_id') . ' = ' . $this->predictionGameId)
            ->where('(' . $db->quoteName('m.cancel') . ' IS NULL OR ' . $db->quoteName('m.cancel') . ' = 0)')
            ->order($db->quoteName('pm.id') . ' ASC, ' . $db->quoteName('m.match_date') . ' ASC, ' . $db->quoteName('m.id') . ' ASC');

        if ($projectId > 0) {
            $query->where($db->quoteName('r.project_id') . ' = ' . $projectId);
        }
        if ($roundTo > 0) {
            $query->where($db->quoteName('r.id') . ' <= ' . $roundTo);
        }
        if ($userId > 0) {
            $query->where($db->quoteName('pr.user_id') . ' = ' . $userId);
        }

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    public function getProjectTeamInfo(int $projectTeamId): ?object
    {
        if ($projectTeamId <= 0) {
            return null;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                't.*',
                $db->quoteName('t.id', 'team_id'),
                $db->quoteName('t.picture', 'team_picture'),
                $db->quoteName('pt.division_id'),
                $db->quoteName('pt.picture', 'projectteam_picture'),
                $db->quoteName('c.logo_small'),
                $db->quoteName('c.logo_middle'),
                $db->quoteName('c.logo_big'),
                $db->quoteName('c.country'),
                "CONCAT_WS(':',t.id,t.alias) AS team_slug",
            ])
            ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't') . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_club', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('t.club_id'))
            ->where($db->quoteName('pt.id') . ' = ' . $projectTeamId);
        $db->setQuery($query, 0, 1);

        return $db->loadObject() ?: null;
    }

    public function createResultObject(int $home, int $away, int $tipp, int $tippHome, int $tippAway, bool $joker, int $homeDecision = 0, int $awayDecision = 0, int $matchRoundId = 0): object
    {
        return (object) [
            'team1_result' => $home,
            'team2_result' => $away,
            'team1_result_decision' => $homeDecision,
            'team2_result_decision' => $awayDecision,
            'tipp' => $tipp,
            'tipp_home' => $tippHome,
            'tipp_away' => $tippAway,
            'joker' => $joker,
            'matchRoundId' => $matchRoundId,
        ];
    }

    public function scorePredictionResult(object $project, object $result): int
    {
        if ((int) ($project->mode ?? 0) !== 0) {
            if (!isset($result->team1_result, $result->team2_result, $result->tipp)) {
                return 0;
            }

            $correct = ($result->team1_result > $result->team2_result && (string) $result->tipp === '1')
                || ($result->team1_result < $result->team2_result && (string) $result->tipp === '2')
                || ($result->team1_result == $result->team2_result && (string) $result->tipp === '0');

            return $correct ? (int) ($project->points_tipp ?? 0) : 0;
        }

        if (!isset($result->team1_result, $result->team2_result, $result->tipp_home, $result->tipp_away)) {
            return 0;
        }

        $rating = $this->rating($project, !empty($result->joker));
        if (empty($result->joker) && !empty($result->matchRoundId)) {
            $roundRatings = $this->roundRatings($project);
            if (isset($roundRatings[(int) $result->matchRoundId])) {
                $rating = $roundRatings[(int) $result->matchRoundId];
            }
        }

        return $this->score($rating, $result);
    }

    public function computeMembersRanking(array $rows, array $config): array
    {
        $keys = [];
        for ($i = 1; $i <= 5; $i++) {
            $value = (string) ($config['sort_order_' . $i] ?? '');
            if ($value !== '') {
                $keys[] = $value;
            }
        }
        $keys = $keys ?: ['points', 'correct_tips', 'correct_diffs', 'correct_tend', 'count_tips_p'];

        uasort($rows, static function (array $a, array $b) use ($keys): int {
            foreach ($keys as $key) {
                $result = match ($key) {
                    'points' => (int) ($b['totalPoints'] ?? 0) <=> (int) ($a['totalPoints'] ?? 0),
                    'correct_tips', 'correct_tipps' => (int) ($b['totalTop'] ?? 0) <=> (int) ($a['totalTop'] ?? 0),
                    'correct_diffs' => (int) ($b['totalDiff'] ?? 0) <=> (int) ($a['totalDiff'] ?? 0),
                    'correct_tend' => (int) ($b['totalTend'] ?? 0) <=> (int) ($a['totalTend'] ?? 0),
                    'count_tips_p' => (int) ($b['predictionsCount'] ?? 0) <=> (int) ($a['predictionsCount'] ?? 0),
                    'count_tips_m' => (int) ($a['predictionsCount'] ?? 0) <=> (int) ($b['predictionsCount'] ?? 0),
                    default => 0,
                };

                if ($result !== 0) {
                    return $result;
                }
            }

            return strcasecmp((string) ($a['membernameAtoZ'] ?? ''), (string) ($b['membernameAtoZ'] ?? ''));
        });

        $position = 0;
        $previousPoints = null;
        foreach ($rows as $key => $row) {
            $position++;
            $points = (int) ($row['totalPoints'] ?? 0);
            $rows[$key]['rank'] = $previousPoints !== null && $points === $previousPoints ? '-' : $position;
            $previousPoints = $points;
        }

        return $rows;
    }

    private function roundRatings(object $project): array
    {
        $predictionId = (int) ($project->prediction_id ?? $this->predictionGameId);
        if ($predictionId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                'round_id',
                'points_tipp',
                'points_correct_result',
                'points_correct_diff',
                'points_correct_draw',
                'points_correct_tendence',
            ])
            ->from($db->quoteName('#__sportsmanagement_prediction_tippround'))
            ->where($db->quoteName('prediction_id') . ' = ' . $predictionId)
            ->where($db->quoteName('published') . ' = 1');
        $db->setQuery($query);

        return $db->loadObjectList('round_id') ?: [];
    }

    private function rating(object $project, bool $joker): object
    {
        $suffix = $joker ? '_joker' : '';

        return (object) [
            'points_tipp' => (int) ($project->{'points_tipp' . $suffix} ?? 0),
            'points_correct_result' => (int) ($project->{'points_correct_result' . $suffix} ?? 0),
            'points_correct_diff' => (int) ($project->{'points_correct_diff' . $suffix} ?? 0),
            'points_correct_draw' => (int) ($project->{'points_correct_draw' . $suffix} ?? 0),
            'points_correct_tendence' => (int) ($project->{'points_correct_tendence' . $suffix} ?? 0),
        ];
    }

    private function score(object $rating, object $result): int
    {
        if ($result->team1_result == $result->tipp_home && $result->team2_result == $result->tipp_away) {
            return (int) $rating->points_correct_result;
        }
        if ($result->team1_result == $result->team2_result
            && ($result->team1_result - $result->team2_result) == ($result->tipp_home - $result->tipp_away)) {
            return (int) $rating->points_correct_draw;
        }
        if (($result->team1_result - $result->team2_result) == ($result->tipp_home - $result->tipp_away)) {
            return (int) $rating->points_correct_diff;
        }
        if ((($result->team1_result - $result->team2_result) > 0 && ($result->tipp_home - $result->tipp_away) > 0)
            || (($result->team1_result - $result->team2_result) < 0 && ($result->tipp_home - $result->tipp_away) < 0)) {
            return (int) $rating->points_correct_tendence;
        }

        return (int) $rating->points_tipp;
    }
}
