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
            ])
            ->from($db->quoteName('#__sportsmanagement_match_player', 'mp'))
            ->where($db->quoteName('mp.match_id') . ' = ' . $matchId)
            ->where($db->quoteName('mp.came_in') . ' > 0')
            ->order($db->quoteName('mp.in_out_time'));
        $db->setQuery($query);
        $substitutions = $db->loadObjectList() ?: [];

        foreach ($substitutions as $substitution) {
            $incomingPlayerId = (int) ($substitution->teamplayer_id ?? 0);
            $outgoingPlayerId = (int) ($substitution->in_for ?? 0);
            $incomingPositionId = (int) ($substitution->project_position_id ?? 0);

            $personQuery = $db->getQuery(true)
                ->select([
                    $db->quoteName('p.firstname'),
                    $db->quoteName('p.nickname'),
                    $db->quoteName('p.lastname'),
                    $db->quoteName('p.id', 'playerid'),
                    "CASE WHEN CHAR_LENGTH(p.alias) THEN CONCAT_WS(':', p.id, p.alias) ELSE p.id END AS person_slug",
                ])
                ->from($db->quoteName('#__sportsmanagement_person', 'p'))
                ->join(
                    'INNER',
                    $db->quoteName('#__sportsmanagement_season_team_person_id', 'tp1')
                    . ' ON ' . $db->quoteName('tp1.person_id') . ' = ' . $db->quoteName('p.id')
                )
                ->where($db->quoteName('tp1.id') . ' = ' . $incomingPlayerId);
            $db->setQuery($personQuery, 0, 1);
            $incoming = $db->loadObject();

            $substitution->firstname = (string) ($incoming->firstname ?? '');
            $substitution->nickname = (string) ($incoming->nickname ?? '');
            $substitution->lastname = (string) ($incoming->lastname ?? '');
            $substitution->playerid = (int) ($incoming->playerid ?? 0);
            $substitution->person_id = (string) ($incoming->person_slug ?? '');
            $substitution->sub_person_slug = (string) ($incoming->person_slug ?? '');

            $positionQuery = $db->getQuery(true)
                ->select([$db->quoteName('pos.id'), $db->quoteName('pos.name')])
                ->from($db->quoteName('#__sportsmanagement_position', 'pos'))
                ->where($db->quoteName('pos.id') . ' = ' . $incomingPositionId);
            $db->setQuery($positionQuery, 0, 1);
            $incomingPosition = $db->loadObject();
            $substitution->in_position = (string) ($incomingPosition->name ?? '');
            $substitution->pposid1 = $this->getTeamplanProjectPositionId((int) ($incomingPosition->id ?? 0));

            $outgoingQuery = $db->getQuery(true)
                ->select([
                    $db->quoteName('p.firstname', 'out_firstname'),
                    $db->quoteName('p.nickname', 'out_nickname'),
                    $db->quoteName('p.lastname', 'out_lastname'),
                    $db->quoteName('p.id', 'out_ptid'),
                    "CASE WHEN CHAR_LENGTH(p.alias) THEN CONCAT_WS(':', p.id, p.alias) ELSE p.id END AS person_slug",
                ])
                ->from($db->quoteName('#__sportsmanagement_person', 'p'))
                ->join(
                    'INNER',
                    $db->quoteName('#__sportsmanagement_season_team_person_id', 'tp1')
                    . ' ON ' . $db->quoteName('tp1.person_id') . ' = ' . $db->quoteName('p.id')
                )
                ->where($db->quoteName('tp1.id') . ' = ' . $outgoingPlayerId);
            $db->setQuery($outgoingQuery, 0, 1);
            $outgoing = $db->loadObject();

            $substitution->out_firstname = (string) ($outgoing->out_firstname ?? '');
            $substitution->out_nickname = (string) ($outgoing->out_nickname ?? '');
            $substitution->out_lastname = (string) ($outgoing->out_lastname ?? '');
            $substitution->out_ptid = (int) ($outgoing->out_ptid ?? 0);
            $substitution->out_person_id = (string) ($outgoing->person_slug ?? '');
            $substitution->person_slug = (string) ($outgoing->person_slug ?? '');

            $outPositionQuery = $db->getQuery(true)
                ->select([$db->quoteName('pos.id'), $db->quoteName('pos.name')])
                ->from($db->quoteName('#__sportsmanagement_position', 'pos'))
                ->join(
                    'INNER',
                    $db->quoteName('#__sportsmanagement_match_player', 'mp')
                    . ' ON ' . $db->quoteName('mp.project_position_id') . ' = ' . $db->quoteName('pos.id')
                )
                ->where($db->quoteName('mp.teamplayer_id') . ' = ' . $outgoingPlayerId)
                ->where($db->quoteName('mp.match_id') . ' = ' . $matchId);
            $db->setQuery($outPositionQuery, 0, 1);
            $outPosition = $db->loadObject();
            $substitution->out_position = (string) ($outPosition->name ?? '');
            $substitution->pposid2 = $this->getTeamplanProjectPositionId((int) ($outPosition->id ?? 0));

            $teamQuery = $db->getQuery(true)
                ->select([
                    $db->quoteName('pt.team_id'),
                    $db->quoteName('pt.id', 'ptid'),
                    "CASE WHEN CHAR_LENGTH(t.alias) THEN CONCAT_WS(':', t.id, t.alias) ELSE t.id END AS team_slug",
                ])
                ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
                ->join(
                    'INNER',
                    $db->quoteName('#__sportsmanagement_season_team_id', 'st1')
                    . ' ON ' . $db->quoteName('st1.id') . ' = ' . $db->quoteName('pt.team_id')
                )
                ->join(
                    'INNER',
                    $db->quoteName('#__sportsmanagement_season_team_person_id', 'tp1')
                    . ' ON ' . $db->quoteName('tp1.team_id') . ' = ' . $db->quoteName('st1.team_id')
                )
                ->join(
                    'INNER',
                    $db->quoteName('#__sportsmanagement_team', 't')
                    . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st1.team_id')
                )
                ->where($db->quoteName('pt.project_id') . ' = ' . $this->projectId)
                ->where($db->quoteName('tp1.id') . ' = ' . $incomingPlayerId);
            $db->setQuery($teamQuery, 0, 1);
            $team = $db->loadObject();

            $substitution->ptid = (int) ($team->ptid ?? 0);
            $substitution->team_id = (string) ($team->team_slug ?? '');
            $substitution->team_slug = (string) ($team->team_slug ?? '');
        }

        return $substitutions;
    }

    private function getTeamplanProjectPositionId(int $positionId): int
    {
        if ($positionId <= 0 || $this->projectId <= 0) {
            return 0;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('ppos.id'))
            ->from($db->quoteName('#__sportsmanagement_project_position', 'ppos'))
            ->where($db->quoteName('ppos.position_id') . ' = ' . $positionId)
            ->where($db->quoteName('ppos.project_id') . ' = ' . $this->projectId);
        $db->setQuery($query, 0, 1);

        return (int) $db->loadResult();
    }
}
