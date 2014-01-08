<?php 

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');
//require_once('person.php');

class sportsmanagementModelPlayer extends JModel
{
	
    var $projectid		= 0;
	var $personid		  = 0;
	var $teamplayerid	= 0;
    
    /**
	 * data array for player history
	 * @var array
	 */
	var $_playerhistory =null;
	var $_teamplayers = null;

/**
 * 	function __construct()
 * 	{
 * 		parent::__construct();
 * 		$this->projectid=JRequest::getInt('p',0);
 * 		$this->personid=JRequest::getInt('pid',0);
 * 		$this->teamplayerid=JRequest::getInt('pt',0);
 * 	}
 */

	// Get all teamplayers of the project where the person played in
	//  (in case the player was transferred team of the project within the season)
	function getTeamPlayers()
	{
	   $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        // Create a new query object.		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
        
        // Select some fields
        $query->select('tp.*');
        $query->select('pt.project_id,pt.team_id,pt.id as projectteam_id');
        $query->select('pos.name AS position_name');
        $query->select('ppos.position_id');
        $query->select('rinjuryfrom.round_date_first as injury_date,rinjuryfrom.name as rinjury_from');
        $query->select('rinjuryto.round_date_last as injury_end,rinjuryto.name as rinjury_to');
        $query->select('rsuspfrom.round_date_first as suspension_date,rsuspfrom.name as rsusp_from');
        $query->select('rsuspto.round_date_last as suspension_end,rsuspto.name as rsusp_to');
        $query->select('rawayfrom.round_date_first as away_date,rawayfrom.name as raway_from');
        $query->select('rawayto.round_date_last as away_end,rawayto.name as raway_to');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_person_id AS tp ');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS pe ON pe.id = tp.person_id');
		$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON pt.team_id = tp.team_id');
		$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS ppos ON ppos.id = tp.project_position_id');
		$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS p ON p.id = pt.project_id');
		$query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS pos ON pos.id = ppos.position_id');
		$query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_round AS rinjuryfrom ON pe.injury_date = rinjuryfrom.id');
		$query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_round AS rinjuryto ON pe.injury_end = rinjuryto.id');
		$query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_round AS rsuspfrom ON pe.suspension_date = rsuspfrom.id');
		$query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_round AS rsuspto ON pe.suspension_end = rsuspto.id');
		$query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_round AS rawayfrom ON pe.away_date = rawayfrom.id');
		$query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_round AS rawayto ON pe.away_end = rawayto.id');        
        $query->where('pt.project_id='.$db->Quote($this->projectid));
        $query->where('tp.person_id='.$db->Quote($this->personid));
        $query->where('p.published = 1');
        $db->setQuery($query);
		$this->_teamplayers = $db->loadObjectList('projectteam_id');
        
        if ( !$this->_teamplayers )
        {
            $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
        }

