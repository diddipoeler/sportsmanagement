<?php
namespace Diddipoeler\Component\SportsManagement\Site\Service;

\defined('_JEXEC') or die;

use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseInterface;

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
        if (!empty($data['useeventtime']) && empty($data['event_time'])) {
            return false;
        }

        if (empty($data['event_sum'])) {
            return false;
        }

        $db = $this->joomlaDatabase;

        try {
            if (empty($data['doubleevents'])) {
                $query = $db->getQuery(true)
                    ->select($db->quoteName('id'))
                    ->from($db->quoteName('#__sportsmanagement_match_event'))
                    ->where($db->quoteName('match_id') . ' = ' . (int) ($data['match_id'] ?? 0))
                    ->where($db->quoteName('projectteam_id') . ' = ' . (int) ($data['projectteam_id'] ?? 0))
                    ->where($db->quoteName('teamplayer_id') . ' = ' . (int) ($data['teamplayer_id'] ?? 0))
                    ->where($db->quoteName('event_time') . ' = ' . $db->quote((string) ($data['event_time'] ?? '')))
                    ->where($db->quoteName('event_sum') . ' = ' . $db->quote((string) ($data['event_sum'] ?? '')));
                $db->setQuery($query, 0, 1);

                if ($db->loadResult()) {
                    return false;
                }
            }

            $event = new \stdClass();
            $event->match_id = (int) ($data['match_id'] ?? 0);
            $event->projectteam_id = (int) ($data['projectteam_id'] ?? 0);
            $event->teamplayer_id = (int) ($data['teamplayer_id'] ?? 0);
            $event->event_time = (string) ($data['event_time'] ?? '');
            $event->event_type_id = (int) ($data['event_type_id'] ?? 0);
            $event->event_sum = (string) ($data['event_sum'] ?? '');
            $event->notice = (string) ($data['notice'] ?? '');
            $event->notes = (string) ($data['notes'] ?? '');
            $event->modified = $this->modified;
            $event->modified_by = $this->userId;

            $db->insertObject('#__sportsmanagement_match_event', $event);
            $eventId = (int) $db->insertid();

            $this->createEventStatisticIfNeeded($data);

            return $eventId > 0 ? $eventId : false;
        } catch (\Throwable $e) {
            $this->logFailure($e);
            return false;
        }
    }

    public function saveSubstitution(array $data): bool
    {
        // Preserve the legacy validation contract. Although the historical
        // implementation contains fallback branches for zero values, they are
        // unreachable after these checks and therefore are not broadened here.
        if (empty($data['project_position_id'])
            || empty($data['in_out_time'])
            || empty($data['in'])
            || empty($data['out'])
            || empty($data['matchid'])) {
            return false;
        }

        $playerIn = (int) $data['in'];
        $playerOut = (int) $data['out'];
        $matchId = (int) $data['matchid'];
        $positionId = (int) $data['project_position_id'];
        $inOutTime = (string) $data['in_out_time'];
        $db = $this->sportsDatabase;

        try {
            $query = $db->getQuery(true)
                ->select($db->quoteName('id'))
                ->from($db->quoteName('#__sportsmanagement_match_player'))
                ->where($db->quoteName('match_id') . ' = ' . $matchId)
                ->where($db->quoteName('teamplayer_id') . ' = ' . $playerIn)
                ->where($db->quoteName('in_for') . ' = ' . $playerOut)
                ->where($db->quoteName('in_out_time') . ' = ' . $db->quote($inOutTime))
                ->where($db->quoteName('came_in') . ' = ' . self::MATCH_ROSTER_SUBSTITUTE_IN);
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
            $query = $db->getQuery(true)
                ->delete($db->quoteName('#__sportsmanagement_match_player'))
                ->where(
                    $db->quoteName('id') . ' IN ('
                    . $substitutionId . ',' . ($substitutionId + 1) . ')'
                );
            $db->setQuery($query);
            $db->execute();

            return true;
        } catch (\Throwable $e) {
            $this->logFailure($e);
            return false;
        }
    }

    public function saveComment(array $data): int|false
    {
        if (empty($data['event_time']) || empty($data['notes'])) {
            return false;
        }

        $db = $this->joomlaDatabase;

        try {
            $comment = new \stdClass();
            $comment->event_time = (string) ($data['event_time'] ?? '');
            $comment->match_id = (int) ($data['match_id'] ?? 0);
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

        $query = $db->getQuery(true)
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
            ->where($db->quoteName('matplay.match_id') . ' = ' . $matchId)
            ->where($db->quoteName('matplay.teamplayer_id') . ' = ' . $teamPlayerId);
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

        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__sportsmanagement_match_statistic'))
            ->where($db->quoteName('match_id') . ' = ' . $matchId)
            ->where($db->quoteName('teamplayer_id') . ' = ' . $teamPlayerId)
            ->where($db->quoteName('statistic_id') . ' = ' . $statisticId);
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
            $query = $db->getQuery(true)
                ->delete($db->quoteName($table))
                ->where($db->quoteName('id') . ' = ' . $id);
            $db->setQuery($query);
            $db->execute();

            return true;
        } catch (\Throwable $e) {
            $this->logFailure($e);
            return false;
        }
    }

    private function logFailure(\Throwable $e): void
    {
        Log::add($e->getMessage(), Log::ERROR, 'jsmerror');
    }
}
