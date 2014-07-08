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
	function getData(&$params)
	{
		//global $mainframe;

		if (!class_exists('sportsmanagementModelRanking')) {
			//require_once(JLG_PATH_SITE.DS.'models'.DS.'ranking.php');
            require_once(JPATH_SITE.DS.JSM_PATH.DS.'models'.DS.'project.php' );
            require_once(JPATH_SITE.DS.JSM_PATH.DS.'models'.DS.'ranking.php' );
            require_once(JPATH_SITE.DS.JSM_PATH.DS.'helpers'.DS.'ranking.php' );
		}
		//$model = JModelLegacy::getInstance('project', 'sportsmanagementModel');
		sportsmanagementModelProject::setProjectId($params->get('p'));

		$project = sportsmanagementModelProject::getProject();

		$ranking = JSMRanking::getInstance($project);
		$ranking->setProjectId($params->get('p'));
		$divisionid = explode(':', $params->get('division_id', 0));
		$divisionid = $divisionid[0];
		$res   = $ranking->getRanking(null, null, $divisionid);
		$teams = sportsmanagementModelProject::getTeamsIndexedByPtid();

		$list = array();
		foreach ($res as $ptid => $t) {
			$t->team = $teams[$ptid];
			$list[] = $t;
		}

		if( $params->get('visible_team') != '' ){
			$exParam=explode(':',$params->get('visible_team'));
			$list = modJSMRankingHelper::getShrinkedDataAroundOneTeam($list,$exParam[0],$params->get('limit', 5));
		}
		$colors = array();
		if ($params->get('show_rank_colors', 0)) {
//			$mdlRanking = JModelLegacy::getInstance("Ranking", "sportsmanagementModel");
//			$mdlRanking->setProjectid($params->get('p'));
			sportsmanagementModelRanking::$projectid = $params->get('p');
            $config = sportsmanagementModelProject::getTemplateConfig("ranking");
			$colors = sportsmanagementModelProject::getColors($config["colors"]);
		}
		return array('project' => $project, 'ranking' => $list, 'colors' => $colors);

	}

	/**
	 * Method to shrinked list so that the alwaysVisibleTeamId is always visible in the middle of the list
	 *
	 * @access public
	 * @return array
	 */
	function getShrinkedDataAroundOneTeam($completeRankingList, $alwaysVisibleTeamId, $paramRowLimit){
		// First Fav-Team should be always visible in the ranking view
		$rank = $completeRankingList;
		$i=0;
		foreach( $rank as $item ){
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
	function getColValue($column, $item)
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
	function getLogo($item, $type = 1)
	{
		if ($type == 1) // club small logo
		{
			if (!empty($item->team->logo_small))
			{
				return JHtml::image($item->team->logo_small, $item->team->short_name, 'class="teamlogo"');
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
	function getTeamLink($item, $params, $project)
	{
		switch ($params->get('teamlink'))
		{
			case 'teaminfo':
				return sportsmanagementHelperRoute::getTeamInfoRoute($project->slug, $item->team->team_slug);
			case 'roster':
				return sportsmanagementHelperRoute::getPlayersRoute($project->slug, $item->team->team_slug);
			case 'teamplan':
				return sportsmanagementHelperRoute::getTeamPlanRoute($project->slug, $item->team->team_slug, $item->team->division_slug);
			case 'clubinfo':
				return sportsmanagementHelperRoute::getClubInfoRoute($project->slug, $item->team->club_slug);

		}
	}
}