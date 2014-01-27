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

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.model');

//require_once( JPATH_COMPONENT.DS . 'helpers' . DS . 'ranking.php' );
//require_once( JLG_PATH_SITE . DS . 'models' . DS . 'project.php' );

class sportsmanagementModelNextMatch extends JModel
{
	var $project = null;
	var $matchid = 0;
	var $projectteamid = 0;
	var $projectid = 0;
	var $divisionid = 0;
	var $showpics = 0;
	var $ranking = null;
	var $teams = null;

	/**
	 * caching match data
	 * @var object
	 */
	var $_match = null;

	function __construct( )
	{
		parent::__construct( );
		$this->projectid = JRequest::getInt( "p", 0 );
		$this->matchid = JRequest::getInt( "mid", 0 );
		$this->showpics = JRequest::getInt( "pics", 0 );
		$this->projectteamid = JRequest::getInt( "ptid", 0 );
        if ( $this->projectteamid )
        {
		self::getSpecifiedMatch($this->projectid, $this->projectteamid, $this->matchid);
        }
        
	}

	function getSpecifiedMatch($projectId, $projectTeamId, $matchId)
	{
	   $mainframe = JFactory::getApplication();
       $option = JRequest::getCmd('option');
       // Create a new query object.		
	   $db = JFactory::getDBO();
	   $query = $db->getQuery(true);
        
		if (!$this->_match)
		{
		  // Select some fields
		  $query->select('m.*, DATE_FORMAT(m.time_present, "%H:%i") time_present');
          $query->select('t1.project_id');
          $query->select('r.roundcode');
          
          $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match AS m  ');

			$config = sportsmanagementModelProject::getTemplateConfig($this->getName());
			$expiry_time = $config ? $config['expiry_time'] : 0;
			
            $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_round AS r ON r.id = m.round_id ');
            $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS t1 ON t1.id = m.projectteam1_id ');
            $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS t2 ON t2.id = m.projectteam2_id ');
            $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS p ON p.id = t1.project_id ');
            $query->where('DATE_ADD(m.match_date, INTERVAL '.$db->Quote($expiry_time).' MINUTE) >= NOW()');
/**
 *             $query =  ' SELECT m.*, DATE_FORMAT(m.time_present, "%H:%i") time_present, t1.project_id '
 * 			  . '              , r.roundcode '
 * 				. ' FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match AS m '
 * 			  . ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_round AS r ON r.id = m.round_id '
 * 				. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS t1 ON t1.id = m.projectteam1_id '
 * 				. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS t2 ON t2.id = m.projectteam2_id '
 * 				. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS p ON p.id = t1.project_id '
 * 				. ' WHERE DATE_ADD(m.match_date, INTERVAL '.$this->_db->Quote($expiry_time).' MINUTE)'
 * // TODO: for now the timezone implementation does not work for quite some users, so it is temporarily disabled.
 * // Because the old serveroffset field is not available anymore in the database schema, this means that the times
 * // are not correctly translated to some timezone; the times as present in the database are just taken. 
 * //				. '    >= CONVERT_TZ(UTC_TIMESTAMP(), '.$this->_db->Quote('UTC').', p.timezone)'
 * 				. '    >= NOW()'
 * 			. ' AND m.cancel=0';
 */
            
            $query->where('m.cancel = 0');
			if ($matchId)
			{
				//$query .= ' AND m.id = ' . $this->_db->Quote($matchId);
                $query->where('m.id = '.$db->Quote($matchId) );
			}
			else
			{
				//$query .= ' AND (team1_result is null  OR  team2_result is null) ';
                $query->where('(team1_result is null  OR  team2_result is null)');
				if ($projectTeamId)
				{
/**
 *                     $query .= ' AND '
 * 						. ' ( '
 * 						. '       m.projectteam1_id = '. $this->_db->Quote($projectTeamId).' OR '
 * 						. '       m.projectteam2_id = '. $this->_db->Quote($projectTeamId)
 * 						. ' ) ';
 */
                         $query->where('(m.projectteam1_id = '.$db->Quote($projectTeamId).' OR m.projectteam2_id = '. $db->Quote($projectTeamId).')');
				}
				else
				{
					//$query .= ' AND (m.projectteam1_id > 0  OR  m.projectteam2_id > 0) ';
                    $query->where('(m.projectteam1_id > 0  OR  m.projectteam2_id > 0)');
				}
			}
			if ($projectId)
			{
				//$query .= ' AND t1.project_id = '.$this->_db->Quote($projectId);
                $query->where('t1.project_id = '.$db->Quote($projectId) );
			}
			//$query .= ' ORDER BY m.match_date';
            $query->order('m.match_date');
            
			$db->setQuery($query, 0, 1);
			$this->_match = $db->loadObject();
            
        if ( !$this->_match  )
	    {
		$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.'<pre>'.print_r($db->getErrorMsg(),true).'</pre>' ),'Error');
	    }
        
