<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      matches.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage editmatch
 */
 
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

/**
 * sportsmanagementControllermatches
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2018
 * @version $Id$
 * @access public
 */
class sportsmanagementControllermatches extends JControllerLegacy {

    /**
     * sportsmanagementControllermatches::__construct()
     * 
     * @return void
     */
    function __construct() {
        parent::__construct();
    }

    /**
     * Returns a reference to the global {@link JoomlaTuneAjaxResponse} object,
     * only creating it if it doesn't already exist.
     *
     * @return JoomlaTuneAjaxResponse
     */
    public static function getAjaxResponse() {
        static $instance = null;

        if (!is_object($instance)) {
            $instance = new JoomlaTuneAjaxResponse('utf-8');
        }

        return $instance;
    }

    /**
     * sportsmanagementControllermatches::saveevent()
     * 
     * @return void
     */
    function saveevent() {
        $option = JFactory::getApplication()->input->getCmd('option');
        $data = array();
        $data['teamplayer_id'] = JFactory::getApplication()->input->getInt('teamplayer_id');
        $data['projectteam_id'] = JFactory::getApplication()->input->getInt('projectteam_id');
        $data['event_type_id'] = JFactory::getApplication()->input->getInt('event_type_id');
        $data['event_time'] = JFactory::getApplication()->input->getVar('event_time', '');
        $data['match_id'] = JFactory::getApplication()->input->getInt('match_id');
        $data['event_sum'] = JFactory::getApplication()->input->getVar('event_sum', '');
        $data['notice'] = JFactory::getApplication()->input->getVar('notice', '');
        $data['notes'] = JFactory::getApplication()->input->getVar('notes', '');

        // diddipoeler
        $data['projecttime'] = JFactory::getApplication()->input->getVar('projecttime', '');


        if (!$result = sportsmanagementModelMatch::saveevent($data)) {
            $result = "0" . "&" . JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_SAVED_EVENT') . ': ' . sportsmanagementModelMatch::getError();
        } else {
            $result = $result . "&" . JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_SAVED_EVENT');
        }

        echo json_encode($result);
        JFactory::getApplication()->close();
    }

    /**
     * sportsmanagementControllermatches::savesubst()
     * 
     * @return void
     */
    function savesubst() {
        $data = array();
        $data['in'] = JFactory::getApplication()->input->getInt('in');
        $data['out'] = JFactory::getApplication()->input->getInt('out');
        $data['matchid'] = JFactory::getApplication()->input->getInt('matchid');
        $data['in_out_time'] = JFactory::getApplication()->input->getVar('in_out_time', '');
        $data['project_position_id'] = JFactory::getApplication()->input->getInt('project_position_id');
        // diddipoeler
        $data['projecttime'] = JFactory::getApplication()->input->getVar('projecttime', '');

        if (!$result = sportsmanagementModelMatch::savesubstitution($data)) {
            $result = "0" . "&" . JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_SAVED_SUBST') . ': ' . sportsmanagementModelMatch::getError();
        } else {
            $result = $result . "&" . JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_SAVED_SUBST');
        }
        echo json_encode($result);
        JFactory::getApplication()->close();
    }

    /**
     * sportsmanagementControllermatches::removeSubst()
     * 
     * @return void
     */
    function removeSubst() {
        $substid = JFactory::getApplication()->input->getInt('substid', 0);

        if (!$result = sportsmanagementModelMatch::removeSubstitution($substid)) {
            $result = "0" . "&" . JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_REMOVE_SUBST') . ': ' . sportsmanagementModelMatch::getError();
        } else {
            $result = "1" . "&" . JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_REMOVE_SUBST') . '&' . $substid;
        }
        echo json_encode($result);
        JFactory::getApplication()->close();
    }

    /**
     * sportsmanagementControllermatches::savecomment()
     * 
     * @return void
     */
    function savecomment() {
        $data = array();
        $data['event_time'] = JFactory::getApplication()->input->getVar('event_time', '');
        $data['match_id'] = JFactory::getApplication()->input->getInt('matchid');
        $data['type'] = JFactory::getApplication()->input->getVar('type', '');
        $data['notes'] = JFactory::getApplication()->input->getVar('notes', '');

        // diddipoeler
        $data['projecttime'] = JFactory::getApplication()->input->getVar('projecttime', '');


        if (!$result = sportsmanagementModelMatch::savecomment($data)) {
            $result = '0&' . JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_SAVED_COMMENT') . ': ' . sportsmanagementModelMatch::getError();
        } else {
            $result = $result . '&' . JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_SAVED_COMMENT');
        }
        echo json_encode($result);
        JFactory::getApplication()->close();
    }
 
 /**
     * sportsmanagementControllermatches::removeEvent()
     * 
     * @return void
     */
    function removeEvent()
    {
		$event_id = JFactory::getApplication()->input->getInt('event_id');
		if (!$result = sportsmanagementModelMatch::deleteevent($event_id))
		{
			$result="0"."&".JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_DELETE_EVENTS').': '.$model->getError();
		}
		else
		{
			$result="1"."&".JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_DELETE_EVENTS').'&'.$event_id;
		}
		echo json_encode($result);
		JFactory::getApplication()->close();
    }

    /**
     * sportsmanagementControllermatches::removeCommentary()
     * 
     * @return void
     */
    public function removeCommentary() {
        $event_id = JFactory::getApplication()->input->getInt('event_id');

        if (!$result = sportsmanagementModelMatch::deletecommentary($event_id)) {
            $result = '0' . '&' . JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_DELETE_COMMENTARY') . ': ' . sportsmanagementModelMatch::getError();
        } else {
            $result = '1' . '&' . JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_DELETE_COMMENTARY') . '&' . $event_id;
        }
        echo json_encode($result);
        JFactory::getApplication()->close();
    }

}
