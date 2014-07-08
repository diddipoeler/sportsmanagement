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

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

//require_once( JLG_PATH_SITE . DS . 'models' . DS . 'project.php' );
//require_once('results.php');

/**
 * sportsmanagementModelTeamPlan
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelTeamPlan extends JModelLegacy
{
	var $projectid = 0;
	var $teamid = 0;
    var $projectteamid = 0;
	var $pro_teamid = 0;
	var $team = null;
	var $club = null;
	var $divisionid = 0;
	var $joomleague = null;
	var $mode = 0;

	/**
	 * sportsmanagementModelTeamPlan::__construct()
	 * 
	 * @return
	 */
	function __construct()
	{
		

		$this->projectid = JRequest::getInt('p',0);
		$this->teamid = JRequest::getInt('tid',0);
        $this->projectteamid = JRequest::getInt('ptid',0);
		$this->divisionid = JRequest::getInt('division',0);
		$this->mode = JRequest::getInt("mode",0);
        parent::__construct();
	}

	/**
	 * sportsmanagementModelTeamPlan::getDivisionID()
	 * 
	 * @return
	 */
	function getDivisionID()
	{
		return $this->divisionid;
	}

	/**
	 * sportsmanagementModelTeamPlan::getMode()
	 * 
	 * @return
	 */
	function getMode()
	{
		return $this->mode;
	}

	/**
	 * sportsmanagementModelTeamPlan::getDivision()
	 * 
	 * @return
	 */
	function getDivision()
	{
		$option = JRequest::getCmd('option');
	   $mainframe = JFactory::getApplication();
       // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $starttime = microtime(); 
        
        $division = null;
		if ( $this->divisionid > 0 )
		{
			
        // Select some fields
        $query->select('d.*,CONCAT_WS(\':\',id,alias) AS slug');
        // From 
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_division AS d');
        // Where
        $query->where('d.id = '.$db->Quote($this->divisionid));
			
            $db->setQuery($query,0,1);
            
            if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
			$division = $db->loadObject();
            
            if (!$division )
		{
			$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__FUNCTION__.' '.'<pre>'.print_r($db->getErrorMsg(),true).'</pre>' ),'Error');
		}
        
		}
		return $division;
	}

	/**
	 * sportsmanagementModelTeamPlan::getProjectTeamId()
	 * 
	 * @return
	 */
	function getProjectTeamId()
	{
		$option = JRequest::getCmd('option');
	   $mainframe = JFactory::getApplication();
       // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $starttime = microtime(); 
        
        // Select some fields
        $query->select('pt.id');
        // From 
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team as pt');
        $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id as st ON st.id = pt.team_id ');
        $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_team as t ON t.id = st.team_id ');
        // Where
        $query->where('pt.project_id = '.$this->projectid);
        $query->where('t.id='.$this->teamid);
                 
		$db->setQuery($query,0,1);
        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
        //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.'<br><pre>'.print_r($query->dump(),true).'</pre>'),'');
        
		if ( !$result = $db->loadResult())
		{
			//$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' '.'<pre>'.print_r($db->getErrorMsg(),true).'</pre>' ),'Error');
            $this->pro_teamid = 0;
            return 0;
		}
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
       {
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' team_id'.'<pre>'.print_r($result,true).'</pre>' ),'');
        }
        
		$this->pro_teamid = $result;
        $this->projectteamid = $result;
		return $result;
	}

	/**
	 * sportsmanagementModelTeamPlan::getMatchesPerRound()
	 * 
	 * @param mixed $config
	 * @param mixed $rounds
	 * @return
	 */
	function getMatchesPerRound($config,$rounds)
	{
	   $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
		$rm=array();

		$ordering='DESC';
		if ($config['plan_order'])
		{
			$ordering=$config['plan_order'];
		}
		foreach ($rounds as $round)
		{
			$matches = self::_getResultsRows($round->roundcode,$this->pro_teamid,$ordering,0,1,$config['show_referee']);
			$rm[$round->roundcode] = $matches;
		}
		return $rm;
	}

	/**
	 * sportsmanagementModelTeamPlan::getMatches()
	 * 
	 * @param mixed $config
	 * @return
	 */
	function getMatches($config)
	{
	   $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        
		$ordering = 'DESC';
		if ($config['plan_order'])
		{
			$ordering = $config['plan_order'];
		}
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
		{
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' pro_teamid'.'<pre>'.print_r($this->pro_teamid,true).'</pre>' ),'');
        }
        
		return self::_getResultsPlan($this->pro_teamid,$ordering,0,1,$config['show_referee']);
	}

	/**
	 * sportsmanagementModelTeamPlan::getMatchesRefering()
	 * 
	 * @param mixed $config
	 * @return
	 */
	function getMatchesRefering($config)
	{
	   $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
		$ordering = 'DESC';
		if ($config['plan_order'])
		{
			$ordering = $config['plan_order'];
		}
		return self::_getResultsPlan(0,$ordering,$this->pro_teamid,1,$config['show_referee']);
	}

	/**
	 * sportsmanagementModelTeamPlan::_getResultsPlan()
	 * 
	 * @param integer $team
	 * @param string $ordering
	 * @param integer $referee
	 * @param integer $getplayground
	 * @param integer $getreferee
	 * @return
	 */
	function _getResultsPlan($team=0,$ordering='ASC',$referee=0,$getplayground=0,$getreferee=0)
	{
		$mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
       // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query2 = $db->getQuery(true);
        $starttime = microtime(); 
        
        // $this->projectteamid
        
		//$mdlProject = JModelLegacy::getInstance("Project", "sportsmanagementModel");
        $matches = array();
		$joomleague = sportsmanagementModelProject::getProject();

		if ($this->divisionid > 0)
		{
		// Select some fields
        $query->select('id');
        // From 
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_division');
        // Where
        $query->where('parent_id = '.(int)$this->divisionid);

			$db->setquery($query);
			$div_for_teams = $db->loadResultArray();
			$div_for_teams[] = self::getDivision()->id;
		}

		// Select some fields
        $query->select('m.*,DATE_FORMAT(m.time_present,"%H:%i") time_present,r.roundcode,r.id roundid,r.project_id,r.name');
        // From 
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match AS m');
        // Join 
        $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_round AS r ON m.round_id = r.id ');
        $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS p ON p.id = r.project_id ');
        // Where
        $query->where('m.published=1');

//win matches
		if (($this->mode)== 1)
		{
		  //$query->where('( (m.projectteam1_id= ' .$team. ' AND m.team1_result > m.team2_result)'.' OR (m.projectteam2_id= ' .$team. ' AND m.team1_result < m.team2_result) )');
          $query->where('( (m.projectteam1_id = ' .$this->projectteamid. ' AND m.team1_result > m.team2_result)'.' OR (m.projectteam2_id = ' .$this->projectteamid. ' AND m.team1_result < m.team2_result) )');
		}
//draw matches
		if (($this->mode)== 2)
		{
		  $query->where('m.team1_result = m.team2_result');
		}
//lost matches
		if (($this->mode)== 3)
		{
 		  //$query->where('( (m.projectteam1_id= ' .$team. ' AND m.team1_result < m.team2_result)'.' OR (m.projectteam2_id= ' .$team. ' AND m.team1_result > m.team2_result) )');
			$query->where('( (m.projectteam1_id = ' .$this->projectteamid. ' AND m.team1_result < m.team2_result)'.' OR (m.projectteam2_id = ' .$this->projectteamid. ' AND m.team1_result > m.team2_result) )');
		}
	
		if ($this->divisionid > 0)
		{
		  $query->where('(pt1.division_id IN ('.(implode(',',$div_for_teams)).') OR pt2.division_id IN ('.(implode(',',$div_for_teams)).'))');
		}

		if ($referee != 0)
		{
			$query->select('p.name AS project_name');
            $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_referee AS mref ON mref.match_id = m.id ');
            $query->where('mref.project_referee_id = '.$referee);
            $query->where('p.season_id = '.$joomleague->season_id);
		}
		else
		{
            $query->where('r.project_id = '.$this->projectid);
		}

		if ($this->teamid != 0)
		{
            //$query->where("(m.projectteam1_id=".$team." OR m.projectteam2_id=".$team.")");
            $query->where("(m.projectteam1_id = ".$this->projectteamid." OR m.projectteam2_id = ".$this->projectteamid.")");
		}
        
        // Group
        $query->group('m.id');
        // Order
        $query->order("r.roundcode ".$ordering.",m.match_date,m.match_number");

		if ($getplayground)
		{
            $query->select('playground.name AS playground_name,playground.short_name AS playground_short_name');
            $query->join('LEFT',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_playground AS playground ON playground.id = m.playground_id ');
		}

		
		$db->setQuery($query);
        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
		$matches = $db->loadObjectList();
        
        //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.'<br><pre>'.print_r($query->dump(),true).'</pre>'),'');

		if ($getreferee)
		{
			$this->_getRefereesByMatch($matches,$joomleague);
		}

if (!$matches && COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
		{
			$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' '.'<pre>'.print_r($db->getErrorMsg(),true).'</pre>' ),'Error');
            $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($query->dump(),true).'</pre>'),'');
		}
		
		return $matches;
	}

	/**
	 * sportsmanagementModelTeamPlan::_getResultsRows()
	 * 
	 * @param integer $roundcode
	 * @param integer $teamId
	 * @param string $ordering
	 * @param integer $unpublished
	 * @param integer $getplayground
	 * @param integer $getreferee
	 * @return
	 */
	function _getResultsRows($roundcode=0,$teamId=0,$ordering='ASC',$unpublished=0,$getplayground=0,$getreferee=0)
	{
		$mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
       // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $starttime = microtime();
        
		//$mdlProject = JModelLegacy::getInstance("Project", "sportsmanagementModel");
        $matches = array();

		$joomleague = sportsmanagementModelProject::getProject();
        
        // Select some fields
        $query->select('matches.*');
        // From 
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match AS matches');
        // Join 
        $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_round AS r ON matches.round_id = r.id ');
        // Where
        $query->where('r.project_id = '.(int)$this->projectid);
        $query->where('r.roundcode = '.$roundcode);

		if ($teamId)
		{
		  //$query->where("(matches.projectteam1_id=".$teamId." OR matches.projectteam2_id=".$teamId.")");
          $query->where("(matches.projectteam1_id = ".$this->projectteamid." OR matches.projectteam2_id = ".$this->projectteamid.")");
		}
		// Group
        $query->group('matches.id');
        // Order
        $query->order('matches.match_date '.$ordering.',matches.match_number');

		if ($this->divisionid > 0)
		{
		  $query->join('LEFT',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_division AS d1 ON pt1.division_id = d1.id ');
          $query->join('LEFT',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_division AS d2 ON pt2.division_id = d2.id ');
          $query->where("(d1.id = ".$this->divisionid." OR d1.parent_id = ".$this->divisionid." OR d2.id = ".$this->divisionid." OR d2.parent_id = ".$this->divisionid.")");
		}

		if ($unpublished != 1)
		{
		  $query->where('matches.published=1');
		}

		if ($getplayground)
		{
			$query->select('playground.name AS playground_name,playground.short_name AS playground_short_name');
			$query->join('LEFT',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_playground AS playground ON playground.id = matches.playground_id');
		}

		$db->setQuery($query);
        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
        $matches = $db->loadObjectList();
		
        //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.'<br><pre>'.print_r($query->dump(),true).'</pre>'),'');

		if ($getreferee)
		{
			$this->_getRefereesByMatch($matches,$joomleague);
		}

if (!$matches && COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
		{

			$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' '.'<pre>'.print_r($db->getErrorMsg(),true).'</pre>' ),'Error');
		}

		return $matches;
	}

	/**
	 * sportsmanagementModelTeamPlan::_getRefereesByMatch()
	 * 
	 * @param mixed $matches
	 * @param mixed $joomleague
	 * @return
	 */
	function _getRefereesByMatch($matches,$joomleague)
	{
	   	$option = JRequest::getCmd('option');
	   $mainframe = JFactory::getApplication();
       // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $starttime = microtime();
        
		for ($index=0; $index < count($matches); $index++) 
        {
			$referees=array();
			if ($joomleague->teams_as_referees)
			{
			 // Select some fields
             $query->select('ref.name AS referee_name');
             // From 
             $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS ref');
             $query->join('LEFT',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_referee AS link ON link.project_referee_id=ref.id ');
             // Where
             $query->where('link.match_id = '.$matches[$index]->id);
             // Order
             $query->order('link.ordering');

			}
			else
			{
			 // Select some fields
             $query->select('ref.firstname AS referee_firstname,ref.lastname AS referee_lastname,ref.id as referee_id');
             $query->select('ppos.position_id');
             $query->select('pos.name AS referee_position_name');
             // From 
             $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS ref');
             $query->join('LEFT',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_season_person_id AS sp ON sp.person_id = ref.id ');
             $query->join('LEFT',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_referee AS pref ON pref.person_id = sp.id ');
             $query->join('LEFT',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_referee AS link ON link.project_referee_id = pref.id ');
             
             $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS ppos ON ppos.id = link.project_position_id');
             $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS pos ON pos.id = ppos.position_id');

             // Where
             $query->where('link.match_id = '.$matches[$index]->id);
             $query->where('ref.published = 1');
             // Order
             $query->order('link.ordering');

			}

			$db->setQuery($query);
            
            if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
			if (! $referees = $db->loadObjectList())
			{
				$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' '.'<pre>'.print_r($db->getErrorMsg(),true).'</pre>' ),'Error');
			}
			$matches[$index]->referees=$referees;
		}
		return $matches;
	}

	/**
	 * sportsmanagementModelTeamPlan::getEventTypes()
	 * 
	 * @param mixed $match_id
	 * @return
	 */
	function getEventTypes($match_id)
	{
		$option = JRequest::getCmd('option');
	   $mainframe = JFactory::getApplication();
       // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        // Select some fields
        $query->select('et.id as etid,me.event_type_id as id,et.*');
        // From 
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_eventtype as et');
        $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_event as me ON et.id = me.event_type_id ');
        $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_match as m ON m.id = me.match_id ');
        // Where
        $query->where('me.match_id = '.$match_id);
        // Order
        $query->order('et.ordering');

		$db->setQuery($query);
		return $db->loadObjectList('etid');
	}

}
?>