<?php
/**
 * Joomla 5/6 project dashboard and standard-position bootstrap service.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Service;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;

/** Project dashboard counts and standard-position bootstrap. */
final class ProjectPanelService
{
    /**
     * Create the administrator project panel data and setup service.
     * Retains the Joomla database connection used to read project counters and initialise missing standard positions.
     *
     * @param DatabaseInterface $db Joomla database driver.
     * @return void
     */
    public function __construct(private DatabaseInterface $db)
    {
    }

    /**
     * Build project panel counters for divisions, teams, referees, positions and rounds.
     * Returns zeros for an invalid project and can initialise missing sport-specific positions for non-exempt project types before returning the final totals.
     *
     * @param object $project Project record containing id, sport type and project kind.
     * @return array{divisions:int,positions:int,referees:int,teams:int,rounds:int} Panel counts.
     */
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

    /**
     * Initialise missing player and referee positions for a project's sport type.
     * Loads non-root position definitions of the selected sport, checks each relation for the project and inserts only associations that do not yet exist.
     *
     * @param int $projectId Project receiving the standard positions.
     * @param int $sportsTypeId Sport type providing its position definitions.
     * @return void
     */
    private function ensureStandardPositions(int $projectId, int $sportsTypeId): void
    {
        $query = $this->db->createQuery()
            ->select($this->db->quoteName('id'))
            ->from($this->db->quoteName('#__sportsmanagement_position'))
            ->where($this->db->quoteName('parent_id') . ' <> 0')
            ->where($this->db->quoteName('sports_type_id') . ' = ' . $sportsTypeId)
            ->where($this->db->quoteName('persontype') . ' IN (1,2)');
        $this->db->setQuery($query);
        $positionIds = array_values(array_filter(array_map('intval', $this->db->loadColumn() ?: [])));

        foreach ($positionIds as $positionId) {
            $check = $this->db->createQuery()
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

    /**
     * Count rows in an internal project-owned database table.
     * Uses a trusted internal table name and column identifier with the project ID to return the panel statistic.
     *
     * @param string $table SportsManagement table to count.
     * @param string $field Column linking the records to the project.
     * @param int $id Project record identifier.
     * @return int Number of matching database rows.
     */
    private function count(string $table, string $field, int $id): int
    {
        $query = $this->db->createQuery()
            ->select('COUNT(*)')
            ->from($this->db->quoteName($table))
            ->where($this->db->quoteName($field) . ' = ' . $id);
        $this->db->setQuery($query);

        return (int) $this->db->loadResult();
    }
}
