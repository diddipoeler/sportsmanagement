<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      predictionrules.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @subpackage predictionrules
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');
//require_once(JPATH_COMPONENT_SITE.DS.'models'.DS.'prediction.php' );


/**
 * sportsmanagementModelPredictionRules
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelPredictionRules extends JModelLegacy
{
	/**
	 * sportsmanagementModelPredictionRules::__construct()
	 * 
	 * @return
	 */
	function __construct()
	{
	    // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
    
//    $this->predictionGameID		= JFactory::getApplication()->input->getInt('prediction_id',		0);
//		$this->predictionMemberID	= JFactory::getApplication()->input->getInt('uid',	0);
//		$this->joomlaUserID			= JFactory::getApplication()->input->getInt('juid',	0);
//		$this->roundID				= JFactory::getApplication()->input->getInt('r',		0);
//        $this->pggroup				= JFactory::getApplication()->input->getInt('pggroup',		0);
//        $this->pggrouprank			= JFactory::getApplication()->input->getInt('pggrouprank',		0);
//		$this->pjID					= JFactory::getApplication()->input->getInt('p',		0);
//		$this->isNewMember			= JFactory::getApplication()->input->getInt('s',		0);
//		$this->tippEntryDone		= JFactory::getApplication()->input->getInt('eok',	0);
//
//		$this->from  				= JFactory::getApplication()->input->getInt('from',	$this->roundID);
//		$this->to	 				= JFactory::getApplication()->input->getInt('to',	$this->roundID);
//		$this->type  				= JFactory::getApplication()->input->getInt('type',	0);
//
//		$this->page  				= JFactory::getApplication()->input->getInt('page',	1);
        
        $prediction = new sportsmanagementModelPrediction();  

//        sportsmanagementModelPrediction::$predictionGameID = $this->predictionGameID;
//        sportsmanagementModelPrediction::$predictionMemberID = $this->predictionMemberID;
//        sportsmanagementModelPrediction::$joomlaUserID = $this->joomlaUserID;
//        sportsmanagementModelPrediction::$roundID = $this->roundID;
//        sportsmanagementModelPrediction::$pggroup = $this->pggroup;
//        sportsmanagementModelPrediction::$pggrouprank = $this->pggrouprank;
//        sportsmanagementModelPrediction::$pjID = $this->pjID;
//        sportsmanagementModelPrediction::$isNewMember = $this->isNewMember;
//        sportsmanagementModelPrediction::$tippEntryDone = $this->tippEntryDone;
//        sportsmanagementModelPrediction::$from = $this->from;
//        sportsmanagementModelPrediction::$to = $this->to;
//        sportsmanagementModelPrediction::$type = $this->type;
//        sportsmanagementModelPrediction::$page = $this->page;
       
       sportsmanagementModelPrediction::$roundID = $jinput->getVar('r','0');
       sportsmanagementModelPrediction::$pjID = $jinput->getVar('pj','0');
       sportsmanagementModelPrediction::$from = $jinput->getVar('from',$jinput->getVar('r','0'));
       sportsmanagementModelPrediction::$to = $jinput->getVar('to',$jinput->getVar('r','0'));
        
       sportsmanagementModelPrediction::$predictionGameID = $jinput->getVar('prediction_id','0');
       
        sportsmanagementModelPrediction::$predictionMemberID = $jinput->getInt('uid',0);
        sportsmanagementModelPrediction::$joomlaUserID = $jinput->getInt('juid',0);
        
        sportsmanagementModelPrediction::$pggroup = $jinput->getInt('pggroup',0);
        sportsmanagementModelPrediction::$pggrouprank = $jinput->getInt('pggrouprank',0);
        
        sportsmanagementModelPrediction::$isNewMember = $jinput->getInt('s',0);
        sportsmanagementModelPrediction::$tippEntryDone = $jinput->getInt('eok',0);
        
        sportsmanagementModelPrediction::$type = $jinput->getInt('type',0);
        sportsmanagementModelPrediction::$page = $jinput->getInt('page',1);
        
		parent::__construct();
	}

}
?>