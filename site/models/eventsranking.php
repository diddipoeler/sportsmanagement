<?php


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

//require_once( JLG_PATH_SITE . DS . 'models' . DS . 'project.php' );

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

	function __construct()
	{
		parent::__construct();
		self::$projectid=JRequest::getInt('p',0);
		self::$divisionid = JRequest::getInt( 'division', 0 );
		self::$teamid = JRequest::getInt( 'tid', 0 );
		self::setEventid(JRequest::getVar('evid', '0'));
		self::$matchid = JRequest::getInt('mid',0);
		$config = sportsmanagementModelProject::getTemplateConfig($this->getName());
		$defaultLimit = self::$eventid != 0 ? $config['max_events'] : $config['count_events'];
		self::$limit=JRequest::getInt('limit',$defaultLimit);
		self::$limitstart=JRequest::getInt('limitstart',0);
		self::setOrder(JRequest::getVar('order','desc'));
        
        self::$cfg_which_database = JRequest::getInt( 'cfg_which_database', 0 );
        sportsmanagementModelProject::$projectid = self::$projectid;
         
	}

	function getDivision()
	{
		$division = null;
		if (self::$divisionid != 0)
		{
			$division = sportsmanagementModelProject::getDivision(self::$divisionid);
		}
		return $division;
	}

	function getTeamId()
	{
		return self::$teamid;
	}

	function getLimit()
	{
		return self::$limit;
	}

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

	function getEventTypes()
	{
	   $app = JFactory::getApplication();
    $option = JRequest::getCmd('option');
        // Create a new query object.		
	   $db = sportsmanagementHelper::getDBConnection(TRUE, self::$cfg_which_database );
	   $query = $db->getQuery(true);
       
       $query->select('et.id as etid,me.event_type_id as id,et.*');
       $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_eventtype as et ');
       $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_event as me ON et.id=me.event_type_id ');
       $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_match as m ON m.id=me.match_id ');
       $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_round as r ON m.round_id=r.id ');
       
//		$query=	 ' SELECT	et.id as etid,me.event_type_id as id,et.* '
//				.' FROM #__joomleague_eventtype as et '
//				.' INNER JOIN #__joomleague_match_event as me ON et.id=me.event_type_id '
//				.' INNER JOIN #__joomleague_match as m ON m.id=me.match_id '
//				.' INNER JOIN #__joomleague_round as r ON m.round_id=r.id ';
                
		if (self::$projectid > 0)
		{
			//$query .= " WHERE r.project_id=".$this->projectid;
		$query->where('r.project_id = ' . self::$projectid );
        }
		if (self::$eventid != 0)
		{
//			if ($this->projectid > 0)
//			{
//				$query .= " AND";
//			}
//			else
//			{
//				$query .= " WHERE";
//			}
            $query->where("me.event_type_id IN (".implode(",", self::$eventid).")");
			//$query .= " me.event_type_id IN (".implode(",", $this->eventid).")";
		}
		//$query .= " ORDER BY et.ordering";
		$query->order('et.ordering');
        
        $db->setQuery($query);
        
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'');
        
		$result = $db->loadObjectList('etid');
		return $result;
	}

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
            
            /*
			$query=	 ' SELECT	COUNT(DISTINCT(teamplayer_id)) as count_player'
					.' FROM #__joomleague_match_event AS me '
					.' INNER JOIN #__joomleague_team_player AS tp ON tp.id=me.teamplayer_id '
					.' INNER JOIN #__joomleague_person pl ON tp.person_id=pl.id '
					.' INNER JOIN #__joomleague_project_team AS pt ON pt.id=tp.projectteam_id '
					.' INNER JOIN #__joomleague_team AS t ON t.id=pt.team_id '
					.' WHERE me.event_type_id IN('.implode("," ,$eventids).")"
					.'   AND pl.published = 1 '
					;
            */        
			$query->where('me.event_type_id IN('.implode("," ,$eventids).')' );
            $query->where('pl.published = 1');
            
            if (self::$projectid > 0)
			{
				//$query .= " AND pt.project_id=".$this->projectid;
                $query->where('pt.project_id = ' . self::$projectid );
			}
			if (self::$divisionid > 0)
			{
				//$query .= " AND pt.division_id=".$this->divisionid;
                $query->where('pt.division_id = ' . self::$divisionid );
			}
			if (self::$teamid > 0)
			{
				//$query .= " AND pt.team_id = ".$this->teamid;
                $query->where('pt.team_id = ' . self::$teamid );
			}
			if (self::$matchid > 0)
			{
				//$query .= " AND me.match_id=".$this->matchid;
                $query->where('me.match_id = ' . self::$matchid );
			}
			
            $db->setQuery($query);
            
            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'');
            
			$this->_total = $db->loadResult();
		}
		return $this->_total;
	}

	function _getEventsRanking($eventtype_id, $order='desc', $limit=10, $limitstart=0)
	{
	   $app = JFactory::getApplication();
    $option = JRequest::getCmd('option');
        // Create a new query object.		
	   $db = sportsmanagementHelper::getDBConnection(TRUE, self::$cfg_which_database );
	   $query = $db->getQuery(true);
       
       /*
		$query=	 ' SELECT	SUM(me.event_sum) as p,'
				.' pl.firstname AS fname,'
				.' pl.nickname AS nname,'
				.' pl.lastname AS lname,'
				.' pl.country,'
				.' pl.id AS pid,'
				.' pl.picture,'
				.' tp.picture AS teamplayerpic,'
				.' t.id AS tid,'
				.' t.name AS tname '
				.' FROM #__joomleague_match_event AS me '
				.' INNER JOIN #__joomleague_team_player AS tp ON tp.id=me.teamplayer_id '
				.' INNER JOIN #__joomleague_person pl ON tp.person_id=pl.id '
				.' INNER JOIN #__joomleague_project_team AS pt ON pt.id=tp.projectteam_id '
				.' INNER JOIN #__joomleague_team AS t ON t.id=pt.team_id '
				.' WHERE me.event_type_id='.$eventtype_id
				.' AND pl.published = 1 '
				;
                */
        $query->select('SUM(me.event_sum) as p,pl.firstname AS fname,pl.nickname AS nname,pl.lastname AS lname,pl.country,pl.id AS pid,pl.picture,tp.picture AS teamplayerpic,t.id AS tid,t.name AS tname');
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
				//$query .= " AND pt.project_id=".$this->projectid;
                $query->where('pt.project_id = ' . self::$projectid );
			}
			if (self::$divisionid > 0)
			{
				//$query .= " AND pt.division_id=".$this->divisionid;
                $query->where('pt.division_id = ' . self::$divisionid );
			}
			if (self::$teamid > 0)
			{
				//$query .= " AND pt.team_id = ".$this->teamid;
                $query->where('pt.team_id = ' . self::$teamid );
			}
			if (self::$matchid > 0)
			{
				//$query .= " AND me.match_id=".$this->matchid;
                $query->where('me.match_id = ' . self::$matchid );
			}
            
		//$query .= " GROUP BY me.teamplayer_id ORDER BY p $order, me.match_id";
		$query->group('me.teamplayer_id');
        $query->order('me.match_id,p '.$order);
        
        $db->setQuery($query, self::getlimitStart(), self::getlimit());
		
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'');
        
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

	function getEventRankings($limit, $limitstart=0, $order=null)
	{
		$order = ($order ? $order : $this->order);
		$eventtypes=$this->getEventTypes();
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