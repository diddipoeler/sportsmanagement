<?php
/**
 * Joomla 5/6 read service for administrator individual-match rosters.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Service;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

/** Read-only roster access for the administrator individual-match view. */
final class IndividualMatchRosterReadService
{
    public function __construct(private DatabaseInterface $db)
    {
    }

    public function getProjectTeamPlayers(int $seasonId, int $projectTeamId): array
    {
        if ($seasonId <= 0 || $projectTeamId <= 0) {
            return [];
        }

        $db = $this->db;
        $query = $db->createQuery()
            ->select([
                $db->quoteName('p') . '.*',
                $db->quoteName('tp.id', 'season_team_person_id'),
            ])
            ->from($db->quoteName('#__sportsmanagement_person', 'p'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_person_id', 'tp') . ' ON tp.person_id = p.id')
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON st.team_id = tp.team_id AND st.season_id = tp.season_id')
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON pt.team_id = st.id')
            ->where($db->quoteName('pt.id') . ' = :projectTeamId')
            ->where($db->quoteName('tp.season_id') . ' = :seasonId')
            ->where($db->quoteName('tp.persontype') . ' = 1')
            ->where($db->quoteName('p.published') . ' = 1')
            ->order($db->quoteName('p.lastname') . ' ASC')
            ->bind(':projectTeamId', $projectTeamId, ParameterType::INTEGER)
            ->bind(':seasonId', $seasonId, ParameterType::INTEGER);

        return $db->setQuery($query)->loadObjectList() ?: [];
    }

    /**
     * Native replacement for the legacy getTeamPersons() roster option reader.
     *
     * @param array<int,int|string> $excludedIds Season-team-person ids to omit.
     * @return array<int,object>
     */
    public function getAvailableTeamPersons(
        int $projectId,
        int $seasonId,
        int $projectTeamId,
        int $personType = 1,
        array $excludedIds = []
    ): array {
        if ($projectId <= 0 || $seasonId <= 0 || $projectTeamId <= 0 || $personType <= 0) {
            return [];
        }

        $excludedIds = array_values(array_unique(array_filter(
            array_map('intval', $excludedIds),
            static fn (int $id): bool => $id > 0
        )));

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
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_team_person_id', 'sp')
                . ' ON ' . $db->quoteName('sp.person_id') . ' = ' . $db->quoteName('pl.id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('sp.team_id')
                . ' AND ' . $db->quoteName('st.season_id') . ' = ' . $db->quoteName('sp.season_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project_team', 'pt')
                . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st.id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_person_project_position', 'ppp')
                . ' ON ' . $db->quoteName('ppp.person_id') . ' = ' . $db->quoteName('sp.person_id')
                . ' AND ' . $db->quoteName('ppp.persontype') . ' = :personType'
                . ' AND ' . $db->quoteName('ppp.project_id') . ' = :projectId'
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_project_position', 'ppos')
                . ' ON ' . $db->quoteName('ppos.id') . ' = ' . $db->quoteName('ppp.project_position_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_position', 'pos')
                . ' ON ' . $db->quoteName('pos.id') . ' = ' . $db->quoteName('ppos.position_id')
            )
            ->where($db->quoteName('pt.id') . ' = :projectTeamId')
            ->where($db->quoteName('pl.published') . ' = 1')
            ->where($db->quoteName('sp.persontype') . ' = :personType')
            ->where($db->quoteName('sp.season_id') . ' = :seasonId')
            ->order($db->quoteName('pl.lastname') . ' ASC')
            ->bind(':projectTeamId', $projectTeamId, ParameterType::INTEGER)
            ->bind(':personType', $personType, ParameterType::INTEGER)
            ->bind(':seasonId', $seasonId, ParameterType::INTEGER)
            ->bind(':projectId', $projectId, ParameterType::INTEGER);

        if ($excludedIds) {
            $query->where($db->quoteName('sp.id') . ' NOT IN (' . implode(',', $excludedIds) . ')');
        }

        return $db->setQuery($query)->loadObjectList() ?: [];
    }

    public function getMatchRosterPlayers(int $seasonId, int $projectTeamId, int $matchId): array
    {
        if ($seasonId <= 0 || $projectTeamId <= 0 || $matchId <= 0) {
            return [];
        }

        $db = $this->db;
        $query = $db->createQuery()
            ->select([
                $db->quoteName('mp.teamplayer_id'),
                $db->quoteName('mp.project_position_id'),
                $db->quoteName('pos.name', 'project_position_name'),
            ])
            ->from($db->quoteName('#__sportsmanagement_match_player', 'mp'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_person_id', 'tp') . ' ON tp.id = mp.teamplayer_id')
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON st.team_id = tp.team_id AND st.season_id = tp.season_id')
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON pt.team_id = st.id')
            ->join('INNER', $db->quoteName('#__sportsmanagement_position', 'pos') . ' ON pos.id = mp.project_position_id')
            ->where($db->quoteName('pt.id') . ' = :projectTeamId')
            ->where($db->quoteName('tp.season_id') . ' = :seasonId')
            ->where($db->quoteName('mp.match_id') . ' = :matchId')
            ->where($db->quoteName('mp.came_in') . ' IN (0,1)')
            ->order($db->quoteName('mp.ordering') . ' ASC')
            ->bind(':projectTeamId', $projectTeamId, ParameterType::INTEGER)
            ->bind(':seasonId', $seasonId, ParameterType::INTEGER)
            ->bind(':matchId', $matchId, ParameterType::INTEGER);

        return $db->setQuery($query)->loadObjectList() ?: [];
    }
}
