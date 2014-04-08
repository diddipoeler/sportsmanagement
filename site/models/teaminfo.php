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

	/**
	 * sportsmanagementModelTeamInfo::__construct()
	 * 
	 * @return void
	 */
	function __construct( )
	{
		$this->projectid = JRequest::getInt( "p", 0 );
		$this->projectteamid = JRequest::getInt( "ptid", 0 );
		$this->teamid = JRequest::getInt( "tid", 0 );
        
        sportsmanagementModelProject::$projectid = $this->projectid; 
		parent::__construct( );
	}

	/**
	* Method to return a team trainingdata array
	* @param int projectid
	* @return	array
	*/
	function getTrainigData( $projectid )
	{
	   $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        // Create a new query object.		
	   $db = JFactory::getDBO();
	   $query = $db->getQuery(true);
       
		$trainingData = array();
		if($this->projectteamid <= 0) {
			$projectTeamID  = sportsmanagementModelProject::getprojectteamID($this->teamid);
		}
        $query->select('*');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_team_trainingdata'); 
        $query->where('project_id = '. $projectid);  
        $query->where('project_team_id = '. $projectTeamID);
        $query->order('dayofweek ASC');
		
        $db->setQuery($query);
		$trainingData = $db->loadObjectList();
		return $trainingData;
	}
    
    /**
	 * get team info
	 * @return object
	 */
	function getTeamByProject()
	{
	   $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        // Create a new query object.		
	   $db = JFactory::getDBO();
	   $query = $db->getQuery(true);
       $starttime = microtime(); 
       
		if (is_null($this->team))
		{
		  $query->select('t.*,t.name AS tname, t.website AS team_website, pt.*, pt.notes AS notes, pt.info AS info');
          $query->select('t.extended AS teamextended, t.picture AS team_picture, pt.picture AS projectteam_picture, c.*');
          $query->select('CONCAT_WS( \':\', t.id, t.alias ) AS slug ');
          $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_team t '); 
	      $query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_club c ON t.club_id = c.id '); 
          $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st ON st.team_id = t.id');
          $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team pt ON pt.team_id = st.id ');
                
            $query->where('pt.project_id = '. $db->Quote($this->projectid));            
                        
			if($this->projectteamid > 0) 
            {
                $query->where('pt.id = '. $db->Quote($this->projectteamid));
			} 
            else 
            {
                $query->where('t.id = '. $db->Quote($this->teamid));
			}		
			
			$db->setQuery($query);
            
            if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
            $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.'<br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
			$this->team  = $db->loadObject();
            
            if ( !$this->team && COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
            $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
        }
        elseif ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
            {
                $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.'<br><pre>'.print_r($query->dump(),true).'</pre>'),'');
            } 
            
