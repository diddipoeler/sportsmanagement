<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      route.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage helpers
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


static $season = 0;
static $view = 0;
static $option = 'com_sportsmanagement';
static $cfg_which_database = 0;

/*


$routeparameter = array();
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = 0;
$routeparameter['division'] = 0;
$routeparameter['tid'] = 0;
$routeparameter['sid'] = 0;
$routeparameter['order'] = '';
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('statsranking',$routeparameter);	
        
$routeparameter = array();
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = 0;
$routeparameter['r'] = 0;
$routeparameter['division'] = 0;
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('resultsrankingmatrix',$routeparameter);				
        
$routeparameter = array();
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = 0;
$routeparameter['division'] = 0;
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('stats',$routeparameter);
                
$routeparameter = array();
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = 0;
$routeparameter['division'] = 0;
$routeparameter['tid'] = 0;
$routeparameter['evid'] = 0;
$routeparameter['mid'] = 0;
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('eventsranking',$routeparameter);
		
$routeparameter = array();
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = 0;
$routeparameter['tid1'] = 0;
$routeparameter['tid2'] = 0;
$routeparameter['division'] = 0;
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('curve',$routeparameter);
                    
$routeparameter = array();
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = 0;
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('referees',$routeparameter);
        
$routeparameter = array();
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = 0;
$routeparameter['division'] = 0;
$routeparameter['r'] = 0;
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('matrix',$routeparameter);

$routeparameter = array();
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = 0;
$routeparameter['pgid'] = 0;
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('playground',$routeparameter);


$routeparameter = array();
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = 0;
$routeparameter['tid'] = 0;
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('teamstats',$routeparameter);

$routeparameter = array();
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = 0;
$routeparameter['tid'] = 0;
$routeparameter['pid'] = 0;
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('player',$routeparameter);

$routeparameter = array();
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = 0;
$routeparameter['pid'] = 0;
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('referee',$routeparameter);


$routeparameter = array();
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = 0;
$routeparameter['tid'] = 0;
$routeparameter['pid'] = 0;
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('staff',$routeparameter);

$routeparameter = array();
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = 0;
$routeparameter['tid'] = 0;
$routeparameter['division'] = 0;
$routeparameter['mode'] = 0;
$routeparameter['ptid'] = 0;
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('teamplan',$routeparameter);

$routeparameter = array();
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = 0;
$routeparameter['tid'] = 0;
$routeparameter['ptid'] = 0;
$routeparameter['division'] = 0;
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('roster',$routeparameter);

$routeparameter = array();
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = 0;
$routeparameter['tid'] = 0;
$routeparameter['ptid'] = 0;
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('teaminfo',$routeparameter);

$routeparameter = array();
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = 0;
$routeparameter['mid'] = 0;
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('matchreport',$routeparameter);  

$routeparameter = array();
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = 0;
$routeparameter['mid'] = $0;
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('nextmatch',$routeparameter);

$routeparameter = array();
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = 0;
$routeparameter['r'] = 0;
$routeparameter['division'] = 0;
$routeparameter['mode'] = 0;
$routeparameter['order'] = '';
$routeparameter['layout'] = '';
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('results',$routeparameter);


$routeparameter = array();
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = 0;
$routeparameter['type'] = 0;
$routeparameter['r'] = 0;
$routeparameter['from'] = 0;
$routeparameter['to'] = 0;
$routeparameter['division'] = 0;
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('ranking',$routeparameter);

*/  

  /**
   * sportsmanagementHelperRoute::getSportsmanagementRoute()
   * 
   * @param string $view
   * @param mixed $parameter
   * @return
   */
  public static function getSportsmanagementRoute($view='',$parameter = array(), $task='')
  {
  $app = JFactory::getApplication();
  $params = array("option" => self::$option,
				"view" => $view );  
  
  //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' view<br><pre>'.print_r($view,true).'</pre>'),'');  
  //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' parameter<br><pre>'.print_r($parameter,true).'</pre>'),'');  
  
  
  foreach( $parameter as $key => $value )
  {
  $params[$key] = $value;  
  }
  
  switch ( $task )
  {
    case 'person.edit':
    $params["layout"] = 'edit'; 
    $params["view"] = 'person';
	$params["id"] = $params['pid'];
    break;
    
  }
  
  $query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );
	
		return $link;
  }
  
  
  
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
	 * sportsmanagementHelperRoute::getRivalsRoute()
	 * 
	 * @param mixed $projectid
	 * @param mixed $teamid
	 * @return
	 */
	public static function getRivalsRoute( $projectid, $teamid,$cfg_which_database = 0,$s=0,$divisionid=0 )
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "rivals" );
    $params["cfg_which_database"] = $cfg_which_database;    
        $params["s"] = $s;
    
    $params["p"] = $projectid;
    $params["tid"] = $teamid;
    
    
