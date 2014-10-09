<?php
/**
 * GCalendar is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * GCalendar is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GCalendar.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package		GCalendar
 * @author		Digital Peak http://www.digital-peak.com
 * @copyright	Copyright (C) 2007 - 2013 Digital Peak. All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();
jimport('joomla.application.component.view');
//JLoader::import('components.com_gcalendar.libraries.GCalendar.view', JPATH_ADMINISTRATOR);

class sportsmanagementViewjsmgcalendarImport extends sportsmanagementView 
{
    
    function display( $tpl = null )
	{
		$app	= JFactory::getApplication();
		$option = JRequest::getCmd('option');
		$db	 		= JFactory::getDBO();
		$uri		= JFactory::getURI();
		$user		= JFactory::getUser();
		$model		= $this->getModel();
        $starttime = microtime(); 
        
        if (strpos($this->getLayout(), 'login') === false) 
        {
			$this->onlineItems = $this->get('OnlineData');
			$this->dbItems = $this->get('DBData');
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' dbItems<br><pre>'.print_r($this->dbItems,true).'</pre>'),'Notice');
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' onlineItems<br><pre>'.print_r($this->onlineItems,true).'</pre>'),'Notice');
        }
        
        
        
        $this->addToolbar();
        
        parent::display($tpl);
   }     

//	protected $onlineItems = null;
//	protected $dbItems = null;

	protected function addToolbar() 
    {
        $option = JRequest::getCmd('option');
		if (strpos($this->getLayout(), 'login') === false) {
			$canDo = jsmGCalendarUtil::getActions();
			if ($canDo->get('core.create')){
				JToolBarHelper::custom('jsmgcalendarimport.save', 'new.png', 'new.png', 'COM_SPORTSMANAGEMENT_JSMGCALENDAR_VIEW_IMPORT_BUTTON_ADD', false);
			}
			JToolBarHelper::cancel('jsmgcalendar.cancel', 'JTOOLBAR_CANCEL');
		}
        else
        {
            JToolBarHelper::cancel('jsmgcalendar.cancel', 'JTOOLBAR_CANCEL');
        }
        
        JToolBarHelper::divider();
        sportsmanagementHelper::ToolbarButtonOnlineHelp();
		JToolBarHelper::preferences($option);

//		JRequest::setVar('hidemainmenu', 0);

//		parent::addToolbar();
	}

//	protected function init() {
//		if (strpos($this->getLayout(), 'login') === false) {
//			$this->onlineItems = $this->get('OnlineData');
//			$this->dbItems = $this->get('DBData');
//		}
//	}

}

?>
