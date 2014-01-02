<?php
/**
* @copyright	Copyright (C) 2007-2012 JoomLeague.net. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

// Component Helper
jimport('joomla.application.component.helper');

/**
 *
 */
class PredictionHelperRoute extends sportsmanagementHelperRoute 
{

public static function getPredictionResultsRoute($predictionID,$roundID=null,$projectID=null,$userID=null,$anchor='',$groupID=null)
	{
		$params = array('option' => 'com_sportsmanagement', 
						'view' => 'predictionresults', 
						'prediction_id' => $predictionID);

        // diddipoeler
        if (!is_null($projectID)){$params['p']=$projectID;}
        if (!is_null($groupID)){$params['pggroup']=$groupID;}
		if (!is_null($projectID)){$params['pj']=$projectID;}
		if (!is_null($roundID)){$params['r']=$roundID;}
		if (!is_null($userID)){$params['uid']=$userID;}
		$query = PredictionHelperRoute::buildQuery($params);
		//echo $query; die();
		$link = JRoute::_('index.php?' . $query . $anchor, false);

		return $link;
	}

public static function getPredictionRankingRoute($predictionID,$projectID=null,$roundID=null,$anchor='',$groupID=null,$groupRank=0)
	{
		$params = array('option' => 'com_sportsmanagement', 
						'view' => 'predictionranking', 
						'prediction_id' => $predictionID);

        // diddipoeler
        if (!is_null($projectID)){$params['p']=$projectID;}
        if (!is_null($groupID)){$params['pggroup']=$groupID;}
		if (!is_null($projectID)){$params['pj']=$projectID;}
		if (!is_null($roundID)){$params['r']=$roundID;}
        //if (!is_null($groupRank)){$params['pggrouprank']=$groupRank;}
        $params['pggrouprank']=$groupRank;

		$query = PredictionHelperRoute::buildQuery($params);
		$link = JRoute::_('index.php?' . $query, false);

		return $link;
	}

public static function getPredictionRulesRoute($predictionID)
	{
		$params = array('option' => 'com_sportsmanagement', 
						'view' => 'predictionrules', 
						'prediction_id' => $predictionID);

		$query = PredictionHelperRoute::buildQuery($params);
		$link = JRoute::_('index.php?' . $query, false);

		return $link;
	}
      	
public static function getPredictionTippEntryRoute($predictionID,$userID=null,$roundID=null,$projectID=null,$anchor='')
	{
		$params = array('option' => 'com_sportsmanagement', 
						'view' => 'predictionentry', 
						'prediction_id' => $predictionID);
    
        // diddipoeler
		if (!is_null($projectID)){$params['p']=$projectID;}
        
		if (!is_null($projectID)){$params['pj']=$projectID;}
		if (!is_null($roundID)){$params['r']=$roundID;}
		if (!is_null($userID)){$params['uid']=$userID;}
		$query = PredictionHelperRoute::buildQuery($params);
		$link = JRoute::_('index.php?' . $query, false);
		return $link;
	}

public static function getPredictionMemberRoute($predictionID,$userID=null,$task=null,$projectID=null)
	{
	
	switch ($task)
	{
  case 'edit';
  $params = array('option' => 'com_sportsmanagement', 
						'view' => 'predictionuser', 
						'prediction_id' => $predictionID);
	break;					
  default:
  $params = array('option' => 'com_sportsmanagement', 
						'view' => 'predictionusers', 
						'prediction_id' => $predictionID);
  break;
  }
		
        // diddipoeler
        if (!is_null($projectID)){$params['p']=$projectID;}
        
		if (!is_null($userID)){$params['uid']=$userID;}
		if (!is_null($projectID)){$params['pj']=$projectID;}
		if (!is_null($task)){$params['layout']=$task;}

		$query = PredictionHelperRoute::buildQuery($params);
		$link = JRoute::_('index.php?' . $query, false);

		return $link;
	}
    		
public static function buildQuery($parts)
	{
		if ($item = JoomleagueHelperRoute::_findItem($parts))
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
  
  	
}
?>