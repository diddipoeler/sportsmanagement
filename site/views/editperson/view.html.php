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
use Joomla\CMS\Uri\Uri;

/**
 * sportsmanagementViewEditPerson
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewEditPerson extends sportsmanagementView {

    
    /**
     * sportsmanagementViewEditPerson::init()
     * 
     * @return
     */
    function init() {

       
$this->item = $this->model->getData();
        $this->form = $this->get('Form');

        $this->form->setValue('sports_type_id', 'request', $this->item->sports_type_id);
        $this->form->setValue('position_id', 'request', $this->item->position_id);
        $this->form->setValue('agegroup_id', 'request', $this->item->agegroup_id);

        $this->form->setValue('person_art', 'request', $this->item->person_art);
        $this->form->setValue('person_id1', 'request', $this->item->person_id1);
        $this->form->setValue('person_id2', 'request', $this->item->person_id2);

        $extended = sportsmanagementHelper::getExtended($this->item->extended, 'person');
        $this->extended = $extended;

        $this->checkextrafields = sportsmanagementHelper::checkUserExtraFields('frontend', $this->cfg_which_database);
        if ($this->checkextrafields) {
            $lists['ext_fields'] = sportsmanagementHelper::getUserExtraFields($this->item->id, 'frontend', $this->cfg_which_database);
        }

    }

}

?>
