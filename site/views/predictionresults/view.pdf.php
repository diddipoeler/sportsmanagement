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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');
jimport( 'joomla.filesystem.file' );

require_once(JPATH_COMPONENT . DS . 'helpers' . DS . 'pagination.php');

/**
 * Joomleague Component prediction View
 *
 * @author	Kurt Norgaz
 * @package	JoomLeague
 * @since	1.5.100627
 */
class JoomleagueViewPredictionResults extends JViewLegacy
{
	function display($tpl=null)
	{
		// Get a refrence of the page instance in joomla
		$document	=& JFactory::getDocument();
		$model		=& $this->getModel();
    $option = JFactory::getApplication()->input->getCmd('option');
    $optiontext = strtoupper(JFactory::getApplication()->input->getCmd('option').'_');
    $this->optiontext = $optiontext;
    
		$app = JFactory::getApplication();

		$this->predictionGame = $model->getPredictionGame();

		if (isset($this->predictionGame))
		{
			$config	= $model->getPredictionTemplateConfig($this->getName());
			$configavatar = $model->getPredictionTemplateConfig('predictionusers');
			$configentry = $model->getPredictionTemplateConfig('predictionentry');
			$config = array_merge($configentry,$config);
			$overallConfig = $model->getPredictionOverallConfig();
     
			$this->model = $model;
			$this->roundID = $this->model->roundID;
			$this->config = array_merge($overallConfig,$config);
			$this->configavatar = $configavatar;
			$this->predictionMember = $model->getPredictionMember($configavatar);
			$this->predictionProjectS = $model->getPredictionProjectS();
			$this->actJoomlaUser = JFactory::getUser();

      $predictionRounds[] = JHTML::_('select.option','0',JText::_('COM_SPORTSMANAGEMENT_PRED_SELECT_ROUNDS'),'value','text');
      if ( $res = &$model->getRoundNames($this->predictionGame->id) ){$predictionRounds = array_merge($predictionRounds,$res);}
			$lists['predictionRounds']=JHTML::_('select.genericList',$predictionRounds,'r','class="inputbox" onchange="this.form.submit(); "','value','text',$this->model->roundID);
			unset($res);
			unset($predictionRounds);
			
			$this->lists = $lists;
			$this->show_debug_info = JComponentHelper::getParams('com_sportsmanagement')->get('show_debug_info',0);
			// Set page title
			$pageTitle = JText::_('COM_SPORTSMANAGEMENT_PRED_RESULTS_TITLE');

			$document->setTitle($pageTitle);

			parent::display($tpl);
		}
		else
		{
			JError::raiseNotice(500,JText::_('COM_SPORTSMANAGEMENT_PRED_PREDICTION_NOT_EXISTING'));
		}
	}

}
?>
