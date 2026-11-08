<?php
/**
 * Native Joomla 5/6 read service for frontend edit-match statistics data.
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

/**
 * Native Joomla 5/6 read service for the edit-match statistics layout.
 *
 * All legacy methods used by initEditStats() read from Joomla's default DB;
 * keep that behaviour explicit here rather than routing through the old
 * administrator Match model.
 */
final class EditmatchStatsViewDataService
{
    /**
     * Create the native frontend edit-match statistics read service.
     * Retains the Joomla default database connection used by the legacy editor's team and statistic queries.
     *
     * @param DatabaseInterface $db Joomla database driver for the edit-match editor.
     * @return void
     */
    public function __construct(private readonly DatabaseInterface $db)
    {
    }

    /**
     * Load the home and away team names used by the statistics editor.
     * Joins the match to both project teams, season-team relations and team names using a bound fixture identifier.
     *
     * @param int $matchId Parent match identifier.
     * @return object|null Team names and project-team IDs, or null.
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
     * List nominated match staff and their sport-specific positions.
     * Filters staff to the given match and project team, includes published people and combines name and project-position details for the editor.
     *
     * @param int $projectTeamId Team whose staff roster is requested.
     * @param int $matchId Fixture identifier.
     * @return array<int,object> Staff rows keyed by season-team-person identifier.
     */
    public function getMatchStaff(int $projectTeamId, int $matchId): array
    {
        if ($projectTeamId <= 0 || $matchId <= 0) {
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
                . ' AND ' . $db->quoteName('ppp.project_id') . ' = ' . $db->quoteName('pt.project_id')
            )
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_position', 'ppos') . ' ON ' . $db->quoteName('ppos.id') . ' = ' . $db->quoteName('ppp.project_position_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_person', 'pl') . ' ON ' . $db->quoteName('pl.id') . ' = ' . $db->quoteName('sp.person_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_position', 'pos') . ' ON ' . $db->quoteName('pos.id') . ' = ' . $db->quoteName('ppos.position_id'))
            ->where($db->quoteName('mp.match_id') . ' = :matchId')
            ->where($db->quoteName('pl.published') . ' = 1')
            ->where($db->quoteName('pt.id') . ' = :projectTeamId')
            ->bind(':matchId', $matchId, ParameterType::INTEGER)
            ->bind(':projectTeamId', $projectTeamId, ParameterType::INTEGER)
            ->order($db->quoteName('mp.project_position_id') . ' ASC')
            ->order($db->quoteName('mp.ordering') . ' ASC')
            ->order($db->quoteName('pl.lastname') . ' ASC')
            ->order($db->quoteName('pl.firstname') . ' ASC');
        $db->setQuery($query);
        $rows = $db->loadObjectList('team_staff_id') ?: [];

        foreach ($rows as $row) {
            $row->text = trim((string) ($row->firstname ?? '') . ':' . (string) ($row->lastname ?? ''));
        }

        return $rows;
    }

    /**
     * Retrieve the statistics definitions allowed by a project's positions.
     * Joins the published project-position configuration to statistic records and wraps each database row in a native EditmatchStatisticDefinition value object.
     *
     * @param int $projectId Project whose statistics editor should be configured.
     * @return array<int,EditmatchStatisticDefinition> Ordered statistic definitions.
     */
    public function getInputStatistics(int $projectId): array
    {
        if ($projectId <= 0) {
            return [];
        }

        $db = $this->db;
        $query = $db->createQuery()
            ->select([
                $db->quoteName('stat.id'),
                $db->quoteName('stat.name'),
                $db->quoteName('stat.short'),
                $db->quoteName('stat.class'),
                $db->quoteName('stat.icon'),
                $db->quoteName('stat.calculated'),
                $db->quoteName('ppos.position_id', 'posid'),
            ])
            ->from($db->quoteName('#__sportsmanagement_statistic', 'stat'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_position_statistic', 'ps') . ' ON ' . $db->quoteName('ps.statistic_id') . ' = ' . $db->quoteName('stat.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_position', 'ppos') . ' ON ' . $db->quoteName('ppos.position_id') . ' = ' . $db->quoteName('ps.position_id'))
            ->where($db->quoteName('ppos.project_id') . ' = :projectId')
            ->bind(':projectId', $projectId, ParameterType::INTEGER)
            ->order($db->quoteName('stat.ordering') . ' ASC')
            ->order($db->quoteName('ps.ordering') . ' ASC');
        $db->setQuery($query);

        return array_map(
            static fn (object $row): EditmatchStatisticDefinition => new EditmatchStatisticDefinition($row),
            $db->loadObjectList() ?: []
        );
    }

    /**
     * Group existing player statistics for the selected match by team and player.
     * Loads saved match statistic values and indexes them as project-team, team-player and statistic identifiers, retaining empty buckets for both teams.
     *
     * @param int $matchId Parent match identifier.
     * @param int $projectTeam1Id Home project-team identifier.
     * @param int $projectTeam2Id Away project-team identifier.
     * @return array<int,array<int,array<int,mixed>>> Saved player statistic input values.
     */
    public function getMatchStatsInput(int $matchId, int $projectTeam1Id, int $projectTeam2Id): array
    {
        $stats = [
            $projectTeam1Id => [],
            $projectTeam2Id => [],
        ];

        if ($matchId <= 0) {
            return $stats;
        }

        $db = $this->db;
        $query = $db->createQuery()
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_match_statistic'))
            ->where($db->quoteName('match_id') . ' = :matchId')
            ->bind(':matchId', $matchId, ParameterType::INTEGER);
        $db->setQuery($query);

        foreach ($db->loadObjectList() ?: [] as $stat) {
            $projectTeamId = (int) ($stat->projectteam_id ?? 0);
            $teamPlayerId = (int) ($stat->teamplayer_id ?? 0);
            $statisticId = (int) ($stat->statistic_id ?? 0);
            $stats[$projectTeamId][$teamPlayerId][$statisticId] = $stat->value;
        }

        return $stats;
    }

    /**
     * Group existing staff statistics for the selected match by team and staff member.
     * Queries the match-staff statistic table and creates the nested project-team, staff and statistic mapping expected by the editor.
     *
     * @param int $matchId Parent match identifier.
     * @param int $projectTeam1Id Home project-team identifier.
     * @param int $projectTeam2Id Away project-team identifier.
     * @return array<int,array<int,array<int,mixed>>> Saved match staff statistic values.
     */
    public function getMatchStaffStatsInput(int $matchId, int $projectTeam1Id, int $projectTeam2Id): array
    {
        $stats = [
            $projectTeam1Id => [],
            $projectTeam2Id => [],
        ];

        if ($matchId <= 0) {
            return $stats;
        }

        $db = $this->db;
        $query = $db->createQuery()
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_match_staff_statistic'))
            ->where($db->quoteName('match_id') . ' = :matchId')
            ->bind(':matchId', $matchId, ParameterType::INTEGER);
        $db->setQuery($query);

        foreach ($db->loadObjectList() ?: [] as $stat) {
            $projectTeamId = (int) ($stat->projectteam_id ?? 0);
            $teamStaffId = (int) ($stat->team_staff_id ?? 0);
            $statisticId = (int) ($stat->statistic_id ?? 0);
            $stats[$projectTeamId][$teamStaffId][$statisticId] = $stat->value;
        }

        return $stats;
    }
}
