<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Service;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;

final class ProjectRelationService
{
    public function __construct(private DatabaseInterface $db)
    {
    }

    public function getProject(int $projectId): ?object
    {
        if ($projectId <= 0) {
            return null;
        }

        $query = $this->db->getQuery(true)
            ->select([
                $this->db->quoteName('p.id'),
                $this->db->quoteName('p.name'),
                $this->db->quoteName('p.season_id'),
                $this->db->quoteName('p.league_id'),
                $this->db->quoteName('p.project_art_id'),
                $this->db->quoteName('p.project_type'),
                $this->db->quoteName('p.sports_type_id'),
                $this->db->quoteName('p.fast_projektteam'),
                $this->db->quoteName('l.country'),
                $this->db->quoteName('s.name', 'season_name'),
            ])
            ->from($this->db->quoteName('#__sportsmanagement_project', 'p'))
            ->join('LEFT', $this->db->quoteName('#__sportsmanagement_league', 'l') . ' ON ' . $this->db->quoteName('l.id') . ' = ' . $this->db->quoteName('p.league_id'))
            ->join('LEFT', $this->db->quoteName('#__sportsmanagement_season', 's') . ' ON ' . $this->db->quoteName('s.id') . ' = ' . $this->db->quoteName('p.season_id'))
            ->where($this->db->quoteName('p.id') . ' = ' . $projectId);
        $this->db->setQuery($query, 0, 1);

        return $this->db->loadObject() ?: null;
    }

    public function getProjectTeam(int $projectTeamId, int $projectId): ?object
    {
        if ($projectTeamId <= 0 || $projectId <= 0) {
            return null;
        }

        $query = $this->db->getQuery(true)
            ->select([
                $this->db->quoteName('pt.id', 'project_team_id'),
                $this->db->quoteName('pt.project_id'),
                $this->db->quoteName('pt.team_id', 'season_team_id'),
                $this->db->quoteName('st.team_id'),
                $this->db->quoteName('st.season_id'),
                $this->db->quoteName('t.name', 'team_name'),
                $this->db->quoteName('t.club_id'),
            ])
            ->from($this->db->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->join('INNER', $this->db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $this->db->quoteName('st.id') . ' = ' . $this->db->quoteName('pt.team_id'))
            ->join('INNER', $this->db->quoteName('#__sportsmanagement_team', 't') . ' ON ' . $this->db->quoteName('t.id') . ' = ' . $this->db->quoteName('st.team_id'))
            ->where($this->db->quoteName('pt.id') . ' = ' . $projectTeamId)
            ->where($this->db->quoteName('pt.project_id') . ' = ' . $projectId);
        $this->db->setQuery($query, 0, 1);

        return $this->db->loadObject() ?: null;
    }

    public function getProjectPositions(int $projectId, int $personType): array
    {
        if ($projectId <= 0 || $personType <= 0) {
            return [];
        }

        $query = $this->db->getQuery(true)
            ->select([
                $this->db->quoteName('pp.id', 'value'),
                $this->db->quoteName('pos.name', 'text'),
                $this->db->quoteName('pos.id', 'position_id'),
            ])
            ->from($this->db->quoteName('#__sportsmanagement_project_position', 'pp'))
            ->join('INNER', $this->db->quoteName('#__sportsmanagement_position', 'pos') . ' ON ' . $this->db->quoteName('pos.id') . ' = ' . $this->db->quoteName('pp.position_id'))
            ->where($this->db->quoteName('pp.project_id') . ' = ' . $projectId)
            ->where($this->db->quoteName('pos.persontype') . ' = ' . $personType)
            ->where($this->db->quoteName('pp.published') . ' = 1')
            ->order($this->db->quoteName('pos.name') . ' ASC');
        $this->db->setQuery($query);

        return $this->db->loadObjectList() ?: [];
    }

    public function getProjectPosition(int $projectPositionId, int $projectId, int $personType): ?object
    {
        if ($projectPositionId <= 0 || $projectId <= 0 || $personType <= 0) {
            return null;
        }

        $query = $this->db->getQuery(true)
            ->select([
                $this->db->quoteName('pp.id'),
                $this->db->quoteName('pp.position_id'),
            ])
            ->from($this->db->quoteName('#__sportsmanagement_project_position', 'pp'))
            ->join('INNER', $this->db->quoteName('#__sportsmanagement_position', 'pos') . ' ON ' . $this->db->quoteName('pos.id') . ' = ' . $this->db->quoteName('pp.position_id'))
            ->where($this->db->quoteName('pp.id') . ' = ' . $projectPositionId)
            ->where($this->db->quoteName('pp.project_id') . ' = ' . $projectId)
            ->where($this->db->quoteName('pos.persontype') . ' = ' . $personType);
        $this->db->setQuery($query, 0, 1);

        return $this->db->loadObject() ?: null;
    }

    public function getDivisions(int $projectId): array
    {
        if ($projectId <= 0) {
            return [];
        }

        $query = $this->db->getQuery(true)
            ->select([
                $this->db->quoteName('id', 'value'),
                $this->db->quoteName('name', 'text'),
            ])
            ->from($this->db->quoteName('#__sportsmanagement_division'))
            ->where($this->db->quoteName('project_id') . ' = ' . $projectId)
            ->order($this->db->quoteName('name') . ' ASC');
        $this->db->setQuery($query);

        return $this->db->loadObjectList() ?: [];
    }

    public function divisionBelongsToProject(int $divisionId, int $projectId): bool
    {
        if ($divisionId === 0) {
            return true;
        }

        $query = $this->db->getQuery(true)
            ->select('1')
            ->from($this->db->quoteName('#__sportsmanagement_division'))
            ->where($this->db->quoteName('id') . ' = ' . $divisionId)
            ->where($this->db->quoteName('project_id') . ' = ' . $projectId);
        $this->db->setQuery($query, 0, 1);

        return (bool) $this->db->loadResult();
    }

    public function getPlaygrounds(): array
    {
        $query = $this->db->getQuery(true)
            ->select([
                $this->db->quoteName('id', 'value'),
                $this->db->quoteName('name', 'text'),
            ])
            ->from($this->db->quoteName('#__sportsmanagement_playground'))
            ->where($this->db->quoteName('published') . ' = 1')
            ->order($this->db->quoteName('name') . ' ASC');
        $this->db->setQuery($query);

        return $this->db->loadObjectList() ?: [];
    }

    public function playgroundExists(int $playgroundId): bool
    {
        if ($playgroundId === 0) {
            return true;
        }

        $query = $this->db->getQuery(true)
            ->select('1')
            ->from($this->db->quoteName('#__sportsmanagement_playground'))
            ->where($this->db->quoteName('id') . ' = ' . $playgroundId);
        $this->db->setQuery($query, 0, 1);

        return (bool) $this->db->loadResult();
    }
}
