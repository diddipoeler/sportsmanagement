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
 * @package	JoomLeague
 * @since	0.1
 */
class JoomleagueViewteamPlayers extends JLGView
{

	function display( $tpl = null )
	{
		if ( $this->getLayout() == 'editlist' )
		{
			$this->_displayEditlist( $tpl );
			return;
		}

		if ( $this->getLayout() == 'default' )
		{
			$this->_displayDefault( $tpl );
			return;
		}

		parent::display( $tpl );
	}

	function _displayEditlist_remove( $tpl )
	{
		$option = JRequest::getCmd('option');
		$mainframe	= JFactory::getApplication();

		$project_id	= $mainframe->getUserState( $option . 'project' );
		$team_id	= $mainframe->getUserState( $option . 'team' );
		$uri		= JFactory::getURI();

		$filter_state		= $mainframe->getUserStateFromRequest( $option . 'tp_filter_state',		'filter_state',		'',				'word' );
		//$filter_order		= $mainframe->getUserStateFromRequest( $option . 'tp_filter_order',		'filter_order',		'ppl.ordering',	'cmd' );
        $filter_order		= $mainframe->getUserStateFromRequest( $option . 'tp_filter_order',		'filter_order',		'tp.ordering',	'cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $option . 'tp_filter_order_Dir',	'filter_order_Dir',	'',				'word' );
		$search			= $mainframe->getUserStateFromRequest( $option . 'tp_search',			'search',			'',				'string' );
		$search_mode		= $mainframe->getUserStateFromRequest( $option . 'tp_search_mode',		'search_mode',		'',				'string' );

		$model			= $this->getModel();
		$projectplayer	=& $this->get( 'Data' );
		$total			=& $this->get( 'Total' );
		$pagination		=& $this->get( 'Pagination' );

		// state filter
		$lists['state'] = JHTML::_( 'grid.state', $filter_state );

		// table ordering
		$lists['order_Dir']		= $filter_order_Dir;
		$lists['order']			= $filter_order;

		// search filter
		$lists['search']		= $search;
		$lists['search_mode']	= $search_mode;

		$projectws				=& $this->get( 'Data', 'projectws' );
		$teamws					=& $this->get( 'Data', 'teamws' );

		//build the html select list for project assigned players
		$ress = array();
		$res1 = array();
		$notusedplayers = array();

		if ( $ress =& $model->getTeamPlayers($teamws->team_id) )
		{
			$playerslist = array();
			foreach( $ress as $res )
			{
				$team_playerslist[] = JHTML::_(	'select.option', $res->value, $res->lastname . ' ' . $res->text . ' (' .
												$res->notes . ')' );
			}

			$lists['team_players'] = JHTML::_(	'select.genericlist', $team_playerslist, 'team_playerslist[]',
												' style="width:150px" class="inputbox" multiple="true" size="' . max( 20, count( $ress ) ) .
												'"', 'value', 'text' );
		}
		else
		{
			$lists['team_players']= '<select name="team_playerslist[]" id="team_playerslist" style="width:150px" class="inputbox" multiple="true" size="20"></select>';
		}

		$ress1 =& $model->getPersons($project_id, $team_id);

		if ( $ress =& $model->getProjectPlayers() )
		{
			foreach ( $ress1 as $res1 )
			{
				$used = 0;
				foreach ( $ress as $res )
				{
					if ( $res1->value == $res->value )
					{
						$used = 1;
					}

				}
				if ( $used == 0 )
				{
					$notusedplayers[] = JHTML::_(	'select.option', $res1->value,
					  JoomleagueHelper::formatName(null, $res1->firstname, $res1->nickname, $res1->lastname, 0) .
													' (' . $res1->notes . ')' );
				}
			}
		}
		else
		{
			foreach ( $ress1 as $res1 )
			{
				$notusedplayers[] = JHTML::_(	'select.option', $res1->value,
				  JoomleagueHelper::formatName(null, $res1->firstname, $res1->nickname, $res1->lastname, 0) .
												' (' . $res1->notes . ')' );
			}
		}

		//build the html select list for players
		if ( count ( $notusedplayers ) > 0 )
		{
			$lists['players'] = JHTML::_(	'select.genericlist', $notusedplayers, 'playerslist[]',
											' style="width:150px" class="inputbox" multiple="true" size="30"', 'value', 'text' );
		}
		else
		{
				$lists['players'] = '<select name="playerslist[]" id="playerslist" style="width:150px" class="inputbox" multiple="true" size="10"></select>';
		}
		unset( $res );
		unset( $res1 );
		unset( $notusedplayers );

		$this->assignRef( 'user',			JFactory::getUser() );
		$this->assignRef( 'lists',			$lists );
		$this->assignRef( 'projectplayer',	$projectplayer );
		$this->assignRef( 'projectws',		$projectws );
		$this->assignRef( 'teamws',			$teamws );
		$this->assignRef( 'pagination',		$pagination );
		$this->assignRef( 'request_url',	$uri->toString() );

		parent::display( $tpl );
	}

	function _displayDefault( $tpl )
	{
		$mainframe	= JFactory::getApplication();
		$option = JRequest::getCmd('option');

		$document = &JFactory::getDocument();
	
		$baseurl    = JURI::root();
		$document->addScript($baseurl.'administrator/components/com_joomleague/assets/js/autocompleter/1_4/Autocompleter.js');
		$document->addScript($baseurl.'administrator/components/com_joomleague/assets/js/autocompleter/1_4/Autocompleter.Request.js');
		$document->addScript($baseurl.'administrator/components/com_joomleague/assets/js/autocompleter/1_4/Observer.js');
		$document->addScript($baseurl.'administrator/components/com_joomleague/assets/js/autocompleter/1_4/quickaddperson.js');
		$document->addStyleSheet($baseurl.'administrator/components/com_joomleague/assets/css/Autocompleter.css');			

		
		$mainframe	= JFactory::getApplication();
		$uri		= JFactory::getURI();
		$project_id	= $mainframe->getUserState( $option . 'project' );

		$filter_state		= $mainframe->getUserStateFromRequest( $option . 'tp_filter_state',		'filter_state',		'',				'word' );
		$filter_order		= $mainframe->getUserStateFromRequest( $option . 'tp_filter_order',		'filter_order',		'ppl.ordering',	'cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $option . 'tp_filter_order_Dir',	'filter_order_Dir',	'',				'word' );
		$search				= $mainframe->getUserStateFromRequest( $option . 'ppl1_search',				'search',			'',				'string' );
		$search_mode		= $mainframe->getUserStateFromRequest( $option . 'tp_search_mode',			'search_mode',		'',				'string' );
// echo '<pre>';print_r($this); echo '</pre>';exit;
		$teamws	=& $this->get( 'Data', 'teamws' );

		$items		= & $this->get( 'Data' );
		$total		= & $this->get( 'Total' );
		$pagination = & $this->get( 'Pagination' );

		// state filter
		$lists['state'] = JHTML::_( 'grid.state', $filter_state );

		// table ordering
		$lists['order_Dir']	= $filter_order_Dir;
		$lists['order']		= $filter_order;

		// search filter
		$lists['search']		= $search;
		$lists['search_mode']	= $search_mode;

		//build the html options for position
		$position_id[] = JHTML::_( 'select.option', '0', JText::_( 'COM_JOOMLEAGUE_GLOBAL_SELECT_POSITION' ) );
		if ( $res =& $this->get('positions'))
		{
			$position_id = array_merge( $position_id, $res );
		}
		$lists['project_position_id'] = $position_id;
		unset( $position_id );

		$projectws	=& $this->get( 'Data', 'projectws' );
		$playerws	=& $this->get( 'Data', 'playerws' );

		$this->assignRef( 'user',				JFactory::getUser() );
		$this->assignRef( 'lists',				$lists );
		$this->assignRef( 'items',				$items );
		$this->assignRef( 'projectws',			$projectws );
		$this->assignRef( 'playerws',			$playerws );
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
	
		JToolBarHelper::title( JText::_( 'COM_JOOMLEAGUE_ADMIN_TPLAYERS_TITLE' ) );

		JLToolBarHelper::publishList('teamplayer.publish');
		JLToolBarHelper::unpublishList('teamplayer.unpublish');
		JLToolBarHelper::apply( 'teamplayer.saveshort', JText::_( 'COM_JOOMLEAGUE_ADMIN_TPLAYERS_APPLY' ) );
		JToolBarHelper::divider();

		JLToolBarHelper::custom( 'teamplayer.assign', 'upload.png', 'upload_f2.png', JText::_( 'COM_JOOMLEAGUE_ADMIN_TPLAYERS_ASSIGN' ), false );
		JLToolBarHelper::custom( 'teamplayer.unassign', 'cancel.png', 'cancel_f2.png', JText::_( 'COM_JOOMLEAGUE_ADMIN_TPLAYERS_UNASSIGN' ), false );
		JToolBarHelper::divider();

		JToolBarHelper::back( 'COM_JOOMLEAGUE_ADMIN_TPLAYERS_BACK', 'index.php?option=com_joomleague&view=projectteams&task=projectteam.display' );
		JToolBarHelper::divider();

		JLToolBarHelper::onlinehelp();
		JToolBarHelper::preferences(JRequest::getCmd('option'));
	}
}
?>
