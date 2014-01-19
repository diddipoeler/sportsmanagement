<?php 

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');
jimport( 'joomla.filesystem.folder' );

//require_once( JLG_PATH_SITE . DS . 'models' . DS . 'project.php' );

class sportsmanagementModelMatchReport extends JModel
{

	var $matchid=0;
	var $match=null;

	/**
	 * caching for players events. Used in stats calculations
	 * @var unknown_type
	 */
	var $_playersevents=null;

	/**
	 * caching for players basic stats. Used in stats calculations
	 * @var unknown_type
	 */
	var $_playersbasicstats=null;

	/**
	 * caching for staff basic stats. Used in stats calculations
	 * @var unknown_type
	 */
	var $_staffsbasicstats=null;


	function __construct()
	{
		$this->matchid = JRequest::getInt('mid',0);
		parent::__construct();
	}


/**
 * 	// Functions (some specific for Matchreport) below to be replaced to project.php when recoded to general functions
 * 	function &getMatch()
 * 	{
 * 		if (is_null($this->match))
 * 		{
 * 			$query=' SELECT m.*,DATE_FORMAT(m.time_present,"%H:%i") time_present,r.project_id '
 * 			      .' FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match AS m '
 * 			      .' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_round AS r on r.id=m.round_id '
 * 			      .' WHERE m.id='. $this->_db->Quote($this->matchid)
 * 			            ;
 * 			$this->_db->setQuery($query,0,1);
 * 			$this->match=$this->_db->loadObject();
 * 		}
 * 		return $this->match;
 * 	}
 */

/**
 * 	function &getProject()
 * 	{
 * 		if (empty($this->_project))
 * 		{
 * 			$match=&$this->getMatch();
 * 			$this->setProjectID($match->project_id);
 * 			parent::getProject();
 * 		}
 * 		return $this->_project;
 * 	}
 */

	function getClubinfo($clubid)
	{
		//$this->club =& $this->getTable('Club','Table');
        $mdl = JModel::getInstance("club", "sportsmanagementModel");
        $this->club = $mdl->getTable();
		$this->club->load($clubid);

		return $this->club;
	}

	function getRound()
	{
		//$match=$this->getMatch();
        $match = sportsmanagementModelMatch::getMatchData($this->matchid);

		//$round =& $this->getTable('Round','sportsmanagementTable');
		//$round->load($match->round_id);
        $mdl = JModel::getInstance("round", "sportsmanagementModel");
        $round = $mdl->getTable();
        $round->load($match->round_id);

		//if no match title set then set the default one
		if(is_null($round->name) || empty($round->name))
		{
			$round->name=JText::sprintf('COM_SPORTSMANAGEMENT_RESULTS_GAMEDAY_NB',$round->id);
		}

		return $round;
	}

  function getMatchPictures($folder)
  {
  $basePath = JPATH_SITE.DS.'images'.DS.'com_sportsmanagement'.DS.'database'.DS.$folder;
  $sitePath = 'images'.DS.'com_sportsmanagement'.DS.'database'.DS.$folder;
  $images 	= array ();
  
  
  if( JFolder::exists($basePath) )
{
  // Get the list of files and folders from the given folder
	$fileList 	= JFolder::files($basePath);
  
  // Iterate over the files if they exist
		if ($fileList !== false) {
			foreach ($fileList as $file)
			{
				if (is_file($basePath.DS.$file) && substr($file, 0, 1) != '.'
					&& strtolower($file) !== 'index.html'
					&& strtolower($file) !== 'thumbs.db'
					&& strtolower($file) !== 'readme.txt'
					)

					{

					if ( $search == '') {
						$tmp = new JObject();
						$tmp->name = $file;
						$tmp->sitepath = $sitePath;
						$tmp->path = JPath::clean($basePath.DS.$file);

						$images[] = $tmp;

					} elseif(stristr( $file, $search)) {
						$tmp = new JObject();
						$tmp->name = $file;
						$tmp->sitepath = $sitePath;
						$tmp->path = JPath::clean($basePath.DS.$file);

						$images[] = $tmp;

					}
				}
			}
		}
		
	$list = $images;
  return $list;
  }
  else
  {
  return false;
  }
  	
  }
  