//if ( ! is_null( $cfg_which_database) ) { $params["cfg_which_database"] = $cfg_which_database; }
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
	public static function getClubInfoRoute( $projectid, $clubid, $task=null,$cfg_which_database = 0,$s=0 )
	{
	   $app = JFactory::getApplication();
       
		$params = array("option" => "com_sportsmanagement",
					"view" => "clubinfo");


	$params["cfg_which_database"] = $cfg_which_database;
        $params["s"] = $s;
        $params["p"] = $projectid;
        $params["cid"] = $clubid;
        
        
        if ( ! is_null( $task ) ) 
        { 

            if( $task=='club.edit' ) 
            {
	$params["view"] = 'editclub'; 
        $params["id"] = $clubid;
	}
	$query = self::buildQuery( $params );
            // diddipoeler
            // nicht im backend, sondern im frontend
	$link = JRoute::_( "index.php?" . $query. '&tmpl=component', false );
		} 
        else 
        {
			$query = self::buildQuery( $params );
			$link = JRoute::_( "index.php?" . $query, false );
		}
        self::sportsmanagementBuildRoute($params);
        
		return $link;
	}



  /**
   * sportsmanagementHelperRoute::getTournamentRoute()
   * 
   * @param mixed $projectid
   * @param mixed $round
   * @return
   */
  public static function getTournamentRoute( $projectid, $round=0,$cfg_which_database = 0,$s=0 )
  {
  $params = array(	"option" => "com_sportsmanagement",
					"view" => "jltournamenttree");
                    
  $params["s"] = $s;
    $params["cfg_which_database"] = $cfg_which_database;
    $params["p"] = $projectid;
    $params["r"] = $round;
                      
//if ( ! is_null( $cfg_which_database) ) { $params["cfg_which_database"] = $cfg_which_database; }					
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
  public static function getRankingAllTimeRoute( $leagueid, $points, $projectid,$cfg_which_database = 0,$s=0,$type=0,$order='points',$dir='DESC')
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "rankingalltime" );
		
        
    $params["cfg_which_database"] = $cfg_which_database;
    $params["l"] = $leagueid;
    $params["points"] = $points;
    
    $params["type"] = $type;
    $params["order"] = $order;
    $params["dir"] = $dir;
    
    $params["s"] = $s;
    $params["p"] = $projectid;
    
    
        
