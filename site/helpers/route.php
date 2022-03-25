<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage helpers
 * @file       route.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Router\Router;
use Joomla\CMS\Component\Router\RouterBase;
use Joomla\CMS\Language\Text;

/**
 * sportsmanagementHelperRoute
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementHelperRoute
{
	static $season = 0;

	static $view = 0;

	static $option = 'com_sportsmanagement';

	static $cfg_which_database = 0;

	public static $views = array(
		'about'    => array('cfg_which_database' => '', 's' => '', 'p' => '' ),
		'calendar' => array('cfg_which_database' => '', 's' => '', 'p' => '', 'tid' => '', 'division' => '', 'mode' => '', 'ptid' => ''),
		'clubinfo' => array('cfg_which_database' => '', 's' => '', 'p' => '', 'cid' => ''),
		'clubplan' => array('cfg_which_database' => '', 's' => '', 'cid' => '', 'p' => '', 'startdate' => '', 'enddate' => ''  ),
		'curve'    => array('cfg_which_database' => '', 's' => '', 'p' => '', 'tid1' => '', 'tid2' => '','division' => ''),

		'editprojectteam' => array('tmpl' => '', 'ptid' => '', 'tid' => '', 'p' => ''),
		'editteam'        => array('tmpl' => '', 'ptid' => '', 'tid' => '', 'p' => ''),
		'editperson'      => array('cfg_which_database' => '', 's' => '', 'p' => '', 'tid' => '', 'pid' => ''),

		'editclub'      => array('cfg_which_database' => '', 's' => '', 'p' => '', 'cid' => '', 'id' => '', 'tmpl' => ''),
		'editmatch'     => array('cfg_which_database' => '', 's' => '', 'p' => '', 'r' => '', 'division' => '', 'mode' => '', 'order' => '', 'layout' => '', 'matchid' => '', 'tmpl' => '', 'oldlayout' => '', 'team' => '', 'pteam' => ''),
		'eventsranking' => array('cfg_which_database' => '', 's' => '', 'p' => '', 'tid' => '', 'evid' => '', 'mid' => ''),
		'ical'          => array('cfg_which_database' => '', 's' => '', 'p' => '', 'tid' => '', 'division' => '', 'mode' => '', 'ptid' => ''),
		'scoresheet'    => array('cfg_which_database' => '', 'p' => '', 'mid' => ''),

		'jltournamenttree' => array('cfg_which_database' => '', 's' => '', 'p' => '', 'r' => ''),
		'matchreport'      => array('cfg_which_database' => '', 's' => '', 'p' => '', 'mid' => ''),
		'matrix'           => array('cfg_which_database' => '', 's' => '', 'p' => '', 'division' => '', 'r' => ''),
		'nextmatch'        => array('cfg_which_database' => '', 's' => '', 'p' => '', 'mid' => ''),
		'player'           => array('cfg_which_database' => '', 's' => '', 'p' => '', 'tid' => '', 'pid' => ''),

		'playground' => array('cfg_which_database' => '', 's' => '', 'p' => '', 'pgid' => ''),
        
        'leaguechampionoverview'    => array('cfg_which_database' => '', 'l' => '', 's' => '', 'p' => ''),

		'ranking'           => array('cfg_which_database' => '', 's' => '', 'p' => '', 'type' => '', 'r' => '', 'from' => '', 'to' => '', 'division' => ''),
		'rankingalltime'    => array('cfg_which_database' => '', 'l' => '', 'points' => '', 'type' => '', 'order' => '', 'dir' => '', 's' => '', 'p' => ''),
		'referee'           => array('cfg_which_database' => '', 's' => '', 'p' => '', 'pid' => ''),
		'referees'          => array('cfg_which_database' => '', 's' => '', 'p' => '', 'division' => '', 'r' => ''),
		'results'           => array('cfg_which_database' => '', 's' => '', 'p' => '', 'r' => '', 'division' => '', 'mode' => '', 'order' => '', 'layout' => ''),
		'resultsranking'    => array('cfg_which_database' => '', 's' => '', 'p' => '', 'r' => '', 'mode' => '', 'order' => '', 'layout' => '', 'division' => ''),
		'rivals'            => array('cfg_which_database' => '', 's' => '', 'p' => '', 'tid' => ''),
		'roster'            => array('cfg_which_database' => '', 's' => '', 'p' => '', 'tid' => '', 'ptid' => ''),
        'rosteralltime'     => array('cfg_which_database' => '', 's' => '', 'p' => '', 'tid' => '', 'start' => ''),
        
		'staff'             => array('cfg_which_database' => '', 's' => '', 'p' => '', 'tid' => '', 'pid' => ''),
		'stats'             => array('cfg_which_database' => '', 's' => '', 'p' => ''),
		'statsranking'      => array('cfg_which_database' => '', 's' => '', 'p' => '', 'division' => '', 'tid' => '', 'sid' => '', 'order' => ''),
		'teaminfo'          => array('cfg_which_database' => '', 's' => '', 'p' => '', 'tid' => '', 'ptid' => ''),
		'teamplan'          => array('cfg_which_database' => '', 's' => '', 'p' => '', 'tid' => '', 'division' => '', 'mode' => '', 'ptid' => ''),
		'teams'             => array('cfg_which_database' => '', 's' => '', 'p' => '', 'division' => ''),
		'teamstats'         => array('cfg_which_database' => '', 's' => '', 'p' => '', 'tid' => ''),
		'teamstree'         => array('cfg_which_database' => '', 's' => '', 'p' => '', 'division' => ''),
		'treetonode'        => array('cfg_which_database' => '', 's' => '', 'p' => '', 'tnid' => ''),
		'predictionentry'   => array('cfg_which_database' => '', 'prediction_id' => '', 'pggroup' => '', 'pj' => '', 'r' => '', 'uid' => ''),
		'predictionresults' => array('cfg_which_database' => '', 'prediction_id' => '', 'pggroup' => '', 'pj' => '', 'r' => '', 'uid' => ''),
		'predictionranking' => array('cfg_which_database' => '', 'prediction_id' => '', 'pggroup' => '', 'pj' => '', 'r' => '', 'pggrouprank' => '', 'type' => '', 'from' => '', 'to' => ''),
		'predictionuser'    => array('cfg_which_database' => '', 'prediction_id' => '', 'pggroup' => '', 'pj' => '', 'r' => '', 'uid' => '', 'layout' => 'edit'),
		'predictionusers'   => array('cfg_which_database' => '', 'prediction_id' => '', 'pggroup' => '', 'pj' => '', 'r' => '', 'uid' => ''),
		'predictionrules'   => array('cfg_which_database' => '', 'prediction_id' => ''),

	);
    
    	public static $views4 = array(
		'about'    => array('cfg_which_database' => '', 's' => '', 'p' => '', 'Itemid' => '' ),
		'calendar' => array('cfg_which_database' => '', 's' => '', 'p' => '', 'tid' => '', 'division' => '', 'mode' => '', 'ptid' => '', 'Itemid' => ''),
		'clubinfo' => array('cfg_which_database' => '', 's' => '', 'p' => '', 'cid' => '', 'Itemid' => ''),
		'clubplan' => array('cfg_which_database' => '', 's' => '', 'cid' => '', 'p' => '', 'startdate' => '', 'enddate' => '', 'Itemid' => ''),
		'curve'    => array('cfg_which_database' => '', 's' => '', 'p' => '', 'tid1' => '', 'tid2' => '','division' => '', 'Itemid' => ''),

		'editprojectteam' => array('tmpl' => '', 'ptid' => '', 'tid' => '', 'p' => '', 'Itemid' => ''),
		'editteam'        => array('tmpl' => '', 'ptid' => '', 'tid' => '', 'p' => '', 'Itemid' => ''),
		'editperson'      => array('cfg_which_database' => '', 's' => '', 'p' => '', 'tid' => '', 'pid' => '', 'Itemid' => ''),

		'editclub'      => array('cfg_which_database' => '', 's' => '', 'p' => '', 'cid' => '', 'id' => '', 'tmpl' => '', 'Itemid' => ''),
		'editmatch'     => array('cfg_which_database' => '', 's' => '', 'p' => '', 'r' => '', 'division' => '', 'mode' => '', 'order' => '', 'layout' => '', 'matchid' => '', 'tmpl' => '', 'oldlayout' => '', 'team' => '', 'pteam' => '', 'Itemid' => ''),
		'eventsranking' => array('cfg_which_database' => '', 's' => '', 'p' => '', 'tid' => '', 'evid' => '', 'mid' => '', 'Itemid' => ''),
		'ical'          => array('cfg_which_database' => '', 's' => '', 'p' => '', 'tid' => '', 'division' => '', 'mode' => '', 'ptid' => '', 'Itemid' => ''),
		'scoresheet'    => array('cfg_which_database' => '', 'p' => '', 'mid' => ''),

		'jltournamenttree' => array('cfg_which_database' => '', 's' => '', 'p' => '', 'r' => '', 'Itemid' => ''),
		'matchreport'      => array('cfg_which_database' => '', 's' => '', 'p' => '', 'mid' => '', 'Itemid' => ''),
		'matrix'           => array('cfg_which_database' => '', 's' => '', 'p' => '', 'division' => '', 'r' => '', 'Itemid' => ''),
		'nextmatch'        => array('cfg_which_database' => '', 's' => '', 'p' => '', 'mid' => '', 'Itemid' => ''),
		'player'           => array('cfg_which_database' => '', 's' => '', 'p' => '', 'tid' => '', 'pid' => '', 'Itemid' => ''),

		'playground' => array('cfg_which_database' => '', 's' => '', 'p' => '', 'pgid' => '', 'Itemid' => ''),
        
        'leaguechampionoverview'    => array('cfg_which_database' => '', 'l' => '', 's' => '', 'p' => '', 'Itemid' => ''),

		'ranking'           => array('cfg_which_database' => '', 's' => '', 'p' => '', 'type' => '', 'r' => '', 'from' => '', 'to' => '', 'division' => '', 'Itemid' => ''),
		'rankingalltime'    => array('cfg_which_database' => '', 'l' => '', 'points' => '', 'type' => '', 'order' => '', 'dir' => '', 's' => '', 'p' => '', 'Itemid' => ''),
		'referee'           => array('cfg_which_database' => '', 's' => '', 'p' => '', 'pid' => '', 'Itemid' => ''),
		'referees'          => array('cfg_which_database' => '', 's' => '', 'p' => '', 'division' => '', 'r' => '', 'Itemid' => ''),
		'results'           => array('cfg_which_database' => '', 's' => '', 'p' => '', 'r' => '', 'division' => '', 'mode' => '', 'order' => '', 'layout' => '', 'Itemid' => ''),
		'resultsranking'    => array('cfg_which_database' => '', 's' => '', 'p' => '', 'r' => '', 'mode' => '', 'order' => '', 'layout' => '', 'division' => '', 'Itemid' => ''),
		'rivals'            => array('cfg_which_database' => '', 's' => '', 'p' => '', 'tid' => '', 'Itemid' => ''),
		'roster'            => array('cfg_which_database' => '', 's' => '', 'p' => '', 'tid' => '', 'ptid' => '', 'Itemid' => ''),
        'rosteralltime'     => array('cfg_which_database' => '', 's' => '', 'p' => '', 'tid' => '', 'start' => '', 'Itemid' => ''),
        
		'staff'             => array('cfg_which_database' => '', 's' => '', 'p' => '', 'tid' => '', 'pid' => '', 'Itemid' => ''),
		'stats'             => array('cfg_which_database' => '', 's' => '', 'p' => '', 'Itemid' => ''),
		'statsranking'      => array('cfg_which_database' => '', 's' => '', 'p' => '', 'division' => '', 'tid' => '', 'sid' => '', 'order' => '', 'Itemid' => ''),
		'teaminfo'          => array('cfg_which_database' => '', 's' => '', 'p' => '', 'tid' => '', 'ptid' => '', 'Itemid' => ''),
		'teamplan'          => array('cfg_which_database' => '', 's' => '', 'p' => '', 'tid' => '', 'division' => '', 'mode' => '', 'ptid' => '', 'Itemid' => ''),
		'teams'             => array('cfg_which_database' => '', 's' => '', 'p' => '', 'division' => '', 'Itemid' => ''),
		'teamstats'         => array('cfg_which_database' => '', 's' => '', 'p' => '', 'tid' => '', 'Itemid' => ''),
		'teamstree'         => array('cfg_which_database' => '', 's' => '', 'p' => '', 'division' => '', 'Itemid' => ''),
		'treetonode'        => array('cfg_which_database' => '', 's' => '', 'p' => '', 'tnid' => '', 'Itemid' => ''),
		'predictionentry'   => array('cfg_which_database' => '', 'prediction_id' => '', 'pggroup' => '', 'pj' => '', 'r' => '', 'uid' => '', 'Itemid' => ''),
		'predictionresults' => array('cfg_which_database' => '', 'prediction_id' => '', 'pggroup' => '', 'pj' => '', 'r' => '', 'uid' => '', 'Itemid' => ''),
		'predictionranking' => array('cfg_which_database' => '', 'prediction_id' => '', 'pggroup' => '', 'pj' => '', 'r' => '', 'pggrouprank' => '', 'type' => '', 'from' => '', 'to' => '', 'Itemid' => ''),
		'predictionuser'    => array('cfg_which_database' => '', 'prediction_id' => '', 'pggroup' => '', 'pj' => '', 'r' => '', 'uid' => '', 'layout' => 'edit', 'Itemid' => ''),
		'predictionusers'   => array('cfg_which_database' => '', 'prediction_id' => '', 'pggroup' => '', 'pj' => '', 'r' => '', 'uid' => '', 'Itemid' => ''),
		'predictionrules'   => array('cfg_which_database' => '', 'prediction_id' => ''),

	);


	/**
	 * sportsmanagementHelperRoute::getSportsmanagementRoute()
	 *
	 * @param   string  $view
	 * @param   mixed   $parameter
	 * @param   string  $task
	 *
	 * @return
	 */
	public static function getSportsmanagementRoute($view = '', $parameter = array(), $task = '')
	{
		$app    = Factory::getApplication();
		$params = array("option" => self::$option,
		                "view"   => $view);

		foreach ($parameter as $key => $value)
		{
			$params[$key] = $value;
		}

		switch ($task)
		{
			//    case 'person.edit':
			//    $params["layout"] = 'edit';
			//    $params["view"] = 'person';
			//	$params["id"] = $params['pid'];
			//    break;
		}

		$query = self::buildQuery($params);
		$link  = Route::_('index.php?' . $query, false);

		return $link;
	}

	/**
	 * sportsmanagementHelperRoute::buildQuery()
	 *
	 * @param   mixed  $parts
	 *
	 * @return string
	 */
	public static function buildQuery($parts)
	{
if ( !array_key_exists('Itemid', $parts) ) {
    $params = ComponentHelper::getParams('com_sportsmanagement');
				$parts['Itemid'] = intval($params->get('default_itemid'));
}

//Factory::getApplication()->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' parts' .'<pre>'.print_r($parts,true).'</pre>'    ), '');		
		
$parts['Itemid'] = $parts['Itemid'] < 0 ? $parts['Itemid'] : Factory::getApplication()->getMenu()->getActive()->id;
		
//		if ($item = self::_findItem($parts))
//		{
//			$parts['Itemid'] = $item->id;
//		}
//		else
//		{
//			$params = ComponentHelper::getParams('com_sportsmanagement');
//			$parts['Itemid'] = intval($params->get('default_itemid'));
//		}

		return Uri::buildQuery($parts);
	}

	/**
	 * sportsmanagementHelperRoute::_findItem()
	 *
	 * @param   mixed  $query
	 *
	 * @return
	 */
	public static function _findItem($query)
	{
		$component = ComponentHelper::getComponent('com_sportsmanagement');
		$app       = Factory::getApplication();
		$menus     = $app->getMenu();
		$items     = $menus->getItems('component', $component->id);
		$user      = Factory::getUser();
		$access    = (int) $user->get('aid');

		if ($items)
		{
			foreach ($items as $item)
			{
				if ((@$item->query['view'] == $query['view']) && ($item->published == 1) && ($item->access <= $access))
				{
					switch ($query['view'])
					{
						case 'teaminfo':
						case 'roster':
						case 'teamplan':
						case 'teamstats':
							if ((int) @$item->query['p'] == (int) $query['p'] && (int) @$item->query['tid'] == (int) $query['tid'])
							{
								return $item;
							}
							break;
						case 'clubinfo':
						case 'clubplan':
							if ((int) @$item->query['p'] == (int) $query['p'] && (int) @$item->query['cid'] == (int) $query['cid'])
							{
								return $item;
							}
							break;
						case 'playground':
							if ((int) @$item->query['p'] == (int) $query['p'] && (int) @$item->query['pgid'] == (int) $query['pgid'])
							{
								return $item;
							}
							break;
						case 'ranking':
						case 'results':
						case 'resultsranking':
						case 'matrix':
						case 'resultsmatrix':
						case 'stats':
							if ((int) @$item->query['p'] == (int) $query['p'])
							{
								return $item;
							}
							break;
						case 'statsranking':
							if ((int) @$item->query['p'] == (int) $query['p'])
							{
								return $item;
							}
							break;
						case 'player':
						case 'staff':
							if ((int) @$item->query['p'] == (int) $query['p']
								&& (int) @$item->query['tid'] == (int) $query['tid']
								&& (int) @$item->query['pid'] == (int) $query['pid']
							)
							{
								return $item;
							}
							break;
						case 'referee':
							if ((int) @$item->query['p'] == (int) $query['p']
								&& (int) @$item->query['pid'] == (int) $query['pid']
							)
							{
								return $item;
							}
							break;
						case 'tree':
							if ((int) @$item->query['p'] == (int) $query['p'] && (int) @$item->query['did'] == (int) $query['did'])
							{
								return $item;
							}
							break;
					}
				}
			}
		}

		return false;
	}

	/**
	 * sportsmanagementHelperRoute::getAllProjectsRoute()
	 *
	 * @param   mixed  $country
	 * @param   mixed  $league_id
	 *
	 * @return
	 */
	public static function getAllProjectsRoute($country, $league_id)
	{
		$params = array("option"                => "com_sportsmanagement",
		                "view"                  => "allprojects",
		                "filter_search_nation"  => $country,
		                "filter_search_leagues" => $league_id);

		$query = self::buildQuery($params);
		$link  = Route::_('index.php?' . $query, false);

		return $link;
	}

	/**
	 * sportsmanagementHelperRoute::getKunenaRoute()
	 *
	 * @param   mixed  $sb_catid
	 *
	 * @return
	 */
	public static function getKunenaRoute($sb_catid)
	{
		$params = array("option" => "com_kunena",
		                "view"   => "topic",
		                "catid"  => $sb_catid);

		$query = self::buildQuery($params);
		$link  = Route::_('index.php?' . $query, false);

		return $link;
	}

	/**
	 * sportsmanagementHelperRoute::getClubInfoRoute()
	 *
	 * @param   mixed  $projectid
	 * @param   mixed  $clubid
	 * @param   mixed  $task
	 *
	 * @return
	 */
	public static function getClubInfoRoute($projectid, $clubid, $task = null, $cfg_which_database = 0, $s = 0)
	{
		$app = Factory::getApplication();

		$params = array("option" => "com_sportsmanagement",
		                "view"   => "clubinfo");

		$params["cfg_which_database"] = $cfg_which_database;
		$params["s"]                  = $s;
		$params["p"]                  = $projectid;
		$params["cid"]                = $clubid;

		if (!is_null($task))
		{
			if ($task == 'club.edit')
			{
				$params["view"] = 'editclub';
				$params["id"]   = $clubid;
			}

			$query = self::buildQuery($params);

			// Diddipoeler
			// nicht im backend, sondern im frontend
			$link = Route::_("index.php?" . $query . '&tmpl=component', false);
		}
		else
		{
			$query = self::buildQuery($params);
			$link  = Route::_("index.php?" . $query, false);
		}

		self::sportsmanagementBuildRoute($params);

		return $link;
	}

	/**
	 * sportsmanagementHelperRoute::sportsmanagementBuildRoute()
	 *
	 * @param   mixed  $query
	 *
	 * @return
	 */
	public static function sportsmanagementBuildRoute(&$query)
	{
		$app      = Factory::getApplication();
		$segments = array();

		if (isset($query['view']))
		{
			$segments[] = $query['view'];
			unset($query['view']);
		}

		if (isset($query['p']))
		{
			$segments[] = $query['p'];
			unset($query['p']);
		}

		if (isset($query['cid']))
		{
			$segments[] = $query['cid'];
			unset($query['cid']);
		}

		return $segments;
	}

	/**
	 * sportsmanagementHelperRoute::getTournamentRoute()
	 *
	 * @param   mixed  $projectid
	 * @param   mixed  $round
	 *
	 * @return
	 */
	public static function getTournamentRoute($projectid, $round = 0, $cfg_which_database = 0, $s = 0)
	{
		$params = array("option" => "com_sportsmanagement",
		                "view"   => "jltournamenttree");

		$params["s"]                  = $s;
		$params["cfg_which_database"] = $cfg_which_database;
		$params["p"]                  = $projectid;
		$params["r"]                  = $round;

		// If ( ! is_null( $cfg_which_database) ) { $params["cfg_which_database"] = $cfg_which_database; }
		$query = self::buildQuery($params);
		$link  = Route::_('index.php?' . $query, false);

		return $link;
	}

	/**
	 * sportsmanagementHelperRoute::getPlayersRouteAllTime()
	 *
	 * @param   mixed  $projectid
	 * @param   mixed  $teamid
	 * @param   mixed  $task
	 *
	 * @return
	 */
	public static function getPlayersRouteAllTime($projectid, $teamid, $task = null, $cfg_which_database = 0, $s = 0)
	{
		$params = array("option" => "com_sportsmanagement",
		                "view"   => "rosteralltime",
		                "p"      => $projectid,
		                "tid"    => $teamid);

		if (!is_null($cfg_which_database))
		{
			$params["cfg_which_database"] = $cfg_which_database;
		}

		$query = self::buildQuery($params);
		$link  = Route::_('index.php?' . $query, false);

		return $link;
	}

	/**
	 * sportsmanagementHelperRoute::getDivisionsRoute()
	 *
	 * @param   mixed  $projectid
	 * @param   mixed  $divisionid
	 * @param   mixed  $task
	 *
	 * @return
	 */
	public static function getDivisionsRoute($projectid, $divisionid, $task = null, $cfg_which_database = 0, $s = 0)
	{
		$params = array("option" => "com_sportsmanagement",
		                "view"   => "treeone",
		                "p"      => $projectid,
		                "did"    => $divisionid);

		if (!is_null($cfg_which_database))
		{
			$params["cfg_which_database"] = $cfg_which_database;
		}

		$query = self::buildQuery($params);
		$link  = Route::_('index.php?' . $query, false);

		return $link;
	}

	/**
	 * sportsmanagementHelperRoute::getFavPlayersRoute()
	 *
	 * @param   mixed  $projectid
	 *
	 * @return
	 */
	public static function getFavPlayersRoute($projectid, $cfg_which_database = 0, $s = 0)
	{
		$params = array("option" => "com_sportsmanagement",
		                "view"   => "players",
		                "task"   => "favplayers",
		                "p"      => $projectid);

		if (!is_null($cfg_which_database))
		{
			$params["cfg_which_database"] = $cfg_which_database;
		}

		$query = self::buildQuery($params);
		$link  = Route::_('index.php?' . $query, false);

		return $link;
	}

	/**
	 * sportsmanagementHelperRoute::getStatsChartDataRoute()
	 *
	 * @param   mixed    $projectid
	 * @param   integer  $division
	 * @param   int      $cfg_which_database
	 * @param   int      $s
	 *
	 * @return string
	 */
	public static function getStatsChartDataRoute($projectid, $division = 0, $cfg_which_database = 0, $s = 0)
	{
		$params = array("option" => "com_sportsmanagement",
		                "view"   => "stats",
		                "layout" => "chartdata",
		                "p"      => $projectid);

		$params["division"] = $division;

		if (!is_null($cfg_which_database))
		{
			$params["cfg_which_database"] = $cfg_which_database;
		}

		$query = self::buildQuery($params);
		$link  = Route::_('index.php?' . $query, false);

		return $link;
	}

	/**
	 * sportsmanagementHelperRoute::getTeamStatsChartDataRoute()
	 *
	 * @param   mixed  $projectid
	 * @param   mixed  $teamid
	 * @param   int    $cfg_which_database
	 * @param   int    $s
	 *
	 * @return string
	 */
	public static function getTeamStatsChartDataRoute($projectid, $teamid, $cfg_which_database = 0, $s = 0)
	{
		$params = array("option" => "com_sportsmanagement",
		                "view"   => "teamstats",
		                "layout" => "chartdata",
		                "p"      => $projectid,
		                "tid"    => $teamid);

		if (!is_null($cfg_which_database))
		{
			$params["cfg_which_database"] = $cfg_which_database;
		}

		$query = self::buildQuery($params);
		$link  = Route::_('index.php?' . $query, false);

		return $link;
	}

	/**
	 * sportsmanagementHelperRoute::getBracketsRoute()
	 *
	 * @param   mixed  $projectid
	 * @param   int    $cfg_which_database
	 * @param   int    $s
	 *
	 * @return string
	 */
	public static function getBracketsRoute($projectid, $cfg_which_database = 0, $s = 0)
	{
		$params = array("option" => "com_sportsmanagement",
		                "view"   => "treetonode",
		                "p"      => $projectid);

		if (!is_null($cfg_which_database))
		{
			$params["cfg_which_database"] = $cfg_which_database;
		}

		$query = self::buildQuery($params);
		$link  = Route::_('index.php?' . $query, false);

		return $link;
	}

	/**
	 * sportsmanagementHelperRoute::getEditLineupRoute()
	 *
	 * @param   mixed    $projectid
	 * @param   mixed    $matchid
	 * @param   string   $layout
	 * @param   integer  $team
	 * @param   integer  $projectTeam
	 * @param   string   $match_date
	 * @param   integer  $cfg_which_database
	 * @param   integer  $s
	 * @param   integer  $r
	 * @param   integer  $division
	 * @param   string   $oldlayout
	 *
	 * @return
	 */
	public static function getEditLineupRoute($projectid, $matchid, $layout = 'editlineup', $team = 0, $projectTeam = 0, $match_date = '0000-00-00', $cfg_which_database = 0, $s = 0, $r = 0, $division = 0, $oldlayout = '')
	{

		$params = array("option"             => "com_sportsmanagement",
		                "view"               => "editmatch",
		                "cfg_which_database" => $cfg_which_database,
		                "s"                  => $s,
		                "p"                  => $projectid,
		                "r"                  => $r,
		                "division"           => $division,
		                "mode"               => 0,
		                "order"              => 0,
		                "layout"             => $layout,
		                "matchid"            => $matchid,
		                "tmpl"               => "component",
		                "oldlayout"          => $oldlayout,
		                "team"               => $team,
		                "pteam"              => $projectTeam,
		                "match_date"         => $match_date
		);

		$query = self::buildQuery($params);
		$link  = Route::_('index.php?' . $query, false);

		return $link;
	}

	/**
	 * sportsmanagementHelperRoute::getContactRoute()
	 *
	 * @param   mixed  $contactid
	 * @param   int    $cfg_which_database
	 * @param   int    $s
	 *
	 * @return string
	 */
	public static function getContactRoute($contactid, $cfg_which_database = 0, $s = 0)
	{
		/*
         Old Route to JOOMLA built in contact id
         $query = self::buildQuery(
         array(
         "option" => "com_contact",
         "task" => "view",
         "contact_id" => $contactid ) );
         */
		// New Route to JOOMLA built in contact id
		$params = array("option" => "com_contact",
		                "view"   => "contact",
		                "id"     => $contactid);

		if (!is_null($cfg_which_database))
		{
			$params["cfg_which_database"] = $cfg_which_database;
		}

		$query = self::buildQuery($params);
		$link  = Route::_('index.php?' . $query, false);

		return $link;
	}

	/**
	 * sportsmanagementHelperRoute::getUserProfileRouteCBE()
	 *
	 * @param   mixed  $u_id
	 * @param   mixed  $p_id
	 * @param   mixed  $pl_id
	 * @param   int    $cfg_which_database
	 * @param   int    $s
	 *
	 * @return string
	 */
	public static function getUserProfileRouteCBE($u_id, $p_id, $pl_id, $cfg_which_database = 0, $s = 0)
	{

		$params = array("option" => "com_cbe",
		                "view"   => "userProfile",
		                "user"   => $u_id,
		                "jlp"    => $p_id,
		                "jlpid"  => $pl_id);

		if (!is_null($cfg_which_database))
		{
			$params["cfg_which_database"] = $cfg_which_database;
		}

		$query = self::buildQuery($params);
		$link  = Route::_('index.php?' . $query, false);

		return $link;
	}

	/**
	 * sportsmanagementHelperRoute::getUserProfileRoute()
	 *
	 * @param   mixed  $userid
	 * @param   int    $cfg_which_database
	 * @param   int    $s
	 *
	 * @return string
	 */
	public static function getUserProfileRoute($userid, $cfg_which_database = 0, $s = 0)
	{
		$params = array("option" => "com_comprofiler",
		                "task"   => "userProfile",
		                "user"   => $userid);

		if (!is_null($cfg_which_database))
		{
			$params["cfg_which_database"] = $cfg_which_database;
		}

		$query = self::buildQuery($params);
		$link  = Route::_('index.php?' . $query, false);

		return $link;
	}
}
