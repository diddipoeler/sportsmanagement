<?php 
/** SportsManagement ein Programm zur Verwaltung f?r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: ? 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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
* SportsManagement ist Freie Software: Sie k?nnen es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder sp?teren
* ver?ffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es n?tzlich sein wird, aber
* OHNE JEDE GEW?HELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gew?hrleistung der MARKTF?HIGKEIT oder EIGNUNG F?R EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License f?r weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

//require_once( JLG_PATH_SITE . DS . 'models' . DS . 'project.php' );

/**
 * sportsmanagementModelRoster
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelRoster extends JModel
{
	var $projectid = 0;
	var $projectteamid = 0;
	var $projectteam = null;
	var $team = null;
    var $seasonid = 0;

	/**
	 * caching for team in out stats
	 * @var array
	 */
	var $_teaminout = null;

	/**
	 * caching players
	 * @var array
	 */
	var $_players = null;

	/**
	 * sportsmanagementModelRoster::__construct()
	 * 
	 * @return
	 */
	function __construct()
	{
		parent::__construct();

		$this->projectid = JRequest::getInt('p',0);
		$this->teamid = JRequest::getInt('tid',0);
		$this->projectteamid = JRequest::getInt('ttid',0);
		self::getProjectTeam();
	}

	
	/**
	 * sportsmanagementModelRoster::getProjectTeam()
	 * 
	 * @return
	 */
	function getProjectTeam()
	{
	   $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
       // Create a new query object.		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
        
		if (is_null($this->projectteam))
		{
			if ($this->projectteamid)
			{
			 $query = $db->getQuery(true);
             $query->clear();
				$query->select('pt.*');
	           $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt'); 
                $query->where('pt.id = '.$db->Quote($this->projectteamid));
    
                
			}
			else
			{
				if (!$this->teamid)
				{
					$this->setError(JText::_('Missing team id'));
					return false;
				}
				if (!$this->projectid)
				{
					$this->setError(JText::_('Missing project id'));
					return false;
				}
                
                $query = $db->getQuery(true);
                $query->clear();
                $query->select('pt.*');
	           $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt'); 
                $query->where('pt.team_id = '.$db->Quote($this->teamid));
                $query->where('pt.project_id = '.$db->Quote($this->projectid));
               
			}
			$db->setQuery($query);
			$this->projectteam = $db->loadObject();
			if ($this->projectteam)
			{
				$this->projectid = $this->projectteam->project_id; // if only ttid was set
				$this->teamid = $this->projectteam->team_id; // if only ttid was set
			}
		}
		return $this->projectteam;
	}

	/**
	 * sportsmanagementModelRoster::getTeam()
	 * 
	 * @return
	 */
	function getTeam()
	{
	   $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
       // Create a new query object.		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
        
		if (is_null($this->team))
		{
			if (!$this->teamid)
			{
				$this->setError(JText::_('Missing team id'));
				return false;
			}
			if (!$this->projectid)
			{
				$this->setError(JText::_('Missing project id'));
				return false;
			}
            
            $query->select('t.*');
            $query->select('CASE WHEN CHAR_LENGTH(t.alias) THEN CONCAT_WS(\':\',t.id,t.alias) ELSE t.id END AS slug');
	           $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t'); 
                $query->where('t.id = '.$db->Quote($this->teamid));
                
                        
			$db->setQuery($query);
			$this->team = $db->loadObject();
		}
		return $this->team;
	}

	
	/**
	 * sportsmanagementModelRoster::getTeamPlayers()
	 * 
	 * @param integer $persontype
	 * @return
	 */
	function getTeamPlayers($persontype = 1)
	{
	   $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
       // Create a new query object.		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
        
		$projectteam = self::getprojectteam();
		//if (empty($this->_players))
		//{
			
        // Select some fields
		$query->select('pr.firstname,pr.nickname,pr.lastname,pr.country,pr.birthday,pr.deathday,pr.id AS pid,pr.picture AS ppic');
        $query->select('tp.id AS playerid,tp.jerseynumber AS position_number,tp.notes AS description,tp.market_value AS market_value,tp.picture');    
        $query->select('pr.suspension AS suspension,pr.away AS away,pr.injury AS injury,pr.id AS pid,pr.picture AS ppic,CASE WHEN CHAR_LENGTH(pr.alias) THEN CONCAT_WS(\':\',pr.id,pr.alias) ELSE pr.id END AS slug');
        $query->select('pos.name AS position');
        $query->select('ppos.position_id,ppos.id as pposid');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_person_id AS tp ');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st ON st.team_id = tp.team_id');    
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON pt.team_id = st.id');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS pr ON tp.person_id = pr.id');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS ppos ON ppos.id = tp.project_position_id');
        switch ( $persontype )
        {
            case 1:
            $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS pos ON pos.id = ppos.position_id');
            break;
            case 2:
            $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS pos ON pos.id = ppos.position_id');
            $query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS posparent ON pos.parent_id = posparent.id');
            break;
        }
        
        $query->where('pt.id='.$db->Quote($projectteam->id));
        $query->where('pr.published = 1');
        $query->where('tp.published = 1');
        $query->where('tp.persontype = '.$persontype);
        $query->where('tp.season_id = '.$this->seasonid);  
        $query->order('pos.ordering, ppos.position_id, tp.ordering, tp.jerseynumber, pr.lastname, pr.firstname');
            

            
            $db->setQuery($query);
            $this->_players = $db->loadObjectList();
            
            if ( !$this->_players && COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
            {
            $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');    
            }
            elseif ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
            {
                $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($query->dump(),true).'</pre>'),'');
            }
            
		//}
        switch ( $persontype )
        {
            case 1:
            $bypos = array();
		      foreach ($this->_players as $player)
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
            return $this->_players;
            break;
        }
        
		
	}


	/**
	 * sportsmanagementModelRoster::getPositionEventTypes()
	 * 
	 * @param integer $positionId
	 * @return
	 */
	function getPositionEventTypes($positionId=0)
	{
		$mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
       // Create a new query object.		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
                
        $result = array();
        // Select some fields
		$query->select('pet.*');
        $query->select('ppos.id AS pposid,ppos.position_id');    
        $query->select('et.name AS name,et.icon AS icon');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_position_eventtype AS pet');    
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_eventtype AS et ON et.id = pet.eventtype_id');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS ppos ON ppos.position_id = pet.position_id');
        $query->where('ppos.project_id = '.$this->projectid);
        $query->where('et.published = 1');

		if ($positionId > 0)
		{
		  $query->where('pet.position_id = '.(int)$positionId);
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
				$posEvents=array();
				foreach ($result as $r)
				{
					$posEvents[$r->position_id][]=$r;
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
	function getPlayerEventStats()
	{
		$playerstats=array();
		$rows = self::getTeamPlayers();
		if (!empty($rows))
		{
			$positioneventtypes = self::getPositionEventTypes();
			//init
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
					$teamstats = self::getTeamEventStat($eventtype->eventtype_id);
					if(isset($rows[$position])) {
						foreach ($rows[$position] as $player)
						{
							$playerstats[$player->pid][$eventtype->eventtype_id]=(isset($teamstats[$player->pid]) ? $teamstats[$player->pid]->total : 0);
						}
					}
				}
			}
		}
		return $playerstats;
	}
    
    /**
     * sportsmanagementModelRoster::getTimePlayed()
     * 
     * @param mixed $player_id
     * @param mixed $game_regular_time
     * @param mixed $match_id
     * @param mixed $cards
     * @return
     */
    function getTimePlayed($player_id,$game_regular_time,$match_id=NULL,$cards=NULL)
    {
        $mainframe = JFactory::getApplication();
    $option = JRequest::getCmd('option');
        // Create a new query object.		
	   $db = JFactory::getDBO();
	   $query = $db->getQuery(true);
       
        //$mainframe->enqueueMessage(JText::_('player_id -> '.'<pre>'.print_r($player_id,true).'</pre>' ),'');
    //    $mainframe->enqueueMessage(JText::_('game_regular_time -> '.'<pre>'.print_r($game_regular_time,true).'</pre>' ),'');
    
    $result = 0;
    // startaufstellung ohne ein und auswechselung
    $query->select('COUNT(distinct match_id) as totalmatch');
	$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_player'); 
    $query->where('teamplayer_id = '.$player_id);
    $query->where('came_in = 0');

    if ( $match_id )
    {
    $query->where('match_id = '.$match_id);
    }   
    
    //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' match_id -> '.'<pre>'.print_r($query,true).'</pre>' ),'');
            
    $db->setQuery($query);
    $totalresult = $db->loadObject();
    if ( $totalresult )
    {
    $result += $totalresult->totalmatch * $game_regular_time;
    }
    
    // einwechselung
    $query = $db->getQuery(true);    
    $query->clear();
    $query->select('count(distinct match_id) as totalmatch, SUM(in_out_time) as totalin');
	$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_player'); 
    $query->where('teamplayer_id = '.$player_id);
    $query->where('came_in = 1');
    $query->where('in_for IS NOT NULL');
       
    if ( $match_id )
    {
    $query->where('match_id = '.$match_id);
    } 
    $db->setQuery($query);
    $cameinresult = $db->loadObject();
    
    //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' cameinresult -> '.'<pre>'.print_r($cameinresult,true).'</pre>' ),'');
    if ( $cameinresult )
    {
    $result += ( $cameinresult->totalmatch * $game_regular_time ) - ( $cameinresult->totalin );
    }
    
    // auswechselung
    $query = $db->getQuery(true);    
    $query->clear();
    $query->select('count(distinct match_id) as totalmatch, SUM(in_out_time) as totalout');
	$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_player'); 
    $query->where('in_for = '.$player_id);
    $query->where('came_in = 1');
 
    if ( $match_id )
    {
    $query->where('match_id = '.$match_id);
    } 
    $db->setQuery($query);
    $cameautresult = $db->loadObject();
    
    //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' cameautresult -> '.'<pre>'.print_r($cameautresult,true).'</pre>' ),'');
    if ( $cameautresult )
    {
    // bug erkannt durch @player2000    
    //$result += ( $cameautresult->totalout );
    $result += ( $cameautresult->totalout ) - ( $cameautresult->totalmatch * $game_regular_time );
    }
    
    // jetzt muss man noch die karten berücksichtigen, die zu einer hinausstellung führen
    if ( $cards )
    {
        $query = $db->getQuery(true);    
    $query->clear();
    $query->select('*');
	$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_event '); 
    $query->where('teamplayer_id = '.$player_id);
    $query->where('event_type_id in ('.$cards.')');

    if ( $match_id )
    {
        $query->where('match_id = '.$match_id);
    
    }
    $db->setQuery($query);
    $cardsresult = $db->loadObjectList(); 
    foreach ( $cardsresult as $row )
    {
        $result -= ( $game_regular_time - $row->event_time );
    }   
    //$mainframe->enqueueMessage(JText::_('cardsresult -> '.'<pre>'.print_r($cardsresult,true).'</pre>' ),'');
    }
    
    //$mainframe->enqueueMessage(JText::_('result -> '.'<pre>'.print_r($result,true).'</pre>' ),'');
    
    return $result;    
    }

	/**
	 * sportsmanagementModelRoster::getTeamEventStat()
	 * 
	 * @param mixed $eventtype_id
	 * @return
	 */
	function getTeamEventStat($eventtype_id)
	{
		$mainframe = JFactory::getApplication();
    $option = JRequest::getCmd('option');
        // Create a new query object.		
	   $db = JFactory::getDBO();
	   $query = $db->getQuery(true);
       
        $projectteam = self::getprojectteam();
        $query->select('SUM(me.event_sum) as total');
        $query->select('tp.person_id');
	$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_event AS me'); 
    $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_person_id AS tp ON me.teamplayer_id = tp.id');
    $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st ON st.team_id = tp.team_id');  
    $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON pt.team_id = st.id');
    $query->where('me.event_type_id = '.$db->Quote($eventtype_id));
    $query->where('pt.id = '.$db->Quote($projectteam->id));
    $query->group('tp.person_id');
       
        $db->setQuery($query);
		$result = $db->loadObjectList('person_id');
        if ( !$result && COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
            $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' <br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
            $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Error');
        } 
        elseif ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
            $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Error');
        }
        
		return $result;
	}

	/**
	 * sportsmanagementModelRoster::getInOutStats()
	 * 
	 * @param mixed $player_id
	 * @return
	 */
	function getInOutStats($player_id)
	{
		$teaminout = self::_getTeamInOutStats();
		if (isset($teaminout[$player_id])) {
			return $teaminout[$player_id];
		}
		else {
			return null;
		}
	}

	/**
	 * sportsmanagementModelRoster::_getTeamInOutStats()
	 * 
	 * @return
	 */
	function _getTeamInOutStats($project_id = 0, $projectteam_id = 0, $teamplayer_id = 0)
	{
		$mainframe = JFactory::getApplication();
    $option = JRequest::getCmd('option');
        // Create a new query object.		
	   $db = JFactory::getDBO();
	   $query = $db->getQuery(true);
       $subquery1 = $db->getQuery(true);
       $subquery2 = $db->getQuery(true);
       
       if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
       {
       $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' project_id<br><pre>'.print_r($project_id,true).'</pre>'),'');
       $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' projectteam_id<br><pre>'.print_r($projectteam_id,true).'</pre>'),'');
       $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' teamplayer_id<br><pre>'.print_r($teamplayer_id,true).'</pre>'),'');
       }
        
        if ( empty($projectteam_id) )
        {
        $projectteam = self::getprojectteam();
        $projectteam_id = $db->Quote($projectteam->id);
        }
        
		if (empty($this->_teaminout))
		{
			//$projectteam_id = $db->Quote($projectteam->id);

			// Split the problem in two;
			// 1. Get the number of matches played per teamplayer of the projectteam.
			//    This is derived from three tables: match_player, match_statistic and match_event
			// 2. Get the in/out stats.
			//    This is derived from the match_player table only.

			// Sub 1: get number of matches played by teamplayers of the projectteam
            
            if ( $teamplayer_id )
            {
            
            }
            else
            {    
            $subquery1->select('m.id AS mid');
            $subquery1->select('tp.id AS tpid');
            $subquery1->select('pt.id as projectteam_id');
            $subquery1->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_player AS md');
            $subquery1->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_match AS m ON m.id = md.match_id');
            $subquery1->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_person_id AS tp ON tp.id = md.teamplayer_id');
            $subquery1->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st ON st.team_id = tp.team_id');  
            $subquery1->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON pt.team_id = st.id'); 
            $subquery1->where('pt.id = '.$projectteam_id);
            $subquery1->where('(md.came_in = 0 || md.came_in = 1)');
            
            $subquery2->select('m.id as mid');
            $subquery2->select('tp.id as tpid, tp.person_id');
            $subquery2->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match AS m');
            $subquery2->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_round r ON m.round_id = r.id ');
            $subquery2->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS p ON p.id = r.project_id');
            $subquery2->join('LEFT','('.$subquery1.') AS mp ON mp.mid = m.id');
            $subquery2->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_person_id AS tp ON tp.id = mp.tpid');
            $subquery2->where('mp.projectteam_id = '.$projectteam_id);
            $subquery2->where('m.published = 1');
            $subquery2->where('p.published = 1');
            
            $query->select('pse.person_id,COUNT(pse.mid) AS played');
            $query->select('0 AS started,0 AS sub_in,0 AS sub_out');
            $query->from('('.$subquery2.') AS pse');
            $query->group('pse.tpid');
            }
                    
			$db->setQuery($query);
			$this->_teaminout = $db->loadObjectList('person_id');
            
            if ( !$this->_teaminout && COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
            $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' <br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
        }
        elseif ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
            $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'');
        }
        
        // Sub 2: get the in/out stats
        $query = $db->getQuery(true); 
        $query->clear();
        
        //$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match AS m'); 
        
        if ( $teamplayer_id )
        {
        $query->select('m.id AS mid, mp.came_in, mp.out, mp.teamplayer_id, mp.in_for');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match AS m'); 
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_player AS mp ON mp.match_id = m.id');
        
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt1 ON m.projectteam1_id = pt1.id');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st1 ON st1.id = pt1.team_id'); 
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt2 ON m.projectteam2_id = pt2.id');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st2 ON st2.id = pt2.team_id');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS p ON p.id = pt1.project_id ');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_person_id AS tp1 ON ( tp1.id = mp.teamplayer_id OR tp1.id = mp.in_for)');
        
        $query->where('tp1.id='.$db->Quote((int)$teamplayer_id));
        
        $query->where('( pt1.project_id='.$db->Quote((int)$project_id).' OR pt2.project_id='.$db->Quote((int)$project_id).' )');
        $query->where('( pt1.id = '.$db->Quote((int)$projectteam_id).' OR pt2.id = '.$db->Quote((int)$projectteam_id).' )');
        $query->where('m.published = 1');
        $query->where('p.published = 1');
        }
        else
        {    
        $query->select('tp1.id AS tp_id1,tp1.person_id AS person_id1');
        $query->select('tp2.id AS tp_id2,tp2.person_id AS person_id2');
        $query->select('m.id AS mid,mp.came_in, mp.out, mp.in_for');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match AS m'); 
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_round r ON m.round_id = r.id ');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS p ON p.id = r.project_id');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_player AS mp ON mp.match_id = m.id');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_person_id AS tp1 ON tp1.id = mp.teamplayer_id');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st ON st.team_id = tp1.team_id');  
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON pt.team_id = st.id');
        $query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_person_id AS tp2 ON tp2.id = mp.in_for');
        $query->where('pt.id = '.$db->Quote($projectteam->id));
        $query->where('m.published = 1');
        $query->where('p.published = 1');
        }
        
        //$query->where('m.published = 1');
        //$query->where('p.published = 1');

			$db->setQuery($query);
			$rows = $db->loadObjectList();
            
            if ( !$rows && COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
            $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' rows<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
        } 
        elseif ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
            $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Error');
        }
        
			
            if ( $teamplayer_id )
            {
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
              $this->_teaminout = $inoutstat;   
            }
            else
            {
            foreach ($rows AS $row)
			{
				$this->_teaminout[$row->person_id1]->started += ($row->came_in == 0);
				$this->_teaminout[$row->person_id1]->sub_in  += ($row->came_in == 1);
				$this->_teaminout[$row->person_id1]->sub_out += ($row->out == 1);

				// Handle the second player tp2 (only applicable when one goes out AND another comes in; tp2 is the player that goes out)
				if (isset($row->person_id2))
				{
					$this->_teaminout[$row->person_id2]->sub_out++;
				}
            }
            }
		}

		return $this->_teaminout;
	}

	
	/**
	 * sportsmanagementModelRoster::getTeamPlayer()
	 * 
	 * @param mixed $round_id
	 * @param mixed $player_id
	 * @return
	 */
	function getTeamPlayer($round_id,$player_id)
	{
	   $mainframe = JFactory::getApplication();
    $option = JRequest::getCmd('option');
        // Create a new query object.		
	   $db = JFactory::getDBO();
	   $query = $db->getQuery(true);
       
		$query="	SELECT	tp.injury AS injury,
							tp.suspension AS suspension,
							tp.away AS away, pt.picture,
							ppos.id As pposid,
							pos.id AS position_id
					FROM #__".COM_SPORTSMANAGEMENT_TABLE."_team_player AS tp
					INNER JOIN #__".COM_SPORTSMANAGEMENT_TABLE."_person AS pr ON tp.person_id=pr.id
					INNER JOIN #__".COM_SPORTSMANAGEMENT_TABLE."_project_team AS pt ON pt.id=tp.projectteam_id
					INNER JOIN #__".COM_SPORTSMANAGEMENT_TABLE."_round AS r ON r.project_id=pt.project_id
					INNER JOIN #__".COM_SPORTSMANAGEMENT_TABLE."_project_position AS ppos ON ppos.id=tp.project_position_id
					INNER JOIN #__".COM_SPORTSMANAGEMENT_TABLE."_position AS pos ON pos.id=ppos.position_id
					WHERE r.id=".$round_id." 
					  AND tp.id=".$player_id." 
					  AND pr.published = '1'
					  AND tp.published = '1'
					  ";
                      
                      
		$this->_db->setQuery($query);
		$rows = $this->_db->loadObjectList();
		return $rows;
	}

	/**
	 * sportsmanagementModelRoster::getRosterStats()
	 * 
	 * @return
	 */
	function getRosterStats()
	{
		$stats = sportsmanagementModelProject::getProjectStats();
		$projectteam = self::getprojectteam();
		$result=array();
		foreach ($stats as $pos => $pos_stats)
		{
			foreach ($pos_stats as $k => $stat)
			{
				$result[$pos][$stat->id]=$stat->getRosterStats($projectteam->team_id,$projectteam->project_id, $pos);
			}
		}
		return $result;
	}
    
    /**
     * sportsmanagementModelRoster::getLastSeasonDate()
     * 
     * @return
     */
    function getLastSeasonDate()
    {
        $mainframe = JFactory::getApplication();
    $option = JRequest::getCmd('option');
        // Create a new query object.		
	   $db = JFactory::getDBO();
	   $query = $db->getQuery(true);
       
       $query->select('max(round_date_last)');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'__round '); 
        $query->where('project_id ='.$this->projectid);
        
//        $query='SELECT max(round_date_last) FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_round 
//					WHERE project_id='.$this->projectid;
                    
        $db->setQuery($query);
        if (!$result = $db->loadResult())
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		return $result;
        
    }

}
?>