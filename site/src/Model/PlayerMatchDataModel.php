<?php
/**
 * Native Joomla 5/6 reader for the player's per-match history and events.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use stdClass;
use Throwable;

/**
 * Native Joomla 5/6 reader for the player's per-match history and events.
 */
final class PlayerMatchDataModel extends SportsManagementProjectModel
{
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);
    }

    public function getGames(array $teamPlayers): array
    {
        $teamPlayerIds = $this->teamPlayerIds($teamPlayers);

        if ($this->projectId <= 0 || $teamPlayerIds === []) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('m.id'),
                $db->quoteName('m.match_date'),
                $db->quoteName('m.projectteam1_id'),
                $db->quoteName('m.projectteam2_id'),
                $db->quoteName('m.team1_result'),
                $db->quoteName('m.team2_result'),
                $db->quoteName('m.match_result_type'),
                $db->quoteName('t1.id', 'team1'),
                $db->quoteName('t1.name', 'home_name'),
                $db->quoteName('t2.id', 'team2'),
                $db->quoteName('t2.name', 'away_name'),
                $db->quoteName('mp.teamplayer_id'),
                $db->quoteName('r.roundcode'),
                $db->quoteName('r.project_id'),
                "CONCAT_WS(':', m.id, CONCAT_WS('_', t1.alias, t2.alias)) AS match_slug",
                $db->quoteName('c1.logo_big', 'home_logo'),
                $db->quoteName('c2.logo_big', 'away_logo'),
            ])
            ->from($db->quoteName('#__sportsmanagement_match', 'm'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_match_player', 'mp') . ' ON ' . $db->quoteName('mp.match_id') . ' = ' . $db->quoteName('m.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_round', 'r') . ' ON ' . $db->quoteName('m.round_id') . ' = ' . $db->quoteName('r.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('r.project_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt1') . ' ON ' . $db->quoteName('m.projectteam1_id') . ' = ' . $db->quoteName('pt1.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st1') . ' ON ' . $db->quoteName('st1.id') . ' = ' . $db->quoteName('pt1.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't1') . ' ON ' . $db->quoteName('t1.id') . ' = ' . $db->quoteName('st1.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_club', 'c1') . ' ON ' . $db->quoteName('c1.id') . ' = ' . $db->quoteName('t1.club_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt2') . ' ON ' . $db->quoteName('m.projectteam2_id') . ' = ' . $db->quoteName('pt2.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st2') . ' ON ' . $db->quoteName('st2.id') . ' = ' . $db->quoteName('pt2.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't2') . ' ON ' . $db->quoteName('t2.id') . ' = ' . $db->quoteName('st2.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_club', 'c2') . ' ON ' . $db->quoteName('c2.id') . ' = ' . $db->quoteName('t2.club_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_person_id', 'stp') . ' ON ' . $db->quoteName('stp.id') . ' = ' . $db->quoteName('mp.teamplayer_id'))
            ->where($db->quoteName('mp.teamplayer_id') . ' IN (' . implode(',', $teamPlayerIds) . ')')
            ->where($db->quoteName('p.id') . ' = ' . $this->projectId)
            ->where($db->quoteName('m.published') . ' = 1')
            ->where($db->quoteName('p.published') . ' = 1')
            ->order($db->quoteName('m.match_date') . ' ASC');

        try {
            $db->setQuery($query);
            $games = $db->loadObjectList() ?: [];
        } catch (Throwable $e) {
            $this->reportDatabaseError($e);
            return [];
        }

        foreach ($games as $game) {
            $inOutStats = $this->getInOutStats(
                (int) $game->project_id,
                (int) $game->projectteam1_id,
                (int) $game->teamplayer_id,
                (int) $game->id
            );
            $game->started = $inOutStats->started;
            $game->sub_in = $inOutStats->sub_in;
            $game->sub_out = $inOutStats->sub_out;
            $game->playedtime = 0;
        }

        return $games;
    }

    public function getGamesEvents(array $teamPlayers, bool $showEventsAsSum = true): array
    {
        $teamPlayerIds = $this->teamPlayerIds($teamPlayers);

        if ($teamPlayerIds === []) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->createQuery();

        if ($showEventsAsSum) {
            $query->select('SUM(' . $db->quoteName('me.event_sum') . ') AS value');
        } else {
            $query->select('COUNT(' . $db->quoteName('me.event_type_id') . ') AS value');
        }

        $query
            ->select([
                $db->quoteName('me.match_id'),
                $db->quoteName('me.event_type_id'),
            ])
            ->from($db->quoteName('#__sportsmanagement_match_event', 'me'))
            ->where($db->quoteName('me.teamplayer_id') . ' IN (' . implode(',', $teamPlayerIds) . ')')
            ->group([
                $db->quoteName('me.match_id'),
                $db->quoteName('me.event_type_id'),
            ]);

        try {
            $db->setQuery($query);
            $events = $db->loadObjectList() ?: [];
        } catch (Throwable $e) {
            $this->reportDatabaseError($e);
            return [];
        }

        $gameEvents = [];
        foreach ($events as $event) {
            $gameEvents[(int) $event->match_id][(int) $event->event_type_id] = $event->value;
        }

        return $gameEvents;
    }

    private function getInOutStats(int $projectId, int $projectTeamId, int $teamPlayerId, int $matchId): object
    {
        $stats = new stdClass();
        $stats->played = 0;
        $stats->started = 0;
        $stats->sub_in = 0;
        $stats->sub_out = 0;

        if ($projectId <= 0 || $teamPlayerId <= 0 || $matchId <= 0) {
            return $stats;
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
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
            ->where($db->quoteName('m.id') . ' = ' . $matchId)
            ->where('(' . $db->quoteName('mp.teamplayer_id') . ' = ' . $teamPlayerId . ' OR ' . $db->quoteName('mp.in_for') . ' = ' . $teamPlayerId . ')')
            ->where($db->quoteName('pt.project_id') . ' = ' . $projectId)
            ->where('(' . $db->quoteName('m.projectteam1_id') . ' = ' . $projectTeamId . ' OR ' . $db->quoteName('m.projectteam2_id') . ' = ' . $projectTeamId . ')')
            ->where($db->quoteName('m.published') . ' = 1')
            ->where($db->quoteName('p.published') . ' = 1');

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

        return $stats;
    }

    private function teamPlayerIds(array $teamPlayers): array
    {
        $ids = [];

        foreach ($teamPlayers as $teamPlayer) {
            $id = (int) ($teamPlayer->id ?? 0);
            if ($id > 0) {
                $ids[$id] = $id;
            }
        }

        return array_values($ids);
    }

    private function reportDatabaseError(Throwable $e): void
    {
        $this->siteApplication()->enqueueMessage(
            Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()),
            'error'
        );
    }
}
