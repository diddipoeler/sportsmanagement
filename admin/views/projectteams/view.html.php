<?php
/**
 * @copyright	Copyright (C) 2013 fussballineuropa.de. All rights reserved.
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
 * HTML View class for the Sportsmanagement Component
 *
 * @static
 * @package	Sportsmanagement
 * @since	0.1
 */
class sportsmanagementViewprojectteams extends JView
{

	function display($tpl=null)
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		$uri = JFactory::getURI();
        $model	= $this->getModel();

		$filter_order		= $mainframe->getUserStateFromRequest($option.'.'.$model->_identifier.'.tl_filter_order','filter_order','t.name','cmd');
		$filter_order_Dir	= $mainframe->getUserStateFromRequest($option.'.'.$model->_identifier.'.tl_filter_order_Dir','filter_order_Dir','','word');
		$search				= $mainframe->getUserStateFromRequest($option.'.'.$model->_identifier.'.tl_search','search','','string');
		$search_mode		= $mainframe->getUserStateFromRequest($option.'.'.$model->_identifier.'.tl_search_mode','search_mode','','string');
        $division			= $mainframe->getUserStateFromRequest($option.'.'.$model->_identifier.'.tl_division','division','','string');
		$search				= JString::strtolower($search);

		$items = $this->get('Items');
		$total = $this->get('Total');
		$pagination = $this->get('Pagination');
        
        $this->project_id	= $mainframe->getUserState( "$option.pid", '0' );;
        $mdlProject = JModel::getInstance("Project", "sportsmanagementModel");
	    $project = $mdlProject->getProject($this->project_id);
        $mdlDivisions = JModel::getInstance("divisions", "sportsmanagementModel");
	    $projectdivisions = $mdlDivisions->getDivisions($this->project_id);
        $lists['divisions'] = $projectdivisions;
        
        //$mainframe->enqueueMessage(JText::_('sportsmanagementViewprojectteams divisions<br><pre>'.print_r($projectdivisions,true).'</pre>'   ),'');

		// table ordering
		$lists['order_Dir']=$filter_order_Dir;
		$lists['order']=$filter_order;

		// search filter
		$lists['search']=$search;
		$lists['search_mode']=$search_mode;
        
        $myoptions = array();
		$myoptions[] = JHtml::_( 'select.option', '0', JText::_( 'JNO' ) );
		$myoptions[] = JHtml::_( 'select.option', '1', JText::_( 'JYES' ) );
		$lists['is_in_score'] = $myoptions;
        $lists['use_finally'] = $myoptions;

		$this->assign('user',JFactory::getUser());
		$this->assign('config',JFactory::getConfig());
		$this->assignRef('lists',$lists);
        $this->assignRef('divisions',$projectdivisions);
        $this->assignRef('division',$division);
		$this->assignRef('projectteam',$items);
		$this->assignRef('pagination',$pagination);
		$this->assign('request_url',$uri->toString());
        $this->assignRef('project',$project);
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
		// Set toolbar items for the page
		JToolBarHelper::title(JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_TITLE'));
        JToolBarHelper::deleteList('', 'projectteam.remove');

		JToolBarHelper::apply('projectteams.saveshort');
		//JToolBarHelper::custom('projectteam.changeteams','move.png','move_f2.png',JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_BUTTON_CHANGE_TEAMS'),false);
        sportsmanagementHelper::ToolbarButton('changeteams','move',JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_BUTTON_CHANGE_TEAMS'));
		//JToolBarHelper::custom('projectteam.editlist','upload.png','upload_f2.png',JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_BUTTON_ASSIGN'),false);
		sportsmanagementHelper::ToolbarButton('editlist','upload',JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_BUTTON_ASSIGN'));
        JToolBarHelper::custom('projectteam.copy','copy','copy', JText::_('JTOOLBAR_DUPLICATE'), true);
		JToolBarHelper::divider();

		sportsmanagementHelper::ToolbarButtonOnlineHelp();
    JToolBarHelper::preferences(JRequest::getCmd('option'));
    
        

		//JToolBarHelper::onlinehelp();
	}
}
?>