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

// no direct access
defined('_JEXEC') or die('Restricted access');

// Component Helper
jimport('joomla.application.component.helper');


/**
 * sportsmanagementHelperRoute
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementHelperRoute
{
	
  /**
   * sportsmanagementHelperRoute::sportsmanagementBuildRoute()
   * 
   * @param mixed $query
   * @return
   */
  public static function sportsmanagementBuildRoute(&$query)
{
	$app = JFactory::getApplication();
    $segments = array();

	if (isset($query['view'])) {
		$segments[] = $query['view'];
		unset($query['view']);
	}
	if (isset($query['p'])) {
		$segments[] = $query['p'];
		unset($query['p']);
	}
    if (isset($query['cid'])) {
		$segments[] = $query['cid'];
		unset($query['cid']);
	}
    
    //$link = JRoute::_( "index.php?" . $segments, false );
    //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' link<br><pre>'.print_r($link,true).'</pre>'),'');
    //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query<br><pre>'.print_r($query,true).'</pre>'),'');
    //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' segments<br><pre>'.print_r($segments,true).'</pre>'),'');

	return $segments;
}
  
  
  /**
   * sportsmanagementHelperRoute::getAllProjectsRoute()
   * 
   * @param mixed $country
   * @param mixed $league_id
   * @return
   */
  public static function getAllProjectsRoute( $country, $league_id )
	{
		$params = array(	"option" => "com_sportsmanagement",
				"view" => "allprojects",
				"filter_search_nation" => $country,
				"filter_search_leagues" => $league_id );
	
		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );
	
		return $link;
	}
    
  /**
   * sportsmanagementHelperRoute::getKunenaRoute()
   * 
   * @param mixed $sb_catid
   * @return
   */
  public static function getKunenaRoute( $sb_catid )
	{
		$params = array(	"option" => "com_kunena",
				"view" => "topic",
				"catid" => $sb_catid );
	
		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );
	
		return $link;
	}
  
  /**
   * sportsmanagementHelperRoute::getTeamInfoRoute()
   * 
   * @param mixed $projectid
   * @param mixed $teamid
   * @return
   */
  public static function getTeamInfoRoute( $projectid, $teamid, $projectteamid = 0,$cfg_which_database = 0 )
	{
		$params = array(	"option" => "com_sportsmanagement",
				"view" => "teaminfo",
				"p" => $projectid,
				"tid" => $teamid,
				"ptid" => $projectteamid );
	
    if ( ! is_null( $cfg_which_database) ) { $params["cfg_which_database"] = $cfg_which_database; }
    
		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );
	
		return $link;
	}
	
	/**
	 * sportsmanagementHelperRoute::getProjectTeamInfoRoute()
	 * 
	 * @param mixed $projectid
	 * @param mixed $projectteamid
	 * @param mixed $task
	 * @return
	 */
	public static function getProjectTeamInfoRoute( $projectid, $projectteamid, $task=null,$cfg_which_database = 0 )
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "teaminfo",
					"p" => $projectid,
					"ptid" => $projectteamid );
		
        if ( ! is_null( $cfg_which_database) ) { $params["cfg_which_database"] = $cfg_which_database; }
        
        if ( ! is_null( $task ) ) {
			if($task=='projectteam.edit') {
				$params["pid"] = $projectid;
				$params["cid"] = $projectteamid;
				$params["task"] = $task;
				$params["layout"] = 'form';
				$params["view"] = 'projectteam';
			}
			$query = self::buildQuery( $params );
			$link = JRoute::_( "administrator/index.php?" . $query, false );
		} else {
			$query = self::buildQuery( $params );
			$link = JRoute::_( "index.php?" . $query, false );
		}
		return $link;
	}
	
	/**
	 * sportsmanagementHelperRoute::getRivalsRoute()
	 * 
	 * @param mixed $projectid
	 * @param mixed $teamid
	 * @return
	 */
	public static function getRivalsRoute( $projectid, $teamid,$cfg_which_database = 0 )
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "rivals",
					"p" => $projectid,
					"tid" => $teamid );
