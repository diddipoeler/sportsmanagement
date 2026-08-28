<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

/**
 * Native event and substitution data used by TeamplanModel.
 */
trait TeamplanEventDataTrait
{
    public function getMatchEvents(int $matchId, bool $showComments = false, bool $sortDesc = false): array
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
                "CASE WHEN CHAR_LENGTH(t.alias) THEN CONCAT_WS(':', t.id, t.alias) ELSE t.id END AS team_id",
                $db->quoteName('et.name', 'eventtype_name'),
                $db->quoteName('t.name', 'team_name'),
                $db->quoteName('tp.picture', 'tppicture1'),
                $db->quoteName('p.firstname', 'firstname1'),
                $db->quoteName('p.nickname', 'nickname1'),
                $db->quoteName('p.lastname', 'lastname1'),
                $db->quoteName('p.picture', 'picture1'),
                "CASE WHEN CHAR_LENGTH(p.alias) THEN CONCAT_WS(':', p.id, p.alias) ELSE p.id END AS playerid",
            ])
            ->from($db->quoteName('#__sportsmanagement_match_event', 'me'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_eventtype', 'et') . ' ON ' . $db->quoteName('me.event_type_id') . ' = ' . $db->quoteName('et.id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON ' . $db->quoteName('me.projectteam_id') . ' = ' . $db->quoteName('pt.id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_team', 't') . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('t.id'))
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_season_team_person_id', 'tp')
                . ' ON ' . $db->quoteName('tp.team_id') . ' = ' . $db->quoteName('st.team_id')
                . ' AND ' . $db->quoteName('tp.id') . ' = ' . $db->quoteName('me.teamplayer_id')
            )
            ->join('LEFT', $db->quoteName('#__sportsmanagement_person', 'p') . ' ON ' . $db->quoteName('tp.person_id') . ' = ' . $db->quoteName('p.id'))
            ->where($db->quoteName('me.match_id') . ' = ' . $matchId)
            ->where('COALESCE(' . $db->quoteName('p.published') . ', 1) = 1');

        $group = [
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
        ];

        if ($showComments) {
            $query->select($db->quoteName('me.notes'));
            $group[] = $db->quoteName('me.notes');
        }

        $query
            ->group($group)
            ->order('(me.event_time + 0)' . ($sortDesc ? ' DESC' : ' ASC') . ', me.event_type_id, me.id');

        $db->setQuery($query);
        $events = $db->loadObjectList() ?: [];

        $commentaryQuery = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_match_commentary'))
            ->where($db->quoteName('match_id') . ' = ' . $matchId);
        $db->setQuery($commentaryQuery);

        foreach ($db->loadObjectList() ?: [] as $comment) {
            $event = new \stdClass();
            $event->event_type_id = 0;
            $event->event_sum = $comment->type ?? '';
            $event->event_time = $comment->event_time ?? 0;
            $event->notes = $comment->notes ?? '';
            $events[] = $event;
        }

        if ($events !== []) {
            $events = ArrayHelper::sortObjects($events, 'event_time', $sortDesc ? -1 : 1);
        }

        return $events;
    }

    public function getMatchSubstitutions(int $matchId): array
    {
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
                $db->quoteName('pin.firstname', 'firstname'),
                $db->quoteName('pin.nickname', 'nickname'),
                $db->quoteName('pin.lastname', 'lastname'),
                $db->quoteName('pin.id', 'playerid'),
                "CASE WHEN CHAR_LENGTH(pin.alias) THEN CONCAT_WS(':', pin.id, pin.alias) ELSE pin.id END AS person_id",
                $db->quoteName('posin.name', 'in_position'),
                $db->quoteName('pposin.id', 'pposid1'),
                $db->quoteName('pout.firstname', 'out_firstname'),
                $db->quoteName('pout.nickname', 'out_nickname'),
                $db->quoteName('pout.lastname', 'out_lastname'),
                $db->quoteName('pout.id', 'out_ptid'),
                "CASE WHEN CHAR_LENGTH(pout.alias) THEN CONCAT_WS(':', pout.id, pout.alias) ELSE pout.id END AS out_person_id",
                $db->quoteName('posout.name', 'out_position'),
                $db->quoteName('pposout.id', 'pposid2'),
                $db->quoteName('pt.id', 'ptid'),
                "CASE WHEN CHAR_LENGTH(t.alias) THEN CONCAT_WS(':', t.id, t.alias) ELSE t.id END AS team_id",
            ])
            ->from($db->quoteName('#__sportsmanagement_match_player', 'mp'))
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_season_team_person_id', 'tpin')
                . ' ON ' . $db->quoteName('tpin.id') . ' = ' . $db->quoteName('mp.teamplayer_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_person', 'pin')
                . ' ON ' . $db->quoteName('pin.id') . ' = ' . $db->quoteName('tpin.person_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_season_team_person_id', 'tpout')
                . ' ON ' . $db->quoteName('tpout.id') . ' = ' . $db->quoteName('mp.in_for')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_person', 'pout')
                . ' ON ' . $db->quoteName('pout.id') . ' = ' . $db->quoteName('tpout.person_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_position', 'posin')
                . ' ON ' . $db->quoteName('posin.id') . ' = ' . $db->quoteName('mp.project_position_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_project_position', 'pposin')
                . ' ON ' . $db->quoteName('pposin.position_id') . ' = ' . $db->quoteName('posin.id')
                . ' AND ' . $db->quoteName('pposin.project_id') . ' = ' . $this->projectId
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_match_player', 'mpout')
                . ' ON ' . $db->quoteName('mpout.match_id') . ' = ' . $db->quoteName('mp.match_id')
                . ' AND ' . $db->quoteName('mpout.teamplayer_id') . ' = ' . $db->quoteName('mp.in_for')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_position', 'posout')
                . ' ON ' . $db->quoteName('posout.id') . ' = ' . $db->quoteName('mpout.project_position_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_project_position', 'pposout')
                . ' ON ' . $db->quoteName('pposout.position_id') . ' = ' . $db->quoteName('posout.id')
                . ' AND ' . $db->quoteName('pposout.project_id') . ' = ' . $this->projectId
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('tpin.team_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_project_team', 'pt')
                . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st.id')
                . ' AND ' . $db->quoteName('pt.project_id') . ' = ' . $this->projectId
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_team', 't')
                . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id')
            )
            ->where($db->quoteName('mp.match_id') . ' = ' . $matchId)
            ->where($db->quoteName('mp.came_in') . ' > 0')
            ->order($db->quoteName('mp.in_out_time') . ' ASC');

        $db->setQuery($query);
        $substitutions = $db->loadObjectList() ?: [];

        foreach ($substitutions as $substitution) {
            $substitution->sub_person_slug = (string) ($substitution->person_id ?? '');
            $substitution->person_slug = (string) ($substitution->out_person_id ?? '');
            $substitution->team_slug = (string) ($substitution->team_id ?? '');
        }

        return $substitutions;
    }
}
