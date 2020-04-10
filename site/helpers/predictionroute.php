<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage helpers
 * @file       predictionroute.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */


defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;

/**
 * JSMPredictionHelperRoute
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class JSMPredictionHelperRoute extends sportsmanagementHelperRoute
{

	/**
	 * JSMPredictionHelperRoute::getPredictionResultsRoute()
	 *
	 * @param   mixed   $predictionID
	 * @param   mixed   $roundID
	 * @param   mixed   $projectID
	 * @param   mixed   $userID
	 * @param   string  $anchor
	 * @param   mixed   $groupID
	 *
	 * @return
	 */
	public static function getPredictionResultsRoute($predictionID, $roundID = 0, $projectID = 0, $userID = 0, $anchor = '', $groupID = 0, $cfg_which_database = 0)
	{
		$app = Factory::getApplication();

		$params = array('option'             => 'com_sportsmanagement',
		                'view'               => 'predictionresults',
		                'cfg_which_database' => $cfg_which_database,
		                'prediction_id'      => $predictionID);


		// diddipoeler
		//        if (!is_null($projectID)){$params['p']=$projectID;}
		//        if (!is_null($groupID)){$params['pggroup']=$groupID;}
		//		if (!is_null($projectID)){$params['pj']=$projectID;}
		//		if (!is_null($roundID)){$params['r']=$roundID;}
		//		if (!is_null($userID)){$params['uid']=$userID;}

		//$params['p'] = $projectID;
		$params['pggroup'] = $groupID;
		$params['pj']      = $projectID;
		$params['r']       = ($roundID != '') ? $roundID : 0;
		$params['uid']     = $userID;


		$query = JSMPredictionHelperRoute::buildQuery($params);
		//echo $query; die();
		$link = Route::_('index.php?' . $query . $anchor, false);


		return $link;
	}

	/**
	 * JSMPredictionHelperRoute::buildQuery()
	 *
	 * @param   mixed  $parts
	 *
	 * @return
	 */
	public static function buildQuery($parts)
	{
		if ($item = sportsmanagementHelperRoute::_findItem($parts))
		{
			$parts['Itemid'] = $item->id;
		}
		else
		{
			$params = ComponentHelper::getParams('com_sportsmanagement');
			if ($params->get('default_itemid'))
			{
				$parts['Itemid'] = intval($params->get('default_itemid'));
			}
		}

		return Uri::buildQuery($parts);
	}

	/**
	 * JSMPredictionHelperRoute::getPredictionRankingRoute()
	 *
	 * @param   mixed    $predictionID
	 * @param   mixed    $projectID
	 * @param   mixed    $roundID
	 * @param   string   $anchor
	 * @param   mixed    $groupID
	 * @param   integer  $groupRank
	 *
	 * @return
	 */
	public static function getPredictionRankingRoute($predictionID, $projectID = 0, $roundID = 0, $anchor = '', $groupID = 0, $groupRank = 0, $type = 0, $from = 0, $to = 0, $cfg_which_database = 0)
	{
		$app = Factory::getApplication();

		$params = array('option'             => 'com_sportsmanagement',
		                'view'               => 'predictionranking',
		                'cfg_which_database' => $cfg_which_database,
		                'prediction_id'      => $predictionID);

		// diddipoeler
		//        if (!is_null($projectID)){$params['p']=$projectID;}
		//        if (!is_null($groupID)){$params['pggroup']=$groupID;}
		//		if (!is_null($projectID)){$params['pj']=$projectID;}
		//		if (!is_null($roundID)){$params['r']=$roundID;}

		//if (!is_null($groupRank)){$params['pggrouprank']=$groupRank;}
		//$params['p'] = $projectID;
		$params['pggroup']     = $groupID;
		$params['pj']          = $projectID;
		$params['r']           = $roundID;
		$params['pggrouprank'] = $groupRank;
		$params['type']        = $type;
		$params['from']        = $from;
		$params['to']          = $to;


		$query = JSMPredictionHelperRoute::buildQuery($params);
		$link  = Route::_('index.php?' . $query, false);


		return $link;
	}

	/**
	 * JSMPredictionHelperRoute::getPredictionRulesRoute()
	 *
	 * @param   mixed  $predictionID
	 *
	 * @return
	 */
	public static function getPredictionRulesRoute($predictionID, $cfg_which_database = 0)
	{
		$params = array('option'             => 'com_sportsmanagement',
		                'view'               => 'predictionrules',
		                'cfg_which_database' => $cfg_which_database,
		                'prediction_id'      => $predictionID);

		$query = JSMPredictionHelperRoute::buildQuery($params);
		$link  = Route::_('index.php?' . $query, false);

		return $link;
	}

	/**
	 * JSMPredictionHelperRoute::getPredictionTippEntryRoute()
	 *
	 * @param   mixed   $predictionID
	 * @param   mixed   $userID
	 * @param   mixed   $roundID
	 * @param   mixed   $projectID
	 * @param   string  $anchor
	 *
	 * @return
	 */
	public static function getPredictionTippEntryRoute($predictionID, $userID = 0, $roundID = 0, $projectID = 0, $anchor = '', $groupID = 0, $cfg_which_database = 0)
	{
		$params = array('option'             => 'com_sportsmanagement',
		                'view'               => 'predictionentry',
		                'cfg_which_database' => $cfg_which_database,
		                'prediction_id'      => $predictionID);

		//        // diddipoeler
		//		if (!is_null($projectID)){$params['p']=$projectID;}
		//
		//		if (!is_null($projectID)){$params['pj']=$projectID;}
		//		if (!is_null($roundID)){$params['r']=$roundID;}
		//		if (!is_null($userID)){$params['uid']=$userID;}

		//$params['p'] = $projectID;
		$params['pggroup'] = $groupID;
		$params['pj']      = $projectID;
		$params['r']       = ($roundID != '') ? $roundID : 0;
		$params['uid']     = $userID;

		$query = JSMPredictionHelperRoute::buildQuery($params);
		$link  = Route::_('index.php?' . $query, false);

		return $link;
	}

	/**
	 * JSMPredictionHelperRoute::getPredictionMemberRoute()
	 *
	 * @param   mixed    $predictionID
	 * @param   mixed    $userID
	 * @param   mixed    $task
	 * @param   mixed    $projectID
	 * @param   integer  $groupID
	 * @param   integer  $roundID
	 *
	 * @return
	 */
	public static function getPredictionMemberRoute($predictionID, $userID = 0, $task = 0, $projectID = 0, $groupID = 0, $roundID = 0, $cfg_which_database = 0)
	{

		switch ($task)
		{
			case 'edit';
				$params = array('option'             => 'com_sportsmanagement',
				                'view'               => 'predictionuser',
				                'cfg_which_database' => $cfg_which_database,
				                'prediction_id'      => $predictionID);
				break;
			default:
				$params = array('option'             => 'com_sportsmanagement',
				                'view'               => 'predictionusers',
				                'cfg_which_database' => $cfg_which_database,
				                'prediction_id'      => $predictionID);
				break;
		}

		//        // diddipoeler
		//        if (!is_null($projectID)){$params['p']=$projectID;}
		//
		//		if (!is_null($userID)){$params['uid']=$userID;}
		//		if (!is_null($projectID)){$params['pj']=$projectID;}
		//		if (!is_null($task)){$params['layout']=$task;}


		//$params['p'] = $projectID;
		$params['pggroup'] = $groupID;
		$params['pj']      = $projectID;
		$params['r']       = ($roundID != '') ? $roundID : 0;
		$params['uid']     = $userID;

		switch ($task)
		{
			case 'edit';
				$params['layout'] = 'edit';
				break;

		}

		$query = JSMPredictionHelperRoute::buildQuery($params);
		$link  = Route::_('index.php?' . $query, false);

		return $link;
	}


}

?>
