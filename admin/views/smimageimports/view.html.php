<?php


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');



class sportsmanagementViewsmimageimports extends JView
{
	function display($tpl=null)
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
        $model = $this->getModel();
        $uri = JFactory::getURI();
        $this->assign('files',$model->getXMLFiles());
       
       
       $this->assign('request_url',$uri->toString());
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
		// Get a refrence of the page instance in joomla
		$document	= JFactory::getDocument();
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
        $document->addCustomTag($stylelink);
        
        // Set toolbar items for the page
        JToolBarHelper::title(JText::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGES_IMPORT'),'images-import');
        JToolBarHelper::custom('smimageimports.import','upload','upload',JText::_('JTOOLBAR_UPLOAD'),false);
        JToolBarHelper::divider();
		sportsmanagementHelper::ToolbarButtonOnlineHelp();
        JToolBarHelper::preferences(JRequest::getCmd('option'));
        
    }    
    
    
    
}
?>
