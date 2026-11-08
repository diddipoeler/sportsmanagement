<?php
/**
 * Shared Joomla 5/6 match roster, statistic and referee write service.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Service;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;

/**
 * Transactional match roster/statistic/referee writes shared by administrator and site clients.
 */
final class MatchWriteService
{
    private const MATCH_ROSTER_STARTER = 0;

    /**
     * Create the administrator match-write service with its Joomla database dependency.
     * Stores the connection used for transactional player, staff, referee and statistics updates.
     *
     * @param DatabaseInterface $db Joomla database driver.
     * @return void
     */
    public function __construct(private DatabaseInterface $db)
    {
    }

    /**
     * Save submitted player and staff statistics for a particular match.
     * Validates every selected statistic and person identifier before deleting existing values; replaces each selected person's rows inside one transaction.
     *
     * @param array $data Submitted match ID, selected people and statistic form entries.
     * @return bool Whether the statistics update was accepted and committed.
     */
    public function saveStatistics(array $data): bool
    {
        $matchId = self::validPositiveId($data['match_id'] ?? null);
        if ($matchId === null
            || !self::validStatisticsSelection($data, 'cid', 'teamplayer_id', 'projectteam_id', 'stat')
            || !self::validStatisticsSelection($data, 'staffcid', 'team_staff_id', 'sprojectteam_id', 'staffstat')) {
            return false;
        }

        return $this->transaction(function () use ($data, $matchId): void {
            if (isset($data['cid'])) {
                foreach ((array) ($data['teamplayer_id'] ?? []) as $index => $teamPlayerId) {
                    $teamPlayerId = (int) $teamPlayerId;
                    $projectTeamId = (int) (($data['projectteam_id'] ?? [])[$index] ?? 0);
                    if ($teamPlayerId <= 0) {
                        continue;
                    }

                    $this->deleteStatistics('#__sportsmanagement_match_statistic', $matchId, 'teamplayer_id', $teamPlayerId);
                    $pattern = '/^stat' . preg_quote((string) $teamPlayerId, '/') . '_([0-9]+)$/';

                    foreach ($data as $key => $value) {
                        if ($value === '' || !preg_match($pattern, (string) $key, $match)) {
                            continue;
                        }

                        $row = (object) [
                            'match_id' => $matchId,
                            'projectteam_id' => $projectTeamId,
                            'teamplayer_id' => $teamPlayerId,
                            'statistic_id' => (int) $match[1],
                            'value' => $value,
                        ];
                        $this->db->insertObject('#__sportsmanagement_match_statistic', $row);
                    }
                }
            }

            if (isset($data['staffcid'])) {
                foreach ((array) ($data['team_staff_id'] ?? []) as $index => $teamStaffId) {
                    $teamStaffId = (int) $teamStaffId;
                    $projectTeamId = (int) (($data['sprojectteam_id'] ?? [])[$index] ?? 0);
                    if ($teamStaffId <= 0) {
                        continue;
                    }

                    $this->deleteStatistics('#__sportsmanagement_match_staff_statistic', $matchId, 'team_staff_id', $teamStaffId);
                    $pattern = '/^staffstat' . preg_quote((string) $teamStaffId, '/') . '_([0-9]+)$/';

                    foreach ($data as $key => $value) {
                        if ($value === '' || !preg_match($pattern, (string) $key, $match)) {
                            continue;
                        }

                        $row = (object) [
                            'match_id' => $matchId,
                            'projectteam_id' => $projectTeamId,
                            'team_staff_id' => $teamStaffId,
                            'statistic_id' => (int) $match[1],
                            'value' => $value,
                        ];
                        $this->db->insertObject('#__sportsmanagement_match_staff_statistic', $row);
                    }
                }
            }
        });
    }

