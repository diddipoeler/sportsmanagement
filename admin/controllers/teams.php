<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
* @version   1.0.05
* @file      teams.php
* @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
* @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license   This file is part of SportsManagement.
* @package   sportsmanagement
* @subpackage controllers
*/

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla controlleradmin library
jimport('joomla.application.component.controlleradmin');

/**
 * sportsmanagementControllerteams
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementControllerteams extends JControllerAdmin {

    /**
     * Save the manual order inputs from the categories list page.
     *
     * @return	void
     * @since	1.6
     */
    public function saveorder() {
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        // Get the arrays from the Request
        $order = JFactory::getApplication()->input->getVar('order', null, 'post', 'array');
        $originalOrder = explode(',', JFactory::getApplication()->input->getString('original_order_values'));

        // Make sure something has changed
        if (!($order === $originalOrder)) {
            parent::saveorder();
        } else {
            // Nothing to reorder
            $this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
            return true;
        }
    }

    /**
     * sportsmanagementControllerteams::saveshort()
     * 
     * @return void
     */
    function saveshort() {
        $model = $this->getModel();
        $msg = $model->saveshort();
        //$this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_list, false));
        $this->setRedirect('index.php?option=com_sportsmanagement&view=teams', $msg);
    }

    /**
     * Proxy for getModel.
     * @since	1.6
     */
    public function getModel($name = 'Team', $prefix = 'sportsmanagementModel', $config = Array()) {
        $model = parent::getModel($name, $prefix, array('ignore_request' => true));
        return $model;
    }

}
