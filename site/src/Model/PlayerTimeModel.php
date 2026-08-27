<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Throwable;

/**
 * Native Joomla 5/6 player participation/time calculator.
 *
 * Replaces the legacy Player model's match-participation DB helper paths and
 * keeps all reads on the selected SportsManagement database connection.
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
        $regularResultType = $projectId > 0 && $addTime > 0 ? 0 : null;

        $starters = $this->loadParticipationAggregate(
            $playerId,
            $projectId,
            $matchId,
            $regularResultType,
            'starter'
        );
        $result += $starters->totalmatch * $gameRegularTime;

        if ($projectId > 0 && $addTime > 0) {
            $extraStarters = $this->loadParticipationAggregate(
                $playerId,
                $projectId,
                $matchId,
                1,
                'starter'
            );
            $result += $extraStarters->totalmatch * ($gameRegularTime + $addTime);
        }

        $substitutionsIn = $this->loadParticipationAggregate(
            $playerId,
            $projectId,
            $matchId,
            $regularResultType,
            'sub_in'
        );
        $result += ($substitutionsIn->totalmatch * $gameRegularTime) - $substitutionsIn->totaltime;

        if ($projectId > 0 && $addTime > 0) {
            $extraSubstitutionsIn = $this->loadParticipationAggregate(
                $playerId,
                $projectId,
                $matchId,
                1,
                'sub_in'
            );
            $result += ($extraSubstitutionsIn->totalmatch * ($gameRegularTime + $addTime))
                - $extraSubstitutionsIn->totaltime;
        }

        $substitutionsOut = $this->loadParticipationAggregate(
            $playerId,
            $projectId,
            $matchId,
            $regularResultType,
            'sub_out'
        );
        $result += $substitutionsOut->totaltime - ($substitutionsOut->totalmatch * $gameRegularTime);

        if ($projectId > 0 && $addTime > 0) {
            $extraSubstitutionsOut = $this->loadParticipationAggregate(
                $playerId,
                $projectId,
                $matchId,
                1,
                'sub_out'
            );
            $result += $extraSubstitutionsOut->totaltime
                - ($extraSubstitutionsOut->totalmatch * ($gameRegularTime + $addTime));
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

    /**
     * Preserve the legacy Player::getInOutStats() contract for player/roster templates.
     */
    public function getInOutStats(
        int $projectId = 0,
        int $projectTeamId = 0,
        int $teamPlayerId = 0,
        int $gameRegularTime = 90,
        int $matchId = 0,
        int $cfgWhichDatabase = 0,
        int $teamId = 0,
        int $personId = 0
    ): object {
        if ($cfgWhichDatabase === 1) {
            $this->setDatabaseSelector(1);
        }

        $stats = (object) [
            'played' => 0,
            'started' => 0,
            'sub_in' => 0,
            'sub_out' => 0,
            'in' => 0,
            'out' => 0,
            'playedtime' => 0,
        ];

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('m.id', 'mid'),
                $db->quoteName('mp.came_in'),
                $db->quoteName('mp.out'),
                $db->quoteName('mp.teamplayer_id'),
                $db->quoteName('mp.in_for'),
                $db->quoteName('mp.in_out_time'),
            ])
            ->from($db->quoteName('#__sportsmanagement_match', 'm'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_match_player', 'mp') . ' ON ' . $db->quoteName('mp.match_id') . ' = ' . $db->quoteName('m.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_person_id', 'tp1') . ' ON ' . $db->quoteName('tp1.id') . ' = ' . $db->quoteName('mp.teamplayer_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st1') . ' ON ' . $db->quoteName('st1.team_id') . ' = ' . $db->quoteName('tp1.team_id'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project_team', 'pt')
                . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st1.id')
                . ' AND (' . $db->quoteName('m.projectteam2_id') . ' = ' . $db->quoteName('pt.id')
                . ' OR ' . $db->quoteName('m.projectteam1_id') . ' = ' . $db->quoteName('pt.id') . ')'
            )
            ->join('INNER', $db->quoteName('#__sportsmanagement_project', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('pt.project_id'))
            ->where($db->quoteName('m.published') . ' = 1')
            ->where($db->quoteName('p.published') . ' = 1');

        if ($teamId > 0) {
            $query->where($db->quoteName('st1.team_id') . ' = ' . $teamId)
                ->where($db->quoteName('tp1.team_id') . ' = ' . $teamId);
        }

        if ($personId > 0) {
            $query->where($db->quoteName('tp1.person_id') . ' = ' . $personId);
        }

        if ($matchId > 0) {
            $query->where($db->quoteName('m.id') . ' = ' . $matchId);
        }

        if ($teamPlayerId > 0) {
            $query->where(
                '(' . $db->quoteName('mp.teamplayer_id') . ' = ' . $teamPlayerId
                . ' OR ' . $db->quoteName('mp.in_for') . ' = ' . $teamPlayerId . ')'
            );
        }

        if ($projectId > 0) {
            $query->where($db->quoteName('pt.project_id') . ' = ' . $projectId);
        }

        if ($projectTeamId > 0) {
            $query->where(
                '(' . $db->quoteName('m.projectteam1_id') . ' = ' . $projectTeamId
                . ' OR ' . $db->quoteName('m.projectteam2_id') . ' = ' . $projectTeamId . ')'
            );
        }

        try {
            $db->setQuery($query);
            $rows = $db->loadObjectList() ?: [];
        } catch (Throwable $e) {
            $this->reportDatabaseError($e);
            return $stats;
        }

        foreach ($rows as $row) {
            $cameIn = (int) ($row->came_in ?? 0);
            $rowTeamPlayerId = (int) ($row->teamplayer_id ?? 0);
            $inFor = (int) ($row->in_for ?? 0);

            $stats->played += $cameIn === 0 ? 1 : 0;
            $stats->played += $cameIn === 1 && $rowTeamPlayerId === $teamPlayerId ? 1 : 0;
            $stats->started += $cameIn === 0 ? 1 : 0;
            $stats->sub_in += $cameIn === 1 && $rowTeamPlayerId === $teamPlayerId ? 1 : 0;
            $stats->sub_out += ((int) ($row->out ?? 0) === 1 || $inFor === $teamPlayerId) ? 1 : 0;
        }

        // Some historic templates used in/out while others used sub_in/sub_out.
        $stats->in = $stats->sub_in;
        $stats->out = $stats->sub_out;

        return $stats;
    }

    private function loadParticipationAggregate(
        int $playerId,
        int $projectId,
        ?int $matchId,
        ?int $resultType,
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

            if ($resultType !== null) {
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