if ( ! is_null( $cfg_which_database) ) { $params["cfg_which_database"] = $cfg_which_database; }
		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );

		return $link;
	}	

	/**
	 * sportsmanagementHelperRoute::getClubInfoRoute()
	 * 
	 * @param mixed $projectid
	 * @param mixed $clubid
	 * @param mixed $task
	 * @return
	 */
	public static function getClubInfoRoute( $projectid, $clubid, $task=null,$cfg_which_database = 0 )
	{
	   $app = JFactory::getApplication();
       
		$params = array("option" => "com_sportsmanagement",
					"view" => "clubinfo",
					"p" => $projectid,
					"cid" => $clubid );

		if ( ! is_null( $cfg_which_database) ) { $params["cfg_which_database"] = $cfg_which_database; }
        
        if ( ! is_null( $task ) ) 
        { 
			
            if(version_compare(JVERSION,'3.0.0','ge')) 
{
    $layout = 'edit'; 
    }
    else
    {
        $layout = 'edit';
    }
            
            if($task=='club.edit') 
            {
				$params["layout"] = $layout; 
				$params["view"] = 'club'; 
                $params["id"] = $clubid;
			}
			$query = self::buildQuery( $params );
            // diddipoeler
            // nicht im backend, sondern im frontend
			$link = JRoute::_( "administrator/index.php?" . $query. '&tmpl=component', false );
            //$link = JRoute::_( "index.php?" . $query, false );
		} 
        else 
        {
			$query = self::buildQuery( $params );
			$link = JRoute::_( "index.php?" . $query, false );
		}
        self::sportsmanagementBuildRoute($params);
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query<br><pre>'.print_r($query,true).'</pre>'),'');
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' link<br><pre>'.print_r($link,true).'</pre>'),'');
        
		return $link;
	}

	/**
	 * sportsmanagementHelperRoute::getClubPlanRoute()
	 * 
	 * @param mixed $projectid
	 * @param mixed $clubid
	 * @param mixed $task
	 * @return
	 */
	public static function getClubPlanRoute( $projectid, $clubid, $task=null,$cfg_which_database = 0 )
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "clubplan",
					"p" => $projectid,
					"cid" => $clubid );

		if ( ! is_null( $task ) ) { $params["task"] = $task; }
