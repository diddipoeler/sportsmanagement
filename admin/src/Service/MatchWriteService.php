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

    public function __construct(private DatabaseInterface $db)
    {
    }

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
     * Validate every selected statistics row before any existing values are
     * deleted. PHP casts malformed strings and arrays to unrelated IDs.
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

    /** Accept only complete decimal IDs within PHP's integer range. */
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

    /** @return array{success:bool,added:array<int,array{project_referee_id:int,project_position_id:int}>} */
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

    private function deleteStatistics(string $table, int $matchId, string $personColumn, int $personId): void
    {
        $query = $this->db->createQuery()
            ->delete($this->db->quoteName($table))
            ->where($this->db->quoteName('match_id') . ' = ' . $matchId)
            ->where($this->db->quoteName($personColumn) . ' = ' . $personId);
        $this->db->setQuery($query)->execute();
    }

    /** @return array<int,int> */
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

    /** @param array<int,int> $ids */
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
     * Check roster/referee IDs and submitted positions before clearing old rows.
     * Empty selections remain valid: removing all assignments is intentional.
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

    private static function administratorApplication(): CMSApplicationInterface
    {
        return SportsManagementAdministratorApplicationResolver::resolve();
    }

    /** @return array{0:string,1:int} */
    private function auditData(): array
    {
        $app = self::administratorApplication();
        return [Factory::getDate()->toSql(), (int) $app->getIdentity()->id];
    }

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
