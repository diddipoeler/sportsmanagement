<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage predictionresults
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.view');
jimport( 'joomla.filesystem.file' );

// pagination
//require_once(JPATH_COMPONENT . DS . 'helpers' . DS . 'pagination.php');


/**
 * sportsmanagementViewPredictionResults
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewPredictionResults extends sportsmanagementView
{
	
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
		
		if (isset($this->predictionGame))
		{
			$config	= sportsmanagementModelPrediction::getPredictionTemplateConfig($this->getName());
			$configavatar = sportsmanagementModelPrediction::getPredictionTemplateConfig('predictionusers');
			$configentry = sportsmanagementModelPrediction::getPredictionTemplateConfig('predictionentry');
			$config = array_merge($configentry,$config);
			$overallConfig	= sportsmanagementModelPrediction::getPredictionOverallConfig();

			$this->roundID = sportsmanagementModelPredictionResults::$roundID;
			$this->config = array_merge($overallConfig,$config);
            $this->model->config = $this->config;
			$this->configavatar = $configavatar;
            $this->model->configavatar = $this->configavatar;

			$this->predictionMember = sportsmanagementModelPrediction::getPredictionMember($configavatar);
			$this->predictionProjectS = sportsmanagementModelPrediction::getPredictionProjectS();
			$this->actJoomlaUser = JFactory::getUser();

      $predictionRounds[] = JHTML::_('select.option','0',JText::_('COM_SPORTSMANAGEMENT_PRED_SELECT_ROUNDS'),'value','text');
      if ( $res = sportsmanagementModelPrediction::getRoundNames($this->predictionGame->id) )
      {
        $predictionRounds = array_merge($predictionRounds,$res);
        }
			$lists['predictionRounds'] = JHTML::_('select.genericList',$predictionRounds,'r','class="inputbox" onchange="this.form.submit(); "','value','text',sportsmanagementModelPrediction::$roundID);
			unset($res);
			unset($predictionRounds);
			
			$this->lists = $lists;
			$this->show_debug_info = JComponentHelper::getParams('com_sportsmanagement')->get('show_debug_info',0);
			// Set page title
			$pageTitle = JText::_('COM_SPORTSMANAGEMENT_PRED_RESULTS_TITLE');
        
            $this->memberList = $this->get('Data');
            $this->pagination = $this->get('Pagination');

//$headertitle
$this->headertitle = $pageTitle;

if ( !isset($this->config['table_class']) )
{
$this->config['table_class'] = 'table';    
}

			$this->document->setTitle($pageTitle);

		}
		else
		{
			JError::raiseNotice(500,JText::_('COM_SPORTSMANAGEMENT_PRED_PREDICTION_NOT_EXISTING'));
		}
	}

}
?>
