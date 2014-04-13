<?php


defined('_JEXEC') or die();
jimport('joomla.application.component.view');
//JLoader::import('components.com_gcalendar.libraries.GCalendar.view', JPATH_ADMINISTRATOR);

//class GCalendarViewGCalendar extends GCalendarView
class sportsmanagementViewjsmgcalendar extends JView  
{

function display( $tpl = null )
	{
		$mainframe	= JFactory::getApplication();
		$option = JRequest::getCmd('option');
		$db	 		= JFactory::getDBO();
		$uri		= JFactory::getURI();
		$user		= JFactory::getUser();
		$model		= $this->getModel();
        $starttime = microtime(); 
        
        // get the Data
		$form = $this->get('Form');
		$item = $this->get('Item');
        
        // Assign the Data
		$this->form = $form;
		$this->gcalendar = $item;
        
        $this->addToolbar();
        
        parent::display($tpl);
        
     }   
//	protected $gcalendar = null;
//	protected $form = null;
//
	protected function addToolbar() {
		//JRequest::setVar('hidemainmenu', true);
$option = JRequest::getCmd('option');
		$canDo = jsmGCalendarUtil::getActions($this->gcalendar->id);
		if ($this->gcalendar->id < 1) {
			if ($canDo->get('core.create')) {
				JToolBarHelper::apply('jsmgcalendar.apply', 'JTOOLBAR_APPLY');
				JToolBarHelper::save('jsmgcalendar.save', 'JTOOLBAR_SAVE');
				JToolBarHelper::custom('jsmgcalendar.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
			}
			JToolBarHelper::cancel('jsmgcalendar.cancel', 'JTOOLBAR_CANCEL');
		} else {
			if ($canDo->get('core.edit')) {
				JToolBarHelper::apply('jsmgcalendar.apply', 'JTOOLBAR_APPLY');
				JToolBarHelper::save('jsmgcalendar.save', 'JTOOLBAR_SAVE');

				if ($canDo->get('core.create')) {
					JToolBarHelper::custom('jsmgcalendar.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
				}
			}
			if ($canDo->get('core.create')) {
				JToolBarHelper::custom('jsmgcalendar.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
			}
			JToolBarHelper::cancel('jsmgcalendar.cancel', 'JTOOLBAR_CLOSE');
		}
        
        JToolBarHelper::divider();
        sportsmanagementHelper::ToolbarButtonOnlineHelp();
		JToolBarHelper::preferences($option);

//		parent::addToolbar();
	}
//
//	protected function init() {
//		$this->form = $this->get('Form');
//		$this->gcalendar = $this->get('Item');
//	}
}