if ( ! is_null( $cfg_which_database) ) { $params["cfg_which_database"] = $cfg_which_database; }
		$query = self::buildQuery( $params );
		$link = JRoute::_( "index.php?" . $query, false );

		return $link;
	}

	/**
	 * sportsmanagementHelperRoute::getPlaygroundRoute()
	 * 
	 * @param mixed $projectid
	 * @param mixed $plagroundid
	 * @return
	 */
	public static function getPlaygroundRoute( $projectid, $plagroundid,$cfg_which_database = 0 )
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "playground",
					"p" => $projectid,
					"pgid" => $plagroundid );

		if ( ! is_null( $cfg_which_database) ) { $params["cfg_which_database"] = $cfg_which_database; }
        
        $query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );

		return $link;
	}

  /**
   * sportsmanagementHelperRoute::getTournamentRoute()
   * 
   * @param mixed $projectid
   * @param mixed $round
   * @return
   */
  public static function getTournamentRoute( $projectid, $round=null,$cfg_which_database = 0 )
  {
  $params = array(	"option" => "com_sportsmanagement",
					"view" => "jltournamenttree",
					"r" => $round,
					"p" => $projectid );
if ( ! is_null( $cfg_which_database) ) { $params["cfg_which_database"] = $cfg_which_database; }					
  $query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );

		return $link;
  }
  
	
  
  /**
   * sportsmanagementHelperRoute::getRankingAllTimeRoute()
   * 
   * @param mixed $leagueid
   * @param mixed $points
   * @param mixed $projectid
   * @return
   */
  public static function getRankingAllTimeRoute( $leagueid, $points, $projectid,$cfg_which_database = 0)
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "rankingalltime",
					"p" => $projectid,
					"l" => $leagueid,
					"points" => $points );
		if ( ! is_null( $cfg_which_database) ) { $params["cfg_which_database"] = $cfg_which_database; }
        $query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );

		return $link;			
	}
  				
  
	/**
	 * sportsmanagementHelperRoute::getRankingRoute()
	 * 
	 * @param mixed $projectid
	 * @param mixed $round
	 * @param mixed $from
	 * @param mixed $to
	 * @param integer $type
	 * @param integer $division
	 * @return
	 */
	public static function getRankingRoute( $projectid, $round=0, $from=0, $to=0, $type=0, $division=0,$cfg_which_database = 0 )
	{
		$params = array("option" => "com_sportsmanagement",
					"view" => "ranking",
					"p" => $projectid );

		
        $params['type'] = $type; 
        $params['r'] = $round; 
        $params['from'] = $from; 
        $params['to'] = $to; 
        $params['division'] = $division; 
        $params['cfg_which_database'] = $cfg_which_database; 
        
//        if ( ! is_null( $type ) ) { $params["type"] = $type; }
//		if ( ! is_null( $round ) ) { $params["r"] = $round; }
//		if ( ! is_null( $from) ) { $params["from"] = $from; }
//		if ( ! is_null( $to ) ) { $params["to"] = $to; }
//		if ( ! is_null( $division) ) { $params["division"] = $division; }
//        if ( ! is_null( $cfg_which_database) ) { $params["cfg_which_database"] = $cfg_which_database; }
        
		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );

		return $link;
	}

	/**
	 * sportsmanagementHelperRoute::getResultsRoute()
	 * 
	 * @param mixed $projectid
	 * @param mixed $roundid
	 * @param integer $divisionid
	 * @param integer $mode
	 * @param integer $order
	 * @param mixed $layout
	 * @return
	 */
	public static function getResultsRoute($projectid, $roundid=0, $divisionid=0, $mode=0, $order=0, $layout=0,$cfg_which_database = 0)
	{
		$params = array('option' => 'com_sportsmanagement',
					'view' => 'results',
					'p' => $projectid );
		$params['r'] = $roundid;
        $params['division'] = $divisionid;
        $params['mode'] = $mode;
        $params['order'] = $order;
        $params['layout'] = $layout;
        $params["cfg_which_database"] = $cfg_which_database;
        
//        if ( !is_null( $roundid ) ) { 
//			 
//		}
//		if ( !is_null( $divisionid ) ) {
//			
//		}
//		if ( !is_null( $mode) ) {
//			
//		}
//		if ( !is_null( $order) ) {
//			$params['order']=$order;
//		}
//		if ( !is_null( $layout) ) {
//			$params['layout']=$layout;
//		}
//		if ( ! is_null( $cfg_which_database) ) { $params["cfg_which_database"] = $cfg_which_database; }
		$query = self::buildQuery($params);
		$link = JRoute::_('index.php?' . $query ,false);

		return $link;
	}

	/**
	 * sportsmanagementHelperRoute::getMatrixRoute()
	 * 
	 * @param mixed $projectid
	 * @param integer $division
	 * @param integer $round
	 * @return
	 */
	public static function getMatrixRoute( $projectid, $division=0, $round=0,$cfg_which_database = 0 )
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "matrix",
					"p" => $projectid );

		$params["division"] = $division;
		$params["r"] = $round;
		if ( ! is_null( $cfg_which_database) ) { $params["cfg_which_database"] = $cfg_which_database; }
		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );

		return $link;
	}

	/**
	 * sportsmanagementHelperRoute::getResultsRankingRoute()
	 * 
	 * @param mixed $projectid
	 * @param mixed $round
	 * @param integer $division
	 * @return
	 */
	public static function getResultsRankingRoute( $projectid, $round=null, $division=0,$cfg_which_database = 0,$mode=0,$order=0,$layout=0 )
	{
		$params = array("option" => "com_sportsmanagement",
					"view" => "resultsranking",
					"p" => $projectid );

		$params['r'] = $round;
        $params['division'] = $division;
        $params['mode'] = $mode;
        $params['order'] = $order;
        $params['layout'] = $layout;
        $params["cfg_which_database"] = $cfg_which_database;
//        if ( ! is_null( $round ) ) { $params["r"] = $round; }
//		$params["division"] = $division;
//		if ( ! is_null( $cfg_which_database) ) { $params["cfg_which_database"] = $cfg_which_database; }
		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );

		return $link;
	}

	/**
	 * sportsmanagementHelperRoute::getResultsMatrixRoute()
	 * 
	 * @param mixed $projectid
	 * @param mixed $round
	 * @param integer $division
	 * @return
	 */
	public static function getResultsMatrixRoute( $projectid, $round=0, $division=0 ,$cfg_which_database = 0,$mode=0,$order=0,$layout=0)
	{
		$params = array("option" => "com_sportsmanagement",
					"view" => "resultsmatrix",
					"p" => $projectid );

		$params['r'] = $round;
        $params['division'] = $division;
        $params['mode'] = $mode;
        $params['order'] = $order;
        $params['layout'] = $layout;
        $params["cfg_which_database"] = $cfg_which_database;
        
//        if ( ! is_null( $round ) ) { $params["r"] = $round; }
//		$params["division"] = $division;
//		if ( ! is_null( $cfg_which_database) ) { $params["cfg_which_database"] = $cfg_which_database; }
        
		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );

		return $link;
	}

	/**
	 * sportsmanagementHelperRoute::getRankingMatrixRoute()
	 * 
	 * @param mixed $projectid
	 * @param mixed $round
	 * @param integer $division
	 * @return
	 */
	public static function getRankingMatrixRoute( $projectid, $round=0, $division=0 ,$cfg_which_database = 0)
	{
		$params = array("option" => "com_sportsmanagement",
					"view" => "rankingmatrix",
					"p" => $projectid );

		if ( ! is_null( $round ) ) { $params["r"] = $round; }
		$params["division"] = $division;
		if ( ! is_null( $cfg_which_database) ) { $params["cfg_which_database"] = $cfg_which_database; }
		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );

		return $link;
	}

	/**
	 * sportsmanagementHelperRoute::getResultsRankingMatrixRoute()
	 * 
	 * @param mixed $projectid
	 * @param mixed $round
	 * @param integer $division
	 * @return
	 */
	public static function getResultsRankingMatrixRoute( $projectid, $round=0, $division=0 ,$cfg_which_database = 0)
	{
		$params = array("option" => "com_sportsmanagement",
					"view" => "resultsrankingmatrix",
					"p" => $projectid );

		if ( ! is_null( $round ) ) { $params["r"] = $round; }
		$params["division"] = $division;
		if ( ! is_null( $cfg_which_database) ) { $params["cfg_which_database"] = $cfg_which_database; }
		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );

		return $link;
	}

	/**
	 * sportsmanagementHelperRoute::getTeamPlanRoute()
	 * 
	 * @param mixed $projectid
	 * @param mixed $teamid
	 * @param integer $division
	 * @param mixed $mode
	 * @return
	 */
	public static function getTeamPlanRoute( $projectid, $teamid, $division=0, $mode=0, $projectteamid = 0,$cfg_which_database = 0)
	{
		$params = array("option" => "com_sportsmanagement",
					"view" => "teamplan",
					"p" => $projectid,
					"tid" => $teamid,
					"division" => $division,
                    "mode" => $mode,
					"ptid" => $projectteamid,
                    "cfg_which_database" => $cfg_which_database );

//		if ( ! is_null( $mode ) ) { $params["mode"] = $mode; }
//        if ( ! is_null( $cfg_which_database) ) { $params["cfg_which_database"] = $cfg_which_database; }

		$query = self::buildQuery( $params );
		$link = JRoute::_( "index.php?" . $query, false );

		return $link;
	}

	/**
	 * sportsmanagementHelperRoute::getMatchReportRoute()
	 * 
	 * @param mixed $projectid
	 * @param mixed $matchid
	 * @return
	 */
	public static function getMatchReportRoute( $projectid, $matchid = null ,$cfg_which_database = 0)
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "matchreport",
					"p" => $projectid );

		if ( ! is_null( $matchid ) ) { $params["mid"] = $matchid; }
        if ( ! is_null( $cfg_which_database) ) { $params["cfg_which_database"] = $cfg_which_database; }

		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );

		return $link;
	}

	
	/**
	 * sportsmanagementHelperRoute::getPlayerRoute()
	 * 
	 * @param mixed $projectid
	 * @param mixed $teamid
	 * @param mixed $personid
	 * @param mixed $task
	 * @return
	 */
	public static function getPlayerRoute($projectid, $teamid, $personid, $task=null,$cfg_which_database = 0)
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "player",
					"p" => $projectid,
					"tid" => $teamid,
					"pid" => $personid );
