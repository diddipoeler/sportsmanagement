<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage predictionranking
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// pagination
//require_once (JPATH_COMPONENT . DS . 'helpers' . DS . 'pagination.php');

/**
 * sportsmanagementViewPredictionRanking
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewpredictionranking extends sportsmanagementView
{
	
	/**
	 * sportsmanagementViewpredictionranking::init()
	 * 
	 * @return void
	 */
	function init()
	{

		$this->predictionGame = sportsmanagementModelPrediction::getPredictionGame();
        $this->allowedAdmin = sportsmanagementModelPrediction::getAllowed();

$this->limit = $this->model->getLimit();
$this->limitstart = $this->model->getLimitStart();
$this->ausgabestart = $this->limitstart + 1;
$this->ausgabeende = $this->limitstart + $this->limit;
		
//$this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' limit <br><pre>'.print_r($this->limit,true).'</pre>'),'');
//$this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' limitstart <br><pre>'.print_r($this->limitstart,true).'</pre>'),'');
//$this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ausgabestart <br><pre>'.print_r($this->ausgabestart,true).'</pre>'),'');
//$this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ausgabeende <br><pre>'.print_r($this->ausgabeende,true).'</pre>'),'');
		
	// push data into the template
	$this->state = $this->get('State');	
	$this->items = $this->get('Data');	
	$this->pagination =$this->get('Pagination');
    
    $this->headertitle = JText::_('COM_SPORTSMANAGEMENT_PRED_RANK_TITLE');
       
		if (isset($this->predictionGame))
		{
			$config	= sportsmanagementModelPrediction::getPredictionTemplateConfig($this->getName());
			$overallConfig	= sportsmanagementModelPrediction::getPredictionOverallConfig();
      $configavatar	= sportsmanagementModelPrediction::getPredictionTemplateConfig('predictionusers');
      $configentries = sportsmanagementModelPrediction::getPredictionTemplateConfig('predictionentry');
      
			$this->roundID = sportsmanagementModelPrediction::$roundID;
      $this->configavatar = $configavatar;
      $this->configentries = $configentries;
      $this->config = array_merge($overallConfig,$config);
      
			$this->predictionMember = sportsmanagementModelPrediction::getPredictionMember($configavatar);
			$this->predictionProjectS = sportsmanagementModelPrediction::getPredictionProjectS();
			$this->actJoomlaUser = JFactory::getUser();
			
            
            $ranking_array = array();
			$ranking_array[] = JHTML ::_('select.option','0',JText::_('COM_SPORTSMANAGEMENT_PRED_RANK_SINGLE_RANK'));
			$ranking_array[] = JHTML ::_('select.option','1',JText::_('COM_SPORTSMANAGEMENT_PRED_RANK_GROUP_RANK'));
			$lists['ranking_array'] = $ranking_array;
			unset($ranking_array);

			$type_array = array();
			$type_array[] = JHTML ::_('select.option','0',JText::_('COM_SPORTSMANAGEMENT_PRED_RANK_FULL_RANKING'));
			$type_array[] = JHTML ::_('select.option','1',JText::_('COM_SPORTSMANAGEMENT_PRED_RANK_FIRST_HALF'));
			$type_array[] = JHTML ::_('select.option','2',JText::_('COM_SPORTSMANAGEMENT_PRED_RANK_SECOND_HALF'));
			$lists['type'] = $type_array;
			unset($type_array);

			$this->lists = $lists;
			// Set page title
			$pageTitle = JText::_('COM_SPORTSMANAGEMENT_PRED_RANK_TITLE');
			
			$mdlProject = JModelLegacy::getInstance("Project", "sportsmanagementModel");
			foreach ( $this->predictionProjectS as $project )
			{
      $mdlProject->setProjectId($project->project_id);
      }
      
      $map_config = $mdlProject->getMapConfig($mdlProject::$cfg_which_database);
		  $this->mapconfig = $map_config; // Loads the project-template -settings for the GoogleMap
			
      $this->PredictionMembersList = sportsmanagementModelPrediction::getPredictionMembersList($this->config,$this->configavatar);
      
      $this->geo = new JSMsimpleGMapGeocoder();
	    $this->geo->genkml3prediction($this->predictionGame->id,$this->PredictionMembersList);
	  
			$this->document->setTitle($pageTitle);

		}
		else
		{
			JError::raiseNotice(500,JText::_('COM_SPORTSMANAGEMENT_PRED_PREDICTION_NOT_EXISTING'));
		}
	}

}
?>
