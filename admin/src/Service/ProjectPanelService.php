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
use Joomla\Database\ParameterType;

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
     * Malformed imported project fields cannot select a different project,
     * and unavailable counts are never interpreted as missing positions.
     *
     * @param object $project Project record containing id, sport type and project kind.
     * @return array{divisions:int,positions:int,referees:int,teams:int,rounds:int} Panel counts.
     */
    public function getCounts(object $project): array
    {
        $projectId = self::nonNegativeInteger($project->id ?? null) ?? 0;
        $sportsTypeId = self::nonNegativeInteger($project->sports_type_id ?? null) ?? 0;
        $projectArtId = self::nonNegativeInteger($project->project_art_id ?? null);

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

        if ($projectArtId !== null && $projectArtId !== 3 && $positions === 0 && $sportsTypeId > 0) {
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
     * Validates imported position IDs and all count results; malformed counts
     * cannot authorise an insertion. Uses bound native Joomla query values.
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
            ->where($this->db->quoteName('sports_type_id') . ' = :panelSport')
            ->where($this->db->quoteName('persontype') . ' IN (1,2)')
            ->bind(':panelSport', $sportsTypeId, ParameterType::INTEGER);
        $this->db->setQuery($query);
        $rows = $this->db->loadColumn();
        if (!is_array($rows)) {
            throw new \UnexpectedValueException('SportsManagement project position definitions are unavailable.');
        }

        $positionIds = [];
        foreach ($rows as $rawId) {
            $id = self::nonNegativeInteger($rawId);
            if ($id !== null && $id > 0) {
                $positionIds[$id] = $id;
            }
        }

        foreach ($positionIds as $positionId) {
            $check = $this->db->createQuery()
                ->select('COUNT(*)')
                ->from($this->db->quoteName('#__sportsmanagement_project_position'))
                ->where($this->db->quoteName('project_id') . ' = :panelProject')
                ->where($this->db->quoteName('position_id') . ' = :panelPosition')
                ->bind(':panelProject', $projectId, ParameterType::INTEGER)
                ->bind(':panelPosition', $positionId, ParameterType::INTEGER);
            $this->db->setQuery($check);

            if ($this->readCount() === 0) {
                $this->db->insertObject(
                    '#__sportsmanagement_project_position',
                    (object) ['project_id' => $projectId, 'position_id' => $positionId]
                );
            }
        }
    }

    /**
     * Count rows in an internal project-owned database table.
     * Uses a trusted internal table and column identifier plus a bound
     * integer project ID. A missing or malformed count is reported rather
     * than being converted into a misleading zero.
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
            ->where($this->db->quoteName($field) . ' = :panelId')
            ->bind(':panelId', $id, ParameterType::INTEGER);
        $this->db->setQuery($query);

        return $this->readCount();
    }

    /**
     * Validate the result of an executed COUNT(*) query.
     *
     * A real zero permits the historical standard-position setup; null,
     * false, overflow or malformed driver output must not cause writes.
     *
     * @return int Nonnegative row count.
     * @throws \UnexpectedValueException When the driver returns an invalid count.
     */
    private function readCount(): int
    {
        $count = self::nonNegativeInteger($this->db->loadResult());
        if ($count === null) {
            throw new \UnexpectedValueException('SportsManagement project panel count is unavailable.');
        }

        return $count;
    }

    /**
     * Parse stored IDs and counts without lossy PHP integer coercion.
     *
     * Accepts nonnegative integers and bounded ASCII decimal strings,
     * including zero padding, and rejects invalid types and overflow.
     *
     * @param mixed $value Raw project field or database result.
     * @return int|null Valid integer, or null when malformed.
     */
    private static function nonNegativeInteger(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value >= 0 ? $value : null;
        }

        if (!is_string($value) || strlen($value) > 64 || preg_match('/^[0-9]+$/D', $value) !== 1) {
            return null;
        }

        $digits = ltrim($value, '0');
        $number = filter_var($digits === '' ? '0' : $digits, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);

        return $number === false ? null : $number;
    }
}
