<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use DateTimeImmutable;
use DateTimeZone;

class PredictionuserModel extends SportsManagementPredictionReadModel
{
    public function getEditableMember(): object
    {
        return $this->getPredictionMember();
    }

    public function getSelectedMemberNumericId(): int
    {
        $member = $this->getEditableMember();
        return $this->memberId($member->pmID ?? $member->id ?? 0);
    }

    public function canEditMember(?object $member = null): bool
    {
        $member ??= $this->getEditableMember();
        $userId = (int) ($member->user_id ?? 0);
        if ($userId <= 0 || $this->predictionGameId <= 0) {
            return false;
        }

        $identityId = (int) $this->siteApplication()->getIdentity()->id;
        return ($identityId > 0 && $identityId === $userId) || $this->isAllowedAdmin();
    }

    public function getEditConfig(): array
    {
        return array_merge(
            $this->getPredictionTemplateConfig('predictionoverall'),
            $this->getPredictionTemplateConfig('predictionranking'),
            $this->getPredictionTemplateConfig('predictionusers')
        );
    }

    public function getPredictionGroupOptions(): array
    {
        return $this->getPredictionGroupList();
    }

    public function getProjectTeamOptions(int $projectId): array
    {
        if ($projectId <= 0) {
            return [];
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
            ->order($db->quoteName('t.name') . ' ASC');
        $db->setQuery($query);
        return $db->loadObjectList() ?: [];
    }

    public function getEditProjects(object $member): array
    {
        $favourites = $this->parseMemberTeamSelections((string) ($member->fav_team ?? ''), false);
        $champions = $this->parseMemberTeamSelections((string) ($member->champ_tipp ?? ''), false);
        $final4 = $this->parseMemberTeamSelections((string) ($member->final4_tipp ?? ''), true);
        $rows = [];

        foreach ($this->getPredictionProjects() as $project) {
            $projectId = (int) ($project->project_id ?? 0);
            if ($projectId <= 0) {
                continue;
            }

            $rows[] = [
                'project' => $project,
                'project_id' => $projectId,
                'project_name' => (string) ($project->projectName ?? $projectId),
                'teams' => $this->getProjectTeamOptions($projectId),
                'fav_team' => (int) ($favourites[$projectId] ?? 0),
                'champ_tipp' => (int) ($champions[$projectId] ?? 0),
                'final4_tipp' => array_values(array_slice((array) ($final4[$projectId] ?? []), 0, 4)),
                'champ_enabled' => !empty($project->champ),
                'competitive_open' => $this->isProjectOpenForCompetitiveEdits($project),
            ];
        }

        return $rows;
    }

    public function canChangeGroup(): bool
    {
        $projects = $this->getPredictionProjects();
        if (!$projects) {
            return false;
        }

        foreach ($projects as $project) {
            if (!$this->isProjectOpenForCompetitiveEdits($project)) {
                return false;
            }
        }

        return true;
    }

    public function isProjectOpenForCompetitiveEdits(object $project): bool
    {
        $startDate = trim((string) ($project->start_date ?? ''));
        if ($startDate === '' || $startDate === '0000-00-00') {
            return false;
        }

        $startTime = trim((string) ($project->start_time ?? ''));
        if ($startTime === '' || $startTime === '00:00:00') {
            $startTime = '00:00:00';
        }

        try {
            $timezone = new DateTimeZone((string) ($project->timezone ?? 'UTC'));
            $start = new DateTimeImmutable($startDate . ' ' . $startTime, $timezone);
            return new DateTimeImmutable('now', $timezone) < $start;
        } catch (\Throwable) {
            $timestamp = strtotime($startDate . ' ' . $startTime);
            return $timestamp !== false && time() < $timestamp;
        }
    }

    public function isValidGroupId(int $groupId): bool
    {
        if ($groupId === 0) {
            return true;
        }

        foreach ($this->getPredictionGroupList() as $group) {
            if ((int) ($group->value ?? 0) === $groupId) {
                return true;
            }
        }

        return false;
    }

    public function isValidProjectTeamId(int $projectId, int $projectTeamId): bool
    {
        if ($projectTeamId === 0) {
            return true;
        }
        return $this->getProjectTeamById($projectId, $projectTeamId) !== null;
    }

    public function selectionMap(string $raw, bool $multiple = false): array
    {
        return $this->parseMemberTeamSelections($raw, $multiple);
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
            ->select($db->quoteName('pt.id'))
            ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->where($db->quoteName('pt.project_id') . ' = ' . $projectId)
            ->where($db->quoteName('pt.id') . ' = ' . $projectTeamId);
        $db->setQuery($query, 0, 1);
        return $db->loadObject() ?: null;
    }

    private function memberId(mixed $value): int
    {
        if (is_int($value)) {
            return max(0, $value);
        }
        return max(0, (int) strtok((string) $value, ':'));
    }
}
