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
use Joomla\CMS\Language\Text;
jimport('joomla.application.component.view');
//JLoader::import('components.com_gcalendar.libraries.GCalendar.view', JPATH_ADMINISTRATOR);

/**
 * sportsmanagementViewjsmgcalendarImport
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2015
 * @version $Id$
 * @access public
 */
class sportsmanagementViewjsmgcalendarImport extends sportsmanagementView 
{
    
    /**
     * sportsmanagementViewjsmgcalendarImport::init()
     * 
     * @param mixed $tpl
     * @return void
     */
    function init( $tpl = null )
	{
//		$app = JFactory::getApplication();
//		$jinput = $app->input;
//		$option = $jinput->getCmd('option');
//		$db	= sportsmanagementHelper::getDBConnection();
//		$uri = JFactory::getURI();
//		$user = JFactory::getUser();
//		$model = $this->getModel();
//        $starttime = microtime(); 
        
        if (strpos($this->getLayout(), 'login') === false) 
        {
			$this->onlineItems = $this->get('OnlineData');
			$this->dbItems = $this->get('DBData');
        }
        
        $this->app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' getLayout<br><pre>'.print_r($this->getLayout(),true).'</pre>'),'Notice');
        
        $this->setLayout('login');
        
        $this->app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' getLayout<br><pre>'.print_r($this->getLayout(),true).'</pre>'),'Notice');

   }     

//	protected $onlineItems = null;
//	protected $dbItems = null;

	/**
	 * sportsmanagementViewjsmgcalendarImport::addToolbar()
	 * 
	 * @return void
	 */
	protected function addToolbar() 
    {
		$jinput = JFactory::getApplication()->input;
        $option = $jinput->getCmd('option');
		if (strpos($this->getLayout(), 'login') === false) {
			$canDo = jsmGCalendarUtil::getActions();
			if ($canDo->get('core.create')){
				JToolbarHelper::custom('jsmgcalendarimport.save', 'new.png', 'new.png', 'COM_SPORTSMANAGEMENT_JSMGCALENDAR_VIEW_IMPORT_BUTTON_ADD', false);
			}
			JToolbarHelper::cancel('jsmgcalendar.cancel', 'JTOOLBAR_CANCEL');
		}
        else
        {
            JToolbarHelper::cancel('jsmgcalendar.cancel', 'JTOOLBAR_CANCEL');
        }
        
//        JToolbarHelper::divider();
//        sportsmanagementHelper::ToolbarButtonOnlineHelp();
//		JToolbarHelper::preferences($option);

//		JFactory::getApplication()->input->setVar('hidemainmenu', 0);

		parent::addToolbar();
	}

//	protected function init() {
//		if (strpos($this->getLayout(), 'login') === false) {
//			$this->onlineItems = $this->get('OnlineData');
//			$this->dbItems = $this->get('DBData');
//		}
//	}

}

?>
