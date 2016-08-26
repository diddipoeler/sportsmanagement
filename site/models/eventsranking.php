<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license                This file is part of SportsManagement.
*
* SportsManagement is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* SportsManagement is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with SportsManagement.  If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von SportsManagement.
*
* SportsManagement ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es nützlich sein wird, aber
* OHNE JEDE GEWÄHELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

/**
 * sportsmanagementModelEventsRanking
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class sportsmanagementModelEventsRanking extends JModelLegacy
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
	   // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        
		parent::__construct();
		self::$projectid = $jinput->get('p',0,'INT');
		self::$divisionid = $jinput->get( 'division', 0,'INT' );
		self::$teamid = $jinput->get( 'tid', 0 ,'INT');
		self::setEventid($jinput->get('evid', '0','INT'));
		self::$matchid = $jinput->get('mid',0,'INT');
		$config = sportsmanagementModelProject::getTemplateConfig($this->getName());
		$defaultLimit = self::$eventid != 0 ? $config['max_events'] : $config['count_events'];
		self::$limit = $jinput->getInt('limit',$defaultLimit);
		self::$limitstart = $jinput->getInt('limitstart',0);
		self::setOrder($jinput->getVar('order','desc'));
        
        self::$cfg_which_database = $jinput->getInt( 'cfg_which_database', 0 );
        sportsmanagementModelProject::$projectid = self::$projectid;
         
	}

	/**
	 * sportsmanagementModelEventsRanking::getDivision()
	 * 
	 * @return
	 */
	function getDivision()
	{
		$division = null;
		if (self::$divisionid != 0)
		{
			$division = sportsmanagementModelProject::getDivision(self::$divisionid);
		}
		return $division;
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
	 * sportsmanagementModelEventsRanking::getLimit()
	 * 
	 * @return
	 */
	function getLimit()
	{
		return self::$limit;
	}

	/**
	 * sportsmanagementModelEventsRanking::getLimitStart()
	 * 
	 * @return
	 */
	function getLimitStart()
	{
		return self::$limitstart;
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
			$this->_pagination = new JPagination( self::getTotal(), self::getLimitStart(), self::getLimit() );
		}
		return $this->_pagination;
	}

	/**
	 * sportsmanagementModelEventsRanking::setEventid()
	 * 
	 * @param mixed $evid
	 * @return void
	 */
	function setEventid($evid)
	{
		// Allow for multiple statistics IDs, arranged in a single parameters (sid) as string
		// with "|" as separator
		$sidarr = explode("|", $evid);
		self::$eventid = array();
		foreach ($sidarr as $sid)
		{
			self::$eventid[] = (int)$sid;	// The cast gets rid of the slug
		}
		// In case 0 was (part of) the evid string, make sure all eventtypes are loaded)
		if (in_array(0, self::$eventid))
		{
			self::$eventid = 0;
		}
	}

	/**
	 * set order (asc or desc)
	 * @param string $order
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
	 * sportsmanagementModelEventsRanking::getEventTypes()
	 * 
	 * @return
	 */
	function getEventTypes()
	{
	   $app = JFactory::getApplication();
    $option = JRequest::getCmd('option');
         //Create a new query object.		
	   $db = sportsmanagementHelper::getDBConnection(TRUE, self::$cfg_which_database );
	   $query = $db->getQuery(true);
       
       $query->select('et.id as etid,me.event_type_id as id,et.*');
       $query->select('CONCAT_WS( \':\', et.id, et.alias ) AS event_slug');
       $query->from('#__sportsmanagement_eventtype as et');
       $query->join('INNER','#__sportsmanagement_match_event as me ON et.id = me.event_type_id');
       $query->join('INNER','#__sportsmanagement_match as m ON m.id = me.match_id');
       $query->join('INNER','#__sportsmanagement_round as r ON m.round_id = r.id');
                
		if (self::$projectid > 0)
		{
		$query->where('r.project_id = ' . self::$projectid );
        }
		if (self::$eventid != 0)
		{
            $query->where("me.event_type_id IN (".implode(",", self::$eventid).")");
		}

		$query->order('et.ordering');
        
        $db->setQuery($query);
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
            {
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'');
        }
        
		$result = $db->loadObjectList('etid');
		return $result;
	}

	/**
	 * sportsmanagementModelEventsRanking::getTotal()
	 * 
	 * @return
	 */
	function getTotal()
	{
	   $app = JFactory::getApplication();
    $option = JRequest::getCmd('option');
        // Create a new query object.		
	   $db = sportsmanagementHelper::getDBConnection(TRUE, self::$cfg_which_database );
	   $query = $db->getQuery(true);
       
		if (empty($this->_total))
		{
			$eventids = is_array(self::$eventid) ? self::$eventid : array(self::$eventid); 

			// Make sure the same restrictions are used here as in statistics/basic.php in getPlayersRanking()
            $query->select('COUNT(DISTINCT(teamplayer_id)) as count_player');
            $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_event AS me ');
            $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_person_id AS tp ON me.teamplayer_id = tp.id');
            $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st ON st.team_id = tp.team_id');  
            $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON pt.team_id = st.id');
            $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t ON t.id = st.team_id');
            $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS pl ON tp.person_id = pl.id');
            
			$query->where('me.event_type_id IN('.implode("," ,$eventids).')' );
            $query->where('pl.published = 1');
            
            if (self::$projectid > 0)
			{
                $query->where('pt.project_id = ' . self::$projectid );
			}
			if (self::$divisionid > 0)
			{
                $query->where('pt.division_id = ' . self::$divisionid );
			}
			if (self::$teamid > 0)
			{
                $query->where('st.team_id = ' . self::$teamid );
			}
			if (self::$matchid > 0)
			{
                $query->where('me.match_id = ' . self::$matchid );
			}
			
            $db->setQuery($query);
            
            if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
            {
            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'');
            }
            
			$this->_total = $db->loadResult();
		}
		return $this->_total;
	}

	/**
	 * sportsmanagementModelEventsRanking::_getEventsRanking()
	 * 
	 * @param mixed $eventtype_id
	 * @param string $order
	 * @param integer $limit
	 * @param integer $limitstart
	 * @return
	 */
	function _getEventsRanking($eventtype_id, $order='desc', $limit=10, $limitstart=0)
	{
	   $app = JFactory::getApplication();
    $option = JRequest::getCmd('option');
        // Create a new query object.		
	   $db = sportsmanagementHelper::getDBConnection(TRUE, self::$cfg_which_database );
	   $query = $db->getQuery(true);

        $query->select('SUM(me.event_sum) as p,pl.firstname AS fname,pl.nickname AS nname,pl.lastname AS lname,pl.country,pl.id AS pid,pl.picture,tp.picture AS teamplayerpic,t.id AS tid,t.name AS tname');
        $query->select('CONCAT_WS( \':\', pl.id, pl.alias ) AS person_slug');
        $query->select('CONCAT_WS( \':\', t.id, t.alias ) AS team_slug');
        $query->select('CONCAT_WS( \':\', pt.id, t.alias ) AS projectteam_slug');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_event AS me ');
            $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_person_id AS tp ON me.teamplayer_id = tp.id');
            $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st ON st.team_id = tp.team_id');  
            $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON pt.team_id = st.id');
            $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t ON t.id = st.team_id');
            $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS pl ON tp.person_id = pl.id');
             
        $query->where('me.event_type_id = '.$eventtype_id );
        $query->where('pl.published = 1');
                    
		if (self::$projectid > 0)
			{
                $query->where('pt.project_id = ' . self::$projectid );
			}
			if (self::$divisionid > 0)
			{
                $query->where('pt.division_id = ' . self::$divisionid );
			}
			if (self::$teamid > 0)
			{
                $query->where('st.team_id = ' . self::$teamid );
			}
			if (self::$matchid > 0)
			{
                $query->where('me.match_id = ' . self::$matchid );
			}
            
		$query->group('me.teamplayer_id');
        $query->order('me.match_id,p '.$order);
        
        $db->setQuery($query, self::getlimitStart(), self::getlimit());
		
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
            {
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'');
        }
        
        $rows = $db->loadObjectList();

		// get ranks
		$previousval = 0;
		$currentrank = 1 + $limitstart;
		foreach ($rows as $k => $row) 
		{
			$rows[$k]->rank = ($row->p == $previousval) ? $currentrank : $k + 1 + $limitstart;
			$previousval = $row->p;
			$currentrank = $row->rank;
		}
		return $rows;
	}

	/**
	 * sportsmanagementModelEventsRanking::getEventRankings()
	 * 
	 * @param mixed $limit
	 * @param integer $limitstart
	 * @param mixed $order
	 * @return
	 */
	function getEventRankings($limit, $limitstart=0, $order=null)
	{
		$order = ($order ? $order : $this->order);
		$eventtypes = self::getEventTypes();
		if (array_keys($eventtypes))
		{
			foreach (array_keys($eventtypes) AS $eventkey)
			{
				$eventrankings[$eventkey] = $this->_getEventsRanking($eventkey, $order, $limit, $limitstart);
			}
		}

		if (!isset ($eventrankings))
		{
			return null;
		}
		return $eventrankings;
	}

}
?>