			if($this->_match)
			{
				$this->projectid = $this->_match->project_id;
				$this->matchid = $this->_match->id;
			}
		}
		return $this->_match;
	}

	/**
	 * get match info
	 * @return object
	 */
	function getMatch()
	{
	   $mainframe = JFactory::getApplication();
       $option = JRequest::getCmd('option');
       // Create a new query object.		
	   $db = JFactory::getDBO();
	   $query = $db->getQuery(true);
       
		if (empty($this->_match))
		{
		  // Select some fields
		$query->select('m.*, DATE_FORMAT(m.time_present, "%H:%i") time_present' );
        $query->select('t1.project_id');
        $query->select('r.roundcode');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match AS m ');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS t1 ON t1.id = m.projectteam1_id ');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_round AS r ON r.id = m.round_id ');
        $query->where('m.id = '. $db->Quote($this->matchid) );
        
        
/**
 * 			$query = ' SELECT m.*, DATE_FORMAT(m.time_present, "%H:%i") time_present, t1.project_id, r.roundcode '
 * 			. ' FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match AS m '
 * 			. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS t1 ON t1.id = m.projectteam1_id '
 * 		  . ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_round AS r ON r.id = m.round_id '
 * 			. ' WHERE m.id = '. $db->Quote($this->matchid);
 */
            
            
			$db->setQuery($query, 0, 1);
            
        if ( !$db->loadObject() )
	    {
		$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.'<pre>'.print_r($db->getErrorMsg(),true).'</pre>' ),'Error');
	    }
        
			$this->_match = $db->loadObject();
		}
		return $this->_match;
	}
	
	function getShowPics( )
	{
		return $this->showpics;
	}

	/**
	 * get match teams details
	 * @return array
	 */
	function getMatchTeams()
	{
		if (empty($this->teams))
		{
			$this->teams = array();

			$match = $this->getMatch();
			if ( is_null ( $match ) )
			{
				return null;
			}

			$team1 = sportsmanagementModelProject::getTeaminfo($match->projectteam1_id);
			$team2 = sportsmanagementModelProject::getTeaminfo($match->projectteam2_id);
			$this->teams[] = $team1;
			$this->teams[] = $team2;
			// Set the division id, so that the home and away ranks are 
			// determined for a division, if the team is part of a division
			$this->divisionid = $team1->division_id;
		}
		return $this->teams;
	}

	function getMatchCommentary()
    {
        $mainframe = JFactory::getApplication();
       $option = JRequest::getCmd('option');
       // Create a new query object.		
	   $db = JFactory::getDBO();
	   $query = $db->getQuery(true);
       
       // Select some fields
		$query->select('*');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_commentary ');
        $query->where('match_id = '.(int)$this->matchidid );
        $query->order('event_time DESC');
       
/**
 *         $query = "SELECT *  
 *     FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_commentary
 *     WHERE match_id = ".(int)$this->matchid." 
 *     ORDER BY event_time DESC";
 */
    
    
    $db->setQuery($query);
		return $db->loadObjectList();
    }
    
    function getReferees()
	{
	   $mainframe = JFactory::getApplication();
       $option = JRequest::getCmd('option');
       // Create a new query object.		
	   $db = JFactory::getDBO();
	   $query = $db->getQuery(true);
       
		$match = $this->getMatch();
		$query = ' SELECT p.firstname, p.nickname, p.lastname, p.country, pos.name AS position_name, p.id as person_id '
		. ' FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_referee AS mr '
		. ' LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_referee AS pref ON mr.project_referee_id=pref.id '
		. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS p ON p.id = pref.person_id '
		. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position ppos ON ppos.id = mr.project_position_id'
		. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS pos ON pos.id = ppos.position_id '
		. ' WHERE mr.match_id = '. $this->_db->Quote($match->id)
		. '  AND p.published = 1 '
		;
		$this->_db->setQuery($query);
		return $this->_db->loadObjectList();
	}

	function _getRanking()
	{
		if (empty($this->ranking))
		{
			$project = sportsmanagementModelProject::getProject();
			$division = $this->divisionid;
			$ranking = JSMRanking::getInstance($project);
			$ranking->setProjectId( $project->id );
			$this->ranking = $ranking->getRanking(0, sportsmanagementModelProject::getCurrentRound(), $division);
		}
		return $this->ranking;
	}

	function getHomeRanked()
	{
		$match = $this->getMatch();
		$rankings = $this->_getRanking();
		foreach ($rankings as $ptid => $team)
		{
			if ($ptid == $match->projectteam1_id) {
				return $team;
			}
		}
		return false;
	}

	function getAwayRanked()
	{
		$match = $this->getMatch();
		$rankings = $this->_getRanking();

		foreach ($rankings as $ptid => $team)
		{
			if ($ptid == $match->projectteam2_id) {
				return $team;
			}
		}
		return false;
	}

	function _getHighestMatches($teamid,$whichteam,$gameart)
	{
	   $mainframe = JFactory::getApplication();
       $option = JRequest::getCmd('option');
       // Create a new query object.		
	   $db = JFactory::getDBO();
	   $query = $db->getQuery(true);
       
		//$match = self::getMatch();
        
        // Select some fields
		$query->select('m.id AS mid,m.team1_result AS homegoals,m.team2_result AS awaygoals');
        $query->select('t1.name AS hometeam');
        $query->select('t2.name AS awayteam');
        $query->select('pt1.project_id AS pid');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match as m  ');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team pt1 ON pt1.id = m.projectteam1_id  ');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st1 ON st1.id = pt1.team_id ');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t1 ON t1.id = st1.team_id ');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team pt2 ON pt2.id = m.projectteam2_id  ');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st2 ON st2.id = pt2.team_id ');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t2 ON t2.id = st2.team_id ');
   
        $query->where('pt1.project_id = '.$db->Quote($this->projectid) );
        
        $query->where('m.published = 1');
        $query->where('m.alt_decision = 0');
        
        switch ($whichteam)
        {
            case 'HOME':
            $query->where('t1.id = '. $db->Quote($teamid) );
            switch ($gameart)
            {
            case 'WIN':
            $query->where('(team1_result - team2_result > 0)' );
            $query->order('(team1_result - team2_result) DESC ' );
            break;
            case 'LOST':
            $query->where('(team1_result - team2_result < 0)' );
            $query->order('(team1_result - team2_result) ASC ' );
            break;
            }
            break;
            case 'AWAY':
            $query->where('t2.id = '. $db->Quote($teamid) );
            switch ($gameart)
            {
            case 'WIN':
            $query->where('(team2_result - team1_result > 0)' );
            $query->order('(team2_result - team1_result) DESC ' );
            break;
            case 'LOST':
            $query->where('(team2_result - team1_result < 0)' );
            $query->order('(team2_result - team1_result) ASC ' );
            break;
            }
            break;
        }
        
        
  
		$db->setQuery($query, 0, 1);
		return $db->loadObject();
	}
    
    
    
    
