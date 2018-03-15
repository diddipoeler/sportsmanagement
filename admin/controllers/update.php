<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
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

defined('_JEXEC') or die('Restricted access'); 

jimport('joomla.application.component.controller');
jimport('joomla.filesystem.file');


/**
 * sportsmanagementControllerUpdate
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementControllerUpdate extends JControllerLegacy
{

	/**
	 * sportsmanagementControllerUpdate::__construct()
	 * 
	 * @return void
	 */
	function __construct()
	{
		// Register Extra tasks
		parent::__construct();
	}


	/**
	 * sportsmanagementControllerUpdate::display()
	 * 
	 * @param bool $cachable
	 * @param bool $urlparams
	 * @return void
	 */
	function display($cachable = false, $urlparams = false)
	{
//		$document = JFactory::getDocument();
//		$model=$this->getModel('updates');
		$viewType=$document->getType();
		$view=$this->getView('updates',$viewType);
//		$view->setModel($model,true);	
		$view->setLayout('updates');

		parent::display();
	}


	/**
	 * sportsmanagementControllerUpdate::save()
	 * 
	 * @return void
	 */
	function save()
	{
		//JToolbarHelper::back(JText::_('COM_SPORTSMANAGEMENT_BACK_UPDATELIST'),JRoute::_('index.php?option=com_sportsmanagement&view=updates&task=update.display'));
		$post = JFactory::getApplication()->input->post->getArray(array());
		$file_name = JFactory::getApplication()->input->getVar('file_name');
		$path = explode('/',$file_name);
		if (count($path) > 1)
		{
			$filepath = JPATH_COMPONENT_SITE.DS.'extensions'.DS.$path[0].DS.'admin'.DS.'install'.DS.$path[1];
		}
		else
		{
			$filepath = JPATH_COMPONENT_ADMINISTRATOR.DS.'assets'.DS.'updates'.DS.$path[0];
		}
		$model = $this->getModel('updates');
		echo JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_UPDATES_FROM_FILE','<b>'.$filepath.'</b>');
		if (JFile::exists($filepath))
		{
			$model->loadUpdateFile($filepath,$file_name);
		}
		else
		{
			echo JText::_('Update file not found!');
		}
	}
}
?>