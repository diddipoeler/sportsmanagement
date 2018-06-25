<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      helper.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage mod_sportsmanagement_ranking
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * modJSMRankingHelper
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class modJSMRankingHelper
{

	
	/**
	 * modJSMRankingHelper::getData()
	 * 
	 * @param mixed $params
	 * @return
	 */
	public static function getData(&$params)
	{
		$app = JFactory::getApplication();
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' params<br><pre>'.print_r($params,true).'</pre>'),'Notice');

		if (!class_exists('sportsmanagementModelRanking')) 
        {
			//require_once(JLG_PATH_SITE.DS.'models'.DS.'ranking.php');
            require_once(JPATH_SITE.DS.JSM_PATH.DS.'models'.DS.'project.php' );
            require_once(JPATH_SITE.DS.JSM_PATH.DS.'models'.DS.'ranking.php' );
            require_once(JPATH_SITE.DS.JSM_PATH.DS.'helpers'.DS.'ranking.php' );
		}
		
        //$app->setUserState( "com_sportsmanagement.cfg_which_database", $params->get( 'cfg_which_database' ) );
        sportsmanagementModelProject::$cfg_which_database = $params->get( 'cfg_which_database' );
		sportsmanagementModelProject::setProjectId($params->get('p'),$params->get( 'cfg_which_database' ));
        //sportsmanagementModelRanking::$cfg_which_database = $params->get( 'cfg_which_database' );

		$project = sportsmanagementModelProject::getProject($params->get( 'cfg_which_database' ),__METHOD__);

		$ranking = JSMRanking::getInstance($project,$params->get( 'cfg_which_database' ));
		$ranking->setProjectId($params->get('p'),$params->get( 'cfg_which_database' ));
		$divisionid = explode(':', $params->get('division_id', 0));
		$divisionid = $divisionid[0];
		$res   = $ranking->getRanking(null, null, $divisionid,$params->get( 'cfg_which_database' ));
		$teams = sportsmanagementModelProject::getTeamsIndexedByPtid(0,'name',$params->get( 'cfg_which_database' ),__METHOD__);
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' res<br><pre>'.print_r($res,true).'</pre>'),'Notice');
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' teams<br><pre>'.print_r($teams,true).'</pre>'),'Notice');

		$list = array();
		foreach ($res as $ptid => $t) 
        {
			$t->team = $teams[$ptid];
			$list[] = $t;
		}

		if( $params->get('visible_team') != '' )
        {
			$exParam=explode(':',$params->get('visible_team'));
			$list = modJSMRankingHelper::getShrinkedDataAroundOneTeam($list,$exParam[0],$params->get('limit', 5));
		}
		$colors = array();
		if ($params->get('show_rank_colors', 0)) 
        {
//			$mdlRanking = JModel::getInstance("Ranking", "sportsmanagementModel");
//			$mdlRanking->setProjectid($params->get('p'));
			sportsmanagementModelRanking::$projectid = $params->get('p');
            $config = sportsmanagementModelProject::getTemplateConfig("ranking",$params->get( 'cfg_which_database' ),__METHOD__);
			$colors = sportsmanagementModelProject::getColors($config["colors"]);
		}
		return array('project' => $project, 'ranking' => $list, 'colors' => $colors);

	}
    
    
    
    
    /**
     * modJSMRankingHelper::getCountGames()
     * 
     * @param mixed $projectid
     * @param mixed $ishd_update_hour
     * @return void
     */
    static function getCountGames($projectid,$ishd_update_hour)
    {
    $db = JFactory::getDBO();
    $app = JFactory::getApplication();
    $query = $db->getQuery(true);      
    $date = time();    // aktuelles Datum     
    $enddatum = $date - ($ishd_update_hour * 60 * 60);  // Ein Tag später (stunden * minuten * sekunden) 
    $match_timestamp = sportsmanagementHelper::getTimestamp($enddatum);
    $query->clear(); 
    $query->select('count(*) AS count');
    $query->from('#__sportsmanagement_match AS m ');
    $query->join('INNER','#__sportsmanagement_round AS r on r.id = m.round_id ');
    $query->join('INNER','#__sportsmanagement_project AS p on p.id = r.project_id ');
    $query->where('p.id = '. $projectid);
	$query->where('m.team1_result IS NULL ');
    $query->where('m.match_timestamp < '. $match_timestamp );
    $db->setQuery($query);
    $matchestoupdate = $db->loadResult();
    //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' matchestoupdate<br><pre>'.print_r($matchestoupdate,true).'</pre>'),'Notice');
    return $matchestoupdate;
            
    }

	/**
	 * Method to shrinked list so that the alwaysVisibleTeamId is always visible in the middle of the list
	 *
	 * @access public
	 * @return array
	 */
	static function getShrinkedDataAroundOneTeam($completeRankingList, $alwaysVisibleTeamId, $paramRowLimit)
    {
		// First Fav-Team should be always visible in the ranking view
		$rank = $completeRankingList;
		$i=0;
		foreach( $rank as $item )
        {
			$isFav= $item->team->id == $alwaysVisibleTeamId;
			if( $isFav ) {
				$limit=$paramRowLimit-1; // Limit-Parameter -1 because fav-team should be in the middle

				$startOffset = $i - floor($limit/2);
				if( $limit%2 > 0 ){
					// odd-number then more ranks before fav-team should be visible
					$startOffset -= $limit%2; //+Rest
				}

				// startOffset out of range then start with 0
				if( $startOffset < 0 ){
					$startOffset = 0;
				}

				// Array anpassen
				return array_slice($rank,$startOffset,$paramRowLimit);
			}

			$i++;
		}
		return $rank;
	}


	/**
	 * returns value corresponding to specified column
	 * @param string column
	 * @param object ranking item
	 * @return value POINTS, RESULTS, DIFF, BONUS, START....see the cases here below :)
	 */
	public static function getColValue($column, $item)
	{
		$column = ucfirst(str_replace("jl_", "", strtolower(trim($column))));
		$column = strtolower($column);
		switch ($column)
		{
			case 'points':
				return $item->getPoints();
			case 'played':
				return $item->cnt_matches;
			case 'wins':
				return $item->cnt_won;
			case 'ties':
				return $item->cnt_draw;
			case 'losses':
				return $item->cnt_lost;
			case 'wot':
				return $item->cnt_wot;
			case 'wso':
				return $item->cnt_wso;
			case 'lot':
				return $item->cnt_lot;
			case 'lso':
				return $item->cnt_lso;				
			case 'scorefor':
				return $item->sum_team1_result;
			case 'scoreagainst':
				return $item->sum_team2_result;
			case 'results':
				return $item->sum_team1_result.':'. $item->sum_team2_result;
			case 'diff':
			case 'scorediff':
				return $item->diff_team_results;
			case 'scorepct':
				return round(($item->scorePct()),2);				
			case 'bonus':
				return $item->bonus_points;
			case 'start':
				return $item->cnt_lost;
			case 'winpct':
				return round(($item->winpct()),2);
			case 'legs':
				return $item->sum_team1_legs.':'. $item->sum_team2_legs;
			case 'legsdiff':
				return $item->diff_team_legs;
			case 'legsratio':
				return round(($item->legsRatio()),2);
			case 'negpoints':
				return $item->neg_points;
			case 'oldnegpoints':
				return $item->getPoints().':'. $item->neg_points;
			case 'pointsratio':
				return round(($item->pointsRatio()),2);
			case 'gfa':
				return round(($item->getGFA()),2);
			case 'gaa':
				return round(($item->getGAA()),2);	
			case 'ppg':
				return round(($item->getPPG()),2);	
			case 'ppp':
				return round(($item->getPPP()),2);					
				
			default:
				if (isset($item->$column)) {
					return $item->$column;
				}
		}
		return '?';
	}

	/**
	 * get img for team
	 * @param object ranking row
	 * @param int type = 1 for club small logo, 2 for country
	 * @return html string
	 */
	static function getLogo($item, $type = 1)
	{
		if ($type == 1) // club small logo
		{
			if (!empty($item->team->logo_small))
			{
				return JHtml::image($item->team->logo_small, $item->team->short_name, 'class="teamlogo" width="20" ');
			}
		}
        elseif ($type == 3) // club small logo
		{
			if (!empty($item->team->logo_middle))
			{
				return JHtml::image($item->team->logo_middle, $item->team->short_name, 'class="teamlogo" width="20" ');
			}
		}
        elseif ($type == 4) // club small logo
		{
			if (!empty($item->team->logo_big))
			{
				return JHtml::image($item->team->logo_big, $item->team->short_name, 'class="teamlogo" width="20" ');
			}
		}
		else if ($type == 2 && !empty($item->team->country))
		{
			return JSMCountries::getCountryFlag($item->team->country, 'class="teamcountry"');
		}

		return '';
	}

	/**
	 * modJSMRankingHelper::getTeamLink()
	 * 
	 * @param mixed $item
	 * @param mixed $params
	 * @param mixed $project
	 * @return
	 */
	public static function getTeamLink($item, $params, $project)
	{
	   $routeparameter = array();
$routeparameter['cfg_which_database'] = $params->get('cfg_which_database');
$routeparameter['s'] = $params->get('s');
$routeparameter['p'] = $project->slug;

		switch ($params->get('teamlink'))
		{
			case 'teaminfo':
            $routeparameter['tid'] = $item->team->team_slug;
$routeparameter['ptid'] = $item->team->projectteamid;
				return sportsmanagementHelperRoute::getSportsmanagementRoute('teaminfo',$routeparameter);
			case 'roster':
            $routeparameter['tid'] = $item->team->team_slug;
$routeparameter['ptid'] = $item->team->projectteamid;
				return sportsmanagementHelperRoute::getSportsmanagementRoute('roster',$routeparameter);
			case 'teamplan':
            $routeparameter['tid'] = $item->team->team_slug;
$routeparameter['division'] = $item->team->division_slug;
$routeparameter['mode'] = 0;
$routeparameter['ptid'] = $item->team->projectteamid;
				return sportsmanagementHelperRoute::getSportsmanagementRoute('teamplan',$routeparameter);
			case 'clubinfo':
				return sportsmanagementHelperRoute::getClubInfoRoute($project->slug,$item->team->club_slug,NULL,$params->get('cfg_which_database'));

		}
	}
}