	function getMatchPlayerPositions()
	{
		$query='	SELECT	pos.id, pos.name, 
							ppos.position_id AS position_id, ppos.id as pposid
					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS pos
					INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS ppos ON pos.id=ppos.position_id
					INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_player AS mp ON ppos.id=mp.project_position_id
					WHERE mp.match_id='.(int)$this->matchid.'
					GROUP BY pos.id
					ORDER BY pos.ordering ASC ';
		$this->_db->setQuery($query);
		return $this->_db->loadObjectList();
	}

	function getMatchStaffPositions()
	{
		$query='	SELECT	pos.id, pos.name, 
							ppos.position_id AS position_id, ppos.id as pposid
					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS pos
					INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS ppos ON pos.id=ppos.position_id
					INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_staff AS mp ON ppos.id=mp.project_position_id
					WHERE mp.match_id='.(int)$this->matchid.'
					GROUP BY pos.id
					ORDER BY pos.ordering ASC ';
		$this->_db->setQuery($query);
		return $this->_db->loadObjectList();
	}

	function getMatchRefereePositions()
	{
		$query='	SELECT	pos.id, pos.name, 
							ppos.position_id AS position_id, ppos.id as pposid
					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS pos
					INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS ppos ON pos.id=ppos.position_id
					INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_referee AS mp ON ppos.id=mp.project_position_id
					WHERE mp.match_id='.(int)$this->matchid.'
					GROUP BY pos.id
					ORDER BY pos.ordering ASC ';
		$this->_db->setQuery($query);
		return $this->_db->loadObjectList();
	}