/**
 *     function _getHighestHomeWin($teamid)
 * 	{
 * 	   $mainframe = JFactory::getApplication();
 *        $option = JRequest::getCmd('option');
 *        // Create a new query object.		
 * 	   $db = JFactory::getDBO();
 * 	   $query = $db->getQuery(true);
 *        
 * 		$match = $this->getMatch();

 * 		$query = ' SELECT t1.name AS hometeam, '
 * 		. ' t2.name AS awayteam, '
 * 		. ' team1_result AS homegoals, '
 * 		. ' team2_result AS awaygoals, '
 * 		. ' pt1.project_id AS pid, '
 * 		. ' m.id AS mid '
 * 		. ' FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match as m '
 * 		. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team pt1 ON pt1.id = m.projectteam1_id '
 * 		. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team t1 ON t1.id = pt1.team_id '
 * 		. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team pt2 ON pt2.id = m.projectteam2_id '
 * 		. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team t2 ON t2.id = pt2.team_id '
 * 		. ' WHERE pt1.project_id = ' . $this->_db->Quote($match->project_id)
 * 		. ' AND m.published = 1 '
 * 		. ' AND m.alt_decision = 0 '
 * 		. ' AND t1.id = '. $this->_db->Quote($teamid)
 * 		. ' AND (team1_result - team2_result > 0) '
 * 		. ' ORDER BY (team1_result - team2_result) DESC '
 * 		;
 * 		$this->_db->setQuery($query, 0, 1);
 * 		return $this->_db->loadObject();
 * 	}
 */

	function getHomeHighestHomeWin( )
	{
		$teams = $this->getMatchTeams();
		if ( is_null ( $teams ) )
		{
			return null;
		}
		//return $this->_getHighestHomeWin( $teams[0]->team_id );
        return self::_getHighestMatches( $teams[0]->team_id , 'HOME' , 'WIN');
	}

	function getAwayHighestHomeWin( )
	{
		$teams = $this->getMatchTeams();
		if ( is_null ( $teams ) )
		{
			return null;
		}
		//return $this->_getHighestHomeWin( $teams[1]->team_id );
        return self::_getHighestMatches( $teams[1]->team_id , 'AWAY' , 'WIN');
	}