    /**
     * Validate person and statistic identifiers for a submitted statistics section.
     * Checks the matching player or staff arrays, team assignments and every matching statistic field before any existing values are removed.
     *
     * @param array $data Submitted form values.
     * @param string $flag Form flag indicating that this section was supplied.
     * @param string $personField Key containing selected person identifiers.
     * @param string $teamField Key containing corresponding project-team IDs.
     * @param string $prefix Prefix used by that section's statistic input names.
     * @return bool Whether every selected statistic has valid identifiers and scalar values.
     */
    private static function validStatisticsSelection(
        array $data,
        string $flag,
        string $personField,
        string $teamField,
        string $prefix
    ): bool {
        if (!isset($data[$flag])) {
            return true;
        }

        if (!is_array($data[$personField] ?? null) || !is_array($data[$teamField] ?? null)) {
            return false;
        }

        foreach ($data[$personField] as $index => $rawId) {
            $personId = self::validPositiveId($rawId, true);
            if ($personId === null) {
                return false;
            }
            if ($personId === 0) {
                continue;
            }
            if (self::validPositiveId($data[$teamField][$index] ?? null) === null) {
                return false;
            }

            $pattern = '/^' . preg_quote($prefix . $personId, '/') . '_([0-9]+)$/';
            foreach ($data as $name => $value) {
                if (!is_string($name) || preg_match($pattern, $name, $matches) !== 1) {
                    continue;
                }
                if (self::validPositiveId($matches[1]) === null
                    || ($value !== '' && !is_scalar($value))) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Parse a strictly decimal record identifier without coercing invalid values.
     * Rejects booleans, partial numeric strings, leading-zero variants and integer overflow; zero can be permitted for intentionally empty selections.
     *
     * @param mixed $value Candidate ID from an incoming form.
     * @param bool $allowZero Whether the unassigned identifier zero is allowed.
     * @return int|null Parsed identifier, or null if invalid.
     */
    private static function validPositiveId(mixed $value, bool $allowZero = false): ?int
    {
        $minimum = $allowZero ? 0 : 1;

        if (is_int($value)) {
            return $value >= $minimum ? $value : null;
        }
        if (!is_string($value)
            || !preg_match($allowZero ? '/^(?:0|[1-9][0-9]*)$/D' : '/^[1-9][0-9]*$/D', $value)) {
            return null;
        }

        $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => $minimum]]);

        return $id === false ? null : $id;
    }

    /**
     * Replace the starting-player assignments for a given fixture and project team.
     * Validates position, person, ordering, shirt and captain fields before removing existing starter rows and inserting selected players with audit metadata.
     *
     * @param array $post Submitted fixture ID, project team, roster positions and shirt details.
     * @return bool Whether the starting roster was successfully updated.
     */
    public function updateRoster(array $post): bool
    {
        $matchId = self::validPositiveId($post['id'] ?? null);
        $projectTeamId = self::validPositiveId($post['team'] ?? null);
        $positions = $post['positions'] ?? [];

        if ($matchId === null || $projectTeamId === null || !is_array($positions)
            || !self::validLineupSelection($post, $positions, 'position')) {
            return false;
        }
        $shirtNumbers = $post['trikot_number'] ?? [];
        $captains = $post['captain'] ?? [];
        if (!is_array($shirtNumbers) || !is_array($captains)) {
            return false;
        }

        foreach ($positions as $positionKey => $position) {
            foreach ($post['position' . $positionKey] ?? [] as $teamPlayerId) {
                $personId = self::validPositiveId($teamPlayerId, true);
                if ($personId === null || $personId === 0) {
                    continue;
                }

                $shirt = $shirtNumbers[$personId] ?? null;
                $captain = $captains[$personId] ?? 0;
                if (($shirt !== null && !is_scalar($shirt))
                    || self::validPositiveId($captain, true) === null) {
                    return false;
                }
            }
        }

        [$modified, $modifiedBy] = $this->auditData();

        return $this->transaction(function () use (
            $post,
            $matchId,
            $projectTeamId,
            $positions,
            $shirtNumbers,
            $captains,
            $modified,
            $modifiedBy
        ): void {
            $ids = $this->assignedIds('#__sportsmanagement_match_player', 'teamplayer_id', $matchId, $projectTeamId, true);
            $this->deleteIds('#__sportsmanagement_match_player', $ids);

            foreach ($positions as $positionKey => $position) {
                $positionId = $this->positionId($position, (int) $positionKey);
                if ($positionId <= 0) {
                    continue;
                }

                foreach ((array) ($post['position' . $positionKey] ?? []) as $ordering => $teamPlayerId) {
                    $teamPlayerId = (int) $teamPlayerId;
                    if ($teamPlayerId <= 0) {
                        continue;
                    }

                    $row = (object) [
                        'match_id' => $matchId,
                        'teamplayer_id' => $teamPlayerId,
                        'project_position_id' => $positionId,
                        'came_in' => self::MATCH_ROSTER_STARTER,
                        'trikot_number' => $shirtNumbers[$teamPlayerId] ?? null,
                        'captain' => (int) ($captains[$teamPlayerId] ?? 0),
                        'ordering' => (int) $ordering,
                        'modified' => $modified,
                        'modified_by' => $modifiedBy,
                    ];
                    $this->db->insertObject('#__sportsmanagement_match_player', $row);
                }
            }
        });
    }

