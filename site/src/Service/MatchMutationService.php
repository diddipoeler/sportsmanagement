<?php
/**
 * Native Joomla 5/6 mutation service for frontend match actions.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Service;

\defined('_JEXEC') or die;

use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

/**
 * Native Joomla 5/6 data service for the frontend edit-match AJAX actions.
 *
 * The legacy match model mixed two database policies. Substitution writes used
 * sportsmanagementHelper::getDBConnection(), while event/comment writes used
 * Joomla's default database directly. Both connections are injected explicitly
 * here so the migration preserves that existing behaviour.
 */
final class MatchMutationService
{
    private const MATCH_ROSTER_SUBSTITUTE_IN = 1;
    private const MATCH_ROSTER_SUBSTITUTE_OUT = 2;

    public function __construct(
        private readonly DatabaseInterface $joomlaDatabase,
        private readonly DatabaseInterface $sportsDatabase,
        private readonly int $userId,
        private readonly string $modified
    ) {
    }

    public function saveEvent(array $data): int|false
    {
        $matchId = self::positiveId($data['match_id'] ?? null);
        $projectTeamId = self::positiveId($data['projectteam_id'] ?? null);
        $eventTypeId = self::positiveId($data['event_type_id'] ?? null);
        $teamPlayerId = self::positiveId($data['teamplayer_id'] ?? 0, true);

        if ($matchId === null || $projectTeamId === null
            || $eventTypeId === null || $teamPlayerId === null
            || !is_scalar($data['event_sum'] ?? null)
            || !is_scalar($data['event_time'] ?? '')
            || !is_scalar($data['notice'] ?? '')
            || !is_scalar($data['notes'] ?? '')) {
            return false;
        }

        if (!empty($data['useeventtime']) && empty($data['event_time'])) {
            return false;
        }

        if (empty($data['event_sum'])) {
            return false;
        }

        $db = $this->joomlaDatabase;
        $transactionStarted = false;

        try {
            if (empty($data['doubleevents'])) {
                $eventTime = (string) ($data['event_time'] ?? '');
                $eventSum = (string) ($data['event_sum'] ?? '');
                $query = $db->createQuery()
                    ->select($db->quoteName('id'))
                    ->from($db->quoteName('#__sportsmanagement_match_event'))
                    ->where($db->quoteName('match_id') . ' = :matchId')
                    ->where($db->quoteName('projectteam_id') . ' = :projectTeamId')
                    ->where($db->quoteName('teamplayer_id') . ' = :teamPlayerId')
                    ->where($db->quoteName('event_time') . ' = :eventTime')
                    ->where($db->quoteName('event_sum') . ' = :eventSum')
                    ->bind(':matchId', $matchId, ParameterType::INTEGER)
                    ->bind(':projectTeamId', $projectTeamId, ParameterType::INTEGER)
                    ->bind(':teamPlayerId', $teamPlayerId, ParameterType::INTEGER)
                    ->bind(':eventTime', $eventTime, ParameterType::STRING)
                    ->bind(':eventSum', $eventSum, ParameterType::STRING);
                $db->setQuery($query, 0, 1);

                if ($db->loadResult()) {
                    return false;
                }
            }

            $event = new \stdClass();
            $event->match_id = $matchId;
            $event->projectteam_id = $projectTeamId;
            $event->teamplayer_id = $teamPlayerId;
            $event->event_time = (string) ($data['event_time'] ?? '');
            $event->event_type_id = $eventTypeId;
            $event->event_sum = (string) ($data['event_sum'] ?? '');
            $event->notice = (string) ($data['notice'] ?? '');
            $event->notes = (string) ($data['notes'] ?? '');
            $event->modified = $this->modified;
            $event->modified_by = $this->userId;

            // The event and any derived match statistic form one logical write.
            // Roll both back when the statistic insert or lookup fails, rather
            // than reporting failure after persisting an orphan event.
            $db->transactionStart();
            $transactionStarted = true;
            $db->insertObject('#__sportsmanagement_match_event', $event);
            $eventId = (int) $db->insertid();

            if ($eventId <= 0) {
                throw new \RuntimeException('Match event insert returned no ID.');
            }

            $this->createEventStatisticIfNeeded($data);
            $db->transactionCommit();
            $transactionStarted = false;

            return $eventId;
        } catch (\Throwable $e) {
            if ($transactionStarted) {
                try {
                    $db->transactionRollback();
                } catch (\Throwable $rollbackError) {
                    $this->logFailure($rollbackError);
                }
            }

            $this->logFailure($e);
            return false;
        }
    }