if ( ! is_null( $cfg_which_database) ) { $params["cfg_which_database"] = $cfg_which_database; }
		if(!is_null($task)) 
        {
			if($task=='person.edit') {
				$params["layout"] = 'edit'; 
				$params["view"] = 'person';
				$params["id"] = $personid;
			}
			$query = self::buildQuery( $params );
			$link = JRoute::_( "administrator/index.php?" . $query. '&tmpl=component', false );
		} else {
			$query = self::buildQuery( $params );
			$link = JRoute::_( "index.php?" . $query, false );
		}
		
		return $link;
	}

	
	/**
	 * sportsmanagementHelperRoute::getStaffRoute()
	 * 
	 * @param mixed $projectid
	 * @param mixed $teamid
	 * @param mixed $personid
	 * @return
	 */
	public static function getStaffRoute( $projectid, $teamid, $personid ,$cfg_which_database = 0)
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "staff",
					"p" => $projectid,
					"tid" => $teamid,
					"pid" => $personid );
if ( ! is_null( $cfg_which_database) ) { $params["cfg_which_database"] = $cfg_which_database; }
		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );

		return $link;
	}


	/**
	 * sportsmanagementHelperRoute::getPersonRoute()
	 * 
	 * @param mixed $projectid
	 * @param mixed $personid
	 * @param mixed $showType
	 * @return
	 */
	public static function getPersonRoute( $projectid, $personid, $showType ,$cfg_which_database = 0)
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "person",
					"p" => $projectid,
					"pid" => $personid,
					"pt" => $showType );
if ( ! is_null( $cfg_which_database) ) { $params["cfg_which_database"] = $cfg_which_database; }
		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );

		return $link;
	}

	/**
	 * sportsmanagementHelperRoute::getPlayersRoute()
	 * 
	 * @param mixed $projectid
	 * @param mixed $teamid
	 * @param mixed $task
	 * @return
	 */
	public static function getPlayersRoute( $projectid, $teamid, $task=null, $projectteamid = 0,$cfg_which_database = 0 )
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "roster",
					"p" => $projectid,
					"tid" => $teamid,
					"ttid" => $projectteamid );

		if ( ! is_null( $cfg_which_database) ) { $params["cfg_which_database"] = $cfg_which_database; }
        
        $query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );

		return $link;
	}
	
	/**
	 * sportsmanagementHelperRoute::getPlayersRouteAllTime()
	 * 
	 * @param mixed $projectid
	 * @param mixed $teamid
	 * @param mixed $task
	 * @return
	 */
	public static function getPlayersRouteAllTime( $projectid, $teamid, $task=null,$cfg_which_database = 0 )
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "rosteralltime",
					"p" => $projectid,
					"tid" => $teamid );
if ( ! is_null( $cfg_which_database) ) { $params["cfg_which_database"] = $cfg_which_database; }
		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );

		return $link;
	}

	/**
	 * sportsmanagementHelperRoute::getDivisionsRoute()
	 * 
	 * @param mixed $projectid
	 * @param mixed $divisionid
	 * @param mixed $task
	 * @return
	 */
	public static function getDivisionsRoute( $projectid, $divisionid, $task=null,$cfg_which_database = 0 )
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "treeone",
					"p" => $projectid,
					"did" => $divisionid );
