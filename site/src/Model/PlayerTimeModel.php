<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Throwable;

/**
 * Native Joomla 5/6 player-time calculator.
 *
 * This replaces the legacy sportsmanagementModelPlayer::getTimePlayed() DB
 * helper path and keeps all reads on the selected SportsManagement database.
 */
final class PlayerTimeModel extends SportsManagementModel
{
    public function getTimePlayed(
        int $playerId,
        int $gameRegularTime,
        ?int $matchId = null,
        ?array $cards = null,
        int $projectId = 0,
        int $addTime = 0
    ): int|float {
        if ($playerId <= 0 || $gameRegularTime <= 0) {
            return 0;
        }

        $matchId = ($matchId ?? 0) > 0 ? (int) $matchId : null;
        $projectId = max(0, $projectId);
        $addTime = max(0, $addTime);
        $result = 0.0;

        $regularDuration = $gameRegularTime;
        $extendedDuration = $gameRegularTime + $addTime;

        $starters = $this->loadParticipationAggregate(
            $playerId,
            $projectId,
            $matchId,
            0,
            'starter'
        );
        $result += $starters->totalmatch * $regularDuration;

        if ($projectId > 0 && $addTime > 0) {
            $extraStarters = $this->loadParticipationAggregate(
                $playerId,
                $projectId,
                $matchId,
                1,
                'starter'
            );
            $result += $extraStarters->totalmatch * $extendedDuration;
        }

        $substitutionsIn = $this->loadParticipationAggregate(
            $playerId,
            $projectId,
            $matchId,
            0,
            'sub_in'
        );
        $result += ($substitutionsIn->totalmatch * $regularDuration) - $substitutionsIn->totaltime;

        if ($projectId > 0 && $addTime > 0) {
            $extraSubstitutionsIn = $this->loadParticipationAggregate(
                $playerId,
                $projectId,
                $matchId,
                1,
                'sub_in'
            );
            $result += ($extraSubstitutionsIn->totalmatch * $extendedDuration) - $extraSubstitutionsIn->totaltime;
        }

        $substitutionsOut = $this->loadParticipationAggregate(
            $playerId,
            $projectId,
            $matchId,
            0,
            'sub_out'
        );
        $result += $substitutionsOut->totaltime - ($substitutionsOut->totalmatch * $regularDuration);

        if ($projectId > 0 && $addTime > 0) {
            $extraSubstitutionsOut = $this->loadParticipationAggregate(
                $playerId,
                $projectId,
                $matchId,
                1,
                'sub_out'
            );
            $result += $extraSubstitutionsOut->totaltime - ($extraSubstitutionsOut->totalmatch * $extendedDuration);
        }

        $cardIds = array_values(array_unique(array_filter(
            array_map('intval', $cards ?? []),
            static fn(int $id): bool => $id > 0
        )));

        if ($cardIds !== []) {
            foreach ($this->loadDismissalEvents($playerId, $projectId, $matchId, $cardIds) as $event) {
                $result -= $gameRegularTime - (float) ($event->event_time ?? 0);
            }
        }

        return $result;
    }

    private function loadParticipationAggregate(
        int $playerId,
        int $projectId,
        ?int $matchId,
        int $resultType,
        string $mode
    ): object {
        $db = $this->getDatabase();
        $query = $db->getQuery(true);

        if ($mode === 'starter') {
            $query->select('COUNT(DISTINCT mp.match_id) AS totalmatch, 0 AS totaltime')
                ->where($db->quoteName('mp.teamplayer_id') . ' = ' . $playerId)
                ->where($db->quoteName('mp.came_in') . ' = 0');
        } elseif ($mode === 'sub_in') {
            $query->select('COUNT(DISTINCT mp.match_id) AS totalmatch, COALESCE(SUM(mp.in_out_time), 0) AS totaltime')
                ->where($db->quoteName('mp.teamplayer_id') . ' = ' . $playerId)
                ->where($db->quoteName('mp.came_in') . ' = 1')
                ->where($db->quoteName('mp.in_for') . ' IS NOT NULL');
        } else {
            $query->select('COUNT(DISTINCT mp.match_id) AS totalmatch, COALESCE(SUM(mp.in_out_time), 0) AS totaltime')
                ->where($db->quoteName('mp.in_for') . ' = ' . $playerId)
                ->where($db->quoteName('mp.came_in') . ' = 1');
        }

        $query->from($db->quoteName('#__sportsmanagement_match_player', 'mp'));

        if ($projectId > 0) {
            $query->join('INNER', $db->quoteName('#__sportsmanagement_match', 'm') . ' ON ' . $db->quoteName('m.id') . ' = ' . $db->quoteName('mp.match_id'))
                ->join('INNER', $db->quoteName('#__sportsmanagement_round', 'r') . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id'))
                ->where($db->quoteName('r.project_id') . ' = ' . $projectId);

            if ($resultType === 0 || $resultType === 1) {
                $query->where($db->quoteName('m.match_result_type') . ' = ' . $resultType);
            }
        }

        if ($matchId !== null) {
            $query->where($db->quoteName('mp.match_id') . ' = ' . $matchId);
        }

        try {
            $db->setQuery($query, 0, 1);
            $row = $db->loadObject();
        } catch (Throwable $e) {
            $this->reportDatabaseError($e);
            $row = null;
        }

        return (object) [
            'totalmatch' => (int) ($row->totalmatch ?? 0),
            'totaltime' => (float) ($row->totaltime ?? 0),
        ];
    }

    private function loadDismissalEvents(int $playerId, int $projectId, ?int $matchId, array $cardIds): array
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('me.*')
            ->from($db->quoteName('#__sportsmanagement_match_event', 'me'))
            ->where($db->quoteName('me.teamplayer_id') . ' = ' . $playerId)
            ->where($db->quoteName('me.event_type_id') . ' IN (' . implode(',', $cardIds) . ')');

        if ($projectId > 0) {
            $query->join('INNER', $db->quoteName('#__sportsmanagement_match', 'm') . ' ON ' . $db->quoteName('m.id') . ' = ' . $db->quoteName('me.match_id'))
                ->join('INNER', $db->quoteName('#__sportsmanagement_round', 'r') . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id'))
                ->where($db->quoteName('r.project_id') . ' = ' . $projectId);
        }

        if ($matchId !== null) {
            $query->where($db->quoteName('me.match_id') . ' = ' . $matchId);
        }

        try {
            $db->setQuery($query);
            return $db->loadObjectList() ?: [];
        } catch (Throwable $e) {
            $this->reportDatabaseError($e);
            return [];
        }
    }

    private function reportDatabaseError(Throwable $e): void
    {
        Factory::getApplication()->enqueueMessage(
            Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()),
            'error'
        );
    }
}
