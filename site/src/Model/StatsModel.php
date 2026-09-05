<?php
/**
 * Native Joomla 5/6 frontend statistics model.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

final class StatsModel extends SportsManagementProjectModel
{
    public static int $projectid = 0;
    public static int $divisionid = 0;
    public static int $cfg_which_database = 0;

    public $highest_home = null;
    public $highest_away = null;
    public $totals = null;
    public $matchdaytotals = null;
    public $totalrounds = null;
    public $attendanceranking = null;

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);
        $input = $this->siteApplication()->getInput();
        self::$projectid = $this->projectId;
        self::$divisionid = $this->divisionId;
        self::$cfg_which_database = max(0, $input->getInt('cfg_which_database', 0));
    }

    public function getHighest($which = 'HOME')
    {
        if (self::$projectid <= 0) {
            return null;
        }

        $mode = strtoupper((string) $which) === 'AWAY' ? 'AWAY' : 'HOME';
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('t1.name', 'hometeam'),
                $db->quoteName('t2.name', 'guestteam'),
                $db->quoteName('t1.id', 'hometeam_id'),
                $db->quoteName('pt1.id', 'project_hometeam_id'),
                $db->quoteName('m.team1_result', 'homegoals'),
                $db->quoteName('m.team2_result', 'guestgoals'),
                $db->quoteName('t2.id', 'awayteam_id'),
                $db->quoteName('pt2.id', 'project_awayteam_id'),
            ])
            ->from($db->quoteName('#__sportsmanagement_match', 'm'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt1') . ' ON ' . $db->quoteName('pt1.id') . ' = ' . $db->quoteName('m.projectteam1_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st1') . ' ON ' . $db->quoteName('st1.id') . ' = ' . $db->quoteName('pt1.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't1') . ' ON ' . $db->quoteName('t1.id') . ' = ' . $db->quoteName('st1.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt2') . ' ON ' . $db->quoteName('pt2.id') . ' = ' . $db->quoteName('m.projectteam2_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st2') . ' ON ' . $db->quoteName('st2.id') . ' = ' . $db->quoteName('pt2.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't2') . ' ON ' . $db->quoteName('t2.id') . ' = ' . $db->quoteName('st2.team_id'))
            ->where($db->quoteName('pt1.project_id') . ' = ' . self::$projectid)
            ->where($db->quoteName('m.published') . ' = 1')
            ->where($db->quoteName('m.alt_decision') . ' = 0')
            ->where('(' . $db->quoteName('m.cancel') . ' IS NULL OR ' . $db->quoteName('m.cancel') . ' = 0)');

        if (self::$divisionid > 0) {
            $query->where($db->quoteName('pt1.division_id') . ' = ' . self::$divisionid);
        }

        if ($mode === 'HOME') {
            $query->where($db->quoteName('m.team1_result') . ' > ' . $db->quoteName('m.team2_result'))
                ->order('(' . $db->quoteName('m.team1_result') . ' - ' . $db->quoteName('m.team2_result') . ') DESC');
        } else {
            $query->where($db->quoteName('m.team2_result') . ' > ' . $db->quoteName('m.team1_result'))
                ->order('(' . $db->quoteName('m.team2_result') . ' - ' . $db->quoteName('m.team1_result') . ') DESC');
        }

        $db->setQuery($query, 0, 1);
        $result = $db->loadObject() ?: null;
        if ($mode === 'HOME') {
            $this->highest_home = $result;
        } else {
            $this->highest_away = $result;
        }
        return $result;
    }

    public function getSeasonTotals()
    {
        if ($this->totals !== null) {
            return $this->totals;
        }
        if (self::$projectid <= 0) {
            return null;
        }

        $db = $this->getDatabase();
        $subQuery = $db->createQuery()
            ->select('COUNT(' . $db->quoteName('sub1.crowd') . ')')
            ->from($db->quoteName('#__sportsmanagement_match', 'sub1'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'sub2') . ' ON ' . $db->quoteName('sub2.id') . ' = ' . $db->quoteName('sub1.projectteam1_id'))
            ->where($db->quoteName('sub1.crowd') . ' > 0')
            ->where($db->quoteName('sub1.published') . ' = 1')
            ->where('(' . $db->quoteName('sub1.cancel') . ' IS NULL OR ' . $db->quoteName('sub1.cancel') . ' = 0)')
            ->where($db->quoteName('sub2.project_id') . ' = ' . self::$projectid);

        if (self::$divisionid > 0) {
            $subQuery->where($db->quoteName('sub2.division_id') . ' = ' . self::$divisionid);
        }

        $query = $db->createQuery()
            ->select([
                'COUNT(' . $db->quoteName('m.id') . ') AS totalmatches',
                'COUNT(' . $db->quoteName('m.team1_result') . ') AS playedmatches',
                'COALESCE(SUM(' . $db->quoteName('m.team1_result') . '), 0) AS homegoals',
                'COALESCE(SUM(' . $db->quoteName('m.team2_result') . '), 0) AS guestgoals',
                'COALESCE(SUM(' . $db->quoteName('m.team1_result') . ' + ' . $db->quoteName('m.team2_result') . '), 0) AS sumgoals',
                'COALESCE(SUM(' . $db->quoteName('m.crowd') . '), 0) AS sumspectators',
                '(' . $subQuery . ') AS attendedmatches',
            ])
            ->from($db->quoteName('#__sportsmanagement_match', 'm'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt1') . ' ON ' . $db->quoteName('pt1.id') . ' = ' . $db->quoteName('m.projectteam1_id'))
            ->where($db->quoteName('pt1.project_id') . ' = ' . self::$projectid)
            ->where($db->quoteName('m.published') . ' = 1')
            ->where('(' . $db->quoteName('m.cancel') . ' IS NULL OR ' . $db->quoteName('m.cancel') . ' = 0)');

        if (self::$divisionid > 0) {
            $query->where($db->quoteName('pt1.division_id') . ' = ' . self::$divisionid);
        }

        $db->setQuery($query, 0, 1);
        $this->totals = $db->loadObject() ?: null;
        return $this->totals;
    }

    public function getChartData(): array
    {
        if ($this->matchdaytotals !== null) {
            return (array) $this->matchdaytotals;
        }
        if (self::$projectid <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('r.id'),
                'COUNT(' . $db->quoteName('m.id') . ') AS totalmatchespd',
                'COUNT(' . $db->quoteName('m.team1_result') . ') AS playedmatchespd',
                'COALESCE(SUM(' . $db->quoteName('m.team1_result') . '), 0) AS homegoalspd',
                'COALESCE(SUM(' . $db->quoteName('m.team2_result') . '), 0) AS guestgoalspd',
                $db->quoteName('r.roundcode'),
            ])
            ->from($db->quoteName('#__sportsmanagement_round', 'r'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_match', 'm') . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_team', 'pt1') . ' ON ' . $db->quoteName('pt1.id') . ' = ' . $db->quoteName('m.projectteam1_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_team', 'pt2') . ' ON ' . $db->quoteName('pt2.id') . ' = ' . $db->quoteName('m.projectteam2_id'))
            ->where($db->quoteName('r.project_id') . ' = ' . self::$projectid)
            ->where('(' . $db->quoteName('m.cancel') . ' IS NULL OR ' . $db->quoteName('m.cancel') . ' = 0)');

        if (self::$divisionid > 0) {
            $division = self::$divisionid;
            $query->where('(' . $db->quoteName('pt1.division_id') . ' = ' . $division
                . ' OR ' . $db->quoteName('pt2.division_id') . ' = ' . $division . ')');
        }

        $query->group([$db->quoteName('r.id'), $db->quoteName('r.roundcode')])
            ->order($db->quoteName('r.roundcode') . ' ASC');
        $db->setQuery($query);
        $this->matchdaytotals = $db->loadObjectList() ?: [];
        return $this->matchdaytotals;
    }

    public function getTotalRounds(): int
    {
        if ($this->totalrounds !== null) {
            return (int) $this->totalrounds;
        }
        if (self::$projectid <= 0) {
            return 0;
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select('COUNT(' . $db->quoteName('id') . ')')
            ->from($db->quoteName('#__sportsmanagement_round'))
            ->where($db->quoteName('project_id') . ' = ' . self::$projectid);
        $db->setQuery($query);
        $this->totalrounds = (int) ($db->loadResult() ?: 0);
        return (int) $this->totalrounds;
    }

    public function getBestAvg()
    {
        $ranking = $this->getAttendanceRanking();
        return $ranking ? round((float) $ranking[0]->avgspectatorspt) : 0;
    }

    public function getAttendanceRanking(): array
    {
        if ($this->attendanceranking !== null) {
            return (array) $this->attendanceranking;
        }
        if (self::$projectid <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                'SUM(' . $db->quoteName('m.crowd') . ') AS sumspectatorspt',
                'AVG(' . $db->quoteName('m.crowd') . ') AS avgspectatorspt',
                $db->quoteName('t1.name', 'team'),
                $db->quoteName('t1.id', 'teamid'),
                $db->quoteName('playground.max_visitors', 'capacity'),
            ])
            ->from($db->quoteName('#__sportsmanagement_match', 'm'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt1') . ' ON ' . $db->quoteName('pt1.id') . ' = ' . $db->quoteName('m.projectteam1_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt1.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't1') . ' ON ' . $db->quoteName('t1.id') . ' = ' . $db->quoteName('st.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_playground', 'playground') . ' ON ' . $db->quoteName('pt1.standard_playground') . ' = ' . $db->quoteName('playground.id'))
            ->where($db->quoteName('pt1.project_id') . ' = ' . self::$projectid)
            ->where($db->quoteName('m.published') . ' = 1')
            ->where($db->quoteName('m.crowd') . ' > 0');

        if (self::$divisionid > 0) {
            $query->where($db->quoteName('pt1.division_id') . ' = ' . self::$divisionid);
        }

        $query->group([
                $db->quoteName('m.projectteam1_id'),
                $db->quoteName('t1.name'),
                $db->quoteName('t1.id'),
                $db->quoteName('playground.max_visitors'),
            ])
            ->order('avgspectatorspt DESC');
        $db->setQuery($query);
        $this->attendanceranking = $db->loadObjectList() ?: [];
        return $this->attendanceranking;
    }

    public function getBestAvgTeam()
    {
        $ranking = $this->getAttendanceRanking();
        return $ranking ? $ranking[0]->team : 0;
    }

    public function getWorstAvg()
    {
        $ranking = $this->getAttendanceRanking();
        return $ranking ? round((float) $ranking[count($ranking) - 1]->avgspectatorspt) : 0;
    }

    public function getWorstAvgTeam()
    {
        $ranking = $this->getAttendanceRanking();
        return $ranking ? $ranking[count($ranking) - 1]->team : 0;
    }

    public function getChartURL(): string
    {
        if (!class_exists('sportsmanagementHelperRoute')) {
            if (is_file(JPATH_SITE . '/components/com_sportsmanagement/helpers/route.php')) {
                require_once JPATH_SITE . '/components/com_sportsmanagement/helpers/route.php';
            }
        }
        if (!class_exists('sportsmanagementHelperRoute')) {
            return '';
        }

        $url = \sportsmanagementHelperRoute::getStatsChartDataRoute(self::$projectid, self::$divisionid);
        return str_replace('&', '%26', (string) $url);
    }

    public function teamNameCmp2(&$a, &$b): int
    {
        return strcasecmp((string) $a->team, (string) $b->team);
    }

    public function totalattendCmp(&$a, &$b): int
    {
        return (int) (($a->sumspectatorspt ?? 0) <=> ($b->sumspectatorspt ?? 0));
    }

    public function avgattendCmp(&$a, &$b): int
    {
        return (int) (($a->avgspectatorspt ?? 0) <=> ($b->avgspectatorspt ?? 0));
    }

    public function capacityCmp(&$a, &$b): int
    {
        return (int) (($a->capacity ?? 0) <=> ($b->capacity ?? 0));
    }

    public function utilisationCmp(&$a, &$b): int
    {
        $aCapacity = (float) ($a->capacity ?? 0);
        $bCapacity = (float) ($b->capacity ?? 0);
        $aValue = $aCapacity > 0 ? (float) ($a->avgspectatorspt ?? 0) / $aCapacity : 0.0;
        $bValue = $bCapacity > 0 ? (float) ($b->avgspectatorspt ?? 0) / $bCapacity : 0.0;
        return $aValue <=> $bValue;
    }
}