if ( ! is_null( $cfg_which_database) ) { $params["cfg_which_database"] = $cfg_which_database; }
		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );

		return $link;
	}

	/**
	 * sportsmanagementHelperRoute::getFavPlayersRoute()
	 * 
	 * @param mixed $projectid
	 * @return
	 */
	public static function getFavPlayersRoute( $projectid ,$cfg_which_database = 0)
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "players",
					"task" => "favplayers",
					"p" => $projectid );
if ( ! is_null( $cfg_which_database) ) { $params["cfg_which_database"] = $cfg_which_database; }
		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );

		return $link;
	}

	/**
	 * sportsmanagementHelperRoute::getRefereeRoute()
	 * 
	 * @param mixed $projectid
	 * @param mixed $personid
	 * @return
	 */
	public static function getRefereeRoute( $projectid, $personid ,$cfg_which_database = 0)
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "referee",
					"p" => $projectid,
					"pid" => $personid );
if ( ! is_null( $cfg_which_database) ) { $params["cfg_which_database"] = $cfg_which_database; }
		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );

		return $link;
	}

	/**
	 * sportsmanagementHelperRoute::getRefereesRoute()
	 * 
	 * @param mixed $projectid
	 * @return
	 */
	public static function getRefereesRoute( $projectid,$cfg_which_database = 0 )
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "referees",
					"p" => $projectid );
if ( ! is_null( $cfg_which_database) ) { $params["cfg_which_database"] = $cfg_which_database; }
		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );

		return $link;
	}

	/**
	 * sportsmanagementHelperRoute::getEventsRankingRoute()
	 * 
	 * @param mixed $projectid
	 * @param integer $divisionid
	 * @param integer $teamid
	 * @param integer $eventid
	 * @param integer $matchid
	 * @return
	 */
	public static function getEventsRankingRoute( $projectid, $divisionid=0, $teamid=0, $eventid=0, $matchid=0,$cfg_which_database = 0)
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "eventsranking",
					"p" => $projectid );

		$params["division"] = $divisionid;
		$params["tid"] = $teamid;
		$params["evid"] = $eventid;
		$params["mid"] = $matchid;
		if ( ! is_null( $cfg_which_database) ) { $params["cfg_which_database"] = $cfg_which_database; }
		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );

		return $link;
	}

	/**
	 * sportsmanagementHelperRoute::getCurveRoute()
	 * 
	 * @param mixed $projectid
	 * @param integer $teamid1
	 * @param integer $teamid2
	 * @param integer $division
	 * @return
	 */
	public static function getCurveRoute($projectid, $teamid1=0, $teamid2=0, $division=0,$cfg_which_database = 0)
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "curve",
					"p" => $projectid );

		$params["tid1"] = $teamid1;
		$params["tid2"] = $teamid2;
		$params["division"] = $division;
if ( ! is_null( $cfg_which_database) ) { $params["cfg_which_database"] = $cfg_which_database; }
		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );

		return $link;
	}

	/**
	 * sportsmanagementHelperRoute::getStatsChartDataRoute()
	 * 
	 * @param mixed $projectid
	 * @param integer $division
	 * @return
	 */
	public static function getStatsChartDataRoute( $projectid, $division=0 ,$cfg_which_database = 0)
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "stats",
					"layout" => "chartdata",
					"p" => $projectid );

		$params["division"] = $division; 
if ( ! is_null( $cfg_which_database) ) { $params["cfg_which_database"] = $cfg_which_database; }
		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );

		return $link;
	}

	/**
	 * sportsmanagementHelperRoute::getTeamStatsChartDataRoute()
	 * 
	 * @param mixed $projectid
	 * @param mixed $teamid
	 * @return
	 */
	public static function getTeamStatsChartDataRoute( $projectid, $teamid ,$cfg_which_database = 0)
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "teamstats",
					"layout" => "chartdata",
					"p" => $projectid,
					"tid" => $teamid );

		if ( ! is_null( $cfg_which_database) ) { $params["cfg_which_database"] = $cfg_which_database; }
        
        $query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );

		return $link;
	}

	/**
	 * sportsmanagementHelperRoute::getStatsRoute()
	 * 
	 * @param mixed $projectid
	 * @param integer $division
	 * @return
	 */
	public static function getStatsRoute( $projectid, $division = 0 ,$cfg_which_database = 0)
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "stats",
					"p" => $projectid );

		$params["division"] = $division;
if ( ! is_null( $cfg_which_database) ) { $params["cfg_which_database"] = $cfg_which_database; }
		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );

		return $link;
	}

	/**
	 * sportsmanagementHelperRoute::getBracketsRoute()
	 * 
	 * @param mixed $projectid
	 * @return
	 */
	public static function getBracketsRoute( $projectid ,$cfg_which_database = 0)
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "treetonode",
					"p" => $projectid );
