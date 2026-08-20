<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Service;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;

/** Transactional writes for match substitutions, events and live commentary. */
final class MatchTimelineWriteService
{
    private const MATCH_ROSTER_SUBSTITUTE_IN = 1;
    private const MATCH_ROSTER_SUBSTITUTE_OUT = 2;

    public function __construct(private DatabaseInterface $db)
    {
    }

    public function saveSubstitution(array $data): bool
    {
        $positionId = (int) ($data['project_position_id'] ?? 0);
        $time = (string) ($data['in_out_time'] ?? '');
        $playerIn = (int) ($data['in'] ?? 0);
        $playerOut = (int) ($data['out'] ?? 0);
        $matchId = (int) ($data['matchid'] ?? 0);

        if ($positionId <= 0 || $time === '' || $playerIn <= 0 || $playerOut <= 0 || $matchId <= 0) {
            return false;
        }

        $duplicate = $this->db->createQuery()
            ->select($this->db->quoteName('mp.id'))
            ->from($this->db->quoteName('#__sportsmanagement_match_player', 'mp'))
            ->where($this->db->quoteName('mp.match_id') . ' = ' . $matchId)
            ->where($this->db->quoteName('mp.teamplayer_id') . ' = ' . $playerIn)
            ->where($this->db->quoteName('mp.in_for') . ' = ' . $playerOut)
            ->where($this->db->quoteName('mp.in_out_time') . ' = ' . $this->db->quote($time))
            ->where($this->db->quoteName('mp.came_in') . ' = ' . self::MATCH_ROSTER_SUBSTITUTE_IN);
        $this->db->setQuery($duplicate, 0, 1);
        if ((int) $this->db->loadResult() > 0) {
            return false;
        }

        [$modified, $modifiedBy] = $this->auditData();
        return $this->transaction(function () use ($matchId, $playerIn, $playerOut, $time, $positionId, $modified, $modifiedBy): bool {
            $row = (object) [
                'match_id' => $matchId,
                'came_in' => self::MATCH_ROSTER_SUBSTITUTE_IN,
                'teamplayer_id' => $playerIn,
                'in_for' => $playerOut,
                'in_out_time' => $time,
                'project_position_id' => $positionId,
                'modified' => $modified,
                'modified_by' => $modifiedBy,
            ];
            $this->db->insertObject('#__sportsmanagement_match_player', $row);
            return true;
        });
    }

    public function removeSubstitution(int $substitutionId): bool
    {
        if ($substitutionId <= 0) {
            return false;
        }

        return $this->transaction(function () use ($substitutionId): bool {
            $query = $this->db->createQuery()
                ->delete($this->db->quoteName('#__sportsmanagement_match_player'))
                ->where($this->db->quoteName('id') . ' IN (' . $substitutionId . ',' . ($substitutionId + 1) . ')');
            $this->db->setQuery($query)->execute();
            return true;
        });
    }

    public function deleteEvent(int $eventId): bool
    {
        return $this->deleteById('#__sportsmanagement_match_event', $eventId);
    }

    public function deleteCommentary(int $commentaryId): bool
    {
        return $this->deleteById('#__sportsmanagement_match_commentary', $commentaryId);
    }

    public function saveComment(array $data): int|false
    {
        if (empty($data['event_time']) || empty($data['notes']) || (int) ($data['match_id'] ?? 0) <= 0) {
            return false;
        }

        [$modified, $modifiedBy] = $this->auditData();
        return $this->transaction(function () use ($data, $modified, $modifiedBy): int {
            $row = (object) [
                'event_time' => (string) $data['event_time'],
                'match_id' => (int) $data['match_id'],
                'type' => $data['type'] ?? '',
                'notes' => (string) $data['notes'],
                'modified' => $modified,
                'modified_by' => $modifiedBy,
            ];
            $this->db->insertObject('#__sportsmanagement_match_commentary', $row);
            return (int) $this->db->insertid();
        });
    }

