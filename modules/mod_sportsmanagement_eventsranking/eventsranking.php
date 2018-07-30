<?php defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

require_once( JLG_PATH_SITE . DS . 'models' . DS . 'project.php' );

class JoomleagueModelEventsRanking extends JoomleagueModelProject
{
	var $projectid=0;
	var $divisionid = 0;
	var $teamid = 0;
	var $eventid=0;
	var $matchid=0;
	var $limit=20;
	var $limitstart=0;

	function __construct()
	{
		parent::__construct();
		$this->projectid=JRequest::getInt('p',0);
		$this->divisionid = JRequest::getInt( 'division', 0 );
		$this->teamid = JRequest::getInt( 'tid', 0 );
		$this->setEventid(JRequest::getVar('evid', '0'));
		$this->matchid = JRequest::getInt('mid',0);
		$config = $this->getTemplateConfig($this->getName());
		$defaultLimit = $this->eventid != 0 ? $config['max_events'] : $config['count_events'];
		$this->limit=JRequest::getInt('limit',$defaultLimit);
		$this->limitstart=JRequest::getInt('limitstart',0);
		$this->setOrder(JRequest::getVar('order','desc'));
	}

	function getDivision()
	{
		$division = null;
		if ($this->divisionid != 0)
		{
			$division = parent::getDivision($this->divisionid);
		}
		return $division;
	}

	function getTeamId()
	{
		return $this->teamid;
	}

	function getLimit()
	{
		return $this->limit;
	}

	function getLimitStart()
	{
		return $this->limitstart;
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
			$this->_pagination = new JPagination( $this->getTotal(), $this->getLimitStart(), $this->getLimit() );
		}
		return $this->_pagination;
	}

	function setEventid($evid)
	{
		// Allow for multiple statistics IDs, arranged in a single parameters (sid) as string
		// with "|" as separator
		$sidarr = explode("|", $evid);
		$this->eventid = array();
		foreach ($sidarr as $sid)
		{
			$this->eventid[] = (int)$sid;	// The cast gets rid of the slug
		}
		// In case 0 was (part of) the evid string, make sure all eventtypes are loaded)
		if (in_array(0, $this->eventid))
		{
			$this->eventid = 0;
		}
	}

	/**
	 * set order (asc or desc)
	 * @param string $order
	 * @return string order
	 */
	function setOrder($order)
	{
		if (strcasecmp($order, 'asc') === 0 || strcasecmp($order, 'desc') === 0) {
			$this->order = strtolower($order);
		}
		return $this->order;
	}

	function getEventTypes()
	{
		$query=	 ' SELECT	et.id as etid,me.event_type_id as id,et.* '
				.' FROM #__joomleague_eventtype as et '
				.' INNER JOIN #__joomleague_match_event as me ON et.id=me.event_type_id '
				.' INNER JOIN #__joomleague_match as m ON m.id=me.match_id '
				.' INNER JOIN #__joomleague_round as r ON m.round_id=r.id ';
		if ($this->projectid > 0)
		{
			$query .= " WHERE r.project_id=".$this->projectid;
		}
		if ($this->eventid != 0)
		{
			if ($this->projectid > 0)
			{
				$query .= " AND";
			}
			else
			{
				$query .= " WHERE";
			}
			$query .= " me.event_type_id IN (".implode(",", $this->eventid).")";
		}
		$query .= " ORDER BY et.ordering";
		$this->_db->setQuery($query);
		$result=$this->_db->loadObjectList('etid');
		return $result;
	}

	function getTotal()
	{
		if (empty($this->_total))
		{
			$eventids = is_array($this->eventid) ? $this->eventid : array($this->eventid); 

			// Make sure the same restrictions are used here as in statistics/basic.php in getPlayersRanking()
			$query=	 ' SELECT	COUNT(DISTINCT(teamplayer_id)) as count_player'
					.' FROM #__joomleague_match_event AS me '
					.' INNER JOIN #__joomleague_team_player AS tp ON tp.id=me.teamplayer_id '
					.' INNER JOIN #__joomleague_person pl ON tp.person_id=pl.id '
					.' INNER JOIN #__joomleague_project_team AS pt ON pt.id=tp.projectteam_id '
					.' INNER JOIN #__joomleague_team AS t ON t.id=pt.team_id '
					.' WHERE me.event_type_id IN('.implode("," ,$eventids).")"
					.'   AND pl.published = 1 '
					;
			if ($this->projectid > 0)
			{
				$query .= " AND pt.project_id=".$this->projectid;
			}
			if ($this->divisionid > 0)
			{
				$query .= " AND pt.division_id=".$this->divisionid;
			}
			if ($this->teamid > 0)
			{
				$query .= " AND pt.team_id = ".$this->teamid;
			}
			if ($this->matchid > 0)
			{
				$query .= " AND me.match_id=".$this->matchid;
			}
			$this->_db->setQuery($query);
			$this->_total = $this->_db->loadResult();
		}
		return $this->_total;
	}

	function _getEventsRanking($eventtype_id, $order='desc', $limit=10, $limitstart=0)
	{
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
		if ($this->projectid > 0)
		{
			$query .= " AND pt.project_id=".$this->projectid;
		}
		if ($this->divisionid > 0)
		{
			$query .= " AND pt.division_id=".$this->divisionid;
		}
		if ($this->teamid > 0)
		{
			$query .= " AND pt.team_id=".$this->teamid;
		}
		if ($this->matchid > 0)
		{
			$query .= " AND me.match_id=".$this->matchid;
		}
		$query .= " GROUP BY me.teamplayer_id ORDER BY p $order, me.match_id";
		$this->_db->setQuery($query, $this->getlimitStart(), $this->getlimit());
		$rows=$this->_db->loadObjectList();

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
				$eventrankings[$eventkey]=$this->_getEventsRanking($eventkey, $order, $limit, $limitstart);
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