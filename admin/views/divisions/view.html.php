<?php
/**
 * @copyright	Copyright (C) 2006-2013 JoomLeague.net. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view' );

/**
 * HTML View class for the Joomleague component
 *
 * @static
 * @package	Joomleague
 * @since	0.1
 */
class sportsmanagementViewDivisions extends JView
{

	function display( $tpl = null )
	{
		$option = JRequest::getCmd('option');
		$mainframe	= JFactory::getApplication();
		$db		= JFactory::getDBO();
		$uri	= JFactory::getURI();
        $model	= $this->getModel();

		$filter_state		= $mainframe->getUserStateFromRequest( $option .'.'.$model->_identifier.'.dv_filter_state','filter_state','','word');
		$filter_order		= $mainframe->getUserStateFromRequest( $option .'.'.$model->_identifier.'.dv_filter_order','filter_order','dv.ordering','cmd');
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $option .'.'.$model->_identifier.'.dv_filter_order_Dir','filter_order_Dir','','word');
		$search				= $mainframe->getUserStateFromRequest( $option .'.'.$model->_identifier.'.dv_search','search','','string');
		$search				= JString::strtolower( $search );
        
        $this->project_id	= $mainframe->getUserState( "$option.pid", '0' );
        $mdlProject = JModel::getInstance("Project", "sportsmanagementModel");
	    $project = $mdlProject->getProject($this->project_id);
        
/*
		$items		=& $this->get( 'Data' );
		$total		=& $this->get( 'Total' );
		$pagination =& $this->get( 'Pagination' );

		$projectws	=& $this->get( 'Data', 'projectws' );

		// state filter
		$lists['state']		= JHTML::_( 'grid.state',  $filter_state );

		// table ordering
		$lists['order_Dir']	= $filter_order_Dir;
		$lists['order']		= $filter_order;

		// search filter
		$lists['search']	= $search;

		$this->assignRef( 'user',			JFactory::getUser() );
		$this->assignRef( 'lists',			$lists );
		$this->assignRef( 'items',			$items );
		$this->assignRef( 'projectws',		$projectws );
		$this->assignRef( 'pagination',		$pagination );
		$this->assignRef( 'request_url',	$uri->toString() );
		$this->addToolbar();
		parent::display( $tpl );
        */
        $items = $this->get('Items');
		$total = $this->get('Total');
		$pagination = $this->get('Pagination');

		// table ordering
		$lists['order_Dir']=$filter_order_Dir;
		$lists['order']=$filter_order;

		// search filter
		$lists['search']=$search;

		$this->assign('user',JFactory::getUser());
		$this->assignRef('lists',$lists);
		$this->assignRef('items',$items);
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
		// Set toolbar items for the page
		JToolBarHelper::title( JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DIVS_TITLE' ) );
		
		JToolBarHelper::addNew('division.add');
		JToolBarHelper::editList('division.edit');
        JToolBarHelper::deleteList('', 'division.delete', 'JTOOLBAR_DELETE');
		JToolBarHelper::divider();
		
		
        sportsmanagementHelper::ToolbarButtonOnlineHelp();
		JToolBarHelper::preferences(JRequest::getCmd('option'));
	}
}
?>