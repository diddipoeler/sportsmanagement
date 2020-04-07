<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       roster.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage roster
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;

/**
 * sportsmanagementModelRoster
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementModelRoster extends JSMModelLegacy
{
	static $projectid = 0;

	static $projectteamid = 0;

	static $teamid = 0;

	static $seasonid = 0;

	static $projectteam = null;

	static $team = null;

	/**
	 * caching for team in out stats
	 *
	 * @var array
	 */
	var $_teaminout = null;

	/**
	 * caching players
	 *
	 * @var array
	 */
	static $_players = null;

	static $cfg_which_database = 0;

	/**
	 * sportsmanagementModelRoster::__construct()
	 *
	 * @return
	 */
	function __construct()
	{
		parent::__construct();
		self::$projectid = $this->jsmjinput->get('p', 0, 'INT');
		self::$teamid = $this->jsmjinput->get('tid', 0, 'INT');
		self::$projectteamid = $this->jsmjinput->get('ptid', 0, 'INT');
		sportsmanagementModelProject::$projectid = self::$projectid;
		self::$cfg_which_database = $this->jsmjinput->get('cfg_which_database', 0, 'INT');
		sportsmanagementModelProject::$cfg_which_database = self::$cfg_which_database;
		sportsmanagementModelProject::setProjectID(self::$projectid, self::$cfg_which_database);
		self::getProjectTeam();
	}


	/**
	 * sportsmanagementModelRoster::getProjectTeam()
	 *
	 * @return
	 */
	public static function getProjectTeam($team_picture_which = 'pt' )
	{
		  $app = Factory::getApplication();
		$option = $app->input->getCmd('option');

		  // Create a new query object.
		$db = sportsmanagementHelper::getDBConnection(true, self::$cfg_which_database);
		$query = $db->getQuery(true);
		$starttime = microtime();

		if (is_null(self::$projectteam))
		{
			if ((int) self::$projectteamid)
			{
				$query = $db->getQuery(true);
					  $query->clear();
				$query->select('pt.project_id,pt.id,st.team_id as season_team_id');
				$query->select("" . $team_picture_which . ".picture as picture");
					 $query->from('#__sportsmanagement_project_team AS pt');
						$query->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.id = pt.team_id');
				$query->join('INNER', '#__sportsmanagement_team AS t ON st.team_id = t.id ');
				$query->where('pt.id = ' . (int) self::$projectteamid);
			}
			else
			{
				if (!self::$teamid)
				{
					Log::add(Text::_('COM_SPORTSMANAGEMENT_ROSTER_ERROR_TEAM_ID'), Log::WARNING, 'jsmerror');

					return false;
				}

				if (!self::$projectid)
				{
					Log::add(Text::_('COM_SPORTSMANAGEMENT_ROSTER_ERROR_PROJECT_ID'), Log::WARNING, 'jsmerror');

					return false;
				}

							  $query = $db->getQuery(true);
				$query->clear();
				$query->select('pt.project_id,pt.id,st.team_id as season_team_id');
				$query->select("" . $team_picture_which . ".picture as picture");
					 $query->from('#__sportsmanagement_project_team AS pt');
						$query->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.id = pt.team_id');
						$query->join('INNER', '#__sportsmanagement_team AS t ON st.team_id = t.id ');
				$query->where('st.team_id = ' . (int) self::$teamid);
				$query->where('pt.project_id = ' . (int) self::$projectid);
			}

			$db->setQuery($query);

				 self::$projectteam = $db->loadObject();

			if (self::$projectteamid)
			{
				self::$projectid = self::$projectteam->project_id; // If only ttid was set
				self::$teamid = self::$projectteam->season_team_id;
			}
		}

			return self::$projectteam;
	}

	/**
	 * sportsmanagementModelRoster::getTeam()
	 *
	 * @return
	 */
	public static function getTeam()
	{
		  $app = Factory::getApplication();
		$option = $app->input->getCmd('option');

		  // Create a new query object.
		$db = sportsmanagementHelper::getDBConnection(true, self::$cfg_which_database);
		$query = $db->getQuery(true);
		$starttime = microtime();

		if (is_null(self::$team))
		{
			if (!self::$teamid)
			{
				$this->setError(Text::_('COM_SPORTSMANAGEMENT_ROSTER_ERROR_TEAM_ID'));

				return false;
			}

			if (!self::$projectid)
			{
				$this->setError(Text::_('COM_SPORTSMANAGEMENT_ROSTER_ERROR_PROJECT_ID'));

				return false;
			}

				$query->select('t.*');
			$query->select('CONCAT_WS(\':\',t.id,t.alias) AS slug');
			$query->from('#__sportsmanagement_team AS t');
			$query->where('t.id = ' . (int) self::$teamid);
			$db->setQuery($query);

			self::$team = $db->loadObject();
		}

			return self::$team;
	}


	/**
	 * sportsmanagementModelRoster::getTeamPlayers()
	 *
	 * @param   integer $persontype
	 * @return
	 */
	public static function getTeamPlayers($persontype = 1)
	{
		  $app = Factory::getApplication();
		$option = $app->input->getCmd('option');

		  // Create a new query object.
		$db = sportsmanagementHelper::getDBConnection(true, self::$cfg_which_database);
		$query = $db->getQuery(true);
		$starttime = microtime();

			  $projectteam = self::getprojectteam();

			  $query->select('pr.firstname,pr.nickname,pr.lastname,pr.country,pr.birthday,pr.deathday,pr.id AS pid,pr.id AS person_id,pr.picture AS ppic');
		$query->select('pr.suspension AS suspension,pr.away AS away,pr.injury AS injury,pr.id AS pid,pr.picture AS ppic,CONCAT_WS(\':\',pr.id,pr.alias) AS person_slug');
		$query->select('tp.id AS playerid,tp.id AS season_team_person_id,tp.jerseynumber AS position_number,tp.notes AS description,tp.market_value AS market_value,tp.picture');
		$query->select('st.id AS season_team_id');
		$query->select('pt.project_id AS project_id');
		$query->select('pt.id AS projectteam_id');
		$query->select('pos.name AS position');
		$query->select('ppos.position_id,ppos.id as pposid');
		$query->select('CONCAT_WS(\':\',pro.id,pro.alias) AS project_slug');
		$query->select('CONCAT_WS(\':\',t.id,t.alias) AS team_slug');
		$query->from('#__sportsmanagement_season_team_person_id AS tp ');
		$query->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id and st.season_id = tp.season_id');
		$query->join('INNER', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
		$query->join('INNER', '#__sportsmanagement_person AS pr ON tp.person_id = pr.id');
		$query->join('INNER', '#__sportsmanagement_project AS pro ON pro.id = pt.project_id and pro.season_id = st.season_id');
		$query->join('INNER', '#__sportsmanagement_team AS t ON t.id = st.team_id');
		$query->join('LEFT', '#__sportsmanagement_person_project_position AS perpos ON perpos.project_id = pro.id AND perpos.person_id = pr.id');
		$query->join('LEFT', '#__sportsmanagement_project_position AS ppos ON ppos.id = perpos.project_position_id');

		switch ($persontype)
		{
			case 1:
				$query->join('LEFT', '#__sportsmanagement_position AS pos ON pos.id = ppos.position_id');
					break;
			case 2:
					$query->select('posparent.name AS parentname');
					$query->join('LEFT', '#__sportsmanagement_position AS pos ON pos.id = ppos.position_id');
					$query->join('LEFT', '#__sportsmanagement_position AS posparent ON pos.parent_id = posparent.id');
					break;
		}

			  $query->where('pt.id = ' . $projectteam->id);
		$query->where('pr.published = 1');
		$query->where('tp.published = 1');
		$query->where('perpos.published = 1');
		$query->where('tp.persontype = ' . $persontype);
		$query->where('pos.persontype = ' . $persontype);
		$query->where('tp.season_id = ' . self::$seasonid);
		$query->where('pt.project_id = ' . self::$projectid);
		$query->where('pro.id = ' . self::$projectid);
		$query->order('pos.ordering, ppos.position_id, tp.ordering, tp.jerseynumber, pr.lastname, pr.firstname');

		try
		{
			$db->setQuery($query);
			self::$_players = $db->loadObjectList();
		}
		catch (Exception $e)
		{
			 Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . ' fehlertext<br><pre>' . $e->getMessage() . '</pre>'), Log::ERROR, 'jsmerror');
			 Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . ' query<br><pre>' . $query->dump() . '</pre>'), Log::ERROR, 'jsmerror');
		}

		switch ($persontype)
		{
			case 1:
				$bypos = array();

				foreach (self::$_players as $player)
				{
					if (isset($bypos[$player->position_id]))
					{
						 $bypos[$player->position_id][] = $player;
					}
					else
					{
						  $bypos[$player->position_id] = array($player);
					}
				}

return $bypos;
			break;
			case 2:
			return self::$_players;
			break;
		}

	}


	/**
	 * sportsmanagementModelRoster::getPositionEventTypes()
	 *
	 * @param   integer $positionId
	 * @return
	 */
	public static function getPositionEventTypes($positionId=0)
	{
		$app = Factory::getApplication();
		$option = $app->input->getCmd('option');

		  // Create a new query object.
		$db = sportsmanagementHelper::getDBConnection(true, self::$cfg_which_database);
		$query = $db->getQuery(true);
		$starttime = microtime();

					  $result = array();

			  $query->select('pet.*');
		$query->select('ppos.id AS pposid,ppos.position_id');
		$query->select('et.name AS name,et.icon AS icon');
		$query->from('#__sportsmanagement_position_eventtype AS pet');
		$query->join('INNER', '#__sportsmanagement_eventtype AS et ON et.id = pet.eventtype_id');
		$query->join('INNER', '#__sportsmanagement_project_position AS ppos ON ppos.position_id = pet.position_id');
		$query->where('ppos.project_id = ' . self::$projectid);
		$query->where('et.published = 1');

		if ($positionId > 0)
		{
			 $query->where('pet.position_id = ' . (int) $positionId);
		}

		$query->order('pet.ordering, et.ordering');
		$db->setQuery($query);

			  $result = $db->loadObjectList();

		if ($result)
		{
			if ($positionId)
			{
				return $result;
			}
			else
			{
				$posEvents = array();

				foreach ($result as $r)
				{
					$posEvents[$r->position_id][$r->eventtype_id] = $r;
				}

				return ($posEvents);
			}
		}

		return array();
	}

	/**
	 * sportsmanagementModelRoster::getPlayerEventStats()
	 *
	 * @return
	 */
	public static function getPlayerEventStats($dart=false,$sumeventid=false)
	{
		$app = Factory::getApplication();
		$playerstats = array();
		$rows = self::getTeamPlayers();

		if (!empty($rows))
		{
			$positioneventtypes = self::getPositionEventTypes();

			// Init
			foreach ($rows as $position => $players)
			{
				foreach ($players as $player)
				{
					$playerstats[$player->pid] = array();
				}
			}

			foreach ($positioneventtypes as $position => $eventtypes)
			{
				foreach ($eventtypes as $eventtype)
				{
					$teamstats = self::getTeamEventStat($eventtype->eventtype_id, $dart, $sumeventid);

					if (isset($rows[$position]))
					{
						foreach ($rows[$position] as $player)
						{
							if ($dart)
							{
								if ($sumeventid)
								{
									$playerstats[$player->pid][$eventtype->eventtype_id] = (isset($teamstats[$player->pid]) ? $teamstats[$player->pid]->total : 0);
								}
								else
								{
									$playerstats[$eventtype->eventtype_id] = $teamstats;
								}
							}
							else
							{
								$playerstats[$player->pid][$eventtype->eventtype_id] = (isset($teamstats[$player->pid]) ? $teamstats[$player->pid]->total : 0);
							}
						}
					}
				}
			}
		}

		return $playerstats;
	}



	/**
	 * sportsmanagementModelRoster::getTeamEventStat()
	 *
	 * @param   mixed $eventtype_id
	 * @return
	 */
	public static function getTeamEventStat($eventtype_id=0,$dart=false,$sumeventid=false)
	{
		$app = Factory::getApplication();
		  $option = $app->input->getCmd('option');

		// Create a new query object.
		  $db = sportsmanagementHelper::getDBConnection(true, self::$cfg_which_database);
		  $query = $db->getQuery(true);
		  $starttime = microtime();

			 $projectteam = self::getprojectteam();

		if ($dart)
		{
			if ($sumeventid)
			{
				$query->select('COUNT(me.event_type_id) as total');
			}
			else
			{
				$query->select('me.event_sum as total,me.event_type_id as event_type_id');
			}
		}
		else
		{
			  $query->select('SUM(me.event_sum) as total');
		}

		$query->select('tp.person_id');
		$query->from('#__sportsmanagement_match_event AS me');
		  $query->join('INNER', '#__sportsmanagement_season_team_person_id AS tp ON me.teamplayer_id = tp.id');
		  $query->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id and st.season_id = tp.season_id');

		$query->join('INNER', '#__sportsmanagement_match AS ma ON ma.id = me.match_id');
		$query->join('INNER', '#__sportsmanagement_round AS r ON r.id = ma.round_id');

				$query->join('INNER', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id and pt.project_id = r.project_id');
		  $query->join('INNER', '#__sportsmanagement_project AS pro ON pro.id = pt.project_id and pro.season_id = st.season_id');
		  $query->where('me.event_type_id = ' . $eventtype_id);
		  $query->where('pt.id = ' . $projectteam->id);
		  $query->where('pt.project_id = ' . self::$projectid);
		$query->where('r.project_id = ' . self::$projectid);
		  $query->where('pro.id = ' . self::$projectid);

		if ($dart)
		{
			if ($sumeventid)
			{
				$query->group('tp.person_id');
			}
		}
		else
		{
			 $query->group('tp.person_id');
		}

			  $db->setQuery($query);

		if ($dart)
		{
			if ($sumeventid)
			{
				$result = $db->loadObjectList('person_id');
			}
			else
			{
				$result = $db->loadObjectList();
			}
		}
		else
		{
			$result = $db->loadObjectList('person_id');
		}

		return $result;
	}


	/**
	 * sportsmanagementModelRoster::getTeamPlayer()
	 *
	 * @param   mixed $round_id
	 * @param   mixed $player_id
	 * @return
	 */
	function getTeamPlayer($round_id,$player_id)
	{
		  $app = Factory::getApplication();
		  $option = $app->input->getCmd('option');

		// Create a new query object.
		  $db = sportsmanagementHelper::getDBConnection(true, self::$cfg_which_database);
		  $query = $db->getQuery(true);
		  $starttime = microtime();

			   $query->select('pr.injury AS injury,pr.suspension AS suspension,pr.away AS away');
		  $query->select('ppos.id As pposid');
		  $query->select('pos.id AS position_id');
		  $query->select('stp.picture');
		  $query->from('#__sportsmanagement_person AS pr');
		  $query->join('INNER', '#__sportsmanagement_season_team_person_id AS stp ON stp.person_id = pr.id');
		  $query->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.team_id = stp.team_id');
		  $query->join('INNER', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');

			   $query->join('INNER', '#__sportsmanagement_round AS r ON r.project_id = pt.project_id');
		  $query->join('INNER', '#__sportsmanagement_project_position AS ppos ON ppos.id = stp.project_position_id');
		  $query->join('INNER', '#__sportsmanagement_position AS pos ON pos.id = ppos.position_id');

			   $query->where('r.id = ' . $round_id);
		  $query->where('stp.id = ' . $player_id);
		  $query->where('pr.published = 1');
		  $query->where('stp.published = 1');

							$db->setQuery($query);

			  $rows = $db->loadObjectList();

		return $rows;
	}

	/**
	 * sportsmanagementModelRoster::getRosterStats()
	 *
	 * @return
	 */
	public static function getRosterStats()
	{
		$app = Factory::getApplication();
		$stats = sportsmanagementModelProject::getProjectStats();
		$projectteam = self::getprojectteam();

			 $result = array();

		foreach ($stats as $pos => $pos_stats)
		{
			foreach ($pos_stats as $k => $stat)
			{
				// $result[$pos][$stat->id] = $stat->getRosterStats($projectteam->team_id,$projectteam->project_id, $pos);
				$result[$pos][$stat->id] = $stat->getRosterStats($projectteam->season_team_id, $projectteam->project_id, $pos);
			}
		}

		return $result;
	}

	/**
	 * sportsmanagementModelRoster::getLastSeasonDate()
	 *
	 * @return
	 */
	public static function getLastSeasonDate()
	{
		$app = Factory::getApplication();
		$option = $app->input->getCmd('option');

		// Create a new query object.
		$db = sportsmanagementHelper::getDBConnection(true, self::$cfg_which_database);
		$query = $db->getQuery(true);
		$starttime = microtime();
		$result = '';
		$query->select('max(round_date_last)');
		$query->from('#__sportsmanagement_round ');
		$query->where('project_id =' . (int) self::$projectid);

						  $db->setQuery($query);

		try
		{
			$db->setQuery($query);
			$result = $db->loadResult();
		}
		catch (Exception $e)
		{
			echo $e->getMessage();
		}

			return $result;

	}

}
