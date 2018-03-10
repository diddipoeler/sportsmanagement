<?php
/** Joomla Sports Management ein Programm zur Verwaltung für alle Sportarten
* @version 1.0.26
* @file		administrator/components/sportsmanagement/models/divisions.php
* @author diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
* @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license This file is part of Joomla Sports Management.
*
* Joomla Sports Management is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Joomla Sports Management is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Joomla Sports Management. If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von Joomla Sports Management.
*
* Joomla Sports Management ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* Joomla Sports Management wird in der Hoffnung, dass es nützlich sein wird, aber
* OHNE JEDE GEWÄHRLEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

defined('_JEXEC') or die();

//JLoader::import('components.com_sportsmanagement.libraries.GCalendar.view', JPATH_ADMINISTRATOR);
jimport('joomla.application.component.view');

//class sportsmanagemantViewjsmgcalendars extends GCalendarView
/**
 * sportsmanagementViewjsmgcalendars
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2015
 * @version $Id$
 * @access public
 */
class sportsmanagementViewjsmgcalendars extends sportsmanagementView 
{


/**
 * sportsmanagementViewjsmgcalendars::init()
 * 
 * @return void
 */
public function init ()
	{
//		$app = JFactory::getApplication();
//		$jinput = $app->input;
//		$option = $jinput->getCmd('option');
//		$uri = JFactory::getUri();
//        
//        $this->items = $this->get('Items');
//		$this->pagination = $this->get('Pagination');
        
        //$this->addToolbar();
        
        //parent::display($tpl);
        }
//	protected $icon = 'calendar';
//	protected $title = 'COM_GCALENDAR_MANAGER_GCALENDAR';
//
//	protected $items = null;
//	protected $pagination = null;

	/**
	 * sportsmanagementViewjsmgcalendars::addToolbar()
	 * 
	 * @return void
	 */
	protected function addToolbar() 
    {
		$jinput = JFactory::getApplication()->input;
        $option = $jinput->getCmd('option');
        $canDo = jsmGCalendarUtil::getActions();
		if ($canDo->get('core.create')) {
			JToolbarHelper::addNew('jsmgcalendar.add', 'JTOOLBAR_NEW');
			JToolbarHelper::custom('jsmgcalendarimport.import', 'upload.png', 'upload.png', 'COM_SPORTSMANAGEMENT_JSMGCALENDAR_VIEW_GCALENDARS_BUTTON_IMPORT', false);
		}

//		if ($canDo->get('core.edit')) {
//			JToolbarHelper::editList('jsmgcalendar.edit', 'JTOOLBAR_EDIT');
//		}
//		if ($canDo->get('core.delete')) {
//			JToolbarHelper::deleteList('', 'jsmgcalendars.delete', 'JTOOLBAR_DELETE');
//		}
        
        $this->icon = 'google-calendar-48-icon.png';
        
//        JToolbarHelper::divider();
//        sportsmanagementHelper::ToolbarButtonOnlineHelp();
//		JToolbarHelper::preferences($option);

		parent::addToolbar();
	}

//	protected function init() 
//    {
//		$this->items = $this->get('Items');
//		$this->pagination = $this->get('Pagination');
//	}
}