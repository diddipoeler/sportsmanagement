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
require_once('player.php');

/**
 * sportsmanagementModelRoster
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelRoster extends JModelLegacy
{
	var $projectid = 0;
	var $projectteamid = 0;
	var $projectteam = null;
	var $team = null;
    //var $seasonid = 0;

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
    
    static $cfg_which_database = 0;

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
		sportsmanagementModelProject::$projectid = $this->projectid;
        self::$cfg_which_database = JRequest::getInt('cfg_which_database',0);
		self::getProjectTeam();
	}

	
	/**
	 * sportsmanagementModelRoster::getProjectTeam()
	 * 
	 * @return
	 */
	function getProjectTeam()
	{
	   $app = JFactory::getApplication();
        $option = JRequest::getCmd('option');
       // Create a new query object.		
		$db = sportsmanagementHelper::getDBConnection(TRUE, self::$cfg_which_database );
		$query = $db->getQuery(true);
        $starttime = microtime(); 
        
		if (is_null($this->projectteam))
		{
			if ($this->projectteamid)
			{
			 $query = $db->getQuery(true);
             $query->clear();
				$query->select('pt.*,st.team_id as season_team_id');
	           $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt'); 
               $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st ON st.id = pt.team_id');
                $query->where('pt.id = '.$this->projectteamid );
 
			}
			else
			{
				if (!$this->teamid)
				{
					$this->setError(JText::_('COM_SPORTSMANAGEMENT_ROSTER_ERROR_TEAM_ID'));
					return false;
				}
				if (!$this->projectid)
				{
					$this->setError(JText::_('COM_SPORTSMANAGEMENT_ROSTER_ERROR_PROJECT_ID'));
					return false;
				}
                
                $query = $db->getQuery(true);
                $query->clear();
                $query->select('pt.*,st.team_id as season_team_id');
	           $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt'); 
               $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st ON st.id = pt.team_id');   
               
                $query->where('st.team_id = '.$db->Quote($this->teamid));
                $query->where('pt.project_id = '.$db->Quote($this->projectid));
                
                
               
			}
			$db->setQuery($query);
            
            if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
            
			$this->projectteam = $db->loadObject();
			
//            $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'');
//            $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' <br><pre>'.print_r($this->projectteam,true).'</pre>'),'');
            
            if ($this->projectteam)
			{
				$this->projectid = $this->projectteam->project_id; // if only ttid was set
				//$this->teamid = $this->projectteam->team_id; // if only ttid was set
                $this->teamid = $this->projectteam->season_team_id;
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
	   $app = JFactory::getApplication();
        $option = JRequest::getCmd('option');
       // Create a new query object.		
		$db = sportsmanagementHelper::getDBConnection(TRUE, self::$cfg_which_database );
		$query = $db->getQuery(true);
        $starttime = microtime(); 
        
		if (is_null($this->team))
		{
			if (!$this->teamid)
			{
				$this->setError(JText::_('COM_SPORTSMANAGEMENT_ROSTER_ERROR_TEAM_ID'));
				return false;
			}
			if (!$this->projectid)
			{
				$this->setError(JText::_('COM_SPORTSMANAGEMENT_ROSTER_ERROR_PROJECT_ID'));
				return false;
			}
            
            $query->select('t.*');
            $query->select('CONCAT_WS(\':\',t.id,t.alias) AS slug');
	           $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t'); 
                $query->where('t.id = '.$db->Quote($this->teamid));
                
            
    
			$db->setQuery($query);
            
            if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
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
	   $app = JFactory::getApplication();
        $option = JRequest::getCmd('option');
       // Create a new query object.		
		$db = sportsmanagementHelper::getDBConnection(TRUE, self::$cfg_which_database );
		$query = $db->getQuery(true);
        $starttime = microtime(); 
        
		$projectteam = self::getprojectteam();
		//if (empty($this->_players))
		//{
			
        // Select some fields
		$query->select('pr.firstname,pr.nickname,pr.lastname,pr.country,pr.birthday,pr.deathday,pr.id AS pid,pr.id AS person_id,pr.picture AS ppic');
        $query->select('pr.suspension AS suspension,pr.away AS away,pr.injury AS injury,pr.id AS pid,pr.picture AS ppic,CONCAT_WS(\':\',pr.id,pr.alias) AS slug');
        $query->select('tp.id AS playerid,tp.id AS season_team_person_id,tp.jerseynumber AS position_number,tp.notes AS description,tp.market_value AS market_value,tp.picture');    
        $query->select('st.id AS season_team_id');
        $query->select('pt.project_id AS project_id');
        $query->select('pos.name AS position');
        $query->select('ppos.position_id,ppos.id as pposid');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_person_id AS tp ');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st ON st.team_id = tp.team_id');    
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON pt.team_id = st.id');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS pr ON tp.person_id = pr.id');
        $query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS ppos ON ppos.id = tp.project_position_id');
        switch ( $persontype )
        {
            case 1:
            $query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS pos ON pos.id = ppos.position_id');
            break;
            case 2:
            $query->select('posparent.name AS parentname');
            $query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS pos ON pos.id = ppos.position_id');
            $query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS posparent ON pos.parent_id = posparent.id');
            break;
        }
        
        $query->where('pt.id='.$db->Quote($projectteam->id));
        $query->where('pr.published = 1');
        $query->where('tp.published = 1');
        $query->where('tp.persontype = '.$persontype);
        $query->where('tp.season_id = '.sportsmanagementModelProject::$seasonid);  
        $query->order('pos.ordering, ppos.position_id, tp.ordering, tp.jerseynumber, pr.lastname, pr.firstname');
           
            $db->setQuery($query);
            
            if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
            $this->_players = $db->loadObjectList();
            
            if ( !$this->_players && COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
            {
            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');   
            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($query->dump(),true).'</pre>'),'Error'); 
            }
            
            
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($query->dump(),true).'</pre>'),'');
            
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
		$app = JFactory::getApplication();
        $option = JRequest::getCmd('option');
       // Create a new query object.		
		$db = sportsmanagementHelper::getDBConnection(TRUE, self::$cfg_which_database );
		$query = $db->getQuery(true);
        $starttime = microtime(); 
                
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
        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
        
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
	 * sportsmanagementModelRoster::getTeamEventStat()
	 * 
	 * @param mixed $eventtype_id
	 * @return
	 */
	function getTeamEventStat($eventtype_id)
	{
		$app = JFactory::getApplication();
    $option = JRequest::getCmd('option');
        // Create a new query object.		
	   $db = sportsmanagementHelper::getDBConnection(TRUE, self::$cfg_which_database );
	   $query = $db->getQuery(true);
       $starttime = microtime(); 
       
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
        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
		$result = $db->loadObjectList('person_id');
        if ( !$result && COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
            $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' <br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
            $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Error');
        } 
        
        
		return $result;
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
	   $app = JFactory::getApplication();
    $option = JRequest::getCmd('option');
        // Create a new query object.		
	   $db = sportsmanagementHelper::getDBConnection(TRUE, self::$cfg_which_database );
	   $query = $db->getQuery(true);
       $starttime = microtime(); 
       
       $query->select('pr.injury AS injury,pr.suspension AS suspension,pr.away AS away');
       $query->select('ppos.id As pposid');
       $query->select('pos.id AS position_id');
       $query->select('stp.picture');
       $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS pr'); 
       $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_person_id AS stp ON stp.person_id = pr.id');
       $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st ON st.team_id = stp.team_id');
       $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON pt.team_id = st.id');
       
       $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_round AS r ON r.project_id = pt.project_id');
       $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS ppos ON ppos.id = stp.project_position_id');
       $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS pos ON pos.id = ppos.position_id');
       
       
       $query->where('r.id = '.$round_id);
       $query->where('stp.id = '.$player_id);
       $query->where('pr.published = 1');
       $query->where('stp.published = 1');
                      
		$db->setQuery($query);
        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');    
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
       
        
		$rows = $db->loadObjectList();
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
				$result[$pos][$stat->id] = $stat->getRosterStats($projectteam->team_id,$projectteam->project_id, $pos);
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
        $app = JFactory::getApplication();
    $option = JRequest::getCmd('option');
        // Create a new query object.		
	   $db = sportsmanagementHelper::getDBConnection(TRUE, self::$cfg_which_database );
	   $query = $db->getQuery(true);
       $starttime = microtime(); 
       
       $query->select('max(round_date_last)');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_round '); 
        $query->where('project_id ='.$this->projectid);
                    
        $db->setQuery($query);
        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
        if (!$result = $db->loadResult())
		{
			$this->setError($db->getErrorMsg());
			return false;
		}
		return $result;
        
    }

}
?>
