<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      eventsranking.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage eventsranking
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
		//self::setEventid($jinput->get('evid'));
		self::$matchid = $jinput->get('mid',0,'INT');
		
		self::$eventid = (is_array($jinput->get('evid'))) ? implode(",", array_map('intval', $jinput->get('evid')) ) : (int)$jinput->get('evid');
		//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r(self::$eventid,true).'</pre>'),'');
//		$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($jinput->get('evid'),true).'</pre>'),'');
		
		$config = sportsmanagementModelProject::getTemplateConfig($this->getName());
		$defaultLimit = self::$eventid != 0 ? $config['max_events'] : $config['count_events'];
		self::$limit = $jinput->getInt('limit',$defaultLimit);
		self::$limitstart = $jinput->getInt('start',0);
		self::setOrder($jinput->getVar('order','desc'));
        
        self::$cfg_which_database = $jinput->getInt( 'cfg_which_database', 0 );
        sportsmanagementModelProject::$projectid = self::$projectid;
         
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
    $option = JFactory::getApplication()->input->getCmd('option');
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
            $query->where("me.event_type_id IN (".self::$eventid.")");
		}

		$query->order('et.ordering');
        
        $db->setQuery($query);
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
            {
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'');
        }
        try{
		$result = $db->loadObjectList('etid');
		return $result;
		 }
        catch (Exception $e)
        {
        $app->enqueueMessage(JText::_(__METHOD__.' '.' '.$e->getMessage()), 'error');
        return false;
        }
	}

	/**
	 * sportsmanagementModelEventsRanking::getTotal()
	 * 
	 * @return
	 */
	function getTotal()
	{
	   $app = JFactory::getApplication();
    $option = JFactory::getApplication()->input->getCmd('option');
        // Create a new query object.		
	   $db = sportsmanagementHelper::getDBConnection(TRUE, self::$cfg_which_database );
	   $query = $db->getQuery(true);
       
		if (empty($this->_total))
		{
//			$eventids = is_array(self::$eventid) ? self::$eventid : array(self::$eventid); 

			// Make sure the same restrictions are used here as in statistics/basic.php in getPlayersRanking()
            $query->select('COUNT(DISTINCT(teamplayer_id)) as count_player');
            $query->from('#__sportsmanagement_match_event AS me ');
            $query->join('INNER','#__sportsmanagement_season_team_person_id AS tp ON me.teamplayer_id = tp.id');
            $query->join('INNER','#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id');  
            $query->join('INNER','#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
            $query->join('INNER','#__sportsmanagement_team AS t ON t.id = st.team_id');
            $query->join('INNER','#__sportsmanagement_person AS pl ON tp.person_id = pl.id');
            
			$query->where('me.event_type_id IN('.self::$eventid.')' );
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
            try{
			$this->_total = $db->loadResult();
		
		
		}
        catch (Exception $e)
        {
        $app->enqueueMessage(JText::_(__METHOD__.' '.' '.$e->getMessage()), 'error');
        return false;
        }
		
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
    $option = JFactory::getApplication()->input->getCmd('option');
        // Create a new query object.		
	   $db = sportsmanagementHelper::getDBConnection(TRUE, self::$cfg_which_database );
	   $query = $db->getQuery(true);

        $query->select('SUM(me.event_sum) as p,pl.firstname AS fname,pl.nickname AS nname,pl.lastname AS lname,pl.country,pl.id AS pid,pl.picture,tp.picture AS teamplayerpic,t.id AS tid,t.name AS tname');
        $query->select('CONCAT_WS( \':\', pl.id, pl.alias ) AS person_slug');
        $query->select('CONCAT_WS( \':\', t.id, t.alias ) AS team_slug');
        $query->select('CONCAT_WS( \':\', pt.id, t.alias ) AS projectteam_slug');
        $query->from('#__sportsmanagement_match_event AS me ');
            $query->join('INNER','#__sportsmanagement_season_team_person_id AS tp ON me.teamplayer_id = tp.id');
            $query->join('INNER','#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id');  
            $query->join('INNER','#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
            $query->join('INNER','#__sportsmanagement_team AS t ON t.id = st.team_id');
            $query->join('INNER','#__sportsmanagement_person AS pl ON tp.person_id = pl.id');
             
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
        $query->order('p '.$order);
        
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
	function getEventRankings($limit=0, $limitstart=0, $order=null)
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