		return $this->_teamplayers;
	}

	// As a player can be transferred from one team to the next within a season, it is possible that 
	function getTeamPlayer()
	{
	   $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        // Create a new query object.		
	   $db = JFactory::getDBO();
	   $query = $db->getQuery(true);
       
       // Select some fields
        $query->select('tp.*');
        $query->select('pt.project_id,pt.team_id,pt.notes AS ptnotes');
        $query->select('pos.name AS position_name');
        $query->select('ppos.position_id');
       $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_person_id AS tp ');
		$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON pt.team_id = tp.team_id');
		$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS ppos ON ppos.id = tp.project_position_id');
		$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS p ON p.id = pt.project_id');
		$query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS pos ON pos.id = ppos.position_id');      
       $query->where('pt.project_id='.$db->Quote($this->projectid));
        $query->where('tp.person_id='.$db->Quote($this->personid));
        $query->where('p.published = 1');
       $query->where('tp.persontype = 1');
       
 		$db->setQuery($query);
 		$this->_inproject = $db->loadObjectList();
        if ( !$this->_inproject )
        {
            $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
        }

		return $this->_inproject;
	}

	function getTeamStaff()
	{
	   $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        // Create a new query object.		
	   $db = JFactory::getDBO();
	   $query = $db->getQuery(true);
       
        // Select some fields
        $query->select('tp.*');
        $query->select('pt.project_id,pt.team_id,pt.notes AS ptnotes');
        $query->select('pos.name AS position_name');
        $query->select('ppos.position_id');
       $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_person_id AS tp ');
		$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON pt.team_id = tp.team_id');
		$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS ppos ON ppos.id = tp.project_position_id');
		$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS p ON p.id = pt.project_id');
		$query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS pos ON pos.id = ppos.position_id');      
       $query->where('pt.project_id='.$db->Quote($this->projectid));
        $query->where('tp.person_id='.$db->Quote($this->personid));
        $query->where('p.published = 1');
        $query->where('tp.persontype = 2');
       
       $db->setQuery($query);
 	$this->_inproject = $db->loadObject();
    if ( !$this->_inproject )
        {
            $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
        }
		

		return $this->_inproject;
	}

	function getPositionEventTypes($positionId=0)
	{
		$result=array();
		// TODO: pj.id is not known in the query. Is this function called anywhere? If not, just remove it...
		$query='	SELECT	pet.*,
							pj.id AS ppID,
							et.name,
							et.icon
					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_position_eventtype AS pet
					INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_eventtype AS et ON et.id=pet.eventtype_id
					INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_event AS me ON et.id=me.event_type_id
					WHERE me.project_id='.$this->projectid;
		if ($positionId > 0)
		{
			$query .= ' AND pet.position_id='.(int)$positionId;
		}
		$query .= ' ORDER BY pet.ordering';
		$this->_db->setQuery($query);
		$result=$this->_db->loadObjectList();
		if ($result)
		{
			if ($positionId) {
				return $result;
			} else {
				$posEvents=array();
				foreach ($result as $r)
				{
					//$posEvents[$r->position_id][]=$r;
					$posEvents[$r->ppID][]=$r;
				}
				return ($posEvents);
			}
		}
		return array();
	}

	/**
	 * get person history across all projects,with team,season,position,... info
	 *
	 * @param int $person_id,linked to player_id from Person object
	 * @param int $order ordering for season and league,default is ASC ordering
	 * @param string $filter e.g. "s.name=2007/2008",default empty string
	 * @return array of objects
	 */
	function getPlayerHistory($sportstype=0, $order='ASC')
	{
	   $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        // Create a new query object.		
	   $db = JFactory::getDBO();
	   $query = $db->getQuery(true);
    
    $query->select('pr.id AS pid,pr.firstname,pr.lastname');
    $query->select('tp.person_id,tp.id AS tpid,tp.project_position_id,tp.market_value');
    $query->select('p.name AS project_name,CASE WHEN CHAR_LENGTH(p.alias) THEN CONCAT_WS(\':\',p.id,p.alias) ELSE p.id END AS project_slug');
    $query->select('s.name AS season_name');
    $query->select('t.name AS team_name,t.id AS team_id,CASE WHEN CHAR_LENGTH(t.alias) THEN CONCAT_WS(\':\',t.id,t.alias) ELSE t.id END AS team_slug');
    $query->select('pos.name AS position_name,pos.id AS posID');
    $query->select('pt.id AS ptid,pt.project_id');
	$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS pr'); 
	$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_person_id AS tp ON tp.person_id = pr.id'); 
	$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON pt.team_id = tp.team_id'); 
	$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t ON t.id = pt.team_id'); 
	$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS p ON p.id = pt.project_id'); 
	$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season AS s ON s.id = p.season_id'); 
	$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_league AS l ON l.id = p.league_id'); 
	$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS ppos ON ppos.id = tp.project_position_id'); 
	$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS pos ON pos.id = ppos.position_id');        
    
        $query->where('pr.id='.$db->Quote($this->personid));
        $query->where('p.published = 1');
        $query->where('pr.published = 1');
        $query->where('tp.persontype = 1'); 
        if ($sportstype > 0)
			{
				$query->where('p.sports_type_id = '.$db->Quote($sportstype));
			}
     $query->order('s.ordering ' . $order .',l.ordering ASC,p.name ASC ');
     
     $db->setQuery($query);
 	$this->_playerhistory = $db->loadObjectList();
    if ( !$this->_playerhistory )
        {
            $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
        }  
       

        
		return $this->_playerhistory;
	}

	// new insert for staff
	function getPlayerHistoryStaff($sportstype=0, $order='ASC')
	{
	   $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        // Create a new query object.		
	   $db = JFactory::getDBO();
	   $query = $db->getQuery(true);
       
$query->select('pr.id AS pid,pr.firstname,pr.lastname');
    $query->select('tp.person_id,tp.id AS tpid,tp.project_position_id,tp.market_value');
    $query->select('p.name AS project_name,CASE WHEN CHAR_LENGTH(p.alias) THEN CONCAT_WS(\':\',p.id,p.alias) ELSE p.id END AS project_slug');
    $query->select('s.name AS season_name');
    $query->select('t.name AS team_name,t.id AS team_id,CASE WHEN CHAR_LENGTH(t.alias) THEN CONCAT_WS(\':\',t.id,t.alias) ELSE t.id END AS team_slug');
    $query->select('pos.name AS position_name,pos.id AS posID');
    $query->select('pt.id AS ptid,pt.project_id');
    $query->select('ppos.position_id');
	$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS pr'); 
	$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_person_id AS tp ON tp.person_id = pr.id'); 
	$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON pt.team_id = tp.team_id'); 
	$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t ON t.id = pt.team_id'); 
	$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS p ON p.id = pt.project_id'); 
	$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season AS s ON s.id = p.season_id'); 
	$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_league AS l ON l.id = p.league_id'); 
	$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS ppos ON ppos.id = tp.project_position_id'); 
	$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS pos ON pos.id = ppos.position_id');        
    
        $query->where('pr.id='.$db->Quote($this->personid));
        $query->where('p.published = 1');
        $query->where('pr.published = 1');
        $query->where('tp.persontype = 2'); 
        if ($sportstype > 0)
			{
				$query->where('p.sports_type_id = '.$db->Quote($sportstype));
			}
     $query->order('s.ordering ' . $order .',l.ordering ASC,p.name ASC ');
     
     $db->setQuery($query);
 	$this->_playerhistorystaff = $db->loadObjectList();
    if ( !$this->_playerhistorystaff )
        {
            $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
        }  


        
		return $this->_playerhistorystaff;
	}

	function getContactID($catid)
	{
		$person=$this->getPerson();
		$query='	SELECT	id
					FROM #__contact_details
					WHERE user_id='.$person->jl_user_id.'
					AND catid='.$catid;
		$this->_db->setQuery($query);
		$contact_id=$this->_db->loadResult();
		return $contact_id;
	}

	function getRounds($roundcodestart,$roundcodeend)
	{
		$projectid=$this->projectid;
		$thisround=0;
		$query="	SELECT	id
					FROM #__".COM_SPORTSMANAGEMENT_TABLE."_round
					WHERE project_id='".(int)$projectid."'
					AND roundcode>='".(int)$roundcodestart."'
					AND roundcode<='".(int)$roundcodeend."'
					ORDER BY round_date_first";
		$this->_db->setQuery($query);
		$rows=$this->_db->loadResultArray();
		$rounds=array();
		if (count($rows) > 0)
		{
			$startround =& $this->getTable('Round','Table');
			$startround->load($rows[0]);
			$rounds[0]=$startround;
			$endround =& $this->getTable('Round','Table');
			$endround->load(end($rows));
			$rounds[1]=$endround;
		}
		return $rounds;
	}

	/**
	 * get all events associated to positions the player was assigned too in different projects
	 *
	 * @return array of event types row objects.
	 */
	function getAllEvents($sportstype=0)
	{
		$history = self::getPlayerHistory($sportstype);
		$positionhistory = array();
		foreach($history as $h)
		{
			if (!in_array($h->posID,$positionhistory) && $h->posID!=null)
			{
				$positionhistory[]=$h->posID;
			}
		}
		if (!count($positionhistory))
		{
			return array();
		}
		$query='	SELECT DISTINCT	et.*
					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_eventtype AS et
					INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_position_eventtype AS pet ON pet.eventtype_id=et.id
					INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS ppos ON ppos.position_id=pet.position_id
					WHERE published=1 
					  AND pet.position_id IN ('. implode(',',$positionhistory) .') 
					ORDER BY pet.ordering ';
		$this->_db->setQuery($query);
		$info=$this->_db->loadObjectList();
		return $info;
	}
    
    function getTimePlayed($player_id,$game_regular_time,$match_id = NULL,$cards=NULL)
    {
    $mainframe = JFactory::getApplication();
    
    //$mainframe->enqueueMessage(JText::_('player_id -> '.'<pre>'.print_r($player_id,true).'</pre>' ),'');
    //$mainframe->enqueueMessage(JText::_('game_regular_time -> '.'<pre>'.print_r($game_regular_time,true).'</pre>' ),'');
    //$mainframe->enqueueMessage(JText::_('match_id -> '.'<pre>'.print_r($match_id,true).'</pre>' ),'');
    //$mainframe->enqueueMessage(JText::_('cards -> '.'<pre>'.print_r($cards,true).'</pre>' ),'');
    
    $result = 0;
    // startaufstellung ohne ein und auswechselung
    $query = 'SELECT count(match_id) as totalmatch
			FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_player 
			WHERE teamplayer_id = '.$player_id.' and came_in = 0'; 
    
    if ( $match_id )
    {
    $query .= ' and match_id = '.$match_id;     
    }   
            
    $this->_db->setQuery($query);
    $totalresult = $this->_db->loadObject();
    //$mainframe->enqueueMessage(JText::_('totalresult -> '.'<pre>'.print_r($totalresult,true).'</pre>' ),'');
    if ( $totalresult )
    {
    $result += $totalresult->totalmatch * $game_regular_time;
    }
    
    // einwechselung
    $query='SELECT count(match_id) as totalmatch, SUM(in_out_time) as totalin
			FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_player 
			WHERE teamplayer_id = '.$player_id.' and came_in = 1 and in_for IS NOT NULL';    
    if ( $match_id )
    {
    $query .= ' and match_id = '.$match_id;     
    }
    $this->_db->setQuery($query);
    $cameinresult = $this->_db->loadObject();
    //$mainframe->enqueueMessage(JText::_('cameinresult -> '.'<pre>'.print_r($cameinresult,true).'</pre>' ),'');
    if ( $cameinresult )
    {
    $result += ( $cameinresult->totalmatch * $game_regular_time ) - ( $cameinresult->totalin );
    }
    
    // auswechselung
    $query='SELECT count(match_id) as totalmatch, SUM(in_out_time) as totalout
			FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_player 
			WHERE in_for = '.$player_id.' and came_in = 1 ';    
    if ( $match_id )
    {
    $query .= ' and match_id = '.$match_id;     
    }
    $this->_db->setQuery($query);
    $cameautresult = $this->_db->loadObject();
    //$mainframe->enqueueMessage(JText::_('cameautresult -> '.'<pre>'.print_r($cameautresult,true).'</pre>' ),'');
    if ( $cameautresult )
    {
    // bug erkannt durch @player2000    
    //$result += ( $cameautresult->totalout );
    $result += ( $cameautresult->totalout ) - ( $cameautresult->totalmatch * $game_regular_time );
    }
    
    // jetzt muss man noch die karten berücksichtigen, die zu einer hinausstellung führen
    if ( $cards )
    {
    $query = 'SELECT *
			FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_event 
			WHERE teamplayer_id = '.$player_id;
    $query .= ' and event_type_id in ('.$cards.')';
    if ( $match_id )
    {
    $query .= ' and match_id = '.$match_id;     
    }
    $this->_db->setQuery($query);
    $cardsresult = $this->_db->loadObjectList();  
    foreach ( $cardsresult as $row )
    {
        $result -= ( $game_regular_time - $row->event_time );
    }  
    //$mainframe->enqueueMessage(JText::_('cardsresult -> '.'<pre>'.print_r($cardsresult,true).'</pre>' ),'');
    }
    
    
    //$mainframe->enqueueMessage(JText::_('result -> '.'<pre>'.print_r($result,true).'</pre>' ),'');
    
    return $result;    
    }

	function getInOutStats($project_id, $projectteam_id, $teamplayer_id)
	{
		// Determine person_id from the teamplayer_id
		$query 	= ' SELECT pr.id'
				. ' FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_team_player AS tp'
				. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS pr ON pr.id=tp.person_id'
				. ' WHERE tp.id='.$this->_db->Quote($teamplayer_id);
		$this->_db->setQuery($query);
		$person_id = $this->_db->loadResult();

		// To be robust against partly filled in information for a match (match player, statistic, event)
		// we determine if a player was contributing to a match, by checking for the following conditions:
		// 1. the player is registered as a player for the match
		// 2. the player has a statistic registered for the match
		// 3. the player has an event registered for the match
		// If any of these conditions are met, we assume the player was part of the match
		$common_query_part 	= ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_match AS m ON m.id = md.match_id'
							. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team_player AS tp ON tp.id = md.teamplayer_id'
							. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON pt.id=tp.projectteam_id'
							. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS p ON p.id=pt.project_id'
							. ' WHERE tp.id='.$this->_db->Quote($teamplayer_id)
							. '   AND pt.id='.$this->_db->Quote($projectteam_id)
							. '   AND p.id='.$this->_db->Quote($project_id);

		// Use md (stands for match detail, where the detail can be a match_player, match_statistic or match_event)
		// All of them have a match_id and teamplayer_id.
		$query_mp = ' SELECT m.id AS mid, tp.person_id'
				  . ' FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_player AS md'
				  . $common_query_part
				  . ' AND (md.came_in = 0 || md.came_in = 1)'
				  . ' GROUP BY m.id';

/*
// blödsinn diddipoeler
		$query_ms = ' SELECT m.id AS mid, tp.person_id'
				  . ' FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_statistic AS md'
				  . $common_query_part
				  . ' GROUP by m.id';

		$query_me = ' SELECT m.id AS mid, tp.person_id'
				  . ' FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_event AS md'
				  . $common_query_part
				  . ' GROUP BY m.id';
                  
        $query 	  = ' SELECT COUNT(m.id)'
				  . ' FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match AS m'
				  . ' LEFT JOIN ('.$query_mp.') AS mp ON mp.mid = m.id'
				  . ' LEFT JOIN ('.$query_ms.') AS ms ON ms.mid = m.id'
				  . ' LEFT JOIN ('.$query_me.') AS me ON me.mid = m.id'
				  . ' WHERE mp.person_id = '.$this->_db->Quote($person_id)
				  . '    OR ms.person_id = '.$this->_db->Quote($person_id)
				  . '    OR me.person_id = '.$this->_db->Quote($person_id);          
*/

		$query 	  = ' SELECT COUNT(m.id)'
				  . ' FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match AS m'
				  . ' LEFT JOIN ('.$query_mp.') AS mp ON mp.mid = m.id'
				  . ' WHERE mp.person_id = '.$this->_db->Quote($person_id);

		$this->_db->setQuery($query);
		$played = $this->_db->loadResult();

		// Get the substitution statistics for those matches (started, sub_in, sub_out)
		$query = '	SELECT	m.id AS mid, mp.came_in, mp.out, mp.teamplayer_id, mp.in_for
				FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match AS m
				INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_player AS mp ON mp.match_id=m.id 
				INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt1 ON m.projectteam1_id=pt1.id 
				INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team_player AS tp ON (tp.id=mp.teamplayer_id OR tp.id=mp.in_for)
				INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t1 ON t1.id=pt1.team_id 
				INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt2 ON m.projectteam2_id=pt2.id 
				INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t2 ON t2.id=pt2.team_id 
				INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS p ON p.id=pt1.project_id 
				WHERE tp.id='.$this->_db->Quote((int)$teamplayer_id).' 
				  AND (pt1.project_id='.$this->_db->Quote((int)$project_id).' 
				       OR pt2.project_id='.$this->_db->Quote((int)$project_id).')
				  AND (pt1.id = '.$this->_db->Quote((int)$projectteam_id).'
				       OR pt2.id = '.$this->_db->Quote((int)$projectteam_id).')
				  AND m.published = 1 
				  AND p.published = 1 
				  ';
		$this->_db->setQuery($query);
		$rows = $this->_db->loadObjectList();

		$inoutstat = new stdclass;
		$inoutstat->played = $played;
		$inoutstat->started = 0;
		$inoutstat->sub_in = 0;
		$inoutstat->sub_out = 0;
		foreach ($rows AS $row)
		{
			$inoutstat->started += ($row->came_in == 0);
			$inoutstat->sub_in  += ($row->came_in == 1) && ($row->teamplayer_id == $teamplayer_id);
			$inoutstat->sub_out += ($row->out == 1) || ($row->in_for == $teamplayer_id);
		}
		return $inoutstat;
	}

	/**
	 * get stats for the player position (the person can be player of multiple teams in the project due to transfer(s))
	 * @return array
	 */
	function getStats()
	{
		$stats = array();
		$players = self::getTeamPlayer();
		if (is_array($players))
		{
			foreach ($players as $player)
			{
				// Remark: we cannot use array_merge because numerical keys will result in duplicate entries
				// so we check if a key already exists in the output array before adding it.
				$projectStats = sportsmanagementModelProject::getProjectStats(0,$player->position_id);
				if (is_array($projectStats))
				{
					foreach ($projectStats as $key=>$projectStat)
					{
						if (!array_key_exists($key, $stats))
						{
							$stats[$key] = $projectStat;
						}
					}
				}
			}
		}
		return $stats;
	}

	/**
	 * get all statistics types that are in use for the player in his/her whole career
	 * @return array
	 */
	function getCareerStats($person_id, $sports_type_id)
	{
		if (empty($this->_careerStats))
		{
			$query = 'SELECT s.id,'
				. ' s.name,'
				. ' s.short,'
				. ' s.class,'
				. ' s.icon,'
				. ' s.calculated,'
				. ' ppos.id AS pposid,'
				. ' ppos.position_id AS position_id,'
				. ' s.params,'
				. ' s.baseparams'
				. ' FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS p'
				. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team_player AS tp ON tp.person_id = p.id'
				. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS ppos ON tp.project_position_id = ppos.id'
				. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS pos ON ppos.position_id = pos.id'
				. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_position_statistic AS ps ON ps.position_id = pos.id'
				. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_statistic AS s ON ps.statistic_id = s.id'
				. ' WHERE p.id='.$this->_db->Quote($person_id);
			if ($sports_type_id > 0)
			{
				$query.= ' AND pos.sports_type_id='.$this->_db->Quote($sports_type_id);
			}
			$query.= ' GROUP BY s.id';

			$this->_db->setQuery($query);
			$this->_careerStats=$this->_db->loadObjectList();
		}
		$stats=array();
		if (count($this->_careerStats) > 0)
		{
			foreach ($this->_careerStats as $k => $row)
			{
				$stat=&JLGStatistic::getInstance($row->class);
				$stat->bind($row);
				$stat->set('position_id',$row->position_id);
				$stats[$row->id]=$stat;
			}
		}
		return $stats;
	}

	/**
	 * get player stats per game (one array entry per statistic ID)
	 * @return array
	 */
	function getPlayerStatsByGame()
	{
		$teamplayers = self::getTeamPlayers();
		$displaystats=array();
		if (count($teamplayers))
		{
			$project = sportsmanagementModelProject::getProject();
			$project_id = $project->id;
			// Determine teamplayer id(s) of the player (plural if (s)he played in multiple teams of the project
			// and the position_id(s) where the player played
			$teamplayer_ids = array();
			$position_ids = array();
			foreach ($teamplayers as $teamplayer)
			{
				$teamplayer_ids[] = $teamplayer->id;
				if (!in_array($teamplayer->position_id, $position_ids))
				{
					$position_ids[] = $teamplayer->position_id;
				}
			}
			// For each position_id get the statistics types and merge the results (prevent duplicate statistics ids)
			// ($pos_stats is an array indexed by statistic_id)
			$pos_stats = array();
			foreach ($position_ids as $position_id)
			{
				$stats_for_position_id = sportsmanagementModelProject::getProjectStats(0, $position_id);
				foreach ($stats_for_position_id as $id => $stat)
				{
					if (!array_key_exists($id, $pos_stats))
					{
						$pos_stats[$id] = $stat;
					}
				}
			}
			foreach ($pos_stats as $stat)
			{
				if(!empty($stat))
				{
					if ($stat->showInSingleMatchReports())
					{
						$stat->set('gamesstats',$stat->getPlayerStatsByGame($teamplayer_ids, $project_id));
						$displaystats[]=$stat;
					}
				}
			}
		}
		return $displaystats;
	}

	/**
	 * get player stats per project (one array entry per statistic ID)
	 * @return array
	 */
	function getPlayerStatsByProject($sportstype=0)
	{
		$teamplayer = self::getTeamPlayer();
		$result=array();
		if (is_array($teamplayer) && !empty($teamplayer))
		{
			// getTeamPlayer can return multiple teamplayers, because a player can be transferred from 
			// one team to another inside a season, but they are all the same person so have same person_id.
			// So we get the player_id from the first array entry.
			$stats  = self::getCareerStats($teamplayer[0]->person_id, $sportstype);
			$history = self::getPlayerHistory($sportstype);
			if(count($history)>0)
			{
				foreach ($stats as $stat)
				{
					if(!empty($stat))
					{
						foreach ($history as $player)
						{
							$result[$stat->id][$player->project_id][$player->ptid]=$stat->getPlayerStatsByProject($player->person_id, $player->ptid, $player->project_id, $sportstype);
						}
						$result[$stat->id]['totals'] = $stat->getPlayerStatsByProject($player->person_id, 0, 0, $sportstype);
					}
				}
			}
		}
		return $result;
	}

	function getGames()
	{
	   $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        // Create a new query object.		
	   $db = JFactory::getDBO();
	   $query = $db->getQuery(true);
       
       
		$teamplayers = self::getTeamPlayers();
		$games = array();
        
        $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' teamplayers<br><pre>'.print_r($teamplayers,true).'</pre>'),'Error');
        
		if (count($teamplayers))
		{
			$quoted_tpids=array();
			foreach ($teamplayers as $teamplayer)
			{
				$quoted_tpids[] = $this->_db->Quote($teamplayer->id);
			}
			$tpid_list = '('.implode(',', $quoted_tpids).')';

			// Get all games played by the player (possible of multiple teams in the project)
			// A player was in a match if:
			// 1. He is defined as a match player in the match
			// 2. There is one or more statistic on his name for the match
			// 3. There is one or more event on his name for the match
/*
			$common_query_part 	= ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_match AS m ON m.id = md.match_id '
								. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team_player AS tp ON tp.id = md.teamplayer_id '
								. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON pt.id = tp.projectteam_id '
								. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS p ON p.id = pt.project_id '
								. ' WHERE tp.id IN '.$tpid_list;

*/
			$common_query_part 	= ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_match AS m ON m.id = md.match_id '
								. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_person_id AS tp ON tp.id = md.teamplayer_id '
								. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON pt.team_id = tp.team_id '
								. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS p ON p.id = pt.project_id '
								. ' WHERE tp.id IN '.$tpid_list;
/*                                
            $query_mp = ' SELECT m.id AS mid, tp.id AS tpid, '
					  . ' sum(IF(md.came_in=0,1,0)) AS started, '
					  . ' sum(IF(md.teamplayer_id IN '.$tpid_list.' AND md.came_in=1,1,0)) AS sub_in,'
					  . ' sum(IF((md.teamplayer_id IN '.$tpid_list.' AND md.out=1) '
					  . ' OR (md.in_for IN '.$tpid_list.'),1,0)) AS sub_out,'
					  . ' tp.projectteam_id'
					  . ' FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_player AS md '
					  . ' LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team_player AS tp_in ON tp_in.id = md.in_for '
					  . $common_query_part
					  . '   OR tp_in.id IN '.$tpid_list.' '
					  . '   AND (md.came_in = 0 || md.came_in = 1) '
					  . '   AND (md.teamplayer_id IN '.$tpid_list.' OR md.in_for IN '.$tpid_list.')'
					  . ' GROUP BY m.id';
*/

  
			$query_mp = ' SELECT m.id AS mid, tp.id AS tpid, '
					  . ' sum(IF(md.came_in=0,1,0)) AS started, '
					  . ' sum(IF(md.teamplayer_id IN '.$tpid_list.' AND md.came_in=1,1,0)) AS sub_in,'
					  . ' sum(IF((md.teamplayer_id IN '.$tpid_list.' AND md.out=1) '
					  . ' OR (md.in_for IN '.$tpid_list.'),1,0)) AS sub_out,'
					  . ' pt.id as projectteam_id'
					  . ' FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_player AS md '
					  . ' LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_person_id AS tp_in ON tp_in.id = md.in_for '
					  . $common_query_part
					  . '   OR tp_in.id IN '.$tpid_list.' '
					  . '   AND (md.came_in = 0 || md.came_in = 1) '
					  . '   AND (md.teamplayer_id IN '.$tpid_list.' OR md.in_for IN '.$tpid_list.')'
					  . ' GROUP BY m.id';		  
			
            $query 	= ' SELECT m.*,'
					. ' t1.id AS team1,'
					. ' t2.id AS team2, '
					. ' r.roundcode, '
					. ' r.project_id, '
					. ' COALESCE(mp.started,0) as started,'
					. ' COALESCE(mp.sub_in, 0) as sub_in,'
					. ' COALESCE(mp.sub_out,0) as sub_out,'
					. ' mp.projectteam_id AS projectteam_id'
					. ' FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match AS m '
					. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_round r ON m.round_id=r.id '
					. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS p ON p.id=r.project_id '
					. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt1 ON m.projectteam1_id=pt1.id '
					. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t1 ON t1.id=pt1.team_id '
					. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt2 ON m.projectteam2_id=pt2.id '
					. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t2 ON t2.id=pt2.team_id' 
					. ' LEFT JOIN ('.$query_mp.') AS mp ON mp.mid = m.id '
					. ' WHERE (mp.tpid IN '.$tpid_list.' )'
					. '   AND m.published = 1 '
					. '   AND p.published = 1 '
					. ' ORDER BY m.match_date';

			$this->_db->setQuery($query);
			$games =  $this->_db->loadObjectList();
		}
        
        if ( !$games )
        {
            $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
        }
        
        
        
		return $games;
	}

	function getGamesEvents()
	{
	   $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        // Create a new query object.		
	   $db = JFactory::getDBO();
	   $query = $db->getQuery(true);
       
       
		$teamplayers = self::getTeamPlayers();
		$gameevents = array();
        
        $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' teamplayers<br><pre>'.print_r($teamplayers,true).'</pre>'),'Error');
        
		if (count($teamplayers))
		{
			$quoted_tpids = array();
			foreach ($teamplayers as $teamplayer)
			{
				$quoted_tpids[]=$this->_db->Quote($teamplayer->id);
			}
			$query='SELECT	SUM(me.event_sum) as value,
					me.*,
					me.match_id
				FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_event AS me
				WHERE me.teamplayer_id IN ('. implode(',', $quoted_tpids) .')
				GROUP BY me.match_id, me.event_type_id';
			$this->_db->setQuery($query);
			$events = $this->_db->loadObjectList();
            
            $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' events<br><pre>'.print_r($events,true).'</pre>'),'Error');
            
			foreach ((array) $events as $ev)
			{
				if (isset($gameevents[$ev->match_id]))
				{
					$gameevents[$ev->match_id][$ev->event_type_id]=$ev->value;
				}
				else
				{
					$gameevents[$ev->match_id]=array($ev->event_type_id => $ev->value);
				}
			}
		}
		return $gameevents;
	}
    
    
    
    

}

?>