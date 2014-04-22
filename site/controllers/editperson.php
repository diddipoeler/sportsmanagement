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

// No direct access.
defined('_JEXEC') or die;
 
// Include dependancy of the main controllerform class
jimport('joomla.application.component.controllerform');
 
/**
 * sportsmanagementControllereditperson
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class sportsmanagementControllereditperson extends JControllerForm
{
 
 /**
	 * Class Constructor
	 *
	 * @param	array	$config		An optional associative array of configuration settings.
	 * @return	void
	 * @since	1.5
	 */
	function __construct($config = array())
	{
		parent::__construct($config);

		// Map the apply task to the save method.
		$this->registerTask('apply', 'save');
	}
    
        public function getModel($name = '', $prefix = '', $config = array('ignore_request' => true))
        {
                return parent::getModel($name, $prefix, array('ignore_request' => false));
        }
 
        public function submit()
        {
//                // Check for request forgeries.
//                JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
// 
//                // Initialise variables.
//                $app    = JFactory::getApplication();
//                $model  = $this->getModel('updhelloworld');
// 
//                // Get the data from the form POST
//                $data = JRequest::getVar('jform', array(), 'post', 'array');
// 
//        // Now update the loaded data to the database via a function in the model
//        $upditem        = $model->updItem($data);
// 
//        // check if ok and display appropriate message.  This can also have a redirect if desired.
//        if ($upditem) {
//            echo "<h2>Updated Greeting has been saved</h2>";
//        } else {
//            echo "<h2>Updated Greeting failed to be saved</h2>";
//        }
 
                return true;
        }
        
        public function save()
        {
            $data	= JRequest::getVar('jform', array(), 'post', 'array');
		    $id		= JRequest::getInt('id');
        
//            $msg = 'save';
//            $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component',$msg);
            
            // Set the redirect based on the task.
		switch ($this->getTask())
		{
			case 'apply':
				$message = JText::_('COM_SPORTSMANAGEMENT_SAVE_SUCCESS');
				$this->setRedirect('index.php?option=com_sportsmanagement&view=editperson&tmpl=component&id='.$id, $message);
				break;

			case 'save':
			default:
				$this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component');
				break;
		}

 
                return true;
        }
        
//        public function apply()
//        {
////            $msg = 'apply';
////            $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component',$msg);
//
// 
//                return true;
//        }
 
}