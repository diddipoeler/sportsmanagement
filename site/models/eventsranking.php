<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage eventsranking
 * @file       eventsranking.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * sportsmanagementModelEventsRanking
 *
 * @package
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class sportsmanagementModelEventsRanking extends BaseDatabaseModel
{
	static $projectid = 0;
	static $divisionid = 0;
	static $teamid = 0;
	static $eventid = 0;
	static $matchid = 0;
	static $limit = 20;
	static $limitstart = 0;
	static $cfg_which_database = 0;

	/**
	 * sportsmanagementModelEventsRanking::__construct()
	 *
	 * @return void
	 */
	function __construct()
	{
		$app = Factory::getApplication();
		$jinput = $app->input;

		parent::__construct();
		self::$projectid  = $jinput->get('p', 0, 'INT');
		self::$divisionid = $jinput->get('division', 0, 'INT');
		self::$teamid     = $jinput->get('tid', 0, 'INT');
		self::$matchid = $jinput->get('mid', 0, 'INT');
		self::$eventid = (is_array($jinput->get('evid'))) ? implode(",", array_map('intval', $jinput->get('evid'))) : (int) $jinput->get('evid');

		$config           = sportsmanagementModelProject::getTemplateConfig($this->getName());
		$defaultLimit     = self::$eventid != 0 ? $config['max_events'] : $config['count_events'];
		self::$limit      = $jinput->getInt('limit', $defaultLimit);
		self::$limitstart = $jinput->getInt('start', 0);
		self::setOrder($jinput->getVar('order', 'desc'));
		self::$cfg_which_database                = $jinput->getInt('cfg_which_database', 0);
		sportsmanagementModelProject::$projectid = self::$projectid;
	}

	/**
	 * set order (asc or desc)
	 *
	 * @param   string  $order
	 *
	 * @return string order
	 */
	function setOrder($order)
	{
		if (strcasecmp($order, 'asc') === 0 || strcasecmp($order, 'desc') === 0)
		{
			$this->order = strtolower($order);
		}

		return $this->order;
	}

	/**
	 * sportsmanagementModelEventsRanking::getTeamId()
	 *
	 * @return
	 */
	function getTeamId()
	{
		return self::$teamid;
	}

	/**
	 * Method to get a pagination object for the events
	 *
	 * @access public
	 * @return integer
	 */
	function getPagination()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_pagination))
		{
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination(self::getTotal(), self::getLimitStart(), self::getLimit());
		}

		return $this->_pagination;
	}

	/**
	 * sportsmanagementModelEventsRanking::getTotal()
	 *
	 * @return
	 */
	function getTotal()
	{
		$app    = Factory::getApplication();
		$option = Factory::getApplication()->input->getCmd('option');
		$db    = sportsmanagementHelper::getDBConnection(true, self::$cfg_which_database);
		$query = $db->getQuery(true);

		if (empty($this->_total))
		{
			/** Make sure the same restrictions are used here as in statistics/basic.php in getPlayersRanking() */
			$query->select('COUNT(DISTINCT(teamplayer_id)) as count_player');
			$query->from('#__sportsmanagement_match_event AS me ');
			$query->join('INNER', '#__sportsmanagement_season_team_person_id AS tp ON me.teamplayer_id = tp.id');
			$query->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id and st.season_id = tp.season_id      ');
			$query->join('INNER', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
          
          $query->join('INNER', '#__sportsmanagement_project AS p ON p.id = pt.project_id and p.season_id = st.season_id  ');
          
			$query->join('INNER', '#__sportsmanagement_team AS t ON t.id = st.team_id');
			$query->join('INNER', '#__sportsmanagement_person AS pl ON tp.person_id = pl.id');

			$query->where('me.event_type_id IN(' . self::$eventid . ')');
			$query->where('pl.published = 1');

			if (self::$projectid > 0)
			{
				$query->where('pt.project_id = ' . self::$projectid);
                $query->where('p.id = ' . self::$projectid);
			}

			if (self::$divisionid > 0)
			{
				$query->where('pt.division_id = ' . self::$divisionid);
			}

			if (self::$teamid > 0)
			{
				$query->where('st.team_id = ' . self::$teamid);
			}

			if (self::$matchid > 0)
			{
				$query->where('me.match_id = ' . self::$matchid);
			}

			$db->setQuery($query);
            
            //$app->enqueueMessage(Text::_(__METHOD__ . ' ' . ' <pre>' . print_r($query->dump(),true).  '</pre>'  ), 'error');

			try
			{
				$this->_total = $db->loadResult();
			}
			catch (Exception $e)
			{
				$app->enqueueMessage(Text::_(__METHOD__ . ' ' . ' ' . $e->getMessage()), 'error');

				return false;
			}
		}
		
		$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
		return $this->_total;
	}

	/**
	 * sportsmanagementModelEventsRanking::getLimitStart()
	 *
	 * @return
	 */
	public static function getLimitStart()
	{
		return self::$limitstart;
	}

	/**
	 * sportsmanagementModelEventsRanking::getLimit()
	 *
	 * @return
	 */
	public static function getLimit()
	{
		return self::$limit;
	}


	/**
	 * sportsmanagementModelEventsRanking::getEventRankings()
	 * 
	 * @param integer $limit
	 * @param integer $limitstart
	 * @param mixed $order
	 * @param bool $dart
	 * @param integer $sports_type_id
	 * @return
	 */
	function getEventRankings($limit = 0, $limitstart = 0, $order = null, $dart = false, $sports_type_id = 0)
	{
		$order      = ($order ? $order : $this->order);
		$eventtypes = self::getEventTypes($sports_type_id);

		if (array_keys($eventtypes))
		{
			foreach (array_keys($eventtypes) AS $eventkey)
			{
				$eventrankings[$eventkey] = self::_getEventsRanking($eventkey, $order, $limit, $limitstart, $dart, $eventtypes[$eventkey]->directionspoint, $eventtypes[$eventkey]->directionscounter, $eventtypes[$eventkey]->directionspointpos);
			}
		}

		if (!isset($eventrankings))
		{
			return null;
		}

		return $eventrankings;
	}


	/**
	 * sportsmanagementModelEventsRanking::getEventTypes()
	 * 
	 * @param integer $sports_type_id
	 * @return
	 */
	public static function getEventTypes($sports_type_id = 0)
	{
		$app    = Factory::getApplication();
		$option = Factory::getApplication()->input->getCmd('option');
		$db    = sportsmanagementHelper::getDBConnection(true, self::$cfg_which_database);
		$query = $db->getQuery(true);
		$query->select('et.id as etid,me.event_type_id as id,et.*');
		$query->select('CONCAT_WS( \':\', et.id, et.alias ) AS event_slug');
		$query->from('#__sportsmanagement_eventtype as et');
		$query->join('INNER', '#__sportsmanagement_match_event as me ON et.id = me.event_type_id');
		$query->join('INNER', '#__sportsmanagement_match as m ON m.id = me.match_id');
		$query->join('INNER', '#__sportsmanagement_round as r ON m.round_id = r.id');

		if (self::$projectid)
		{
			$query->where('r.project_id IN (' . self::$projectid . ')');
		}

		if (self::$eventid != 0)
		{
			$query->where("me.event_type_id IN (" . self::$eventid . ")");
		}
        
        if ( $sports_type_id )
        {
        $query->where('et.sports_type_id = ' . $sports_type_id);
        }

		$query->order('et.ordering');

		$db->setQuery($query);

		try
		{
			$result = $db->loadObjectList('etid');
			$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
			return $result;
		}
		catch (Exception $e)
		{
			$app->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage()), 'error');
			$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
			return false;
		}
	}

	/**
	 * sportsmanagementModelEventsRanking::_getEventsRanking()
	 *
	 * @param   mixed    $eventtype_id
	 * @param   string   $order
	 * @param   integer  $limit
	 * @param   integer  $limitstart
	 * @param   bool     $dart
	 * @param   string   $directionspoint
	 * @param   string   $directionscounter
	 * @param   integer  $directionspointpos
	 *
	 * @return
	 */
	public static function _getEventsRanking($eventtype_id, $order = 'DESC', $limit = 10, $limitstart = 0, $dart = false, $directionspoint = 'DESC', $directionscounter = 'DESC', $directionspointpos = 1)
	{
		$app    = Factory::getApplication();
		$option = Factory::getApplication()->input->getCmd('option');
		$db    = sportsmanagementHelper::getDBConnection(true, self::$cfg_which_database);
		$query = $db->getQuery(true);

		if ($dart)
		{
			switch ($directionspointpos)
			{
				case 1:
					$query->select('me.event_sum as p, COUNT(me.event_sum) as zaehler');
					break;
				case 2:
					$query->select('me.event_sum as zaehler, COUNT(me.event_sum) as p');
					break;
			}

			$query->select('pl.firstname AS fname,pl.nickname AS nname,pl.lastname AS lname,pl.country,pl.id AS pid,pl.picture,tp.picture AS teamplayerpic,t.id AS tid,t.name AS tname');
		}
		else
		{
			$query->select('SUM(me.event_sum) as p,pl.firstname AS fname,pl.nickname AS nname,pl.lastname AS lname,pl.country,pl.id AS pid,pl.picture,tp.picture AS teamplayerpic,t.id AS tid,t.name AS tname');
		}

		$query->select('CONCAT_WS( \':\', pl.id, pl.alias ) AS person_slug');
		$query->select('CONCAT_WS( \':\', t.id, t.alias ) AS team_slug');
		$query->select('CONCAT_WS( \':\', pt.id, t.alias ) AS projectteam_slug');
		$query->from('#__sportsmanagement_match_event AS me ');
		$query->join('INNER', '#__sportsmanagement_season_team_person_id AS tp ON me.teamplayer_id = tp.id');
		$query->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id and st.season_id = tp.season_id');
		$query->join('INNER', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
		$query->join('INNER', '#__sportsmanagement_project AS p ON pt.project_id = p.id and p.season_id = st.season_id');
		$query->join('INNER', '#__sportsmanagement_team AS t ON t.id = st.team_id');
		$query->join('INNER', '#__sportsmanagement_person AS pl ON tp.person_id = pl.id');

		$query->where('me.event_type_id = ' . $eventtype_id);
		$query->where('pl.published = 1');

		if (self::$projectid)
		{
			$query->where('pt.project_id IN (' . self::$projectid . ')');
			$query->where('p.id IN (' . self::$projectid . ')');
		}

		if (self::$divisionid > 0)
		{
			$query->where('pt.division_id = ' . self::$divisionid);
		}

		if (self::$teamid > 0)
		{
			$query->where('st.team_id = ' . self::$teamid);
			$query->where('tp.team_id = ' . self::$teamid);
		}

		if (self::$matchid > 0)
		{
			$query->where('me.match_id = ' . self::$matchid);
		}

		if ($dart)
		{
			$query->group('me.event_sum,me.teamplayer_id');
			$query->order('p ' . $directionspoint . ' , zaehler ' . $directionscounter);
		}
		else
		{
			$query->group('me.teamplayer_id');
			$query->order('p ' . $directionspoint);
		}

		try
		{
			$db->setQuery($query, self::getlimitStart(), self::getlimit());
			$rows = $db->loadObjectList();
		}
		catch (Exception $e)
		{
			$app->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage()), 'error');
			$app->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $query->dump()), 'error');

			return false;
		}

		/** get ranks */
		$previousval = 0;
		$currentrank = 1 + $limitstart;

		foreach ($rows as $k => $row)
		{
			$rows[$k]->rank = ($row->p == $previousval) ? $currentrank : $k + 1 + $limitstart;
			$previousval    = $row->p;
			$currentrank    = $row->rank;
		}
		
		$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
		return $rows;
	}

}