/**
 * 	function _getHighestHomeDef( $teamid )
 * 	{
 * 	   $mainframe = JFactory::getApplication();
 *        $option = JRequest::getCmd('option');
 *        // Create a new query object.		
 * 	   $db = JFactory::getDBO();
 * 	   $query = $db->getQuery(true);
 *        
 * 		$match = $this->getMatch();

 * 		$query = ' SELECT t1.name AS hometeam, '
 * 		. ' t2.name AS awayteam, '
 * 		. ' team1_result AS homegoals, '
 * 		. ' team2_result AS awaygoals, '
 * 		. ' pt1.project_id AS pid, '
 * 		. ' m.id AS mid '
 * 		. ' FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match as m '
 * 		. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team pt1 ON pt1.id = m.projectteam1_id '
 * 		. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team t1 ON t1.id = pt1.team_id '
 * 		. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team pt2 ON pt2.id = m.projectteam2_id '
 * 		. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team t2 ON t2.id = pt2.team_id '
 * 		. ' WHERE pt1.project_id = ' . $this->_db->Quote($match->project_id)
 * 		. ' AND m.published = 1 '
 * 		. ' AND m.alt_decision = 0 '
 * 		. ' AND t1.id = '. $this->_db->Quote($teamid)
 * 		. ' AND (team1_result - team2_result < 0) '
 * 		. ' ORDER BY (team1_result - team2_result) ASC '
 * 		;
 * 		$this->_db->setQuery($query, 0, 1);
 * 		return $this->_db->loadObject();
 * 	}
 */

	function getHomeHighestHomeDef()
	{
		$teams = $this->getMatchTeams();
		if ( is_null ( $teams ) )
		{
			return null;
		}
		//return $this->_getHighestHomeDef( $teams[0]->team_id );
        return self::_getHighestMatches( $teams[0]->team_id , 'HOME' , 'LOST');
	}

	function getAwayHighestHomeDef()
	{
		$teams = $this->getMatchTeams();
		if ( is_null ( $teams ) )
		{
			return null;
		}
		//return $this->_getHighestHomeDef( $teams[1]->team_id );
        return self::_getHighestMatches( $teams[1]->team_id , 'AWAY' , 'LOST');
	}