    /**
     * Replace the nominated match staff for a project team in a transaction.
     * Checks that staff and position identifiers match the authoritative list, removes old assignments and inserts the new ordered staff rows.
     *
     * @param array $post Submitted match ID, project team and staff positions.
     * @return bool Whether the replacement staff roster was committed.
     */
    public function updateStaff(array $post): bool
    {
        $matchId = self::validPositiveId($post['id'] ?? null);
        $projectTeamId = self::validPositiveId($post['team'] ?? null);
        $positions = $post['staffpositions'] ?? [];

        if ($matchId === null || $projectTeamId === null || !is_array($positions)
            || !self::validLineupSelection($post, $positions, 'staffposition')) {
            return false;
        }
        [$modified, $modifiedBy] = $this->auditData();

        return $this->transaction(function () use ($post, $matchId, $projectTeamId, $positions, $modified, $modifiedBy): void {
            $ids = $this->assignedIds('#__sportsmanagement_match_staff', 'team_staff_id', $matchId, $projectTeamId, false);
            $this->deleteIds('#__sportsmanagement_match_staff', $ids);

            foreach ($positions as $positionKey => $position) {
                $positionId = $this->positionId($position, (int) $positionKey);
                if ($positionId <= 0) {
                    continue;
                }

                foreach ((array) ($post['staffposition' . $positionKey] ?? []) as $ordering => $teamStaffId) {
                    $teamStaffId = (int) $teamStaffId;
                    if ($teamStaffId <= 0) {
                        continue;
                    }

                    $row = (object) [
                        'match_id' => $matchId,
                        'team_staff_id' => $teamStaffId,
                        'project_position_id' => $positionId,
                        'ordering' => (int) $ordering,
                        'modified' => $modified,
                        'modified_by' => $modifiedBy,
                    ];
                    $this->db->insertObject('#__sportsmanagement_match_staff', $row);
                }
            }
        });
    }

    /**
     * Synchronise a fixture's project-referee assignments without losing retained records.
     * Checks the submitted referee positions, deletes unselected assignments, updates retained rows and inserts new rows, returning only successfully added referees.
     *
     * @param array $post Submitted match ID, referee positions and selected project referee IDs.
     * @return array{success:bool,added:array<int,array{project_referee_id:int,project_position_id:int}>} Update result and newly added assignments.
     */
    public function updateReferees(array $post): array
    {
        $matchId = self::validPositiveId($post['id'] ?? null);
        $positions = $post['positions'] ?? [];
        if ($matchId === null || !is_array($positions)
            || !self::validLineupSelection($post, $positions, 'position')) {
            return ['success' => false, 'added' => []];
        }
        $selected = [];
        foreach ($positions as $positionKey => $position) {
            foreach ((array) ($post['position' . $positionKey] ?? []) as $ordering => $projectRefereeId) {
                $projectRefereeId = (int) $projectRefereeId;
                if ($projectRefereeId <= 0) {
                    continue;
                }
                $selected[] = [
                    'project_referee_id' => $projectRefereeId,
                    'project_position_id' => $this->positionId($position, (int) $positionKey),
                    'ordering' => (int) $ordering,
                ];
            }
        }

        $added = [];
        $success = $this->transaction(function () use ($matchId, $selected, &$added): void {
            $selectedIds = array_values(array_unique(array_column($selected, 'project_referee_id')));
            $query = $this->db->createQuery()
                ->delete($this->db->quoteName('#__sportsmanagement_match_referee'))
                ->where($this->db->quoteName('match_id') . ' = ' . $matchId);
            if ($selectedIds) {
                $query->where($this->db->quoteName('project_referee_id') . ' NOT IN (' . implode(',', array_map('intval', $selectedIds)) . ')');
            }
            $this->db->setQuery($query)->execute();

            foreach ($selected as $assignment) {
                if ($assignment['project_position_id'] <= 0) {
                    continue;
                }

                $query = $this->db->createQuery()
                    ->select($this->db->quoteName('id'))
                    ->from($this->db->quoteName('#__sportsmanagement_match_referee'))
                    ->where($this->db->quoteName('match_id') . ' = ' . $matchId)
                    ->where($this->db->quoteName('project_referee_id') . ' = ' . (int) $assignment['project_referee_id']);
                $this->db->setQuery($query, 0, 1);
                $id = (int) $this->db->loadResult();

                if ($id > 0) {
                    $row = (object) [
                        'id' => $id,
                        'project_position_id' => (int) $assignment['project_position_id'],
                        'ordering' => (int) $assignment['ordering'],
                    ];
                    $this->db->updateObject('#__sportsmanagement_match_referee', $row, 'id');
                    continue;
                }

                $row = (object) [
                    'match_id' => $matchId,
                    'project_referee_id' => (int) $assignment['project_referee_id'],
                    'project_position_id' => (int) $assignment['project_position_id'],
                    'ordering' => (int) $assignment['ordering'],
                ];
                $this->db->insertObject('#__sportsmanagement_match_referee', $row);
                $added[] = [
                    'project_referee_id' => (int) $assignment['project_referee_id'],
                    'project_position_id' => (int) $assignment['project_position_id'],
                ];
            }
        });

        return ['success' => $success, 'added' => $success ? $added : []];
    }

