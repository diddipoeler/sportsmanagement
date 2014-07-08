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
 * sportsmanagementModelStats
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelStats extends JModelLegacy
{
	var $projectid = 0;
	var $divisionid = 0;
	var $highest_home = null;
	var $highest_away = null;
	var $totals = null;
	var $matchdaytotals = null;
	var $totalrounds = null;
	var $attendanceranking = null;

	/**
	 * sportsmanagementModelStats::__construct()
	 * 
	 * @return
	 */
	function __construct( )
	{
		parent::__construct();

		$this->projectid = JRequest::getInt( "p", 0 );
		$this->divisionid = JRequest::getint( "division", 0 );
        
        sportsmanagementModelProject::$projectid = $this->projectid;
	}

//	/**
//	 * sportsmanagementModelStats::getDivision()
//	 * 
//	 * @return
//	 */
//	function getDivision()
//	{
//		$division = null;
//		if ($this->divisionid != 0)
//		{
//			$division = parent::getDivision($this->divisionid);
//		}
//		return $division;
//	}
//
	
    /**
	 * sportsmanagementModelStats::getHighest()
	 * 
	 * @return
	 */
	function getHighest($which = 'HOME' )
	{
	   $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        // Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
        $starttime = microtime(); 
        
//		if ( is_null( $this->highest_home ) )
//		{
			$query->select('t1.name AS hometeam');
            $query->select('t2.name AS guestteam');
            $query->select('t1.id AS hometeam_id');
            $query->select('pt1.id AS project_hometeam_id');
            $query->select('matches.team1_result AS homegoals');
            $query->select('matches.team2_result AS guestgoals');
            $query->select('t2.id AS awayteam_id');
            $query->select('pt2.id AS project_awayteam_id');
            
            $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match AS matches');
            $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt1 ON pt1.id = matches.projectteam1_id');
            $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id as st1 ON st1.id = pt1.team_id ');
            $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t1 ON st1.team_id = t1.id ');
        
            $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt2 ON pt2.id = matches.projectteam2_id');
            $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id as st2 ON st2.id = pt2.team_id ');
            $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t2 ON st2.team_id = t2.id ');
            
            $query->where('pt1.project_id = '.$this->projectid);
            
//            $query  = ' SELECT t1.name AS hometeam, '
//				. ' t2.name AS guestteam, '
//				. ' t1.id AS hometeam_id, '
//				. ' pt1.id AS project_hometeam_id, '
//				. ' team1_result AS homegoals, '
//				. ' team2_result AS guestgoals, '
//				. ' t2.id AS awayteam_id, '
//				. ' pt2.id AS project_awayteam_id '
//				. ' FROM #__joomleague_match as matches '
//				. ' INNER JOIN #__joomleague_project_team pt1 ON pt1.id = matches.projectteam1_id '
//				. ' INNER JOIN #__joomleague_team t1 ON t1.id = pt1.team_id '
//				. ' INNER JOIN #__joomleague_project_team pt2 ON pt2.id = matches.projectteam2_id '
//				. ' INNER JOIN #__joomleague_team t2 ON t2.id = pt2.team_id '
//				. ' WHERE pt1.project_id = '.$this->projectid
//			;
            
			if ($this->divisionid != 0)
			{
			 $query->where('pt1.division.id = '.$this->divisionid);
//				$query .= ' AND pt1.division_id = '.$this->divisionid;
			}
            
            $query->where('published = 1');
            $query->where('alt_decision = 0');
            $query->where('(matches.cancel IS NULL OR matches.cancel = 0)');
            
            switch ($which)
            {
                case 'HOME':
                $query->where('team1_result > team2_result');
                $query->order('(team1_result - team2_result) DESC');
                break;
                case 'AWAY':
                $query->where('team2_result > team1_result');
                $query->order('(team2_result - team1_result) DESC');
                break;
                
            }
            
//			$query .= ' AND published=1 '
//				. ' AND alt_decision=0 '
//				. ' AND team1_result > team2_result '
//				. ' AND (matches.cancel IS NULL OR matches.cancel = 0)'	
//				. ' ORDER BY (team1_result-team2_result) DESC '
//			;

			$db->setQuery($query, 0, 1);
            
            //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'');
            
            if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
            switch ($which)
            {
                case 'HOME':
                $this->highest_home = $db->loadObject();
                //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' '.'<pre>'.print_r($db->getErrorMsg(),true).'</pre>' ),'Error');
                return $this->highest_home;
                break;
                case 'AWAY':
                $this->highest_away = $db->loadObject();
                //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' '.'<pre>'.print_r($db->getErrorMsg(),true).'</pre>' ),'Error');
        		return $this->highest_away;
                break;
                
            }
            
			//echo($this->_db->getQuery());
			//$this->highest_home = $db->loadObject();
		//}
		//return $this->highest_home;
	}
    
    
    
//    /**
//	 * sportsmanagementModelStats::getHighestHome()
//	 * 
//	 * @return
//	 */
//	function getHighestHome( )
//	{
//	   $mainframe = JFactory::getApplication();
//        $option = JRequest::getCmd('option');
//        // Create a new query object.
//		$db		= $this->getDbo();
//		$query	= $db->getQuery(true);
//        $starttime = microtime(); 
//        
//		if ( is_null( $this->highest_home ) )
//		{
//			$query  = ' SELECT t1.name AS hometeam, '
//				. ' t2.name AS guestteam, '
//				. ' t1.id AS hometeam_id, '
//				. ' pt1.id AS project_hometeam_id, '
//				. ' team1_result AS homegoals, '
//				. ' team2_result AS guestgoals, '
//				. ' t2.id AS awayteam_id, '
//				. ' pt2.id AS project_awayteam_id '
//				. ' FROM #__joomleague_match as matches '
//				. ' INNER JOIN #__joomleague_project_team pt1 ON pt1.id = matches.projectteam1_id '
//				. ' INNER JOIN #__joomleague_team t1 ON t1.id = pt1.team_id '
//				. ' INNER JOIN #__joomleague_project_team pt2 ON pt2.id = matches.projectteam2_id '
//				. ' INNER JOIN #__joomleague_team t2 ON t2.id = pt2.team_id '
//				. ' WHERE pt1.project_id = '.$this->projectid
//			;
//			if ($this->divisionid != 0)
//			{
//				$query .= ' AND pt1.division_id = '.$this->divisionid;
//			}
//			$query .= ' AND published=1 '
//				. ' AND alt_decision=0 '
//				. ' AND team1_result > team2_result '
//				. ' AND (matches.cancel IS NULL OR matches.cancel = 0)'	
//				. ' ORDER BY (team1_result-team2_result) DESC '
//			;
//
//			$this->_db->setQuery($query, 0, 1);
//			//echo($this->_db->getQuery());
//			$this->highest_home = $this->_db->loadObject();
//		}
//		return $this->highest_home;
//	}

//	/**
//	 * sportsmanagementModelStats::getHighestAway()
//	 * 
//	 * @return
//	 */
//	function getHighestAway( )
//	{
//	   $mainframe = JFactory::getApplication();
//        $option = JRequest::getCmd('option');
//        // Create a new query object.
//		$db		= $this->getDbo();
//		$query	= $db->getQuery(true);
//        $starttime = microtime(); 
//        
//		if ( is_null( $this->highest_away ) )
//		{
//			$query  = ' SELECT t1.name AS hometeam, '
//				. ' t1.id AS hometeam_id, '
//				. ' pt1.id AS project_hometeam_id, '
//				. ' t2.name AS guestteam, '
//				. ' pt2.id AS project_awayteam_id, '
//				. ' t2.id AS awayteam_id, '
//				. ' team1_result AS homegoals, '
//				. ' team2_result AS guestgoals '
//				. ' FROM #__joomleague_match as matches '
//				. ' INNER JOIN #__joomleague_project_team pt1 ON pt1.id = matches.projectteam1_id '
//				. ' INNER JOIN #__joomleague_team t1 ON t1.id = pt1.team_id '
//				. ' INNER JOIN #__joomleague_project_team pt2 ON pt2.id = matches.projectteam2_id '
//				. ' INNER JOIN #__joomleague_team t2 ON t2.id = pt2.team_id '
//				. ' WHERE pt1.project_id = '.$this->projectid
//			;
//			if ($this->divisionid != 0)
//			{
//				$query .= ' AND pt1.division_id = '.$this->divisionid;
//			}
//			$query .= ' AND published=1 '
//				. ' AND alt_decision=0 '
//				. ' AND team2_result > team1_result '
//				. ' AND (matches.cancel IS NULL OR matches.cancel = 0)'	
//				. ' ORDER BY (team2_result-team1_result) DESC '
//			;
//
//			$this->_db->setQuery($query, 0, 1);
//			$this->highest_away = $this->_db->loadObject();
//		}
//		return $this->highest_away;
//	}

	/**
	 * sportsmanagementModelStats::getSeasonTotals()
	 * 
	 * @return
	 */
	function getSeasonTotals( )
	{
	   $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        // Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
        $Subquery	= $db->getQuery(true);
        
        $starttime = microtime(); 
        
		if ( is_null( $this->totals ) )
		{
			$query->select('COUNT(matches.id) AS totalmatches');
          $query->select('COUNT(matches.team1_result) as playedmatches');
          $query->select('SUM(matches.team1_result) AS homegoals');
          $query->select('SUM(matches.team2_result) AS guestgoals');
          $query->select('SUM(team1_result + team2_result) AS sumgoals');
          $query->select('SUM(crowd) AS sumspectators');
          
          $Subquery->select('COUNT(crowd)');
          $Subquery->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match AS sub1');
          $Subquery->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS sub2 ON sub2.id = sub1.projectteam1_id');
          $Subquery->where('sub1.crowd > 0');
          $Subquery->where('sub1.published = 1');
          $Subquery->where('(sub1.cancel IS NULL OR sub1.cancel = 0)');
          $Subquery->where('sub2.project_id = '.$this->projectid);
          
          $query->select('('.$Subquery.') AS attendedmatches');
          
          $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match AS matches');
          $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt1 ON pt1.id = matches.projectteam1_id');
            
          $query->where('pt1.project_id = '.$this->projectid);
            
//            $query  = ' SELECT '
//				. ' COUNT(matches.id) AS totalmatches, '
//				. ' COUNT(team1_result) AS playedmatches, '
//				. ' SUM(team1_result) AS homegoals, '
//				. ' SUM(team2_result) AS guestgoals, '
//				. ' SUM(team1_result + team2_result) AS sumgoals, '
//				. ' (SELECT COUNT(crowd) '
//				. '		 FROM #__joomleague_match AS sub1 '
//				. '		 INNER JOIN #__joomleague_project_team sub2 ON sub2.id = sub1.projectteam1_id '
//				. '		 WHERE sub1.crowd > 0 '
//				. ' 		AND sub1.published = 1 '
//				. ' 		AND (sub1.cancel IS NULL OR sub1.cancel = 0) '
//				. ' 		AND sub2.project_id = '.$this->projectid.') AS attendedmatches, '
//                
//				. ' SUM(crowd) AS sumspectators '
//				. ' FROM #__joomleague_match AS matches'
//				. ' INNER JOIN #__joomleague_project_team pt1 ON pt1.id = matches.projectteam1_id '
//				. ' WHERE pt1.project_id = '.$this->projectid
//			;
            
			if ($this->divisionid != 0)
			{
			 $query->where('pt1.division.id = '.$this->divisionid);
//				$query .= ' AND pt1.division_id = '.$this->divisionid;
			}
            
            $query->where('published = 1');
            $query->where('(matches.cancel IS NULL OR matches.cancel = 0)');
            
//			$query .= ' AND published=1 '
//				. ' AND (matches.cancel IS NULL OR matches.cancel = 0)'	
//			;
            
			$db->setQuery($query, 0, 1);
            
            //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'');
            
            if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
            
			$this->totals = $db->loadObject();
		}
		return $this->totals;
	}

	/**
	 * sportsmanagementModelStats::getChartData()
	 * 
	 * @return
	 */
	function getChartData( )
	{
	   $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        // Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
        $starttime = microtime(); 
        
		if ( is_null( $this->matchdaytotals ) )
		{
		  $query->select('rounds.id');
          $query->select('COUNT(matches.id) AS totalmatchespd');
          $query->select('COUNT(matches.team1_result) as playedmatchespd');
          $query->select('SUM(matches.team1_result) AS homegoalspd');
          $query->select('SUM(matches.team2_result) AS guestgoalspd');
          $query->select('rounds.roundcode');
          
          $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_round AS rounds');
          $query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_match AS matches ON rounds.id = matches.round_id');
          
//			$query  = ' SELECT rounds.id,'
//				. ' COUNT(matches.id) AS totalmatchespd,'
//				. ' COUNT(matches.team1_result) as playedmatchespd,'
//				. ' SUM(matches.team1_result) AS homegoalspd,'
//				. ' SUM(matches.team2_result) AS guestgoalspd,'
//				. ' rounds.roundcode'
//				. ' FROM #__joomleague_round AS rounds'
//				. ' LEFT JOIN #__joomleague_match AS matches ON rounds.id = matches.round_id'
//			;
			if ($this->divisionid != 0)
			{
			 $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_division AS division ON division.project_id = rounds.project_id');
             $query->where('rounds.project_id = '.$this->projectid);
             $query->where('division.id = '.$this->divisionid);
             
//				$query .= ' INNER JOIN #__joomleague_division AS division ON division.project_id=rounds.project_id'
//					. ' WHERE rounds.project_id = '.$this->projectid
//					. ' AND division.id = '.$this->divisionid;
			}
			else
			{
			 $query->where('rounds.project_id = '.$this->projectid);
//				$query .= ' WHERE rounds.project_id = '.$this->projectid;
 			}
            
            $query->where('(matches.cancel IS NULL OR matches.cancel = 0)');
            $query->group('rounds.roundcode');
            
//			$query .= ' AND (matches.cancel IS NULL OR matches.cancel = 0)'
//				. ' GROUP BY rounds.roundcode'
//			;
			$db->setQuery( $query );
            
            if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
            
            
			$this->matchdaytotals = $db->loadObjectList();
		}
		return $this->matchdaytotals;
	}

	/**
	 * sportsmanagementModelStats::getTotalRounds()
	 * 
	 * @return
	 */
	function getTotalRounds( )
	{
	   $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        // Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
        $starttime = microtime(); 
        
		if ( is_null( $this->totalrounds ) )
		{
		  $query->select('COUNT(id)');
          $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_round');
          $query->where('project_id = '.$this->projectid);
          
//			$query  = ' SELECT COUNT(id)'
//				. ' FROM #__joomleague_round'
//				. ' WHERE project_id = '.$this->projectid
//			;
			$db->setQuery($query);
			$this->totalrounds = $db->loadResult();
		}
        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
		return $this->totalrounds;
	}

	/**
	 * sportsmanagementModelStats::getAttendanceRanking()
	 * 
	 * @return
	 */
	function getAttendanceRanking( )
	{
	   $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        // Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
        $starttime = microtime(); 
        
		if ( is_null( $this->attendanceranking ) )
		{
		  $query->select('SUM(matches.crowd) AS sumspectatorspt');
          $query->select('AVG(matches.crowd) AS avgspectatorspt');
          $query->select('t1.name AS team');
          $query->select('t1.id AS teamid');
          $query->select('playground.max_visitors AS capacity');
          
          $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'match AS matches ');
          $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team pt1 ON pt1.id = matches.projectteam1_id ');
          $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id as st ON st.id = pt1.team_id ');
          $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_team t1 ON t1.id = st.team_id');
          $query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_playground AS playground ON pt1.standard_playground = playground.id');
          
          $query->where('pt1.project_id = '.$this->projectid);
          
//			$query  = ' SELECT '
//				. ' SUM(matches.crowd) AS sumspectatorspt, '
//				. ' AVG(matches.crowd) AS avgspectatorspt, '
//				. ' t1.name AS team, '
//				. ' t1.id AS teamid, '
//				. ' playground.max_visitors AS capacity '
//				. ' FROM #__joomleague_match AS matches '
//				. ' INNER JOIN #__joomleague_project_team pt1 ON pt1.id = matches.projectteam1_id '
//				. ' INNER JOIN #__joomleague_team t1 ON t1.id = pt1.team_id '
//				. ' LEFT JOIN #__joomleague_playground AS playground ON pt1.standard_playground = playground.id '
//				. ' WHERE pt1.project_id = '.$this->projectid
//			;
            
			if ($this->divisionid != 0)
			{
			 $query->where('pt1.division.id = '.$this->divisionid);
//				$query .= ' AND pt1.division_id = '.$this->divisionid;
			}
            
            $query->where('matches.published = 1');
            $query->where('matches.crowd > 0');
            $query->group('matches.projectteam1_id');
            $query->order('avgspectatorspt DESC');
            
//			$query .= ' AND matches.published=1 '
//				. ' AND matches.crowd > 0 '
//				. ' GROUP BY matches.projectteam1_id '
//				. ' ORDER BY avgspectatorspt DESC'
//			;

			$db->setQuery($query);
            
            if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
            
			$this->attendanceranking = $db->loadObjectList();
		}
		return $this->attendanceranking;
	}

	/**
	 * sportsmanagementModelStats::getBestAvg()
	 * 
	 * @return
	 */
	function getBestAvg( )
	{
		$attendanceranking = self::getAttendanceRanking();
		return (count($attendanceranking)>0) ? round( $attendanceranking[0]->avgspectatorspt ) : 0;
	}

	/**
	 * sportsmanagementModelStats::getBestAvgTeam()
	 * 
	 * @return
	 */
	function getBestAvgTeam( )
	{
		$attendanceranking = self::getAttendanceRanking();
		return (count($attendanceranking)>0) ? $attendanceranking[0]->team : 0;
	}

	/**
	 * sportsmanagementModelStats::getWorstAvg()
	 * 
	 * @return
	 */
	function getWorstAvg( )
	{
		$attendanceranking = self::getAttendanceRanking();
		$worstavg = 0;
		if ( count( $attendanceranking ) )
		{
			$n = count( $attendanceranking );
			$worstavg = round( $attendanceranking[$n-1]->avgspectatorspt );
		}
		return $worstavg;
	}

	/**
	 * sportsmanagementModelStats::getWorstAvgTeam()
	 * 
	 * @return
	 */
	function getWorstAvgTeam( )
	{
		$attendanceranking = self::getAttendanceRanking();
		$worstavgteam = 0;
		if ( count( $attendanceranking ) )
		{
			$n = count( $attendanceranking );
			$worstavgteam = $attendanceranking[$n-1]->team;
		}
		return $worstavgteam;
	}

	/**
	 * sportsmanagementModelStats::getChartURL()
	 * 
	 * @return
	 */
	function getChartURL( )
	{
		$url = sportsmanagementHelperRoute::getStatsChartDataRoute( $this->projectid, $this->divisionid );
		$url = str_replace( '&', '%26', $url );
		return $url;
	}
	
	//comparisations in stats view	
	/**
	 * sportsmanagementModelStats::teamNameCmp2()
	 * 
	 * @param mixed $a
	 * @param mixed $b
	 * @return
	 */
	function teamNameCmp2( &$a, &$b){
	  return strcasecmp ($a->team, $b->team);
	}

	/**
	 * sportsmanagementModelStats::totalattendCmp()
	 * 
	 * @param mixed $a
	 * @param mixed $b
	 * @return
	 */
	function totalattendCmp( &$a, &$b){
	  $res = ($a->sumspectatorspt - $b->sumspectatorspt);

	  return $res;
	}

	/**
	 * sportsmanagementModelStats::avgattendCmp()
	 * 
	 * @param mixed $a
	 * @param mixed $b
	 * @return
	 */
	function avgattendCmp( &$a, &$b){
	  $res = ($a->avgspectatorspt - $b->avgspectatorspt);
	  return $res;
	}

	/**
	 * sportsmanagementModelStats::capacityCmp()
	 * 
	 * @param mixed $a
	 * @param mixed $b
	 * @return
	 */
	function capacityCmp( &$a, &$b){
	  $res = ($a->capacity - $b->capacity);
	  return $res;
	}

	/**
	 * sportsmanagementModelStats::utilisationCmp()
	 * 
	 * @param mixed $a
	 * @param mixed $b
	 * @return
	 */
	function utilisationCmp( &$a, &$b){
	  $res = (($a->capacity?($a->avgspectatorspt / $a->capacity):0) - ($b->capacity>0?($b->avgspectatorspt / $b->capacity):0));
	  return $res;
	}
}

?>