/**
 * 	function _getHighestAwayWin( $teamid )
 * 	{
 * 	   $mainframe = JFactory::getApplication();
 *        $option = JRequest::getCmd('option');
 *        // Create a new query object.		
 * 	   $db = JFactory::getDBO();
 * 	   $query = $db->getQuery(true);
 *        
 * 		$match = $this->getMatch();

 * 		$query = ' SELECT t1.name AS hometeam, '
 * 		. ' t2.name AS awayteam, '
 * 		. ' team1_result AS homegoals, '
 * 		. ' team2_result AS awaygoals, '
 * 		. ' pt1.project_id AS pid, '
 * 		. ' m.id AS mid '
 * 		. ' FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match as m '
 * 		. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team pt1 ON pt1.id = m.projectteam1_id '
 * 		. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team t1 ON t1.id = pt1.team_id '
 * 		. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team pt2 ON pt2.id = m.projectteam2_id '
 * 		. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team t2 ON t2.id = pt2.team_id '
 * 		. ' WHERE pt1.project_id = ' . $this->_db->Quote($match->project_id)
 * 		. ' AND m.published = 1 '
 * 		. ' AND m.alt_decision = 0 '
 * 		. ' AND t2.id = '. $this->_db->Quote($teamid)
 * 		. ' AND (team2_result - team1_result > 0) '
 * 		. ' ORDER BY (team2_result - team1_result) DESC '
 * 		;
 * 		$this->_db->setQuery($query, 0, 1);
 * 		return $this->_db->loadObject();
 * 	}
 */

	function getHomeHighestAwayWin( )
	{
		$teams = $this->getMatchTeams();
		if ( is_null ( $teams ) )
		{
			return null;
		}
		//return $this->_getHighestAwayWin( $teams[0]->team_id );
        return self::_getHighestMatches( $teams[0]->team_id , 'AWAY' , 'WIN');
	}

	function getAwayHighestAwayWin( )
	{
		$teams = $this->getMatchTeams();
		if ( is_null ( $teams ) )
		{
			return null;
		}
		//return $this->_getHighestAwayWin( $teams[1]->team_id );
        return self::_getHighestMatches( $teams[1]->team_id , 'HOME' , 'WIN');
	}