    public function saveEvent(array $data): int|false
    {
        $matchId = (int) ($data['match_id'] ?? 0);
        $projectTeamId = (int) ($data['projectteam_id'] ?? 0);
        $teamPlayerId = (int) ($data['teamplayer_id'] ?? 0);
        $eventTypeId = (int) ($data['event_type_id'] ?? 0);
        $eventTime = (string) ($data['event_time'] ?? '');
        $eventSum = $data['event_sum'] ?? null;
        $useEventTime = !empty($data['useeventtime']);

        if ($matchId <= 0 || $projectTeamId <= 0 || $teamPlayerId <= 0 || $eventTypeId <= 0) {
            return false;
        }
        if ($useEventTime && $eventTime === '') {
            return false;
        }
        if (empty($eventSum)) {
            return false;
        }

        if (empty($data['doubleevents'])) {
            $query = $this->db->createQuery()
                ->select($this->db->quoteName('me.id'))
                ->from($this->db->quoteName('#__sportsmanagement_match_event', 'me'))
                ->where($this->db->quoteName('me.match_id') . ' = ' . $matchId)
                ->where($this->db->quoteName('me.projectteam_id') . ' = ' . $projectTeamId)
                ->where($this->db->quoteName('me.teamplayer_id') . ' = ' . $teamPlayerId)
                ->where($this->db->quoteName('me.event_time') . ' = ' . $this->db->quote($eventTime))
                ->where($this->db->quoteName('me.event_sum') . ' = ' . $this->db->quote((string) $eventSum));
            $this->db->setQuery($query, 0, 1);
            if ((int) $this->db->loadResult() > 0) {
                return false;
            }
        }

        [$modified, $modifiedBy] = $this->auditData();
        return $this->transaction(function () use ($data, $matchId, $projectTeamId, $teamPlayerId, $eventTypeId, $eventTime, $eventSum, $modified, $modifiedBy): int {
            $row = (object) [
                'match_id' => $matchId,
                'projectteam_id' => $projectTeamId,
                'teamplayer_id' => $teamPlayerId,
                'event_time' => $eventTime,
                'event_type_id' => $eventTypeId,
                'event_sum' => $eventSum,
                'notice' => $data['notice'] ?? '',
                'notes' => $data['notes'] ?? '',
                'modified' => $modified,
                'modified_by' => $modifiedBy,
            ];
            $this->db->insertObject('#__sportsmanagement_match_event', $row);
            $eventId = (int) $this->db->insertid();

            [$statisticId, $statisticValue] = $this->mappedStatistic($matchId, $teamPlayerId, $eventTypeId);
            if ($statisticId > 0 && $statisticValue != 0) {
                $query = $this->db->createQuery()
                    ->select($this->db->quoteName('id'))
                    ->from($this->db->quoteName('#__sportsmanagement_match_statistic'))
                    ->where($this->db->quoteName('match_id') . ' = ' . $matchId)
                    ->where($this->db->quoteName('teamplayer_id') . ' = ' . $teamPlayerId)
                    ->where($this->db->quoteName('statistic_id') . ' = ' . $statisticId);
                $this->db->setQuery($query, 0, 1);

                if ((int) $this->db->loadResult() <= 0) {
                    $stat = (object) [
                        'match_id' => $matchId,
                        'projectteam_id' => $projectTeamId,
                        'teamplayer_id' => $teamPlayerId,
                        'statistic_id' => $statisticId,
                        'value' => $statisticValue,
                        'modified' => $modified,
                        'modified_by' => $modifiedBy,
                    ];
                    $this->db->insertObject('#__sportsmanagement_match_statistic', $stat);
                }
            }

            return $eventId;
        });
    }

    /** @return array{0:int,1:int|float|string} */
    private function mappedStatistic(int $matchId, int $teamPlayerId, int $eventTypeId): array
    {
        $query = $this->db->createQuery()
            ->select([$this->db->quoteName('st.id'), $this->db->quoteName('st.params'), $this->db->quoteName('st.class')])
            ->from($this->db->quoteName('#__sportsmanagement_statistic', 'st'))
            ->join('INNER', $this->db->quoteName('#__sportsmanagement_position_statistic', 'possta') . ' ON ' . $this->db->quoteName('st.id') . ' = ' . $this->db->quoteName('possta.statistic_id'))
            ->join('INNER', $this->db->quoteName('#__sportsmanagement_match_player', 'matplay') . ' ON ' . $this->db->quoteName('matplay.project_position_id') . ' = ' . $this->db->quoteName('possta.position_id'))
            ->where($this->db->quoteName('matplay.match_id') . ' = ' . $matchId)
            ->where($this->db->quoteName('matplay.teamplayer_id') . ' = ' . $teamPlayerId);
        $this->db->setQuery($query);

        $statisticId = 0;
        $statisticValue = 0;
        foreach ($this->db->loadObjectList() ?: [] as $stat) {
            if ((string) $stat->class !== 'basic') {
                continue;
            }
            $params = json_decode((string) $stat->params, true);
            if (!is_array($params) || (int) ($params['event_id'] ?? 0) !== $eventTypeId) {
                continue;
            }
            $statisticId = (int) $stat->id;
            $statisticValue = $params['event_value'] ?? 0;
        }

        return [$statisticId, $statisticValue];
    }

    private function deleteById(string $table, int $id): bool
    {
        if ($id <= 0) {
            return false;
        }

        return $this->transaction(function () use ($table, $id): bool {
            $query = $this->db->createQuery()
                ->delete($this->db->quoteName($table))
                ->where($this->db->quoteName('id') . ' = ' . $id);
            $this->db->setQuery($query)->execute();
            return true;
        });
    }

    /** @return array{0:string,1:int} */
    private function auditData(): array
    {
        return [Factory::getDate()->toSql(), (int) Factory::getApplication()->getIdentity()->id];
    }

    private function transaction(callable $callback): mixed
    {
        $this->db->transactionStart();
        try {
            $result = $callback();
            $this->db->transactionCommit();
            return $result;
        } catch (\Throwable $e) {
            try {
                $this->db->transactionRollback();
            } catch (\Throwable) {
            }
            throw $e;
        }
    }
}