    public function saveSubstitution(array $data): bool
    {
        // Preserve the legacy validation contract. Although the historical
        // implementation contains fallback branches for zero values, they are
        // unreachable after these checks and therefore are not broadened here.
        $playerIn = self::positiveId($data['in'] ?? null);
        $playerOut = self::positiveId($data['out'] ?? null);
        $matchId = self::positiveId($data['matchid'] ?? null);
        $positionId = self::positiveId($data['project_position_id'] ?? null);

        if ($playerIn === null || $playerOut === null
            || $matchId === null || $positionId === null
            || !is_scalar($data['in_out_time'] ?? null)
            || (string) $data['in_out_time'] === '') {
            return false;
        }

        $inOutTime = (string) $data['in_out_time'];
        $db = $this->sportsDatabase;

        try {
            $query = $db->createQuery()
                ->select($db->quoteName('id'))
                ->from($db->quoteName('#__sportsmanagement_match_player'))
                ->where($db->quoteName('match_id') . ' = :matchId')
                ->where($db->quoteName('teamplayer_id') . ' = :playerIn')
                ->where($db->quoteName('in_for') . ' = :playerOut')
                ->where($db->quoteName('in_out_time') . ' = :inOutTime')
                ->where($db->quoteName('came_in') . ' = ' . self::MATCH_ROSTER_SUBSTITUTE_IN)
                ->bind(':matchId', $matchId, ParameterType::INTEGER)
                ->bind(':playerIn', $playerIn, ParameterType::INTEGER)
                ->bind(':playerOut', $playerOut, ParameterType::INTEGER)
                ->bind(':inOutTime', $inOutTime, ParameterType::STRING);
            $db->setQuery($query, 0, 1);

            if ($db->loadResult()) {
                return false;
            }

            $incoming = new \stdClass();
            $incoming->match_id = $matchId;
            $incoming->came_in = self::MATCH_ROSTER_SUBSTITUTE_IN;
            $incoming->teamplayer_id = $playerIn;
            $incoming->in_for = $playerOut > 0 ? $playerOut : 0;
            $incoming->in_out_time = $inOutTime;
            $incoming->project_position_id = $positionId;
            $incoming->modified = $this->modified;
            $incoming->modified_by = $this->userId;
            $db->insertObject('#__sportsmanagement_match_player', $incoming);

            // Kept for parity with the historic method even though the legacy
            // validation above makes this branch unreachable for current calls.
            if ($playerOut > 0 && $playerIn === 0) {
                $outgoing = new \stdClass();
                $outgoing->match_id = $matchId;
                $outgoing->came_in = self::MATCH_ROSTER_SUBSTITUTE_OUT;
                $outgoing->teamplayer_id = $playerOut;
                $outgoing->in_out_time = $inOutTime;
                $outgoing->project_position_id = $positionId;
                $outgoing->out = 1;
                $outgoing->modified = $this->modified;
                $outgoing->modified_by = $this->userId;
                $db->insertObject('#__sportsmanagement_match_player', $outgoing);
            }

            return true;
        } catch (\Throwable $e) {
            $this->logFailure($e);
            return false;
        }
    }

