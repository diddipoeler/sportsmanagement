<?php
namespace Diddipoeler\Component\SportsManagement\Site\Service;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

/**
 * Resolve Inline Hockey project metadata and keep team/project assignments native.
 */
final class InlineHockeyProjectService
{
    public function __construct(private readonly DatabaseInterface $db)
    {
    }

    public function getMatchLink(int $projectId, string $fieldName = 'jsminlinehockey'): string
    {
        if ($projectId <= 0) {
            return '';
        }

        $fieldName = trim($fieldName) !== '' ? trim($fieldName) : 'jsminlinehockey';
        $backend = 'project';
        $fieldType = 'link';
        $query = $this->db->getQuery(true)
            ->select($this->db->quoteName('ev.fieldvalue'))
            ->from($this->db->quoteName('#__sportsmanagement_user_extra_fields_values', 'ev'))
            ->join(
                'INNER',
                $this->db->quoteName('#__sportsmanagement_user_extra_fields', 'ef')
                . ' ON ' . $this->db->quoteName('ef.id') . ' = ' . $this->db->quoteName('ev.field_id')
            )
            ->where($this->db->quoteName('ev.jl_id') . ' = :projectId')
            ->where($this->db->quoteName('ef.name') . ' = :fieldName')
            ->where($this->db->quoteName('ef.template_backend') . ' = :backend')
            ->where($this->db->quoteName('ef.field_type') . ' = :fieldType')
            ->bind(':projectId', $projectId, ParameterType::INTEGER)
            ->bind(':fieldName', $fieldName, ParameterType::STRING)
            ->bind(':backend', $backend, ParameterType::STRING)
            ->bind(':fieldType', $fieldType, ParameterType::STRING);

        $this->db->setQuery($query, 0, 1);

        return trim((string) $this->db->loadResult());
    }

    public function ensureProjectTeam(int $teamId, int $projectId, int $seasonId): int
    {
        if ($teamId <= 0 || $projectId <= 0 || $seasonId <= 0) {
            return 0;
        }

        $seasonTeamId = $this->findSeasonTeam($teamId, $seasonId);

        if ($seasonTeamId <= 0) {
            $record = (object) [
                'team_id' => $teamId,
                'season_id' => $seasonId,
            ];
            $this->db->insertObject('#__sportsmanagement_season_team_id', $record);
            $seasonTeamId = (int) $this->db->insertid();
        }

        if ($seasonTeamId <= 0) {
            return 0;
        }

        $projectTeamId = $this->findProjectTeam($seasonTeamId, $projectId);

        if ($projectTeamId > 0) {
            return $projectTeamId;
        }

        $record = (object) [
            'team_id' => $seasonTeamId,
            'project_id' => $projectId,
        ];
        $this->db->insertObject('#__sportsmanagement_project_team', $record);

        return (int) $this->db->insertid();
    }

    private function findSeasonTeam(int $teamId, int $seasonId): int
    {
        $query = $this->db->getQuery(true)
            ->select($this->db->quoteName('id'))
            ->from($this->db->quoteName('#__sportsmanagement_season_team_id'))
            ->where($this->db->quoteName('team_id') . ' = :teamId')
            ->where($this->db->quoteName('season_id') . ' = :seasonId')
            ->bind(':teamId', $teamId, ParameterType::INTEGER)
            ->bind(':seasonId', $seasonId, ParameterType::INTEGER);
        $this->db->setQuery($query, 0, 1);

        return (int) ($this->db->loadResult() ?: 0);
    }

    private function findProjectTeam(int $seasonTeamId, int $projectId): int
    {
        $query = $this->db->getQuery(true)
            ->select($this->db->quoteName('id'))
            ->from($this->db->quoteName('#__sportsmanagement_project_team'))
            ->where($this->db->quoteName('team_id') . ' = :seasonTeamId')
            ->where($this->db->quoteName('project_id') . ' = :projectId')
            ->bind(':seasonTeamId', $seasonTeamId, ParameterType::INTEGER)
            ->bind(':projectId', $projectId, ParameterType::INTEGER);
        $this->db->setQuery($query, 0, 1);

        return (int) ($this->db->loadResult() ?: 0);
    }
}
