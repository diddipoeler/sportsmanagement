<?php


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');



class sportsmanagementViewsmquotetxt extends JView
{
	function display($tpl=null)
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
        $model = $this->getModel();
        $this->file_name = JRequest::getVar('file_name');
        
        // Initialise variables.
		$this->form		= $this->get('Form');
        $this->source	= $this->get('Source');
        
       //$this->assign('contents',$model->getContents());
       
       //$mainframe->enqueueMessage(JText::_('sportsmanagementViewsmextxmleditor contents<br><pre>'.print_r($this->contents,true).'</pre>'   ),'');
       
        $this->assignRef('option',$option);
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
		JRequest::setVar('hidemainmenu', true);
        // Get a refrence of the page instance in joomla
		$document	= JFactory::getDocument();
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
        $document->addCustomTag($stylelink);

        // Set toolbar items for the page
        JToolBarHelper::title($this->file_name,'txt-edit');
        
        JToolBarHelper::apply('smquotetxt.apply');
        JToolBarHelper::save('smquotetxt.save');
        JToolBarHelper::cancel('smquotetxt.cancel', 'JTOOLBAR_CANCEL');
        
        
        
        
        JToolBarHelper::divider();
		sportsmanagementHelper::ToolbarButtonOnlineHelp();
        JToolBarHelper::preferences(JRequest::getCmd('option'));
        
    }    
    
    
    
}
?>