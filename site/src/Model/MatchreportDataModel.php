<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use stdClass;
use Throwable;

/**
 * Native Joomla 5/6 read model for match-report data that historically lived
 * in the administrator Match model, the Project model and the global helper.
 */
final class MatchreportDataModel extends SportsManagementProjectModel
{
    private int $matchId = 0;

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);
        $this->matchId = max(0, $this->siteApplication()->getInput()->getInt('mid', 0));
    }

    public function getMatchSingleData(?int $matchId = null): array
    {
        $matchId ??= $this->matchId;

        if ($matchId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('m') . '.*')
            ->from($db->quoteName('#__sportsmanagement_match_single', 'm'))
            ->where($db->quoteName('m.match_id') . ' = ' . $matchId);

        try {
            $db->setQuery($query);
            $rows = $db->loadObjectList() ?: [];
        } catch (Throwable $e) {
            $this->reportDatabaseError($e);
            return [];
        }

        foreach ($rows as $row) {
            $row->person_art = 0;
            $teamPlayerId = (int) ($row->teamplayer1_id ?? 0);

            if ($teamPlayerId > 0) {
                $personQuery = $db->getQuery(true)
                    ->select($db->quoteName('person_art'))
                    ->from($db->quoteName('#__sportsmanagement_season_team_person_id'))
                    ->where($db->quoteName('id') . ' = ' . $teamPlayerId);
                $db->setQuery($personQuery, 0, 1);
                $row->person_art = (int) $db->loadResult();
            }

            if ((string) ($row->match_type ?? '') === 'DOUBLE') {
                $row->person_art = 2;
            }
        }

        return $rows;
    }

    public function getMatchReferees(?int $matchId = null): array
    {
        $matchId ??= $this->matchId;

        if ($matchId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('p.id'),
                $db->quoteName('pref.id', 'person_id'),
                $db->quoteName('p.firstname'),
                $db->quoteName('p.lastname'),
                $db->quoteName('p.nickname'),
                $db->quoteName('mr.project_position_id'),
                $db->quoteName('pos.name', 'position_name'),
                $db->quoteName('pref.picture'),
                "CONCAT_WS(':', p.id, p.alias) AS person_slug",
            ])
            ->from($db->quoteName('#__sportsmanagement_match_referee', 'mr'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_referee', 'pref') . ' ON ' . $db->quoteName('mr.project_referee_id') . ' = ' . $db->quoteName('pref.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_person_id', 'spi') . ' ON ' . $db->quoteName('pref.person_id') . ' = ' . $db->quoteName('spi.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_person', 'p') . ' ON ' . $db->quoteName('spi.person_id') . ' = ' . $db->quoteName('p.id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_position', 'ppos') . ' ON ' . $db->quoteName('mr.project_position_id') . ' = ' . $db->quoteName('ppos.id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_position', 'pos') . ' ON ' . $db->quoteName('ppos.position_id') . ' = ' . $db->quoteName('pos.id'))
            ->where($db->quoteName('mr.match_id') . ' = ' . $matchId)
            ->where($db->quoteName('p.published') . ' = 1')
            ->order([
                $db->quoteName('pos.name') . ' ASC',
                $db->quoteName('mr.ordering') . ' ASC',
            ]);

        try {
            $db->setQuery($query);
            return $db->loadObjectList() ?: [];
        } catch (Throwable $e) {
            $this->reportDatabaseError($e);
            return [];
        }
    }

    public function getMatchCommentary(?int $matchId = null): array
    {
        $matchId ??= $this->matchId;

        if ($matchId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_match_commentary'))
            ->where($db->quoteName('match_id') . ' = ' . $matchId)
            ->order($db->quoteName('event_time') . ' ASC');

        try {
            $db->setQuery($query);
            return $db->loadObjectList() ?: [];
        } catch (Throwable $e) {
            $this->reportDatabaseError($e);
            return [];
        }
    }

    public function getMatchSubstitutions(?int $matchId = null): array
    {
        $matchId ??= $this->matchId;

        if ($matchId <= 0 || $this->projectId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('mp.in_out_time'),
                $db->quoteName('mp.teamplayer_id'),
                $db->quoteName('mp.in_for'),
                $db->quoteName('mp.project_position_id'),
                $db->quoteName('pin.firstname'),
                $db->quoteName('pin.nickname'),
                $db->quoteName('pin.lastname'),
                $db->quoteName('pin.id', 'playerid'),
                "CONCAT_WS(':', pin.id, pin.alias) AS person_id",
                "CONCAT_WS(':', pin.id, pin.alias) AS sub_person_slug",
                $db->quoteName('posin.name', 'in_position'),
                $db->quoteName('pposin.id', 'pposid1'),
                $db->quoteName('pout.firstname', 'out_firstname'),
                $db->quoteName('pout.nickname', 'out_nickname'),
                $db->quoteName('pout.lastname', 'out_lastname'),
                $db->quoteName('pout.id', 'out_ptid'),
                "CONCAT_WS(':', pout.id, pout.alias) AS out_person_id",
                "CONCAT_WS(':', pout.id, pout.alias) AS person_slug",
                $db->quoteName('posout.name', 'out_position'),
                $db->quoteName('pposout.id', 'pposid2'),
                $db->quoteName('pt.id', 'ptid'),
                "CONCAT_WS(':', t.id, t.alias) AS team_id",
                "CONCAT_WS(':', t.id, t.alias) AS team_slug",
            ])
            ->from($db->quoteName('#__sportsmanagement_match_player', 'mp'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_person_id', 'tpin') . ' ON ' . $db->quoteName('tpin.id') . ' = ' . $db->quoteName('mp.teamplayer_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_person', 'pin') . ' ON ' . $db->quoteName('pin.id') . ' = ' . $db->quoteName('tpin.person_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_position', 'pposin') . ' ON ' . $db->quoteName('pposin.id') . ' = ' . $db->quoteName('mp.project_position_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_position', 'posin') . ' ON ' . $db->quoteName('posin.id') . ' = ' . $db->quoteName('pposin.position_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_person_id', 'tpout') . ' ON ' . $db->quoteName('tpout.id') . ' = ' . $db->quoteName('mp.in_for'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_person', 'pout') . ' ON ' . $db->quoteName('pout.id') . ' = ' . $db->quoteName('tpout.person_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_match_player', 'mpout') . ' ON ' . $db->quoteName('mpout.match_id') . ' = ' . $db->quoteName('mp.match_id') . ' AND ' . $db->quoteName('mpout.teamplayer_id') . ' = ' . $db->quoteName('mp.in_for'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_position', 'pposout') . ' ON ' . $db->quoteName('pposout.id') . ' = ' . $db->quoteName('mpout.project_position_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_position', 'posout') . ' ON ' . $db->quoteName('posout.id') . ' = ' . $db->quoteName('pposout.position_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('tpin.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st.id') . ' AND ' . $db->quoteName('pt.project_id') . ' = ' . $this->projectId)
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't') . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id'))
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

    public function getMatchEvents(?int $matchId = null, bool $showComments = true, bool $sortDescending = false): array
    {
        $matchId ??= $this->matchId;

        if ($matchId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $select = [
            $db->quoteName('me.event_type_id'),
            $db->quoteName('me.id', 'event_id'),
            $db->quoteName('me.event_time'),
            $db->quoteName('me.notice'),
            $db->quoteName('me.projectteam_id', 'ptid'),
            $db->quoteName('me.event_sum'),
            "CONCAT_WS(':', t.id, t.alias) AS team_id",
            $db->quoteName('et.name', 'eventtype_name'),
            $db->quoteName('t.name', 'team_name'),
            $db->quoteName('tp.picture', 'tppicture1'),
            $db->quoteName('p.firstname', 'firstname1'),
            $db->quoteName('p.nickname', 'nickname1'),
            $db->quoteName('p.lastname', 'lastname1'),
            $db->quoteName('p.picture', 'picture1'),
            "CONCAT_WS(':', p.id, p.alias) AS playerid",
        ];

        if ($showComments) {
            $select[] = $db->quoteName('me.notes');
        }

        $query = $db->getQuery(true)
            ->select($select)
            ->from($db->quoteName('#__sportsmanagement_match_event', 'me'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_eventtype', 'et') . ' ON ' . $db->quoteName('me.event_type_id') . ' = ' . $db->quoteName('et.id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON ' . $db->quoteName('me.projectteam_id') . ' = ' . $db->quoteName('pt.id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_team', 't') . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('t.id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_person_id', 'tp') . ' ON ' . $db->quoteName('tp.team_id') . ' = ' . $db->quoteName('st.team_id') . ' AND ' . $db->quoteName('tp.id') . ' = ' . $db->quoteName('me.teamplayer_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_person', 'p') . ' ON ' . $db->quoteName('tp.person_id') . ' = ' . $db->quoteName('p.id'))
            ->where($db->quoteName('me.match_id') . ' = ' . $matchId)
            ->where('COALESCE(' . $db->quoteName('p.published') . ', 1) = 1')
            ->group([
                $db->quoteName('me.event_type_id'),
                $db->quoteName('me.id'),
                $db->quoteName('me.event_time'),
                $db->quoteName('me.notice'),
                $db->quoteName('me.event_sum'),
                $db->quoteName('me.projectteam_id'),
                $db->quoteName('t.alias'),
                $db->quoteName('t.id'),
                $db->quoteName('et.name'),
                $db->quoteName('t.name'),
                $db->quoteName('p.picture'),
                $db->quoteName('tp.picture'),
                $db->quoteName('p.firstname'),
                $db->quoteName('p.nickname'),
                $db->quoteName('p.lastname'),
                $db->quoteName('p.alias'),
                $db->quoteName('p.id'),
            ]);

        if ($showComments) {
            $query->group($db->quoteName('me.notes'));
        }

        $query->order('(me.event_time + 0) ' . ($sortDescending ? 'DESC' : 'ASC') . ', me.event_type_id, me.id');

        try {
            $db->setQuery($query);
            $events = $db->loadObjectList() ?: [];
        } catch (Throwable $e) {
            $this->reportDatabaseError($e);
            $events = [];
        }

        foreach ($this->getMatchCommentary($matchId) as $comment) {
            $event = new stdClass();
            $event->event_type_id = 0;
            $event->event_sum = $comment->type ?? '';
            $event->event_time = $comment->event_time ?? 0;
            $event->notes = $comment->notes ?? '';
            $events[] = $event;
        }

        usort($events, static function (object $a, object $b) use ($sortDescending): int {
            $timeA = (float) ($a->event_time ?? 0);
            $timeB = (float) ($b->event_time ?? 0);
            $result = $timeA <=> $timeB;
            return $sortDescending ? -$result : $result;
        });

        return $events;
    }

    public function getPlayground(int $playgroundId): ?object
    {
        if ($playgroundId <= 0) {
            return null;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_playground'))
            ->where($db->quoteName('id') . ' = ' . $playgroundId);
        $db->setQuery($query, 0, 1);

        return $db->loadObject() ?: null;
    }

    private function reportDatabaseError(Throwable $e): void
    {
        $this->siteApplication()->enqueueMessage(
            Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()),
            'error'
        );
    }
}
