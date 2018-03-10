<?php 
/** SportsManagement ein Programm zur Verwaltung f?r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
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

//jimport('joomla.application.component.model');
jimport('joomla.application.component.modellist');


/**
 * sportsmanagementModelRosteralltime
 * 
 * @package 
 * @author abcde
 * @copyright 2015
 * @version $Id$
 * @access public
 */
//class sportsmanagementModelRosteralltime extends JModelLegacy
class sportsmanagementModelRosteralltime extends JModelList
{
	static $projectid = 0;
	static $projectteamid = 0;
	static $projectteam = 0;
    static $teamid = 0;
    static $cfg_which_database = 0;
	var $team = null;

	/**
	 * caching for team in out stats
	 * @var array
	 */
	var $_teaminout=null;

	/**
	 * caching players
	 * @var array
	 */
	var $_players=null;

var $_identifier = "rosteralltime";
	var $limitstart = 0;
    var $limit = 0;
    
	/**
	 * sportsmanagementModelRosteralltime::__construct()
	 * 
	 * @return void
	 */
	function __construct()
	{
		parent::__construct();
$option = JFactory::getApplication()->input->getCmd('option');
		$app = JFactory::getApplication();
        // JInput object
       $jinput = $app->input;
		self::$projectid = (int) $jinput->get( 'p', 0 );
		self::$teamid = (int) $jinput->get( 'tid', 0 );
		self::$projectteamid = (int) $jinput->get( 'ttid', 0 );
		self::$cfg_which_database = JFactory::getApplication()->input->get('cfg_which_database', 0, 'INT');
        $this->limitstart = $jinput->getVar('limitstart', 0, '', 'int');
	}
    
    /**
 * Method to get the starting number of items for the data set.
 *
 * @return  integer  The starting number of items available in the data set.
 *
 * @since   11.1
 */
public function getStart()
{
    // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
    //$limitstart = $this->getUserStateFromRequest($this->context.'.limitstart', 'limitstart');
    $this->setState('list.start', $this->limitstart );
    
    $store = $this->getStoreId('getstart');

    // Try to load the data from internal storage.
    if (isset($this->cache[$store]))
    {
        return $this->cache[$store];
    }

    $start = $this->getState('list.start');
    $limit = $this->getState('list.limit');
    $total = $this->getTotal();
    if ($start > $total - $limit)
    {
        $start = max(0, (int) (ceil($total / $limit) - 1) * $limit);
    }
    
//    $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' limitstart<br><pre>'.print_r($limitstart,true).'</pre>'),'');
//    $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' this->limitstart<br><pre>'.print_r($this->limitstart,true).'</pre>'),'');
//    $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' store<br><pre>'.print_r($store,true).'</pre>'),'');
//    $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' list.start<br><pre>'.print_r($this->getState('list.start'),true).'</pre>'),'');
//    $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' list.limit<br><pre>'.print_r($this->getState('list.limit'),true).'</pre>'),'');

    // Add the total to the internal cache.
    $this->cache[$store] = $start;

    return $this->cache[$store];
}

/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since	1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        // Initialise variables.
		$app = JFactory::getApplication('site');
        
        // List state information
		//$value = JFactory::getApplication()->input->getUInt('limit', $app->getCfg('list_limit', 0));
        $value = $this->getUserStateFromRequest($this->context.'.limit', 'limit', $app->getCfg('list_limit', 0));
		$this->setState('list.limit', $value);

