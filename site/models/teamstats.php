<?php 
/** SportsManagement ein Programm zur Verwaltung f�r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: � 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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
* SportsManagement ist Freie Software: Sie k�nnen es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder sp�teren
* ver�ffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es n�tzlich sein wird, aber
* OHNE JEDE GEW�HELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gew�hrleistung der MARKTF�HIGKEIT oder EIGNUNG F�R EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License f�r weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.model');

//require_once( JLG_PATH_SITE . DS . 'models' . DS . 'project.php' );


/**
 * sportsmanagementModelTeamStats
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelTeamStats extends JModelLegacy
{
	var $projectid = 0;
	var $teamid = 0;
	var $highest_home = null;
	var $highest_away = null;
	var $highestdef_home = null;
	var $highestdef_away = null;
	var $highestdraw_home = null;
	var $highestdraw_away = null;
	var $totalshome = null;
	var $totalsaway = null;
	var $matchdaytotals = null;
	var $totalrounds = null;
	var $attendanceranking = null;

	/**
	 * sportsmanagementModelTeamStats::__construct()
	 * 
	 * @return
	 */
	function __construct( )
	{
		parent::__construct();

		$this->projectid = JRequest::getInt( "p", 0 );
		$this->teamid = JRequest::getInt( "tid", 0 );
		sportsmanagementModelProject::$projectid = $this->projectid;
		//preload the team;
		$this->getTeam();
	}

	/**
	 * sportsmanagementModelTeamStats::getTeam()
	 * 
	 * @return
	 */
	function getTeam( )
	{
		# it should be checked if any tid is given in the params of the url
		# if ( is_null( $this->team ) )
		if ( !isset( $this->team ) )
		{
			if ( $this->teamid > 0 )
			{
				$this->team = $this->getTable( 'Team', 'sportsmanagementTable' );
				$this->team->load( $this->teamid );
			}
		}
		return $this->team;
	}

	/**
	 * sportsmanagementModelTeamStats::getHighest()
	 * 
	 * @return
	 */
	function getHighest($homeaway, $which)
	{
	   $option = JRequest::getCmd('option');
	    $mainframe = JFactory::getApplication();
        // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $starttime = microtime(); 
		
			$query->select('matches.id AS matchid, t1.name AS hometeam');
            $query->select('t2.name AS guestteam');
            $query->select('team1_result AS homegoals');
            $query->select('team2_result AS guestgoals');
            $query->select('t1.id AS team1_id');
            $query->select('t2.id AS team2_id');
        
        
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match as matches ');
        
        $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt1 ON pt1.id = matches.projectteam1_id ');
        $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt2 ON pt2.id = matches.projectteam2_id  ');
           
        $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id as st1 ON st1.id = pt1.team_id ');
        $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t1 ON st1.team_id = t1.id ');
           
        $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id as st2 ON st2.id = pt2.team_id ');
        $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t2 ON st2.team_id = t2.id ');
        
        $query->where('pt1.project_id = '.$this->projectid);
        
        $query->where('matches.published = 1');
        $query->where('alt_decision = 0');
        $query->where('(matches.cancel IS NULL OR matches.cancel = 0)');
        switch ($homeaway)
        {
            case 'HOME':
            $query->where('t1.id = '. $this->team->id);
            
            switch ($which)
            {
            case 'WIN':
            $query->where('team1_result > team2_result');
            $query->order('(team1_result-team2_result) DESC');
            break;
            case 'DEF':
            $query->where('team2_result > team1_result');
            $query->order('(team2_result-team1_result) DESC');
            break;
            case 'DRAW':
            $query->where('team2_result = team1_result');
            $query->order('team1_result DESC');
            break;
            }
            
            break;
            
            case 'AWAY':
            $query->where('t2.id = '. $this->team->id);
            
            switch ($which)
            {
            case 'WIN':
            $query->where('team2_result > team1_result');
            $query->order('(team2_result-team1_result) DESC');
            break;
            case 'DEF':
            $query->where('team1_result > team2_result');
            $query->order('(team1_result-team2_result) DESC');
            break;
            case 'DRAW':
            $query->where('team1_result = team2_result');
            $query->order('team2_result DESC');
            break;
            }
                        
            break;
        }

            $db->setQuery($query, 0, 1);
            
            if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
       if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' '.$homeaway.' '.$which.   ' <br><pre>'.print_r($query->dump(),true).'</pre>'),'');
        }
        
        $result= $db->loadObject( );
        
        if ( !$result )
        {
            $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
        }
        
        return $result;
        
        
        
    }

    
    /**
     * sportsmanagementModelTeamStats::getNoGoalsAgainst()
     * 
     * @return
     */
    function getNoGoalsAgainst( )
    {
        $option = JRequest::getCmd('option');
	    $mainframe = JFactory::getApplication();
        // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $starttime = microtime(); 
        
    	if ( (!isset( $this->nogoals_against )) || is_null( $this->nogoals_against ) )
    	{
           $query->select('COUNT( round_id ) AS totalzero ');
	       $query->select('SUM( t1.id = '.$this->team->id.' AND team2_result=0 ) AS homezero ');
           $query->select('SUM( t2.id = '.$this->team->id.' AND team1_result=0 ) AS awayzero ');
           $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match AS matches');
           $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team pt1 ON pt1.id = matches.projectteam1_id ');
           $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id as st1 ON st1.id = pt1.team_id ');
           $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t1 ON st1.team_id = t1.id ');
           
           $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team pt2 ON pt2.id = matches.projectteam2_id ');
           $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id as st2 ON st2.id = pt2.team_id ');
           $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t2 ON st2.team_id = t2.id ');
        
           $query->where('pt1.project_id = '.$this->projectid);
           $query->where('published=1 ');
           $query->where('alt_decision=0');
           $query->where('( (t1.id = '.$this->team->id.' AND team2_result=0 ) OR (t2.id = '.$this->team->id.' AND team1_result=0 ) ) ');
           $query->where('( matches.cancel IS NULL OR matches.cancel = 0 )');
                   
    		$db->setQuery($query);
            
            if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
    		$this->nogoals_against = $db->loadObject( );
    	}
    	return $this->nogoals_against;
    }
    
    
    /**
     * sportsmanagementModelTeamStats::getSeasonTotals()
     * 
     * @param mixed $which
     * @return
     */
    function getSeasonTotals($which)
    {
        $option = JRequest::getCmd('option');
	    $mainframe = JFactory::getApplication();
        // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $starttime = microtime(); 

        $query->select('COUNT(matches.id) AS totalmatches ');
        $query->select('COUNT(team1_result) AS playedmatches ');
//	    $query->select('IFNULL(SUM(team1_result),0) AS goalsfor,IFNULL(SUM(team2_result),0) AS goalsagainst,IFNULL(SUM(team1_result + team2_result),0) AS totalgoals,IFNULL(SUM(IF(team1_result=team2_result,1,0)),0) AS totaldraw,IFNULL(SUM(IF(team1_result<team2_result,1,0)),0) AS totalloss,IFNULL(SUM(IF(team1_result>team2_result,1,0)),0) AS totalwin  ');
		$query->select('COUNT(crowd) AS attendedmatches ');
		$query->select('SUM(crowd) AS sumspectators ');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match AS matches');
        
        switch ($which)
        {
            case 'HOME':
            $query->select('IFNULL(SUM(team1_result),0) AS goalsfor,IFNULL(SUM(team2_result),0) AS goalsagainst,IFNULL(SUM(team1_result + team2_result),0) AS totalgoals,IFNULL(SUM(IF(team1_result=team2_result,1,0)),0) AS totaldraw,IFNULL(SUM(IF(team1_result<team2_result,1,0)),0) AS totalloss,IFNULL(SUM(IF(team1_result>team2_result,1,0)),0) AS totalwin  ');
            $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team pt1 ON pt1.id = matches.projectteam1_id ');
            break;
            case 'AWAY':
            $query->select('IFNULL(SUM(team2_result),0) AS goalsfor,IFNULL(SUM(team1_result),0) AS goalsagainst,IFNULL(SUM(team2_result + team1_result),0) AS totalgoals,IFNULL(SUM(IF(team2_result=team1_result,1,0)),0) AS totaldraw,IFNULL(SUM(IF(team2_result<team1_result,1,0)),0) AS totalloss,IFNULL(SUM(IF(team2_result>team1_result,1,0)),0) AS totalwin  ');
            $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team pt1 ON pt1.id = matches.projectteam2_id ');
            break;
        }
        
        $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id as st ON st.id = pt1.team_id ');
        $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t ON st.team_id = t.id ');
        
        $query->where('pt1.project_id = '.$this->projectid);
        $query->where('published = 1');
        $query->where('t.id = '.$this->team->id);
        $query->where('(matches.cancel IS NULL OR matches.cancel = 0)');
        
        $db->setQuery($query, 0, 1);
        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
    	//$this->totalshome = $db->loadObject();
    	
        if ( !$db->loadObject() )
        {
            $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
        }
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'');
        }
        
        switch ($which)
        {
            case 'HOME':
            if ( is_null( $this->totalshome ) )
    	    {
    	       $this->totalshome = $db->loadObject();
               return $this->totalshome;
    	    }   
            break;
            case 'AWAY':
            if ( is_null( $this->totalsaway ) )
    	    {
    	       $this->totalsaway = $db->loadObject();
               return $this->totalsaway;
    	    }
            break;
        }
        
    	
        
    }
    
		/**
		 * sportsmanagementModelTeamStats::getChartData()
		 * 
		 * @return
		 */
		function getChartData( )
		{
		  $option = JRequest::getCmd('option');
	    $mainframe = JFactory::getApplication();
        // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        
        $query->select('rounds.id');
        $query->select('SUM(CASE WHEN st1.team_id ='.$this->teamid.' THEN matches.team1_result ELSE matches.team2_result END) AS goalsfor');
        $query->select('SUM(CASE WHEN st1.team_id ='.$this->teamid.' THEN matches.team2_result ELSE matches.team1_result END) AS goalsagainst');
        $query->select('rounds.roundcode');
        
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_round AS rounds ');
        
        $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_match AS matches ON rounds.id = matches.round_id ');
        $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt1 ON pt1.id = matches.projectteam1_id ');
        $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt2 ON pt2.id = matches.projectteam2_id  ');
           
        $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id as st1 ON st1.id = pt1.team_id ');
        $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t1 ON st1.team_id = t1.id ');
           
        $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id as st2 ON st2.id = pt2.team_id ');
        $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t2 ON st2.team_id = t2.id ');
        
        $query->where('rounds.project_id = '.$this->projectid);
        $query->where('( (st1.team_id ='.$this->teamid.' ) OR (st2.team_id ='.$this->teamid.' ) )' );
        $query->where('(matches.cancel IS NULL OR matches.cancel = 0)');
        $query->where('team1_result IS NOT NULL');
        $query->group('rounds.roundcode');   


                   
    		$db->setQuery( $query );
    		$this->matchdaytotals = $db->loadObjectList();
            
            if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
            $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'');
            }
            
            if ( !$this->matchdaytotals )
        {
            $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
        }
            
            
            
    		return $this->matchdaytotals;
    }
    
    /**
     * sportsmanagementModelTeamStats::getMatchDayTotals()
     * 
     * @return
     */
    function getMatchDayTotals( )
    {
        $option = JRequest::getCmd('option');
	    $mainframe = JFactory::getApplication();
        // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        
    	if ( is_null( $this->matchdaytotals ) )
    	{
    	   $query->select('rounds.id');
           $query->select('COUNT(matches.round_id) AS totalmatchespd');
           $query->select('COUNT(matches.id) as playedmatchespd');
           $query->select('SUM(matches.team1_result) AS homegoalspd');
           $query->select('SUM(matches.team2_result) AS guestgoalspd');
           $query->select('rounds.roundcode');

           $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_round AS rounds ');
           $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_match AS matches ON rounds.id = matches.round_id ');
           $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt1 ON pt1.id = matches.projectteam1_id ');
           $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt2 ON pt2.id = matches.projectteam2_id  ');
           
           $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id as st1 ON st1.id = pt1.team_id ');
           $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t1 ON st1.team_id = t1.id ');
           
           $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id as st2 ON st2.id = pt2.team_id ');
           $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t2 ON st2.team_id = t2.id ');
           
           $query->where('rounds.project_id = '.$this->projectid);
           $query->where('( (st1.team_id ='.$this->teamid.' ) OR (st2.team_id ='.$this->teamid.' ) )' );
           $query->where('(matches.cancel IS NULL OR matches.cancel = 0)');
           $query->group('rounds.roundcode');
        
            
    		$db->setQuery( $query );
            
            if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
            $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'');
         }
            
    		$this->matchdaytotals = $db->loadObjectList();
            
            if ( !$this->matchdaytotals )
        {
            $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
        }
            
            
            
    	}
    	return $this->matchdaytotals;
    }

    /**
     * sportsmanagementModelTeamStats::getTotalRounds()
     * 
     * @return
     */
    function getTotalRounds( )
    {
        $option = JRequest::getCmd('option');
	    $mainframe = JFactory::getApplication();
        // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $starttime = microtime(); 
        
        if ( is_null( $this->totalrounds ) )
        {
            $query->select('COUNT(id)');
           $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_round ');
           $query->where('project_id = '.$this->projectid);

//            $query= "SELECT COUNT(id)
//                     FROM #__joomleague_round
//                     WHERE project_id= ".$this->projectid;
                     
            $db->setQuery($query);
            
            if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
            $this->totalrounds = $db->loadResult();
        }
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'');
        }
        
        if ( !$this->totalrounds )
        {
            $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
        }
        
        return $this->totalrounds;
    }

   
    /**
     * sportsmanagementModelTeamStats::_getAttendance()
     * 
     * @return
     */
    function _getAttendance( )
    {
        $option = JRequest::getCmd('option');
	    $mainframe = JFactory::getApplication();
        // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $starttime = microtime(); 
        
    	if ( is_null( $this->attendanceranking ) )
    	{
    	   $query->select('matches.crowd');

        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match AS matches ');
        $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt1 ON pt1.id = matches.projectteam1_id ');
        $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id as st1 ON st1.id = pt1.team_id ');
        $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t1 ON st1.team_id = t1.id ');
        $query->join('LEFT',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_playground AS playground ON pt1.standard_playground = playground.id ');
        
        $query->where('st1.team_id = '.$this->teamid);
        $query->where('matches.crowd > 0 ');
        $query->where('matches.published = 1');

                       
    		$db->setQuery( $query );
        
            if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
    		$this->attendanceranking = $db->loadResultArray();
    	}
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'');
        }
            
    	         
        if ( !$this->attendanceranking )
        {
            $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
            $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Error');
        }
        
    	return $this->attendanceranking;
    }

	/**
	 * sportsmanagementModelTeamStats::getBestAttendance()
	 * 
	 * @return
	 */
	function getBestAttendance( )
	{
		$attendance = self::_getAttendance();
		return (count($attendance)>0) ? max($attendance) : 0;
	}

	/**
	 * sportsmanagementModelTeamStats::getWorstAttendance()
	 * 
	 * @return
	 */
	function getWorstAttendance( )
	{
		$attendance = self::_getAttendance();
		return (count($attendance)>0) ? min($attendance) : 0;
	}

	/**
	 * sportsmanagementModelTeamStats::getTotalAttendance()
	 * 
	 * @return
	 */
	function getTotalAttendance( )
	{
		$attendance = self::_getAttendance();
		return (count($attendance)>0) ? array_sum($attendance) : 0;
	}
	
	/**
	 * sportsmanagementModelTeamStats::getAverageAttendance()
	 * 
	 * @return
	 */
	function getAverageAttendance( )
	{
		$attendance = self::_getAttendance();
		return (count($attendance)>0) ? round(array_sum($attendance)/count($attendance), 0) : 0;
	}

	/**
	 * sportsmanagementModelTeamStats::getChartURL()
	 * 
	 * @return
	 */
	function getChartURL( )
	{
		$url = sportsmanagementHelperRoute::getTeamStatsChartDataRoute( $this->projectid, $this->teamid );
		$url = str_replace( '&', '%26', $url );
		return $url;
	}

	/**
	 * sportsmanagementModelTeamStats::getLogo()
	 * 
	 * @return
	 */
	function getLogo( )
	{
	   $option = JRequest::getCmd('option');
	    $mainframe = JFactory::getApplication();
        // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        
		$query->select('logo_big');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_club AS clubs ');
        $query->join('LEFT',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS teams ON clubs.id = teams.club_id ');
        $query->where('teams.id = '.$this->teamid);
    	$db->setQuery( $query );
    	$logo = JURI::root().$db->loadResult();

		return $logo;
	}

	/**
	 * sportsmanagementModelTeamStats::getResults()
	 * 
	 * @return
	 */
	function getResults()
	{
	   $option = JRequest::getCmd('option');
	    $mainframe = JFactory::getApplication();
        // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $starttime = microtime(); 
        
        $query->select('m.id, m.projectteam1_id, m.projectteam2_id, pt1.team_id AS team1_id, pt2.team_id AS team2_id');
        $query->select('m.team1_result, m.team2_result');
        $query->select('m.alt_decision, m.team1_result_decision, m.team2_result_decision');


        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match AS m ');
        $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt1 ON pt1.id = m.projectteam1_id ');
        $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt2 ON pt2.id = m.projectteam2_id  ');
           
        $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id as st1 ON st1.id = pt1.team_id ');
        $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t1 ON st1.team_id = t1.id ');
         
        $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id as st2 ON st2.id = pt2.team_id ');
        $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t2 ON st2.team_id = t2.id ');
           
        $query->where('pt1.project_id = '.$this->projectid);
        $query->where('( (st1.team_id ='.$this->teamid.' ) OR (st2.team_id ='.$this->teamid.' ) )' );
           
        $query->where('(m.team1_result IS NOT NULL OR m.alt_decision > 0)');
        $query->where('(m.cancel IS NULL OR m.cancel = 0)');
           


		$db->setQuery($query);
        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
        $matches = $db->loadObjectList();
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'');
        }
            
    	         
        if ( !$matches )
        {
            $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
        }
		
		$results = array(	'win' => array(), 'tie' => array(), 'loss' => array(), 'forfeit' => array(),
							'home_wins' => 0, 'home_draws' => 0, 'home_losses' => 0, 
							'away_wins' => 0, 'away_draws' => 0, 'away_losses' => 0,);
		foreach ($matches as $match)
		{
			if (!$match->alt_decision)
			{
				if ($match->team1_id == $this->teamid)
				{
					// We are the home team
					if ($match->team1_result > $match->team2_result)
					{
						$results['win'][] = $match;
						$results['home_wins']++;
					}
					else if ($match->team1_result < $match->team2_result)
					{
						$results['loss'][] = $match;
						$results['home_losses']++;
					}
					else
					{
						$results['tie'][] = $match;
						$results['home_draws']++;
					}
				}
				else
				{
					// We are the away team
					if ($match->team1_result > $match->team2_result)
					{
						$results['loss'][] = $match;
						$results['away_losses']++;
					}
					else if ($match->team1_result < $match->team2_result)
					{
						$results['win'][] = $match;
						$results['away_wins']++;
					}
					else
					{
						$results['tie'][] = $match;
						$results['away_draws']++;
					}
				}
			}
			else
			{
				if ($match->team1_id == $this->teamid)
				{
					// We are the home team
					if (empty($match->team1_result_decision)) {
						$results['forfeit'][] = $match;
					}
					else if (empty($match->team2_result_decision)) {
						$results['win'][] = $match;
					}
					else {
						if ($match->team1_result_decision > $match->team2_result_decision) {
							$results['win'][] = $match;
							$results['home_wins']++;
						}
						else if ($match->team1_result_decision < $match->team2_result_decision) {
							$results['loss'][] = $match;
							$results['home_losses']++;
						}
						else {
							$results['tie'][] = $match;
							$results['home_draws']++;
						}
					}
				}
				else
				{
					// We are the away team
					if (empty($match->team2_result_decision)) {
						$results['forfeit'][] = $match;
					}
					else if (empty($match->team1_result_decision)) {
						$results['win'][] = $match;
					}
					else {
						if ($match->team1_result_decision > $match->team2_result_decision) {
							$results['loss'][] = $match;
							$results['away_losses']++;
						}
						else if ($match->team1_result_decision < $match->team2_result_decision) {
							$results['win'][] = $match;
							$results['away_wins']++;
						}
						else {
							$results['tie'][] = $match;
							$results['away_draws']++;
						}
					}
				}
			}
		}
		
		return $results;
	}
	
	/**
	 * sportsmanagementModelTeamStats::getStats()
	 * 
	 * @return
	 */
	function getStats()
	{
		$stats = sportsmanagementModelProject::getProjectStats();
		
		// those are per positions, group them so that we have team globlas stats
		
		$teamstats = array();
		foreach ($stats as $pos => $pos_stats)
		{
			foreach ($pos_stats as $k => $stat) 
			{
				if ($stat->getParam('show_in_teamstats', 1))
				{
					if (!isset($teamstats[$k])) 
					{
						$teamstats[$k] = $stat;
						$teamstats[$k]->value = $stat->getRosterTotalStats($this->teamid, $this->projectid);
					}
				}
			}
		}
		
		return $teamstats;
	}
}
?>
