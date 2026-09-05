<?php
/**
 * Native Joomla 5/6 frontend staff model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

final class StaffModel extends SportsManagementProjectModel
{
    public static int $projectid = 0;
    public static int $personid = 0;
    public static int $teamplayerid = 0;
    public static int $teamid = 0;
    public static $_history = null;
    public static $_inproject = null;
    public static int $cfg_which_database = 0;

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);

        $input = $this->siteApplication()->getInput();
        self::$projectid = $this->projectId;
        self::$personid = $input->getInt('pid', 0);
        self::$teamplayerid = $input->getInt('pt', 0);
        self::$teamid = $input->getInt('tid', 0);
        self::$cfg_which_database = $input->getInt('cfg_which_database', 0);
        self::$_history = null;
        self::$_inproject = null;

        PersonModel::$projectid = self::$projectid;
        PersonModel::$personid = self::$personid;
        PersonModel::$cfg_which_database = self::$cfg_which_database;
    }

    public function getPresenceStats($project_id, $person_id)
    {
        $projectId = max(0, (int) $project_id);
        $personId = max(0, (int) $person_id);
        if ($projectId <= 0 || $personId <= 0) {
            return 0;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('COUNT(' . $db->quoteName('mp.id') . ')')
            ->from($db->quoteName('#__sportsmanagement_match_staff', 'mp'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_match', 'm') . ' ON ' . $db->quoteName('mp.match_id') . ' = ' . $db->quoteName('m.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_round', 'r') . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_person_id', 'tp') . ' ON ' . $db->quoteName('tp.id') . ' = ' . $db->quoteName('mp.team_staff_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('tp.team_id')
                . ' AND ' . $db->quoteName('st.season_id') . ' = ' . $db->quoteName('tp.season_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt')
                . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st.id')
                . ' AND ' . $db->quoteName('pt.project_id') . ' = ' . $db->quoteName('r.project_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project', 'pro')
                . ' ON ' . $db->quoteName('pro.id') . ' = ' . $db->quoteName('pt.project_id')
                . ' AND ' . $db->quoteName('pro.season_id') . ' = ' . $db->quoteName('tp.season_id'))
            ->where($db->quoteName('r.project_id') . ' = ' . $projectId)
            ->where($db->quoteName('tp.person_id') . ' = ' . $personId)
            ->where($db->quoteName('tp.persontype') . ' = 2')
            ->where($db->quoteName('tp.published') . ' = 1')
            ->where($db->quoteName('pt.published') . ' = 1')
            ->where($db->quoteName('pro.published') . ' = 1')
            ->where($db->quoteName('m.published') . ' = 1');

        try {
            $db->setQuery($query, 0, 1);
            return (int) $db->loadResult();
        } catch (\Throwable $e) {
            $this->enqueueDatabaseError($e);
            return 0;
        }
    }

    public function getStats(): array
    {
        $staff = $this->getTeamStaff();
        if (!$staff) {
            return [];
        }

        return $this->getProjectStats(0, (int) ($staff->position_id ?? 0));
    }

    public function getTeamStaff()
    {
        if (self::$_inproject !== null) {
            return self::$_inproject;
        }

        if (self::$projectid <= 0 || self::$personid <= 0) {
            return null;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                'ts.*',
                $db->quoteName('ts.picture', 'season_picture'),
                $db->quoteName('pos.name', 'position_name'),
                $db->quoteName('ppos.id', 'pPosID'),
                $db->quoteName('ppos.position_id'),
            ])
            ->from($db->quoteName('#__sportsmanagement_season_team_person_id', 'ts'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('ts.team_id')
                . ' AND ' . $db->quoteName('st.season_id') . ' = ' . $db->quoteName('ts.season_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project', 'pro')
                . ' ON ' . $db->quoteName('pro.id') . ' = ' . $db->quoteName('pt.project_id')
                . ' AND ' . $db->quoteName('pro.season_id') . ' = ' . $db->quoteName('ts.season_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't') . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('ts.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_person', 'pr') . ' ON ' . $db->quoteName('pr.id') . ' = ' . $db->quoteName('ts.person_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_position', 'ppos') . ' ON ' . $db->quoteName('ppos.id') . ' = ' . $db->quoteName('ts.project_position_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_position', 'pos') . ' ON ' . $db->quoteName('pos.id') . ' = ' . $db->quoteName('ppos.position_id'))
            ->where($db->quoteName('pt.project_id') . ' = ' . self::$projectid)
            ->where($db->quoteName('ts.person_id') . ' = ' . self::$personid)
            ->where($db->quoteName('ts.published') . ' = 1')
            ->where($db->quoteName('pt.published') . ' = 1')
            ->where($db->quoteName('pro.published') . ' = 1')
            ->where($db->quoteName('t.published') . ' = 1')
            ->where($db->quoteName('pr.published') . ' = 1')
            ->where($db->quoteName('pr.show_on_frontend') . ' = 1')
            ->where($db->quoteName('ts.persontype') . ' = 2');

        if (self::$teamid > 0) {
            $query->where($db->quoteName('ts.team_id') . ' = ' . self::$teamid);
        }

        try {
            $db->setQuery($query, 0, 1);
            self::$_inproject = $db->loadObject() ?: null;
        } catch (\Throwable $e) {
            $this->enqueueDatabaseError($e);
            self::$_inproject = null;
        }

        return self::$_inproject;
    }

    public function getStaffStats(): array
    {
        $staff = $this->getTeamStaff();
        if (!$staff) {
            return [];
        }

        $stats = $this->getProjectStats(0, (int) ($staff->position_id ?? 0));
        $history = $this->getStaffHistory();
        if (!$stats || !$history) {
            return [];
        }

        $result = [];
        foreach ($history as $historyRow) {
            $projectId = (int) ($historyRow->project_id ?? 0);
            $personId = (int) ($historyRow->person_id ?? 0);
            $teamId = (int) ($historyRow->team_id ?? 0);
            if ($projectId <= 0 || $personId <= 0) {
                continue;
            }

            foreach ($stats as $stat) {
                if (!is_object($stat) || !method_exists($stat, 'getStaffStats')) {
                    continue;
                }

                $statId = (int) ($stat->id ?? 0);
                if ($statId <= 0) {
                    continue;
                }

                $result[$statId][$projectId] = $stat->getStaffStats($personId, $teamId, $projectId);
            }
        }

        return $result;
    }

    public function getStaffHistory($order = 'DESC')
    {
        if (self::$_history !== null) {
            return self::$_history;
        }

        if (self::$personid <= 0) {
            return [];
        }

        $direction = strtoupper((string) $order) === 'ASC' ? 'ASC' : 'DESC';
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('pr.id', 'pid'),
                $db->quoteName('pr.firstname'),
                $db->quoteName('pr.lastname'),
                $db->quoteName('o.person_id'),
                $db->quoteName('o.picture', 'season_picture'),
                $db->quoteName('tt.project_id'),
                $db->quoteName('tt.id', 'ptid'),
                $db->quoteName('t.id', 'team_id'),
                $db->quoteName('t.name', 'team_name'),
                "CONCAT_WS(':', t.id, t.alias) AS team_slug",
                $db->quoteName('p.name', 'project_name'),
                "CONCAT_WS(':', p.id, p.alias) AS project_slug",
                $db->quoteName('s.name', 'season_name'),
                $db->quoteName('ppos.position_id'),
                $db->quoteName('pos.name', 'position_name'),
                $db->quoteName('pos.id', 'posID'),
            ])
            ->from($db->quoteName('#__sportsmanagement_season_team_person_id', 'o'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('o.team_id')
                . ' AND ' . $db->quoteName('st.season_id') . ' = ' . $db->quoteName('o.season_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_person', 'pr') . ' ON ' . $db->quoteName('o.person_id') . ' = ' . $db->quoteName('pr.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'tt') . ' ON ' . $db->quoteName('tt.team_id') . ' = ' . $db->quoteName('st.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't') . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('o.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project', 'p')
                . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('tt.project_id')
                . ' AND ' . $db->quoteName('p.season_id') . ' = ' . $db->quoteName('o.season_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season', 's') . ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('p.season_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_league', 'l') . ' ON ' . $db->quoteName('l.id') . ' = ' . $db->quoteName('p.league_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_person_project_position', 'ppp')
                . ' ON ' . $db->quoteName('ppp.person_id') . ' = ' . $db->quoteName('o.person_id')
                . ' AND ' . $db->quoteName('ppp.project_id') . ' = ' . $db->quoteName('p.id')
                . ' AND ' . $db->quoteName('ppp.persontype') . ' = ' . $db->quoteName('o.persontype'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_position', 'ppos') . ' ON ' . $db->quoteName('ppos.id') . ' = ' . $db->quoteName('ppp.project_position_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_position', 'pos') . ' ON ' . $db->quoteName('pos.id') . ' = ' . $db->quoteName('ppos.position_id'))
            ->where($db->quoteName('o.person_id') . ' = ' . self::$personid)
            ->where($db->quoteName('pr.published') . ' = 1')
            ->where($db->quoteName('pr.show_on_frontend') . ' = 1')
            ->where($db->quoteName('o.published') . ' = 1')
            ->where($db->quoteName('tt.published') . ' = 1')
            ->where($db->quoteName('t.published') . ' = 1')
            ->where($db->quoteName('p.published') . ' = 1')
            ->where($db->quoteName('o.persontype') . ' = 2')
            ->order([
                $db->quoteName('s.name') . ' ' . $direction,
                $db->quoteName('p.name') . ' ' . $direction,
            ]);

        try {
            $db->setQuery($query);
            self::$_history = $db->loadObjectList() ?: [];
        } catch (\Throwable $e) {
            $this->enqueueDatabaseError($e);
            self::$_history = [];
        }

        return self::$_history;
    }

    public function getHistoryStaffStats(): array
    {
        $staff = $this->getTeamStaff();
        if (!$staff) {
            return [];
        }

        $stats = $this->getProjectStats(0, (int) ($staff->position_id ?? 0));
        if (!$stats) {
            return [];
        }

        $result = [];
        foreach ($stats as $stat) {
            if (!is_object($stat) || !method_exists($stat, 'getHistoryStaffStats')) {
                continue;
            }

            $statId = (int) ($stat->id ?? 0);
            if ($statId <= 0) {
                continue;
            }

            $result[$statId] = $stat->getHistoryStaffStats((int) ($staff->person_id ?? self::$personid));
        }

        return $result;
    }

    private function enqueueDatabaseError(\Throwable $e): void
    {
        $this->siteApplication()->enqueueMessage(
            Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()),
            'notice'
        );
    }
}
