<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage nextmatch
 * @file       nextmatch.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * sportsmanagementModelNextMatch
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementModelNextMatch extends BaseDatabaseModel
{
	static $matchid = 0;
	static $projectteamid = 0;
	static $projectid = 0;
	static $showpics = 0;
	static $cfg_which_database = 0;
	var $project = null;
	var $divisionid = 0;
	var $ranking = null;
	var $teams = null;
	/**
	 * caching match data
	 *
	 * @var object
	 */
	var $_match = null;

	/**
	 * sportsmanagementModelNextMatch::__construct()
	 *
	 * @return void
	 */
	function __construct()
	{
		/**
		 *
		 * Reference global application object
		 */
		$app = Factory::getApplication();
		parent::__construct();
		self::$projectid          = Factory::getApplication()->input->get('p', 0, 'INT');
		self::$matchid            = Factory::getApplication()->input->get('mid', 0, 'INT');
		self::$showpics           = Factory::getApplication()->input->get('pics', 0, 'INT');
		self::$projectteamid      = Factory::getApplication()->input->get('ptid', 0, 'INT');
		self::$cfg_which_database = Factory::getApplication()->input->get('cfg_which_database', 0, 'INT');

		sportsmanagementModelProject::$projectid          = self::$projectid;
		sportsmanagementModelProject::$cfg_which_database = self::$cfg_which_database;

		if (self::$projectteamid)
		{
			self::getSpecifiedMatch(self::$projectid, self::$projectteamid, self::$matchid);
		}

	}

	/**
	 * sportsmanagementModelNextMatch::getSpecifiedMatch()
	 *
	 * @param   mixed  $projectId
	 * @param   mixed  $projectTeamId
	 * @param   mixed  $matchId
	 *
	 * @return
	 */
	function getSpecifiedMatch($projectId, $projectTeamId, $matchId)
	{
		$app    = Factory::getApplication();
		$option = Factory::getApplication()->input->getCmd('option');
		/**
		 *
		 * Create a new query object.
		 */
		$db    = sportsmanagementHelper::getDBConnection(true, self::$cfg_which_database);
		$query = $db->getQuery(true);

		if (!$this->_match)
		{
			/**
			 *
			 * Select some fields
			 */
			$query->select('m.*, DATE_FORMAT(m.time_present, "%H:%i") time_present');
			$query->select('t1.project_id');
			$query->select('r.roundcode');

			$query->from('#__sportsmanagement_match AS m  ');

			$config      = sportsmanagementModelProject::getTemplateConfig($this->getName());
			$expiry_time = $config ? $config['expiry_time'] : 0;

			$query->join('INNER', '#__sportsmanagement_round AS r ON r.id = m.round_id ');
			$query->join('INNER', '#__sportsmanagement_project_team AS t1 ON t1.id = m.projectteam1_id ');
			$query->join('INNER', '#__sportsmanagement_project_team AS t2 ON t2.id = m.projectteam2_id ');
			$query->join('INNER', '#__sportsmanagement_project AS p ON p.id = t1.project_id ');
			$query->where('DATE_ADD(m.match_date, INTERVAL ' . $db->Quote($expiry_time) . ' MINUTE) >= NOW()');

			$query->where('m.cancel = 0');

			if ($matchId)
			{
				$query->where('m.id = ' . $db->Quote($matchId));
			}
			else
			{
				$query->where('(team1_result is null  OR  team2_result is null)');

				if ($projectTeamId)
				{
					$query->where('(m.projectteam1_id = ' . $db->Quote($projectTeamId) . ' OR m.projectteam2_id = ' . $db->Quote($projectTeamId) . ')');
				}
				else
				{
					$query->where('(m.projectteam1_id > 0  OR  m.projectteam2_id > 0)');
				}
			}

			if ($projectId)
			{
				$query->where('t1.project_id = ' . $db->Quote($projectId));
			}

			$query->order('m.match_date');

			$db->setQuery($query, 0, 1);
			$this->_match = $db->loadObject();

			if (!$this->_match)
			{
			}

			if ($this->_match)
			{
				self::$projectid = $this->_match->project_id;
				self::$matchid   = $this->_match->id;
			}
		}

		$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

		return $this->_match;
	}

	/**
	 * sportsmanagementModelNextMatch::getShowPics()
	 *
	 * @return
	 */
	function getShowPics()
	{
		return $this->showpics;
	}

	/**
	 * sportsmanagementModelNextMatch::getReferees()
	 *
	 * @return
	 */
	function getReferees()
	{
		$app    = Factory::getApplication();
		$option = Factory::getApplication()->input->getCmd('option');
		/**
		 *
		 * Create a new query object.
		 */
		$db    = sportsmanagementHelper::getDBConnection(true, self::$cfg_which_database);
		$query = $db->getQuery(true);

		$match = self::getMatch();

		/**
		 *
		 * Select some fields
		 */
		$query->select('p.firstname, p.nickname, p.lastname, p.country, p.id as person_id');
		$query->select('pos.name AS position_name');
		$query->from('#__sportsmanagement_match_referee AS mr  ');
		$query->join('INNER', '#__sportsmanagement_project_referee AS pref ON mr.project_referee_id = pref.id  ');
		$query->join('INNER', '#__sportsmanagement_season_person_id AS spi ON pref.person_id=spi.id');
		$query->join('INNER', '#__sportsmanagement_person AS p ON p.id = spi.person_id ');
		$query->join('INNER', '#__sportsmanagement_project_position ppos ON ppos.id = mr.project_position_id');
		$query->join('INNER', '#__sportsmanagement_position AS pos ON pos.id = ppos.position_id  ');

		$query->where('mr.match_id = ' . $db->Quote($match->id));
		$query->where('p.published = 1');

		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * get match info
	 *
	 * @return object
	 */
	function getMatch()
	{
		$app    = Factory::getApplication();
		$option = Factory::getApplication()->input->getCmd('option');
		/**
		 *
		 * Create a new query object.
		 */
		$db    = sportsmanagementHelper::getDBConnection(true, self::$cfg_which_database);
		$query = $db->getQuery(true);

		if (empty($this->_match))
		{
			/**
			 *
			 * Select some fields
			 */
			$query->select('m.*, DATE_FORMAT(m.time_present, "%H:%i") time_present');
			$query->select('t1.project_id');
			$query->select('r.roundcode');
			$query->select('CONCAT_WS( \':\', pl.id, pl.alias ) AS playground_slug');
			$query->from('#__sportsmanagement_match AS m ');
			$query->join('INNER', '#__sportsmanagement_project_team AS t1 ON t1.id = m.projectteam1_id ');
			$query->join('INNER', '#__sportsmanagement_round AS r ON r.id = m.round_id ');
			$query->join('LEFT', '#__sportsmanagement_playground AS pl ON pl.id = m.playground_id ');
			$query->where('m.id = ' . self::$matchid);

			try
			{
				$db->setQuery($query, 0, 1);
				$this->_match = $db->loadObject();
			}
			catch (Exception $e)
			{
				$app->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage()), 'error');
			}
		}

		$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

		return $this->_match;
	}

	/**
	 * sportsmanagementModelNextMatch::getHomeHighestHomeWin()
	 *
	 * @return
	 */
	function getHomeHighestHomeWin()
	{
		$teams = self::getMatchTeams();

		if (is_null($teams))
		{
			return null;
		}

		return self::_getHighestMatches($teams[0]->team_id, 'HOME', 'WIN');
	}

	/**
	 * get match teams details
	 *
	 * @return array
	 */
	function getMatchTeams()
	{
		if (empty($this->teams))
		{
			$this->teams = array();

			$match = $this->getMatch();

			if (is_null($match))
			{
				return null;
			}

			$team1         = sportsmanagementModelProject::getTeaminfo($match->projectteam1_id, self::$cfg_which_database);
			$team2         = sportsmanagementModelProject::getTeaminfo($match->projectteam2_id, self::$cfg_which_database);
			$this->teams[] = $team1;
			$this->teams[] = $team2;
			/**
			 *             Set the division id, so that the home and away ranks are
			 *             determined for a division, if the team is part of a division
			 */
			$this->divisionid = $team1->division_id;
		}

		return $this->teams;
	}

	/**
	 * sportsmanagementModelNextMatch::_getHighestMatches()
	 *
	 * @param   mixed  $teamid
	 * @param   mixed  $whichteam
	 * @param   mixed  $gameart
	 *
	 * @return
	 */
	function _getHighestMatches($teamid, $whichteam, $gameart)
	{
		$app    = Factory::getApplication();
		$option = Factory::getApplication()->input->getCmd('option');
		/**
		 *
		 * Create a new query object.
		 */
		$db    = sportsmanagementHelper::getDBConnection(true, self::$cfg_which_database);
		$query = $db->getQuery(true);

		/**
		 *
		 * Select some fields
		 */
		$query->select('m.id AS mid,m.team1_result AS homegoals,m.team2_result AS awaygoals');
		$query->select('t1.name AS hometeam');
		$query->select('t2.name AS awayteam');
		$query->select('pt1.project_id AS pid');
		$query->select('CONCAT_WS(\':\',m.id,CONCAT_WS("_",t1.alias,t2.alias)) AS match_slug ');
		$query->select('CONCAT_WS( \':\', p.id, p.alias ) AS project_slug');
		$query->from('#__sportsmanagement_match as m  ');
		$query->join('INNER', '#__sportsmanagement_project_team pt1 ON pt1.id = m.projectteam1_id  ');
		$query->join('INNER', '#__sportsmanagement_season_team_id AS st1 ON st1.id = pt1.team_id ');
		$query->join('INNER', '#__sportsmanagement_team AS t1 ON t1.id = st1.team_id ');
		$query->join('INNER', '#__sportsmanagement_project_team pt2 ON pt2.id = m.projectteam2_id  ');
		$query->join('INNER', '#__sportsmanagement_season_team_id AS st2 ON st2.id = pt2.team_id ');
		$query->join('INNER', '#__sportsmanagement_team AS t2 ON t2.id = st2.team_id ');

		$query->join('INNER', '#__sportsmanagement_round AS r ON m.round_id = r.id ');
		$query->join('INNER', '#__sportsmanagement_project AS p ON p.id = r.project_id ');

		$query->where('pt1.project_id = ' . self::$projectid);

		$query->where('m.published = 1');
		$query->where('m.alt_decision = 0');

		switch ($whichteam)
		{
			case 'HOME':
				$query->where('t1.id = ' . $db->Quote($teamid));

				switch ($gameart)
				{
					case 'WIN':
						$query->where('(team1_result - team2_result > 0)');
						$query->order('(team1_result - team2_result) DESC ');
						break;
					case 'LOST':
						$query->where('(team1_result - team2_result < 0)');
						$query->order('(team1_result - team2_result) ASC ');
						break;
				}
				break;
			case 'AWAY':
				$query->where('t2.id = ' . $db->Quote($teamid));

				switch ($gameart)
				{
					case 'WIN':
						$query->where('(team2_result - team1_result > 0)');
						$query->order('(team2_result - team1_result) DESC ');
						break;
					case 'LOST':
						$query->where('(team2_result - team1_result < 0)');
						$query->order('(team2_result - team1_result) ASC ');
						break;
				}
				break;
		}

		$db->setQuery($query, 0, 1);

		return $db->loadObject();
	}

	/**
	 * sportsmanagementModelNextMatch::getAwayHighestHomeWin()
	 *
	 * @return
	 */
	function getAwayHighestHomeWin()
	{
		$teams = self::getMatchTeams();

		if (is_null($teams))
		{
			return null;
		}

		return self::_getHighestMatches($teams[1]->team_id, 'AWAY', 'WIN');
	}

	/**
	 * sportsmanagementModelNextMatch::getHomeHighestHomeDef()
	 *
	 * @return
	 */
	function getHomeHighestHomeDef()
	{
		$teams = self::getMatchTeams();

		if (is_null($teams))
		{
			return null;
		}

		return self::_getHighestMatches($teams[0]->team_id, 'HOME', 'LOST');
	}

	/**
	 * sportsmanagementModelNextMatch::getAwayHighestHomeDef()
	 *
	 * @return
	 */
	function getAwayHighestHomeDef()
	{
		$teams = self::getMatchTeams();

		if (is_null($teams))
		{
			return null;
		}

		return self::_getHighestMatches($teams[1]->team_id, 'AWAY', 'LOST');
	}

	/**
	 * sportsmanagementModelNextMatch::getHomeHighestAwayWin()
	 *
	 * @return
	 */
	function getHomeHighestAwayWin()
	{
		$teams = self::getMatchTeams();

		if (is_null($teams))
		{
			return null;
		}

		return self::_getHighestMatches($teams[0]->team_id, 'AWAY', 'WIN');
	}

	/**
	 * sportsmanagementModelNextMatch::getAwayHighestAwayWin()
	 *
	 * @return
	 */
	function getAwayHighestAwayWin()
	{
		$teams = self::getMatchTeams();

		if (is_null($teams))
		{
			return null;
		}

		return self::_getHighestMatches($teams[1]->team_id, 'HOME', 'WIN');
	}

	/**
	 * sportsmanagementModelNextMatch::getHomeHighestAwayDef()
	 *
	 * @return
	 */
	function getHomeHighestAwayDef()
	{
		$teams = self::getMatchTeams();

		if (is_null($teams))
		{
			return null;
		}

		return self::_getHighestMatches($teams[0]->team_id, 'AWAY', 'LOST');
	}

	/**
	 * sportsmanagementModelNextMatch::getAwayHighestAwayDef()
	 *
	 * @return
	 */
	function getAwayHighestAwayDef()
	{
		$teams = self::getMatchTeams();

		if (is_null($teams))
		{
			return null;
		}

		return self::_getHighestMatches($teams[1]->team_id, 'HOME', 'LOST');
	}

	/**
	 * get all games in all projects for these 2 teams
	 *
	 * @return array
	 */
	function getGames()
	{
		$app    = Factory::getApplication();
		$option = Factory::getApplication()->input->getCmd('option');
		$db                  = sportsmanagementHelper::getDBConnection(true, self::$cfg_which_database);
		$query               = $db->getQuery(true);
		$not_used_project_id = array();
		$result              = array();
		$teams               = self::getMatchTeams();

		if (is_null($teams))
		{
			return null;
		}

		/** schritt 1 */
		$query->clear();
		$query->select('m.id,m.match_date,m.team1_result,m.team2_result,m.show_report,m.projectteam1_id,m.projectteam2_id');
		$query->select('DATE_FORMAT(m.time_present, "%H:%i") time_present');
		$query->select('pt1.project_id');
		$query->select('s.name as seasonname,s.id as season_id');
		$query->select('l.name as leaguename,l.id as league_id');
		$query->select('p.name AS project_name,p.id AS prid');
		$query->select('r.id AS roundid,r.roundcode AS roundcode,r.name AS mname');
		$query->select('t1.id as team1_id');
		$query->select('t2.id as team2_id');
		$query->select('CONCAT_WS(\':\',m.id,CONCAT_WS("_",t1.alias,t2.alias)) AS match_slug ');
		$query->select('CONCAT_WS( \':\', p.id, p.alias ) AS project_slug');
		$query->select('CONCAT_WS( \':\', r.id, r.alias ) AS round_slug');
		$query->from('#__sportsmanagement_match AS m ');
		$query->join('INNER', '#__sportsmanagement_project_team AS pt1 ON pt1.id = m.projectteam1_id ');
		$query->join('INNER', '#__sportsmanagement_season_team_id AS st1 ON st1.id = pt1.team_id ');
		$query->join('INNER', '#__sportsmanagement_team AS t1 ON t1.id = st1.team_id ');
		$query->join('INNER', '#__sportsmanagement_project_team AS pt2 ON pt2.id = m.projectteam2_id ');
		$query->join('INNER', '#__sportsmanagement_season_team_id AS st2 ON st2.id = pt2.team_id ');
		$query->join('INNER', '#__sportsmanagement_team AS t2 ON t2.id = st2.team_id ');
		$query->join('INNER', '#__sportsmanagement_project AS p ON p.id = pt1.project_id ');
		$query->join('INNER', '#__sportsmanagement_league AS l ON l.id = p.league_id ');
		$query->join('INNER', '#__sportsmanagement_season AS s ON s.id = p.season_id ');
		$query->join('INNER', '#__sportsmanagement_round r ON m.round_id = r.id  ');
		$query->where('(st1.team_id = ' . $teams[0]->team_id . ' AND st2.team_id = ' . $teams[1]->team_id . ')');
		$query->where('p.published = 1');
		$query->where('m.published = 1');
		$query->order('s.name DESC, m.match_date ASC');
		$db->setQuery($query);
		try{
		$result1 = $db->loadObjectList();
}
			catch (Exception $e)
			{
				$app->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), 'notice');
                $app->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__), 'notice');
			}
		/**
		 * schritt 2
		 */
		$query->clear();
		$query->select('m.id,m.match_date,m.team1_result,m.team2_result,m.show_report,m.projectteam1_id,m.projectteam2_id');
		$query->select('DATE_FORMAT(m.time_present, "%H:%i") time_present');
		$query->select('pt1.project_id');
		$query->select('s.name as seasonname,s.id as season_id');
		$query->select('l.name as leaguename,l.id as league_id');
		$query->select('p.name AS project_name,p.id AS prid');
		$query->select('r.id AS roundid,r.roundcode AS roundcode,r.name AS mname');
		$query->select('t1.id as team1_id');
		$query->select('t2.id as team2_id');
		$query->select('CONCAT_WS(\':\',m.id,CONCAT_WS("_",t1.alias,t2.alias)) AS match_slug ');
		$query->select('CONCAT_WS( \':\', p.id, p.alias ) AS project_slug');
		$query->select('CONCAT_WS( \':\', r.id, r.alias ) AS round_slug');
		$query->from('#__sportsmanagement_match AS m ');
		$query->join('INNER', '#__sportsmanagement_project_team AS pt1 ON pt1.id = m.projectteam1_id ');
		$query->join('INNER', '#__sportsmanagement_season_team_id AS st1 ON st1.id = pt1.team_id ');
		$query->join('INNER', '#__sportsmanagement_team AS t1 ON t1.id = st1.team_id ');
		$query->join('INNER', '#__sportsmanagement_project_team AS pt2 ON pt2.id = m.projectteam2_id ');
		$query->join('INNER', '#__sportsmanagement_season_team_id AS st2 ON st2.id = pt2.team_id ');
		$query->join('INNER', '#__sportsmanagement_team AS t2 ON t2.id = st2.team_id ');
		$query->join('INNER', '#__sportsmanagement_project AS p ON p.id = pt1.project_id ');
		$query->join('INNER', '#__sportsmanagement_league AS l ON l.id = p.league_id ');
		$query->join('INNER', '#__sportsmanagement_season AS s ON s.id = p.season_id ');
		$query->join('INNER', '#__sportsmanagement_round r ON m.round_id = r.id  ');
		$query->where('(st1.team_id = ' . $teams[1]->team_id . ' AND st2.team_id = ' . $teams[0]->team_id . ')');
		$query->where('p.published = 1');
		$query->where('m.published = 1');
		$query->order('s.name DESC, m.match_date ASC');
		$db->setQuery($query);
		try{
		$result2 = $db->loadObjectList();
}
			catch (Exception $e)
			{
				$app->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), 'notice');
                $app->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__), 'notice');
			}

		foreach ($result1 as $key => $val)
		{
			if (!isset($not_used_project_id[$val->project_id]))
			{
				$not_used_project_id[$val->project_id] = $val->project_id;
			}
		}

		foreach ($result2 as $key => $val)
		{
			if (!isset($not_used_project_id[$val->project_id]))
			{
				$not_used_project_id[$val->project_id] = $val->project_id;
			}
		}

		$not_used_project = implode(",", $not_used_project_id);

		/**
		 * schritt 3
		 * jetzt kann es aber auch vorkommen, das beide mannschaften in einer liga
		 * gespielt haben, aber es dazu noch keine spielpaarungen gibt
		 */

		$query->clear();
		$query->select('pt1.project_id,pt1.id as projectteam1_id');
		$query->select('pt2.id as projectteam2_id');
		$query->select('s.name as seasonname,s.id as season_id');
		$query->select('l.name as leaguename,l.id as league_id');
		$query->select('p.name AS project_name,p.id AS prid');
		$query->select('t1.id as team1_id');
		$query->select('t2.id as team2_id');
		$query->select('CONCAT_WS( \':\', p.id, p.alias ) AS project_slug');
		$query->select('CONCAT_WS( \':\', \'' . Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_HISTORY1') . '\', \'' . Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_HISTORY2') . '\' ) AS round_slug');
		$query->select('CONCAT_WS( \':\', \'' . Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_HISTORY1') . '\', \'' . Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_HISTORY2') . '\' ) AS roundid');
		$query->select('CONCAT_WS( \':\', \'' . Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_HISTORY1') . '\', \'' . Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_HISTORY2') . '\' ) AS roundcode');
		$query->select('CONCAT_WS( \' \', \'0000-00-00\', \'00:00:00\' ) AS match_date');
		$query->from('#__sportsmanagement_project_team AS pt1 ');
		$query->join('INNER', '#__sportsmanagement_season_team_id AS st1 ON st1.id = pt1.team_id ');
		$query->join('INNER', '#__sportsmanagement_team AS t1 ON t1.id = st1.team_id ');
		$query->join('INNER', '#__sportsmanagement_project_team AS pt2 ON pt2.project_id = pt1.project_id ');
		$query->join('INNER', '#__sportsmanagement_season_team_id AS st2 ON st2.id = pt2.team_id ');
		$query->join('INNER', '#__sportsmanagement_team AS t2 ON t2.id = st2.team_id ');
		$query->join('INNER', '#__sportsmanagement_project AS p ON p.id = pt1.project_id AND p.id = pt2.project_id ');
		$query->join('INNER', '#__sportsmanagement_league AS l ON l.id = p.league_id ');
		$query->join('INNER', '#__sportsmanagement_season AS s ON s.id = p.season_id ');
		$query->where('(st1.team_id = ' . $teams[1]->team_id . ' AND st2.team_id = ' . $teams[0]->team_id . ')');
		$query->where('p.published = 1');
		$query->where('p.project_type = ' . $db->Quote('SIMPLE_LEAGUE'));
		$query->where('p.id not in (' . $not_used_project . ')');
		$query->where('pt1.project_id not in (' . $not_used_project . ')');
		$query->where('pt2.project_id not in (' . $not_used_project . ')');
		$query->order('s.name DESC');
		$db->setQuery($query);
		try{
		$result3 = $db->loadObjectList();
}
			catch (Exception $e)
			{
				$app->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), 'notice');
                $app->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__), 'notice');
			}

		$result = array_merge($result1, $result2, $result3);
		$prod   = usort(
			$result, function ($a, $b) {
			$c = strcmp($b->seasonname, $a->seasonname);
			$c .= strcmp($a->project_name, $b->project_name);
			$c .= $a->roundcode - $b->roundcode;

			return $c;
		}
		);

		$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

		return $result;
	}

	/**
	 * sportsmanagementModelNextMatch::getTeamsFromMatches()
	 *
	 * @param   mixed  $games
	 * @param   mixed  $config
	 *
	 * @return
	 */
	function getTeamsFromMatches(&$games, $config = array())
	{
		$app    = Factory::getApplication();
		$option = Factory::getApplication()->input->getCmd('option');
		/**
		 *
		 * Create a new query object.
		 */
		$db    = sportsmanagementHelper::getDBConnection(true, self::$cfg_which_database);
		$query = $db->getQuery(true);

		$teams = Array();

		if (!count($games))
		{
			return $teams;
		}

		foreach ($games as $m)
		{
			$teamsId[] = $m->projectteam1_id;
			$teamsId[] = $m->projectteam2_id;
		}

		$listTeamId = implode(",", array_unique($teamsId));

		/**
		 *
		 * Select some fields
		 */
		$query->select('t.id, t.name');
		$query->select('pt.id as ptid');
		$query->from('#__sportsmanagement_project_team AS pt ');
		$query->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.id = pt.team_id ');
		$query->join('INNER', '#__sportsmanagement_team AS t ON t.id = st.team_id ');
		$query->join('INNER', '#__sportsmanagement_club as c ON c.id = t.club_id ');

		switch ($config['show_picture'])
		{
			case 'team_picture':
				$query->select('t.picture AS picture');
				break;
			case 'projectteam_picture':
				$query->select('st.picture AS picture');
				break;
			case 'logo_small':
				$query->select('c.logo_small AS picture');
				break;
			case 'logo_middle':
				$query->select('c.logo_middle AS picture');
				break;
			case 'logo_big':
				$query->select('c.logo_big AS picture');
				break;
		}

		$query->where('pt.id IN (' . $listTeamId . ')');

		try
		{
			$db->setQuery($query);
			$result = $db->loadObjectList();

			foreach ($result as $r)
			{
				$teams[$r->ptid] = $r;
			}
		}
		catch (Exception $e)
		{
			$app->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage()), 'error');
			$teams = false;
		}

		$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

		return $teams;
	}

	/**
	 * Calculates chances between 2 team
	 * Code is from LMO, all credits go to the LMO developers
	 *
	 * @return array
	 */
	function getChances()
	{
		$home = self::getHomeRanked();
		$away = self::getAwayRanked();

		if ((($home->cnt_matches) > 0) && (($away->cnt_matches) > 0))
		{
			$won1          = $home->cnt_won;
			$won2          = $away->cnt_won;
			$loss1         = $home->cnt_lost;
			$loss2         = $away->cnt_lost;
			$matches1      = $home->cnt_matches;
			$matches2      = $away->cnt_matches;
			$goalsfor1     = $home->sum_team1_result;
			$goalsfor2     = $away->sum_team1_result;
			$goalsagainst1 = $home->sum_team2_result;
			$goalsagainst2 = $away->sum_team2_result;

			$ax = (100 * $won1 / $matches1) + (100 * $loss2 / $matches2);
			$bx = (100 * $won2 / $matches2) + (100 * $loss1 / $matches1);
			$cx = ($goalsfor1 / $matches1) + ($goalsagainst2 / $matches2);
			$dx = ($goalsfor2 / $matches2) + ($goalsagainst1 / $matches1);
			$ex = $ax + $bx;
			$fx = $cx + $dx;

			if (isset($ex) && ($ex > 0) && isset($fx) && ($fx > 0))
			{
				$ax = round(10000 * $ax / $ex);
				$bx = round(10000 * $bx / $ex);
				$cx = round(10000 * $cx / $fx);
				$dx = round(10000 * $dx / $fx);

				$chg1   = number_format((($ax + $cx) / 200), 2, ",", ".");
				$chg2   = number_format((($bx + $dx) / 200), 2, ",", ".");
				$result = array($chg1, $chg2);

				return $result;
			}
		}
	}

	/**
	 * sportsmanagementModelNextMatch::getHomeRanked()
	 *
	 * @return
	 */
	function getHomeRanked()
	{
		$match    = self::getMatch();
		$rankings = self::_getRanking();

		foreach ($rankings as $ptid => $team)
		{
			if ($ptid == $match->projectteam1_id)
			{
				return $team;
			}
		}

		return new JSMRankingTeamClass(0);
	}

	/**
	 * sportsmanagementModelNextMatch::_getRanking()
	 *
	 * @return
	 */
	function _getRanking()
	{
		if (empty($this->ranking))
		{
			$project  = sportsmanagementModelProject::getProject(self::$cfg_which_database);
			$division = $this->divisionid;
			$ranking  = JSMRanking::getInstance($project, self::$cfg_which_database);
			$ranking->setProjectId($project->id, self::$cfg_which_database);
			$this->ranking = $ranking->getRanking(0, sportsmanagementModelProject::getCurrentRound(null, self::$cfg_which_database), $division, self::$cfg_which_database);
		}

		return $this->ranking;
	}

	/**
	 * sportsmanagementModelNextMatch::getAwayRanked()
	 *
	 * @return
	 */
	function getAwayRanked()
	{
		$match    = self::getMatch();
		$rankings = self::_getRanking();

		foreach ($rankings as $ptid => $team)
		{
			if ($ptid == $match->projectteam2_id)
			{
				return $team;
			}
		}

		return new JSMRankingTeamClass(0);
	}

	/**
	 * get Previous X games of each team
	 *
	 * @return array
	 */
	function getPreviousX($config = array())
	{
		if (!$this->_match)
		{
			return false;
		}

		$games                                 = array();
		$games[$this->_match->projectteam1_id] = self::_getTeamPreviousX($this->_match->roundcode, $this->_match->projectteam1_id, $config);
		$games[$this->_match->projectteam2_id] = self::_getTeamPreviousX($this->_match->roundcode, $this->_match->projectteam2_id, $config);

		return $games;
	}

	/**
	 * returns last X games
	 *
	 * @param   int  $current_roundcode
	 * @param   int  $ptid  project team id
	 *
	 * @return array
	 */
	function _getTeamPreviousX($current_roundcode, $ptid, $config = array())
	{
		$app    = Factory::getApplication();
		$option = Factory::getApplication()->input->getCmd('option');
		/**
		 *
		 * Create a new query object.
		 */
		$db    = sportsmanagementHelper::getDBConnection(true, self::$cfg_which_database);
		$query = $db->getQuery(true);

		$config = sportsmanagementModelProject::getTemplateConfig('nextmatch', self::$cfg_which_database);
		$nblast = $config['nb_previous'];

		/**
		 *
		 * Select some fields
		 */
		$query->select('m.id,m.match_date,m.team1_result,m.team2_result,m.show_report,m.projectteam1_id,m.projectteam2_id');
		$query->select('r.project_id, r.id AS roundid, r.roundcode ');
		$query->select('CONCAT_WS(\':\',m.id,CONCAT_WS("_",t1.alias,t2.alias)) AS match_slug ');
		$query->select('CONCAT_WS( \':\', p.id, p.alias ) AS project_slug');
		$query->select('CONCAT_WS( \':\', r.id, r.alias ) AS round_slug');
		$query->from('#__sportsmanagement_match AS m ');
		$query->join('INNER', '#__sportsmanagement_round AS r ON r.id = m.round_id ');
		$query->join('INNER', '#__sportsmanagement_project AS p ON p.id = r.project_id ');
		$query->join('INNER', '#__sportsmanagement_project_team pt1 ON pt1.id = m.projectteam1_id  ');
		$query->join('INNER', '#__sportsmanagement_season_team_id AS st1 ON st1.id = pt1.team_id ');
		$query->join('INNER', '#__sportsmanagement_team AS t1 ON t1.id = st1.team_id ');
		$query->join('INNER', '#__sportsmanagement_club as c1 ON c1.id = t1.club_id ');

		$query->join('INNER', '#__sportsmanagement_project_team pt2 ON pt2.id = m.projectteam2_id  ');
		$query->join('INNER', '#__sportsmanagement_season_team_id AS st2 ON st2.id = pt2.team_id ');
		$query->join('INNER', '#__sportsmanagement_team AS t2 ON t2.id = st2.team_id ');
		$query->join('INNER', '#__sportsmanagement_club as c2 ON c2.id = t2.club_id ');

		switch ($config['show_picture'])
		{
			case 'team_picture':
				$query->select('t1.picture AS home_picture');
				$query->select('t2.picture AS away_picture');
				break;
			case 'projectteam_picture':
				$query->select('st1.picture AS home_picture');
				$query->select('st2.picture AS away_picture');
				break;
			case 'logo_small':
				$query->select('c1.logo_small AS home_picture');
				$query->select('c2.logo_small AS away_picture');
				break;
			case 'logo_middle':
				$query->select('c1.logo_middle AS home_picture');
				$query->select('c2.logo_middle AS away_picture');
				break;
			case 'logo_big':
				$query->select('c1.logo_big AS home_picture');
				$query->select('c2.logo_big AS away_picture');
				break;
		}

		$query->where('r.roundcode < ' . $current_roundcode);
		$query->where('(m.projectteam1_id = ' . $ptid . ' OR m.projectteam2_id = ' . $ptid . ')');
		$query->where('m.published = 1');
		$query->order('r.roundcode DESC');

		try
		{
			$db->setQuery($query, 0, $nblast);
			$res = $db->loadObjectList();

			if ($res)
			{
				$res = array_reverse($res);
			}
		}
		catch (Exception $e)
		{
			$app->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage()), 'error');
			$res = false;
		}

		$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

		return $res;
	}
}
