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

jimport( 'joomla.application.component.model' );

//require_once( JPATH_COMPONENT.DS . 'helpers' . DS . 'ranking.php' );
//require_once( JLG_PATH_SITE . DS . 'models' . DS . 'project.php' );

/**
 * sportsmanagementModelTeamInfo
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelTeamInfo extends JModel
{
	var $project = null;
	var $projectid = 0;
	var $projectteamid = 0;
	var $teamid = 0;
	var $team = null;
	var $club = null;

	function __construct( )
	{
		$this->projectid = JRequest::getInt( "p", 0 );
		$this->projectteamid = JRequest::getInt( "ptid", 0 );
		$this->teamid = JRequest::getInt( "tid", 0 );
		parent::__construct( );
	}

	/**
	* Method to return a team trainingdata array
	* @param int projectid
	* @return	array
	*/
	function getTrainigData( $projectid )
	{
		$trainingData = array();
		if($this->projectteamid <= 0) {
			$projectTeamID  = sportsmanagementModelProject::getprojectteamID($this->teamid);
		}
		$query = "SELECT * FROM #__".COM_SPORTSMANAGEMENT_TABLE."_team_trainingdata WHERE project_id=$projectid "
				."AND project_team_id=$projectTeamID ORDER BY dayofweek ASC";
		$this->_db->setQuery($query);
		$trainingData = $this->_db->loadObjectList();
		return $trainingData;
	}
    
    /**
	 * get team info
	 * @return object
	 */
	function getTeamByProject()
	{
		if (is_null($this->team))
		{
			$query = ' SELECT t.*,t.name AS tname, t.website AS team_website, pt.*, pt.notes AS notes, pt.info AS info, 
						t.extended AS teamextended, t.picture AS team_picture, pt.picture AS projectteam_picture, c.*,
						CASE WHEN CHAR_LENGTH( t.alias ) THEN CONCAT_WS( \':\', t.id, t.alias ) ELSE t.id END AS slug 
						FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_team t 
						LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_club c ON t.club_id=c.id
						INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team pt ON pt.team_id = t.id 
						WHERE pt.project_id = '. $this->_db->Quote($this->projectid); 
			if($this->projectteamid > 0) {
				$query .= ' AND pt.id = '. $this->_db->Quote($this->projectteamid);
			} else {
				$query .= ' AND t.id = '. $this->_db->Quote($this->teamid);
			}		
			
			$this->_db->setQuery($query);
			$this->team  = $this->_db->loadObject();
		}
		return $this->team;
	}

	/**
	 * get club info
	 * @return object
	 */
	function getClub()
	{
		if ( is_null( $this->club ) )
		{
			$team = $this->getTeamByProject();
			if ( $team->club_id > 0 )
			{
				$query = ' SELECT *, '
				       . ' CASE WHEN CHAR_LENGTH( alias ) THEN CONCAT_WS( \':\', id, alias ) ELSE id END AS slug '
				       . ' FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_club WHERE id = '. $this->_db->Quote($team->club_id);
				$this->_db->setQuery($query);
				$this->club  = $this->_db->loadObject();
			}
		}
		return $this->club;
	}

	/**
	 * get history of team in differents projects
	 * @param object config
	 * @return array
	 */
	function getSeasons( $config )
	{
	    $seasons = array();
	    if ( $config['ordering_teams_seasons'] == "1")
	    {
	    	$season_ordering = "DESC";
	    }
	    else
	    {
	    	$season_ordering = "ASC";
	    }

	    // now get all Leagues and Seasons which joined the team so far
	    $query = ' SELECT pt.id as ptid, pt.team_id, pt.picture, pt.info, pt.project_id AS projectid, '
				. ' p.name as projectname, pt.division_id, '
				. ' s.name as season, '
				. ' l.name as league, t.extended as teamextended, '
				. ' CASE WHEN CHAR_LENGTH( p.alias ) THEN CONCAT_WS( \':\', p.id, p.alias ) ELSE p.id END AS project_slug, '
				. ' CASE WHEN CHAR_LENGTH( t.alias ) THEN CONCAT_WS( \':\', t.id, t.alias ) ELSE t.id END AS team_slug, '
				. ' CASE WHEN CHAR_LENGTH( d.alias ) THEN CONCAT_WS( \':\', d.id, d.alias ) ELSE d.id END AS division_slug, '
				. ' d.name AS division_name, '
				. ' d.shortname AS division_short_name '
				. ' FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt '
				. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t ON t.id = pt.team_id '
				. ' LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_division AS d ON d.id = pt.division_id '
				. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS p ON p.id = pt.project_id '
				. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_season AS s ON s.id = p.season_id '
				. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_league AS l ON l.id = p.league_id ';
				if($this->projectteamid > 0) {
					$query .= ' where pt.id = '. $this->_db->Quote($this->projectteamid);
				} else {
					$query .= ' where t.id = '. $this->_db->Quote($this->teamid);
				}
				$query .=  ' AND p.published = 1 '
				. ' ORDER BY s.ordering '.$season_ordering;

	    $this->_db->setQuery( $query );
	    $seasons = $this->_db->loadObjectList();

	    foreach ($seasons as $k => $season)
	    {
	    	$ranking = $this->getTeamRanking($season->projectid, $season->division_id);
			if(!empty($ranking)) {
		    	$seasons[$k]->rank       = $ranking['rank'];
		    	$seasons[$k]->leaguename = $this->getLeague($season->projectid);
		    	$seasons[$k]->games      = $ranking['games'];
		    	$seasons[$k]->points     = $ranking['points'];
		    	$seasons[$k]->series     = $ranking['series'];
		    	$seasons[$k]->goals      = $ranking['goals'];
		    	$seasons[$k]->playercnt  = $this->getPlayerCount($season->projectid, $season->ptid);
          $seasons[$k]->playermeanage  = $this->getPlayerMeanAge($season->projectid, $season->ptid);
          $seasons[$k]->market_value  = $this->getPlayerMarketValue($season->projectid, $season->ptid);
	    	} else {
	    		$seasons[$k]->rank       = 0;
	    		$seasons[$k]->leaguename = '';
	    		$seasons[$k]->games      = 0;
	    		$seasons[$k]->points     = 0;
	    		$seasons[$k]->series     = 0;
	    		$seasons[$k]->goals      = 0;
	    		$seasons[$k]->playercnt  = 0;
	    	}
		}
    	return $seasons;
	}

	
    
    function getPlayerMarketValue($projectid, $projectteamid)
    {
    $player = array();
		$query = " SELECT SUM(tp.market_value) AS market_value "
		       . " FROM #__".COM_SPORTSMANAGEMENT_TABLE."_person AS ps "
		       . " INNER JOIN #__".COM_SPORTSMANAGEMENT_TABLE."_team_player AS tp ON tp.person_id = ps.id "
		       . " INNER JOIN #__".COM_SPORTSMANAGEMENT_TABLE."_project_team AS pt ON tp.projectteam_id = pt.id "
		       . " WHERE pt.project_id=" . $projectid
		       . " AND pt.id=" . $projectteamid
		       . " AND tp.published = 1 " 
		       . " AND ps.published = 1 ";
		       $this->_db->setQuery($query);
		$player = $this->_db->loadResult();
		return $player;    
        
    }
    
    /**
	 * get ranking of current team in a project
	 * @param int projectid
	 * @param int division_id
	 * @return array
	 */
	function getTeamRanking($projectid, $division_id)
	{
		$rank = array();
		//$model = &JLGModel::getInstance('Project', 'JoomleagueModel');
		sportsmanagementModelProject::setProjectID($projectid);
		$project = sportsmanagementModelProject::getProject();
		$tableconfig = sportsmanagementModelProject::getTemplateConfig( "ranking" );
		$ranking = JSMRanking::getInstance($project);
		$ranking->setProjectId( $project->id );
		$this->ranking = $ranking->getRanking(
								0,
								sportsmanagementModelProject::getCurrentRound(),
								$division_id);
		foreach ($this->ranking as $ptid => $value)
		{
			if ($value->getPtid() == $this->projectteamid)
			{
				$rank['rank']   = $value->rank;
				$rank['games']  = $value->cnt_matches;
				$rank['points'] = $value->getPoints();
				$rank['series'] = $value->cnt_won . "/" . $value->cnt_draw . "/" . $value->cnt_lost;
				$rank['goals']  = $value->sum_team1_result . ":" . $value->sum_team2_result;
				break;
			} 
			else if ($value->getTeamId() == $this->teamid)
			{
				$rank['rank']   = $value->rank;
				$rank['games']  = $value->cnt_matches;
				$rank['points'] = $value->getPoints();
				$rank['series'] = $value->cnt_won . "/" . $value->cnt_draw . "/" . $value->cnt_lost;
				$rank['goals']  = $value->sum_team1_result . ":" . $value->sum_team2_result;
				break;
			}
				
		}
		return $rank;
	}


  function getMergeClubs( $merge_clubs )
  {
  
  $query = ' SELECT *, '
				       . ' CASE WHEN CHAR_LENGTH( alias ) THEN CONCAT_WS( \':\', id, alias ) ELSE id END AS slug '
				       . ' FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_club WHERE id IN ('. $merge_clubs .')';
				$this->_db->setQuery($query);
				$result  = $this->_db->loadObjectList();
			
		return $result;
  }
  
	/**
	 * gets name of league associated to project
	 * @param int $projectid
	 * @return string
	 */
	function getLeague($projectid)
	{
		$query = 'SELECT l.name AS league FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS p, #__'.COM_SPORTSMANAGEMENT_TABLE.'_league AS l WHERE p.id=' . $projectid . ' AND l.id=p.league_id ';
	    $this->_db->setQuery($query, 0, 1);
    	$league = $this->_db->loadResult();
		return $league;
	}
    
    function getLeagueRankOverviewDetail( $seasonsranking )
	{
  $leaguesoverviewdetail = array();
  
  foreach ($seasonsranking as $season)
	{
	$temp = new stdClass();
  $temp->match = 0;
  $temp->won = 0;
  $temp->draw = 0;
  $temp->loss = 0;
  $temp->goalsfor = 0;
  $temp->goalsagain = 0;
	$leaguesoverviewdetail[$season->league] = $temp;
	}
 
  foreach ($seasonsranking as $season)
	{
	$leaguesoverviewdetail[$season->league]->match += $season->games;
	$teile = explode("/",$season->series);
	$leaguesoverviewdetail[$season->league]->won += $teile[0];
	$leaguesoverviewdetail[$season->league]->draw += $teile[1];
	$leaguesoverviewdetail[$season->league]->loss += $teile[2];
	$teile = explode(":",$season->goals);
	$leaguesoverviewdetail[$season->league]->goalsfor += $teile[0];
	$leaguesoverviewdetail[$season->league]->goalsagain += $teile[1];
	
	}
 
  return $leaguesoverviewdetail;
  
  }
  
  function getLeagueRankOverview( $seasonsranking )
	{
  $leaguesoverview = array();
  
  foreach ($seasonsranking as $season)
	{
	$leaguesoverview[$season->league][(int) $season->rank] += 1;
	}
  
  ksort($leaguesoverview);
  
  foreach ( $leaguesoverview as $key => $value)
  {
  ksort($leaguesoverview[$key]);
  
  }
 
  return $leaguesoverview;
  
  }
  
  /**
	 * Get total number of players assigned to a team
	 * @param int projectid
	 * @param int projectteamid
	 * @return int
	 */
	function getPlayerMeanAge($projectid, $projectteamid)
	{
		//$player = array();
    $meanage = 0;
    $countplayer = 0;
    $age = 0;
		$query = " SELECT ps.*"
		       . " FROM #__".COM_SPORTSMANAGEMENT_TABLE."_person AS ps "
		       . " INNER JOIN #__".COM_SPORTSMANAGEMENT_TABLE."_team_player AS tp ON tp.person_id = ps.id "
		       . " INNER JOIN #__".COM_SPORTSMANAGEMENT_TABLE."_project_team AS pt ON tp.projectteam_id = pt.id "
		       . " WHERE pt.project_id=" . $projectid
		       . " AND pt.id=" . $projectteamid
		       . " AND tp.published = 1 " 
		       . " AND ps.published = 1 ";
		       $this->_db->setQuery($query);
		$players = $this->_db->loadObjectList();
    foreach ( $players as $player )
    {
    if ( $player->birthday != '0000-00-00' )
    {
    $age += JoomleagueHelper::getAge( $player->birthday,$player->deathday );
    $countplayer++;
    }
    
    }
    
    // diddipoeler
    // damit kein fehler hochkommt: Warning: Division by zero
    if ( $age != 0 )
    {
    $meanage = round( $age / $countplayer , 2);
    }
    
		return $meanage;
	}
  
	/**
	 * Get total number of players assigned to a team
	 * @param int projectid
	 * @param int projectteamid
	 * @return int
	 */
	function getPlayerCount($projectid, $projectteamid)
	{
		$player = array();
		$query = " SELECT COUNT(*) AS playercnt "
		       . " FROM #__".COM_SPORTSMANAGEMENT_TABLE."_person AS ps "
		       . " INNER JOIN #__".COM_SPORTSMANAGEMENT_TABLE."_team_player AS tp ON tp.person_id = ps.id "
		       . " INNER JOIN #__".COM_SPORTSMANAGEMENT_TABLE."_project_team AS pt ON tp.projectteam_id = pt.id "
		       . " WHERE pt.project_id=" . $projectid
		       . " AND pt.id=" . $projectteamid
		       . " AND tp.published = 1 " 
		       . " AND ps.published = 1 ";
		       $this->_db->setQuery($query);
		$player = $this->_db->loadResult();
		return $player;
	}
	
	function hasEditPermission($task=null)
	{
		//check for ACL permsission and project admin/editor
		$allowed = parent::hasEditPermission($task);
		$user = JFactory::getUser();
		if($user->id > 0 && !$allowed)
		{
			// Check if user is the projectteam admin
			$team = $this->getTeamByProject();
			if ( $user->id == $team->admin )
			{
				$allowed = true;
			}
		}
		return $allowed;
	}
    

    
    
	
}
?>