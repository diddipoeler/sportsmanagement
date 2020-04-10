<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage stats
 * @file       stats.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * sportsmanagementModelStats
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementModelStats extends BaseDatabaseModel
{
	static $projectid = 0;

	static $divisionid = 0;

	static $cfg_which_database = 0;

	var $highest_home = null;

	var $highest_away = null;

	var $totals = null;

	var $matchdaytotals = null;

	var $totalrounds = null;

	var $attendanceranking = null;

	/**
	 * sportsmanagementModelStats::__construct()
	 *
	 * @return
	 */
	function __construct()
	{
		// Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;
		parent::__construct();

		self::$projectid          = $jinput->getInt("p", 0);
		self::$divisionid         = $jinput->getint("division", 0);
		self::$cfg_which_database = $jinput->getint("cfg_which_database", 0);

		sportsmanagementModelProject::$projectid = self::$projectid;
	}

	/**
	 * sportsmanagementModelStats::getHighest()
	 *
	 * @return
	 */
	function getHighest($which = 'HOME')
	{
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

		// Create a new query object.
		$db        = sportsmanagementHelper::getDBConnection();
		$query     = $db->getQuery(true);
		$starttime = microtime();

		$query->select('t1.name AS hometeam');
		$query->select('t2.name AS guestteam');
		$query->select('t1.id AS hometeam_id');
		$query->select('pt1.id AS project_hometeam_id');
		$query->select('matches.team1_result AS homegoals');
		$query->select('matches.team2_result AS guestgoals');
		$query->select('t2.id AS awayteam_id');
		$query->select('pt2.id AS project_awayteam_id');

		$query->from('#__sportsmanagement_match AS matches');
		$query->join('INNER', '#__sportsmanagement_project_team AS pt1 ON pt1.id = matches.projectteam1_id');
		$query->join('INNER', '#__sportsmanagement_season_team_id as st1 ON st1.id = pt1.team_id ');
		$query->join('INNER', '#__sportsmanagement_team AS t1 ON st1.team_id = t1.id ');

		$query->join('INNER', '#__sportsmanagement_project_team AS pt2 ON pt2.id = matches.projectteam2_id');
		$query->join('INNER', '#__sportsmanagement_season_team_id as st2 ON st2.id = pt2.team_id ');
		$query->join('INNER', '#__sportsmanagement_team AS t2 ON st2.team_id = t2.id ');

		$query->where('pt1.project_id = ' . self::$projectid);

		if (self::$divisionid != 0)
		{
			$query->where('pt1.division_id = ' . self::$divisionid);
		}

		$query->where('matches.published = 1');
		$query->where('matches.alt_decision = 0');
		$query->where('(matches.cancel IS NULL OR matches.cancel = 0)');

		switch ($which)
		{
			case 'HOME':
				$query->where('team1_result > team2_result');
				$query->order('(team1_result - team2_result) DESC');
				break;
			case 'AWAY':
				$query->where('team2_result > team1_result');
				$query->order('(team2_result - team1_result) DESC');
				break;
		}

		$db->setQuery($query, 0, 1);

		switch ($which)
		{
			case 'HOME':
				$this->highest_home = $db->loadObject();

				return $this->highest_home;
				break;
			case 'AWAY':
				$this->highest_away = $db->loadObject();

				return $this->highest_away;
				break;
		}

	}

	/**
	 * sportsmanagementModelStats::getSeasonTotals()
	 *
	 * @return
	 */
	function getSeasonTotals()
	{
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

		// Create a new query object.
		$db       = sportsmanagementHelper::getDBConnection();
		$query    = $db->getQuery(true);
		$Subquery = $db->getQuery(true);

		$starttime = microtime();

		if (is_null($this->totals))
		{
			$query->select('COUNT(matches.id) AS totalmatches');
			$query->select('COUNT(matches.team1_result) as playedmatches');
			$query->select('SUM(matches.team1_result) AS homegoals');
			$query->select('SUM(matches.team2_result) AS guestgoals');
			$query->select('SUM(team1_result + team2_result) AS sumgoals');
			$query->select('SUM(crowd) AS sumspectators');

			$Subquery->select('COUNT(crowd)');
			$Subquery->from('#__sportsmanagement_match AS sub1');
			$Subquery->join('INNER', '#__sportsmanagement_project_team AS sub2 ON sub2.id = sub1.projectteam1_id');
			$Subquery->where('sub1.crowd > 0');
			$Subquery->where('sub1.published = 1');
			$Subquery->where('(sub1.cancel IS NULL OR sub1.cancel = 0)');
			$Subquery->where('sub2.project_id = ' . self::$projectid);

			$query->select('(' . $Subquery . ') AS attendedmatches');

			$query->from('#__sportsmanagement_match AS matches');
			$query->join('INNER', '#__sportsmanagement_project_team AS pt1 ON pt1.id = matches.projectteam1_id');

			$query->where('pt1.project_id = ' . self::$projectid);

			if (self::$divisionid != 0)
			{
				$query->where('pt1.division_id = ' . self::$divisionid);
			}

			$query->where('matches.published = 1');
			$query->where('(matches.cancel IS NULL OR matches.cancel = 0)');

			$db->setQuery($query, 0, 1);

			$this->totals = $db->loadObject();
		}

		return $this->totals;
	}

	/**
	 * sportsmanagementModelStats::getChartData()
	 *
	 * @return
	 */
	function getChartData()
	{
		$app    = Factory::getApplication();
		$option = Factory::getApplication()->input->getCmd('option');

		// Create a new query object.
		$db        = sportsmanagementHelper::getDBConnection();
		$query     = $db->getQuery(true);
		$starttime = microtime();

		if (is_null($this->matchdaytotals))
		{
			$query->select('rounds.id');
			$query->select('COUNT(matches.id) AS totalmatchespd');
			$query->select('COUNT(matches.team1_result) as playedmatchespd');
			$query->select('SUM(matches.team1_result) AS homegoalspd');
			$query->select('SUM(matches.team2_result) AS guestgoalspd');
			$query->select('rounds.roundcode');

			$query->from('#__sportsmanagement_round AS rounds');
			$query->join('LEFT', '#__sportsmanagement_match AS matches ON rounds.id = matches.round_id');

			if (self::$divisionid != 0)
			{
				$query->join('INNER', '#__sportsmanagement_division AS division ON division.project_id = rounds.project_id');
				$query->where('rounds.project_id = ' . self::$projectid);
				$query->where('division.id = ' . self::$divisionid);
			}
			else
			{
				$query->where('rounds.project_id = ' . self::$projectid);
			}

			$query->where('(matches.cancel IS NULL OR matches.cancel = 0)');
			$query->group('rounds.roundcode');

			$db->setQuery($query);

			$this->matchdaytotals = $db->loadObjectList();
		}

		return $this->matchdaytotals;
	}

	/**
	 * sportsmanagementModelStats::getTotalRounds()
	 *
	 * @return
	 */
	function getTotalRounds()
	{
		$app    = Factory::getApplication();
		$option = Factory::getApplication()->input->getCmd('option');

		// Create a new query object.
		$db        = sportsmanagementHelper::getDBConnection();
		$query     = $db->getQuery(true);
		$starttime = microtime();

		if (is_null($this->totalrounds))
		{
			$query->select('COUNT(id)');
			$query->from('#__sportsmanagement_round');
			$query->where('project_id = ' . self::$projectid);

			$db->setQuery($query);
			$this->totalrounds = $db->loadResult();
		}

		return $this->totalrounds;
	}

	/**
	 * sportsmanagementModelStats::getBestAvg()
	 *
	 * @return
	 */
	function getBestAvg()
	{
		$attendanceranking = self::getAttendanceRanking();

		return (count($attendanceranking) > 0) ? round($attendanceranking[0]->avgspectatorspt) : 0;
	}

	/**
	 * sportsmanagementModelStats::getAttendanceRanking()
	 *
	 * @return
	 */
	function getAttendanceRanking()
	{
		$app    = Factory::getApplication();
		$option = Factory::getApplication()->input->getCmd('option');

		// Create a new query object.
		$db        = sportsmanagementHelper::getDBConnection();
		$query     = $db->getQuery(true);
		$starttime = microtime();

		if (is_null($this->attendanceranking))
		{
			$query->select('SUM(matches.crowd) AS sumspectatorspt');
			$query->select('AVG(matches.crowd) AS avgspectatorspt');
			$query->select('t1.name AS team');
			$query->select('t1.id AS teamid');
			$query->select('playground.max_visitors AS capacity');

			$query->from('#__sportsmanagement_match AS matches ');
			$query->join('INNER', '#__sportsmanagement_project_team pt1 ON pt1.id = matches.projectteam1_id ');
			$query->join('INNER', '#__sportsmanagement_season_team_id as st ON st.id = pt1.team_id ');
			$query->join('INNER', '#__sportsmanagement_team t1 ON t1.id = st.team_id');
			$query->join('LEFT', '#__sportsmanagement_playground AS playground ON pt1.standard_playground = playground.id');

			$query->where('pt1.project_id = ' . self::$projectid);

			if (self::$divisionid != 0)
			{
				$query->where('pt1.division_id = ' . self::$divisionid);
			}

			$query->where('matches.published = 1');
			$query->group('matches.projectteam1_id');
			$query->order('avgspectatorspt DESC');

			$db->setQuery($query);

			$this->attendanceranking = $db->loadObjectList();
		}

		return $this->attendanceranking;
	}

	/**
	 * sportsmanagementModelStats::getBestAvgTeam()
	 *
	 * @return
	 */
	function getBestAvgTeam()
	{
		$attendanceranking = self::getAttendanceRanking();

		return (count($attendanceranking) > 0) ? $attendanceranking[0]->team : 0;
	}

	/**
	 * sportsmanagementModelStats::getWorstAvg()
	 *
	 * @return
	 */
	function getWorstAvg()
	{
		$attendanceranking = self::getAttendanceRanking();
		$worstavg          = 0;

		if (count($attendanceranking))
		{
			$n        = count($attendanceranking);
			$worstavg = round($attendanceranking[$n - 1]->avgspectatorspt);
		}

		return $worstavg;
	}

	/**
	 * sportsmanagementModelStats::getWorstAvgTeam()
	 *
	 * @return
	 */
	function getWorstAvgTeam()
	{
		$attendanceranking = self::getAttendanceRanking();
		$worstavgteam      = 0;

		if (count($attendanceranking))
		{
			$n            = count($attendanceranking);
			$worstavgteam = $attendanceranking[$n - 1]->team;
		}

		return $worstavgteam;
	}

	/**
	 * sportsmanagementModelStats::getChartURL()
	 *
	 * @return
	 */
	function getChartURL()
	{
		$url = sportsmanagementHelperRoute::getStatsChartDataRoute($this->projectid, $this->divisionid);
		$url = str_replace('&', '%26', $url);

		return $url;
	}

	// Comparisations in stats view

	/**
	 * sportsmanagementModelStats::teamNameCmp2()
	 *
	 * @param   mixed  $a
	 * @param   mixed  $b
	 *
	 * @return
	 */
	function teamNameCmp2(&$a, &$b)
	{
		return strcasecmp($a->team, $b->team);
	}

	/**
	 * sportsmanagementModelStats::totalattendCmp()
	 *
	 * @param   mixed  $a
	 * @param   mixed  $b
	 *
	 * @return
	 */
	function totalattendCmp(&$a, &$b)
	{
		$res = ($a->sumspectatorspt - $b->sumspectatorspt);

		return $res;
	}

	/**
	 * sportsmanagementModelStats::avgattendCmp()
	 *
	 * @param   mixed  $a
	 * @param   mixed  $b
	 *
	 * @return
	 */
	function avgattendCmp(&$a, &$b)
	{
		$res = ($a->avgspectatorspt - $b->avgspectatorspt);

		return $res;
	}

	/**
	 * sportsmanagementModelStats::capacityCmp()
	 *
	 * @param   mixed  $a
	 * @param   mixed  $b
	 *
	 * @return
	 */
	function capacityCmp(&$a, &$b)
	{
		$res = ($a->capacity - $b->capacity);

		return $res;
	}

	/**
	 * sportsmanagementModelStats::utilisationCmp()
	 *
	 * @param   mixed  $a
	 * @param   mixed  $b
	 *
	 * @return
	 */
	function utilisationCmp(&$a, &$b)
	{
		$res = (($a->capacity ? ($a->avgspectatorspt / $a->capacity) : 0) - ($b->capacity > 0 ? ($b->avgspectatorspt / $b->capacity) : 0));

		return $res;
	}
}

