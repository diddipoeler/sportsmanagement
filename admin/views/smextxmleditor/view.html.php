<?php


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');


/**
 * sportsmanagementViewsmextxmleditor
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2013
 * @access public
 */
class sportsmanagementViewsmextxmleditor extends JView
{
	function display($tpl=null)
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
        $model = $this->getModel();
        $this->file_name = JRequest::getVar('file_name');
        
       $this->assign('contents',$model->getContents());
       
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
		// Get a refrence of the page instance in joomla
		$document	= JFactory::getDocument();
        // Set toolbar items for the page
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
        $document->addCustomTag($stylelink);
        // Set toolbar items for the page
        JToolBarHelper::title($this->file_name,'xmlimport');
		sportsmanagementHelper::ToolbarButtonOnlineHelp();
        JToolBarHelper::preferences(JRequest::getCmd('option'));
        
    }    
    
    
    
}
?>