		$value = $jinput->getUInt('limitstart', 0);
		$this->setState('list.start', $value);
        
    }
        
    /**
     * sportsmanagementModelRosteralltime::getListQuery()
     * 
     * @return
     */
    function getListQuery()
	{
		// Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        
        // Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
		$user	= JFactory::getUser(); 
        
        $query->select('tp.person_id AS person_id');
        //$query->select('pos.ordering, ppos.position_id, tp.ordering, tp.jerseynumber, pr.lastname, pr.firstname');
        $query->from('#__sportsmanagement_season_team_person_id AS tp ');
        $query->join('INNER','#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id');    
        $query->join('INNER','#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
        $query->join('INNER','#__sportsmanagement_person AS pr ON tp.person_id = pr.id');
        $query->join('INNER','#__sportsmanagement_project AS pro ON pro.id = pt.project_id'); 
        $query->join('INNER','#__sportsmanagement_team AS t ON t.id = st.team_id');
        $query->join('LEFT','#__sportsmanagement_project_position AS ppos ON ppos.id = tp.project_position_id');
        $query->join('LEFT','#__sportsmanagement_position AS pos ON pos.id = ppos.position_id');
        $query->where('tp.team_id = '.self::$teamid );
        $query->where('pr.published = 1');
        $query->where('tp.published = 1');
        
        //$query->order('pos.ordering, ppos.position_id, tp.ordering, tp.jerseynumber, pr.lastname, pr.firstname');
        $query->group('tp.person_id');
        $query->order('pos.ordering, ppos.position_id, tp.ordering, tp.jerseynumber, pr.lastname, pr.firstname');
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ' .  ' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        
    return $query;

	}    
    
    
    /**
     * sportsmanagementModelRosteralltime::getPlayerPosition()
     * 
     * @return
     */
    function getPlayerPosition()
    {
         $option = JFactory::getApplication()->input->getCmd('option');
	$app = JFactory::getApplication();
    $db = JFactory::getDbo();
 $query = $db->getQuery(true);
 // Select some fields
        $query->select('po.*');
        $query->from('#__sportsmanagement_position as po');
        $query->where('po.parent_id != 0 ');
$query->where('po.persontype = 1 ');

			$db->setQuery($query);

return $db->loadObjectList();    
        
    }
    
    /**
     * sportsmanagementModelRosteralltime::getPositionEventTypes()
     * 
     * @param integer $positionId
     * @return
     */
    function getPositionEventTypes($positionId=0)
	{
	    $option = JFactory::getApplication()->input->getCmd('option');
	$app = JFactory::getApplication();
    $db = JFactory::getDbo();
 $query = $db->getQuery(true);
		$result = array();
        
         // Select some fields
        $query->select('pet.*');
		$query->select('et.name AS name,et.icon AS icon');
        $query->from('#__sportsmanagement_position_eventtype AS pet');
        $query->join('INNER','#__sportsmanagement_eventtype AS et ON et.id = pet.eventtype_id');
        $query->where('et.published = 1');
        $query->group('pet.ordering, et.ordering');

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
     * sportsmanagementModelRosteralltime::getTeam()
     * 
     * @return
     */
    function getTeam()
	{
	   $option = JFactory::getApplication()->input->getCmd('option');
	$app = JFactory::getApplication();
    $db = JFactory::getDbo();
 $query = $db->getQuery(true);
 
		if (is_null($this->team))
		{
			if (!self::$teamid)
			{
				JError::raiseWarning( 'ERROR_CODE', JText::_( 'COM_SPORTSMANAGEMENT_TEAMINFO_ERROR' ) );
				return false;
			}
			if (!self::$projectid)
			{
				JError::raiseWarning( 'ERROR_CODE', JText::_( 'COM_SPORTSMANAGEMENT_RANKING_ERROR_PROJECTID_REQUIRED' ) );
				return false;
			}
            
            // Select some fields
        $query->select('t.*');
        $query->select('CONCAT_WS(\':\',t.id,t.alias) AS team_slug');
        $query->from('#__sportsmanagement_team AS t');
        $query->where('t.id = '.self::$teamid );

			$db->setQuery($query);
			$this->team = $db->loadObject();
		}
		return $this->team;
	}


	
	
	/**
	 * sportsmanagementModelRosteralltime::getTeamPlayers()
	 * 
	 * @param integer $persontype
	 * @param mixed $positioneventtypes
	 * @param integer $from
	 * @param integer $to
	 * @return
	 */
	function getTeamPlayers($persontype = 1,$positioneventtypes = array(),$items = array() )
	{
	$option = JFactory::getApplication()->input->getCmd('option');
	$app = JFactory::getApplication();
    $db = JFactory::getDbo();
 $query = $db->getQuery(true);
	
    $person_range = array();	
        foreach( $items as $row )
        {
        $person_range[] = $row->person_id;    
        }
        
//        if (empty($this->_players))
//		{
		// Select some fields
		$query->select('pr.firstname,pr.nickname,pr.lastname,pr.country,pr.birthday,pr.deathday,pr.id AS pid,pr.id AS person_id,pr.picture AS ppic');
        $query->select('pr.suspension AS suspension,pr.away AS away,pr.injury AS injury,pr.id AS pid,pr.picture AS ppic,CONCAT_WS(\':\',pr.id,pr.alias) AS person_slug');
        $query->select('tp.id AS playerid,tp.id AS season_team_person_id,tp.jerseynumber AS position_number,tp.notes AS description,tp.market_value AS market_value,tp.picture');    
        $query->select('st.id AS season_team_id');
        $query->select('pt.project_id AS project_id');
        $query->select('pos.name AS position');
        $query->select('ppos.position_id,ppos.id as pposid');
        $query->select('CONCAT_WS(\':\',pro.id,pro.alias) AS project_slug');
        $query->select('CONCAT_WS(\':\',t.id,t.alias) AS team_slug');
        $query->from('#__sportsmanagement_season_team_person_id AS tp ');
        $query->join('INNER','#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id');    
        $query->join('INNER','#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
        $query->join('INNER','#__sportsmanagement_person AS pr ON tp.person_id = pr.id');
        $query->join('INNER','#__sportsmanagement_project AS pro ON pro.id = pt.project_id'); 
        $query->join('INNER','#__sportsmanagement_team AS t ON t.id = st.team_id');
        $query->join('LEFT','#__sportsmanagement_project_position AS ppos ON ppos.id = tp.project_position_id');
        switch ( $persontype )
        {
            case 1:
            $query->join('LEFT','#__sportsmanagement_position AS pos ON pos.id = ppos.position_id');
            break;
            case 2:
            $query->select('posparent.name AS parentname');
            $query->join('LEFT','#__sportsmanagement_position AS pos ON pos.id = ppos.position_id');
            $query->join('LEFT','#__sportsmanagement_position AS posparent ON pos.parent_id = posparent.id');
            break;
        }
        
        $query->where('tp.person_id IN ( '.implode(",",$person_range).' )');
        $query->where('st.team_id = '.self::$teamid );
        $query->where('pr.published = 1');
        $query->where('tp.published = 1');
        $query->where('tp.persontype = '.$persontype);
        $query->order('pos.ordering, ppos.position_id, tp.ordering, tp.jerseynumber, pr.lastname, pr.firstname');
        	

			$db->setQuery($query);
			$this->_players = $db->loadObjectList();
			$this->_all_time_players = $db->loadObjectList('pid');
//		}
		
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' dump<br><pre>'.print_r($query->dump(),true).'</pre>'),'');
//		$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' _players<br><pre>'.print_r($this->_players,true).'</pre>'),'');
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' positioneventtypes<br><pre>'.print_r($positioneventtypes,true).'</pre>'),'');
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' teamid<br><pre>'.print_r(self::$teamid,true).'</pre>'),'');
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' persontype<br><pre>'.print_r($persontype,true).'</pre>'),'');
        
		foreach ($this->_players as $player)
		{
		$player->start = 0;
		$player->came_in = 0;
		$player->out = 0;
        
        if (  !isset($this->_all_time_players[$player->pid]->start) )
        {
            $this->_all_time_players[$player->pid]->start = 0;
        }
        if (  !isset($this->_all_time_players[$player->pid]->came_in) )
        {
            $this->_all_time_players[$player->pid]->came_in = 0;
        }
        if (  !isset($this->_all_time_players[$player->pid]->out) )
        {
            $this->_all_time_players[$player->pid]->out = 0;
        }
        
        $this->InOutStat = sportsmanagementModelPlayer::getInOutStats(0,0,0,0,0,JFactory::getApplication()->input->getInt('cfg_which_database',0),self::$teamid,$player->pid);

//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' InOutStat<br><pre>'.print_r($this->InOutStat,true).'</pre>'),'');
$this->_all_time_players[$player->pid]->played = $this->InOutStat->played;
                $this->_all_time_players[$player->pid]->start = $this->InOutStat->started;
				$this->_all_time_players[$player->pid]->came_in = $this->InOutStat->sub_in;
				$this->_all_time_players[$player->pid]->out = $this->InOutStat->sub_out;
                
                

foreach ($positioneventtypes AS $event => $eventid )
			{
			 //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' eventId<br><pre>'.print_r($eventid,true).'</pre>'),'');
             
             for($a=0; $a < count($eventid); $a++ )
{
			 
             $query->clear();
             $query->select('count(*) as total');
             $query->from('#__sportsmanagement_match_event');
             $query->where('event_type_id = '.$eventid[$a]->eventtype_id );
             $query->where('teamplayer_id = '.$player->playerid);
             $db->setQuery($query);
             $event_type_id = 'event_type_id_'.$eventid[$a]->eventtype_id;
             $player->$event_type_id = $db->loadResult();
             
             if ( !isset($this->_all_time_players[$player->pid]->$event_type_id)  )
             {
                $this->_all_time_players[$player->pid]->$event_type_id = 0;
             }
             
             $this->_all_time_players[$player->pid]->$event_type_id = $this->_all_time_players[$player->pid]->$event_type_id + $player->$event_type_id;
             }
             }

		
		}