	function getMatchPlayers()
	{
	   $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        // Create a new query object.		
	   $db = JFactory::getDBO();
	   $query = $db->getQuery(true);
       
		$query=' SELECT	pt.id,'
		      .' tp.person_id,'
		      .' p.firstname,'
		      .' p.nickname,'
		      .' p.lastname,'
		      .' tp.jerseynumber,'
              .' mp.trikot_number,'
		      .' ppos.position_id,'
		      .' ppos.id AS pposid,'
		      .' pt.team_id,'
		      .' pt.id as ptid,'
		      .' mp.teamplayer_id,'
		      .' tp.picture,'
			  .' p.picture AS ppic,'
		      .' CASE WHEN CHAR_LENGTH(t.alias) THEN CONCAT_WS(\':\',t.id,t.alias) ELSE t.id END AS team_slug,'
		      .' CASE WHEN CHAR_LENGTH(p.alias) THEN CONCAT_WS(\':\',p.id,p.alias) ELSE p.id END AS person_slug '
		      .' FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_player AS mp '
		      .' INNER JOIN	#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_person_id AS tp ON tp.id = mp.teamplayer_id '
		      .' INNER JOIN	#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON pt.team_id = tp.team_id '
		      .' INNER JOIN	#__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t ON t.id = pt.team_id '
		      .' INNER JOIN	#__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS p ON tp.person_id = p.id '
		      .' LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS ppos ON ppos.id = mp.project_position_id '
		      .' LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS pos ON ppos.position_id = pos.id '
		      .' WHERE mp.match_id='.(int)$this->matchid
		      .' AND mp.came_in=0 '
		      .' AND p.published = 1 '
		      .' ORDER BY mp.ordering, tp.jerseynumber, p.lastname ';
		$this->_db->setQuery($query);
        
        $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' matchid<br><pre>'.print_r($this->matchid,true).'</pre>'),'Error');
        $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' <br><pre>'.print_r($this->_db->loadObjectList(),true).'</pre>'),'Error');
        
		return $this->_db->loadObjectList();
	}

	function getMatchStaff()
	{
	   $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        // Create a new query object.		
	   $db = JFactory::getDBO();
	   $query = $db->getQuery(true);
       
		$query=' SELECT	p.id,'
		      .' p.id AS person_id,'
		      .' ms.team_staff_id,'
		      .' p.firstname,'
		      .' p.nickname,'
		      .' p.lastname,'
		      .' ppos.position_id,'
		      .' ppos.id AS pposid,'
		      .' pt.team_id,'
		      .' pt.id as ptid,'
		      .' tp.picture,'
			  .' CASE WHEN CHAR_LENGTH(t.alias) THEN CONCAT_WS(\':\',t.id,t.alias) ELSE t.id END AS team_slug,'
			  .' CASE WHEN CHAR_LENGTH(p.alias) THEN CONCAT_WS(\':\',p.id,p.alias) ELSE p.id END AS person_slug '
		      .' FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_staff AS ms '
		      .' INNER JOIN	#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_person_id AS tp ON tp.id=ms.team_staff_id '
		      .' INNER JOIN	#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON pt.team_id=tp.team__id '
		      .' INNER JOIN	#__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS p ON tp.person_id=p.id '
		      .' INNER JOIN	#__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t ON t.id=pt.team_id '
		      .' LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS ppos ON ppos.id=ms.project_position_id '
		      .' LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS pos ON ppos.position_id=pos.id '
		      .' WHERE ms.match_id='.(int)$this->matchid
		      .'  AND p.published = 1';
		       $this->_db->setQuery($query);
               
               $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' matchid<br><pre>'.print_r($this->matchid,true).'</pre>'),'Error');
               $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' <br><pre>'.print_r($this->_db->loadObjectList(),true).'</pre>'),'Error');
               
		return $this->_db->loadObjectList();
	}

	function getMatchCommentary()
    {
        $query = "SELECT *  
    FROM #__".COM_SPORTSMANAGEMENT_TABLE."_match_commentary
    WHERE match_id = ".(int)$this->matchid." 
    ORDER BY event_time DESC";
    $this->_db->setQuery($query);
		return $this->_db->loadObjectList();
    }
    
    
    function getMatchReferees()
	{
		$query=' SELECT	p.id,'
		      .' p.firstname,'
		      .' p.nickname,'
		      .' p.lastname,'
		      .' ppos.position_id,'
		      .' ppos.id AS pposid,'
		      .' pos.name AS position_name ,'
		      .' CASE WHEN CHAR_LENGTH(p.alias) THEN CONCAT_WS(\':\',p.id,p.alias) ELSE p.id END AS person_slug '
		      .' FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_referee AS mr '
		      .' LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_referee AS pref ON mr.project_referee_id=pref.id '
		      .' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS p ON pref.person_id=p.id '
		      .' LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS ppos ON ppos.id=mr.project_position_id '
		      .' LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS pos ON ppos.position_id=pos.id '
		      .' WHERE mr.match_id='.(int)$this->matchid
		      .' AND p.published = 1';
		$this->_db->setQuery($query);
		return $this->_db->loadObjectList();
	}

	function getSubstitutes()
	{
		$query=' SELECT	mp.in_out_time,
						mp.teamplayer_id,
						pt.team_id,
						pt.id AS ptid,
						tp.person_id,
						tp.jerseynumber,
						tp2.person_id AS out_person_id,
						mp.in_for,
						p2.id AS out_ptid,
						p.firstname,
						p.nickname,
						p.lastname,
						pos.name AS in_position,
						pos2.name AS out_position,
						p2.firstname AS out_firstname,
						p2.nickname AS out_nickname,
						p2.lastname AS out_lastname,
						ppos.id AS pposid1,
						ppos2.id AS pposid2,
						CASE WHEN CHAR_LENGTH(t.alias) THEN CONCAT_WS(\':\',t.id,t.alias) ELSE t.id END AS team_slug,
						CASE WHEN CHAR_LENGTH(p.alias) THEN CONCAT_WS(\':\',p.id,p.alias) ELSE p.id END AS person_slug
					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_player AS mp
						LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team_player AS tp ON mp.teamplayer_id=tp.id
						LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON tp.projectteam_id=pt.id
						LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS p ON tp.person_id=p.id
						  AND p.published = 1
						LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team_player AS tp2 ON mp.in_for=tp2.id
						LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS p2 ON tp2.person_id=p2.id
						  AND p2.published = 1
						LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS ppos ON ppos.id=mp.project_position_id
						LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS pos ON ppos.position_id=pos.id
						LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_player AS mp2 ON mp.match_id=mp2.match_id and mp.in_for=mp2.teamplayer_id
						LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS ppos2 ON ppos2.id=mp2.project_position_id
						LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS pos2 ON ppos2.position_id=pos2.id
						INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t ON t.id=pt.team_id
					WHERE mp.match_id = '.(int)$this->matchid.' 
					  AND mp.came_in > 0
					GROUP BY mp.in_out_time, mp.teamplayer_id, pt.team_id
					ORDER by (mp.in_out_time+0)';
		$this->_db->setQuery($query);
		//echo($this->_db->getQuery());
		$result=$this->_db->loadObjectList();
		return $result;
	}

	function getEventTypes()
	{
		$query='	SELECT	et.id,
							et.name,
							et.icon
					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_eventtype AS et
					INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_position_eventtype AS pet ON pet.eventtype_id=et.id					
					LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_event AS me ON et.id=me.event_type_id
					WHERE me.match_id='.(int)$this->matchid.'
					GROUP BY et.id
					ORDER BY pet.ordering ';
		$this->_db->setQuery($query);
		return $this->_db->loadObjectList();
	}

	function getPlayground($pgid)
	{
		//$this->playground =& $this->getTable('Playground','Table');
        $mdl = JModel::getInstance("Playground", "sportsmanagementModel");
        $this->playground = $mdl->getTable();
		$this->playground->load($pgid);

		return $this->playground;
	}

	/**
	 * get match statistics as an array (projectteam_id => teamplayer_id => statistic_id)
	 * @return array
	 */
	function getMatchStats()
	{
		$match = sportsmanagementModelMatch::getMatchData($this->matchid);
		$query=' SELECT * FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_statistic '
		      .' WHERE match_id='. $this->_db->Quote($match->id);
		$this->_db->setQuery($query);
		$res=$this->_db->loadObjectList();

		$stats = array(	$match->projectteam1_id => array(),
						$match->projectteam2_id => array());
		if(count($stats)>0 && count($res)>0) {
			foreach ($res as $stat)
			{
				@$stats[$stat->projectteam_id][$stat->teamplayer_id][$stat->statistic_id]=$stat->value;
			}
		}
		return $stats;
	}

	/**
	 * get match statistics as array(teamplayer_id => array(statistic_id => value))
	 * @return array
	 */
	function getPlayersStats()
	{
		if (!($this->_playersbasicstats))
		{
			$match=&$this->getMatch();

			$query=' SELECT * FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_statistic '
			      .' WHERE match_id='. $this->_db->Quote($match->id);
			$this->_db->setQuery($query);
			$res=$this->_db->loadObjectList();

			$stats=array();
			if (count($res))
			{
				foreach ($res as $stat)
				{
					@$stats[$stat->teamplayer_id][$stat->statistic_id]=$stat->value;
				}
			}
			$this->_playersbasicstats=$stats;
		}

		return $this->_playersbasicstats;
	}

	/**
	 * get match statistics as array(teamplayer_id => array(event_type_id => value))
	 * @return array
	 */
	function getPlayersEvents()
	{
		if (!($this->_playersevents))
		{
			$match = sportsmanagementModelMatch::getMatchData($this->matchid);

			$query=' SELECT * FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_event '
			      .' WHERE match_id='. $this->_db->Quote($match->id);
			$this->_db->setQuery($query);
			$res=$this->_db->loadObjectList();

			$events=array();
			if (count($res))
			{
				foreach ($res as $event)
				{
					@$events[$event->teamplayer_id][$event->event_type_id] += $event->event_sum;
				}
			}
			$this->_playersevents=$events;
		}

		return $this->_playersevents;
	}

	/**
	 * get match statistics as an array (team_staff_id => statistic_id)
	 * @return array
	 */
	function getMatchStaffStats()
	{
		if (!($this->_staffsbasicstats))
		{
			$match = sportsmanagementModelMatch::getMatchData($this->matchid);

			$query=' SELECT * FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_staff_statistic '
			      .' WHERE match_id='. $this->_db->Quote($match->id);
			$this->_db->setQuery($query);
			$res=$this->_db->loadObjectList();

			$stats=array();
			if (count($res))
			{
				foreach ($res as $stat)
				{
					@$stats[$stat->team_staff_id][$stat->statistic_id]=$stat->value;
				}
			}
			$this->_staffsbasicstats=$stats;
		}
		return $this->_staffsbasicstats;
	}

	function getMatchText($match_id)
	{
		$query="SELECT	m.*,
						t1.name t1name,
						t2.name t2name
				FROM #__".COM_SPORTSMANAGEMENT_TABLE."_match AS m
				INNER JOIN #__".COM_SPORTSMANAGEMENT_TABLE."_project_team AS pt1 ON m.projectteam1_id=pt1.id
				INNER JOIN #__".COM_SPORTSMANAGEMENT_TABLE."_project_team AS pt2 ON m.projectteam2_id=pt2.id
				INNER JOIN #__".COM_SPORTSMANAGEMENT_TABLE."_team AS t1 ON pt1.team_id=t1.id
				INNER JOIN #__".COM_SPORTSMANAGEMENT_TABLE."_team AS t2 ON pt2.team_id=t2.id
				WHERE m.id=".$match_id."
				AND m.published=1
				ORDER BY m.match_date,t1.short_name";
		$this->_db->setQuery($query);
		return $this->_db->loadObject();
	}
	
	function getSchemaHome($schemahome)
  {
  $db = Jfactory::getDBO();
  $bildpositionen = array();
  $position = 1;
  
  if ( $schemahome )
  {
  $query =" SELECT extended"
				. " FROM #__".COM_SPORTSMANAGEMENT_TABLE."_rosterposition "
				. " WHERE name LIKE '" . $schemahome ."'"
				. " AND short_name = 'HOME_POS'";
		$db->setQuery($query);
		$res = $db->loadResult();
		
		$xmlfile=JPATH_COMPONENT_ADMINISTRATOR.DS.'assets'.DS.'extended'.DS.'rosterposition.xml';
		$jRegistry = new JRegistry;
		$jRegistry->loadString($res, 'ini');
    
    for($a=0; $a < 11; $a++)
    {
    $bildpositionen[$schemahome][$a]['heim']['oben'] = $jRegistry->get('COM_SPORTSMANAGEMENT_EXT_ROSTERPOSITIONS_'.$position.'_TOP');
    $bildpositionen[$schemahome][$a]['heim']['links'] = $jRegistry->get('COM_SPORTSMANAGEMENT_EXT_ROSTERPOSITIONS_'.$position.'_LEFT');
    $position++;
    }
		
		return $bildpositionen;
  }
  else
  {
    return false;
  }
  
  }
  
  function getSchemaAway($schemaaway)
  {
  $db = Jfactory::getDBO();
  $bildpositionen = array();
  $position = 1;
    
  if ( $schemaaway )
  {
  $query =" SELECT extended"
				. " FROM #__".COM_SPORTSMANAGEMENT_TABLE."_rosterposition "
				. " WHERE name LIKE '" . $schemaaway ."'"
				. " AND short_name = 'AWAY_POS'";
		$db->setQuery($query);
		$res = $db->loadResult();

		$xmlfile=JPATH_COMPONENT_ADMINISTRATOR.DS.'assets'.DS.'extended'.DS.'rosterposition.xml';
		$jRegistry = new JRegistry;
		$jRegistry->loadString($res, 'ini');
    
    for($a=0; $a < 11; $a++)
    {
    $bildpositionen[$schemaaway][$a]['gast']['oben'] = $jRegistry->get('COM_SPORTSMANAGEMENT_EXT_ROSTERPOSITIONS_'.$position.'_TOP');
    $bildpositionen[$schemaaway][$a]['gast']['links'] = $jRegistry->get('COM_SPORTSMANAGEMENT_EXT_ROSTERPOSITIONS_'.$position.'_LEFT');
    $position++;
    }
		
		return $bildpositionen;
  }
  else
  {
    return false;
  }		
			
  }
  

}
?>
