<?php
/**
 * Native Joomla 5/6 read service for frontend match events and commentary.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Service;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

/** Native read service for the frontend editmatch event/commentary layout. */
final class EditmatchEventViewDataService
{
    /**
     * Create the frontend event and commentary view reader with explicit database scopes.
     * Separates Joomla's default database, the configured external sports database for commentary and the component sports database for match events.
     *
     * @param DatabaseInterface $joomlaDatabase Joomla's primary database connection.
     * @param DatabaseInterface $selectedSportsDatabase Selected sports connection for commentary.
     * @param DatabaseInterface $componentSportsDatabase Connection selected for component match events.
     * @return void
     */
    public function __construct(
        private readonly DatabaseInterface $joomlaDatabase,
        private readonly DatabaseInterface $selectedSportsDatabase,
        private readonly DatabaseInterface $componentSportsDatabase
    ) {
    }

    /**
     * Read both team names and project-team IDs for one frontend match.
     * Resolves home and away team names through the season and project relations, returning no row when the match identifier is invalid.
     *
     * @param int $matchId Parent fixture identifier.
     * @return object|null Home and away team details, or null.
     */
    public function getMatchTeams(int $matchId): ?object
    {
        if ($matchId <= 0) {
            return null;
        }

        $db = $this->joomlaDatabase;
        $query = $db->createQuery()
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
            ->where($db->quoteName('m.id') . ' = :matchId')
            ->bind(':matchId', $matchId, ParameterType::INTEGER);
        $db->setQuery($query, 0, 1);

        return $db->loadObject() ?: null;
    }

    /**
     * Load the published event types configured for the selected project.
     * Joins project positions to their allowed event types and localises the unique option labels for the edit-match event dropdown.
     *
     * @param int $projectId Project defining the available event types.
     * @return array<int,object> Localised event-type option rows.
     */
    public function getEventsOptions(int $projectId): array
    {
        if ($projectId <= 0) {
            return [];
        }

        $db = $this->joomlaDatabase;
        $query = $db->createQuery()
            ->select([
                $db->quoteName('et.id', 'value'),
                $db->quoteName('et.name', 'text'),
                $db->quoteName('et.icon', 'icon'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project_position', 'ppos'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_position_eventtype', 'pet') . ' ON ' . $db->quoteName('pet.position_id') . ' = ' . $db->quoteName('ppos.position_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_eventtype', 'et') . ' ON ' . $db->quoteName('et.id') . ' = ' . $db->quoteName('pet.eventtype_id'))
            ->where($db->quoteName('ppos.project_id') . ' = :projectId')
            ->where($db->quoteName('et.published') . ' = 1')
            ->group([
                $db->quoteName('et.id'),
                $db->quoteName('et.name'),
                $db->quoteName('et.icon'),
            ])
            ->order($db->quoteName('et.id') . ' ASC')
            ->order($db->quoteName('pet.ordering') . ' ASC')
            ->order($db->quoteName('et.ordering') . ' ASC')
            ->bind(':projectId', $projectId, ParameterType::INTEGER);
        $db->setQuery($query);
        $events = $db->loadObjectList() ?: [];

        foreach ($events as $event) {
            $event->text = Text::_((string) $event->text);
        }

        return $events;
    }

    /**
     * Read a fixture's commentary in reverse event-time order.
     * Queries the explicitly selected sports database with a bound match ID, preserving the legacy external-database selection policy.
     *
     * @param int $matchId Fixture whose commentary is requested.
     * @return array<int,object> Commentary entries ordered by newest first.
     */
    public function getMatchCommentary(int $matchId): array
    {
        if ($matchId <= 0) {
            return [];
        }

        // The historical method explicitly respected the selected external DB.
        $db = $this->selectedSportsDatabase;
        $query = $db->createQuery()
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_match_commentary'))
            ->where($db->quoteName('match_id') . ' = :matchId')
            ->order($db->quoteName('event_time') . ' DESC')
            ->bind(':matchId', $matchId, ParameterType::INTEGER);
        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    /**
     * Read match events with team names and associated player details.
     * Uses the component sports database and bound match identifier, enriches event rows with participant names and sorts by increasing event time.
     *
     * @param int $matchId Fixture whose scored and disciplinary events are requested.
     * @return array<int,object> Match events with formatted player names.
     */
    public function getMatchEvents(int $matchId): array
    {
        if ($matchId <= 0) {
            return [];
        }

        // sportsmanagementModelMatch::getMatchEvents() used getDBConnection()
        // without an explicit request selector, so preserve component DB policy.
        $db = $this->componentSportsDatabase;
        $query = $db->createQuery()
            ->select([
                $db->quoteName('me') . '.*',
                $db->quoteName('t.name', 'team'),
                $db->quoteName('et.name', 'event'),
                $db->quoteName('t1.firstname', '_player_firstname'),
                $db->quoteName('t1.nickname', '_player_nickname'),
                $db->quoteName('t1.lastname', '_player_lastname'),
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
            ->where($db->quoteName('me.match_id') . ' = :matchId')
            ->order($db->quoteName('me.event_time') . ' ASC')
            ->bind(':matchId', $matchId, ParameterType::INTEGER);
        $db->setQuery($query);
        $events = $db->loadObjectList() ?: [];

        foreach ($events as $event) {
            $event->player1 = trim(
                (string) ($event->_player_firstname ?? '')
                . " '" . (string) ($event->_player_nickname ?? '') . "' "
                . (string) ($event->_player_lastname ?? '')
            );
            unset($event->_player_firstname, $event->_player_nickname, $event->_player_lastname);
        }

        return $events;
    }
}
