<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      editmatch.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage editmatch
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Include dependancy of the main controllerform class
jimport('joomla.application.component.controllerform');

/**
 * sportsmanagementControllerEditMatch
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2016
 * @version $Id$
 * @access public
 */
class sportsmanagementControllerEditMatch extends JControllerForm {

    /**
     * sportsmanagementControllerEditMatch::__construct()
     * 
     * @param mixed $config
     * @return void
     */
    function __construct($config = array()) {
        parent::__construct($config);

        // Map the apply task to the save method.
        $this->registerTask('apply', 'save');
    }

    /**
     * sportsmanagementControllerEditMatch::getModel()
     * 
     * @param string $name
     * @param string $prefix
     * @param mixed $config
     * @return
     */
    public function getModel($name = '', $prefix = '', $config = array('ignore_request' => true)) {
        return parent::getModel($name, $prefix, array('ignore_request' => false));
    }

    /**
     * sportsmanagementControllerEditMatch::saveReferees()
     * 
     * @return void
     */
    function saveReferees() {
        $app = JFactory::getApplication();
        $post = $app->input->post->getArray(array());

        $model = $this->getModel('editmatch');
        $return = $model->updateReferees($post);
        $routeparameter = array();
        $routeparameter['cfg_which_database'] = $post['cfg_which_database'];
        $routeparameter['s'] = $post['s'];
        $routeparameter['p'] = $post['p'];
        $routeparameter['r'] = $post['r'];
        $routeparameter['division'] = $post['division'];
        $routeparameter['mode'] = 0;
        $routeparameter['order'] = 0;
        $routeparameter['layout'] = 'form';
        $link = sportsmanagementHelperRoute::getSportsmanagementRoute('results', $routeparameter);
        $msg = JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_SAVED');

        $this->setRedirect($link, $msg);
    }

    /**
     * sportsmanagementControllerEditMatch::save()
     * 
     * @return void
     */
    function save() {
        $app = JFactory::getApplication();
        $post = $app->input->post->getArray(array());

        $model = $this->getModel('editmatch');
        $return = $model->updateRoster($post);
        $return = $model->updateStaff($post);

        $link = $_SERVER['HTTP_REFERER'];
        $msg = JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_SAVED');

        $this->setRedirect($link, $msg);
    }

    /**
     * sportsmanagementControllerEditMatch::saveshort()
     * 
     * @return void
     */
    function saveshort() {
        $app = JFactory::getApplication();
        $date = JFactory::getDate();
        $user = JFactory::getUser();
        $post = JFactory::getApplication()->input->post->getArray(array());
        $option = JFactory::getApplication()->input->getCmd('option');
        
        /* Ein Datenbankobjekt beziehen */
        $db = JFactory::getDbo();
        
        // Set the values
        $data['modified'] = $date->toSql();
        $data['modified_by'] = $user->get('id');
        $data['id'] = $post['matchid'];

        $data['cancel'] = $post['cancel'];
        $data['cancel_reason'] = $post['cancel_reason'];
        $data['playground_id'] = $post['playground_id'];
        $data['overtime'] = $post['overtime'];
        $data['count_result'] = $post['count_result'];
        $data['alt_decision'] = $post['alt_decision'];
        $data['team_won'] = $post['team_won'];
        $data['preview'] = $post['preview'];
        $data['team1_bonus'] = $post['team1_bonus'];
        $data['team2_bonus'] = $post['team2_bonus'];
        $data['match_result_detail'] = $post['match_result_detail'];

        $data['show_report'] = $post['show_report'];

        $data['summary'] = $post['summary'];
        $data['old_match_id'] = $post['old_match_id'];
        $data['new_match_id'] = $post['new_match_id'];

        if (isset($post['extended']) && is_array($post['extended'])) {
            // Convert the extended field to a string.
            $parameter = new JRegistry;
            $parameter->loadArray($post['extended']);
            $data['extended'] = (string) $parameter;
        }

        $data['team1_result_decision'] = $post['team1_result_decision'];
        $data['team2_result_decision'] = $post['team2_result_decision'];
        $data['decision_info'] = $post['decision_info'];

/**
 * Create an object for the record we are going to update.
 */
        $object = new stdClass();
        foreach ($data as $key => $value) {
            $object->$key = $value;
        }

/**
 * Update their details in the table using id as the primary key.
 */
        $result_update = JFactory::getDbo()->updateObject('#__sportsmanagement_match', $object, 'id', true);

        $routeparameter = array();
        $routeparameter['cfg_which_database'] = $post['cfg_which_database'];
        $routeparameter['s'] = $post['s'];
        $routeparameter['p'] = $post['p'];
        $routeparameter['r'] = $post['r'];
        $routeparameter['division'] = $post['division'];
        $routeparameter['mode'] = $post['mode'];
        $routeparameter['order'] = $post['order'];
        $routeparameter['layout'] = $post['oldlayout'];
        $link = sportsmanagementHelperRoute::getSportsmanagementRoute('results', $routeparameter);
        $msg = JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_SAVED');

        //$url = sportsmanagementHelperRoute::getEditMatchRoute($post['p'],$post['matchid']);
        $this->setRedirect($link, $msg);
    }

}

?>
