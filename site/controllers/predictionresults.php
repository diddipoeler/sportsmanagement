<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      predictionresults.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage prediction
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');


/**
 * sportsmanagementControllerPredictionResults
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementControllerPredictionResults extends JControllerLegacy
{

		/**
		 * sportsmanagementControllerPredictionResults::display()
		 * 
		 * @param bool $cachable
		 * @param bool $urlparams
		 * @return void
		 */
		function display($cachable = false, $urlparams = false)
	{

		parent::display($cachable, $urlparams = false);
	}

	/**
	 * sportsmanagementControllerPredictionResults::selectprojectround()
	 * 
	 * @return void
	 */
	function selectprojectround()
	{
		JSession::checkToken() or jexit(\JText::_('JINVALID_TOKEN'));
        // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $pID = $jinput->getVar('prediction_id','0');
        $pggroup = $jinput->getVar('pggroup','0');
        $pggrouprank = $jinput->getVar('pggrouprank','0');
        $pjID = $jinput->getVar('pj','0');
        $rID = $jinput->getVar('r','0');
        $set_pj = $jinput->getVar('set_pj','0');
        $set_r = $jinput->getVar('set_r','0');
        $cfg_which_database = $jinput->getVar('cfg_which_database','0');
        
//		//echo '<br /><pre>~' . print_r($post,true) . '~</pre><br />'; die();
//		$pID	= JFactory::getApplication()->input->getVar('prediction_id',	null,	'post',	'int');
//		// diddipoeler
//        $pggroup	= JFactory::getApplication()->input->getVar('pggroup',	null,	'post',	'int');
//		$pjID	= JFactory::getApplication()->input->getVar('pj',	null,	'post',	'int');
//        $rID	= JFactory::getApplication()->input->getVar('r',				null,	'post',	'int');
        
        
        
		$link = JSMPredictionHelperRoute::getPredictionResultsRoute($pID,$rID,$pjID,NULL,'',$pggroup,$cfg_which_database);
		$this->setRedirect($link);
	}

}
?>