//        if ( ! is_null( $cfg_which_database) ) { $params["cfg_which_database"] = $cfg_which_database; }
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
	public static function getPlayersRouteAllTime( $projectid, $teamid, $task=null,$cfg_which_database = 0,$s=0 )
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
	public static function getDivisionsRoute( $projectid, $divisionid, $task=null,$cfg_which_database = 0,$s=0 )
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
	public static function getFavPlayersRoute( $projectid ,$cfg_which_database = 0,$s = 0)
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
     * sportsmanagementHelperRoute::getStatsChartDataRoute()
     *
     * @param mixed   $projectid
     * @param integer $division
     * @param int     $cfg_which_database
     * @param int     $s
     *
     * @return string
     */
	public static function getStatsChartDataRoute( $projectid, $division=0 ,$cfg_which_database = 0,$s = 0)
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
     * @param int   $cfg_which_database
     * @param int   $s
     *
     * @return string
     */
	public static function getTeamStatsChartDataRoute( $projectid, $teamid ,$cfg_which_database = 0,$s = 0)
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
     * sportsmanagementHelperRoute::getBracketsRoute()
     *
     * @param mixed $projectid
     * @param int   $cfg_which_database
     * @param int   $s
     *
     * @return string
     */
	public static function getBracketsRoute( $projectid ,$cfg_which_database = 0,$s = 0)
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
     * sportsmanagementHelperRoute::getClubsRoute()
     *
     * @param mixed $projectid
     * @param mixed $divisionid
     * @param int   $cfg_which_database
     * @param int   $s
     *
     * @return string
     */
	public static function getClubsRoute( $projectid, $divisionid = null ,$cfg_which_database = 0,$s = 0 )
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
     * @param int   $cfg_which_database
     * @param int   $s
     *
     * @return string
     */
	public static function getTeamsRoute( $projectid, $divisionid = null ,$cfg_which_database = 0,$s = 0 )
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
     * sportsmanagementHelperRoute::getTeamStaffRoute()
     *
     * @param mixed $projectid
     * @param mixed $playerid
     * @param mixed $showType
     * @param int   $cfg_which_database
     * @param int   $s
     *
     * @return string
     */
	public static function getTeamStaffRoute( $projectid, $playerid, $showType ,$cfg_which_database = 0,$s = 0 )
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
     * sportsmanagementHelperRoute::getEditLineupRoute()
     * 
     * @param mixed $projectid
     * @param mixed $matchid
     * @param string $layout
     * @param integer $team
     * @param integer $projectTeam
     * @param string $match_date
     * @param integer $cfg_which_database
     * @param integer $s
     * @param integer $r
     * @param integer $division
     * @param string $oldlayout
     * @return
     */
    public static function getEditLineupRoute($projectid, $matchid, $layout = 'editlineup', $team = 0, $projectTeam = 0, $match_date = '0000-00-00',$cfg_which_database = 0,$s = 0,$r = 0,$division = 0,$oldlayout = '' )
	{
//	   $params = array(	"option" => "com_sportsmanagement",
//					"view" => "results",
//                    "layout" => "editlineup",
//					
//                    "match_date" => $match_date,
//                    "team" => $team,
//					"p" => $projectid,
//					"id" => $matchid );
//
//		if ( ! is_null( $task ) ) { $params['layout'] = $task; }
//		if ( ! is_null( $team ) ) { $params['team'] = $team; }
//		if ( ! is_null( $projectTeam ) ) { $params['pteam'] = $projectTeam; }
//if ( ! is_null( $cfg_which_database) ) { $params["cfg_which_database"] = $cfg_which_database; }

$params = array("option" => "com_sportsmanagement",
		"view" => "editmatch",
                "cfg_which_database" => $cfg_which_database,
                "s" => $s,
                "p" => $projectid,
                "r" => $r,
                "division" => $division,
                "mode" => 0,
                "order" => 0,
                "layout" => $layout,
		"matchid" => $matchid,
                "tmpl" => "component",
                "oldlayout" => $oldlayout,
                "team" => $team,
                "pteam" => $projectTeam,
		"match_date" => $match_date
                    );                    



		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query , false );
        //$link = JRoute::_( 'index2.php?' . $query , false );

		return $link;
    }

    /**
     * sportsmanagementHelperRoute::getContactRoute()
     *
     * @param mixed $contactid
     * @param int   $cfg_which_database
     * @param int   $s
     *
     * @return string
     */
	public static function getContactRoute( $contactid ,$cfg_which_database = 0,$s = 0)
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
     * @param int   $cfg_which_database
     * @param int   $s
     *
     * @return string
     */
	public static function getUserProfileRouteCBE( $u_id, $p_id, $pl_id ,$cfg_which_database = 0,$s = 0)
	{

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
     * @param int   $cfg_which_database
     * @param int   $s
     *
     * @return string
     */
	public static function getUserProfileRoute( $userid ,$cfg_which_database = 0,$s = 0)
	{
		$params = array(	"option" => "com_comprofiler",
					"task" => "userProfile",
					"user" => $userid );
if ( ! is_null( $cfg_which_database) ) { $params["cfg_which_database"] = $cfg_which_database; }
		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );

		return $link;
	}

    ///**
//	 * sportsmanagementHelperRoute::getNextMatchRoute()
//	 *
//     * @param mixed $project_id
//     * @param mixed $match_id
//     * @return string
//     */
//    public static function getNextMatchRoute($project_id, $match_id) {
//        $params = array();
//        $params['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
//        $params['s'] = JFactory::getApplication()->input->getInt('s',0);
//        $params['p'] = $project_id;
//        $params['mid'] = $match_id;
//        return sportsmanagementHelperRoute::getSportsmanagementRoute('matchreport',$params);
//    }

//    /**
//     * sportsmanagementHelperRoute::getIcalRoute()
//     *
//     * @param mixed $projectid
//     * @param mixed $teamid
//     * @param mixed $pgid
//     * @param int   $cfg_which_database
//     * @param int   $s
//     *
//     * @return string
//     */
//	public static function getIcalRoute( $projectid, $teamid=null, $pgid=null ,$cfg_which_database = 0,$s = 0)
//	{
//		$params = array(	"option" => "com_sportsmanagement",
//					"view" => "ical",
//					"p" => $projectid );
//
//		if ( !is_null( $pgid ) ) { $params["pgid"] = $pgid; }
//		if ( !is_null( $teamid ) ) { $params["teamid"] = $teamid; }
//if ( ! is_null( $cfg_which_database) ) { $params["cfg_which_database"] = $cfg_which_database; }
//		$query = self::buildQuery( $params );
//		$link = JRoute::_( "index.php?" . $query, false );
//
//		return $link;
//	}

    /**
     * sportsmanagementHelperRoute::buildQuery()
     *
     * @param mixed $parts
     *
     * @return string
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
