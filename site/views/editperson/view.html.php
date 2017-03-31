<?php
/** SportsManagement ein Programm zur Verwaltung f?r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: ? 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license                This file is part of SportsManagement.
*
* SportsManagement is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* SportsManagement is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with SportsManagement.  If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von SportsManagement.
*
* SportsManagement ist Freie Software: Sie k?nnen es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder sp?teren
* ver?ffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es n?tzlich sein wird, aber
* OHNE JEDE GEW?HELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gew?hrleistung der MARKTF?HIGKEIT oder EIGNUNG F?R EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License f?r weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
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
class sportsmanagementViewEditPerson extends JViewLegacy
{
    
	/**
	 * sportsmanagementViewEditPerson::display()
	 * 
	 * @param mixed $tpl
	 * @return void
	 */
	function display($tpl=null)
	{
		
		$option = JRequest::getCmd('option');
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		$uri = JFactory::getURI();
		$user = JFactory::getUser();
        
        $params         = $app->getParams();
        $dispatcher = JDispatcher::getInstance();
        
        // Get some data from the models
        $state          = $this->get('State');
        $this->item           = $this->get('Item');
        $this->form     = $this->get('Form');
        
        $this->form->setValue('sports_type_id', 'request', $this->item->sports_type_id);
        $this->form->setValue('position_id', 'request', $this->item->position_id);
        $this->form->setValue('agegroup_id', 'request', $this->item->agegroup_id);
        
        $this->form->setValue('person_art', 'request', $this->item->person_art);
        $this->form->setValue('person_id1', 'request', $this->item->person_id1);
        $this->form->setValue('person_id2', 'request', $this->item->person_id2);
        
        $extended = sportsmanagementHelper::getExtended($this->item->extended, 'person');
		$this->extended = $extended; 
        
        $this->checkextrafields = sportsmanagementHelper::checkUserExtraFields('frontend',$model::$cfg_which_database);
        if ( $this->checkextrafields )
        {
            $lists['ext_fields'] = sportsmanagementHelper::getUserExtraFields($this->item->id,'frontend',$model::$cfg_which_database);
        }
               
 
                // Check for errors.
                if (count($errors = $this->get('Errors'))) 
                {
                        JError::raiseError(500, implode('<br />', $errors));
                        return false;
                }

		
		parent::display($tpl);
	}

}
?>