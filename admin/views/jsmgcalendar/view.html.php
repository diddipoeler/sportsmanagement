<?php


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
class sportsmanagementViewjsmgcalendar extends JView  
{

/**
 * sportsmanagementViewjsmgcalendar::display()
 * 
 * @param mixed $tpl
 * @return void
 */
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
	/**
	* Add the page title and toolbar.
	*
	* @since	1.7
	*/
    protected function addToolbar() 
    {
		//JRequest::setVar('hidemainmenu', true);
        $mainframe	= JFactory::getApplication();
$option = JRequest::getCmd('option');
		$canDo = jsmGCalendarUtil::getActions($this->gcalendar->id);
		if ($this->gcalendar->id < 1) 
        {
            JToolBarHelper::title(JText::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_INSERT_NEW_GOOGLE'),'gcalendar');
            
			if ($canDo->get('core.create')) 
            {
                $mainframe->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_INSERT_ON_GOOGLE'),'Notice');
                
                $this->gcalendar->username = JComponentHelper::getParams(JRequest::getCmd('option'))->get('google_mail_account','');
                $this->gcalendar->password = JComponentHelper::getParams(JRequest::getCmd('option'))->get('google_mail_password','');
            
				JToolBarHelper::apply('jsmgcalendar.apply', 'JTOOLBAR_APPLY');
				JToolBarHelper::save('jsmgcalendar.save', 'JTOOLBAR_SAVE');
				JToolBarHelper::custom('jsmgcalendar.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
			}
			JToolBarHelper::cancel('jsmgcalendar.cancel', 'JTOOLBAR_CANCEL');
		} 
        else 
        {
            JToolBarHelper::title(JText::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_EDIT_NEW_GOOGLE'),'gcalendar');
            
			if ($canDo->get('core.edit')) 
            {
				JToolBarHelper::publish('jsmgcalendar.insertgooglecalendar', 'JLIB_HTML_CALENDAR');
                
                JToolBarHelper::apply('jsmgcalendar.apply', 'JTOOLBAR_APPLY');
				JToolBarHelper::save('jsmgcalendar.save', 'JTOOLBAR_SAVE');

				if ($canDo->get('core.create')) 
                {
					JToolBarHelper::custom('jsmgcalendar.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
				}
			}
			if ($canDo->get('core.create')) 
            {
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