/**
 * 	function _getHighestAwayDef( $teamid )
 * 	{
 * 	   $mainframe = JFactory::getApplication();
 *        $option = JRequest::getCmd('option');
 *        // Create a new query object.		
 * 	   $db = JFactory::getDBO();
 * 	   $query = $db->getQuery(true);
 *        
 * 		$match = $this->getMatch();

 * 		$query = ' SELECT t1.name AS hometeam, '
 * 		. ' t2.name AS awayteam, '
 * 		. ' team1_result AS homegoals, '
 * 		. ' team2_result AS awaygoals, '
 * 		. ' pt1.project_id AS pid, '
 * 		. ' m.id AS mid '
 * 		. ' FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match as m '
 * 		. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team pt1 ON pt1.id = m.projectteam1_id '
 * 		. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team t1 ON t1.id = pt1.team_id '
 * 		. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team pt2 ON pt2.id = m.projectteam2_id '
 * 		. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team t2 ON t2.id = pt2.team_id '
 * 		. ' WHERE pt1.project_id = ' . $this->_db->Quote($match->project_id)
 * 		. ' AND m.published = 1 '
 * 		. ' AND m.alt_decision = 0 '
 * 		. ' AND t2.id = '. $this->_db->Quote($teamid)
 * 		. ' AND (team1_result - team2_result > 0) '
 * 		. ' ORDER BY (team2_result - team1_result) ASC '
 * 		;
 * 		$this->_db->setQuery($query, 0, 1);
 * 		return $this->_db->loadObject();
 * 	}
 */


	function getHomeHighestAwayDef()
	{
		$teams = $this->getMatchTeams();
		if ( is_null ( $teams ) )
		{
			return null;
		}
		//return $this->_getHighestAwayDef( $teams[0]->team_id );
        return self::_getHighestMatches( $teams[0]->team_id , 'AWAY' , 'LOST');
	}

	function getAwayHighestAwayDef()
	{
		$teams = $this->getMatchTeams();
		if ( is_null ( $teams ) )
		{
			return null;
		}
		//return $this->_getHighestAwayDef( $teams[1]->team_id );
        return self::_getHighestMatches( $teams[1]->team_id , 'HOME' , 'LOST');
	}

	/**
	 * get all games in all projects for these 2 teams
	 * @return array
	 */
	function getGames( )
	{
	   $mainframe = JFactory::getApplication();
       $option = JRequest::getCmd('option');
       // Create a new query object.		
	   $db = JFactory::getDBO();
	   $query = $db->getQuery(true);
       
		$result = array();
		$teams = self::getMatchTeams();
		if ( is_null ( $teams ) )
		{
			return null;
		}
        
        // Select some fields
		$query->select('m.*, DATE_FORMAT(m.time_present, "%H:%i") time_present' );
        $query->select('pt1.project_id');
        $query->select('p.name AS project_name,p.id AS prid');
        $query->select('r.id AS roundid,r.roundcode AS roundcode,r.name AS mname');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match AS m ');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team pt1 ON pt1.id = m.projectteam1_id ');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team pt2 ON pt2.id = m.projectteam2_id ');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS p ON p.id = pt1.project_id ');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_round r ON m.round_id = r.id  ');
        $query->where('(pt1.team_id = '. $teams[0]->team_id .' AND pt2.team_id = '.$teams[1]->team_id .') OR (pt1.team_id = '.$teams[1]->team_id .' AND pt2.team_id = '.$teams[0]->team_id .')');
        $query->where('p.published = 1');
        $query->where('m.published = 1');
        $query->where('m.team1_result IS NOT NULL AND m.team2_result IS NOT NULL');
        $query->group('m.id');
        $query->order('p.ordering, m.match_date ASC');
        

/**
 * 		$query = ' SELECT m.*, DATE_FORMAT(m.time_present, "%H:%i") time_present, pt1.project_id, '
 * 		. ' p.name AS project_name, '
 * 		. ' r.id AS roundid, '
 * 		. ' r.roundcode AS roundcode, '
 * 		. ' r.name AS mname, '
 * 		. ' p.id AS prid '
 * 		. ' FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match as m '
 * 		. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team pt1 ON pt1.id = m.projectteam1_id '
 * 		. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team pt2 ON pt2.id = m.projectteam2_id '
 * 		. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS p ON p.id = pt1.project_id '
 * 		. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_round r ON m.round_id=r.id '
 * 		. ' WHERE ((pt1.team_id = '. $teams[0]->team_id .' AND pt2.team_id = '.$teams[1]->team_id .') '
 * 		. '        OR (pt1.team_id = '.$teams[1]->team_id .' AND pt2.team_id = '.$teams[0]->team_id .')) '
 * 		. ' AND p.published = 1 '
 * 		. ' AND m.published = 1 '
 * 		. ' AND m.team1_result IS NOT NULL AND m.team2_result IS NOT NULL';

 * 		$query .= " GROUP BY m.id ORDER BY p.ordering, m.match_date ASC";
 */
        
		$db->setQuery( $query );
		$result = $db->loadObjectList();

		return $result;
	}

	function getTeamsFromMatches( & $games )
	{
	   $mainframe = JFactory::getApplication();
       $option = JRequest::getCmd('option');
       // Create a new query object.		
	   $db = JFactory::getDBO();
	   $query = $db->getQuery(true);
       
		$teams = Array();

		if ( !count( $games ) )
		{
			return $teams;
		}

		foreach ( $games as $m )
		{
			$teamsId[] = $m->projectteam1_id;
			$teamsId[] = $m->projectteam2_id;
		}
		$listTeamId = implode( ",", array_unique( $teamsId ) );

		// Select some fields
		$query->select('t.id, t.name');
        $query->select('pt.id as ptid');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st ON st.id = pt.team_id ');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t ON t.id = st.team_id ');
        $query->where('pt.id IN ('.$listTeamId.')' );
        
