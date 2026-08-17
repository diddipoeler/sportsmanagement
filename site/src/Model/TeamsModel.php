<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

final class TeamsModel extends SportsManagementProjectModel
{
    public function getTeams(bool $includePlayground = false): array
    {
        if ($this->projectId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                'tl.id AS projectteamid', 'tl.division_id', 'tl.standard_playground', 'tl.admin',
                'tl.start_points', 'tl.points_finally', 'tl.neg_points_finally', 'tl.matches_finally',
                'tl.won_finally', 'tl.draws_finally', 'tl.lost_finally', 'tl.homegoals_finally',
                'tl.guestgoals_finally', 'tl.diffgoals_finally', 'tl.info', 'tl.reason',
                'tl.team_id AS project_team_team_id', 'tl.checked_out', 'tl.checked_out_time',
                'tl.is_in_score', 'tl.picture AS projectteam_picture', 'tl.project_id',
                "IF((ISNULL(tl.picture) OR tl.picture=''), (IF((ISNULL(t.picture) OR t.picture=''), c.logo_small, t.picture)), t.picture) AS picture",
                't.id', 't.name', 't.short_name', 't.middle_name', 't.notes', 't.club_id',
                't.website AS team_www', 't.picture AS team_picture', 'u.username', 'u.email', 'st.team_id',
                'c.email AS club_email', 'c.phone AS club_phone', 'c.fax AS club_fax',
                'c.logo_small', 'c.logo_middle', 'c.logo_big', 'c.country', 'c.website AS club_www',
                'c.new_club_id', 'c.facebook', 'c.twitter', 'c.instagram', 'c.name AS club_name',
                'c.address AS club_address', 'c.zipcode AS club_zipcode', 'c.state AS club_state',
                'c.location AS club_location', 'c.unique_id', 'c.country AS club_country',
                'c.trikot_home', 'c.trikot_away',
                'd.name AS division_name', 'd.shortname AS division_shortname', 'd.parent_id AS parent_division_id',
                'plg.name AS playground_name', 'plg.short_name AS playground_short_name',
                "CONCAT_WS(':', p.id, p.alias) AS project_slug",
                "CONCAT_WS(':', t.id, t.alias) AS team_slug",
                "CONCAT_WS(':', tl.id, t.alias) AS projectteam_slug",
                "CONCAT_WS(':', d.id, d.alias) AS division_slug",
                "CONCAT_WS(':', c.id, c.alias) AS club_slug",
            ])
            ->from($db->quoteName('#__sportsmanagement_project_team', 'tl'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('tl.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_team', 't') . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id'))
            ->join('LEFT', $db->quoteName('#__users', 'u') . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('tl.admin'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_club', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('t.club_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_division', 'd') . ' ON ' . $db->quoteName('d.id') . ' = ' . $db->quoteName('tl.division_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_playground', 'plg') . ' ON ' . $db->quoteName('plg.id') . ' = ' . $db->quoteName('tl.standard_playground'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('tl.project_id'))
            ->where($db->quoteName('tl.project_id') . ' = ' . $this->projectId)
            ->where($db->quoteName('tl.is_in_score') . ' = 1')
            ->order($db->quoteName('t.name') . ' ASC');

        if ($includePlayground) {
            $query->select([$db->quoteName('plg.picture', 'playground_picture'), "CONCAT_WS(':', plg.id, plg.alias) AS playground_slug"]);
        }
        $divisionIds = $this->getDivisionTreeIds();
        if ($divisionIds) {
            $query->where($db->quoteName('tl.division_id') . ' IN (' . implode(',', array_map('intval', $divisionIds)) . ')');
        }

        $db->setQuery($query);
        return $db->loadObjectList() ?: [];
    }
}
