<?php

// no direct access
defined('_JEXEC') or die('Restricted access');

// Component Helper
jimport('joomla.application.component.helper');

/**
 *
 */
class sportsmanagementHelperRoute
{
	
  public static function getKunenaRoute( $sb_catid )
	{
		$params = array(	"option" => "com_kunena",
				"view" => "topic",
				"catid" => $sb_catid );
	
		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );
	
		return $link;
	}
  
  public static function getTeamInfoRoute( $projectid, $teamid )
	{
		$params = array(	"option" => "com_sportsmanagement",
				"view" => "teaminfo",
				"p" => $projectid,
				"tid" => $teamid );
	
		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );
	
		return $link;
	}
	
	public static function getProjectTeamInfoRoute( $projectid, $projectteamid, $task=null )
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "teaminfo",
					"p" => $projectid,
					"ptid" => $projectteamid );
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
	
	public static function getRivalsRoute( $projectid, $teamid )
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "rivals",
					"p" => $projectid,
					"tid" => $teamid );

		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );

		return $link;
	}	

	public static function getClubInfoRoute( $projectid, $clubid, $task=null )
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "clubinfo",
					"p" => $projectid,
					"cid" => $clubid );

		if ( ! is_null( $task ) ) { 
			if($task=='club.edit') {
				$params["layout"] = 'form'; 
				$params["view"] = 'club'; 
			}
			$query = self::buildQuery( $params );
            // diddipoeler
            // nicht im backend, sondern im frontend
			$link = JRoute::_( "administrator/index.php?" . $query, false );
            //$link = JRoute::_( "index.php?" . $query, false );
		} else {
			$query = self::buildQuery( $params );
			$link = JRoute::_( "index.php?" . $query, false );
		}
		return $link;
	}

	public static function getClubPlanRoute( $projectid, $clubid, $task=null )
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "clubplan",
					"p" => $projectid,
					"cid" => $clubid );

		if ( ! is_null( $task ) ) { $params["task"] = $task; }

		$query = self::buildQuery( $params );
		$link = JRoute::_( "index.php?" . $query, false );

		return $link;
	}

	public static function getPlaygroundRoute( $projectid, $plagroundid )
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "playground",
					"p" => $projectid,
					"pgid" => $plagroundid );

		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );

		return $link;
	}

  public static function getTournamentRoute( $projectid, $round=null )
  {
  $params = array(	"option" => "com_sportsmanagement",
					"view" => "jltournamenttree",
					"r" => $round,
					"p" => $projectid );
					
  $query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );

		return $link;
  }
  
	
  
  public static function getRankingAllTimeRoute( $leagueid, $points, $projectid)
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "rankingalltime",
					"p" => $projectid,
					"l" => $leagueid,
					"points" => $points );
		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );

		return $link;			
	}
  				
  /**
	 * 
	 * @param int $projectid
	 * @param int $round
	 * @param int $from
	 * @param int $to
	 * @param int $type
	 */
	public static function getRankingRoute( $projectid, $round=null, $from=null, $to=null, $type=0, $division=0 )
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "ranking",
					"p" => $projectid );

		if ( ! is_null( $type ) ) { $params["type"] = $type; }
		if ( ! is_null( $round ) ) { $params["r"] = $round; }
		if ( ! is_null( $from) ) { $params["from"] = $from; }
		if ( ! is_null( $to ) ) { $params["to"] = $to; }
		if ( ! is_null( $division) ) { $params["division"] = $division; }
		
		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );

		return $link;
	}

	public static function getResultsRoute($projectid, $roundid=null, $divisionid=0, $mode=0, $order=0, $layout=null)
	{
		$params = array(	'option' => 'com_sportsmanagement',
					'view' => 'results',
					'p' => $projectid );
		if ( !is_null( $roundid ) ) { 
			$params['r']=$roundid; 
		}
		if ( !is_null( $divisionid ) ) {
			$params['division']=$divisionid;
		}
		if ( !is_null( $mode) ) {
			$params['mode']=$mode;
		}
		if ( !is_null( $order) ) {
			$params['order']=$order;
		}
		if ( !is_null( $layout) ) {
			$params['layout']=$layout;
		}
		
		$query = self::buildQuery($params);
		$link = JRoute::_('index.php?' . $query ,false);

		return $link;
	}

	public static function getMatrixRoute( $projectid, $division=0, $round=0 )
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "matrix",
					"p" => $projectid );

		$params["division"] = $division;
		$params["r"] = $round;
		
		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );

		return $link;
	}

	public static function getResultsRankingRoute( $projectid, $round=null, $division=0 )
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "resultsranking",
					"p" => $projectid );

		if ( ! is_null( $round ) ) { $params["r"] = $round; }
		$params["division"] = $division;
		
		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );

		return $link;
	}

	public static function getResultsMatrixRoute( $projectid, $round=null, $division=0 )
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "resultsmatrix",
					"p" => $projectid );

		if ( ! is_null( $round ) ) { $params["r"] = $round; }
		$params["division"] = $division;
		
		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );

		return $link;
	}

	public static function getRankingMatrixRoute( $projectid, $round=null, $division=0 )
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "rankingmatrix",
					"p" => $projectid );

		if ( ! is_null( $round ) ) { $params["r"] = $round; }
		$params["division"] = $division;
		
		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );

		return $link;
	}

	public static function getResultsRankingMatrixRoute( $projectid, $round=null, $division=0 )
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "resultsrankingmatrix",
					"p" => $projectid );

		if ( ! is_null( $round ) ) { $params["r"] = $round; }
		$params["division"] = $division;
		
		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );

		return $link;
	}

	public static function getTeamPlanRoute( $projectid, $teamid, $division=0, $mode=null )
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "teamplan",
					"p" => $projectid,
					"tid" => $teamid,
					"division" => $division );

		if ( ! is_null( $mode ) ) { $params["mode"] = $mode; }

		$query = self::buildQuery( $params );
		$link = JRoute::_( "index.php?" . $query, false );

		return $link;
	}

	public static function getMatchReportRoute( $projectid, $matchid = null )
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "matchreport",
					"p" => $projectid );

		if ( ! is_null( $matchid ) ) { $params["mid"] = $matchid; }

		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );

		return $link;
	}

	/**
	 * return links to a team player
	 * @param int projectid
	 * @param int teamid
	 * @param int personid
	 * @param string task
	 * @return url
	 */
	public static function getPlayerRoute($projectid, $teamid, $personid, $task=null)
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "player",
					"p" => $projectid,
					"tid" => $teamid,
					"pid" => $personid );

		if(!is_null($task)) {
			if($task=='person.edit') {
				$params["layout"] = 'form'; 
				$params["view"] = 'person';
				$params["cid"] = $personid;
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
	 * return links to a team staff
	 * @param int projectid
	 * @param int teamid
	 * @param int personid
	 * @return url
	 */
	public static function getStaffRoute( $projectid, $teamid, $personid )
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "staff",
					"p" => $projectid,
					"tid" => $teamid,
					"pid" => $personid );

		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );

		return $link;
	}

	/**
	 * returns url to a person
	 * @param int project id
	 * @param int person id
	 * @param int Type: 1 for player, 2 for staff, 3 for referee
	 * @deprecated since 1.5
	 * @return url
	 */
	public static function getPersonRoute( $projectid, $personid, $showType )
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "person",
					"p" => $projectid,
					"pid" => $personid,
					"pt" => $showType );

		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );

		return $link;
	}

	public static function getPlayersRoute( $projectid, $teamid, $task=null )
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "roster",
					"p" => $projectid,
					"tid" => $teamid );

		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );

		return $link;
	}
	
	public static function getPlayersRouteAllTime( $projectid, $teamid, $task=null )
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "rosteralltime",
					"p" => $projectid,
					"tid" => $teamid );

		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );

		return $link;
	}

	public static function getDivisionsRoute( $projectid, $divisionid, $task=null )
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "treeone",
					"p" => $projectid,
					"did" => $divisionid );

		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );

		return $link;
	}

	public static function getFavPlayersRoute( $projectid )
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "players",
					"task" => "favplayers",
					"p" => $projectid );

		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );

		return $link;
	}

	public static function getRefereeRoute( $projectid, $personid )
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "referee",
					"p" => $projectid,
					"pid" => $personid );

		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );

		return $link;
	}

	public static function getRefereesRoute( $projectid )
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "referees",
					"p" => $projectid );

		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );

		return $link;
	}

	public static function getEventsRankingRoute( $projectid, $divisionid=0, $teamid=0, $eventid=0, $matchid=0)
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "eventsranking",
					"p" => $projectid );

		$params["division"] = $divisionid;
		$params["tid"] = $teamid;
		$params["evid"] = $eventid;
		$params["mid"] = $matchid;
		
		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );

		return $link;
	}

	public static function getCurveRoute($projectid, $teamid1=0, $teamid2=0, $division=0)
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "curve",
					"p" => $projectid );

		$params["tid1"] = $teamid1;
		$params["tid2"] = $teamid2;
		$params["division"] = $division;

		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );

		return $link;
	}

	public static function getStatsChartDataRoute( $projectid, $division=0 )
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "stats",
					"layout" => "chartdata",
					"p" => $projectid );

		$params["division"] = $division; 

		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );

		return $link;
	}

	public static function getTeamStatsChartDataRoute( $projectid, $teamid )
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "teamstats",
					"layout" => "chartdata",
					"p" => $projectid,
					"tid" => $teamid );

		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );

		return $link;
	}

	public static function getStatsRoute( $projectid, $division = 0 )
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "stats",
					"p" => $projectid );

		$params["division"] = $division;

		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );

		return $link;
	}

	public static function getBracketsRoute( $projectid )
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "treetonode",
					"p" => $projectid );

		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );

		return $link;
	}

	public static function getStatsRankingRoute( $projectid, $divisionid = null, $teamid = null, $statid = 0, $order = null )
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "statsranking",
					"p" => $projectid );

		if ( isset( $divisionid ) ) { $params["division"] = $divisionid; }
		if ( isset( $teamid ) ) { $params["tid"] = $teamid; }
		if ($statid) { $params['sid'] = $statid; }
		if (strcasecmp($order, 'asc') === 0 || strcasecmp($order, 'desc') === 0) { $params['order'] = strtolower($order); }

		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );

		return $link;
	}

	public static function getClubsRoute( $projectid, $divisionid = null )
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "clubs",
					"p" => $projectid );

		if ( isset( $divisionid ) ) { $params["division"] = $divisionid; }

		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );

		return $link;
	}

	public static function getTeamsRoute( $projectid, $divisionid = null )
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "teams",
					"p" => $projectid );

		if ( isset( $divisionid ) ) { $params["division"] = $divisionid; }

		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );

		return $link;
	}

	public static function getTeamStatsRoute( $projectid, $teamid )
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "teamstats",
					"p" => $projectid,
					"tid" => $teamid );

		$query = self::buildQuery( $params );
		$link = JRoute::_( "index.php?" . $query, false );

		return $link;
	}

	public static function getTeamStaffRoute( $projectid, $playerid, $showType )
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "person",
					"p" => $projectid,
					"pid" => $playerid,
					"pt" => $showType );

		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );

		return $link;
	}

	public static function getNextMatchRoute( $projectid, $matchid )
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "nextmatch",
					"p" => $projectid,
					"mid" => $matchid );

		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );

		return $link;
	}

	public static function getEditEventsRoute( $projectid, $matchid, $task = null, $team = null, $projectTeam = null )
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "editevents",
					//"no_html" => 1,
					"p" => $projectid,
					"mid" => $matchid );

		if ( ! is_null( $task ) ) { $params['layout'] = $task; }
		if ( ! is_null( $team ) ) { $params['team'] = $team; }
		if ( ! is_null( $projectTeam ) ) { $params['pteam'] = $projectTeam; }

		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query . '&tmpl=component', false );

		return $link;
	}

	public static function getEditEventsRouteNew( $projectid, $matchid, $team1 = null, $projectTeam1 = null, $team2 = null, $projectTeam2 = null )
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "editevents",
					"p" => $projectid,
					"mid" => $matchid );

		if ( ! is_null( $team1 ) ) { $params['t1'] = $team1; }
		if ( ! is_null( $team2 ) ) { $params['t2'] = $team2; }
		if ( ! is_null( $projectTeam1 ) ) { $params['pt1'] = $projectTeam1; }
		if ( ! is_null( $projectTeam2 ) ) { $params['pt2'] = $projectTeam2; }

		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query . '&tmpl=component', false );

		return $link;
	}

	public static function getEditMatchRoute($projectid, $matchid)
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "editmatch",
					"p" => $projectid,
					"mid" => $matchid );

		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query . '&tmpl=component', false );

		return $link;
	}

	public static function getContactRoute( $contactid )
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

		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );

		return $link;
	}

	public static function getUserProfileRouteCBE( $u_id, $p_id, $pl_id )
	{
		// Old Route to Community Builder User Page with support for CBE-JoomLeague Tab
		// index.php?option=com_cbe&view=userProfile&user=JOOMLA_USER_ID&jlp=PROJECT_ID&jlpid=JOOMLEAGUE_PLAYER_ID
		$params = array(	"option" => "com_cbe",
					"view" => "userProfile",
					"user" => $u_id,
					"jlp" => $p_id,
					"jlpid" => $pl_id );

		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );

		return $link;
	}

	public static function getUserProfileRoute( $userid )
	{
		$params = array(	"option" => "com_comprofiler",
					"task" => "userProfile",
					"user" => $userid );

		$query = self::buildQuery( $params );
		$link = JRoute::_( 'index.php?' . $query, false );

		return $link;
	}

	public static function getIcalRoute( $projectid, $teamid=null, $pgid=null )
	{
		$params = array(	"option" => "com_sportsmanagement",
					"view" => "ical",
					"p" => $projectid );

		if ( !is_null( $pgid ) ) { $params["pgid"] = $pgid; }
		if ( !is_null( $teamid ) ) { $params["teamid"] = $teamid; }

		$query = self::buildQuery( $params );
		$link = JRoute::_( "index.php?" . $query, false );

		return $link;
	}
	
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
	 * Determines the Itemid
	 *
	 * searches if a menuitem for this item exists
	 * if not the first match will be returned
	 *
	 * @param array The id and view
	 * @since 0.9
	 *
	 * @return int Itemid
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
