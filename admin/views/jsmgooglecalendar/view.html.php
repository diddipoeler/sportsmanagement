<?php


defined('_JEXEC') or die();

JLoader::import( 'joomla.application.component.view' );

class sportsmanagementViewjsmgooglecalendar extends JViewLegacy {

	public function init ()
    {
//		JToolBarHelper::title(JText::_('COM_GCALENDAR'), 'calendar');
//
//		$canDo = jsmGCalendarUtil::getActions();
//		if ($canDo->get('core.admin')) {
//			JToolBarHelper::preferences('com_gcalendar', 550);
//			JToolBarHelper::divider();
//		}
$this->addToolbar();
		parent::display($tpl);   
	}
    
    
    /**
	 * Add the page title and toolbar.
	 *
	 * @since	1.7
	 */
	protected function addToolbar()
	{
		$option = JRequest::getCmd('option');
        // Get a refrence of the page instance in joomla  
		$document	= JFactory::getDocument();  
        // Set toolbar items for the page
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
        $document->addCustomTag($stylelink);  
        
        // Set toolbar items for the page
		JToolBarHelper::title( JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_GCALENDAR_TITLE' ),'gcalendar' );
        

		JToolBarHelper::divider();
        sportsmanagementHelper::ToolbarButtonOnlineHelp();
		JToolBarHelper::preferences($option);
	}
}  