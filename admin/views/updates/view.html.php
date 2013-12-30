<?php
/**
 * @copyright	Copyright (C) 2006-2013 JoomLeague.net. All rights reserved.
 * @license		GNU/GPL,see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License,and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

/**
 * HTML View class for the Joomleague component
 *
 * @author	Kurt Norgaz
 * @package	JoomLeague
 * @since	1.5
 */
class sportsmanagementViewUpdates extends JView
{
	function display($tpl=null)
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		$mainframe->setUserState($option.'update_part',0); // 0
		$filter_order		= $mainframe->getUserStateFromRequest($option.'updates_filter_order','filter_order','dates','cmd');
		$filter_order_Dir	= $mainframe->getUserStateFromRequest($option.'updates_filter_order_Dir','filter_order_Dir','','word');
		
		
        
		$db = JFactory::getDBO();
		$uri = JFactory::getURI();
		$model = $this->getModel();
		$versions=$model->getVersions();
		//$versionhistory=$model->getVersionHistory();
		$updateFiles = array();
		$lists=array();
		$updateFiles=$model->loadUpdateFiles();
        /*
        if($updateFiles=$model->loadUpdateFiles()) {
			for ($i=0, $n=count($updateFiles); $i < $n; $i++)
			{
				foreach ($versions as $version)
				{
					if (strpos($version->version,$updateFiles[$i]['file_name']))
					{
						$updateFiles[$i]['updateTime']=$version->date;
						break;
					}
					else
					{
						$updateFiles[$i]['updateTime']="-";
					}
				}
			}
		}
        */
		// table ordering
		$lists['order_Dir']=$filter_order_Dir;
		$lists['order']=$filter_order;
		//$this->assignRef('versionhistory',$versionhistory);
		$this->assignRef('updateFiles',$updateFiles);
		$this->assign('request_url',$uri->toString());
		$this->assignRef('lists',$lists);
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
        $document = JFactory::getDocument();
        // Set toolbar items for the page
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
        $document->addCustomTag($stylelink);
		// Set toolbar items for the page
        JToolBarHelper::title(JText::_('COM_JOOMLEAGUE_ADMIN_UPDATES_TITLE'),'updates');
		sportsmanagementHelper::ToolbarButtonOnlineHelp();
        JToolBarHelper::preferences(JRequest::getCmd('option'));
        
    }    
    
    
    
}
?>
