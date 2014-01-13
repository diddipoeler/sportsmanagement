<?php


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');



/**
 * sportsmanagementViewsmquotestxt
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewsmquotestxt extends JView
{
	function display($tpl=null)
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
        $model = $this->getModel();
        $this->assign('files',$model->getTXTFiles());
       
       //$mainframe->enqueueMessage(JText::_('sportsmanagementViewsmextxmleditors files<br><pre>'.print_r($this->files,true).'</pre>'   ),'');
       
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
        JToolBarHelper::title(JText::_('COM_SPORTSMANAGEMENT_ADMIN_TXT_EDITORS'),'txt-edits');
		sportsmanagementHelper::ToolbarButtonOnlineHelp();
        JToolBarHelper::preferences(JRequest::getCmd('option'));
        
    }    
    
    
    
}
?>