    public function removeSubstitution(int $substitutionId): bool
    {
        if ($substitutionId <= 0) {
            return false;
        }

        $db = $this->sportsDatabase;

        try {
            // Deleting id + 1 is unsafe: another edit may have inserted a
            // different match player's row between the two substitutions.
            $incomingQuery = $db->createQuery()
                ->select([
                    $db->quoteName('match_id'),
                    $db->quoteName('in_for'),
                    $db->quoteName('in_out_time'),
                    $db->quoteName('project_position_id'),
                ])
                ->from($db->quoteName('#__sportsmanagement_match_player'))
                ->where($db->quoteName('id') . ' = :substitutionId')
                ->where($db->quoteName('came_in') . ' = ' . self::MATCH_ROSTER_SUBSTITUTE_IN)
                ->bind(':substitutionId', $substitutionId, ParameterType::INTEGER);
            $db->setQuery($incomingQuery, 0, 1);
            $incoming = $db->loadObject();

            if (!$incoming) {
                return false;
            }

            $ids = [$substitutionId];
            $matchId = (int) ($incoming->match_id ?? 0);
            $outPlayerId = (int) ($incoming->in_for ?? 0);
            $positionId = (int) ($incoming->project_position_id ?? 0);
            $substitutionTime = (string) ($incoming->in_out_time ?? '');

            if ($matchId > 0 && $outPlayerId > 0 && $positionId > 0 && $substitutionTime !== '') {
                $outgoingQuery = $db->createQuery()
                    ->select($db->quoteName('id'))
                    ->from($db->quoteName('#__sportsmanagement_match_player'))
                    ->where($db->quoteName('match_id') . ' = :matchId')
                    ->where($db->quoteName('teamplayer_id') . ' = :outPlayerId')
                    ->where($db->quoteName('project_position_id') . ' = :positionId')
                    ->where($db->quoteName('in_out_time') . ' = :substitutionTime')
                    ->where($db->quoteName('came_in') . ' = ' . self::MATCH_ROSTER_SUBSTITUTE_OUT)
                    ->bind(':matchId', $matchId, ParameterType::INTEGER)
                    ->bind(':outPlayerId', $outPlayerId, ParameterType::INTEGER)
                    ->bind(':positionId', $positionId, ParameterType::INTEGER)
                    ->bind(':substitutionTime', $substitutionTime, ParameterType::STRING);
                $db->setQuery($outgoingQuery, 0, 2);
                $outgoingIds = array_values(array_unique(array_map('intval', $db->loadColumn() ?: [])));

                // Ambiguous legacy substitutions must be resolved manually.
                if (count($outgoingIds) > 1) {
                    return false;
                }

                if ($outgoingIds !== []) {
                    $ids[] = $outgoingIds[0];
                }
            }

            $db->transactionStart();
            try {
                $query = $db->createQuery()
                    ->delete($db->quoteName('#__sportsmanagement_match_player'))
                    ->whereIn($db->quoteName('id'), $ids, ParameterType::INTEGER);
                $db->setQuery($query);
                $db->execute();
                $db->transactionCommit();
            } catch (\Throwable $error) {
                $db->transactionRollback();
                throw $error;
            }

            return true;
        } catch (\Throwable $error) {
            $this->logFailure($error);
            return false;
        }
    }

    public function saveComment(array $data): int|false
    {
        $matchId = self::positiveId($data['match_id'] ?? null);

        if ($matchId === null
            || !is_scalar($data['event_time'] ?? null)
            || !is_scalar($data['notes'] ?? null)
            || !is_scalar($data['type'] ?? '')
            || empty($data['event_time']) || empty($data['notes'])) {
            return false;
        }

        $db = $this->joomlaDatabase;

        try {
            $comment = new \stdClass();
            $comment->event_time = (string) ($data['event_time'] ?? '');
            $comment->match_id = $matchId;
            $comment->type = (string) ($data['type'] ?? '');
            $comment->notes = (string) ($data['notes'] ?? '');
            $comment->modified = $this->modified;
            $comment->modified_by = $this->userId;

            $db->insertObject('#__sportsmanagement_match_commentary', $comment);
            $commentId = (int) $db->insertid();

            return $commentId > 0 ? $commentId : false;
        } catch (\Throwable $e) {
            $this->logFailure($e);
            return false;
        }
    }

