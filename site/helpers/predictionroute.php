<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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
* SportsManagement ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es nützlich sein wird, aber
* OHNE JEDE GEWÄHELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
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
 * JSMPredictionHelperRoute
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class JSMPredictionHelperRoute extends sportsmanagementHelperRoute 
{

/**
 * JSMPredictionHelperRoute::getPredictionResultsRoute()
 * 
 * @param mixed $predictionID
 * @param mixed $roundID
 * @param mixed $projectID
 * @param mixed $userID
 * @param string $anchor
 * @param mixed $groupID
 * @return
 */
public static function getPredictionResultsRoute($predictionID,$roundID=0,$projectID=0,$userID=0,$anchor='',$groupID=0)
	{
		$params = array('option' => 'com_sportsmanagement', 
						'view' => 'predictionresults', 
						'prediction_id' => $predictionID);

        // diddipoeler
//        if (!is_null($projectID)){$params['p']=$projectID;}
//        if (!is_null($groupID)){$params['pggroup']=$groupID;}
//		if (!is_null($projectID)){$params['pj']=$projectID;}
//		if (!is_null($roundID)){$params['r']=$roundID;}
//		if (!is_null($userID)){$params['uid']=$userID;}
        
        $params['p']=$projectID;
        $params['pggroup']=$groupID;
        $params['pj']=$projectID;
        $params['r']=$roundID;
        $params['uid']=$userID;
        
		$query = JSMPredictionHelperRoute::buildQuery($params);
		//echo $query; die();
		$link = JRoute::_('index.php?' . $query . $anchor, false);

		return $link;
	}

/**
 * JSMPredictionHelperRoute::getPredictionRankingRoute()
 * 
 * @param mixed $predictionID
 * @param mixed $projectID
 * @param mixed $roundID
 * @param string $anchor
 * @param mixed $groupID
 * @param integer $groupRank
 * @return
 */
public static function getPredictionRankingRoute($predictionID,$projectID=0,$roundID=0,$anchor='',$groupID=0,$groupRank=0)
	{
		$params = array('option' => 'com_sportsmanagement', 
						'view' => 'predictionranking', 
						'prediction_id' => $predictionID);

        // diddipoeler
//        if (!is_null($projectID)){$params['p']=$projectID;}
//        if (!is_null($groupID)){$params['pggroup']=$groupID;}
//		if (!is_null($projectID)){$params['pj']=$projectID;}
//		if (!is_null($roundID)){$params['r']=$roundID;}
        
        //if (!is_null($groupRank)){$params['pggrouprank']=$groupRank;}
        $params['p']=$projectID;
        $params['pggroup']=$groupID;
        $params['pj']=$projectID;
        $params['r']=$roundID;
        $params['pggrouprank']=$groupRank;

		$query = JSMPredictionHelperRoute::buildQuery($params);
		$link = JRoute::_('index.php?' . $query, false);

		return $link;
	}

/**
 * JSMPredictionHelperRoute::getPredictionRulesRoute()
 * 
 * @param mixed $predictionID
 * @return
 */
public static function getPredictionRulesRoute($predictionID)
	{
		$params = array('option' => 'com_sportsmanagement', 
						'view' => 'predictionrules', 
						'prediction_id' => $predictionID);

		$query = JSMPredictionHelperRoute::buildQuery($params);
		$link = JRoute::_('index.php?' . $query, false);

		return $link;
	}
      	
/**
 * JSMPredictionHelperRoute::getPredictionTippEntryRoute()
 * 
 * @param mixed $predictionID
 * @param mixed $userID
 * @param mixed $roundID
 * @param mixed $projectID
 * @param string $anchor
 * @return
 */
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
		$query = JSMPredictionHelperRoute::buildQuery($params);
		$link = JRoute::_('index.php?' . $query, false);
		return $link;
	}

/**
 * JSMPredictionHelperRoute::getPredictionMemberRoute()
 * 
 * @param mixed $predictionID
 * @param mixed $userID
 * @param mixed $task
 * @param mixed $projectID
 * @return
 */
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

		$query = JSMPredictionHelperRoute::buildQuery($params);
		$link = JRoute::_('index.php?' . $query, false);

		return $link;
	}
    		
/**
 * JSMPredictionHelperRoute::buildQuery()
 * 
 * @param mixed $parts
 * @return
 */
public static function buildQuery($parts)
	{
		if ($item = sportsmanagementHelperRoute::_findItem($parts))
		{
			$parts['Itemid'] = $item->id;
		}
		else {
			$params = JComponentHelper::getParams('com_sportsmanagement');
			if ($params->get('default_itemid')) {
				$parts['Itemid'] = intval($params->get('default_itemid'));				
			}
		}

		return JUri::buildQuery( $parts );
	}
  
  	
}
?>