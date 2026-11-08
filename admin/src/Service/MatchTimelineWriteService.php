<?php
/**
 * Joomla 5/6 transactional match timeline write service.
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
use Joomla\Database\ParameterType;

/** Transactional writes for match substitutions, events and live commentary. */
final class MatchTimelineWriteService
{
    private const MATCH_ROSTER_SUBSTITUTE_IN = 1;
    private const MATCH_ROSTER_SUBSTITUTE_OUT = 2;

    /**
     * Create the Joomla 5/6 match timeline write service.
     * Stores the database used for substitution records, match events, commentary and their associated statistic rows.
     *
     * @param DatabaseInterface $db Joomla database driver.
     * @return void
     */
    public function __construct(private DatabaseInterface $db)
    {
    }

    /**
     * Insert a new match substitution after validating its participant IDs and time.
     * Rejects invalid or duplicate incoming-substitution records and inserts the incoming player with administrator audit metadata inside a transaction.
     *
     * @param array $data Form values including matchid, in, out, position and in_out_time.
     * @return bool Whether a new substitution was successfully recorded.
     */
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

    /**
     * Remove a recorded substitution and its corresponding outgoing-player row.
     * Loads the incoming substitution with a bound ID, searches for an outgoing entry that matches the same fixture, player, position and time, and rejects ambiguous outgoing rows.
     *
     * @param int $substitutionId Identifier of the incoming match-player substitution.
     * @return bool Whether the matching substitution records were deleted.
     */
    public function removeSubstitution(int $substitutionId): bool
    {
        if ($substitutionId <= 0) {
            return false;
        }

        // An adjacent auto-increment ID may belong to another match. Resolve
        // the outgoing substitute by its recorded match, player and time.
        $incomingQuery = $this->db->createQuery()
            ->select([
                $this->db->quoteName('match_id'),
                $this->db->quoteName('in_for'),
                $this->db->quoteName('in_out_time'),
                $this->db->quoteName('project_position_id'),
            ])
            ->from($this->db->quoteName('#__sportsmanagement_match_player'))
            ->where($this->db->quoteName('id') . ' = :substitutionId')
            ->where($this->db->quoteName('came_in') . ' = ' . self::MATCH_ROSTER_SUBSTITUTE_IN)
            ->bind(':substitutionId', $substitutionId, ParameterType::INTEGER);
        $this->db->setQuery($incomingQuery, 0, 1);
        $incoming = $this->db->loadObject();

        if (!$incoming) {
            return false;
        }

        $ids = [$substitutionId];
        $matchId = (int) ($incoming->match_id ?? 0);
        $outPlayerId = (int) ($incoming->in_for ?? 0);
        $positionId = (int) ($incoming->project_position_id ?? 0);
        $substitutionTime = (string) ($incoming->in_out_time ?? '');

        if ($matchId > 0 && $outPlayerId > 0 && $positionId > 0 && $substitutionTime !== '') {
            $outgoingQuery = $this->db->createQuery()
                ->select($this->db->quoteName('id'))
                ->from($this->db->quoteName('#__sportsmanagement_match_player'))
                ->where($this->db->quoteName('match_id') . ' = :matchId')
                ->where($this->db->quoteName('teamplayer_id') . ' = :outPlayerId')
                ->where($this->db->quoteName('project_position_id') . ' = :positionId')
                ->where($this->db->quoteName('in_out_time') . ' = :substitutionTime')
                ->where($this->db->quoteName('came_in') . ' = ' . self::MATCH_ROSTER_SUBSTITUTE_OUT)
                ->bind(':matchId', $matchId, ParameterType::INTEGER)
                ->bind(':outPlayerId', $outPlayerId, ParameterType::INTEGER)
                ->bind(':positionId', $positionId, ParameterType::INTEGER)
                ->bind(':substitutionTime', $substitutionTime, ParameterType::STRING);
            $this->db->setQuery($outgoingQuery, 0, 2);
            $outgoingIds = array_values(array_unique(array_map('intval', $this->db->loadColumn() ?: [])));

            if (count($outgoingIds) > 1) {
                return false;
            }

            if ($outgoingIds !== []) {
                $ids[] = $outgoingIds[0];
            }
        }

        return $this->transaction(function () use ($ids): bool {
            $query = $this->db->createQuery()
                ->delete($this->db->quoteName('#__sportsmanagement_match_player'))
                ->whereIn($this->db->quoteName('id'), $ids, ParameterType::INTEGER);
            $this->db->setQuery($query)->execute();

            return true;
        });
    }

