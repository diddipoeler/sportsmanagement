<?php


defined('_JEXEC') or die();

//JLoader::import('components.com_sportsmanagement.libraries.GCalendar.view', JPATH_ADMINISTRATOR);
jimport('joomla.application.component.view');

//class sportsmanagemantViewjsmgcalendars extends GCalendarView
class sportsmanagementViewjsmgcalendars extends sportsmanagementView 
{


public function init ()
	{
		$option 	= JRequest::getCmd('option');
		$mainframe	= JFactory::getApplication();
		$uri		= JFactory::getUri();
        
        $this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
        
        $this->addToolbar();
        
        parent::display($tpl);
        }
//	protected $icon = 'calendar';
//	protected $title = 'COM_GCALENDAR_MANAGER_GCALENDAR';
//
//	protected $items = null;
//	protected $pagination = null;

	protected function addToolbar() 
    {
		$option = JRequest::getCmd('option');
        $canDo = jsmGCalendarUtil::getActions();
		if ($canDo->get('core.create')) {
			JToolBarHelper::addNew('jsmgcalendar.add', 'JTOOLBAR_NEW');
			JToolBarHelper::custom('jsmgcalendarimport.import', 'upload.png', 'upload.png', 'COM_SPORTSMANAGEMENT_JSMGCALENDAR_VIEW_GCALENDARS_BUTTON_IMPORT', false);
		}
		if ($canDo->get('core.edit')) {
			JToolBarHelper::editList('jsmgcalendar.edit', 'JTOOLBAR_EDIT');
		}
		if ($canDo->get('core.delete')) {
			JToolBarHelper::deleteList('', 'jsmgcalendars.delete', 'JTOOLBAR_DELETE');
		}
        
        JToolBarHelper::divider();
        sportsmanagementHelper::ToolbarButtonOnlineHelp();
		JToolBarHelper::preferences($option);

		//parent::addToolbar();
	}

//	protected function init() 
//    {
//		$this->items = $this->get('Items');
//		$this->pagination = $this->get('Pagination');
//	}
}