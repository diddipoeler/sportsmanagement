<?php
/**
 * Native Joomla 5/6 read service for frontend edit-match lineup data.
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

/** Native Joomla 5/6 read service for the edit-match lineup layout. */
final class EditmatchLineupViewDataService
{
    /**
     * Initialise the frontend match-lineup read service.
     * Uses Joomla's selected database driver to read team, player, substitution and staff data required by the frontend match editor.
     *
     * @param DatabaseInterface $db Joomla database driver.
     * @return void
     */
    public function __construct(private readonly DatabaseInterface $db)
    {
    }

    /**
     * Read the project-team identifiers and team names for a fixture.
     * Joins the match to its two season-team and underlying team records, returning null for an invalid match identifier.
     *
     * @param int $matchId Parent match identifier.
     * @return object|null Home and away team details, or null.
     */
    public function getMatchTeams(int $matchId): ?object
    {
        if ($matchId <= 0) {
            return null;
        }

        $db = $this->db;
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
     * List available registered team people for a specific project, season and person type.
     * Joins person and season-team relations, applies the project scope and excludes selected person IDs to populate the editable lineup choices.
     *
     * @param int $projectTeamId Owning project-team identifier.
     * @param array<int,int|string> $excludedPersonIds Already nominated season-team-person IDs.
     * @param int $personType Requested person category, such as player or staff.
     * @param int $seasonId Selected season.
     * @param int $projectId Owning project.
     * @return array<int,object> Eligible person options and associated position metadata.
     */
    public function getTeamPersons(
        int $projectTeamId,
        array $excludedPersonIds,
        int $personType,
        int $seasonId,
        int $projectId
    ): array {
        if ($projectTeamId <= 0 || $personType <= 0 || $seasonId <= 0 || $projectId <= 0) {
            return [];
        }

        $db = $this->db;
        $query = $db->createQuery()
            ->select([
                $db->quoteName('sp.id', 'value'),
                $db->quoteName('pl.firstname'),
                $db->quoteName('pl.nickname'),
                $db->quoteName('pl.lastname'),
                $db->quoteName('pl.info'),
                $db->quoteName('sp.jerseynumber'),
                $db->quoteName('pl.ordering'),
                $db->quoteName('pl.knvbnr'),
                $db->quoteName('pos.name', 'positionname'),
            ])
            ->from($db->quoteName('#__sportsmanagement_person', 'pl'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_person_id', 'sp') . ' ON ' . $db->quoteName('sp.person_id') . ' = ' . $db->quoteName('pl.id'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('sp.team_id')
                . ' AND ' . $db->quoteName('st.season_id') . ' = ' . $db->quoteName('sp.season_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_person_project_position', 'ppp')
                . ' ON ' . $db->quoteName('ppp.person_id') . ' = ' . $db->quoteName('sp.person_id')
            )
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_position', 'ppos') . ' ON ' . $db->quoteName('ppos.id') . ' = ' . $db->quoteName('ppp.project_position_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_position', 'pos') . ' ON ' . $db->quoteName('pos.id') . ' = ' . $db->quoteName('ppos.position_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st.id'))
            ->where($db->quoteName('pt.id') . ' = :projectTeamId')
            ->where($db->quoteName('pl.published') . ' = 1')
            ->where($db->quoteName('sp.persontype') . ' = :seasonPersonType')
            ->where($db->quoteName('ppp.persontype') . ' = :projectPersonType')
            ->where($db->quoteName('sp.season_id') . ' = :seasonId')
            ->where($db->quoteName('ppp.project_id') . ' = :projectId')
            ->bind(':projectTeamId', $projectTeamId, ParameterType::INTEGER)
            ->bind(':seasonPersonType', $personType, ParameterType::INTEGER)
            ->bind(':projectPersonType', $personType, ParameterType::INTEGER)
            ->bind(':seasonId', $seasonId, ParameterType::INTEGER)
            ->bind(':projectId', $projectId, ParameterType::INTEGER)
            ->order($db->quoteName('pl.lastname') . ' ASC');

        $excludedPersonIds = array_values(array_filter(array_map('intval', $excludedPersonIds)));

        if ($excludedPersonIds !== []) {
            $query->whereNotIn($db->quoteName('sp.id'), $excludedPersonIds, ParameterType::INTEGER);
        }

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    /**
     * Read an existing match's starting players for one project position.
     * Uses match, project and team constraints to retrieve assigned player IDs, shirt numbers, captain flags and ordering fields.
     *
     * @param int $teamId Project-team identifier whose roster is being edited.
     * @param int $projectPositionId Project-specific position identifier.
     * @param int $matchId Parent fixture identifier.
     * @param int $projectId Owning project identifier.
     * @return array<int,object> Nominated player entries for the selected position.
     */
    public function getRoster(int $teamId, int $projectPositionId, int $matchId, int $projectId): array
    {
        if ($teamId <= 0 || $projectPositionId <= 0 || $matchId <= 0 || $projectId <= 0) {
            return [];
        }

        $db = $this->db;
        $query = $db->createQuery()
            ->select([
                $db->quoteName('mp.id', 'table_id'),
                $db->quoteName('mp.match_id'),
                $db->quoteName('mp.teamplayer_id', 'value'),
                $db->quoteName('mp.trikot_number'),
                $db->quoteName('mp.captain'),
                $db->quoteName('pl.firstname'),
                $db->quoteName('pl.nickname'),
                $db->quoteName('pl.lastname'),
                $db->quoteName('pl.info'),
                $db->quoteName('pl.ordering'),
                $db->quoteName('pl.position_id', 'person_position_id'),
                $db->quoteName('pos.name', 'positionname'),
                $db->quoteName('pos.id', 'position_position_id'),
                $db->quoteName('sp.jerseynumber'),
                $db->quoteName('sp.project_position_id', 'stp_project_position_id'),
            ])
            ->from($db->quoteName('#__sportsmanagement_match_player', 'mp'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_person_id', 'sp') . ' ON ' . $db->quoteName('sp.id') . ' = ' . $db->quoteName('mp.teamplayer_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_person', 'pl') . ' ON ' . $db->quoteName('pl.id') . ' = ' . $db->quoteName('sp.person_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_position', 'ppos') . ' ON ' . $db->quoteName('ppos.position_id') . ' = ' . $db->quoteName('mp.project_position_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_position', 'pos') . ' ON ' . $db->quoteName('pos.id') . ' = ' . $db->quoteName('ppos.position_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st1') . ' ON ' . $db->quoteName('st1.team_id') . ' = ' . $db->quoteName('sp.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st1.id'))
            ->where($db->quoteName('mp.match_id') . ' = :matchId')
            ->where($db->quoteName('ppos.project_id') . ' = :projectId')
            ->where($db->quoteName('mp.came_in') . ' = 0')
            ->where($db->quoteName('pl.published') . ' = 1')
            ->where($db->quoteName('ppos.id') . ' = :projectPositionId')
            ->where($db->quoteName('pt.id') . ' = :teamId')
            ->bind(':matchId', $matchId, ParameterType::INTEGER)
            ->bind(':projectId', $projectId, ParameterType::INTEGER)
            ->bind(':projectPositionId', $projectPositionId, ParameterType::INTEGER)
            ->bind(':teamId', $teamId, ParameterType::INTEGER)
            ->order($db->quoteName('mp.ordering') . ' ASC');
        $db->setQuery($query);

        return $db->loadObjectList('value') ?: [];
    }

    /**
     * Load a project's match substitution entries for the specified team.
     * Reads incoming and outgoing player, position and timestamp data for the match in the order needed by the lineup editor.
     *
     * @param int $teamId Project-team identifier.
     * @param int $matchId Parent fixture identifier.
     * @param int $projectId Owning project identifier.
     * @return array<int,object> Substitution entries and participant identifiers.
     */
    public function getSubstitutions(int $teamId, int $matchId, int $projectId): array
    {
        if ($teamId <= 0 || $matchId <= 0 || $projectId <= 0) {
            return [];
        }

        $db = $this->db;
        $query = $db->createQuery()
            ->select([
                $db->quoteName('mp.id'),
                $db->quoteName('mp.came_in'),
                $db->quoteName('mp.teamplayer_id'),
                $db->quoteName('mp.match_id'),
                $db->quoteName('mp.in_out_time'),
                $db->quoteName('p1.firstname'),
                $db->quoteName('p1.nickname'),
                $db->quoteName('p1.lastname'),
                $db->quoteName('p2.firstname', 'out_firstname'),
                $db->quoteName('p2.nickname', 'out_nickname'),
                $db->quoteName('p2.lastname', 'out_lastname'),
                $db->quoteName('pos.name', 'in_position'),
            ])
            ->from($db->quoteName('#__sportsmanagement_match_player', 'mp'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_person_id', 'sp1') . ' ON ' . $db->quoteName('sp1.id') . ' = ' . $db->quoteName('mp.teamplayer_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_person', 'p1') . ' ON ' . $db->quoteName('p1.id') . ' = ' . $db->quoteName('sp1.person_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_position', 'pos') . ' ON ' . $db->quoteName('pos.id') . ' = ' . $db->quoteName('p1.position_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_person_id', 'sp2') . ' ON ' . $db->quoteName('sp2.id') . ' = ' . $db->quoteName('mp.in_for'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_person', 'p2') . ' ON ' . $db->quoteName('p2.id') . ' = ' . $db->quoteName('sp2.person_id'))
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st1')
                . ' ON ' . $db->quoteName('st1.team_id') . ' = ' . $db->quoteName('sp1.team_id')
                . ' AND ' . $db->quoteName('st1.season_id') . ' = ' . $db->quoteName('sp1.season_id')
            )
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_team', 'pt1') . ' ON ' . $db->quoteName('pt1.team_id') . ' = ' . $db->quoteName('st1.id'))
            ->where($db->quoteName('pt1.project_id') . ' = :projectId')
            ->where($db->quoteName('mp.match_id') . ' = :matchId')
            ->where($db->quoteName('mp.came_in') . ' > 0')
            ->where($db->quoteName('pt1.id') . ' = :teamId')
            ->bind(':projectId', $projectId, ParameterType::INTEGER)
            ->bind(':matchId', $matchId, ParameterType::INTEGER)
            ->bind(':teamId', $teamId, ParameterType::INTEGER)
            ->order('(mp.in_out_time + 0) ASC');
        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    /**
     * Read nominated staff members for the selected project team and match.
     * Resolves season-team-person records, project positions and published person names so the editor can preserve existing staff assignments.
     *
     * @param int $projectTeamId Project-team identifier.
     * @param int $matchId Parent fixture identifier.
     * @param int $projectId Owning project identifier.
     * @return array<int,object> Staff roster entries with position and display information.
     */
    public function getMatchStaff(int $projectTeamId, int $matchId, int $projectId): array
    {
        if ($projectTeamId <= 0 || $matchId <= 0 || $projectId <= 0) {
            return [];
        }

        $db = $this->db;
        $query = $db->createQuery()
            ->select([
                $db->quoteName('mp.team_staff_id'),
                $db->quoteName('mp.project_position_id'),
                $db->quoteName('mp.match_id'),
                $db->quoteName('mp.id', 'update_id'),
                $db->quoteName('pt.id', 'projectteam_id'),
                $db->quoteName('sp.id', 'value'),
                $db->quoteName('pl.firstname'),
                $db->quoteName('pl.nickname'),
                $db->quoteName('pl.lastname'),
                $db->quoteName('pl.knvbnr'),
                $db->quoteName('pos.name', 'positionname'),
                $db->quoteName('ppos.position_id'),
                $db->quoteName('ppos.id', 'pposid'),
                $db->quoteName('mp.ordering', 'playerordering'),
            ])
            ->from($db->quoteName('#__sportsmanagement_match_staff', 'mp'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_person_id', 'sp') . ' ON ' . $db->quoteName('mp.team_staff_id') . ' = ' . $db->quoteName('sp.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('sp.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st.id'))
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_person_project_position', 'ppp')
                . ' ON ' . $db->quoteName('ppp.person_id') . ' = ' . $db->quoteName('sp.person_id')
                . ' AND ' . $db->quoteName('ppp.persontype') . ' = ' . $db->quoteName('sp.persontype')
            )
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_position', 'ppos') . ' ON ' . $db->quoteName('ppos.id') . ' = ' . $db->quoteName('ppp.project_position_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_person', 'pl') . ' ON ' . $db->quoteName('pl.id') . ' = ' . $db->quoteName('sp.person_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_position', 'pos') . ' ON ' . $db->quoteName('pos.id') . ' = ' . $db->quoteName('ppos.position_id'))
            ->where($db->quoteName('mp.match_id') . ' = :matchId')
            ->where($db->quoteName('pl.published') . ' = 1')
            ->where($db->quoteName('ppp.project_id') . ' = :projectId')
            ->where($db->quoteName('pt.id') . ' = :projectTeamId')
            ->bind(':matchId', $matchId, ParameterType::INTEGER)
            ->bind(':projectId', $projectId, ParameterType::INTEGER)
            ->bind(':projectTeamId', $projectTeamId, ParameterType::INTEGER)
            ->order($db->quoteName('mp.project_position_id') . ' ASC')
            ->order($db->quoteName('mp.ordering') . ' ASC')
            ->order($db->quoteName('pl.lastname') . ' ASC')
            ->order($db->quoteName('pl.firstname') . ' ASC');
        $db->setQuery($query);

        return $db->loadObjectList('team_staff_id') ?: [];
    }
}