//            $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
//            $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.'<br><pre>'.print_r($query->dump(),true).'</pre>'),'');
            
		}
		return $this->team;
	}

	/**
	 * get club info
	 * @return object
	 */
	function getClub()
	{
	    $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        // Create a new query object.		
	   $db = JFactory::getDBO();
	   $query = $db->getQuery(true);
       $starttime = microtime(); 
       
		if ( is_null( $this->club ) )
		{
			$team = self::getTeamByProject();
			if ( $team->club_id > 0 )
			{
			 $query->select('*');
             $query->select('CONCAT_WS( \':\', id, alias ) AS slug');
             $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_club'); 
             $query->where('id = '. $db->Quote($team->club_id));  

				$db->setQuery($query);
                
                if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
				$this->club  = $db->loadObject();
			}
		}
		return $this->club;
	}

	
	/**
	 * sportsmanagementModelTeamInfo::getSeasons()
	 * 
	 * @param mixed $config
	 * @param integer $history
	 * @return
	 */
	function getSeasons( $config, $history = 0 )
	{
	   $mainframe = JFactory::getApplication();
    $option = JRequest::getCmd('option');
        // Create a new query object.		
	   $db = JFactory::getDBO();
	   $query = $db->getQuery(true);
       $starttime = microtime(); 
       
       
       
	    $seasons = array();
	    if ( $config['ordering_teams_seasons'] == "1")
	    {
	    	$season_ordering = "DESC";
	    }
	    else
	    {
	    	$season_ordering = "ASC";
	    }
        
        $query->select('pt.id as ptid, pt.team_id, pt.picture, pt.info, pt.project_id AS projectid');
		$query->select('p.name as projectname,p.season_id,p.current_round, pt.division_id');
		$query->select('s.name as season');
		$query->select('l.name as league, t.extended as teamextended');
		$query->select('CONCAT_WS( \':\', p.id, p.alias ) AS project_slug');
		$query->select('CONCAT_WS( \':\', t.id, t.alias ) AS team_slug');
		$query->select('CONCAT_WS( \':\', d.id, d.alias ) AS division_slug');
		$query->select('d.name AS division_name');
		$query->select('d.shortname AS division_short_name');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st ON st.id = pt.team_id'); 
		$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t ON t.id = st.team_id ');
		$query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_division AS d ON d.id = pt.division_id ');
		$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS p ON p.id = pt.project_id ');
		$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season AS s ON s.id = p.season_id ');
		$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_league AS l ON l.id = p.league_id ');
                        
        
        if ( $history )
        {
        $query->where('t.id = '. $db->Quote($this->teamid));    
        }
        else
        {
        if($this->projectteamid > 0) 
        {
                    $query->where('pt.id = '. $db->Quote($this->projectteamid));
				} 
                else 
                {
                    $query->where('t.id = '. $db->Quote($this->teamid));
				}
         }       

$query->order('s.ordering '.$season_ordering);

	    $db->setQuery( $query );
        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
	    $seasons = $db->loadObjectList();
        
        if ( !$seasons && COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
            $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
            $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Error');
        } 
        elseif ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
            $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'');
            $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' seasons<br><pre>'.print_r($seasons,true).'</pre>'),'');
            $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' history<br><pre>'.print_r($history,true).'</pre>'),'');
        }

	    foreach ($seasons as $k => $season)
	    {
	    	$ranking = self::getTeamRanking($season->projectid, $season->division_id);
			if(!empty($ranking)) 
            {
		    	$seasons[$k]->rank       = $ranking['rank'];
		    	$seasons[$k]->leaguename = self::getLeague($season->projectid);
		    	$seasons[$k]->games      = $ranking['games'];
		    	$seasons[$k]->points     = $ranking['points'];
		    	$seasons[$k]->series     = $ranking['series'];
		    	$seasons[$k]->goals      = $ranking['goals'];
		    	$seasons[$k]->playercnt  = self::getPlayerCount($season->projectid, $season->ptid, $season->season_id);
          $seasons[$k]->playermeanage  = self::getPlayerMeanAge($season->projectid, $season->ptid, $season->season_id);
          $seasons[$k]->market_value  = self::getPlayerMarketValue($season->projectid, $season->ptid, $season->season_id);
	    	} 
            else 
            {
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

	
    
    /**
     * sportsmanagementModelTeamInfo::getPlayerMarketValue()
     * 
     * @param mixed $projectid
     * @param mixed $projectteamid
     * @param mixed $season_id
     * @return
     */
    function getPlayerMarketValue($projectid, $projectteamid, $season_id)
    {
        $mainframe = JFactory::getApplication();
    $option = JRequest::getCmd('option');
        // Create a new query object.		
	   $db = JFactory::getDBO();
	   $query = $db->getQuery(true);
       $starttime = microtime(); 
       
    $player = array();
    $query->select('SUM(tp.market_value) AS market_value');
    $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_person_id AS stp'); 
    $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st ON st.team_id = stp.team_id');
    $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON pt.team_id = st.id');
    
    $query->where('pt.project_id = ' . $projectid);
    $query->where('pt.id = ' . $projectteamid);
    $query->where('tp.published = 1');
    $query->where('ps.published = 1');
		       
               $db->setQuery($query);
               
               if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
		$player = $db->loadResult();
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
	   $mainframe = JFactory::getApplication();
    $option = JRequest::getCmd('option');
        // Create a new query object.		
	   $db = JFactory::getDBO();
	   $query = $db->getQuery(true);
       
       if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
            $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' projectid<br><pre>'.print_r($projectid,true).'</pre>'),'');
        }
        

       
		$rank = array();
		//$model = &JLGModel::getInstance('Project', 'JoomleagueModel');
		sportsmanagementModelProject::setProjectID($projectid);
		$project = sportsmanagementModelProject::getProject();
		$tableconfig = sportsmanagementModelProject::getTemplateConfig( "ranking" );
		$ranking = JSMRanking::getInstance($project);
		$ranking->setProjectId( $project->id );
		$this->ranking = $ranking->getRanking(0,sportsmanagementModelProject::getCurrentRound(),$division_id);
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
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
            $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' rank<br><pre>'.print_r($rank,true).'</pre>'),'');
        }
        
        
		return $rank;
	}


  /**
   * sportsmanagementModelTeamInfo::getMergeClubs()
   * 
   * @param mixed $merge_clubs
   * @return
   */
  function getMergeClubs( $merge_clubs )
  {
    $mainframe = JFactory::getApplication();
    $option = JRequest::getCmd('option');
        // Create a new query object.		
	   $db = JFactory::getDBO();
	   $query = $db->getQuery(true);
       $query->select('*, CASE WHEN CHAR_LENGTH( alias ) THEN CONCAT_WS( \':\', id, alias ) ELSE id END AS slug');
	$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_club'); 
    $query->where('id IN ( '. $merge_clubs .' )');
				
                $db->setQuery($query);
				$result  = $db->loadObjectList();
			
            if ( !$result && COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
            $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
            $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Error');
        } 
        elseif ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
            $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'');
        }
        
		return $result;
  }
  
	/**
	 * gets name of league associated to project
	 * @param int $projectid
	 * @return string
	 */
	function getLeague($projectid)
	{
	   $mainframe = JFactory::getApplication();
    $option = JRequest::getCmd('option');
        // Create a new query object.		
	   $db = JFactory::getDBO();
	   $query = $db->getQuery(true);
       $query->select('l.name AS league');
	$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS p'); 
    $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_league AS l ON l.id = p.league_id');
    $query->where('p.id =' . $projectid);
    
//		$query = 'SELECT l.name AS league FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS p, #__'.COM_SPORTSMANAGEMENT_TABLE.'_league AS l WHERE p.id=' . $projectid . ' AND l.id=p.league_id ';

	    $db->setQuery($query, 0, 1);
    	$league = $db->loadResult();
        
        if ( !$league && COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
            $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
            $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Error');
        } 
        elseif ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
            $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'');
        }
        
        
        
		return $league;
	}
    
    /**
     * sportsmanagementModelTeamInfo::getLeagueRankOverviewDetail()
     * 
     * @param mixed $seasonsranking
     * @return
     */
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
  
  /**
   * sportsmanagementModelTeamInfo::getLeagueRankOverview()
   * 
   * @param mixed $seasonsranking
   * @return
   */
  function getLeagueRankOverview( $seasonsranking )
	{
	    $mainframe = JFactory::getApplication();
    $option = JRequest::getCmd('option');
    
    if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' seasonsranking<br><pre>'.print_r($seasonsranking,true).'</pre>'),'');    
    }
    
    
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
	function getPlayerMeanAge($projectid, $projectteamid, $season_id)
	{
	   $mainframe = JFactory::getApplication();
    $option = JRequest::getCmd('option');
        // Create a new query object.		
	   $db = JFactory::getDBO();
	   $query = $db->getQuery(true);
       
		//$player = array();
    $meanage = 0;
    $countplayer = 0;
    $age = 0;
    
    $query->select('ps.*');
    $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS ps'); 
    $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_person_id AS tp ON tp.person_id = ps.id');
    $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st ON st.team_id = tp.team_id AND st.season_id = tp.season_id');
    $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON st.id = pt.team_id');
        
    $query->where('pt.project_id =' . $projectid);
    $query->where('pt.id =' . $projectteamid);
    $query->where('tp.published = 1');
    $query->where('ps.published = 1');
               
               
		       $db->setQuery($query);
		$players = $db->loadObjectList();
        
        if ( !$players && COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
            $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' getErrorMsg<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
            $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' dump<br><pre>'.print_r($query->dump(),true).'</pre>'),'Error');
        } 
        elseif ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' dump<br><pre>'.print_r($query->dump(),true).'</pre>'),'');    
    }
    
    foreach ( $players as $player )
    {
    if ( $player->birthday != '0000-00-00' )
    {
    $age += sportsmanagementHelper::getAge( $player->birthday,$player->deathday );
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
	function getPlayerCount($projectid, $projectteamid, $season_id)
	{
	   $mainframe = JFactory::getApplication();
    $option = JRequest::getCmd('option');
        // Create a new query object.		
	   $db = JFactory::getDBO();
	   $query = $db->getQuery(true);
       
		$player = array();
        $query->select('COUNT(*) AS playercnt');
	    $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS ps'); 
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_person_id AS tp ON tp.person_id = ps.id');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st ON st.team_id = tp.team_id AND st.season_id = tp.season_id');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON st.id = pt.team_id');
        
        $query->where('pt.project_id =' . $projectid);
        $query->where('pt.id =' . $projectteamid);
        $query->where('tp.season_id =' . $season_id);
        //$query->where('tp.published = 1');
        $query->where('ps.published = 1');
    
	
        $db->setQuery($query);
		$player = $db->loadResult();
        
        if ( !$player && COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
            $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
            $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Error');
        } 
        elseif ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
            $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'');
        }
        
        
		return $player;
	}
	
	/**
	 * sportsmanagementModelTeamInfo::hasEditPermission()
	 * 
	 * @param mixed $task
	 * @return
	 */
	function hasEditPermission($task=null)
	{
		//check for ACL permsission and project admin/editor
		$allowed = parent::hasEditPermission($task);
		$user = JFactory::getUser();
		if($user->id > 0 && !$allowed)
		{
			// Check if user is the projectteam admin
			$team = self::getTeamByProject();
			if ( $user->id == $team->admin )
			{
				$allowed = true;
			}
		}
		return $allowed;
	}
    

    
    
	
}
?>