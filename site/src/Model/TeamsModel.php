<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

final class TeamsModel extends SportsManagementProjectModel
{
    /**
     * Legacy public static state retained for existing views/extensions.
     */
    public static int $projectid = 0;
    public static int $divisionid = 0;
    public static int $cfg_which_database = 0;

    /**
     * Legacy public instance properties retained for compatibility.
     */
    public int $teamid = 0;
    public $team = null;
    public $club = null;

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);

        $input = $this->siteApplication()->getInput();
        self::$projectid = $this->projectId;
        self::$divisionid = $this->divisionId;
        self::$cfg_which_database = $input->getInt('cfg_which_database', 0);

        if (class_exists('sportsmanagementModelProject')) {
            \sportsmanagementModelProject::$projectid = self::$projectid;
        }
    }

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
            $query->select([
                $db->quoteName('plg.picture', 'playground_picture'),
                "CONCAT_WS(':', plg.id, plg.alias) AS playground_slug",
            ]);
        }

        $divisionIds = $this->getDivisionTreeIds();

        if ($divisionIds) {
            $query->where($db->quoteName('tl.division_id') . ' IN (' . implode(',', array_map('intval', $divisionIds)) . ')');
        }

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    /**
     * Teams assigned to a playground, preserving the historical view data shape
     * without the old per-row team/project queries.
     */
    public function getTeamsByPlayground(int $playgroundId): array
    {
        if ($playgroundId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('pt.id', 'projectteam_id'),
                $db->quoteName('st.team_id'),
                $db->quoteName('pt.project_id'),
                $db->quoteName('p.name', 'project_name'),
                $db->quoteName('t.name', 'team_name'),
                $db->quoteName('t.short_name'),
                $db->quoteName('t.notes'),
                "CONCAT_WS(':', p.id, p.alias) AS project_slug",
                "CONCAT_WS(':', t.id, t.alias) AS team_slug",
            ])
            ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_team', 't')
                . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project', 'p')
                . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('pt.project_id')
            )
            ->where($db->quoteName('pt.standard_playground') . ' = ' . $playgroundId)
            ->order([
                $db->quoteName('p.name') . ' ASC',
                $db->quoteName('t.name') . ' ASC',
            ]);
        $db->setQuery($query);

        $teams = [];
        foreach ($db->loadObjectList() ?: [] as $row) {
            $projectTeam = (object) [
                'id' => (int) $row->projectteam_id,
                'team_id' => (int) $row->team_id,
                'project_id' => (int) $row->project_id,
                'project_slug' => (string) $row->project_slug,
            ];
            $teamInfo = (object) [
                'name' => (string) $row->team_name,
                'short_name' => (string) $row->short_name,
                'notes' => (string) $row->notes,
                'team_slug' => (string) $row->team_slug,
            ];

            $teams[(int) $row->projectteam_id] = (object) [
                'project_team' => [$projectTeam],
                // Historical template shape: one list entry containing the team row list.
                'teaminfo' => [[$teamInfo]],
                'project' => (string) $row->project_name,
            ];
        }

        return $teams;
    }

    /**
     * Batch-load the teams referenced by a match list, including club logos for
     * native presentation templates.
     */
    public function getTeamsFromMatches(array $games): array
    {
        $teamIds = [];

        foreach ($games as $game) {
            foreach (['team1', 'team2'] as $property) {
                $id = (int) ($game->{$property} ?? 0);
                if ($id > 0) {
                    $teamIds[$id] = $id;
                }
            }
        }

        if (!$teamIds) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('t.id'),
                $db->quoteName('t.name'),
                $db->quoteName('t.picture'),
                $db->quoteName('c.logo_small'),
                $db->quoteName('c.logo_middle'),
                $db->quoteName('c.logo_big'),
            ])
            ->from($db->quoteName('#__sportsmanagement_team', 't'))
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_club', 'c')
                . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('t.club_id')
            )
            ->where($db->quoteName('t.id') . ' IN (' . implode(',', array_values($teamIds)) . ')');
        $db->setQuery($query);

        $teams = [];
        foreach ($db->loadObjectList() ?: [] as $team) {
            $teams[(int) $team->id] = $team;
        }

        return $teams;
    }
}
