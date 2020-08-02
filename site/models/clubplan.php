<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage clubplan
 * @file       clubplan.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * sportsmanagementModelClubPlan
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementModelClubPlan extends BaseDatabaseModel
{
	static $clubid = 0;
	static $project_id = 0;
	static $startdate = null;
	static $enddate = null;
	static $teamartsel = 0;
	static $type = 0;
	static $teamprojectssel = 0;
	static $teamseasonssel = 0;
	static $cfg_which_database = 0;
	var $club = null;
	var $awaymatches = null;
	var $homematches = null;
	var $allmatches = null;
	var $teamprojects = 0;
	var $teamseasons = 0;

	/**
	 * sportsmanagementModelClubPlan::__construct()
	 *
	 * @return void
	 */
	function __construct()
	{
		$app = Factory::getApplication();
		$jinput = $app->input;
		parent::__construct();
		self::$clubid     = $jinput->request->get('cid', 0, 'INT');
		self::$project_id = $jinput->request->get('p', 0, 'INT');

		self::$teamartsel      = $jinput->request->get('teamartsel', 0, 'INT');
		self::$type            = $jinput->request->get('type', 0, 'INT');
		self::$teamprojectssel = $jinput->request->get('teamprojectssel', 0, 'INT');
		self::$teamseasonssel  = $jinput->request->get('teamseasonssel', 0, 'INT');

		self::setStartDate($jinput->request->get('startdate', self::$startdate, 'STR'));
		self::setEndDate($jinput->request->get('enddate', self::$enddate, 'STR'));

		self::$cfg_which_database = $jinput->request->get('cfg_which_database', 0, 'INT');
	}

	/**
	 * sportsmanagementModelClubPlan::setStartDate()
	 *
	 * @param   mixed  $date
	 *
	 * @return void
	 */
	public static function setStartDate($date)
	{
		$app = Factory::getApplication();

		// Should be in proper sql format
		if (strtotime($date))
		{
			self::$startdate = strftime("%Y-%m-%d", strtotime($date));
		}
		else
		{
			self::$startdate = null;
		}
	}

	/**
	 * sportsmanagementModelClubPlan::setEndDate()
	 *
	 * @param   mixed  $date
	 *
	 * @return void
	 */
	public static function setEndDate($date)
	{
		$app = Factory::getApplication();

		// Should be in proper sql format
		if (strtotime($date))
		{
			self::$enddate = strftime("%Y-%m-%d", strtotime($date));
		}
		else
		{
			self::$enddate = null;
		}
	}

	/**
	 * sportsmanagementModelClubPlan::getClubIconHtmlSimple()
	 *
	 * @param   mixed    $logo_small
	 * @param   mixed    $country
	 * @param   integer  $type
	 * @param   integer  $with_space
	 *
	 * @return
	 */
	static function getClubIconHtmlSimple($logo_small, $country, $type = 1, $with_space = 0)
	{
		if ($type == 1)
		{
			$params           = array();
			$params["align"]  = "top";
			$params["border"] = 0;
			$params['width']  = 21;
			$params['hight']  = 'auto';

			if ($with_space == 1)
			{
				$params["style"] = "padding:1px;";
			}

			if ($logo_small == "")
			{
				$logo_small = sportsmanagementHelper::getDefaultPlaceholder("clublogosmall");
			}

			return HTMLHelper::image($logo_small, "", $params);
		}
		elseif ($type == 2 && isset($country))
		{
			return JSMCountries::getCountryFlag($team->country);
		}
	}

	/**
	 * sportsmanagementModelClubPlan::getTeamsArt()
	 *
	 * @return
	 */
	function getTeamsArt()
	{
		// Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

		// Get a db connection.
		$db    = sportsmanagementHelper::getDBConnection(true, self::$cfg_which_database);
		$query = $db->getQuery(true);

		if (self::$clubid > 0)
		{
			$query->select('ag.id as value,ag.name as text');

			// From
			$query->from('#__sportsmanagement_team as t');
			$query->join('INNER', '#__sportsmanagement_agegroup as ag ON ag.id = t.agegroup_id');

			// Where
			$query->where('t.club_id = ' . (int) self::$clubid);

			// Group
			$query->group('ag.id');

			// Order
			$query->order('ag.name ASC');

			try
			{
				$db->setQuery($query);
				$teamsart = $db->loadObjectList();
			}
			catch (Exception $e)
			{
				$app->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage()), 'error');
			}
		}

		return $teamsart;
	}

	/**
	 * sportsmanagementModelClubPlan::getTeamsProjects()
	 *
	 * @return
	 */
	function getTeamsProjects()
	{
		$option = Factory::getApplication()->input->getCmd('option');
		$app    = Factory::getApplication();

		// Get a db connection.
		$db        = sportsmanagementHelper::getDBConnection(true, self::$cfg_which_database);
		$query     = $db->getQuery(true);
		$starttime = microtime();

		if (self::$clubid > 0)
		{
			$query->select('p.id as value,p.name as text');

			// From
			$query->from('#__sportsmanagement_team as t');
			$query->join('INNER', ' #__sportsmanagement_season_team_id as st ON st.team_id = t.id ');
			$query->join('INNER', ' #__sportsmanagement_season as s ON s.id = st.season_id ');
			$query->join('INNER', ' #__sportsmanagement_project_team as pt ON pt.team_id = st.id ');
			$query->join('INNER', ' #__sportsmanagement_project as p ON p.id = pt.project_id ');

			// Where
			$query->where('t.club_id = ' . (int) self::$clubid);

			// Group
			$query->group('p.id,p.name');

			// Order
			$query->order('p.name DESC');

			$db->setQuery($query);

			$teamsprojects = $db->loadObjectList();
		}

		return $teamsprojects;

	}

	/**
	 * sportsmanagementModelClubPlan::getTeamsSeasons()
	 *
	 * @return
	 */
	function getTeamsSeasons()
	{
		$option = Factory::getApplication()->input->getCmd('option');
		$app    = Factory::getApplication();

		// Get a db connection.
		$db    = sportsmanagementHelper::getDBConnection(true, self::$cfg_which_database);
		$query = $db->getQuery(true);

		if (self::$clubid > 0)
		{
			$query->select('s.id as value,s.name as text');

			// From
			$query->from('#__sportsmanagement_team as t');
			$query->join('INNER', ' #__sportsmanagement_season_team_id as st ON st.team_id = t.id ');
			$query->join('INNER', ' #__sportsmanagement_season as s ON s.id = st.season_id ');

			// Where
			$query->where('t.club_id = ' . (int) self::$clubid);

			// Group
			$query->group('s.id,s.name');

			// Order
			$query->order('s.name DESC');

			$db->setQuery($query);
			$teamsseasons = $db->loadObjectList();
		}

		return $teamsseasons;

	}

	/**
	 * sportsmanagementModelClubPlan::getAllMatches()
	 *
	 * @param   string   $orderBy
	 * @param   integer  $type
	 *
	 * @return
	 */
	function getAllMatches($orderBy = 'ASC', $type = 0)
	{
		$option = Factory::getApplication()->input->getCmd('option');
		$app    = Factory::getApplication();

		$project           = sportsmanagementModelProject::getProject(self::$cfg_which_database);
		$this->teamseasons = $project->season_id;

		// Get a db connection.
		$db    = sportsmanagementHelper::getDBConnection(true, self::$cfg_which_database);
		$query = $db->getQuery(true);

		$result    = array();
		$round_ids = null;
		$teams     = self::getTeams();
		$startdate = self::getStartDate();
		$enddate   = self::getEndDate();

		if (is_null($teams))
		{
			return null;
		}

		/*
		if ( $startdate && $enddate )
		{
		$query->clear();
		$query->select('r.id');
		$query->from('#__sportsmanagement_round as r');
		$query->join('INNER','#__sportsmanagement_match as m ON m.round_id = r.id ');
		$query->where('(r.round_date_first >= '.$db->Quote(''.$startdate.'').' AND r.round_date_last <= '.$db->Quote(''.$enddate.'').')');
		$query->group('r.id');
		$db->setquery($query);

		if(version_compare(JVERSION,'3.0.0','ge'))
		{
		// Joomla! 3.0 code here
		$rounds = $db->loadColumn();
		}
		elseif(version_compare(JVERSION,'2.5.0','ge'))
		{
		// Joomla! 2.5 code here
		$rounds = $db->loadResultArray();
		}

		if ( $rounds )
		{
		$round_ids = implode(',',$rounds);
		}
		}
		*/
		$start_timestamp = sportsmanagementHelper::getTimestamp($startdate . ' 00:00:00');
		$end_timestamp   = sportsmanagementHelper::getTimestamp($enddate . ' 23:59:59');

		// If ( $round_ids )
		// {
		$query->clear();

		// $query->select('m.*,m.id as match_id ,DATE_FORMAT(m.time_present,"%H:%i") time_present');
		$query->select('m.match_date,m.projectteam1_id,m.projectteam2_id,m.id as match_id ,DATE_FORMAT(m.time_present,"%H:%i") time_present');
		$query->select('m.playground_id,m.alt_decision ,m.team1_result ,m.team2_result ,m.cancel,m.cancel_reason');
		$query->select('p.name AS project_name,p.id AS project_id,p.id AS prid,CONCAT_WS(\':\',p.id,p.alias) AS project_slug');
		$query->select('r.id AS roundid,r.roundcode AS roundcode,r.name AS roundname');
		$query->select('l.name AS l_name');
		$query->select('playground.name AS pl_name');
		$query->select('CONCAT_WS(\':\',playground.id,playground.alias) AS playground_slug');
		$query->select('t1.club_id as t1club_id,t1.id AS team1_id,t1.name AS tname1,t1.short_name AS tname1_short,t1.middle_name AS tname1_middle,t1.club_id AS club1_id,CONCAT_WS(\':\',t1.id,t1.alias) AS team1_slug');
		$query->select('t2.club_id as t2club_id,t2.id AS team2_id,t2.name AS tname2,t2.short_name AS tname2_short,t2.middle_name AS tname2_middle,t2.club_id AS club2_id,CONCAT_WS(\':\',t2.id,t2.alias) AS team2_slug');
		$query->select('c1.logo_small AS home_logo_small,CONCAT_WS(\':\',c1.id,c1.alias) AS club1_slug,c1.country AS club1_country');
		$query->select('c2.logo_small AS away_logo_small,CONCAT_WS(\':\',c2.id,c2.alias) AS club2_slug,c2.country AS club2_country');
		$query->select('c1.logo_big AS home_logo_big');
		$query->select('c2.logo_big AS away_logo_big');
		$query->select('c1.logo_middle AS home_logo_middle');
		$query->select('c2.logo_middle AS away_logo_middle');
		$query->select('tj1.division_id');

		$query->select('CONCAT_WS(\':\',m.projectteam1_id,t1.alias) AS projectteam1_slug');
		$query->select('CONCAT_WS(\':\',m.projectteam2_id,t2.alias) AS projectteam2_slug');

		$query->select('d.name AS division_name, d.shortname AS division_shortname, d.parent_id AS parent_division_id,CONCAT_WS(\':\',d.id,d.alias) AS division_slug');

		$query->select('CONCAT_WS(\':\',m.id,CONCAT_WS("_",t1.alias,t2.alias)) AS match_slug ');
		$query->select('CONCAT_WS(\':\',r.id,r.alias) AS round_slug');

		// From
		$query->from('#__sportsmanagement_match AS m');

		// Join
		$query->join('INNER', '#__sportsmanagement_project_team as tj1 ON tj1.id = m.projectteam1_id ');
		$query->join('INNER', '#__sportsmanagement_project_team as tj2 ON tj2.id = m.projectteam2_id ');
		$query->join('INNER', '#__sportsmanagement_season_team_id as st1 ON st1.id = tj1.team_id ');
		$query->join('INNER', '#__sportsmanagement_season_team_id as st2 ON st2.id = tj2.team_id ');
		$query->join('INNER', '#__sportsmanagement_team as t1 ON t1.id = st1.team_id ');
		$query->join('INNER', '#__sportsmanagement_team as t2 ON t2.id = st2.team_id ');
		$query->join('INNER', '#__sportsmanagement_project AS p ON p.id = tj1.project_id ');
		$query->join('INNER', '#__sportsmanagement_league as l ON p.league_id = l.id ');
		$query->join('INNER', '#__sportsmanagement_club as c1 ON c1.id = t1.club_id ');
		$query->join('INNER', '#__sportsmanagement_round as r ON m.round_id = r.id ');
		$query->join('INNER', '#__sportsmanagement_club as c2 ON c2.id = t2.club_id ');
		$query->join('LEFT', '#__sportsmanagement_playground AS playground ON playground.id = m.playground_id ');
		$query->join('LEFT', '#__sportsmanagement_division as d ON d.id = tj1.division_id');

		// Where
		$query->where('p.published = 1');

		if (self::$project_id == 0 && self::$teamartsel == 0 && self::$teamseasonssel == 0)
		{
			// $query->where('(r.round_date_first >= '.$db->Quote(''.$startdate.'').' AND r.round_date_last <= '.$db->Quote(''.$enddate.'').')');
			$query->where('(m.match_timestamp >= ' . $start_timestamp . ' AND m.match_timestamp <= ' . $end_timestamp . ')');
		}

		if ($startdate && $enddate)
		{
			// $query->where('m.round_id IN ('.$round_ids.')');
			$query->where('(m.match_timestamp >= ' . $start_timestamp . ' AND m.match_timestamp <= ' . $end_timestamp . ')');
		}

		if (self::$teamartsel > 0)
		{
			// Where
			$query->where("( t1.agegroup_id = " . self::$teamartsel . " OR t2.agegroup_id = " . self::$teamartsel . " )");
		}

		if (self::$teamseasonssel > 0)
		{
			// Where
			$query->where('p.season_id = ' . self::$teamseasonssel);
		}

		if (self::$clubid > 0)
		{
			switch ($type)
			{
				case 0:
				case 3:
				case 4:
					// Where
					$query->where('(t1.club_id = ' . self::$clubid . ' OR t2.club_id = ' . self::$clubid . ')');
					break;
				case 1:
					// Where
					$query->where('t1.club_id = ' . self::$clubid);
					break;
				case 2:
					// Where
					$query->where('t2.club_id = ' . self::$clubid);
					break;
			}
		}

		// Where
		$query->where('m.published = 1');

		// Order
		$query->order('m.match_date ' . $orderBy);

		try
		{
			$db->setQuery($query);
			$this->allmatches = $db->loadObjectList();
		}
		catch (Exception $e)
		{
			$app->enqueueMessage(Text::_($e->getMessage()), 'error');
		}

		// }

		if (!$this->allmatches)
		{
			$app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_CLUBPLAN_NO_MATCHES'), 'Error');
		}

		return $this->allmatches;
	}

	/**
	 * sportsmanagementModelClubPlan::getTeams()
	 *
	 * @return
	 */
	function getTeams()
	{
		$option = Factory::getApplication()->input->getCmd('option');
		$app    = Factory::getApplication();

		// Get a db connection.
		$db    = sportsmanagementHelper::getDBConnection(true, self::$cfg_which_database);
		$query = $db->getQuery(true);

		$teams = array(0);

		if (self::$clubid > 0)
		{
			$query->select('id,name as team_name,short_name as team_shortcut,info as team_description');

			// From
			$query->from('#__sportsmanagement_team');

			// Where
			$query->where('club_id = ' . (int) self::$clubid);

			$db->setQuery($query);
			$teams = $db->loadObjectList();
		}

		if (!$teams)
		{
		}

		return $teams;
	}

	/**
	 * sportsmanagementModelClubPlan::getStartDate()
	 *
	 * @return
	 */
	function getStartDate()
	{
		$app = Factory::getApplication();

		$config = sportsmanagementModelProject::getTemplateConfig("clubplan");

		if (empty(self::$startdate))
		{
			$dayz = $config['days_before'];

			// $dayz=6;
			$prevweek        = mktime(0, 0, 0, date("m"), date("d") - $dayz, date("y"));
			self::$startdate = date("Y-m-d", $prevweek);
		}

		if ($config['use_project_start_date'] && empty(self::$startdate))
		{
			$project         = sportsmanagementModelProject::getProject(self::$cfg_which_database);
			self::$startdate = $project->start_date;
		}

		return self::$startdate;
	}

	/**
	 * sportsmanagementModelClubPlan::getEndDate()
	 *
	 * @return
	 */
	function getEndDate()
	{
		$app = Factory::getApplication();

		if (empty(self::$enddate))
		{
			$config = sportsmanagementModelProject::getTemplateConfig("clubplan");
			$dayz   = $config['days_after'];

			// $dayz=6;
			$nextweek      = mktime(0, 0, 0, date("m"), date("d") + $dayz, date("y"));
			self::$enddate = date("Y-m-d", $nextweek);
		}

		return self::$enddate;
	}

	/**
	 * sportsmanagementModelClubPlan::getMatchReferees()
	 *
	 * @param   mixed  $matchID
	 *
	 * @return
	 */
	function getMatchReferees($matchID)
	{
		$option = Factory::getApplication()->input->getCmd('option');
		$app    = Factory::getApplication();

		$db    = sportsmanagementHelper::getDBConnection(true, self::$cfg_which_database);
		$query = $db->getQuery(true);
		$query->select('p.id,p.firstname,p.lastname,p.nickname,CONCAT_WS(\':\',p.id,p.alias) AS person_slug');
		$query->select('mp.project_position_id');
		$query->from('#__sportsmanagement_match_referee AS mp');
		$query->join('LEFT', ' #__sportsmanagement_project_referee AS pref ON mp.project_referee_id = pref.id ');
		$query->join('INNER', ' #__sportsmanagement_season_person_id AS sp ON pref.person_id = sp.id ');
		$query->join('INNER', ' #__sportsmanagement_person AS p ON sp.person_id = p.id ');
		$query->where('mp.match_id = ' . (int) $matchID);
		$query->where('p.published = 1');

		$db->setQuery($query);

		$result = $db->loadObjectList();

		return $result;
	}

}