    public function deleteEvent(int $eventId): bool
    {
        return $this->deleteById($this->joomlaDatabase, '#__sportsmanagement_match_event', $eventId);
    }

    public function deleteCommentary(int $commentaryId): bool
    {
        return $this->deleteById($this->joomlaDatabase, '#__sportsmanagement_match_commentary', $commentaryId);
    }

    private function createEventStatisticIfNeeded(array $data): void
    {
        $db = $this->joomlaDatabase;
        $matchId = (int) ($data['match_id'] ?? 0);
        $teamPlayerId = (int) ($data['teamplayer_id'] ?? 0);
        $eventTypeId = (int) ($data['event_type_id'] ?? 0);
        $statisticId = 0;
        $statisticValue = 0;

        $query = $db->createQuery()
            ->select([
                $db->quoteName('st.id'),
                $db->quoteName('st.params'),
                $db->quoteName('st.class'),
            ])
            ->from($db->quoteName('#__sportsmanagement_statistic', 'st'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_position_statistic', 'possta')
                . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('possta.statistic_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_match_player', 'matplay')
                . ' ON ' . $db->quoteName('matplay.project_position_id') . ' = ' . $db->quoteName('possta.position_id')
            )
            ->where($db->quoteName('matplay.match_id') . ' = :matchId')
            ->where($db->quoteName('matplay.teamplayer_id') . ' = :teamPlayerId')
            ->bind(':matchId', $matchId, ParameterType::INTEGER)
            ->bind(':teamPlayerId', $teamPlayerId, ParameterType::INTEGER);
        $db->setQuery($query);
        $statistics = $db->loadObjectList() ?: [];

        foreach ($statistics as $statistic) {
            if ((string) ($statistic->class ?? '') !== 'basic') {
                continue;
            }

            $params = json_decode((string) ($statistic->params ?? ''), true);

            if (!is_array($params) || (int) ($params['event_id'] ?? 0) !== $eventTypeId) {
                continue;
            }

            $statisticId = (int) ($statistic->id ?? 0);
            $statisticValue = $params['event_value'] ?? 0;
        }

        if (!$statisticId || !$statisticValue) {
            return;
        }

        $query = $db->createQuery()
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__sportsmanagement_match_statistic'))
            ->where($db->quoteName('match_id') . ' = :matchId')
            ->where($db->quoteName('teamplayer_id') . ' = :teamPlayerId')
            ->where($db->quoteName('statistic_id') . ' = :statisticId')
            ->bind(':matchId', $matchId, ParameterType::INTEGER)
            ->bind(':teamPlayerId', $teamPlayerId, ParameterType::INTEGER)
            ->bind(':statisticId', $statisticId, ParameterType::INTEGER);
        $db->setQuery($query, 0, 1);

        if ($db->loadResult()) {
            return;
        }

        $record = new \stdClass();
        $record->match_id = $matchId;
        $record->projectteam_id = (int) ($data['projectteam_id'] ?? 0);
        $record->teamplayer_id = $teamPlayerId;
        $record->statistic_id = $statisticId;
        $record->value = $statisticValue;
        $record->modified = $this->modified;
        $record->modified_by = $this->userId;
        $db->insertObject('#__sportsmanagement_match_statistic', $record);
    }

    private function deleteById(DatabaseInterface $db, string $table, int $id): bool
    {
        if ($id <= 0) {
            return false;
        }

        try {
            $query = $db->createQuery()
                ->delete($db->quoteName($table))
                ->where($db->quoteName('id') . ' = :id')
                ->bind(':id', $id, ParameterType::INTEGER);
            $db->setQuery($query);
            $db->execute();

            return true;
        } catch (\Throwable $e) {
            $this->logFailure($e);
            return false;
        }
    }

    /** Prevent malformed POST values from being silently cast to unrelated IDs. */
    private static function positiveId(mixed $value, bool $allowZero = false): ?int
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

    private function logFailure(\Throwable $e): void
    {
        Log::add($e->getMessage(), Log::ERROR, 'jsmerror');
    }
}