if ( ! is_null( $cfg_which_database) ) { $params["cfg_which_database"] = $cfg_which_database; }
		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );

		return $link;
	}

	/**
	 * sportsmanagementHelperRoute::getStatsRankingRoute()
	 * 
	 * @param mixed $projectid
	 * @param mixed $divisionid
	 * @param mixed $teamid
	 * @param integer $statid
	 * @param mixed $order
	 * @return
	 */
	public static function getStatsRankingRoute( $projectid, $divisionid = null, $teamid = null, $statid = 0, $order = null ,$cfg_which_database = 0)
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "statsranking",
					"p" => $projectid );

		if ( isset( $divisionid ) ) { $params["division"] = $divisionid; }
		if ( isset( $teamid ) ) { $params["tid"] = $teamid; }
		if ($statid) { $params['sid'] = $statid; }
		if (strcasecmp($order, 'asc') === 0 || strcasecmp($order, 'desc') === 0) { $params['order'] = strtolower($order); }
if ( ! is_null( $cfg_which_database) ) { $params["cfg_which_database"] = $cfg_which_database; }
		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );

		return $link;
	}

	/**
	 * sportsmanagementHelperRoute::getClubsRoute()
	 * 
	 * @param mixed $projectid
	 * @param mixed $divisionid
	 * @return
	 */
	public static function getClubsRoute( $projectid, $divisionid = null ,$cfg_which_database = 0)
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "clubs",
					"p" => $projectid );

		if ( isset( $divisionid ) ) { $params["division"] = $divisionid; }
if ( ! is_null( $cfg_which_database) ) { $params["cfg_which_database"] = $cfg_which_database; }
		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );

		return $link;
	}

	/**
	 * sportsmanagementHelperRoute::getTeamsRoute()
	 * 
	 * @param mixed $projectid
	 * @param mixed $divisionid
	 * @return
	 */
	public static function getTeamsRoute( $projectid, $divisionid = null ,$cfg_which_database = 0)
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "teams",
					"p" => $projectid );

		if ( isset( $divisionid ) ) { $params["division"] = $divisionid; }
if ( ! is_null( $cfg_which_database) ) { $params["cfg_which_database"] = $cfg_which_database; }
		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );

		return $link;
	}

	/**
	 * sportsmanagementHelperRoute::getTeamStatsRoute()
	 * 
	 * @param mixed $projectid
	 * @param mixed $teamid
	 * @return
	 */
	public static function getTeamStatsRoute( $projectid, $teamid ,$cfg_which_database = 0)
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "teamstats",
					"p" => $projectid,
					"tid" => $teamid );
        $params["cfg_which_database"] = $cfg_which_database;            
//if ( ! is_null( $cfg_which_database) ) { $params["cfg_which_database"] = $cfg_which_database; }
		$query = self::buildQuery( $params );
		$link = JRoute::_( "index.php?" . $query, false );

		return $link;
	}

	/**
	 * sportsmanagementHelperRoute::getTeamStaffRoute()
	 * 
	 * @param mixed $projectid
	 * @param mixed $playerid
	 * @param mixed $showType
	 * @return
	 */
	public static function getTeamStaffRoute( $projectid, $playerid, $showType ,$cfg_which_database = 0)
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "person",
					"p" => $projectid,
					"pid" => $playerid,
					"pt" => $showType );
if ( ! is_null( $cfg_which_database) ) { $params["cfg_which_database"] = $cfg_which_database; }
		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );

		return $link;
	}

	/**
	 * sportsmanagementHelperRoute::getNextMatchRoute()
	 * 
	 * @param mixed $projectid
	 * @param mixed $matchid
	 * @return
	 */
	public static function getNextMatchRoute( $projectid, $matchid ,$cfg_which_database = 0)
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "nextmatch",
					"p" => $projectid,
					"mid" => $matchid );

		if ( ! is_null( $cfg_which_database) ) { $params["cfg_which_database"] = $cfg_which_database; }

        $query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );

		return $link;
	}
    
    /**
     * sportsmanagementHelperRoute::getEditLineupRoute()
     * 
     * @param mixed $projectid
     * @param mixed $matchid
     * @param mixed $task
     * @param mixed $team
     * @param mixed $projectTeam
     * @param mixed $match_date
     * @return
     */
    public static function getEditLineupRoute($projectid, $matchid, $task = null, $team = null, $projectTeam = null, $match_date = null,$cfg_which_database = 0)
	{
	   $params = array(	"option" => "com_sportsmanagement",
					"view" => "match",
                    "layout" => "editlineup",
					
                    "match_date" => $match_date,
                    "team" => $team,
					"pid" => $projectid,
					"id" => $matchid );

		if ( ! is_null( $task ) ) { $params['layout'] = $task; }
		if ( ! is_null( $team ) ) { $params['team'] = $team; }
		if ( ! is_null( $projectTeam ) ) { $params['pteam'] = $projectTeam; }
if ( ! is_null( $cfg_which_database) ) { $params["cfg_which_database"] = $cfg_which_database; }
		$query = self::buildQuery( $params );
		$link = JRoute::_( 'administrator/index.php?' . $query . '&tmpl=component', false );

		return $link;
    }   

	/**
	 * sportsmanagementHelperRoute::getEditEventsRoute()
	 * 
	 * @param mixed $projectid
	 * @param mixed $matchid
	 * @param mixed $task
	 * @param mixed $team
	 * @param mixed $projectTeam
	 * @return
	 */
	public static function getEditEventsRoute( $projectid, $matchid, $task = null, $team = null, $projectTeam = null, $match_date = null,$cfg_which_database = 0 )
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "match",
                    "layout" => "editevents",
					
                    "match_date" => $match_date,
                    "team" => $team,
					"pid" => $projectid,
					"id" => $matchid );

		if ( ! is_null( $task ) ) { $params['layout'] = $task; }
		if ( ! is_null( $team ) ) { $params['team'] = $team; }
		if ( ! is_null( $projectTeam ) ) { $params['pteam'] = $projectTeam; }
