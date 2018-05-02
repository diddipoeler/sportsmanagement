<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      predictionusers.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage prediction
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');
jimport('joomla.application.component.controllerform');

/**
 * sportsmanagementControllerPredictionUsers
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementControllerPredictionUsers extends JControllerLegacy
{


	/**
	 * sportsmanagementControllerPredictionUsers::display()
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
	 * sportsmanagementControllerPredictionUsers::cancel()
	 * 
	 * @return
	 */
	function cancel()
	{
		JFactory::getApplication()->redirect(str_ireplace('&layout=edit','',JFactory::getURI()->toString()));
	}

	/**
	 * sportsmanagementControllerPredictionUsers::select()
	 * 
	 * @return
	 */
	function select()
	{
		JSession::checkToken() or jexit(\JText::_('JINVALID_TOKEN'));
        // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $post = $jinput->post->getArray(array());
        
//		$pID	= JFactory::getApplication()->input->getVar('prediction_id',	'',		'post',	'int');
//		$uID	= JFactory::getApplication()->input->getVar('uid',			null,	'post',	'int');
//		if (empty($post ['uid']))
//        {
//            $post ['uid'] = null;
//            }
		$link = JSMPredictionHelperRoute::getPredictionMemberRoute($post['prediction_id'],$post['uid'],$post['task'],$post['pj'],$post['pggroup'],$post['r']);

//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' link <br><pre>'.print_r($link,true).'</pre>'),'Notice');		
		
		//echo '<br />' . $link . '<br />';
		$this->setRedirect($link);
	}

	/**
	 * sportsmanagementControllerPredictionUsers::savememberdata()
	 * 
	 * @return
	 */
	function savememberdata()
	{
JSession::checkToken() or jexit(\JText::_('JINVALID_TOKEN'));
        $option = JFactory::getApplication()->input->getCmd('option');
        $optiontext = strtoupper(JFactory::getApplication()->input->getCmd('option').'_');
		$app = JFactory::getApplication();
		$document = JFactory::getDocument();
        
		$msg	= '';
		$link	= '';

		$post	= JFactory::getApplication()->input->post->getArray(array());
		//echo '<br /><pre>~' . print_r($post,true) . '~</pre><br />';
		$predictionGameID	= JFactory::getApplication()->input->getVar('prediction_id',	'','post','int');
		$joomlaUserID		= JFactory::getApplication()->input->getVar('user_id',		'','post','int');

		//$model			= $this->getModel('predictionusers');
        $modelusers = JModelLegacy::getInstance("predictionusers", "sportsmanagementModel");
        $model = JModelLegacy::getInstance("prediction", "sportsmanagementModel");
		$user			= JFactory::getUser();
		$isMember		= $model->checkPredictionMembership();
		$allowedAdmin	= $model->getAllowed();

		if ( ( ( $user->id != $joomlaUserID ) ) && ( !$allowedAdmin ) )
		{
			$msg .= JText::_('COM_SPORTSMANAGEMENT_PRED_USERS_CONTROLLER_ERROR_1');
			$link = JFactory::getURI()->toString();
		}
		else
		{
			if ((!$isMember) && (!$allowedAdmin))
			{
				$msg .= JText::_('COM_SPORTSMANAGEMENT_PRED_USERS_CONTROLLER_ERROR_2');
				$link = JFactory::getURI()->toString();
			}
			else
			{
				if (!$modelusers->savememberdata())
				{
					$msg .= JText::_('COM_SPORTSMANAGEMENT_PRED_USERS_CONTROLLER_ERROR_3');
					$link = JFactory::getURI()->toString();
				}
				else
				{
					$msg .= JText::_('COM_SPORTSMANAGEMENT_PRED_USERS_CONTROLLER_MSG_1');
					$link = JFactory::getURI()->toString();
				}
			}
		}

		//echo '<br />';
		//echo '' . $link . '<br />';
		//echo '' . $msg . '<br />';
		$this->setRedirect($link,$msg);
	}

	/**
	 * sportsmanagementControllerPredictionUsers::selectprojectround()
	 * 
	 * @return
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

		$link = JSMPredictionHelperRoute::getPredictionMemberRoute($pID,$uID,null,$pjID,$pggroup ,$rID );
		//echo '<br />' . $link . '<br />';
		$this->setRedirect($link);
	}

}
?>
