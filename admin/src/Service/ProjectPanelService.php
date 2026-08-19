<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Service;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;

/** Project dashboard counts and standard-position bootstrap. */
final class ProjectPanelService
{
    public function __construct(private DatabaseInterface $db)
    {
    }

    public function getCounts(object $project): array
    {
        $projectId = (int) ($project->id ?? 0);
        $sportsTypeId = (int) ($project->sports_type_id ?? 0);
        $projectArtId = (int) ($project->project_art_id ?? 0);

        if ($projectId <= 0) {
            return [
                'divisions' => 0,
                'positions' => 0,
                'referees' => 0,
                'teams' => 0,
                'rounds' => 0,
            ];
        }

        $positions = $this->count('#__sportsmanagement_project_position', 'project_id', $projectId);

        if ($projectArtId !== 3 && $positions === 0 && $sportsTypeId > 0) {
            $this->ensureStandardPositions($projectId, $sportsTypeId);
            $positions = $this->count('#__sportsmanagement_project_position', 'project_id', $projectId);
        }

        return [
            'divisions' => $this->count('#__sportsmanagement_division', 'project_id', $projectId),
            'positions' => $positions,
            'referees' => $this->count('#__sportsmanagement_project_referee', 'project_id', $projectId),
            'teams' => $this->count('#__sportsmanagement_project_team', 'project_id', $projectId),
            'rounds' => $this->count('#__sportsmanagement_round', 'project_id', $projectId),
        ];
    }

    private function ensureStandardPositions(int $projectId, int $sportsTypeId): void
    {
        $query = $this->db->getQuery(true)
            ->select($this->db->quoteName('id'))
            ->from($this->db->quoteName('#__sportsmanagement_position'))
            ->where($this->db->quoteName('parent_id') . ' <> 0')
            ->where($this->db->quoteName('sports_type_id') . ' = ' . $sportsTypeId)
            ->where($this->db->quoteName('persontype') . ' IN (1,2)');
        $this->db->setQuery($query);
        $positionIds = array_values(array_filter(array_map('intval', $this->db->loadColumn() ?: [])));

        foreach ($positionIds as $positionId) {
            $check = $this->db->getQuery(true)
                ->select('COUNT(*)')
                ->from($this->db->quoteName('#__sportsmanagement_project_position'))
                ->where($this->db->quoteName('project_id') . ' = ' . $projectId)
                ->where($this->db->quoteName('position_id') . ' = ' . $positionId);
            $this->db->setQuery($check);

            if ((int) $this->db->loadResult() === 0) {
                $this->db->insertObject(
                    '#__sportsmanagement_project_position',
                    (object) ['project_id' => $projectId, 'position_id' => $positionId]
                );
            }
        }
    }

    private function count(string $table, string $field, int $id): int
    {
        $query = $this->db->getQuery(true)
            ->select('COUNT(*)')
            ->from($this->db->quoteName($table))
            ->where($this->db->quoteName($field) . ' = ' . $id);
        $this->db->setQuery($query);

        return (int) $this->db->loadResult();
    }
}
