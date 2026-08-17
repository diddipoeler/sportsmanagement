<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

final class ClubsModel extends SportsManagementProjectModel
{
    public function getClubs(): array
    {
        if ($this->projectId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $divisionIds = $this->getDivisionTreeIds();

        $exists = $db->getQuery(true)
            ->select('1')
            ->from($db->quoteName('#__sportsmanagement_team', 't'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('t.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st.id'))
            ->where($db->quoteName('t.club_id') . ' = ' . $db->quoteName('c.id'))
            ->where($db->quoteName('pt.project_id') . ' = ' . $this->projectId);

        if ($divisionIds) {
            $exists->where($db->quoteName('pt.division_id') . ' IN (' . implode(',', array_map('intval', $divisionIds)) . ')');
        }

        $clubQuery = $db->getQuery(true)
            ->select(['c.*', "CONCAT_WS(':', c.id, c.alias) AS club_slug"])
            ->from($db->quoteName('#__sportsmanagement_club', 'c'))
            ->where('EXISTS (' . $exists . ')')
            ->order($db->quoteName('c.name') . ' ASC');
        $db->setQuery($clubQuery);
        $clubs = $db->loadObjectList() ?: [];

        if (!$clubs) {
            return [];
        }

        $teamQuery = $db->getQuery(true)
            ->select([
                't.*',
                $db->quoteName('t.picture', 'team_picture'),
                $db->quoteName('pt.picture', 'projectteam_picture'),
                $db->quoteName('pt.division_id'),
                "CONCAT_WS(':', t.id, t.alias) AS team_slug",
            ])
            ->from($db->quoteName('#__sportsmanagement_team', 't'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('t.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st.id'))
            ->where($db->quoteName('pt.project_id') . ' = ' . $this->projectId)
            ->order($db->quoteName('t.name') . ' ASC');

        if ($divisionIds) {
            $teamQuery->where($db->quoteName('pt.division_id') . ' IN (' . implode(',', array_map('intval', $divisionIds)) . ')');
        }

        $db->setQuery($teamQuery);
        $teams = $db->loadObjectList() ?: [];
        $teamsByClub = [];
        foreach ($teams as $team) {
            $teamsByClub[(int) $team->club_id][] = $team;
        }
        foreach ($clubs as $club) {
            $club->teams = $teamsByClub[(int) $club->id] ?? [];
        }
        return $clubs;
    }
}
