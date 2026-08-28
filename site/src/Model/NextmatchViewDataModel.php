<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Throwable;

/**
 * Native Joomla 5/6 reader for the remaining next-match view data.
 */
final class NextmatchViewDataModel extends SportsManagementProjectModel
{
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);
    }

    public function getProjectEvents(int $projectId = 0): array
    {
        $projectId = $projectId > 0 ? $projectId : $this->projectId;

        if ($projectId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('et.id'),
                $db->quoteName('et.name'),
                $db->quoteName('et.icon'),
            ])
            ->from($db->quoteName('#__sportsmanagement_eventtype', 'et'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_position_eventtype', 'pet')
                . ' ON ' . $db->quoteName('pet.eventtype_id') . ' = ' . $db->quoteName('et.id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project_position', 'ppos')
                . ' ON ' . $db->quoteName('ppos.position_id') . ' = ' . $db->quoteName('pet.position_id')
            )
            ->where($db->quoteName('ppos.project_id') . ' = ' . $projectId)
            ->where($db->quoteName('et.published') . ' = 1')
            ->group([
                $db->quoteName('et.id'),
                $db->quoteName('et.name'),
                $db->quoteName('et.icon'),
                $db->quoteName('pet.ordering'),
            ])
            ->order($db->quoteName('pet.ordering') . ' ASC');

        try {
            $db->setQuery($query);
            return $db->loadObjectList() ?: [];
        } catch (Throwable $e) {
            $this->reportDatabaseError($e);
            return [];
        }
    }

    /**
     * Aggregate project event totals in the structure consumed by the
     * next-match event ranking layouts.
     *
     * @return array<int, object>
     */
    public function getProjectEventTotals(int $projectId = 0): array
    {
        $projectId = $projectId > 0 ? $projectId : $this->projectId;
        if ($projectId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('p.id', 'playerid'),
                $db->quoteName('p.firstname', 'firstname1'),
                $db->quoteName('p.nickname', 'nickname1'),
                $db->quoteName('p.lastname', 'lastname1'),
                $db->quoteName('tp.picture', 'tppicture1'),
                $db->quoteName('t.name', 'team_name'),
                $db->quoteName('me.event_type_id'),
                'SUM(' . $db->quoteName('me.event_sum') . ') AS ' . $db->quoteName('event_sum'),
            ])
            ->from($db->quoteName('#__sportsmanagement_match_event', 'me'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_match', 'm') . ' ON ' . $db->quoteName('m.id') . ' = ' . $db->quoteName('me.match_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_round', 'r') . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_person_id', 'tp') . ' ON ' . $db->quoteName('tp.id') . ' = ' . $db->quoteName('me.teamplayer_id'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('tp.team_id')
                . ' AND ' . $db->quoteName('st.season_id') . ' = ' . $db->quoteName('tp.season_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project_team', 'pt')
                . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st.id')
                . ' AND ' . $db->quoteName('pt.project_id') . ' = ' . $db->quoteName('r.project_id')
            )
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't') . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_person', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('tp.person_id'))
            ->where($db->quoteName('r.project_id') . ' = ' . $projectId)
            ->where($db->quoteName('m.published') . ' = 1')
            ->where($db->quoteName('p.published') . ' = 1')
            ->group([
                $db->quoteName('p.id'),
                $db->quoteName('p.firstname'),
                $db->quoteName('p.nickname'),
                $db->quoteName('p.lastname'),
                $db->quoteName('tp.picture'),
                $db->quoteName('t.name'),
                $db->quoteName('me.event_type_id'),
            ])
            ->order([
                $db->quoteName('t.name') . ' ASC',
                $db->quoteName('p.lastname') . ' ASC',
                $db->quoteName('p.firstname') . ' ASC',
            ]);

        try {
            $db->setQuery($query);
            $rows = $db->loadObjectList() ?: [];
        } catch (Throwable $e) {
            $this->reportDatabaseError($e);
            return [];
        }

        $players = [];
        foreach ($rows as $row) {
            $playerId = (int) ($row->playerid ?? 0);
            $eventTypeId = (int) ($row->event_type_id ?? 0);
            if ($playerId <= 0 || $eventTypeId <= 0) {
                continue;
            }

            if (!isset($players[$playerId])) {
                $players[$playerId] = (object) [
                    'playerid' => $playerId,
                    'firstname1' => (string) ($row->firstname1 ?? ''),
                    'nickname1' => (string) ($row->nickname1 ?? ''),
                    'lastname1' => (string) ($row->lastname1 ?? ''),
                    'tppicture1' => (string) ($row->tppicture1 ?? ''),
                    'team_name' => (string) ($row->team_name ?? ''),
                    'events' => [],
                ];
            }

            $players[$playerId]->events[$eventTypeId] = (object) [
                'event_sum' => (float) ($row->event_sum ?? 0),
            ];
        }

        return $players;
    }

    /** @return array<int, object> */
    public function getMatchEvents(int $matchId): array
    {
        if ($matchId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('me.event_type_id'),
                $db->quoteName('me.id', 'event_id'),
                $db->quoteName('me.event_time'),
                $db->quoteName('me.notice'),
                $db->quoteName('me.projectteam_id', 'ptid'),
                $db->quoteName('me.event_sum'),
                $db->quoteName('et.name', 'eventtype_name'),
                $db->quoteName('et.icon', 'eventtype_icon'),
                $db->quoteName('t.name', 'team_name'),
                $db->quoteName('tp.picture', 'tppicture1'),
                $db->quoteName('p.firstname', 'firstname1'),
                $db->quoteName('p.nickname', 'nickname1'),
                $db->quoteName('p.lastname', 'lastname1'),
                $db->quoteName('p.picture', 'picture1'),
                $db->quoteName('p.id', 'playerid'),
            ])
            ->from($db->quoteName('#__sportsmanagement_match_event', 'me'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_eventtype', 'et') . ' ON ' . $db->quoteName('et.id') . ' = ' . $db->quoteName('me.event_type_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON ' . $db->quoteName('pt.id') . ' = ' . $db->quoteName('me.projectteam_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't') . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_person_id', 'tp') . ' ON ' . $db->quoteName('tp.id') . ' = ' . $db->quoteName('me.teamplayer_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_person', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('tp.person_id'))
            ->where($db->quoteName('me.match_id') . ' = ' . $matchId)
            ->where('COALESCE(' . $db->quoteName('p.published') . ', 1) = 1')
            ->order([
                '(' . $db->quoteName('me.event_time') . ' + 0) ASC',
                $db->quoteName('me.event_type_id') . ' ASC',
                $db->quoteName('me.id') . ' ASC',
            ]);

        try {
            $db->setQuery($query);
            return $db->loadObjectList() ?: [];
        } catch (Throwable $e) {
            $this->reportDatabaseError($e);
            return [];
        }
    }

    /** @return array<int, object> */
    public function getMatchSubstitutions(int $matchId): array
    {
        if ($matchId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('mp.in_out_time'),
                $db->quoteName('pt.id', 'ptid'),
                $db->quoteName('pin.firstname', 'firstname'),
                $db->quoteName('pin.nickname', 'nickname'),
                $db->quoteName('pin.lastname', 'lastname'),
                $db->quoteName('pout.firstname', 'out_firstname'),
                $db->quoteName('pout.nickname', 'out_nickname'),
                $db->quoteName('pout.lastname', 'out_lastname'),
                $db->quoteName('posin.name', 'in_position'),
                $db->quoteName('posout.name', 'out_position'),
            ])
            ->from($db->quoteName('#__sportsmanagement_match_player', 'mp'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_match', 'm') . ' ON ' . $db->quoteName('m.id') . ' = ' . $db->quoteName('mp.match_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_round', 'r') . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_person_id', 'tpin') . ' ON ' . $db->quoteName('tpin.id') . ' = ' . $db->quoteName('mp.teamplayer_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_person', 'pin') . ' ON ' . $db->quoteName('pin.id') . ' = ' . $db->quoteName('tpin.person_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_person_id', 'tpout') . ' ON ' . $db->quoteName('tpout.id') . ' = ' . $db->quoteName('mp.in_for'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_person', 'pout') . ' ON ' . $db->quoteName('pout.id') . ' = ' . $db->quoteName('tpout.person_id'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('tpin.team_id')
                . ' AND ' . $db->quoteName('st.season_id') . ' = ' . $db->quoteName('tpin.season_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project_team', 'pt')
                . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st.id')
                . ' AND ' . $db->quoteName('pt.project_id') . ' = ' . $db->quoteName('r.project_id')
            )
            ->join('LEFT', $db->quoteName('#__sportsmanagement_position', 'posin') . ' ON ' . $db->quoteName('posin.id') . ' = ' . $db->quoteName('mp.project_position_id'))
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_match_player', 'mpout')
                . ' ON ' . $db->quoteName('mpout.match_id') . ' = ' . $db->quoteName('mp.match_id')
                . ' AND ' . $db->quoteName('mpout.teamplayer_id') . ' = ' . $db->quoteName('mp.in_for')
            )
            ->join('LEFT', $db->quoteName('#__sportsmanagement_position', 'posout') . ' ON ' . $db->quoteName('posout.id') . ' = ' . $db->quoteName('mpout.project_position_id'))
            ->where($db->quoteName('mp.match_id') . ' = ' . $matchId)
            ->where($db->quoteName('mp.came_in') . ' > 0')
            ->order($db->quoteName('mp.in_out_time') . ' ASC');

        try {
            $db->setQuery($query);
            return $db->loadObjectList() ?: [];
        } catch (Throwable $e) {
            $this->reportDatabaseError($e);
            return [];
        }
    }

    public function getMatchText(int $matchId): ?object
    {
        if ($matchId <= 0) {
            return null;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                'm.*',
                $db->quoteName('t1.name', 't1name'),
                $db->quoteName('t2.name', 't2name'),
            ])
            ->from($db->quoteName('#__sportsmanagement_match', 'm'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt1') . ' ON ' . $db->quoteName('m.projectteam1_id') . ' = ' . $db->quoteName('pt1.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st1') . ' ON ' . $db->quoteName('st1.id') . ' = ' . $db->quoteName('pt1.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't1') . ' ON ' . $db->quoteName('t1.id') . ' = ' . $db->quoteName('st1.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt2') . ' ON ' . $db->quoteName('m.projectteam2_id') . ' = ' . $db->quoteName('pt2.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st2') . ' ON ' . $db->quoteName('st2.id') . ' = ' . $db->quoteName('pt2.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't2') . ' ON ' . $db->quoteName('t2.id') . ' = ' . $db->quoteName('st2.team_id'))
            ->where($db->quoteName('m.id') . ' = ' . $matchId)
            ->where($db->quoteName('m.published') . ' = 1')
            ->order([
                $db->quoteName('m.match_date') . ' ASC',
                $db->quoteName('t1.short_name') . ' ASC',
            ]);

        try {
            $db->setQuery($query, 0, 1);
            return $db->loadObject() ?: null;
        } catch (Throwable $e) {
            $this->reportDatabaseError($e);
            return null;
        }
    }

    public function getMatchCommentary(int $matchId): array
    {
        if ($matchId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_match_commentary'))
            ->where($db->quoteName('match_id') . ' = ' . $matchId)
            ->order($db->quoteName('event_time') . ' DESC');

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
