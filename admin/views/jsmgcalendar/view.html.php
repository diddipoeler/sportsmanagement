<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage jsmgcalendar
 */

defined('_JEXEC') or die();
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\ToolbarHelper;

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
		$app = Factory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
		$db	= sportsmanagementHelper::getDBConnection();
		$uri = Factory::getURI();
		$user = Factory::getUser();
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
            $this->form->setValue('username', null, JComponentHelper::getParams(Factory::getApplication()->input->getCmd('option'))->get('google_mail_account',''));
            $this->form->setValue('password', null, JComponentHelper::getParams(Factory::getApplication()->input->getCmd('option'))->get('google_mail_password',''));
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
        $app = Factory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
		$canDo = jsmGCalendarUtil::getActions($this->gcalendar->id);
		if ($this->gcalendar->id < 1) 
        {
            ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_INSERT_NEW_GOOGLE'),'gcalendar');
            
			if ($canDo->get('core.create')) 
            {
                $app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_INSERT_ON_GOOGLE'),'Notice');
                
                $this->gcalendar->username = JComponentHelper::getParams(Factory::getApplication()->input->getCmd('option'))->get('google_mail_account','');
                $this->gcalendar->password = JComponentHelper::getParams(Factory::getApplication()->input->getCmd('option'))->get('google_mail_password','');
            
				ToolbarHelper::apply('jsmgcalendar.apply', 'JTOOLBAR_APPLY');
				ToolbarHelper::save('jsmgcalendar.save', 'JTOOLBAR_SAVE');
				ToolbarHelper::custom('jsmgcalendar.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
			}
			ToolbarHelper::cancel('jsmgcalendar.cancel', 'JTOOLBAR_CANCEL');
		} 
        else 
        {
            ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_EDIT_NEW_GOOGLE'),'gcalendar');
            
			if ($canDo->get('core.edit')) 
            {
				ToolbarHelper::publish('jsmgcalendar.insertgooglecalendar', 'JLIB_HTML_CALENDAR');
                
                ToolbarHelper::apply('jsmgcalendar.apply', 'JTOOLBAR_APPLY');
				ToolbarHelper::save('jsmgcalendar.save', 'JTOOLBAR_SAVE');

				if ($canDo->get('core.create')) 
                {
					ToolbarHelper::custom('jsmgcalendar.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
				}
			}
			if ($canDo->get('core.create')) 
            {
				ToolbarHelper::custom('jsmgcalendar.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
			}
			ToolbarHelper::cancel('jsmgcalendar.cancel', 'JTOOLBAR_CLOSE');
		}
        
        ToolbarHelper::divider();
        sportsmanagementHelper::ToolbarButtonOnlineHelp();
		ToolbarHelper::preferences($option);

		parent::addToolbar();
	}
//
//	protected function init() {
//		$this->form = $this->get('Form');
//		$this->gcalendar = $this->get('Item');
//	}
}