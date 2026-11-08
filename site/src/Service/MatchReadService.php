<?php
/**
 * Native Joomla 5/6 read service for match data.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Service;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

final class MatchReadService
{
    /**
     * Create the frontend match reader with an injected Joomla database.
     *
     * @param DatabaseInterface $db Database driver for read-only match queries.
     * @return void
     */
    public function __construct(private DatabaseInterface $db)
    {
    }

    /**
     * Load individual-match records associated with a parent match.
     *
     * Optionally restricts the results to a single match number, using bound
     * parameters for both selectors and returning no rows for invalid IDs.
     *
     * @param int $matchId Parent SportsManagement match identifier.
     * @param string|null $matchNumber Optional individual-game sequence number.
     * @return array<int,object> Matching individual-game records.
     */
    public function getSingleMatches(int $matchId, ?string $matchNumber = null): array
    {
        if ($matchId <= 0) {
            return [];
        }

        $query = $this->db->createQuery()
            ->select($this->db->quoteName('m') . '.*')
            ->from($this->db->quoteName('#__sportsmanagement_match_single', 'm'))
            ->where($this->db->quoteName('m.match_id') . ' = :matchId')
            ->bind(':matchId', $matchId, ParameterType::INTEGER);

        if ($matchNumber !== null) {
            $query->where($this->db->quoteName('m.match_number') . ' = :matchNumber')
                ->bind(':matchNumber', $matchNumber, ParameterType::STRING);
        }

        $this->db->setQuery($query);

        return $this->db->loadObjectList() ?: [];
    }

    /**
     * Read one match with its project, season and home/away team metadata.
     *
     * Retrieves an individual fixture with left joins so missing optional
     * team relations do not suppress the match itself.
     *
     * @param int $matchId Requested match identifier.
     * @return object|null Enriched match row, or null if not found.
     */
    public function getMatch(int $matchId): ?object
    {
        if ($matchId <= 0) {
            return null;
        }

        $query = $this->db->createQuery()
            ->select([
                $this->db->quoteName('m') . '.*',
                $this->db->quoteName('t1.name', 'hometeam'),
                $this->db->quoteName('t2.name', 'awayteam'),
                $this->db->quoteName('pt1.project_id'),
                $this->db->quoteName('p.season_id'),
                $this->db->quoteName('p.timezone'),
            ])
            ->from($this->db->quoteName('#__sportsmanagement_match', 'm'))
            ->join('LEFT', $this->db->quoteName('#__sportsmanagement_project_team', 'pt1') . ' ON ' . $this->db->quoteName('pt1.id') . ' = ' . $this->db->quoteName('m.projectteam1_id'))
            ->join('LEFT', $this->db->quoteName('#__sportsmanagement_project_team', 'pt2') . ' ON ' . $this->db->quoteName('pt2.id') . ' = ' . $this->db->quoteName('m.projectteam2_id'))
            ->join('LEFT', $this->db->quoteName('#__sportsmanagement_season_team_id', 'st1') . ' ON ' . $this->db->quoteName('st1.id') . ' = ' . $this->db->quoteName('pt1.team_id'))
            ->join('LEFT', $this->db->quoteName('#__sportsmanagement_season_team_id', 'st2') . ' ON ' . $this->db->quoteName('st2.id') . ' = ' . $this->db->quoteName('pt2.team_id'))
            ->join('LEFT', $this->db->quoteName('#__sportsmanagement_team', 't1') . ' ON ' . $this->db->quoteName('t1.id') . ' = ' . $this->db->quoteName('st1.team_id'))
            ->join('LEFT', $this->db->quoteName('#__sportsmanagement_team', 't2') . ' ON ' . $this->db->quoteName('t2.id') . ' = ' . $this->db->quoteName('st2.team_id'))
            ->join('LEFT', $this->db->quoteName('#__sportsmanagement_project', 'p') . ' ON ' . $this->db->quoteName('p.id') . ' = ' . $this->db->quoteName('pt1.project_id'))
            ->where($this->db->quoteName('m.id') . ' = :matchId')
            ->bind(':matchId', $matchId, ParameterType::INTEGER);

        $this->db->setQuery($query, 0, 1);
        $row = $this->db->loadObject();

        return $row ?: null;
    }

    /**
     * Load published matches from a project for match-relation selectors.
     *
     * Includes team names, dates and timezone, ordered newest first. Excluded
     * IDs are filtered to positive integers before generating a NOT IN clause.
     *
     * @param int $projectId Project whose related matches are requested.
     * @param array<int,int> $excludeIds Match IDs to omit from the result.
     * @return array<int,object> Published match relation options.
     */
    public function getMatchRelations(int $projectId, array $excludeIds = []): array
    {
        if ($projectId <= 0) {
            return [];
        }

        $query = $this->db->createQuery()
            ->select([
                $this->db->quoteName('m.id', 'value'),
                $this->db->quoteName('m.match_date'),
                $this->db->quoteName('p.timezone'),
                $this->db->quoteName('t1.name', 't1_name'),
                $this->db->quoteName('t2.name', 't2_name'),
            ])
            ->from($this->db->quoteName('#__sportsmanagement_match', 'm'))
            ->join('LEFT', $this->db->quoteName('#__sportsmanagement_project_team', 'pt1') . ' ON ' . $this->db->quoteName('pt1.id') . ' = ' . $this->db->quoteName('m.projectteam1_id'))
            ->join('LEFT', $this->db->quoteName('#__sportsmanagement_project_team', 'pt2') . ' ON ' . $this->db->quoteName('pt2.id') . ' = ' . $this->db->quoteName('m.projectteam2_id'))
            ->join('LEFT', $this->db->quoteName('#__sportsmanagement_season_team_id', 'st1') . ' ON ' . $this->db->quoteName('st1.id') . ' = ' . $this->db->quoteName('pt1.team_id'))
            ->join('LEFT', $this->db->quoteName('#__sportsmanagement_season_team_id', 'st2') . ' ON ' . $this->db->quoteName('st2.id') . ' = ' . $this->db->quoteName('pt2.team_id'))
            ->join('LEFT', $this->db->quoteName('#__sportsmanagement_team', 't1') . ' ON ' . $this->db->quoteName('t1.id') . ' = ' . $this->db->quoteName('st1.team_id'))
            ->join('LEFT', $this->db->quoteName('#__sportsmanagement_team', 't2') . ' ON ' . $this->db->quoteName('t2.id') . ' = ' . $this->db->quoteName('st2.team_id'))
            ->join('LEFT', $this->db->quoteName('#__sportsmanagement_project', 'p') . ' ON ' . $this->db->quoteName('p.id') . ' = ' . $this->db->quoteName('pt1.project_id'))
            ->where($this->db->quoteName('pt1.project_id') . ' = :projectId')
            ->where($this->db->quoteName('m.published') . ' = 1')
            ->bind(':projectId', $projectId, ParameterType::INTEGER)
            ->order($this->db->quoteName('m.match_date') . ' DESC')
            ->order($this->db->quoteName('t1.short_name') . ' ASC');

        $excludeIds = array_values(array_unique(array_filter(
            $excludeIds,
            static fn (mixed $id): bool => is_int($id) && $id > 0
        )));
        if ($excludeIds) {
            $query->whereNotIn($this->db->quoteName('m.id'), $excludeIds, ParameterType::INTEGER);
        }

        $this->db->setQuery($query);

        return $this->db->loadObjectList() ?: [];
    }

    /**
     * Load recorded match events with their player, team and event labels.
     *
     * Uses the parent match identifier and sorts event records chronologically
     * for frontend match presentations.
     *
     * @param int $matchId Parent fixture identifier.
     * @return array<int,object> Ordered match-event rows.
     */
    public function getEvents(int $matchId): array
    {
        if ($matchId <= 0) {
            return [];
        }

        $query = $this->db->createQuery()
            ->select([
                $this->db->quoteName('me') . '.*',
                $this->db->quoteName('t.name', 'team'),
                $this->db->quoteName('et.name', 'event'),
                $this->db->quoteName('p.firstname'),
                $this->db->quoteName('p.nickname'),
                $this->db->quoteName('p.lastname'),
            ])
            ->from($this->db->quoteName('#__sportsmanagement_match_event', 'me'))
            ->join('LEFT', $this->db->quoteName('#__sportsmanagement_season_team_person_id', 'tp') . ' ON ' . $this->db->quoteName('tp.id') . ' = ' . $this->db->quoteName('me.teamplayer_id'))
            ->join('LEFT', $this->db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $this->db->quoteName('st.team_id') . ' = ' . $this->db->quoteName('tp.team_id') . ' AND ' . $this->db->quoteName('st.season_id') . ' = ' . $this->db->quoteName('tp.season_id'))
            ->join('LEFT', $this->db->quoteName('#__sportsmanagement_person', 'p') . ' ON ' . $this->db->quoteName('p.id') . ' = ' . $this->db->quoteName('tp.person_id'))
            ->join('LEFT', $this->db->quoteName('#__sportsmanagement_team', 't') . ' ON ' . $this->db->quoteName('t.id') . ' = ' . $this->db->quoteName('st.team_id'))
            ->join('LEFT', $this->db->quoteName('#__sportsmanagement_eventtype', 'et') . ' ON ' . $this->db->quoteName('et.id') . ' = ' . $this->db->quoteName('me.event_type_id'))
            ->where($this->db->quoteName('me.match_id') . ' = :matchId')
            ->bind(':matchId', $matchId, ParameterType::INTEGER)
            ->order($this->db->quoteName('me.event_time') . ' ASC');

        $this->db->setQuery($query);

        return $this->db->loadObjectList() ?: [];
    }

    /**
     * Load the published referees attached to one SportsManagement match.
     *
     * Joins project assignments and person details, then constructs Joomla
     * person ID:alias slugs in PHP for database portability.
     *
     * @param int $matchId Parent fixture identifier.
     * @return array<int,object> Referee assignments including person slugs.
     */
    public function getReferees(int $matchId): array
    {
        if ($matchId <= 0) {
            return [];
        }

        $query = $this->db->createQuery()
            ->select([
                $this->db->quoteName('p.id'),
                $this->db->quoteName('pref.id', 'project_referee_id'),
                $this->db->quoteName('p.firstname'),
                $this->db->quoteName('p.nickname'),
                $this->db->quoteName('p.lastname'),
                $this->db->quoteName('p.alias'),
                $this->db->quoteName('pos.name', 'position_name'),
                $this->db->quoteName('mr.project_position_id'),
                $this->db->quoteName('pref.picture'),
            ])
            ->from($this->db->quoteName('#__sportsmanagement_match_referee', 'mr'))
            ->join('LEFT', $this->db->quoteName('#__sportsmanagement_project_referee', 'pref') . ' ON ' . $this->db->quoteName('pref.id') . ' = ' . $this->db->quoteName('mr.project_referee_id'))
            ->join('INNER', $this->db->quoteName('#__sportsmanagement_season_person_id', 'spi') . ' ON ' . $this->db->quoteName('spi.id') . ' = ' . $this->db->quoteName('pref.person_id'))
            ->join('INNER', $this->db->quoteName('#__sportsmanagement_person', 'p') . ' ON ' . $this->db->quoteName('p.id') . ' = ' . $this->db->quoteName('spi.person_id'))
            ->join('LEFT', $this->db->quoteName('#__sportsmanagement_project_position', 'ppos') . ' ON ' . $this->db->quoteName('ppos.id') . ' = ' . $this->db->quoteName('mr.project_position_id'))
            ->join('LEFT', $this->db->quoteName('#__sportsmanagement_position', 'pos') . ' ON ' . $this->db->quoteName('pos.id') . ' = ' . $this->db->quoteName('ppos.position_id'))
            ->where($this->db->quoteName('mr.match_id') . ' = :matchId')
            ->where($this->db->quoteName('p.published') . ' = 1')
            ->bind(':matchId', $matchId, ParameterType::INTEGER)
            ->order($this->db->quoteName('pos.name') . ' ASC')
            ->order($this->db->quoteName('mr.ordering') . ' ASC');

        $this->db->setQuery($query);

        $referees = $this->db->loadObjectList() ?: [];

        // Build Joomla ID:alias slugs in PHP instead of using the
        // MySQL-only CONCAT_WS() function in a portable database query.
        foreach ($referees as $referee) {
            $personId = (int) ($referee->id ?? 0);
            $referee->person_slug = $personId > 0
                ? $personId . ':' . (string) ($referee->alias ?? '')
                : '';
        }

        return $referees;
    }
}