if ( ! is_null( $cfg_which_database) ) { $params["cfg_which_database"] = $cfg_which_database; }
		$query = self::buildQuery( $params );
		$link = JRoute::_( 'administrator/index.php?' . $query . '&tmpl=component', false );

		return $link;
	}

	/**
	 * sportsmanagementHelperRoute::getEditEventsRouteNew()
	 * 
	 * @param mixed $projectid
	 * @param mixed $matchid
	 * @param mixed $team1
	 * @param mixed $projectTeam1
	 * @param mixed $team2
	 * @param mixed $projectTeam2
	 * @return
	 */
	public static function getEditEventsRouteNew( $projectid, $matchid, $team1 = null, $projectTeam1 = null, $team2 = null, $projectTeam2 = null ,$cfg_which_database = 0)
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "editevents",
					"p" => $projectid,
					"mid" => $matchid );

		if ( ! is_null( $team1 ) ) { $params['t1'] = $team1; }
		if ( ! is_null( $team2 ) ) { $params['t2'] = $team2; }
		if ( ! is_null( $projectTeam1 ) ) { $params['pt1'] = $projectTeam1; }
		if ( ! is_null( $projectTeam2 ) ) { $params['pt2'] = $projectTeam2; }
if ( ! is_null( $cfg_which_database) ) { $params["cfg_which_database"] = $cfg_which_database; }
		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query . '&tmpl=component', false );

		return $link;
	}

	/**
	 * sportsmanagementHelperRoute::getEditMatchRoute()
	 * 
	 * @param mixed $projectid
	 * @param mixed $matchid
	 * @return
	 */
	public static function getEditMatchRoute($projectid, $matchid,$cfg_which_database = 0)
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "match",
                    "layout" => "edit",
					"pid" => $projectid,
					"id" => $matchid );
if ( ! is_null( $cfg_which_database) ) { $params["cfg_which_database"] = $cfg_which_database; }
		$query = self::buildQuery( $params );
		$link = JRoute::_( 'administrator/index.php?' . $query . '&tmpl=component', false );

		return $link;
	}
    
    
    /**
     * sportsmanagementHelperRoute::getEditStatisticsRoute()
     * 
     * @param mixed $projectid
     * @param mixed $matchid
     * @param integer $cfg_which_database
     * @return
     */
    public static function getEditStatisticsRoute($projectid, $matchid,$cfg_which_database = 0)
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "match",
                    "layout" => "editstats",
					"pid" => $projectid,
					"id" => $matchid );
if ( ! is_null( $cfg_which_database) ) { $params["cfg_which_database"] = $cfg_which_database; }
		$query = self::buildQuery( $params );
		$link = JRoute::_( 'administrator/index.php?' . $query . '&tmpl=component', false );

		return $link;
	}
    
    /**
     * sportsmanagementHelperRoute::getEditRefereesRoute()
     * 
     * @param mixed $projectid
     * @param mixed $matchid
     * @param integer $cfg_which_database
     * @return
     */
    public static function getEditRefereesRoute($projectid, $matchid,$cfg_which_database = 0)
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "match",
                    "layout" => "editreferees",
					"pid" => $projectid,
					"id" => $matchid );
if ( ! is_null( $cfg_which_database) ) { $params["cfg_which_database"] = $cfg_which_database; }
		$query = self::buildQuery( $params );
		$link = JRoute::_( 'administrator/index.php?' . $query . '&tmpl=component', false );

		return $link;
	}
    
    