/**
 *         $query = "SELECT t.id, t.name, pt.id as ptid
 *                  FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt
 *                  INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t ON t.id = pt.team_id
 *                  WHERE pt.id IN (".$listTeamId.")";
 */
                 
                 
		$db->setQuery( $query );
		$result = $db->loadObjectList();
        
        if ( !$result )
	    {
		$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.'<pre>'.print_r($db->getErrorMsg(),true).'</pre>' ),'Error');
	    }

		foreach ( $result as $r )
		{
			$teams[$r->ptid] = $r;
		}

		return $teams;
	}

	function getPlayground( $pgid )
	{
	   $mainframe = JFactory::getApplication();
       $option = JRequest::getCmd('option');
       // Create a new query object.		
	   $db = JFactory::getDBO();
	   $query = $db->getQuery(true);
       
       // Select some fields
		$query->select('*');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_playground ');
        $query->where('id = '. $db->Quote($pgid));
       
/**
 * 		$query = 'SELECT * FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_playground
 * 					WHERE id = '. $this->_db->Quote($pgid);
 */
                    
		$db->setQuery($query, 0, 1);
        
        if ( !$db->loadObject() )
	    {
		$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.'<pre>'.print_r($db->getErrorMsg(),true).'</pre>' ),'Error');
	    }
        
		return $db->loadObject();
	}

	function getMatchText($match_id)
	{
	   $mainframe = JFactory::getApplication();
       $option = JRequest::getCmd('option');
       // Create a new query object.		
	   $db = JFactory::getDBO();
	   $query = $db->getQuery(true);
       
       // Select some fields
		$query->select('m.*');
        $query->select('t1.name as t1name,t2.name as t2name');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match AS m ');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt1 ON m.projectteam1_id = pt1.id ');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st1 ON st1.id = pt1.team_id ');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t1 ON t1.id = st1.team_id ');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt2 ON m.projectteam2_id = pt2.id ');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st2 ON st2.id = pt2.team_id ');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t2 ON t2.id = st2.team_id ');
        $query->where('m.id = ' . $match_id );
        $query->where('m.published = 1');
        $query->order('m.match_date, t1.short_name');
        
