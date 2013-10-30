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
 * @author	Kurt Norgaz
 * @package	JoomLeague
 * @since	1.5
 */
class JoomleagueViewTeamStaffs extends JLGView
{

	function display( $tpl = null )
	{
		if ( $this->getLayout() == 'default' )
		{
			$this->_displayDefault( $tpl );
			return;
		}

		parent::display( $tpl );
	}

	function _displayDefault( $tpl )
	{
		$document = &JFactory::getDocument();
		$option = JRequest::getCmd('option');
		$mainframe	= JFactory::getApplication();

		$uri = JFactory::getURI();
	
		$baseurl    = JUri::root();
		$document->addScript($baseurl.'administrator/components/com_joomleague/assets/js/autocompleter/1_4/Autocompleter.js');
		$document->addScript($baseurl.'administrator/components/com_joomleague/assets/js/autocompleter/1_4/Autocompleter.Request.js');
		$document->addScript($baseurl.'administrator/components/com_joomleague/assets/js/autocompleter/1_4/Observer.js');
		$document->addScript($baseurl.'administrator/components/com_joomleague/assets/js/autocompleter/1_4/quickaddperson.js');
		$document->addStyleSheet($baseurl.'administrator/components/com_joomleague/assets/css/Autocompleter.css');	

		$filter_state		= $mainframe->getUserStateFromRequest( $option . 'ts_filter_state',		'filter_state',		'',				'word' );
		//$filter_order		= $mainframe->getUserStateFromRequest( $option . 'ts_filter_order',		'filter_order',		'ppl.ordering',	'cmd' );
        $filter_order		= $mainframe->getUserStateFromRequest( $option . 'ts_filter_order',		'filter_order',		'ts.ordering',	'cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $option . 'ts_filter_order_Dir',	'filter_order_Dir',	'',				'word' );
		$search			= $mainframe->getUserStateFromRequest( $option . 'ts_search',			'search',			'',				'string' );
		$search_mode		= $mainframe->getUserStateFromRequest( $option . 'ts_search_mode',		'search_mode',		'',				'string' );

		$teamws	= $this->get( 'Data', 'teamws' );
		$mainframe->setUserState( 'team_id', $teamws->team_id );

		$items		= $this->get( 'Data' );
		$total		= $this->get( 'Total' );
		$pagination = $this->get( 'Pagination' );

		$model		= $this->getModel();

		// state filter
		$lists['state'] = JHtml::_( 'grid.state', $filter_state );

		// table ordering
		$lists['order_Dir'] = $filter_order_Dir;
		$lists['order'] = $filter_order;

		// search filter
		$lists['search'] = $search;
		$lists['search_mode']= $search_mode;

		//build the html options for position
		$position_id[] = JHtml::_( 'select.option', '0', JText::_( 'COM_JOOMLEAGUE_GLOBAL_SELECT_FUNCTION' ) );
		if ( $res = & $model->getPositions() )
		{
			$position_id = array_merge( $position_id, $res );
		}
		$lists['project_position_id'] = $position_id;
		unset( $position_id );

		$projectws		= $this->get( 'Data', 'projectws' );
		$teamstaffws	= $this->get( 'Data', 'teamstaffws' );

		$this->assignRef( 'user',				JFactory::getUser() );
		$this->assignRef( 'lists',				$lists );
		$this->assignRef( 'items',				$items );
		$this->assignRef( 'projectws',			$projectws );
		$this->assignRef( 'teamstaffws',		$teamstaffws );
		$this->assignRef( 'teamws',				$teamws );
		$this->assignRef( 'pagination',			$pagination );
		$this->assignRef( 'request_url',		$uri->toString() );

		$this->addToolbar();		
		parent::display( $tpl );
	}
	/**
	* Add the page title and toolbar.
	*
	* @since	1.7
	*/
	protected function addToolbar()
	{
		// Set toolbar items for the page
		JToolBarHelper::title( JText::_( 'COM_JOOMLEAGUE_ADMIN_TSTAFFS_TITLE' ) );

		JLToolBarHelper::publishList('teamstaff.publish');
		JLToolBarHelper::unpublishList('teamstaff.unpublish');
		JLToolBarHelper::apply( 'teamstaff.saveshort', JText::_( 'COM_JOOMLEAGUE_ADMIN_TSTAFFS_APPLY' ) );
		JToolBarHelper::divider();

		JLToolBarHelper::custom( 'teamstaff.assign', 'upload.png', 'upload_f2.png', JText::_( 'COM_JOOMLEAGUE_ADMIN_TSTAFFS_ASSIGN' ), false );
		JLToolBarHelper::custom( 'teamstaff.unassign', 'cancel.png', 'cancel_f2.png', JText::_( 'COM_JOOMLEAGUE_ADMIN_TSTAFFS_UNASSIGN' ), false );
		JToolBarHelper::divider();

		JToolBarHelper::back( 'COM_JOOMLEAGUE_ADMIN_TSTAFFS_BACK', 'index.php?option=com_joomleague&view=projectteams&task=projectteam.display' );
		JToolBarHelper::divider();

		
		JLToolBarHelper::onlinehelp();
		JToolBarHelper::preferences(JRequest::getCmd('option'));
		
	}
}
?>
