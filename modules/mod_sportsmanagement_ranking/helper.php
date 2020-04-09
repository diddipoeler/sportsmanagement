<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage mod_sportsmanagement_ranking
 * @file       helper.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

/**
 * modJSMRankingHelper
 *
 * @package
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class modJSMRankingHelper
{

	/**
	 * modJSMRankingHelper::getData()
	 *
	 * @param   mixed  $params
	 *
	 * @return
	 */
	public static function getData(&$params)
	{
		$app = Factory::getApplication();

		if (!class_exists('sportsmanagementModelRanking'))
		{
			JLoader::import('components.com_sportsmanagement.models.project', JPATH_SITE);
			JLoader::import('components.com_sportsmanagement.models.ranking', JPATH_SITE);
			JLoader::import('components.com_sportsmanagement.helpers.ranking', JPATH_SITE);
		}

		sportsmanagementModelProject::$cfg_which_database = $params->get('cfg_which_database');
		sportsmanagementModelProject::setProjectId($params->get('p'), $params->get('cfg_which_database'));

		$project = sportsmanagementModelProject::getProject($params->get('cfg_which_database'), __METHOD__);

		$ranking = JSMRanking::getInstance($project, $params->get('cfg_which_database'));
		$ranking->setProjectId($params->get('p'), $params->get('cfg_which_database'));

		//		$divisionid = explode(':', $params->get('division_id', 0));
		//		$divisionid = $divisionid[0];
		$divisionid = (int) $params->get('division_id', 0);
		$res        = $ranking->getRanking(null, null, $divisionid, $params->get('cfg_which_database'));
		$teams      = sportsmanagementModelProject::getTeamsIndexedByPtid(0, 'name', $params->get('cfg_which_database'), __METHOD__);

		$list = array();

		foreach ($res as $ptid => $t)
		{
			$t->team = $teams[$ptid];
			$list[]  = $t;
		}

		if ($params->get('visible_team') != '')
		{
			$exParam = explode(':', $params->get('visible_team'));
			$list    = self::getShrinkedDataAroundOneTeam($list, $exParam[0], $params->get('limit', 5));
		}

		$colors = array();

		if ($params->get('show_rank_colors', 0))
		{
			sportsmanagementModelRanking::$projectid = $params->get('p');
			$config                                  = sportsmanagementModelProject::getTemplateConfig("ranking", $params->get('cfg_which_database'), __METHOD__);
			$colors                                  = sportsmanagementModelProject::getColors($config["colors"]);
		}

		return array('project' => $project, 'ranking' => $list, 'colors' => $colors);

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
		$i    = 0;

		foreach ($rank as $item)
		{
			$isFav = $item->team->id == $alwaysVisibleTeamId;

			if ($isFav)
			{
				$limit = $paramRowLimit - 1; // Limit-Parameter -1 because fav-team should be in the middle

				$startOffset = $i - floor($limit / 2);

				if ($limit % 2 > 0)
				{
					// Odd-number then more ranks before fav-team should be visible
					$startOffset -= $limit % 2; // +Rest
				}

				// StartOffset out of range then start with 0
				if ($startOffset < 0)
				{
					$startOffset = 0;
				}

				// Array anpassen
				return array_slice($rank, $startOffset, $paramRowLimit);
			}

			$i++;
		}

		return $rank;
	}

	/**
	 * modJSMRankingHelper::getCountGames()
	 *
	 * @param   mixed  $projectid
	 * @param   mixed  $ishd_update_hour
	 *
	 * @return void
	 */
	static function getCountGames($projectid, $ishd_update_hour)
	{
		$db              = Factory::getDBO();
		$app             = Factory::getApplication();
		$query           = $db->getQuery(true);
		$date            = time();    // Aktuelles Datum
		$enddatum        = $date - ($ishd_update_hour * 60 * 60);  // Ein Tag später (stunden * minuten * sekunden)
		$match_timestamp = sportsmanagementHelper::getTimestamp($enddatum);
		$query->clear();
		$query->select('count(*) AS count');
		$query->from('#__sportsmanagement_match AS m ');
		$query->join('INNER', '#__sportsmanagement_round AS r on r.id = m.round_id ');
		$query->join('INNER', '#__sportsmanagement_project AS p on p.id = r.project_id ');
		$query->where('p.id = ' . $projectid);
		$query->where('m.team1_result IS NULL ');
		$query->where('m.match_timestamp < ' . $match_timestamp);
		$db->setQuery($query);
		$matchestoupdate = $db->loadResult();

		return $matchestoupdate;
	}

	/**
	 * returns value corresponding to specified column
	 *
	 * @param   string column
	 * @param   object ranking item
	 *
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
				return $item->sum_team1_result . ':' . $item->sum_team2_result;
			case 'diff':
			case 'scorediff':
				return $item->diff_team_results;
			case 'scorepct':
				return round(($item->scorePct()), 2);
			case 'bonus':
				return $item->bonus_points;
			case 'start':
				return $item->cnt_lost;
			case 'winpct':
				return round(($item->winpct()), 2);
			case 'legs':
				return $item->sum_team1_legs . ':' . $item->sum_team2_legs;
			case 'legsdiff':
				return $item->diff_team_legs;
			case 'legsratio':
				return round(($item->legsRatio()), 2);
			case 'negpoints':
				return $item->neg_points;
			case 'oldnegpoints':
				return $item->getPoints() . ':' . $item->neg_points;
			case 'pointsratio':
				return round(($item->pointsRatio()), 2);
			case 'gfa':
				return round(($item->getGFA()), 2);
			case 'gaa':
				return round(($item->getGAA()), 2);
			case 'ppg':
				return round(($item->getPPG()), 2);
			case 'ppp':
				return round(($item->getPPP()), 2);

			default:
				if (isset($item->$column))
				{
					return $item->$column;
				}
		}

		return '?';
	}

	/**
	 * get img for team
	 *
	 * @param   object ranking row
	 * @param   int type = 1 for club small logo, 2 for country
	 *
	 * @return html string
	 */
	static function getLogo($item, $type = 1)
	{
		if ($type == 1) // Club small logo
		{
			if (!empty($item->team->logo_small))
			{
				return HTMLHelper::image($item->team->logo_small, $item->team->short_name, 'class="teamlogo" width="20" ');
			}
		}
		elseif ($type == 3) // Club small logo
		{
			if (!empty($item->team->logo_middle))
			{
				return HTMLHelper::image($item->team->logo_middle, $item->team->short_name, 'class="teamlogo" width="20" ');
			}
		}
		elseif ($type == 4) // Club small logo
		{
			if (!empty($item->team->logo_big))
			{
				return HTMLHelper::image($item->team->logo_big, $item->team->short_name, 'class="teamlogo" width="20" ');
			}
		}
		elseif ($type == 2 && !empty($item->team->country))
		{
			return JSMCountries::getCountryFlag($item->team->country, 'class="teamcountry"');
		}

		return '';
	}

	/**
	 * modJSMRankingHelper::getTeamLink()
	 *
	 * @param   mixed  $item
	 * @param   mixed  $params
	 * @param   mixed  $project
	 *
	 * @return
	 */
	public static function getTeamLink($item, $params, $project)
	{
		$routeparameter                       = array();
		$routeparameter['cfg_which_database'] = $params->get('cfg_which_database');
		$routeparameter['s']                  = $params->get('s');
		$routeparameter['p']                  = $project->slug;

		switch ($params->get('teamlink'))
		{
			case 'teaminfo':
				$routeparameter['tid']  = $item->team->team_slug;
				$routeparameter['ptid'] = $item->team->projectteamid;

				return sportsmanagementHelperRoute::getSportsmanagementRoute('teaminfo', $routeparameter);
			case 'roster':
				$routeparameter['tid']  = $item->team->team_slug;
				$routeparameter['ptid'] = $item->team->projectteamid;

				return sportsmanagementHelperRoute::getSportsmanagementRoute('roster', $routeparameter);
			case 'teamplan':
				$routeparameter['tid']      = $item->team->team_slug;
				$routeparameter['division'] = $item->team->division_slug;
				$routeparameter['mode']     = 0;
				$routeparameter['ptid']     = $item->team->projectteamid;

				return sportsmanagementHelperRoute::getSportsmanagementRoute('teamplan', $routeparameter);
			case 'clubinfo':
				return sportsmanagementHelperRoute::getClubInfoRoute($project->slug, $item->team->club_slug, null, $params->get('cfg_which_database'));
		}
	}
}