    /**
     * Remove a participant's stored statistics for one match before replacement.
     * Deletes from the trusted player- or staff-statistic table with the match and participant identifier scoped together.
     *
     * @param string $table Internal statistics table to update.
     * @param int $matchId Parent match identifier.
     * @param string $personColumn Participant ID column in the selected table.
     * @param int $personId Player or staff record identifier.
     * @return void
     */
    private function deleteStatistics(string $table, int $matchId, string $personColumn, int $personId): void
    {
        $query = $this->db->createQuery()
            ->delete($this->db->quoteName($table))
            ->where($this->db->quoteName('match_id') . ' = ' . $matchId)
            ->where($this->db->quoteName($personColumn) . ' = ' . $personId);
        $this->db->setQuery($query)->execute();
    }

    /**
     * Find existing roster row IDs belonging to the specified match and project team.
     * Joins participant registrations to their season and project-team relationships and optionally restricts the result to starting players.
     *
     * @param string $table Trusted match-player or match-staff table.
     * @param string $personColumn Participant foreign-key column.
     * @param int $matchId Parent match identifier.
     * @param int $projectTeamId Owning project-team identifier.
     * @param bool $startersOnly Whether to exclude substitution rows.
     * @return array<int,int> Existing roster record identifiers.
     */
    private function assignedIds(string $table, string $personColumn, int $matchId, int $projectTeamId, bool $startersOnly): array
    {
        $query = $this->db->createQuery()
            ->select($this->db->quoteName('mp.id'))
            ->from($this->db->quoteName($table, 'mp'))
            ->join('INNER', $this->db->quoteName('#__sportsmanagement_season_team_person_id', 'sp') . ' ON ' . $this->db->quoteName('sp.id') . ' = ' . $this->db->quoteName('mp.' . $personColumn))
            ->join('INNER', $this->db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $this->db->quoteName('st.team_id') . ' = ' . $this->db->quoteName('sp.team_id') . ' AND ' . $this->db->quoteName('st.season_id') . ' = ' . $this->db->quoteName('sp.season_id'))
            ->join('LEFT', $this->db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON ' . $this->db->quoteName('pt.team_id') . ' = ' . $this->db->quoteName('st.id'))
            ->where($this->db->quoteName('mp.match_id') . ' = ' . $matchId)
            ->where($this->db->quoteName('pt.id') . ' = ' . $projectTeamId);

        if ($startersOnly) {
            $query->where($this->db->quoteName('mp.came_in') . ' = ' . self::MATCH_ROSTER_STARTER);
        }

        $this->db->setQuery($query);
        return array_values(array_filter(array_map('intval', $this->db->loadColumn() ?: [])));
    }

    /**
     * Delete a collection of existing rows from an internal roster table.
     * Returns without SQL for an empty list; otherwise deletes only the supplied integer record identifiers.
     *
     * @param string $table Trusted roster table to update.
     * @param array<int,int> $ids Existing row identifiers to remove.
     * @return void
     */
    private function deleteIds(string $table, array $ids): void
    {
        if (!$ids) {
            return;
        }

        $query = $this->db->createQuery()
            ->delete($this->db->quoteName($table))
            ->where($this->db->quoteName('id') . ' IN (' . implode(',', array_map('intval', $ids)) . ')');
        $this->db->setQuery($query)->execute();
    }

    /**
     * Check submitted roster or referee positions against the authoritative form definition.
     * Rejects invalid person IDs, ordering keys, malformed position references and extra fields for unknown positions before any assigned rows are deleted.
     *
     * @param array $post Complete lineup form submission.
     * @param array $positions Position definitions made available by the project.
     * @param string $prefix Name prefix of selected player, staff or referee fields.
     * @return bool Whether the lineup selection may safely replace existing records.
     */
    private static function validLineupSelection(array $post, array $positions, string $prefix): bool
    {
        foreach ($positions as $key => $position) {
            $field = $prefix . $key;
            $entries = $post[$field] ?? [];
            if (!is_array($entries)) {
                return false;
            }

            foreach ($entries as $ordering => $personId) {
                if ($personId === '' || $personId === null) {
                    continue;
                }

                if (self::validPositiveId($personId, true) === null
                    || self::validPositiveId($ordering, true) === null) {
                    return false;
                }

                if (self::validPositiveId($personId, true) > 0
                    && self::positionId($position, (int) $key) <= 0) {
                    return false;
                }
            }
        }

        // Reject assignments for a position not supplied by the project's
        // authoritative list, instead of silently deleting the existing team.
        foreach ($post as $name => $value) {
            if (is_string($name)
                && preg_match('/^' . preg_quote($prefix, '/') . '[0-9]+$/D', $name)
                && !array_key_exists(substr($name, strlen($prefix)), $positions)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Resolve the project-position identifier from a form position descriptor.
     * Accepts an object or array containing posid, pposid or value and otherwise uses the provided fallback; invalid positions become zero.
     *
     * @param mixed $position Position descriptor from the form or project model.
     * @param int $fallback Identifier implied by the position key.
     * @return int Positive project-position identifier, or zero.
     */
    private static function positionId(mixed $position, int $fallback): int
    {
        if (is_object($position)) {
            $id = $position->posid ?? $position->pposid ?? $position->value ?? $fallback;
        } elseif (is_array($position)) {
            $id = $position['posid'] ?? $position['pposid'] ?? $position['value'] ?? $fallback;
        } else {
            $id = $fallback;
        }

        return self::validPositiveId($id) ?? 0;
    }

    /**
     * Resolve the active Joomla administrator application for match auditing.
     * Uses the component's central administrator application resolver instead of deprecated global application access.
     *
     * @return CMSApplicationInterface Current Joomla administrator application.
     */
    private static function administratorApplication(): CMSApplicationInterface
    {
        return SportsManagementAdministratorApplicationResolver::resolve();
    }

    /**
     * Read the administrator user ID and current SQL timestamp for persisted edits.
     * Captures the modifying identity once so all rows in a transaction receive consistent audit fields.
     *
     * @return array{0:string,1:int} Modification time and user identifier.
     */
    private function auditData(): array
    {
        $app = self::administratorApplication();
        return [Factory::getDate()->toSql(), (int) $app->getIdentity()->id];
    }

    /**
     * Execute a match-write callback atomically using Joomla's database driver.
     * Commits after successful execution and attempts rollback on failure before propagating the exception to the caller.
     *
     * @param callable $callback Match mutation performed inside a database transaction.
     * @return bool True after a successful commit.
     */
    private function transaction(callable $callback): bool
    {
        $this->db->transactionStart();
        try {
            $callback();
            $this->db->transactionCommit();
            return true;
        } catch (\Throwable $e) {
            try {
                $this->db->transactionRollback();
            } catch (\Throwable) {
            }
            throw $e;
        }
    }
}
