<?php
namespace Diddipoeler\Component\SportsManagement\Site\Service;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;

/** Native read service for the frontend editmatch event/commentary layout. */
final class EditmatchEventViewDataService
{
    public function __construct(
        private readonly DatabaseInterface $joomlaDatabase,
        private readonly DatabaseInterface $selectedSportsDatabase,
        private readonly DatabaseInterface $componentSportsDatabase
    ) {
    }

    public function getMatchTeams(int $matchId): ?object
    {
        if ($matchId <= 0) {
            return null;
        }

        $db = $this->joomlaDatabase;
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('m.projectteam1_id'),
                $db->quoteName('m.projectteam2_id'),
                $db->quoteName('t1.name', 'team1'),
                $db->quoteName('t2.name', 'team2'),
            ])
            ->from($db->quoteName('#__sportsmanagement_match', 'm'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_team', 'pt1') . ' ON ' . $db->quoteName('pt1.id') . ' = ' . $db->quoteName('m.projectteam1_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_team', 'pt2') . ' ON ' . $db->quoteName('pt2.id') . ' = ' . $db->quoteName('m.projectteam2_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_id', 'st1') . ' ON ' . $db->quoteName('st1.id') . ' = ' . $db->quoteName('pt1.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_id', 'st2') . ' ON ' . $db->quoteName('st2.id') . ' = ' . $db->quoteName('pt2.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_team', 't1') . ' ON ' . $db->quoteName('t1.id') . ' = ' . $db->quoteName('st1.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_team', 't2') . ' ON ' . $db->quoteName('t2.id') . ' = ' . $db->quoteName('st2.team_id'))
            ->where($db->quoteName('m.id') . ' = ' . $matchId);
        $db->setQuery($query, 0, 1);

        return $db->loadObject() ?: null;
    }

    /** @return array<int,object> */
    public function getEventsOptions(int $projectId): array
    {
        if ($projectId <= 0) {
            return [];
        }

        $db = $this->joomlaDatabase;
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('et.id', 'value'),
                $db->quoteName('et.name', 'text'),
                $db->quoteName('et.icon', 'icon'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project_position', 'ppos'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_position_eventtype', 'pet') . ' ON ' . $db->quoteName('pet.position_id') . ' = ' . $db->quoteName('ppos.position_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_eventtype', 'et') . ' ON ' . $db->quoteName('et.id') . ' = ' . $db->quoteName('pet.eventtype_id'))
            ->where($db->quoteName('ppos.project_id') . ' = ' . $projectId)
            ->where($db->quoteName('et.published') . ' = 1')
            ->group([
                $db->quoteName('et.id'),
                $db->quoteName('et.name'),
                $db->quoteName('et.icon'),
            ])
            ->order($db->quoteName('et.id') . ' ASC')
            ->order($db->quoteName('pet.ordering') . ' ASC')
            ->order($db->quoteName('et.ordering') . ' ASC');
        $db->setQuery($query);
        $events = $db->loadObjectList() ?: [];

        foreach ($events as $event) {
            $event->text = Text::_((string) $event->text);
        }

        return $events;
    }

    /** @return array<int,object> */
    public function getMatchCommentary(int $matchId): array
    {
        if ($matchId <= 0) {
            return [];
        }

        // The historical method explicitly respected the selected external DB.
        $db = $this->selectedSportsDatabase;
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_match_commentary'))
            ->where($db->quoteName('match_id') . ' = ' . $matchId)
            ->order($db->quoteName('event_time') . ' DESC');
        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    /** @return array<int,object> */
    public function getMatchEvents(int $matchId): array
    {
        if ($matchId <= 0) {
            return [];
        }

        // sportsmanagementModelMatch::getMatchEvents() used getDBConnection()
        // without an explicit request selector, so preserve component DB policy.
        $db = $this->componentSportsDatabase;
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('me') . '.*',
                $db->quoteName('t.name', 'team'),
                $db->quoteName('et.name', 'event'),
                "CONCAT(t1.firstname, ' \\'', t1.nickname, '\\' ', t1.lastname) AS player1",
            ])
            ->from($db->quoteName('#__sportsmanagement_match_event', 'me'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_person_id', 'tp1') . ' ON ' . $db->quoteName('tp1.id') . ' = ' . $db->quoteName('me.teamplayer_id'))
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st1')
                . ' ON ' . $db->quoteName('st1.team_id') . ' = ' . $db->quoteName('tp1.team_id')
                . ' AND ' . $db->quoteName('st1.season_id') . ' = ' . $db->quoteName('tp1.season_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_person', 't1')
                . ' ON ' . $db->quoteName('t1.id') . ' = ' . $db->quoteName('tp1.person_id')
                . ' AND ' . $db->quoteName('t1.published') . ' = 1'
            )
            ->join('LEFT', $db->quoteName('#__sportsmanagement_team', 't') . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st1.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_eventtype', 'et') . ' ON ' . $db->quoteName('et.id') . ' = ' . $db->quoteName('me.event_type_id'))
            ->where($db->quoteName('me.match_id') . ' = ' . $matchId)
            ->order($db->quoteName('me.event_time') . ' ASC');
        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }
}
