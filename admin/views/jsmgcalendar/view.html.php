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
jimport('joomla.application.component.view');
//JLoader::import('components.com_gcalendar.libraries.GCalendar.view', JPATH_ADMINISTRATOR);

//class GCalendarViewGCalendar extends GCalendarView
/**
 * sportsmanagementViewjsmgcalendar
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class sportsmanagementViewjsmgcalendar extends sportsmanagementView  
{


/**
 * sportsmanagementViewjsmgcalendar::init()
 * 
 * @param mixed $tpl
 * @return void
 */
function init( $tpl = null )
	{
		$app = JFactory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
		$db	= sportsmanagementHelper::getDBConnection();
		$uri = JFactory::getURI();
		$user = JFactory::getUser();
		$model = $this->getModel();
        $starttime = microtime(); 
        
        // get the Data
		$form = $this->get('Form');
		$item = $this->get('Item');
        
        // Assign the Data
		$this->form = $form;
		$this->gcalendar = $item;
        
        // bei neuanlage user und passwort aus der konfiguration der komponente nehmen
        if ($this->gcalendar->id < 1) 
        {
            $this->form->setValue('username', null, JComponentHelper::getParams(JFactory::getApplication()->input->getCmd('option'))->get('google_mail_account',''));
            $this->form->setValue('password', null, JComponentHelper::getParams(JFactory::getApplication()->input->getCmd('option'))->get('google_mail_password',''));
        }
            
        //$this->addToolbar();
        
        //parent::display($tpl);
        
     }   
//	protected $gcalendar = null;
//	protected $form = null;
//
	/**
	* Add the page title and toolbar.
	*
	* @since	1.7
	*/
    protected function addToolbar() 
    {
		//JFactory::getApplication()->input->setVar('hidemainmenu', true);
        $app = JFactory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
		$canDo = jsmGCalendarUtil::getActions($this->gcalendar->id);
		if ($this->gcalendar->id < 1) 
        {
            JToolbarHelper::title(JText::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_INSERT_NEW_GOOGLE'),'gcalendar');
            
			if ($canDo->get('core.create')) 
            {
                $app->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_INSERT_ON_GOOGLE'),'Notice');
                
                $this->gcalendar->username = JComponentHelper::getParams(JFactory::getApplication()->input->getCmd('option'))->get('google_mail_account','');
                $this->gcalendar->password = JComponentHelper::getParams(JFactory::getApplication()->input->getCmd('option'))->get('google_mail_password','');
            
				JToolbarHelper::apply('jsmgcalendar.apply', 'JTOOLBAR_APPLY');
				JToolbarHelper::save('jsmgcalendar.save', 'JTOOLBAR_SAVE');
				JToolbarHelper::custom('jsmgcalendar.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
			}
			JToolbarHelper::cancel('jsmgcalendar.cancel', 'JTOOLBAR_CANCEL');
		} 
        else 
        {
            JToolbarHelper::title(JText::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_EDIT_NEW_GOOGLE'),'gcalendar');
            
			if ($canDo->get('core.edit')) 
            {
				JToolbarHelper::publish('jsmgcalendar.insertgooglecalendar', 'JLIB_HTML_CALENDAR');
                
                JToolbarHelper::apply('jsmgcalendar.apply', 'JTOOLBAR_APPLY');
				JToolbarHelper::save('jsmgcalendar.save', 'JTOOLBAR_SAVE');

				if ($canDo->get('core.create')) 
                {
					JToolbarHelper::custom('jsmgcalendar.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
				}
			}
			if ($canDo->get('core.create')) 
            {
				JToolbarHelper::custom('jsmgcalendar.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
			}
			JToolbarHelper::cancel('jsmgcalendar.cancel', 'JTOOLBAR_CLOSE');
		}
        
        JToolbarHelper::divider();
        sportsmanagementHelper::ToolbarButtonOnlineHelp();
		JToolbarHelper::preferences($option);

		parent::addToolbar();
	}
//
//	protected function init() {
//		$this->form = $this->get('Form');
//		$this->gcalendar = $this->get('Item');
//	}
}