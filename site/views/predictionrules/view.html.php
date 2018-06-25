<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage predictionrules
 */
 
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.view');

/**
 * sportsmanagementViewPredictionRules
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewPredictionRules extends JViewLegacy
{
	/**
	 * sportsmanagementViewPredictionRules::display()
	 * 
	 * @param mixed $tpl
	 * @return
	 */
	function display($tpl=null)
	{
		// Get a refrence of the page instance in joomla
		$document	= JFactory::getDocument();
		$model		= $this->getModel();
    $option = JFactory::getApplication()->input->getCmd('option');
    
		$app = JFactory::getApplication();

		$this->predictionGame = sportsmanagementModelPrediction::getPredictionGame();
        $this->allowedAdmin = sportsmanagementModelPrediction::getAllowed();
        $this->headertitle = JText::_('COM_SPORTSMANAGEMENT_PRED_RULES_SECTION_TITLE');

		if (isset($this->predictionGame))
		{
			$config			= sportsmanagementModelPrediction::getPredictionTemplateConfig($this->getName());
			$overallConfig	= sportsmanagementModelPrediction::getPredictionOverallConfig();

			$this->model = $model;
			$this->config = array_merge($overallConfig,$config);
      $configavatar	= sportsmanagementModelPrediction::getPredictionTemplateConfig('predictionusers');
      $this->configavatar = $configavatar;
			$this->predictionMember = sportsmanagementModelPrediction::getPredictionMember($configavatar);
			$this->predictionProjectS = sportsmanagementModelPrediction::getPredictionProjectS();
			$this->actJoomlaUser = JFactory::getUser();
			// Set page title
			$pageTitle = JText::_('COM_SPORTSMANAGEMENT_PRED_USERS_TITLE'); // 'Tippspiel Regeln'

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