    /**
     * Delete an individual match event by its record identifier.
     * Delegates deletion to the shared transactional delete helper using the match-event table.
     *
     * @param int $eventId Match-event record identifier.
     * @return bool Whether a valid event identifier was deleted.
     */
    public function deleteEvent(int $eventId): bool
    {
        return $this->deleteById('#__sportsmanagement_match_event', $eventId);
    }

    /**
     * Delete a match commentary entry by its record identifier.
     * Removes the selected commentary row through the shared transactional delete helper.
     *
     * @param int $commentaryId Commentary record identifier.
     * @return bool Whether a valid commentary identifier was deleted.
     */
    public function deleteCommentary(int $commentaryId): bool
    {
        return $this->deleteById('#__sportsmanagement_match_commentary', $commentaryId);
    }

    /**
     * Insert an administrator commentary note into a match timeline.
     * Requires a valid match, event time and nonempty notes; persists the commentary and modification audit data in a transaction.
     *
     * @param array $data Submitted match, time, type and commentary content.
     * @return int|false New commentary ID, or false for incomplete input.
     */
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
            $commentId = (int) $this->db->insertid();

            if ($commentId <= 0) {
                throw new \RuntimeException('Match commentary insert returned no ID.');
            }

            return $commentId;
        });
    }

    /**
     * Insert a match event and any corresponding basic statistic row.
     * Checks fixture, team, player and event type IDs, optionally rejects duplicates, then creates the event and first matching statistic record in the same transaction.
     *
     * @param array $data Submitted event, player, team, time, sum and note values.
     * @return int|false Inserted event identifier, or false for invalid or duplicate input.
     */
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

            if ($eventId <= 0) {
                throw new \RuntimeException('Match event insert returned no ID.');
            }

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

    /**
     * Find a basic player statistic associated with a match event type.
     * Reads statistics permitted for the player's nominated match position and matches their JSON event ID to the chosen event type.
     *
     * @param int $matchId Fixture containing the player.
     * @param int $teamPlayerId Season-team-person identifier.
     * @param int $eventTypeId Event type to map into a statistic.
     * @return array{0:int,1:int|float|string} Statistic ID and configured event value.
     */
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

    /**
     * Delete a row from a trusted internal match timeline table.
     * Checks for a positive identifier, quotes the table and ID column and executes the removal atomically.
     *
     * @param string $table Internal match event or commentary table.
     * @param int $id Row identifier to delete.
     * @return bool False for invalid IDs or true after successful deletion.
     */
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

    /**
     * Resolve the active Joomla administrator application for timeline audit metadata.
     * Uses the native SportsManagement administrator application resolver rather than removed global Joomla application accessors.
     *
     * @return CMSApplicationInterface Active Joomla administrator application.
     */
    private static function administratorApplication(): CMSApplicationInterface
    {
        return SportsManagementAdministratorApplicationResolver::resolve();
    }

    /**
     * Create the SQL modification timestamp and acting administrator identifier.
     * Reads the Joomla current date and the authenticated administrator identity for newly inserted timeline rows.
     *
     * @return array{0:string,1:int} SQL modification timestamp and administrator user ID.
     */
    private function auditData(): array
    {
        return [Factory::getDate()->toSql(), (int) self::administratorApplication()->getIdentity()->id];
    }

    /**
     * Execute timeline mutations inside a database transaction.
     * Commits successful callbacks and attempts rollback on exceptions before propagating the original failure.
     *
     * @param callable $callback Database operation executed transactionally.
     * @return mixed Callback result.
     */
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