//    function getEditClubRoute($club_id)
//    {
//    $params = array("option" => "com_sportsmanagement",
//					"view" => "club",
//                    "layout" => "edit",
//					"id" => $club_id );
//
//		$query = self::buildQuery( $params );
//		$link = JRoute::_( 'administrator/index.php?' . $query . '&tmpl=component', false );
//
//		return $link;    
//        
//        
//    }

	/**
	 * sportsmanagementHelperRoute::getContactRoute()
	 * 
	 * @param mixed $contactid
	 * @return
	 */
	public static function getContactRoute( $contactid ,$cfg_which_database = 0)
	{
		/* Old Route to JOOMLA built in contact id
		 $query = self::buildQuery(
		 array(
		 "option" => "com_contact",
		 "task" => "view",
		 "contact_id" => $contactid ) );
		 */
		// New Route to JOOMLA built in contact id
		$params = array(	"option" => "com_contact",
					"view" => "contact",
					"id" => $contactid );
if ( ! is_null( $cfg_which_database) ) { $params["cfg_which_database"] = $cfg_which_database; }
		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );

		return $link;
	}

	/**
	 * sportsmanagementHelperRoute::getUserProfileRouteCBE()
	 * 
	 * @param mixed $u_id
	 * @param mixed $p_id
	 * @param mixed $pl_id
	 * @return
	 */
	public static function getUserProfileRouteCBE( $u_id, $p_id, $pl_id ,$cfg_which_database = 0)
	{
		// Old Route to Community Builder User Page with support for CBE-JoomLeague Tab
		// index.php?option=com_cbe&view=userProfile&user=JOOMLA_USER_ID&jlp=PROJECT_ID&jlpid=JOOMLEAGUE_PLAYER_ID
		$params = array(	"option" => "com_cbe",
					"view" => "userProfile",
					"user" => $u_id,
					"jlp" => $p_id,
					"jlpid" => $pl_id );
if ( ! is_null( $cfg_which_database) ) { $params["cfg_which_database"] = $cfg_which_database; }
		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );

		return $link;
	}

	/**
	 * sportsmanagementHelperRoute::getUserProfileRoute()
	 * 
	 * @param mixed $userid
	 * @return
	 */
	public static function getUserProfileRoute( $userid ,$cfg_which_database = 0)
	{
		$params = array(	"option" => "com_comprofiler",
					"task" => "userProfile",
					"user" => $userid );
if ( ! is_null( $cfg_which_database) ) { $params["cfg_which_database"] = $cfg_which_database; }
		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );

		return $link;
	}

	/**
	 * sportsmanagementHelperRoute::getIcalRoute()
	 * 
	 * @param mixed $projectid
	 * @param mixed $teamid
	 * @param mixed $pgid
	 * @return
	 */
	public static function getIcalRoute( $projectid, $teamid=null, $pgid=null ,$cfg_which_database = 0)
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "ical",
					"p" => $projectid );

		if ( !is_null( $pgid ) ) { $params["pgid"] = $pgid; }
		if ( !is_null( $teamid ) ) { $params["teamid"] = $teamid; }
if ( ! is_null( $cfg_which_database) ) { $params["cfg_which_database"] = $cfg_which_database; }
		$query = self::buildQuery( $params );
		$link = JRoute::_( "index.php?" . $query, false );

		return $link;
	}
	
	/**
	 * sportsmanagementHelperRoute::buildQuery()
	 * 
	 * @param mixed $parts
	 * @return
	 */
	public static function buildQuery($parts)
	{
		if ($item = self::_findItem($parts))
		{
			$parts['Itemid'] = $item->id;
		}
		else {
			$params = JComponentHelper::getParams('com_sportsmanagement');
			if ($params->get('default_itemid')) {
				$parts['Itemid'] = intval($params->get('default_itemid'));				
			}
		}

		return JURI::buildQuery( $parts );
	}

	
	/**
	 * sportsmanagementHelperRoute::_findItem()
	 * 
	 * @param mixed $query
	 * @return
	 */
	public static function _findItem($query)
	{
		$component = JComponentHelper::getComponent('com_sportsmanagement');
		$site = new JSite();
		$menus	= $site->getMenu();
		$items	= $menus->getItems('component', $component->id);
		$user 	=  JFactory::getUser();
		$access = (int)$user->get('aid');

		if ($items) {
			foreach($items as $item)
			{
				if ((@$item->query['view'] == $query['view']) && ($item->published == 1) && ($item->access <= $access)) {

					switch ($query['view'])
					{
						case 'teaminfo':
						case 'roster':
						case 'teamplan':
						case 'teamstats':
							if ((int)@$item->query['p'] == (int) $query['p'] && (int)@$item->query['tid'] == (int) $query['tid']) {
								return $item;
							}
							break;
						case 'clubinfo':
						case 'clubplan':
							if ((int)@$item->query['p'] == (int) $query['p'] && (int)@$item->query['cid'] == (int) $query['cid']) {
								return $item;
							}
							break;
						case 'playground':
							if ((int)@$item->query['p'] == (int) $query['p'] && (int)@$item->query['pgid'] == (int) $query['pgid']) {
								return $item;
							}
							break;
						case 'ranking':
						case 'results':
						case 'resultsranking':
						case 'matrix':
						case 'resultsmatrix':
						case 'stats':
							if ((int)@$item->query['p'] == (int) $query['p']) {
								return $item;
							}
							break;
						case 'statsranking':
							if ((int)@$item->query['p'] == (int) $query['p']) {
								return $item;
							}
							break;
						case 'player':
						case 'staff':
							if (    (int)@$item->query['p'] == (int) $query['p']
							&& (int)@$item->query['tid'] == (int) $query['tid']
							&& (int)@$item->query['pid'] == (int) $query['pid']) {
								return $item;
							}
							break;
						case 'referee':
							if (    (int)@$item->query['p'] == (int) $query['p']
							&& (int)@$item->query['pid'] == (int) $query['pid']) {
								return $item;
							}
							break;
						case 'tree':
							if ((int)@$item->query['p'] == (int) $query['p'] && (int)@$item->query['did'] == (int) $query['did']) {
								return $item;
							}
							break;
					}
				}
			}
		}

		return false;
	}
}
?>
