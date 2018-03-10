<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage editperson
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

/**
 * sportsmanagementViewEditPerson
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewEditPerson extends JViewLegacy {

    /**
     * sportsmanagementViewEditPerson::display()
     * 
     * @param mixed $tpl
     * @return void
     */
    function display($tpl = null) {

        $option = JFactory::getApplication()->input->getCmd('option');
        $app = JFactory::getApplication();
        $db = JFactory::getDBO();
        if (version_compare(JSM_JVERSION, '4', 'eq')) {
            $uri = JUri::getInstance();
        } else {
            $uri = JFactory::getURI();
        }
        $user = JFactory::getUser();

        $params = $app->getParams();
        $dispatcher = JDispatcher::getInstance();

        // Get some data from the models
        $state = $this->get('State');
        $this->item = $this->get('Item');
        $this->form = $this->get('Form');

        $this->form->setValue('sports_type_id', 'request', $this->item->sports_type_id);
        $this->form->setValue('position_id', 'request', $this->item->position_id);
        $this->form->setValue('agegroup_id', 'request', $this->item->agegroup_id);

        $this->form->setValue('person_art', 'request', $this->item->person_art);
        $this->form->setValue('person_id1', 'request', $this->item->person_id1);
        $this->form->setValue('person_id2', 'request', $this->item->person_id2);

        $extended = sportsmanagementHelper::getExtended($this->item->extended, 'person');
        $this->extended = $extended;

        $this->checkextrafields = sportsmanagementHelper::checkUserExtraFields('frontend', $model::$cfg_which_database);
        if ($this->checkextrafields) {
            $lists['ext_fields'] = sportsmanagementHelper::getUserExtraFields($this->item->id, 'frontend', $model::$cfg_which_database);
        }


        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode('<br />', $errors));
            return false;
        }


        parent::display($tpl);
    }

}

?>
