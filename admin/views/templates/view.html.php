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
 * @author	Marco Vaninetti <martizva@tiscali.it>
 * @package	JoomLeague
 * @since	0.1
 */
class sportsmanagementViewTemplates extends JView
{
	function display($tpl=null)
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		$document = JFactory::getDocument();
		$uri = JFactory::getURI();
        $model	= $this->getModel();
        
        
		//$templates =& $this->get('Data');
//		$total =& $this->get('Total');
//		$pagination =& $this->get('Pagination');
        
        $templates = $this->get('Items');
		$total = $this->get('Total');
		$pagination = $this->get('Pagination');
        
		//$projectws =& $this->get('Data','projectws');
        $this->project_id	= $mainframe->getUserState( "$option.pid", '0' );
        $mdlProject = JModel::getInstance("Project", "sportsmanagementModel");
	    $project = $mdlProject->getProject($this->project_id);
		
		
		if ($project->master_template)
		{
			/*
            $model->set('_getALL',1);
			$allMasterTemplates=$this->get('MasterTemplatesList');
			$model->set('_getALL',0);
			$masterTemplates=$this->get('MasterTemplatesList');
			$importlist=array();
			$importlist[]=JHtml::_('select.option',0,JText::_('COM_JOOMLEAGUE_ADMIN_TEMPLATES_SELECT_FROM_MASTER'));
			$importlist=array_merge($importlist,$masterTemplates);
			$lists['mastertemplates']=JHtml::_('select.genericlist',$importlist,'templateid');
			$master=$this->get('MasterName');
			$this->assign('master',$master);
			$templates=array_merge($templates,$allMasterTemplates);
            */
		}

		$filter_state		= $mainframe->getUserStateFromRequest($option.'.'.$model->_identifier.'tmpl_filter_state','filter_state','','word');
		$filter_order		= $mainframe->getUserStateFromRequest($option.'.'.$model->_identifier.'tmpl_filter_order','filter_order','tmpl.template','cmd');
		$filter_order_Dir	= $mainframe->getUserStateFromRequest($option.'.'.$model->_identifier.'tmpl_filter_order_Dir','filter_order_Dir','','word');
		$search				= $mainframe->getUserStateFromRequest($option.'.'.$model->_identifier.'tmpl_search','search','','string');
		$search_mode		= $mainframe->getUserStateFromRequest($option.'.'.$model->_identifier.'tmpl_search_mode','search_mode','','string');
		$search				= JString::strtolower($search);

		// state filter
		$lists['state']=JHtml::_('grid.state',$filter_state);

		// table ordering
		$lists['order_Dir']=$filter_order_Dir;
		$lists['order']=$filter_order;

		// search filter
		$lists['search']=$search;
		$lists['search_mode']=$search_mode;

		$this->assign('user',JFactory::getUser());
		$this->assignRef('lists',$lists);
		$this->assignRef('templates',$templates);
		$this->assignRef('projectws',$project);
		$this->assignRef('pagination',$pagination);
		$this->assign('request_url',$uri->toString());
		
		
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
		JToolBarHelper::title(JText::_('COM_SPORTSMANAGEMENT_ADMIN_TEMPLATES_TITLE'),'templates');
		JToolBarHelper::editList('template.edit');
		JToolBarHelper::save('template.save');
		if ($this->projectws->master_template)
		{
			JToolBarHelper::deleteList('','template.remove');
		}
		else
		{
			JToolBarHelper::custom('template.reset','restore','restore',JText::_('COM_SPORTSMANAGEMENT_GLOBAL_RESET'));
		}
		JToolBarHelper::divider();
        sportsmanagementHelper::ToolbarButtonOnlineHelp();
		JToolBarHelper::preferences(JRequest::getCmd('option'));
	}	
}
?>
