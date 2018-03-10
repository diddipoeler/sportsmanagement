<?php

/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      predictionentry.php
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
 * sportsmanagementControllerPredictionEntry
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementControllerPredictionEntry extends JControllerLegacy {

    /**
     * sportsmanagementControllerPredictionEntry::__construct()
     * 
     * @return void
     */
    function __construct() {
        $option = JFactory::getApplication()->input->getCmd('option');
        $app = JFactory::getApplication();
        $document = JFactory::getDocument();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' getTask<br><pre>'.print_r($this->getTask(),true).'</pre>'),'');
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' post<br><pre>'.print_r($jinput->post,true).'</pre>'),'');
        //$post	= JFactory::getApplication()->input->post->getArray(array());
        // Register Extra tasks
        //$this->registerTask( 'add',			'display' );
        //$this->registerTask( 'edit',		'display' );
        //$this->registerTask( 'apply',		'save' );
        //$this->registerTask( 'copy',		'copysave' );
        //$this->registerTask( 'apply',		'savepredictiongame' );
        parent::__construct();
    }

    /**
     * sportsmanagementControllerPredictionEntry::display()
     * 
     * @param bool $cachable
     * @param bool $urlparams
     * @return void
     */
    function display($cachable = false, $urlparams = false) {
        // Get the view name from the query string
        //$viewName = JFactory::getApplication()->input->getVar( 'view', 'editmatch' );
        //$viewName = JFactory::getApplication()->input->getVar( 'view' );
        //echo '<br /><pre>~' . print_r( $viewname, true ) . '~</pre><br />';
        // Get the view
        //$view =& $this->getView( $viewName );
//		$this->showprojectheading();
//		$this->showbackbutton();
//		$this->showfooter();
        parent::display($cachable, $urlparams = false);
    }

    /**
     * sportsmanagementControllerPredictionEntry::register()
     * 
     * @return void
     */
    function register() {
        $option = JFactory::getApplication()->input->getCmd('option');
        $app = JFactory::getApplication();
        $document = JFactory::getDocument();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');

        JSession::checkToken() or jexit(\JText::_('JINVALID_TOKEN'));

        $msg = '';
        $link = '';
        $post = JFactory::getApplication()->input->post->getArray(array());

        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' getTask<br><pre>'.print_r($this->getTask(),true).'</pre>'),'');
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' post<br><pre>'.print_r($post,true).'</pre>'),'');

        $predictionGameID = JFactory::getApplication()->input->getVar('prediction_id', '', 'post', 'int');
        $joomlaUserID = JFactory::getApplication()->input->getVar('user_id', '', 'post', 'int');
        $approved = JFactory::getApplication()->input->getVar('approved', 0, '', 'int');

        $model = $this->getModel('Prediction');
        $mdlPredictionEntry = JModelLegacy::getInstance("PredictionEntry", "sportsmanagementModel");
        $user = JFactory::getUser();
        $isMember = $model->checkPredictionMembership();

        if (( $user->id != $joomlaUserID)) {
            $msg .= JText::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_CONTROLLER_ERROR_1');
            $link = JFactory::getURI()->toString();
        } else {
            if ($isMember) {
                $msg .= JText::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_CONTROLLER_ERROR_4');
                $link = JFactory::getURI()->toString();
            } else {
                //$post['registerDate'] = JHTML::date(time(),'Y-m-d h:i:s');
                $post['registerDate'] = JHtml::date($input = 'now', 'Y-m-d h:i:s', false);
                //if (!$model->store($post,'PredictionEntry'))
                //$model = JTable::getInstance('PredictionEntry','sportsmanagementTable');
                if (!$mdlPredictionEntry->store($post)) {
                    $msg .= JText::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_CONTROLLER_ERROR_5');
                    $link = JFactory::getURI()->toString();
                } else {
                    $cids = array();
                    $cids[] = $mdlPredictionEntry->getDbo()->insertid();
                    JArrayHelper::toInteger($cids);

                    $msg .= JText::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_CONTROLLER_MSG_2');
                    if ($model->sendMembershipConfirmation($cids)) {
                        $msg .= ' - ';
                        $msg .= JText::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_CONTROLLER_MSG_3');
                    } else {
                        $msg .= ' - ';
                        $msg .= JText::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_CONTROLLER_ERROR_6');
                    }
                    $params = array('option' => 'com_sportsmanagement',
                        'view' => 'predictionentry',
                        'prediction_id' => $predictionGameID,
                        's' => '1');

                    $query = sportsmanagementHelperRoute::buildQuery($params);
                    $link = JRoute::_('index.php?' . $query, false);
                }
            }
        }

        echo '<br /><br />';
        echo '#' . $msg . '#<br />';
        $this->setRedirect($link, $msg);
    }

    /**
     * sportsmanagementControllerPredictionEntry::select()
     * 
     * @return void
     */
    function select() {
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;

        JSession::checkToken() or jexit(\JText::_('JINVALID_TOKEN'));
        $pID = JFactory::getApplication()->input->getVar('prediction_id', '', 'post', 'int');
        $uID = JFactory::getApplication()->input->getVar('uid', null, 'post', 'int');
        if (empty($uID)) {
            $uID = null;
        }
        $link = JSMPredictionHelperRoute::getPredictionTippEntryRoute($pID, $uID);
        //echo '<br />' . $link . '<br />';
        $this->setRedirect($link);
    }

    /**
     * sportsmanagementControllerPredictionEntry::selectprojectround()
     * 
     * @return void
     */
    function selectprojectround() {
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;

        JSession::checkToken() or jexit(\JText::_('JINVALID_TOKEN'));
        //$post	= JFactory::getApplication()->input->post->getArray(array());
        $pID = $jinput->get('prediction_id', 0, '');
        $groupID = $jinput->get('pggroup', 0, '');
        $pjID = $jinput->get('pj', 0, '');
        $rID = $jinput->get('r', 0, '');
        $uID = $jinput->get('uid', 0, '');

//		$pID	= JFactory::getApplication()->input->getVar('prediction_id',	null,	'post',	'int');
//        
//        // diddipoeler
//		//$pjID	= JFactory::getApplication()->input->getVar('project_id',	null,	'post',	'int');
//        $pjID	= JFactory::getApplication()->input->getVar('p',	null,	'post',	'int');
//        
//		$rID	= JFactory::getApplication()->input->getVar('r',				null,	'post',	'int');
//		$uID	= JFactory::getApplication()->input->getVar('uid',			null,	'post',	'int');
        $link = JSMPredictionHelperRoute::getPredictionTippEntryRoute($pID, $uID, $rID, $pjID, $groupID);
        $this->setRedirect($link);
    }

    /**
     * Proxy for getModel
     *
     * @param	string	$name	The model name. Optional.
     * @param	string	$prefix	The class prefix. Optional.
     *
     * @return	object	The model.
     * @since	1.6
     */
    function getModel($name = 'predictionentry', $prefix = 'sportsmanagementModel', $config = array('ignore_request' => true)) {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }

    /**
     * sportsmanagementControllerPredictionEntry::addtipp()
     * 
     * @return void
     */
    function addtipp() {
        JSession::checkToken() or jexit(\JText::_('JINVALID_TOKEN'));

        $app = JFactory::getApplication();
        $document = JFactory::getDocument();

        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        $post = $jinput->post->getArray();
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' post<br><pre>'.print_r($post,true).'</pre>'),'');
        //$optiontext = strtoupper(JFactory::getApplication()->input->getCmd('option').'_');

        $msg = '';
        $link = '';

        $predictionGameID = $jinput->getVar('prediction_id', '', 'post', 'int');
        $joomlaUserID = $jinput->getVar('user_id', '', 'post', 'int');
        $memberID = $jinput->getVar('memberID', '', 'post', 'int');
        $round_id = $jinput->getVar('round_id', '', 'post', 'int');
        $pjID = $jinput->getVar('pjID', '', 'post', 'int');
        $set_r = $jinput->getVar('set_r', '', 'post', 'int');
        $set_pj = $jinput->getVar('set_pj', '', 'post', 'int');

        $model = $this->getModel('Prediction');
        $user = JFactory::getUser();
        $isMember = $model->checkPredictionMembership();
        $allowedAdmin = $model->getAllowed();

        if (( ( $user->id != $joomlaUserID ) ) && (!$allowedAdmin )) {
            $msg .= JText::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_CONTROLLER_ERROR_1');
            $link = JFactory::getURI()->toString();
        } else {
            if ((!$isMember ) && (!$allowedAdmin )) {
                $msg .= JText::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_CONTROLLER_ERROR_2');
                $link = JFactory::getURI()->toString();
            } else {
                if ($pjID != $set_pj) {
                    $params = array('option' => 'com_sportsmanagement',
                        'view' => 'predictionentry',
                        'prediction_id' => $predictionGameID,
                        'pj' => $set_pj
                    );

                    $query = sportsmanagementHelperRoute::buildQuery($params);
                    $link = JRoute::_('index.php?' . $query, false);
                    $this->setRedirect($link);
                }

                if ($round_id != $set_r) {
                    $params = array('option' => 'com_sportsmanagement',
                        'view' => 'predictionentry',
                        'prediction_id' => $predictionGameID,
                        'r' => $set_r,
                        'pj' => $pjID
                    );

                    $query = sportsmanagementHelperRoute::buildQuery($params);
                    $link = JRoute::_('index.php?' . $query, false);
                    $this->setRedirect($link);
                }

                $model = $this->getModel('PredictionEntry');
                if (!$model->savePredictions($allowedAdmin)) {
                    $msg .= JText::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_CONTROLLER_ERROR_3');
                    $link = JFactory::getURI()->toString();
                } else {
                    $msg .= JText::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_CONTROLLER_MSG_1');
                    $link = JFactory::getURI()->toString();
                }
            }
        }

        //echo '<br />' . $link . '<br />';
        //echo '<br />' . $msg . '<br />';

        $this->setRedirect($link, $msg);
    }

}

?>