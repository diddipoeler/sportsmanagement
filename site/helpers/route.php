<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    5.6.0
 * @package    Sportsmanagement
 * @subpackage helpers
 * @file       route.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Component\ComponentHelper;

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
    /**
     * Resolve the active Joomla site application for legacy route selection.
     * Retrieves the site application via Joomla's public dependency-injection container and validates the service before legacy menu lookup code uses it.
     *
     * @return SiteApplication Active Joomla frontend application.
     */
    private static function siteApplication(): SiteApplication
    {
        $application = Factory::getContainer()->get(SiteApplication::class);

        if (!$application instanceof SiteApplication) {
            throw new \RuntimeException('SportsManagement routing requires the Joomla site application.', 500);
        }

        return $application;
    }

	static $season = 0;

	static $view = 0;

	static $option = 'com_sportsmanagement';

	static $cfg_which_database = 0;

	public static $views = array(
		'about'    => array('cfg_which_database' => '', 's' => '', 'p' => '' ),
        'uefawertung'    => array('cfg_which_database' => '', 's' => '', 'p' => '', 'coefficientyear' => '' ),
		'calendar' => array('cfg_which_database' => '', 's' => '', 'p' => '', 'tid' => '', 'division' => '', 'mode' => '', 'ptid' => ''),
		'clubinfo' => array('cfg_which_database' => '', 's' => '', 'p' => '', 'cid' => ''),
		'clubplan' => array('cfg_which_database' => '', 's' => '', 'cid' => '', 'p' => '', 'startdate' => '', 'enddate' => ''  ),
		'curve'    => array('cfg_which_database' => '', 's' => '', 'p' => '', 'tid1' => '', 'tid2' => '','division' => ''),

		'editprojectteam' => array('tmpl' => '', 'ptid' => '', 'tid' => '', 'p' => ''),
		'editteam'        => array('tmpl' => '', 'ptid' => '', 'tid' => '', 'p' => ''),
		'editperson'      => array('cfg_which_database' => '', 's' => '', 'p' => '', 'tid' => '', 'pid' => ''),

		'editclub'      => array('cfg_which_database' => '', 's' => '', 'p' => '', 'cid' => '', 'id' => '', 'tmpl' => ''),
		'editmatch'     => array('cfg_which_database' => '', 's' => '', 'p' => '', 'r' => '', 'division' => '', 'mode' => '', 'order' => '', 'layout' => '', 'matchid' => '', 'tmpl' => '', 'oldlayout' => '', 'team' => '', 'pteam' => ''),
		'eventsranking' => array('cfg_which_database' => '', 's' => '', 'p' => '', 'tid' => '', 'evid' => '', 'mid' => '', 'division' => ''),
		'ical'          => array('cfg_which_database' => '', 's' => '', 'p' => '', 'tid' => '', 'division' => '', 'mode' => '', 'ptid' => ''),
		'scoresheet'    => array('cfg_which_database' => '', 'p' => '', 'mid' => ''),

		'jltournamenttree' => array('cfg_which_database' => '', 's' => '', 'p' => '', 'r' => ''),
        'tournamentbracket' => array('cfg_which_database' => '', 's' => '', 'p' => '', 'r' => ''),

		'matchreport'      => array('cfg_which_database' => '', 's' => '', 'p' => '', 'mid' => ''),
		'matrix'           => array('cfg_which_database' => '', 's' => '', 'p' => '', 'division' => '', 'r' => ''),
        'rankingmatrix'           => array('cfg_which_database' => '', 's' => '', 'p' => '', 'division' => '', 'r' => ''),
		'nextmatch'        => array('cfg_which_database' => '', 's' => '', 'p' => '', 'mid' => ''),
		'player'           => array('cfg_which_database' => '', 's' => '', 'p' => '', 'tid' => '', 'pid' => ''),

		'playground' => array('cfg_which_database' => '', 's' => '', 'p' => '', 'pgid' => ''),
        
        'leaguechampionoverview'    => array('cfg_which_database' => '', 'l' => '', 's' => '', 'p' => ''),

		'ranking'           => array('cfg_which_database' => '', 's' => '', 'p' => '', 'type' => '', 'r' => '', 'from' => '', 'to' => '', 'division' => ''),
		'rankingalltime'    => array('cfg_which_database' => '', 'l' => '', 'points' => '', 'type' => '', 'order' => '', 'dir' => '', 's' => '', 'p' => ''),
		'referee'           => array('cfg_which_database' => '', 's' => '', 'p' => '', 'pid' => ''),
		'referees'          => array('cfg_which_database' => '', 's' => '', 'p' => '', 'division' => '', 'r' => ''),
		'results'           => array('cfg_which_database' => '', 's' => '', 'p' => '', 'r' => '', 'division' => '', 'mode' => '', 'order' => '', 'layout' => ''),
		'allprojectrounds'           => array('cfg_which_database' => '', 's' => '', 'p' => '', 'r' => '', 'division' => '', 'mode' => '', 'order' => '', 'layout' => ''),
		
		
		'resultsranking'    => array('cfg_which_database' => '', 's' => '', 'p' => '', 'r' => '', 'mode' => '', 'order' => '', 'layout' => '', 'division' => ''),
        'resultsmatrix'    => array('cfg_which_database' => '', 's' => '', 'p' => '', 'r' => '', 'mode' => '', 'order' => '', 'layout' => '', 'division' => ''),
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
        'uefawertung'    => array('cfg_which_database' => '', 's' => '', 'p' => '', 'coefficientyear' => '', 'Itemid' => '' ),
		'calendar' => array('cfg_which_database' => '', 's' => '', 'p' => '', 'tid' => '', 'division' => '', 'mode' => '', 'ptid' => '', 'Itemid' => ''),
		'clubinfo' => array('cfg_which_database' => '', 's' => '', 'p' => '', 'cid' => '', 'Itemid' => ''),
		'clubplan' => array('cfg_which_database' => '', 's' => '', 'cid' => '', 'p' => '', 'startdate' => '', 'enddate' => '', 'Itemid' => ''),
		'curve'    => array('cfg_which_database' => '', 's' => '', 'p' => '', 'tid1' => '', 'tid2' => '','division' => '', 'Itemid' => ''),

		'editprojectteam' => array('tmpl' => '', 'ptid' => '', 'tid' => '', 'p' => '', 'Itemid' => ''),
		'editteam'        => array('tmpl' => '', 'ptid' => '', 'tid' => '', 'p' => '', 'Itemid' => ''),
		'editperson'      => array('cfg_which_database' => '', 's' => '', 'p' => '', 'tid' => '', 'pid' => '', 'Itemid' => ''),

		'editclub'      => array('cfg_which_database' => '', 's' => '', 'p' => '', 'cid' => '', 'id' => '', 'tmpl' => '', 'Itemid' => ''),
		'editmatch'     => array('cfg_which_database' => '', 's' => '', 'p' => '', 'r' => '', 'division' => '', 'mode' => '', 'order' => '', 'layout' => '', 'matchid' => '', 'tmpl' => '', 'oldlayout' => '', 'team' => '', 'pteam' => '', 'Itemid' => ''),
		'eventsranking' => array('cfg_which_database' => '', 's' => '', 'p' => '', 'tid' => '', 'evid' => '', 'mid' => '','division' => '', 'Itemid' => ''),
		'ical'          => array('cfg_which_database' => '', 's' => '', 'p' => '', 'tid' => '', 'division' => '', 'mode' => '', 'ptid' => '', 'Itemid' => ''),
		'scoresheet'    => array('cfg_which_database' => '', 'p' => '', 'mid' => ''),

		'jltournamenttree' => array('cfg_which_database' => '', 's' => '', 'p' => '', 'r' => '', 'Itemid' => ''),
        'tournamentbracket' => array('cfg_which_database' => '', 's' => '', 'p' => '', 'r' => '', 'Itemid' => ''),
        
		'matchreport'      => array('cfg_which_database' => '', 's' => '', 'p' => '', 'mid' => '', 'Itemid' => ''),
		'matrix'           => array('cfg_which_database' => '', 's' => '', 'p' => '', 'division' => '', 'r' => '', 'Itemid' => ''),
		'rankingmatrix'           => array('cfg_which_database' => '', 's' => '', 'p' => '', 'division' => '', 'r' => '', 'Itemid' => ''),
		'nextmatch'        => array('cfg_which_database' => '', 's' => '', 'p' => '', 'mid' => '', 'Itemid' => ''),
		'player'           => array('cfg_which_database' => '', 's' => '', 'p' => '', 'tid' => '', 'pid' => '', 'Itemid' => ''),

		'playground' => array('cfg_which_database' => '', 's' => '', 'p' => '', 'pgid' => '', 'Itemid' => ''),
        
        'leaguechampionoverview'    => array('cfg_which_database' => '', 'l' => '', 's' => '', 'p' => '', 'Itemid' => ''),

		'ranking'           => array('cfg_which_database' => '', 's' => '', 'p' => '', 'type' => '', 'r' => '', 'from' => '', 'to' => '', 'division' => '', 'Itemid' => ''),
		'rankingalltime'    => array('cfg_which_database' => '', 'l' => '', 'points' => '', 'type' => '', 'order' => '', 'dir' => '', 's' => '', 'p' => '', 'Itemid' => ''),
		'referee'           => array('cfg_which_database' => '', 's' => '', 'p' => '', 'pid' => '', 'Itemid' => ''),
		'referees'          => array('cfg_which_database' => '', 's' => '', 'p' => '', 'division' => '', 'r' => '', 'Itemid' => ''),
		'results'           => array('cfg_which_database' => '', 's' => '', 'p' => '', 'r' => '', 'division' => '', 'mode' => '', 'order' => '', 'layout' => '', 'Itemid' => ''),
		'allprojectrounds'           => array('cfg_which_database' => '', 's' => '', 'p' => '', 'r' => '', 'division' => '', 'mode' => '', 'order' => '', 'layout' => '', 'Itemid' => ''),
		'resultsranking'    => array('cfg_which_database' => '', 's' => '', 'p' => '', 'r' => '', 'mode' => '', 'order' => '', 'layout' => '', 'division' => '', 'Itemid' => ''),
        'resultsmatrix'    => array('cfg_which_database' => '', 's' => '', 'p' => '', 'r' => '', 'mode' => '', 'order' => '', 'layout' => '', 'division' => '', 'Itemid' => ''),
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
	 * Build a SportsManagement URL for a requested frontend view.
	 * Combines the provided view with legacy route parameters and delegates query-string building and final routing to Joomla.
	 *
	 * @param string $view Destination component view.
	 * @param array $parameter Route variables and view identifiers.
	 * @param string $task Legacy task argument retained for template compatibility.
	 * @return string Joomla-routed component URL.
	 */
	public static function getSportsmanagementRoute($view = '', $parameter = array(), $task = '')
	{
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
	 * Build a query string with a suitable SportsManagement menu item.
	 * Keeps explicit Itemids, otherwise searches published accessible component menus and falls back to the configured menu ID; it never assigns the component's menu to unrelated extensions.
	 *
	 * @param array|object $parts Initial URL query variables.
	 * @return string Encoded query string suitable for Joomla Route.
	 */
	public static function buildQuery($parts)
	{
		$parts = (array) $parts;

		// Only SportsManagement URLs may receive a SportsManagement menu Itemid.
		// Explicit Itemids are preserved; otherwise use the best matching menu
		// entry and finally the configured component fallback.
		if ((string) ($parts['option'] ?? self::$option) === self::$option)
		{
			$itemId = (int) ($parts['Itemid'] ?? 0);

			if ($itemId <= 0 && ($item = self::_findItem($parts)))
			{
				$itemId = (int) $item->id;
			}

			if ($itemId <= 0)
			{
				$params = ComponentHelper::getParams(self::$option);
				$itemId = (int) $params->get('default_itemid', 0);
			}

			if ($itemId > 0)
			{
				$parts['Itemid'] = $itemId;
			}
			else
			{
				unset($parts['Itemid']);
			}
		}

		return Uri::buildQuery($parts);
	}

	/**
	 * Find an accessible published menu item matching the requested component view.
	 * Checks view and project/team/club/person identifiers against Joomla's site menu and current user's authorised view levels before returning a compatible menu record.
	 *
	 * @param array|object $query Requested component route variables.
	 * @return object|false Matching Joomla menu item, or false when none qualifies.
	 */
	public static function _findItem($query)
	{
		$query     = (array) $query;
		$component = ComponentHelper::getComponent(self::$option);
		/** @var SiteApplication $app */
		$app       = self::siteApplication();
		$menus     = $app->getMenu();
		$items     = $menus->getItems('component', self::$option);

		if (!$items)
		{
			$items = $menus->getItems('component_id', (int) $component->id);
		}

		$identity = method_exists($app, 'getIdentity') ? $app->getIdentity() : null;
		$authorisedLevels = $identity && method_exists($identity, 'getAuthorisedViewLevels')
			? array_map('intval', (array) $identity->getAuthorisedViewLevels())
			: array();

		if ($items && !empty($query['view']))
		{
			foreach ($items as $item)
			{
				if (
					(@$item->query['view'] == $query['view'])
					&& ((int) ($item->published ?? 0) === 1)
					&& (!$authorisedLevels || in_array((int) ($item->access ?? 0), $authorisedLevels, true))
				)
				{
					switch ($query['view'])
					{
						case 'teaminfo':
						case 'roster':
						case 'teamplan':
						case 'teamstats':
							if ((int) @$item->query['p'] == (int) ($query['p'] ?? 0) && (int) @$item->query['tid'] == (int) ($query['tid'] ?? 0))
							{
								return $item;
							}
							break;
						case 'clubinfo':
						case 'clubplan':
							if ((int) @$item->query['p'] == (int) ($query['p'] ?? 0) && (int) @$item->query['cid'] == (int) ($query['cid'] ?? 0))
							{
								return $item;
							}
							break;
						case 'playground':
							if ((int) @$item->query['p'] == (int) ($query['p'] ?? 0) && (int) @$item->query['pgid'] == (int) ($query['pgid'] ?? 0))
							{
								return $item;
							}
							break;
						case 'ranking':
						case 'results':
						case 'resultsranking':
						case 'matrix':
                        case 'rankingmatrix':
						case 'resultsmatrix':
						case 'stats':
						case 'statsranking':
							if ((int) @$item->query['p'] == (int) ($query['p'] ?? 0))
							{
								return $item;
							}
							break;
						case 'player':
						case 'staff':
							if ((int) @$item->query['p'] == (int) ($query['p'] ?? 0)
								&& (int) @$item->query['tid'] == (int) ($query['tid'] ?? 0)
								&& (int) @$item->query['pid'] == (int) ($query['pid'] ?? 0)
							)
							{
								return $item;
							}
							break;
						case 'referee':
							if ((int) @$item->query['p'] == (int) ($query['p'] ?? 0)
								&& (int) @$item->query['pid'] == (int) ($query['pid'] ?? 0)
							)
							{
								return $item;
							}
							break;
						case 'tree':
							if ((int) @$item->query['p'] == (int) ($query['p'] ?? 0) && (int) @$item->query['did'] == (int) ($query['did'] ?? 0))
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
	 * Build the SportsManagement overview link for projects in a country and league.
	 * Passes the country and league criteria to the allprojects view while preserving Joomla's normal menu routing.
	 *
	 * @param string $country Country selector.
	 * @param int|string $league_id League identifier.
	 * @return string Routed all-projects overview URL.
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
	 * Generate a Joomla Kunena forum topic link for a legacy category identifier.
	 * Keeps existing SportsManagement forum links compatible while routing through the com_kunena extension instead of the sports component.
	 *
	 * @param int|string $sb_catid Legacy Kunena topic or category identifier.
	 * @return string Routed Kunena topic URL.
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
	 * Build a link to a club's information page for the current project.
	 * Includes club, project, season and database context and keeps the historical task argument for older template overrides.
	 *
	 * @param int|string $projectid Project identifier.
	 * @param int|string $clubid Club identifier.
	 * @param mixed $task Unused historical task argument.
	 * @param int $cfg_which_database Database selector.
	 * @param int|string $s Season identifier.
	 * @return string Routed club information URL.
	 */
	public static function getClubInfoRoute($projectid, $clubid, $task = null, $cfg_which_database = 0, $s = 0)
	{
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
	 * Translate old SportsManagement query parameters into route segments.
	 * Consumes recognised view, project and club variables from the supplied query array and returns ordered segments for legacy callers.
	 *
	 * @param array $query Query variables consumed by reference.
	 * @return array<int,string|int> Ordered route path segments.
	 */
	public static function sportsmanagementBuildRoute(&$query)
	{
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
	 * Link to the legacy tournament tree for a selected project and round.
	 * Constructs the jltournamenttree view URL with round, season and database selection before passing it through the Joomla router.
	 *
	 * @param int|string $projectid Project identifier.
	 * @param int|string $round Optional round identifier.
	 * @param int $cfg_which_database Database selector.
	 * @param int|string $s Optional season identifier.
	 * @return string Routed tournament tree URL.
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
     * Build a frontend link to a project's tournament bracket view.
     * Preserves the legacy arguments for project, round, season and database selector while letting Joomla's router resolve the final URL.
     *
     * @param int|string $projectid Project identifier or ID:alias value.
     * @param int|string $round Optional tournament round.
     * @param int $cfg_which_database SportsManagement database selector.
     * @param int|string $s Optional season identifier.
     * @return string Joomla-routed tournament bracket URL.
     */
    public static function gettournamentbracket($projectid, $round = 0, $cfg_which_database = 0, $s = 0)
	{
		$params = array("option" => "com_sportsmanagement",
		                "view"   => "tournamentbracket");

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
	 * Open the all-time team roster for a specific project and team.
	 * Maintains the rosteralltime view contract for historical templates, including the optional database selector.
	 *
	 * @param int|string $projectid Project identifier.
	 * @param int|string $teamid Team identifier.
	 * @param mixed $task Historical optional task parameter.
	 * @param int|null $cfg_which_database Optional database selector.
	 * @param int|string $s Legacy season argument.
	 * @return string Routed all-time roster URL.
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
	 * Build the division tree URL for the selected project and division.
	 * Uses the historical treeone view and division ID parameter, then passes the query through Joomla's route builder.
	 *
	 * @param int|string $projectid Project identifier.
	 * @param int|string $divisionid Division identifier.
	 * @param mixed $task Historical task argument.
	 * @param int|null $cfg_which_database Optional database selector.
	 * @param int|string $s Legacy season argument.
	 * @return string Routed division-tree URL.
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
	 * Link to the favourite-players listing for the selected project.
	 * Uses the players view with favplayers task and includes the optional database selector for older SportsManagement layouts.
	 *
	 * @param int|string $projectid Project identifier.
	 * @param int|null $cfg_which_database Optional database selector.
	 * @param int|string $s Legacy season argument.
	 * @return string Routed favourite-players URL.
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
	 * Build the raw statistics chart-data route for a project or division.
	 * Selects the stats view's chartdata layout and includes the division, project and database selector.
	 *
	 * @param int|string $projectid Project identifier.
	 * @param int|string $division Optional division identifier.
	 * @param int|null $cfg_which_database Optional database selector.
	 * @param int|string $s Legacy season argument.
	 * @return string Routed statistics chart-data URL.
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
	 * Build the statistics chart-data URL for a specific team.
	 * Routes to the teamstats chartdata layout using project, team and optional database parameters.
	 *
	 * @param int|string $projectid Project identifier.
	 * @param int|string $teamid Team identifier.
	 * @param int|null $cfg_which_database Optional database selector.
	 * @param int|string $s Legacy season argument.
	 * @return string Routed team chart-data URL.
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
	 * Build a legacy tournament tree-node link for a project.
	 * Preserves the historical treetonode destination with optional database selection for existing menu layouts.
	 *
	 * @param int|string $projectid Project identifier.
	 * @param int|null $cfg_which_database Optional database selector.
	 * @param int|string $s Legacy season argument.
	 * @return string Routed tournament bracket-node URL.
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
	 * Build the frontend popup URL for editing a match lineup.
	 * Carries match, team, project, season and layout context to the editmatch view; Joomla's route builder then resolves an appropriate site menu and language path.
	 *
	 * @param int|string $projectid Project identifier.
	 * @param int|string $matchid Match identifier.
	 * @param string $layout Match-editor layout name.
	 * @param int|string $team Optional team identifier.
	 * @param int|string $projectTeam Optional project-team identifier.
	 * @param string $match_date Match date passed to the editor.
	 * @param int $cfg_which_database SportsManagement database selector.
	 * @param int|string $s Season identifier.
	 * @param int|string $r Round identifier.
	 * @param int|string $division Division identifier.
	 * @param string $oldlayout Previous layout to return to.
	 * @param int $doubleevents Whether double-event editing is enabled.
	 * @return string Joomla-routed match-lineup editor URL.
	 */
	public static function getEditLineupRoute($projectid, $matchid, $layout = 'editlineup', $team = 0, $projectTeam = 0, $match_date = '0000-00-00', $cfg_which_database = 0, $s = 0, $r = 0, $division = 0, $oldlayout = '',$doubleevents = 0)
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
		                "match_date"         => $match_date,
                        "doubleevents"         => $doubleevents
		);

		$query = self::buildQuery($params);
		$link  = Route::_('index.php?' . $query, false);

		return $link;
	}

	/**
	 * Create a Joomla Contacts link for an associated contact record.
	 * Uses com_contact's contact view, not the sports component, and preserves the optional database selector for compatibility.
	 *
	 * @param int|string $contactid Joomla contact identifier.
	 * @param int|null $cfg_which_database Optional legacy database selector.
	 * @param int|string $s Legacy season argument.
	 * @return string Routed Joomla contact URL.
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
	 * Open the Community Builder Enhanced profile for a SportsManagement member.
	 * Passes the user and linked person identifiers to com_cbe while preserving the legacy database selector argument.
	 *
	 * @param int|string $u_id Member's Joomla user identifier.
	 * @param int|string $p_id SportsManagement person identifier.
	 * @param int|string $pl_id Legacy player identifier.
	 * @param int|null $cfg_which_database Optional database selector.
	 * @param int|string $s Legacy season argument.
	 * @return string Routed CBE user-profile URL.
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
	 * Create a Community Builder user-profile link for a member.
	 * Uses com_comprofiler routing with the userProfile task and optional legacy database selector rather than a SportsManagement view.
	 *
	 * @param int|string $userid Joomla user identifier.
	 * @param int|null $cfg_which_database Optional database selector.
	 * @param int|string $s Legacy season argument.
	 * @return string Routed Community Builder profile URL.
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