//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' _all_time_players<br><pre>'.print_r($this->_all_time_players,true).'</pre>'),'');

		return $this->_all_time_players;
	}

	function getStaffList()
	{
		$projectteam =& $this->getprojectteam();
		$query='	SELECT	pr.firstname, 
							pr.nickname,
							pr.lastname,
							pr.country,
							pr.birthday,
							pr.deathday,
							ts.id AS ptid,
							ppos.position_id,
							ppos.id AS pposid,
							pr.id AS pid,
							pr.picture AS ppic,
							pos.name AS position,
							ts.picture,
							ts.notes AS description,
							ts.injury AS injury,
							ts.suspension AS suspension,
							ts.away AS away,
							pos.parent_id,
							posparent.name AS parentname,				
							CASE WHEN CHAR_LENGTH(pr.alias) THEN CONCAT_WS(\':\',pr.id,pr.alias) ELSE pr.id END AS slug
					FROM #__joomleague_team_staff ts
					INNER JOIN #__joomleague_person AS pr ON ts.person_id=pr.id
					INNER JOIN #__joomleague_project_position AS ppos ON ppos.id=ts.project_position_id
					INNER JOIN #__joomleague_position AS pos ON pos.id=ppos.position_id
					LEFT JOIN #__joomleague_position AS posparent ON pos.parent_id=posparent.id
					WHERE ts.projectteam_id='.$this->_db->Quote($projectteam->id).'
					  AND pr.published = 1
					  AND ts.published = 1
					ORDER BY pos.parent_id, pos.ordering';
		$this->_db->setQuery($query);
		$stafflist=$this->_db->loadObjectList();
		return $stafflist;
	}

	
	

	

	

	

	

	

}
?>