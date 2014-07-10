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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.model');

//require_once (JLG_PATH_ADMIN .DS.'models'.DS.'rounds.php');
//require_once( JPATH_COMPONENT.DS.'helpers'.DS.'ranking.php');
//require_once( JLG_PATH_SITE . DS . 'models' . DS . 'project.php' );

/**
 * sportsmanagementModelCurve
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelCurve extends JModelLegacy
{
	var $project = null;
	var $projectid = 0;
	var $teamid1 = 0;
	var $team1 = array();
	var $teamid2 = 0;
	var $team2 = array();
	var $allteams = null;
	var $divisions = null;
	var $favteams = null;
	var $divlevel = null;
	var $height = 180;
	var $selectoptions = array();
	var $teamlist2options = array();

	// Chart Data
	var $division = 0;
	var $round = 0;
	var $roundsName = array();
	var $ranking1 = array();
	var $ranking2 = array();
	var $ranking = array(); // cache for ranking function return data
	var $teamcount = array();
	
	/**
	 * sportsmanagementModelCurve::__construct()
	 * 
	 * @return
	 */
	function __construct( )
	{
		parent::__construct( );
		$this->projectid = JRequest::getInt('p', 0);
		$this->division  = JRequest::getInt('division', 0);
		$this->teamid1   = JRequest::getInt('tid1', 0);
		$this->teamid2   = JRequest::getInt('tid2', 0);
		$this->both      = JRequest::getInt('both', 0);
        sportsmanagementModelProject::$projectid = $this->projectid;
        
		$this->determineTeam1And2();
	}

	/**
	 * sportsmanagementModelCurve::determineTeam1And2()
	 * 
	 * @return
	 */
	function determineTeam1And2()
	{
	   $option = JRequest::getCmd('option');
	$mainframe = JFactory::getApplication();
        // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $starttime = microtime(); 
        
		// Use favorite team(s) in case both teamids are 0
		if (($this->teamid1 == 0) && ($this->teamid2 == 0))
		{
			$favteams = sportsmanagementModelProject::getFavTeams();
			$selteam1 = ( isset( $favteams[0] ) ) ? $favteams[0] : 0;
			$selteam2 = ( isset( $favteams[1] ) ) ? $favteams[1] : 0;
			$this->teamid1 = ($this->teamid1 == 0 ) ? $selteam1 : $this->teamid1;
			$this->teamid2 = ($this->teamid2 == 0 ) ? $selteam2 : $this->teamid2;
		}

		// When (one of) the teams are not specified, search for the next unplayed or the latest played match
		if (($this->teamid1 == 0) || ($this->teamid2 == 0))
		{
			
            $query->select('t1.id AS teamid1, t2.id AS teamid2');
            $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match AS m ');
            $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt1 ON m.projectteam1_id = pt1.id ');
            $query->where('pt1.project_id='.$db->Quote($this->projectid));
            
//            $query  = ' SELECT t1.id AS teamid1, t2.id AS teamid2'
//				. ' FROM #__joomleague_match AS m'
//				. ' INNER JOIN #__joomleague_project_team AS pt1 ON m.projectteam1_id=pt1.id'
//				. ' AND pt1.project_id='.$this->_db->Quote($this->projectid);
			if ($this->division)
			{
//				$query .= ' AND pt1.division_id='.$this->_db->Quote($this->division);
                $query->where('pt1.division_id='.$db->Quote($this->division));
			}
            
            $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t1 ON st1.team_id = t1.id ');
            $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id as st1 ON st1.id = pt1.team_id ');
            $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt2 ON m.projectteam2_id = pt2.id ');
            $query->where('pt2.project_id='.$db->Quote($this->projectid));
            
//			$query .= ' INNER JOIN #__joomleague_team AS t1 ON pt1.team_id=t1.id'
//				. ' INNER JOIN #__joomleague_project_team AS pt2 ON m.projectteam2_id=pt2.id'
//				. ' AND pt2.project_id='.$this->_db->Quote($this->projectid);
                
			if ($this->division)
			{
//				$query .= ' AND pt2.division_id='.$this->_db->Quote($this->division);
                $query->where('pt2.division_id='.$db->Quote($this->division));
			}
            
            $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t2 ON st2.team_id = t2.id ');
            $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id as st2 ON st2.id = pt2.team_id ');
            $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS p ON pt1.project_id = p.id AND pt2.project_id = p.id ');
            
//			$query .= ' INNER JOIN #__joomleague_team AS t2 ON pt2.team_id=t2.id'
//				. ' INNER JOIN #__joomleague_project AS p ON pt1.project_id=p.id AND pt2.project_id=p.id';

//			$where = ' WHERE m.published=1 AND m.cancel=0';
            $query->where('m.published = 1 AND m.cancel = 0');
            
			if ($this->teamid1)
			{
				$quoted_team_id = $db->Quote($this->teamid1);
				//$team = 't1';
                $team = 'st1';
			}
			else
			{
				$quoted_team_id = $db->Quote($this->teamid2);
				//$team = 't2';
                $team = 'st2';
			}
            
			if ($this->both)
			{
//				$where .= ' AND (t1.id='.$quoted_team_id.' OR t2.id='.$quoted_team_id.')';
                //$query->where('(t1.id='.$quoted_team_id.' OR t2.id='.$quoted_team_id.')');
                $query->where('(st1.team_id='.$quoted_team_id.' OR st2.team_id='.$quoted_team_id.')');
			}
			else
			{
//				$where .= ' AND '.$team.'.id='.$quoted_team_id;
                //$query->where($team.'.id='.$quoted_team_id);
                $query->where($team.'.team_id='.$quoted_team_id);
			}
            
			$config = sportsmanagementModelProject::getTemplateConfig($this->getName());
			$expiry_time = $config ? $config['expiry_time'] : 0;
            
            $query->where('(m.team1_result IS NULL OR m.team2_result IS NULL)');
            $query->where('DATE_ADD(m.match_date, INTERVAL '.$this->_db->Quote($expiry_time).' MINUTE) >= NOW()');
            
//			$where_unplayed = ' AND (m.team1_result IS NULL OR m.team2_result IS NULL)'
//					. ' AND DATE_ADD(m.match_date, INTERVAL '.$this->_db->Quote($expiry_time).' MINUTE)'
//					// . '    >= CONVERT_TZ(UTC_TIMESTAMP(), '.$this->_db->Quote('UTC').', p.timezone)';
//                    . ' >= NOW()';
                    
//			$order = ' ORDER BY m.match_date';
            $query->order('m.match_date');
            
			$db->setQuery($query);
            

            
            if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
            $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
			$match = $db->loadObject();

			// If there is no unplayed match left, take the latest match played
			if (!isset($match))
			{
//				$order = ' ORDER BY m.match_date DESC';
                $query->clear('order');
                $query->order('m.match_date DESC');
                
                $starttime = microtime(); 
                
				$db->setQuery($query);
                
                if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
            $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
				$match = $db->loadObject();
			}
			if (isset($match))
			{
				$this->teamid1 = $match->teamid1;
				$this->teamid2 = $match->teamid2;
			}
		}
	}

	/**
	 * sportsmanagementModelCurve::getDivLevel()
	 * 
	 * @return
	 */
	function getDivLevel()
	{
		if ( is_null( $this->divlevel ) )
		{
			$config = sportsmanagementModelProject::getTemplateConfig("ranking");
			$this->divlevel = $config['default_division_view'];
		}
		return $this->divlevel;
	}

	/**
	 * sportsmanagementModelCurve::getTeam1()
	 * 
	 * @param integer $division
	 * @return
	 */
	function getTeam1($division=0)
	{
		if (!$this->teamid1) {
			return false;
		}
		$data = self::getDataByDivision($division);
		foreach ($data as $team)
		{
			if ($team->id == $this->teamid1) {
				return $team;
			}
		}
		return false;
	}

	/**
	 * sportsmanagementModelCurve::getTeam2()
	 * 
	 * @param integer $division
	 * @return
	 */
	function getTeam2($division=0)
	{
		if (!$this->teamid2) {
			return false;
		}
		$data = self::getDataByDivision($division);
		foreach ($data as $team)
		{
			if ($team->id == $this->teamid2) {
				return $team;
			}
		}
		return false;
	}

	/**
	 * sportsmanagementModelCurve::getDivision()
	 * 
	 * @return
	 */
	function getDivision( )
	{
		return sportsmanagementModelProject::getDivision($this->division);
	}

	/**
	 * sportsmanagementModelCurve::getDivisionId()
	 * 
	 * @return
	 */
	function getDivisionId( )
	{
		return $this->division;
	}
	
	/**
	 * sportsmanagementModelCurve::getDataByDivision()
	 * 
	 * @param integer $division
	 * @return
	 */
	function getDataByDivision($division=0)
	{
	   $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        
		$project = sportsmanagementModelProject::getProject();
		$rounds  = sportsmanagementModelProject::getRounds();
		$teams   = sportsmanagementModelProject::getTeamsIndexedByPtid($division);
		
        //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' teams<br><pre>'.print_r($teams,true).'</pre>'),'');
        	
		$rankinghelper = JSMRanking::getInstance($project);
		$rankinghelper->setProjectId( $project->id );
		//$mdlRounds = JModelLegacy::getInstance("Rounds", "sportsmanagementModel");
		//$mdlRounds->setProjectId($project->id);
        sportsmanagementModelRounds::$_project_id = $project->id;
		$firstRound = sportsmanagementModelRounds::getFirstRound($project->id);
		$firstRoundId = $firstRound['id'];
		
		$rankings = array();
		foreach ($rounds as $r)
		{
			$rankings[$r->id] = $rankinghelper->getRanking($firstRoundId, 
															$r->id, 
															$division);
		}
        
		foreach ($teams as $ptid => $team)
		{
			if($team->is_in_score==0) continue;
			$team_rankings = array();
			foreach ($rankings as $roundcode => $t)
			{
				if(empty($t[$ptid])) continue;
				$team_rankings[] = $t[$ptid]->rank;
			}
			$teams[$ptid]->rankings = $team_rankings;
		}
		return $teams;
	}
}
?>
