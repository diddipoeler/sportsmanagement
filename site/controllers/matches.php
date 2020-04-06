<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       matches.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage editmatch
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
require_once JPATH_ADMINISTRATOR .DIRECTORY_SEPARATOR. JSM_PATH .DIRECTORY_SEPARATOR. 'models' .DIRECTORY_SEPARATOR. 'match.php';

/**
 * sportsmanagementControllermatches
 *
 * @package
 * @author    Dieter Plöger
 * @copyright 2018
 * @version   $Id$
 * @access    public
 */
class sportsmanagementControllermatches extends BaseController
{

    /**
     * sportsmanagementControllermatches::__construct()
     *
     * @return void
     */
    function __construct()
    {
        parent::__construct();
    }

    /**
     * Returns a reference to the global {@link JoomlaTuneAjaxResponse} object,
     * only creating it if it doesn't already exist.
     *
     * @return JoomlaTuneAjaxResponse
     */
    public static function getAjaxResponse()
    {
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
    function saveevent()
    {
        $option = Factory::getApplication()->input->getCmd('option');
        $data = array();
        $data['teamplayer_id'] = Factory::getApplication()->input->getInt('teamplayer_id');
        $data['projectteam_id'] = Factory::getApplication()->input->getInt('projectteam_id');
        $data['event_type_id'] = Factory::getApplication()->input->getInt('event_type_id');
        $data['event_time'] = Factory::getApplication()->input->getVar('event_time', '');
        $data['match_id'] = Factory::getApplication()->input->getInt('match_id');
        $data['event_sum'] = Factory::getApplication()->input->getVar('event_sum', '');
        $data['notice'] = Factory::getApplication()->input->getVar('notice', '');
        $data['notes'] = Factory::getApplication()->input->getVar('notes', '');
        $data['useeventtime'] = Factory::getApplication()->input->getVar('useeventtime', '');

        // diddipoeler
        $data['projecttime'] = Factory::getApplication()->input->getVar('projecttime', '');


        if (!$result = sportsmanagementModelMatch::saveevent($data)) {
            //$result = "0" . "&" . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_SAVED_EVENT') . ': ' . sportsmanagementModelMatch::getError();
            $result = "0" . "&" . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_SAVED_EVENT') . ': ';
        } else {
            $result = $result . "&" . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_SAVED_EVENT');
        }

        echo json_encode($result);
        Factory::getApplication()->close();
    }

    /**
     * sportsmanagementControllermatches::savesubst()
     *
     * @return void
     */
    function savesubst()
    {
        $data = array();
        $data['in'] = Factory::getApplication()->input->getInt('in');
        $data['out'] = Factory::getApplication()->input->getInt('out');
        $data['matchid'] = Factory::getApplication()->input->getInt('matchid');
        $data['in_out_time'] = Factory::getApplication()->input->getVar('in_out_time', '');
        $data['project_position_id'] = Factory::getApplication()->input->getInt('project_position_id');
        // diddipoeler
        $data['projecttime'] = Factory::getApplication()->input->getVar('projecttime', '');

        if (!$result = sportsmanagementModelMatch::savesubstitution($data)) {
            //$result = "0" . "&" . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_SAVED_SUBST') . ': ' . sportsmanagementModelMatch::getError();
            $result = "0" . "&" . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_SAVED_SUBST') . ': ';
        } else {
            $result = $result . "&" . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_SAVED_SUBST');
        }
        echo json_encode($result);
        Factory::getApplication()->close();
    }

    /**
     * sportsmanagementControllermatches::removeSubst()
     *
     * @return void
     */
    function removeSubst()
    {
        $substid = Factory::getApplication()->input->getInt('substid', 0);

        if (!$result = sportsmanagementModelMatch::removeSubstitution($substid)) {
            //$result = "0" . "&" . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_REMOVE_SUBST') . ': ' . sportsmanagementModelMatch::getError();
            $result = "0" . "&" . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_REMOVE_SUBST') . ': ';
        } else {
            $result = "1" . "&" . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_REMOVE_SUBST') . '&' . $substid;
        }
        echo json_encode($result);
        Factory::getApplication()->close();
    }

    /**
     * sportsmanagementControllermatches::savecomment()
     *
     * @return void
     */
    function savecomment()
    {
        $data = array();
        $data['event_time'] = Factory::getApplication()->input->getVar('event_time', '');
        $data['match_id'] = Factory::getApplication()->input->getInt('matchid');
        $data['type'] = Factory::getApplication()->input->getVar('type', '');
        $data['notes'] = Factory::getApplication()->input->getVar('notes', '');

        // diddipoeler
        $data['projecttime'] = Factory::getApplication()->input->getVar('projecttime', '');


        if (!$result = sportsmanagementModelMatch::savecomment($data)) {
            //$result = '0&' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_SAVED_COMMENT') . ': ' . sportsmanagementModelMatch::getError();
            $result = '0&' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_SAVED_COMMENT') . ': ';
        } else {
            $result = $result . '&' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_SAVED_COMMENT');
        }
        echo json_encode($result);
        Factory::getApplication()->close();
    }

    /**
     * sportsmanagementControllermatches::removeEvent()
     *
     * @return void
     */
    function removeEvent()
    {
        $event_id = Factory::getApplication()->input->getInt('event_id');
        if (!$result = sportsmanagementModelMatch::deleteevent($event_id)) {
               //$result="0"."&".Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_DELETE_EVENTS').': '.$model->getError();
            $result="0"."&".Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_DELETE_EVENTS').': ';
        }
        else
        {
               $result="1"."&".Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_DELETE_EVENTS').'&'.$event_id;
        }
        echo json_encode($result);
        Factory::getApplication()->close();
    }

    /**
     * sportsmanagementControllermatches::removeCommentary()
     *
     * @return void
     */
    public function removeCommentary()
    {
        $event_id = Factory::getApplication()->input->getInt('event_id');

        if (!$result = sportsmanagementModelMatch::deletecommentary($event_id)) {
            //$result = '0' . '&' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_DELETE_COMMENTARY') . ': ' . sportsmanagementModelMatch::getError();
            $result = '0' . '&' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_DELETE_COMMENTARY') . ': ';
        } else {
            $result = '1' . '&' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_DELETE_COMMENTARY') . '&' . $event_id;
        }
        echo json_encode($result);
        Factory::getApplication()->close();
    }

}