/**
 * 		$query = "SELECT m.*, t1.name t1name,  t2.name t2name
 * 					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match AS m
 * 					INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt1 ON m.projectteam1_id = pt1.id
 * 					INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt2 ON m.projectteam2_id = pt2.id
 * 					INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t1 ON pt1.team_id=t1.id
 * 					INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t2 ON pt2.team_id=t2.id
 * 					WHERE m.id = " . $match_id . "
 * 					AND m.published = 1
 * 					ORDER BY m.match_date, t1.short_name"
 * 					;
 */
                    
                    
		$db->setQuery($query);
        
        if ( !$db->loadObject() )
	    {
		$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.'<pre>'.print_r($db->getErrorMsg(),true).'</pre>' ),'Error');
	    }
        
		return $db->loadObject();
	}
	
	/**
	 * Calculates chances between 2 team
	 * Code is from LMO, all credits go to the LMO developers
	 * @return array
	 */
	function getChances()
	{
		$home=$this->getHomeRanked();
		$away=$this->getAwayRanked();

		if ((($home->cnt_matches)>0) && (($away->cnt_matches)>0))
		{
			$won1=$home->cnt_won;		
			$won2=$away->cnt_won;
			$loss1=$home->cnt_lost;
			$loss2=$away->cnt_lost;
			$matches1=$home->cnt_matches;
			$matches2=$away->cnt_matches;
			$goalsfor1=$home->sum_team1_result;
			$goalsfor2=$away->sum_team1_result;
			$goalsagainst1=$home->sum_team2_result;
			$goalsagainst2=$away->sum_team2_result;
		
			$ax=(100*$won1/$matches1)+(100*$loss2/$matches2);
			$bx=(100*$won2/$matches2)+(100*$loss1/$matches1);
			$cx=($goalsfor1/$matches1)+($goalsagainst2/$matches2);
			$dx=($goalsfor2/$matches2)+($goalsagainst1/$matches1);
			$ex=$ax+$bx;
			$fx=$cx+$dx;
		
			if (isset($ex) && ($ex>0) && isset($fx) &&($fx>0)) 
			{	 
				$ax=round(10000*$ax/$ex);
				$bx=round(10000*$bx/$ex);
				$cx=round(10000*$cx/$fx);
				$dx=round(10000*$dx/$fx);
		
				$chg1=number_format((($ax+$cx)/200),2,",",".");
				$chg2=number_format((($bx+$dx)/200),2,",",".");
				$result=array($chg1,$chg2);

				return $result;
			}
		}	
	}
		
	/**
	* get Previous X games of each team
	*
	* @return array
	*/
	function getPreviousX()
	{
		if (!$this->_match) {
			return false;
		}
		$games = array();
		$games[$this->_match->projectteam1_id] = self::_getTeamPreviousX($this->_match->roundcode, $this->_match->projectteam1_id);
		$games[$this->_match->projectteam2_id] = self::_getTeamPreviousX($this->_match->roundcode, $this->_match->projectteam2_id);
		
		return $games;
	}
	
	/**
	* returns last X games
	*
	* @param int $current_roundcode
	* @param int $ptid project team id
	* @return array
	*/
	function _getTeamPreviousX($current_roundcode, $ptid)
	{
	   $mainframe = JFactory::getApplication();
       $option = JRequest::getCmd('option');
       // Create a new query object.		
	   $db = JFactory::getDBO();
	   $query = $db->getQuery(true);
       
		$config = sportsmanagementModelProject::getTemplateConfig('nextmatch');
		$nblast = $config['nb_previous'];
        
        // Select some fields
		$query->select('m.*');
        $query->select('r.project_id, r.id AS roundid, r.roundcode ');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match AS m ');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_round AS r ON r.id = m.round_id ');
        $query->where('r.roundcode < '.$current_roundcode);
        $query->where('(m.projectteam1_id = '.$ptid.' OR m.projectteam2_id = '.$ptid.')');
        $query->where('m.published = 1');
        $query->order('r.roundcode DESC');
        $query->setLimit('0,'.$nblast);

/*        
		$query = ' SELECT m.*, r.project_id, r.id AS roundid, r.roundcode '
		       . ' FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match AS m '
		       . ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_round AS r ON r.id = m.round_id '
		       . ' WHERE (m.projectteam1_id = ' . $ptid
		       . '       OR m.projectteam2_id = ' . $ptid.')'
		       . '   AND r.roundcode < '.$current_roundcode
		       . '   AND m.published = 1 '
		       . ' ORDER BY r.roundcode DESC '
		       .  ' LIMIT 0, '.$nblast
		       ;
*/
               
		$db->setQuery($query);
		$res = $db->loadObjectList();
        
        if ( !$res )
	    {
		$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.'<pre>'.print_r($db->getErrorMsg(),true).'</pre>' ),'Error');
	    }
        
		if ($res) {
			$res = array_reverse($res);
		}
		return $res;
	}
}
?>