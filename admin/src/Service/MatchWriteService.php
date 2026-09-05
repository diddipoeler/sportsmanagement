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
        $matchId = (int) ($data['match_id'] ?? 0);
        if ($matchId <= 0) {
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

    public function updateRoster(array $post): bool
    {
        $matchId = (int) ($post['id'] ?? 0);
        $projectTeamId = (int) ($post['team'] ?? 0);
        if ($matchId <= 0 || $projectTeamId <= 0) {
            return true;
        }

        $positions = (array) ($post['positions'] ?? []);
        $shirtNumbers = (array) ($post['trikot_number'] ?? []);
        $captains = (array) ($post['captain'] ?? []);
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
        $matchId = (int) ($post['id'] ?? 0);
        $projectTeamId = (int) ($post['team'] ?? 0);
        if ($matchId <= 0 || $projectTeamId <= 0) {
            return true;
        }

        $positions = (array) ($post['staffpositions'] ?? []);
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
        $matchId = (int) ($post['id'] ?? 0);
        if ($matchId <= 0) {
            return ['success' => false, 'added' => []];
        }

        $positions = (array) ($post['positions'] ?? []);
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

    private function positionId(mixed $position, int $fallback): int
    {
        if (is_object($position)) {
            return (int) ($position->posid ?? $position->pposid ?? $position->value ?? $fallback);
        }
        if (is_array($position)) {
            return (int) ($position['posid'] ?? $position['pposid'] ?? $position['value'] ?? $fallback);
        }
        return $fallback;
    }

    /** @return array{0:string,1:int} */
    private function auditData(): array
    {
        $app = Factory::getApplication();
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
