<?php
/** SportsManagement ein Programm zur Verwaltung f�r Sportarten
 * @version   1.0.05
 * @file      predictionranking.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: � 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage prediction
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;

jimport('joomla.application.component.controller');


/**
 * sportsmanagementControllerPredictionRanking
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementControllerPredictionRanking extends JControllerLegacy
{
	
    	/**
    	 * sportsmanagementControllerPredictionRanking::display()
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
	 * sportsmanagementControllerPredictionRanking::selectprojectround()
	 * 
	 * @return void
	 */
	function selectprojectround()
	{
		JSession::checkToken() or jexit(\JText::_('JINVALID_TOKEN'));
        // Reference global application object
        $app = Factory::getApplication();
        // JInput object
        $jinput = $app->input;
        $pID = $jinput->getVar('prediction_id','0');
        $pggroup = $jinput->getVar('pggroup','0');
        $pggrouprank = $jinput->getVar('pggrouprank','0');
        $pjID = $jinput->getVar('pj','0');
        $rID = $jinput->getVar('r','0');
        $set_pj = $jinput->getVar('set_pj','0');
        $set_r = $jinput->getVar('set_r','0');
        
        $type = $jinput->getVar('type','0');
        $from = $jinput->getVar('from','0');
        $to = $jinput->getVar('to','0');
        
        if ( !$rID )
        {
        $rID = sportsmanagementModelPrediction::getProjectSettings($pjID);    
        }
        
		$link = JSMPredictionHelperRoute::getPredictionRankingRoute($pID,$pjID,$rID,'',$pggroup,$pggrouprank,$type,$from,$to);
        
		//echo '<br />' . $link . '<br />';
		$this->setRedirect($link);
	}

}
?>