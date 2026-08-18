<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use DateTimeImmutable;
use DateTimeZone;
use Joomla\CMS\Factory;

final class PredictionusersModel extends SportsManagementPredictionReadModel
{
    public function getProjectId(): int
    {
        $projectId = parent::getProjectId();
        if ($projectId <= 0) {
            return 0;
        }

        foreach ($this->getPredictionProjects() as $project) {
            if ((int) ($project->project_id ?? 0) === $projectId) {
                return $projectId;
            }
        }

        $this->projectId = 0;
        return 0;
    }

    public function getRoundId(): int
    {
        $roundId = parent::getRoundId();
        $projectId = $this->getProjectId();
        if ($roundId <= 0 || $projectId <= 0) {
            return 0;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__sportsmanagement_round'))
            ->where($db->quoteName('id') . ' = ' . $roundId)
            ->where($db->quoteName('project_id') . ' = ' . $projectId);
        $db->setQuery($query, 0, 1);

        if (!$db->loadResult()) {
            $this->roundId = 0;
        }

        return $this->roundId;
    }

    public function getSelectedMemberNumericId(): int
    {
        $member = $this->getPredictionMember();
        return $this->extractMemberId($member->pmID ?? $member->id ?? 0);
    }

    public function isPredictionMember(): bool
    {
        $userId = (int) Factory::getApplication()->getIdentity()->id;
        if ($userId <= 0 || $this->predictionGameId <= 0) {
            return false;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__sportsmanagement_prediction_member'))
            ->where($db->quoteName('prediction_id') . ' = ' . $this->predictionGameId)
            ->where($db->quoteName('user_id') . ' = ' . $userId);
        $db->setQuery($query, 0, 1);
        return (bool) $db->loadResult();
    }

    public function getMemberOptions(array $config = []): array
    {
        if ($this->predictionGameId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $nameField = !empty($config['show_full_name']) ? 'name' : 'username';
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('pm.id', 'value'),
                $db->quoteName('u.' . $nameField, 'text'),
                $db->quoteName('pm.user_id'),
            ])
            ->from($db->quoteName('#__sportsmanagement_prediction_member', 'pm'))
            ->join('INNER', $db->quoteName('#__users', 'u') . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('pm.user_id'))
            ->where($db->quoteName('pm.prediction_id') . ' = ' . $this->predictionGameId)
            ->order($db->quoteName('u.' . $nameField) . ' ASC');

        if (!$this->isAllowedAdmin()) {
            $userId = (int) Factory::getApplication()->getIdentity()->id;
            if ($userId <= 0) {
                return [];
            }
            $query->where($db->quoteName('pm.user_id') . ' = ' . $userId);
        }

        $db->setQuery($query);
        return $db->loadObjectList() ?: [];
    }

    public function getProjectOptions(bool $includeAll = true): array
    {
        $options = [];
        if ($includeAll) {
            $options[] = (object) ['value' => 0, 'text' => 'JALL'];
        }

        foreach ($this->getPredictionProjects() as $project) {
            $options[] = (object) [
                'value' => (int) ($project->project_id ?? 0),
                'text' => (string) ($project->projectName ?? $project->project_id ?? ''),
            ];
        }

        return $options;
    }

    public function getMemberStats(object $member): array
    {
        $memberId = $this->extractMemberId($member->pmID ?? $member->id ?? 0);
        $userId = (int) ($member->user_id ?? 0);
        $projectId = $this->getProjectId();
        $stats = [
            'rank' => 0,
            'predictionsCount' => 0,
            'totalPoints' => 0,
            'lastPoints' => 0,
            'totalTop' => 0,
            'totalDiff' => 0,
            'totalTend' => 0,
            'totalJoker' => 0,
            'averagePoints' => 0.0,
        ];

        if ($memberId <= 0 || $userId <= 0) {
            return $stats;
        }

        $rows = $this->getPredictionMembersResultsList($projectId, 1, 0, $userId);
        $latestRound = 0;
        foreach ($rows as $row) {
            if (!$this->hasPlayedResult($row)) {
                continue;
            }

            $stats['predictionsCount']++;
            $stats['totalPoints'] += (int) ($row->prPoints ?? 0);
            $stats['totalTop'] += (int) ($row->prTop ?? 0);
            $stats['totalDiff'] += (int) ($row->prDiff ?? 0);
            $stats['totalTend'] += (int) ($row->prTend ?? 0);
            $stats['totalJoker'] += (int) ($row->prJoker ?? 0);

            $rowRound = (int) ($row->matchRoundId ?? 0);
            if ($rowRound > $latestRound) {
                $latestRound = $rowRound;
                $stats['lastPoints'] = (int) ($row->prPoints ?? 0);
            } elseif ($rowRound === $latestRound) {
                $stats['lastPoints'] += (int) ($row->prPoints ?? 0);
            }
        }

        if ($stats['predictionsCount'] > 0) {
            $stats['averagePoints'] = round($stats['totalPoints'] / $stats['predictionsCount'], 2);
        }

        $stats['rank'] = $this->getOverallMemberRank($memberId, $projectId);
        return $stats;
    }

    public function getFavouriteTeams(object $member): array
    {
        return $this->resolveTeamSelections((string) ($member->fav_team ?? ''), false, false, (int) ($member->user_id ?? 0));
    }

    public function getChampionTips(object $member): array
    {
        return $this->resolveTeamSelections((string) ($member->champ_tipp ?? ''), false, true, (int) ($member->user_id ?? 0));
    }

    public function getFinal4Tips(object $member): array
    {
        return $this->resolveTeamSelections((string) ($member->final4_tipp ?? ''), true, true, (int) ($member->user_id ?? 0));
    }

    public function getPointsChartData(object $member): array
    {
        $userId = (int) ($member->user_id ?? 0);
        if ($userId <= 0 || $this->predictionGameId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('p.id', 'project_id'),
                $db->quoteName('p.name', 'project_name'),
                $db->quoteName('r.id', 'round_id'),
                $db->quoteName('r.name', 'round_name'),
                'COALESCE(SUM(' . $db->quoteName('pr.points') . '), 0) AS points',
            ])
            ->from($db->quoteName('#__sportsmanagement_prediction_result', 'pr'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_match', 'm') . ' ON ' . $db->quoteName('m.id') . ' = ' . $db->quoteName('pr.match_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_round', 'r') . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('r.project_id'))
            ->where($db->quoteName('pr.prediction_id') . ' = ' . $this->predictionGameId)
            ->where($db->quoteName('pr.user_id') . ' = ' . $userId)
            ->where('(' . $db->quoteName('m.cancel') . ' IS NULL OR ' . $db->quoteName('m.cancel') . ' = 0)')
            ->group([$db->quoteName('p.id'), $db->quoteName('p.name'), $db->quoteName('r.id'), $db->quoteName('r.name')])
            ->order([$db->quoteName('p.id') . ' ASC', $db->quoteName('r.id') . ' ASC']);

        if ($this->getProjectId() > 0) {
            $query->where($db->quoteName('r.project_id') . ' = ' . $this->getProjectId());
        }

        $db->setQuery($query);
        $rows = $db->loadObjectList() ?: [];
        $multipleProjects = $this->getProjectId() <= 0;

        return array_map(static function (object $row) use ($multipleProjects): array {
            $label = (string) ($row->round_name ?? '');
            if ($multipleProjects) {
                $label = trim((string) ($row->project_name ?? '') . ' – ' . $label, ' –');
            }
            return ['label' => $label, 'value' => (int) ($row->points ?? 0)];
        }, $rows);
    }

    public function getRankingChartData(object $member): array
    {
        $memberId = $this->extractMemberId($member->pmID ?? $member->id ?? 0);
        if ($memberId <= 0 || $this->predictionGameId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('p.id', 'project_id'),
                $db->quoteName('p.name', 'project_name'),
                $db->quoteName('r.id', 'round_id'),
                $db->quoteName('r.name', 'round_name'),
                $db->quoteName('pm.id', 'member_id'),
                'COALESCE(SUM(' . $db->quoteName('pr.points') . '), 0) AS points',
            ])
            ->from($db->quoteName('#__sportsmanagement_prediction_member', 'pm'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_prediction_result', 'pr') . ' ON ' . $db->quoteName('pr.user_id') . ' = ' . $db->quoteName('pm.user_id') . ' AND ' . $db->quoteName('pr.prediction_id') . ' = ' . $this->predictionGameId)
            ->join('INNER', $db->quoteName('#__sportsmanagement_match', 'm') . ' ON ' . $db->quoteName('m.id') . ' = ' . $db->quoteName('pr.match_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_round', 'r') . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('r.project_id'))
            ->where($db->quoteName('pm.prediction_id') . ' = ' . $this->predictionGameId)
            ->where('(' . $db->quoteName('m.cancel') . ' IS NULL OR ' . $db->quoteName('m.cancel') . ' = 0)')
            ->group([$db->quoteName('p.id'), $db->quoteName('p.name'), $db->quoteName('r.id'), $db->quoteName('r.name'), $db->quoteName('pm.id')])
            ->order([$db->quoteName('p.id') . ' ASC', $db->quoteName('r.id') . ' ASC', 'points DESC', $db->quoteName('pm.id') . ' ASC']);

        if ($this->getProjectId() > 0) {
            $query->where($db->quoteName('r.project_id') . ' = ' . $this->getProjectId());
        }

        $db->setQuery($query);
        $rows = $db->loadObjectList() ?: [];
        $byRound = [];
        foreach ($rows as $row) {
            $roundKey = (int) $row->project_id . ':' . (int) $row->round_id;
            $byRound[$roundKey]['project_name'] = (string) $row->project_name;
            $byRound[$roundKey]['round_name'] = (string) $row->round_name;
            $byRound[$roundKey]['rows'][] = $row;
        }

        $multipleProjects = $this->getProjectId() <= 0;
        $series = [];
        foreach ($byRound as $round) {
            $rank = 0;
            $position = 0;
            $previousPoints = null;
            foreach ($round['rows'] as $row) {
                $position++;
                $points = (int) $row->points;
                if ($previousPoints === null || $points !== $previousPoints) {
                    $rank = $position;
                    $previousPoints = $points;
                }
                if ((int) $row->member_id === $memberId) {
                    $label = (string) $round['round_name'];
                    if ($multipleProjects) {
                        $label = trim((string) $round['project_name'] . ' – ' . $label, ' –');
                    }
                    $series[] = [
                        'label' => $label,
                        'rank' => $rank,
                        'members' => count($round['rows']),
                    ];
                    break;
                }
            }
        }

        return $series;
    }

    public function getAvatarPath(object $member, array $config): string
    {
        if (empty($config['show_photo'])) {
            return '';
        }

        $source = (string) ($config['show_image_from'] ?? 'prediction');
        $userId = (int) ($member->user_id ?? 0);
        $picture = '';

        if (in_array($source, ['prediction', 'com_sportsmanagement'], true)) {
            $picture = (string) ($member->picture ?? '');
        } elseif ($userId > 0) {
            try {
                $db = $this->getDatabase();
                $query = $db->getQuery(true);
                switch ($source) {
                    case 'com_cbe':
                        $query->select($db->quoteName('avatar'))->from($db->quoteName('#__cbe_users'))->where($db->quoteName('userid') . ' = ' . $userId);
                        break;
                    case 'com_comprofiler':
                        $query->select($db->quoteName('avatar'))->from($db->quoteName('#__comprofiler'))->where($db->quoteName('user_id') . ' = ' . $userId);
                        break;
                    case 'com_kunena':
                        $query->select($db->quoteName('avatar'))->from($db->quoteName('#__kunena_users'))->where($db->quoteName('userid') . ' = ' . $userId);
                        break;
                    case 'com_community':
                        $query->select($db->quoteName('avatar'))->from($db->quoteName('#__community_users'))->where($db->quoteName('userid') . ' = ' . $userId);
                        break;
                    default:
                        return '';
                }
                $db->setQuery($query, 0, 1);
                $value = (string) ($db->loadResult() ?? '');
                if ($source === 'com_comprofiler' && $value !== '') {
                    $picture = 'images/comprofiler/' . $value;
                } elseif ($source === 'com_kunena' && $value !== '') {
                    $picture = 'media/kunena/avatars/' . $value;
                } else {
                    $picture = $value;
                }
            } catch (\Throwable) {
                $picture = '';
            }
        }

        if ($picture !== '') {
            return $picture;
        }

        return 'images/com_sportsmanagement/database/placeholders/placeholder_150_2.png';
    }

    public function canViewMemberProfile(object $member): bool
    {
        $identityId = (int) Factory::getApplication()->getIdentity()->id;
        return !empty($member->show_profile)
            || ($identityId > 0 && $identityId === (int) ($member->user_id ?? 0))
            || $this->isAllowedAdmin();
    }

    private function getOverallMemberRank(int $memberId, int $projectId): int
    {
        $db = $this->getDatabase();
        $pointsExpression = $projectId > 0
            ? 'COALESCE(SUM(CASE WHEN ' . $db->quoteName('r.project_id') . ' = ' . $projectId . ' THEN ' . $db->quoteName('pr.points') . ' ELSE 0 END), 0)'
            : 'COALESCE(SUM(' . $db->quoteName('pr.points') . '), 0)';

        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('pm.id', 'member_id'),
                $pointsExpression . ' AS total_points',
            ])
            ->from($db->quoteName('#__sportsmanagement_prediction_member', 'pm'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_prediction_result', 'pr') . ' ON ' . $db->quoteName('pr.user_id') . ' = ' . $db->quoteName('pm.user_id') . ' AND ' . $db->quoteName('pr.prediction_id') . ' = ' . $this->predictionGameId)
            ->join('LEFT', $db->quoteName('#__sportsmanagement_match', 'm') . ' ON ' . $db->quoteName('m.id') . ' = ' . $db->quoteName('pr.match_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_round', 'r') . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id'))
            ->where($db->quoteName('pm.prediction_id') . ' = ' . $this->predictionGameId)
            ->group($db->quoteName('pm.id'));

        $db->setQuery($query);
        $rows = $db->loadObjectList() ?: [];
        usort($rows, static fn(object $a, object $b): int => ((int) $b->total_points <=> (int) $a->total_points) ?: ((int) $a->member_id <=> (int) $b->member_id));

        $position = 0;
        $rank = 0;
        $previous = null;
        foreach ($rows as $row) {
            $position++;
            $points = (int) $row->total_points;
            if ($previous === null || $points !== $previous) {
                $rank = $position;
                $previous = $points;
            }
            if ((int) $row->member_id === $memberId) {
                return $rank;
            }
        }

        return 0;
    }

    private function resolveTeamSelections(string $raw, bool $multiple, bool $hideBeforeStart, int $memberUserId): array
    {
        $selections = $this->parseMemberTeamSelections($raw, $multiple);
        if (!$selections) {
            return [];
        }

        $identityId = (int) Factory::getApplication()->getIdentity()->id;
        $ownProfile = $identityId > 0 && $identityId === $memberUserId;
        $selectedProjectId = $this->getProjectId();
        $rows = [];

        foreach ($this->getPredictionProjects() as $predictionProject) {
            $projectId = (int) ($predictionProject->project_id ?? 0);
            if ($projectId <= 0 || ($selectedProjectId > 0 && $projectId !== $selectedProjectId)) {
                continue;
            }

            $teamIds = $selections[$projectId] ?? [];
            $teamIds = is_array($teamIds) ? $teamIds : [$teamIds];
            $teamIds = array_values(array_filter(array_map('intval', $teamIds)));
            if (!$teamIds) {
                continue;
            }

            $visible = !$hideBeforeStart || $ownProfile || $this->isProjectTipVisible($predictionProject);
            foreach ($teamIds as $teamId) {
                $team = $this->getProjectTeamById($projectId, $teamId);
                if (!$team) {
                    continue;
                }
                $rows[] = [
                    'project_id' => $projectId,
                    'project_name' => (string) ($predictionProject->projectName ?? ''),
                    'team_id' => $teamId,
                    'team_name' => (string) ($team->text ?? ''),
                    'visible' => $visible,
                ];
            }
        }

        return $rows;
    }

    private function parseMemberTeamSelections(string $raw, bool $multiple): array
    {
        $result = [];
        foreach (array_filter(array_map('trim', explode(';', $raw))) as $part) {
            [$projectId, $teamId] = array_pad(array_map('intval', explode(',', $part, 2)), 2, 0);
            if ($projectId <= 0 || $teamId <= 0) {
                continue;
            }
            if ($multiple) {
                $result[$projectId][] = $teamId;
            } else {
                $result[$projectId] = $teamId;
            }
        }
        return $result;
    }

    private function getProjectTeamById(int $projectId, int $projectTeamId): ?object
    {
        if ($projectId <= 0 || $projectTeamId <= 0) {
            return null;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('pt.id', 'value'),
                $db->quoteName('t.name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't') . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id'))
            ->where($db->quoteName('pt.project_id') . ' = ' . $projectId)
            ->where($db->quoteName('pt.id') . ' = ' . $projectTeamId);
        $db->setQuery($query, 0, 1);
        return $db->loadObject() ?: null;
    }

    private function isProjectTipVisible(object $predictionProject): bool
    {
        $startDate = (string) ($predictionProject->start_date ?? '');
        if ($startDate === '' || $startDate === '0000-00-00') {
            return false;
        }

        try {
            $timezone = new DateTimeZone((string) ($predictionProject->timezone ?? 'UTC'));
            $start = new DateTimeImmutable($startDate . ' 00:00:00', $timezone);
            return new DateTimeImmutable('now', $timezone) > $start->modify('+1 day');
        } catch (\Throwable) {
            return strtotime($startDate . ' +1 day') < time();
        }
    }

    private function extractMemberId(mixed $value): int
    {
        if (is_int($value)) {
            return max(0, $value);
        }
        $value = (string) $value;
        return max(0, (int) strtok($value, ':'));
    }

    private function hasPlayedResult(object $row): bool
    {
        return $row->homeResult !== null
            || $row->awayResult !== null
            || $row->homeDecision !== null
            || $row->awayDecision !== null;
    }
}
