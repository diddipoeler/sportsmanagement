<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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
* SportsManagement ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es nützlich sein wird, aber
* OHNE JEDE GEWÄHELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla controllerform library
jimport('joomla.application.component.controllerform');
 

/**
 * sportsmanagementControllersmquotetxt
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementControllersmquotetxt extends JControllerForm
{
    
    /**
	 * Constructor.
	 *
	 * @param	array An optional associative array of configuration settings.
	 * @see		JController
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		// Apply, Save & New, and Save As copy should be standard on forms.
		$this->registerTask('apply','save');
	}
    
    /**
     * sportsmanagementControllersmquotetxt::cancel()
     * 
     * @return void
     */
    public function cancel()
	{
    // Redirect to the list screen.
				$this->setRedirect(JRoute::_('index.php?option=com_sportsmanagement&view=smquotestxt&layout=default', false));
    }
    
    /**
	 * Saves a template source file.
	 */
	public function save()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialise variables.
		$app		= JFactory::getApplication();
		$data		= JFactory::getApplication()->input->getVar('jform', array(), 'post', 'array');
		//$context	= 'com_templates.edit.source';
		$task		= $this->getTask();
		$model		= $this->getModel();
        $model->save($data);
        
        switch ($task)
		{
			case 'apply':
				// Reset the record data in the session.
				//$app->setUserState($context.'.data',	null);

				// Redirect back to the edit screen.
				$this->setRedirect(JRoute::_('index.php?option=com_sportsmanagement&view=smquotetxt&layout=default&file_name='.$data['filename'], false));
				break;

			default:
				// Clear the record id and data from the session.
				//$app->setUserState($context.'.id', null);
				//$app->setUserState($context.'.data', null);

				// Redirect to the list screen.
				$this->setRedirect(JRoute::_('index.php?option=com_sportsmanagement&view=smquotestxt&layout=default', false));
				break;
		}
        
        
        
    } 
    
    /**
	 * Method to get a model object, loading it if required.
	 *
	 * @param	string	The model name. Optional.
	 * @param	string	The class prefix. Optional.
	 * @param	array	Configuration array for model. Optional (note, the empty array is atypical compared to other models).
	 *
	 * @return	object	The model.
	 */
	public function getModel($name = 'smquotetxt', $prefix = 'sportsmanagementModel', $config = array())
	{
		$model = parent::getModel($name, $prefix, $config);
		return $model;
	}   



}
