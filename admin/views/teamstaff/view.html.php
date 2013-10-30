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
 * @since	0.1
 */
class JoomleagueViewTeamStaff extends JLGView
{

	function display( $tpl = null )
	{
		$mainframe	= JFactory::getApplication();
		$uri		= JFactory::getURI();
		$user		= JFactory::getUser();
		$model		= $this->getModel();
		$lists		= array();

		//get the project_TeamStaff data of the project_team
		$project_teamstaff	= $this->get( 'data' );
		$isNew				= ( $project_teamstaff->id < 1 );

		// fail if checked out not by 'me'
		if ( $model->isCheckedOut( $user->get( 'id' ) ) )
		{
			$msg = JText::sprintf( 'DESCBEINGEDITTED', JText::_( 'COM_JOOMLEAGUE_ADMIN_TEAMSTAFF_THEPLAYER' ), $project_teamstaff->name );
			$mainframe->redirect( 'index.php?option=com_joomleague', $msg );
		}

		// Edit or Create?
		if ( $isNew ) { $project_teamstaff->order = 0; }

		//build the html select list for positions
		$selectedvalue = $project_teamstaff->project_position_id;
		$projectpositions = array();
		$projectpositions[] = JHtml::_('select.option', '0', JText::_( 'COM_JOOMLEAGUE_GLOBAL_SELECT_FUNCTION' ) );
		if ( $res = & $model->getProjectPositions() )
		{
			$projectpositions = array_merge( $projectpositions, $res );
		}
		$lists['projectpositions'] = JHtml::_(	'select.genericlist',
												$projectpositions,
												'project_position_id',
												'class="inputbox" size="1"',
												'value',
												'text', $selectedvalue );
		unset($projectpositions);

		$matchdays[] = JHtml::_( 'select.option', '0', JText::_( 'COM_JOOMLEAGUE_GLOBAL_SELECT_ROUND' ) );
		if ( $res = & $model->getProjectMatchdays() )
		{
			$matchdays = array_merge( $matchdays, $res );
		}

		// injury details
		$myoptions = array();
		$myoptions[]		= JHtml::_( 'select.option', '0', JText::_( 'JNO' ) );
		$myoptions[]		= JHtml::_( 'select.option', '1', JText::_( 'JYES' ) );
		$lists['injury']	= JHtml::_( 'select.radiolist',
										$myoptions,
										'injury',
										'class="inputbox" size="1"',
										'value',
										'text',
										$project_teamstaff->injury );
		unset($myoptions);

		$lists['injury_date']	 = JHtml::_( 'select.genericlist',
											$matchdays,
											'injury_date',
											'class="inputbox" size="1"',
											'value',
											'text',
											$project_teamstaff->injury_date );
		$lists['injury_end']	= JHtml::_( 'select.genericlist',
											$matchdays,
											'injury_end',
											'class="inputbox" size="1"',
											'value',
											'text',
											$project_teamstaff->injury_end );

		// suspension details
		$myoptions		= array();
		$myoptions[]	= JHtml::_('select.option', '0', JText::_( 'JNO' ) );
		$myoptions[]	= JHtml::_('select.option', '1', JText::_( 'JYES' ));
		$lists['suspension']		= JHtml::_( 'select.radiolist',
												$myoptions,
												'suspension',
												'class="inputbox" size="1"',
												'value',
												'text',
												$project_teamstaff->suspension );
		unset($myoptions);

		$lists['suspension_date']	 = JHtml::_( 'select.genericlist',
												$matchdays,
												'suspension_date',
												'class="inputbox" size="1"',
												'value',
												'text',
												$project_teamstaff->suspension_date );
		$lists['suspension_end']	= JHtml::_( 'select.genericlist',
												$matchdays,
												'suspension_end',
												'class="inputbox" size="1"',
												'value',
												'text',
												$project_teamstaff->suspension_end );

		// away details
		$myoptions		= array();
		$myoptions[]	= JHtml::_( 'select.option', '0', JText::_( 'JNO' ) );
		$myoptions[]	= JHtml::_( 'select.option', '1', JText::_( 'JYES' ) );
		$lists['away']	= JHtml::_( 'select.radiolist',
									$myoptions,
									'away',
									'class="inputbox" size="1"',
									'value',
									'text',
									$project_teamstaff->away );
		unset($myoptions);

		$lists['away_date'] = JHtml::_( 'select.genericlist',
										$matchdays,
										'away_date',
										'class="inputbox" size="1"',
										'value',
										'text',
										$project_teamstaff->away_date );
		$lists['away_end']	= JHtml::_( 'select.genericlist',
										$matchdays,
										'away_end',
										'class="inputbox" size="1"',
										'value',
										'text',
										$project_teamstaff->away_end );

		$projectws		= $this->get( 'Data', 'projectws' );
		$teamws			= $this->get( 'Data', 'teamws' );
		$extended = $this->getExtended($project_teamstaff->extended, 'teamstaff');
		$this->assignRef( 'extended', $extended );
		$this->assignRef('form'      	, $this->get('form'));			
		#$this->assignRef( 'default_person',		$default_person );
		$this->assignRef( 'projectws',			$projectws );
		$this->assignRef( 'teamws',				$teamws );
		$this->assignRef( 'lists',				$lists );
		$this->assignRef( 'project_teamstaff',	$project_teamstaff );

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
		$edit = JRequest::getVar( 'edit', true );
		$option = JRequest::getCmd('option');
		$params = JComponentHelper::getParams( $option );
		$default_name_format = $params->get("name_format");
		$name = JoomleagueHelper::formatName(null, $this->project_teamstaff->firstname, $this->project_teamstaff->nickname, $this->project_teamstaff->lastname, $default_name_format);
		$text = !$edit ? JText::_( 'COM_JOOMLEAGUE_GLOBAL_NEW' ) : JText::_( 'COM_JOOMLEAGUE_ADMIN_TEAMSTAFF_TITLE' ). ': ' . $name;
		JToolBarHelper::title( $text);
		JLToolBarHelper::save('teamstaff.save');
			
		if ( !$edit )
		{
			JLToolBarHelper::cancel('teamstaff.cancel');
		}
		else
		{
			// for existing items the button is renamed `close` and the apply button is showed
			JLToolBarHelper::apply('teamstaff.apply');
			JLToolBarHelper::cancel( 'teamstaff.cancel', 'COM_JOOMLEAGUE_GLOBAL_CLOSE' );
		}
		JToolBarHelper::back();
		JToolBarHelper::help( 'screen.joomleague', true );
	}
}
?>