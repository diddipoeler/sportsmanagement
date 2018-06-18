<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      predictionroute.php
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
public static function getPredictionResultsRoute($predictionID,$roundID=0,$projectID=0,$userID=0,$anchor='',$groupID=0,$cfg_which_database = 0)
	{
	   $app = JFactory::getApplication();
       
		$params = array('option' => 'com_sportsmanagement', 
						'view' => 'predictionresults', 
                        'cfg_which_database' => $cfg_which_database,
						'prediction_id' => $predictionID);


        // diddipoeler
//        if (!is_null($projectID)){$params['p']=$projectID;}
//        if (!is_null($groupID)){$params['pggroup']=$groupID;}
//		if (!is_null($projectID)){$params['pj']=$projectID;}
//		if (!is_null($roundID)){$params['r']=$roundID;}
//		if (!is_null($userID)){$params['uid']=$userID;}
        
       //$params['p'] = $projectID;
        $params['pggroup'] = $groupID;
        $params['pj'] = $projectID;
        $params['r'] =  ( $roundID != '' ) ? $roundID  : 0;
        $params['uid'] = $userID;

//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' params<br><pre>'.print_r($params,true).'</pre>'   ),'');
        
		$query = JSMPredictionHelperRoute::buildQuery($params);
		//echo $query; die();
		$link = JRoute::_('index.php?' . $query . $anchor, false);

//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query<br><pre>'.print_r($query,true).'</pre>'   ),'');

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
public static function getPredictionRankingRoute($predictionID,$projectID=0,$roundID=0,$anchor='',$groupID=0,$groupRank=0,$type=0,$from=0,$to=0,$cfg_which_database = 0)
	{
	   $app = JFactory::getApplication();
       
		$params = array('option' => 'com_sportsmanagement', 
						'view' => 'predictionranking', 
                        'cfg_which_database' => $cfg_which_database,
						'prediction_id' => $predictionID);

        // diddipoeler
//        if (!is_null($projectID)){$params['p']=$projectID;}
//        if (!is_null($groupID)){$params['pggroup']=$groupID;}
//		if (!is_null($projectID)){$params['pj']=$projectID;}
//		if (!is_null($roundID)){$params['r']=$roundID;}
        
        //if (!is_null($groupRank)){$params['pggrouprank']=$groupRank;}
        //$params['p'] = $projectID;
        $params['pggroup'] = $groupID;
        $params['pj'] = $projectID;
        $params['r'] =  $roundID ;
        $params['pggrouprank'] = $groupRank;
        $params['type'] = $type;
        $params['from'] = $from;
        $params['to'] = $to;

//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' params<br><pre>'.print_r($params,true).'</pre>'   ),'');

		$query = JSMPredictionHelperRoute::buildQuery($params);
		$link = JRoute::_('index.php?' . $query, false);

//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query<br><pre>'.print_r($query,true).'</pre>'   ),'');

		return $link;
	}

/**
 * JSMPredictionHelperRoute::getPredictionRulesRoute()
 * 
 * @param mixed $predictionID
 * @return
 */
public static function getPredictionRulesRoute($predictionID,$cfg_which_database = 0)
	{
		$params = array('option' => 'com_sportsmanagement', 
						'view' => 'predictionrules', 
                        'cfg_which_database' => $cfg_which_database,
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
public static function getPredictionTippEntryRoute($predictionID,$userID=0,$roundID=0,$projectID=0,$anchor='',$groupID=0,$cfg_which_database = 0)
	{
		$params = array('option' => 'com_sportsmanagement', 
						'view' => 'predictionentry', 
                        'cfg_which_database' => $cfg_which_database,
						'prediction_id' => $predictionID);
    
//        // diddipoeler
//		if (!is_null($projectID)){$params['p']=$projectID;}
//        
//		if (!is_null($projectID)){$params['pj']=$projectID;}
//		if (!is_null($roundID)){$params['r']=$roundID;}
//		if (!is_null($userID)){$params['uid']=$userID;}
        
        //$params['p'] = $projectID;
        $params['pggroup'] = $groupID;
        $params['pj'] = $projectID;
        $params['r'] =  ( $roundID != '' ) ? $roundID  : 0;
        $params['uid'] = $userID;
        
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
 * @param integer $groupID
 * @param integer $roundID
 * @return
 */
public static function getPredictionMemberRoute($predictionID,$userID=0,$task=0,$projectID=0,$groupID=0,$roundID=0,$cfg_which_database = 0)
	{
	
	switch ($task)
	{
  case 'edit';
  $params = array('option' => 'com_sportsmanagement', 
						'view' => 'predictionuser', 
                        'cfg_which_database' => $cfg_which_database,
						'prediction_id' => $predictionID);
	break;					
  default:
  $params = array('option' => 'com_sportsmanagement', 
						'view' => 'predictionusers', 
                        'cfg_which_database' => $cfg_which_database,
						'prediction_id' => $predictionID);
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
        $params['pj'] = $projectID;
        $params['r'] =  ( $roundID != '' ) ? $roundID  : 0;
        $params['uid'] = $userID;
        
              switch ($task)
	{
  case 'edit';
  $params['layout'] = 'edit';
	break;					
  
  }

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

		return JURI::buildQuery( $parts );
	}
  
  	
}
?>
