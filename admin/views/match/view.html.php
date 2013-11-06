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
jimport('joomla.filesystem.file');
JHTML::_('behavior.mootools');
/**
 * HTML View class for the Joomleague component
 *
 * @author	Marco Vaninetti <martizva@tiscali.it>
 * @package	JoomLeague
 * @since	0.1
 */

class sportsmanagementViewMatch extends JView
{

	/**
	 * display method of Hello view
	 * @return void
	 */
	public function display($tpl = null) 
	{
		// get the Data
		$form = $this->get('Form');
		$item = $this->get('Item');
		$script = $this->get('Script');
 
		// Check for errors.
		if (count($errors = $this->get('Errors'))) 
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}
        
        $mdlPlaygrounds = JModel::getInstance("Playgrounds", "sportsmanagementModel");
        
        //build the html select list for playgrounds
		$playgrounds[]=JHTML::_('select.option','0',JText::_('COM_JOOMLEAGUE_GLOBAL_SELECT_PLAYGROUND'));
		if ($res =& $mdlPlaygrounds->getPlaygrounds())
		{
			$playgrounds=array_merge($playgrounds,$res);
		}
		$lists['playgrounds']=JHTML::_(	'select.genericlist',$playgrounds,'playground_id','class="inputbox" size="1"','value',
										'text',$item->playground_id);

		// build the html select booleanlist for cancel
		$lists['cancel']=JHTML::_('select.booleanlist','cancel','class="inputbox"',$item->cancel);

		// build the html select booleanlist for show_report
		$lists['show_report']=JHTML::_('select.booleanlist','show_report','class="inputbox"',$item->show_report);

		// build the html select booleanlist for count match result
		$lists['count_result']=JHTML::_('select.booleanlist','count_result','class="inputbox"',$item->count_result);
        
        
		// Assign the Data
		$this->form = $form;
		$this->item = $item;
		$this->script = $script;
		
		$extended = sportsmanagementHelper::getExtended($item->extended, 'match');
		$this->assignRef( 'extended', $extended );
        $this->assignRef('lists',$lists);
		$this->assign('cfg_which_media_tool', JComponentHelper::getParams('com_sportsmanagement')->get('cfg_which_media_tool',0) );
 
 
		// Set the toolbar
		$this->addToolBar();
		
//		echo '<pre>'.print_r($this->item,true).'</pre><br>'; 
 
		// Display the template
		parent::display($tpl);
 
		// Set the document
		$this->setDocument();
	}
 
 
/*
	function display($tpl=null)
	{
		$mainframe = JFactory::getApplication();

		if ($this->getLayout() == 'form')
		{
			$this->_displayForm($tpl);
			return;
		}
		elseif ($this->getLayout() == 'editevents')
		{
			$this->_displayEditevents($tpl);
			return;
		}
		elseif ($this->getLayout() == 'editeventsbb')
		{
			$this->_displayEditeventsbb($tpl);
			return;
		}
		elseif ($this->getLayout() == 'editstats')
		{
			$this->_displayEditstats($tpl);
			return;
		}
		elseif ($this->getLayout() == 'editlineup')
		{
			$this->_displayEditlineup($tpl);
			return;
		}
		elseif ($this->getLayout() == 'editreferees')
		{
			$this->_displayEditReferees($tpl);
			return;
		}
        elseif ($this->getLayout() == 'readpressebericht')
		{
			$this->_displayPressebericht($tpl);
			return;
		}
        elseif ($this->getLayout() == 'savepressebericht')
		{
			$this->_displaySavePressebericht($tpl);
			return;
		}
        
        $config =& JComponentHelper::getParams('com_media');
		$post=JRequest::get('post');
		$files=JRequest::get('files');
        $uri = JFactory::getURI();
		$this->assignRef('request_url',$uri->toString());
		$this->assignRef('config',$config);
        $cid = JRequest::getVar('cid',array(0),'','array');
        $match_id = $cid[0];
        $this->assignRef('match_id',$match_id);

		parent::display($tpl);
	}
*/
    
    function _displaySavePressebericht($tpl)
    {
    $mainframe = JFactory::getApplication();
	$document = JFactory::getDocument();
    $project_id = $mainframe->getUserState('com_joomleagueproject');
    $model = $this->getModel();
    $csv_file_save = $model->savePressebericht();
    
    $this->assignRef('importData',$model->_success_text);
        
    parent::display($tpl);    
    }
    
    function _displayPressebericht($tpl)
    {
        $mainframe = JFactory::getApplication();
		$document = JFactory::getDocument();
        $project_id=$mainframe->getUserState('com_joomleagueproject');
//$mainframe->enqueueMessage(JText::_('displayPressebericht<br><pre>'.print_r($this->_datas['match'],true).'</pre>'   ),'');     
//    $document->addScript(JURI::root() . 'administrator/components/com_joomleague/assets/js/jquery.csv-0.71.js');       
//    $document->addScript(JURI::root() . 'administrator/components/com_joomleague/assets/js/jquery.csv.js');
$model = $this->getModel();
$csv_file = $model->getPressebericht(); 
$this->assignRef('csv',$csv_file); 
$matchnumber = $model->getPresseberichtMatchnumber($csv_file);    
$this->assignRef('matchnumber',$matchnumber);
if ( $matchnumber )
{
$readplayers = $model->getPresseberichtReadPlayers($csv_file);  
$this->assignRef('csvplayers',$model->csv_player);   
$this->assignRef('csvinout',$model->csv_in_out);
$this->assignRef('csvcards',$model->csv_cards);
$this->assignRef('csvstaff',$model->csv_staff);
}

//build the html options for position
		$position_id[] = JHTML::_( 'select.option', '0', JText::_( 'COM_SPORTSMANAGEMENT_GLOBAL_SELECT_POSITION' ) );
		if ( $res = $model->getProjectPositionsOptions(0,1) )
		{
			$position_id = array_merge( $position_id, $res );
		}
		$lists['project_position_id'] = $position_id;
        $lists['inout_position_id'] = $position_id;
		unset( $position_id );
        
        $position_id[] = JHTML::_( 'select.option', '0', JText::_( 'COM_SPORTSMANAGEMENT_GLOBAL_SELECT_POSITION' ) );
		if ( $res = $model->getProjectPositionsOptions(0,2) )
		{
			$position_id = array_merge( $position_id, $res );
		}
		$lists['project_staff_position_id'] = $position_id;
		unset( $position_id );
        
        // events
		$events=$model->getEventsOptions($project_id);
		if (!$events)
		{
			JError::raiseWarning(440,'<br />'.JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_NO_EVENTS_POS').'<br /><br />');
			return;
		}
		$eventlist=array();
        $eventlist[] = JHTML::_( 'select.option', '0', JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_SELECT_EVENT' ) );
		$eventlist=array_merge($eventlist,$events);
		
        $lists['events'] = $eventlist;
        unset( $eventlist );
        
        $this->assignRef( 'lists',			$lists );
 
        parent::display($tpl);
    }

	function _displayEditReferees($tpl)
	{
		$mainframe = JFactory::getApplication();
		$document = JFactory::getDocument();
		$project_id=$mainframe->getUserState('com_joomleagueproject');
		$option = JRequest::getCmd('option');
		$params =& JComponentHelper::getParams( $option );
		$default_name_format = $params->get("name_format");

		//add the js script
		$version = urlencode(sportsmanagementHelper::getVersion());
		$document->addScript(JURI::base().'components/com_joomleague/assets/js/startinglineup.js?v='.$version);

		$model = $this->getModel();
		$match =& $this->get('data');

		$allreferees=array();
		$allreferees=$model->getRefereeRoster();
		$inroster=array();
		$projectreferees=array();
		$projectreferees2=array();

		if (isset($allreferees))
		{
			foreach ($allreferees AS $referee) {
				$inroster[]=$referee->value;
			}
		}
		$projectreferees=$model->getProjectReferees($inroster,$project_id);

		if (count($projectreferees) > 0)
		{
			foreach ($projectreferees AS $referee)
			{
				$projectreferees2[]=JHTML::_('select.option',$referee->value,
				  sportsmanagementHelper::formatName(null, $referee->firstname, $referee->nickname, $referee->lastname, $default_name_format) .
				  ' - ('.strtolower(JText::_($referee->positionname)).')');
			}
		}
		$lists['team_referees']=JHTML::_(	'select.genericlist',$projectreferees2,'roster[]',
											'style="font-size:12px;height:auto;min-width:15em;" ' .
											'class="inputbox" multiple="true" size="'.max(10,count($projectreferees2)).'"',
											'value','text');

		$selectpositions[]=JHTML::_('select.option','0',JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_REF_FUNCTION'));
		if ($projectpositions =& $model->getProjectPositionsOptions(0, 3))
		{
			$selectpositions=array_merge($selectpositions,$projectpositions);
		}
		$lists['projectpositions']=JHTML::_('select.genericlist',$selectpositions,'project_position_id','class="inputbox" size="1"','value','text');

		$squad=array();
		if (!$projectpositions)
		{
			JError::raiseWarning(440,'<br />'.JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_NO_REF_POS').'<br /><br />');
			return;
		}

		// generate selection list for each position
		foreach ($projectpositions AS $key => $pos)
		{
			// get referees assigned to this position
			$squad[$key]=$model->getRefereeRoster($pos->value);
		}
		if (count($squad) > 0)
		{
			foreach ($squad AS $key => $referees)
			{
				$temp[$key]=array();
				if (isset($referees))
				{
					foreach ($referees AS $referee)
					{
						$temp[$key][]=JHTML::_('select.option',$referee->value,
						  sportsmanagementHelper::formatName(null, $referee->firstname, $referee->nickname, $referee->lastname, $default_name_format));
					}
				}
				$lists['team_referees'.$key]=JHTML::_(	'select.genericlist',$temp[$key],'position'.$key.'[]',
														'id="testing" style="font-size:12px;height:auto;min-width:15em;" '.
														'class="inputbox position-starters" multiple="true" ',
														'value','text');
			}
		}
		$this->assignRef('project_id',$project_id);
		$this->assignRef('match',$match);
		$this->assignRef('positions',$projectpositions);
		$this->assignRef('lists',$lists);
		parent::display($tpl);
	}

	function _displayEditevents($tpl)
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		$project_id=$mainframe->getUserState('com_joomleagueproject');
		$document = JFactory::getDocument();
		$params =& JComponentHelper::getParams( $option );
		$default_name_format = $params->get("name_format");

		//add the js script
		$version = urlencode(sportsmanagementHelper::getVersion());
		$document->addScript(JURI::base().'components/com_joomleague/assets/js/editevents.js?v='.$version);

		$model = $this->getModel();
		$teams =& $model->getMatchTeams();

		$homeRoster=$model->getTeamPlayers($teams->projectteam1_id);
		if (count($homeRoster)==0)
		{
			$homeRoster=$model->getGhostPlayer();
		}
		$awayRoster=$model->getTeamPlayers($teams->projectteam2_id);
		if (count($awayRoster)==0)
		{
			$awayRoster=$model->getGhostPlayer();
		}
		$rosters=array('home' => $homeRoster,'away' => $awayRoster);
		$matchevents =& $model->getMatchEvents();
        $matchcommentary =& $model->getMatchCommentary();
		$project_model = $this->getModel('projectws');

		$lists=array();

		// teams
		$teamlist=array();
		$teamlist[]=JHTML::_('select.option',$teams->projectteam1_id,$teams->team1);
		$teamlist[]=JHTML::_('select.option',$teams->projectteam2_id,$teams->team2);
		$lists['teams']=JHTML::_('select.genericlist',$teamlist,'team_id','class="inputbox select-team"');

		// events
		$events=$model->getEventsOptions($project_id);
		if (!$events)
		{
			JError::raiseWarning(440,'<br />'.JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_NO_EVENTS_POS').'<br /><br />');
			return;
		}
		$eventlist=array();
		$eventlist=array_merge($eventlist,$events);

		$lists['events']=JHTML::_('select.genericlist',$eventlist,'event_type_id','class="inputbox select-event"');

		$this->assignRef('overall_config',$project_model->getTemplateConfig('overall'));
		$this->assignRef('lists',$lists);
		$this->assignRef('rosters',$rosters);
		$this->assignRef('teams',$teams);
		$this->assignRef('matchevents',$matchevents);
        $this->assignRef('matchcommentary',$matchcommentary);
        
        // diddipoeler
        $this->assign('show_debug_info', JComponentHelper::getParams($option)->get('show_debug_info',0) );
        $mdlMatchProject = JModel::getInstance('project','JoomleagueModel');
        $this->assignRef('eventsprojecttime',$mdlMatchProject->getProjectGameRegularTime($project_id) );
        

		parent::display($tpl);
	}

	function _displayEditeventsbb($tpl)
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		$project_id=$mainframe->getUserState('com_joomleagueproject');
		$document = JFactory::getDocument();
		$params =& JComponentHelper::getParams( $option );
		$default_name_format = $params->get("name_format");

		$model = $this->getModel();
		$teams =& $model->getMatchTeams();

		$homeRoster=$model->getTeamPlayers($teams->projectteam1_id);
		if (count($homeRoster)==0)
		{
			$homeRoster=$model->getGhostPlayerbb($teams->projectteam1_id);
		}
		$awayRoster=$model->getTeamPlayers($teams->projectteam2_id);
		if (count($awayRoster)==0)
		{
			$awayRoster=$model->getGhostPlayerbb($teams->projectteam2_id);
		}
		$project_model = $this->getModel('projectws');
		// events
		$events=$model->getEventsOptions($project_id);
		if (!$events)
		{
			JError::raiseWarning(440,'<br />'.JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_NO_EVENTS_POS').'<br /><br />');
			return;
		}

		$this->assignRef('homeRoster',$homeRoster);
		$this->assignRef('awayRoster',$awayRoster);
		$this->assignRef('teams',$teams);
		$this->assignRef('events',$events);
        
        // diddipoeler
        $this->assignRef('eventsprojecttime',$project_model->_data->game_regular_time);
        

		$this->addToolbar_Editeventsbb();			
		parent::display($tpl);
	}
	/**
	* Add the page title and toolbar.
	*
	* @since	1.7
	*/
	protected function addToolbar_Editeventsbb()
	{	
		//set toolbar items for the page
		JToolBarHelper::title( JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_MATCH_EEBB_TITLE' ),'events' );
		JLToolBarHelper::apply( 'match.saveeventbb' );
		JToolBarHelper::divider();
		JToolBarHelper::back( 'back', 'index.php?option=com_joomleague&view=matches&task=match.display' );
		JLToolBarHelper::onlinehelp();	
	}
	
	function _displayEditstats($tpl)
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		$project_id=$mainframe->getUserState($option.'project');
		$document = JFactory::getDocument();
		$params =& JComponentHelper::getParams( $option );
		$default_name_format = $params->get("name_format");

		//add the js script
		$version = urlencode(sportsmanagementHelper::getVersion());
		$document->addScript(JURI::base().'components/com_joomleague/assets/js/editmatchstats.js?v='.$version);

		$model = $this->getModel();
		$match =& $this->get('data');
		$teams =& $this->get('MatchTeams');
		$positions =& $this->get('ProjectPositions');
		$staffpositions =& $this->get('ProjectStaffPositions');

		$homeRoster=$model->getMatchPlayers($teams->projectteam1_id);
		if (count($homeRoster)==0)
		{
			$homeRoster=$model->getGhostPlayerbb($teams->projectteam1_id);
		}
		$awayRoster=$model->getMatchPlayers($teams->projectteam2_id);
		if (count($awayRoster)==0)
		{
			$awayRoster=$model->getGhostPlayerbb($teams->projectteam2_id);
		}

		$homeStaff=$model->getMatchStaffs($teams->projectteam1_id);
		$awayStaff=$model->getMatchStaffs($teams->projectteam2_id);

		// stats
		$stats=$model->getInputStats();
		if (!$stats)
		{
			JError::raiseWarning(440,'<br />'.JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_NO_STATS_POS').'<br /><br />');
			return;
		}
		$playerstats=$model->getMatchStatsInput();
		$staffstats=$model->getMatchStaffStatsInput();

		$this->assignRef('homeRoster',$homeRoster);
		$this->assignRef('awayRoster',$awayRoster);
		$this->assignRef('homeStaff',$homeStaff);
		$this->assignRef('awayStaff',$awayStaff);
		$this->assignRef('teams',$teams);
		$this->assignRef('stats',$stats);
		$this->assignRef('playerstats',$playerstats);
		$this->assignRef('staffstats',$staffstats);
		$this->assignRef('match',$match);
		$this->assignRef('positions',$positions);
		$this->assignRef('staffpositions',$staffpositions);

		parent::display($tpl);
	}

	function _displayEditlineup($tpl)
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		$project_id=$mainframe->getUserState($option.'project');
		$document = JFactory::getDocument();
		$tid=JRequest::getVar('team','0');
		$params =& JComponentHelper::getParams( $option );
		$default_name_format = $params->get("name_format");

		//add the js script
		$version = urlencode(sportsmanagementHelper::getVersion());
		$document->addScript(JURI::base().'components/com_joomleague/assets/js/startinglineup.js?v='.$version);

		$model = $this->getModel();
		$match =& $model->getMatchTeams();

		if (!$match)
		{
			JError::raiseWarning(440,'<br />'.JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_NO_TEAM_MATCH').'<br /><br />');
		}
		$teamname = ($tid == $match->projectteam1_id) ? $match->team1 : $match->team2;

		if($params->get('use_prefilled_match_roster')>0) {
			$bDeleteCurrrentRoster = $params->get('on_prefill_delete_current_match_roster', 0);
			$prefillType = JRequest::getInt('prefill',0);
			if($prefillType==0) {
				$prefillType = $params->get('use_prefilled_match_roster');
			}
			$projectteam_id = ($tid == $match->projectteam1_id) ? $match->projectteam1_id : $match->projectteam2_id;
			if($prefillType == 2) {
				$preFillSuccess = false;
				if(!$model->prefillMatchPlayersWithProjectteamPlayers($projectteam_id, $bDeleteCurrrentRoster)) {
					if($model->getError() != '') {
						JError::raiseWarning(440,'<br />'.$model->getError().'<br /><br />');
						return;
					} else {
						$preFillSuccess = false;
					}
				} else {
					$preFillSuccess = true;
				}
			} elseif($prefillType == 1) {
				if(!$model->prefillMatchPlayersWithLastMatch($projectteam_id, $bDeleteCurrrentRoster)) {
					if($model->getError() != '') {
						JError::raiseWarning(440,'<br />'.$model->getError().'<br /><br />');
						return;
					} else {
						$preFillSuccess = false;
					}
				} else {
					$preFillSuccess = true;
				}
			}
		}

		// get starters
		$starters = $model->getRoster($tid);
		$starters_id = array_keys($starters);

		// get players not already assigned to starter
		$not_assigned=$model->getTeamPlayers($tid,$starters_id);
		if (!$not_assigned && !$starters_id)
		{
			JError::raiseWarning(440,'<br />'.JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_NO_PLAYERS_MATCH').'<br /><br />');
			return;
		}

		$projectpositions =& $model->getProjectPositions();
		if (!$projectpositions)
		{
			JError::raiseWarning(440,'<br />'.JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_NO_POS').'<br /><br />');
			return;
		}

		// build select list for not assigned players
		$not_assigned_options=array();
		foreach ((array) $not_assigned AS $p)
		{
			$not_assigned_options[] = JHTML::_( 'select.option',$p->value,'['.$p->jerseynumber.'] '.
			  									sportsmanagementHelper::formatName(null, $p->firstname, $p->nickname, $p->lastname, $default_name_format) .
			  									' - ('.JText::_($p->positionname).')');
		}
		$lists['team_players']=JHTML::_(	'select.genericlist',$not_assigned_options,'roster[]',
											'style="font-size:12px;height:auto;min-width:15em;" class="inputbox" multiple="true" size="18"',
											'value','text');

		// build position select
		$selectpositions[]=JHTML::_('select.option','0',JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_IN_POSITION'));
		$selectpositions=array_merge($selectpositions,$model->getProjectPositionsOptions(0,1));
		$lists['projectpositions']=JHTML::_('select.genericlist',$selectpositions,'project_position_id','class="inputbox" size="1"','value','text', NULL, false, true);
		// build player select
		$allplayers=$model->getTeamPlayers($tid);
		$playersoptions=array();
		$playersoptions[]=JHTML::_('select.option','0',JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_PLAYER'));
		foreach ((array)$allplayers AS $player)
		{
			$playersoptions[]=JHTML::_('select.option',$player->value,
			  sportsmanagementHelper::formatName(null, $player->firstname, $player->nickname, $player->lastname, $default_name_format).' - ('.JText::_($player->positionname).')');
		}
		$lists['all_players']=JHTML::_(	'select.genericlist',$playersoptions,'roster[]',
										'id="roster" style="font-size:12px;height:auto;min-width:15em;" class="inputbox" size="4"',
										'value','text');

		// generate selection list for each position
		$starters=array();
		foreach ($projectpositions AS $position_id => $pos)
		{
			// get players assigned to this position
			$starters[$position_id] = $model->getRoster($tid, $pos->pposid);
		}

		foreach ($starters AS $position_id => $players)
		{
			$options=array();
			foreach ((array) $players AS $p)
			{
				$options[]=JHTML::_('select.option',$p->value,'['.$p->jerseynumber.'] '.
				  sportsmanagementHelper::formatName(null, $p->firstname, $p->nickname, $p->lastname, $default_name_format));
			}

			$lists['team_players'.$position_id]=JHTML::_(	'select.genericlist',$options,'position'.$position_id.'[]',
															'style="font-size:12px;height:auto;min-width:15em;" size="4" class="inputbox position-starters" multiple="true" ',
															'value','text');
		}

		$substitutions=$model->getSubstitutions($tid);

		/**
		 * staff positions
		 */
		$staffpositions =& $model->getProjectStaffPositions();	// get staff not already assigned to starter
		//echo '<pre>'.print_r($staffpositions,true).'</pre>';

		// assigned staff
		$assigned=$model->getMatchStaffs($tid);
		$assigned_id=array_keys($assigned);
		// not assigned staff
		$not_assigned=$model->getTeamStaffs($tid,$assigned_id);

		// build select list for not assigned
		$not_assigned_options=array();
		foreach ((array) $not_assigned AS $p)
		{
			$not_assigned_options[]=JHTML::_('select.option',$p->value,
				  sportsmanagementHelper::formatName(null, $p->firstname, $p->nickname, $p->lastname, $default_name_format).' - ('.JText::_($p->positionname).')');
		}
		$lists['team_staffs']=JHTML::_(	'select.genericlist',$not_assigned_options,'staff[]',
										'style="font-size:12px;height:auto;min-width:15em;" size="18" class="inputbox" multiple="true" size="18"',
										'value','text');

		// generate selection list for each position
		$options=array();
		foreach ($staffpositions AS $position_id => $pos)
		{
			// get players assigned to this position
			$options=array();
			foreach ($assigned as $staff)
			{
				if ($staff->project_position_id == $pos->pposid)
				{
					$options[]=JHTML::_('select.option',$staff->team_staff_id,
					  sportsmanagementHelper::formatName(null, $staff->firstname, $staff->nickname, $staff->lastname, $default_name_format));
				}
			}
			$lists['team_staffs'.$position_id]=JHTML::_(	'select.genericlist',$options,'staffposition'.$position_id.'[]',
															'style="font-size:12px;height:auto;min-width:15em;" size="4" class="inputbox position-staff" multiple="true" ',
															'value','text');
		}

		$this->assignRef('match',			$match);
		$this->assignRef('tid',				$tid);
		$this->assignRef('teamname',		$teamname);
		$this->assignRef('positions',		$projectpositions);
		$this->assignRef('staffpositions',	$staffpositions);
		$this->assignRef('substitutions',	$substitutions[$tid]);
		$this->assignRef('playersoptions',	$playersoptions);
		$this->assignRef('lists',			$lists);
		$this->assignRef('preFillSuccess',	$preFillSuccess);
        
        // diddipoeler
        $this->assign('show_debug_info', JComponentHelper::getParams($option)->get('show_debug_info',0) );
        $mdlMatchProject = JModel::getInstance('project','JoomleagueModel');
        $this->assignRef('eventsprojecttime',$mdlMatchProject->getProjectGameRegularTime($project_id) );
        
    $this->assignRef('starters',			$starters);
    
		parent::display($tpl);
	}

	function _displayForm($tpl)
	{
		$mainframe = JFactory::getApplication();
		$option = JRequest::getCmd('option');
		$user = JFactory::getUser();
		$model = $this->getModel();
		$lists=array();
		
		$cid = JRequest::getVar('cid',array(0),'','array');
		$match_id = $cid[0];
		$dest = 'matchreport/'.$match_id;
    JRequest::setVar( 'folder', $dest );
    
		//get the match
		$match =& $this->get('data');
		$isNew = ($match->id < 1);

		if ((!$match->projectteam1_id) AND (!$match->projectteam2_id))
		{
			JError::raiseWarning(440,'<br />'.JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_NO_TEAMS').'<br /><br />');
			return;
		}

		// fail if checked out not by 'me'
		if ($model->isCheckedOut($user->get('id')))
		{
			$msg=JText::sprintf('DESCBEINGEDITTED',JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_THE_MATCH'),$match->name);
			$mainframe->redirect('index.php?option=com_joomleague',$msg);
		}

		// Edit or Create?
		if (!$isNew)
		{
			$model->checkout($user->get('id'));
		}

		// build the html select booleanlist for published
		$lists['published']=JHTML::_('select.booleanlist','published','class="inputbox"',$match->published);

		//build the html select list for playgrounds
		$playgrounds[]=JHTML::_('select.option','0',JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_PLAYGROUND'));
		if ($res =& $model->getPlaygrounds())
		{
			$playgrounds=array_merge($playgrounds,$res);
		}
		$lists['playgrounds']=JHTML::_(	'select.genericlist',$playgrounds,'playground_id','class="inputbox" size="1"','value',
										'text',$match->playground_id);

		// build the html select booleanlist for cancel
		$lists['cancel']=JHTML::_('select.booleanlist','cancel','class="inputbox"',$match->cancel);

		// build the html select booleanlist for show_report
		$lists['show_report']=JHTML::_('select.booleanlist','show_report','class="inputbox"',$match->show_report);

		// build the html select booleanlist for count match result
		$lists['count_result']=JHTML::_('select.booleanlist','count_result','class="inputbox"',$match->count_result);

		// build the html select booleanlist which team got the won
		$myoptions=array();
		$myoptions[]=JHTML::_('select.option','0',JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_NO_TEAM'));
		$myoptions[]=JHTML::_('select.option','1',JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_HOME_TEAM'));
		$myoptions[]=JHTML::_('select.option','2',JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_AWAY_TEAM'));
		$myoptions[]=JHTML::_('select.option','3',JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_LOSS_BOTH_TEAMS'));
		$myoptions[]=JHTML::_('select.option','4',JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_WON_BOTH_TEAMS'));
		$lists['team_won']=JHTML::_('select.genericlist',$myoptions,'team_won','class="inputbox" size="1"','value','text',$match->team_won);

		$projectws =& $this->get('Data','projectws');
		$model = $this->getModel('projectws');

		$overall_config=$model->getTemplateConfig('overall');
		$table_config=$model->getTemplateConfig('ranking');

		$extended = $this->getExtended($match->extended, 'match');

		//match relation tab
		$mdlMatch=JModel::getInstance('match','JoomleagueModel');
		$oldmatches[]=JHTML::_('select.option','0',JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_OLD_MATCH'));
		$res=array();
		$new_match_id=($match->new_match_id) ? $match->new_match_id : 0;
		if ($res =& $mdlMatch->getMatchRelationsOptions($mainframe->getUserState($option.'project',0),$match->id.",".$new_match_id))
		{
			$oldmatches=array_merge($oldmatches,$res);
		}
		$lists['old_match']=JHTML::_(	'select.genericlist',
										$oldmatches,
										'old_match_id',
										'class="inputbox" size="1"',
										'value',
										'text',$match->old_match_id);

		$newmatches[]=JHTML::_('select.option','0',JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_NEW_MATCH'));
		$res=array();
		$old_match_id=($match->old_match_id) ? $match->old_match_id : 0;
		if ($res =& $mdlMatch->getMatchRelationsOptions($mainframe->getUserState($option.'project',0),$match->id.",".$old_match_id))
		{
			$newmatches=array_merge($newmatches,$res);
		}
		$lists['new_match']=JHTML::_(	'select.genericlist',
										$newmatches,
										'new_match_id',
										'class="inputbox" size="1"',
										'value',
										'text',$match->new_match_id);
    // diddipoeler
		$mdlImages = JModel::getInstance("Imagehandler", "JoomleagueModel");
		$images 	= $mdlImages->get('Images');
		$this->assignRef('images', 	$images);
		
		$this->assignRef('overall_config',$overall_config);
		$this->assignRef('table_config',$table_config);
		$this->assignRef('projectws',$projectws);
		$this->assignRef('lists',$lists);
		$this->assignRef('match',$match);
		$this->assignRef('extended',$extended);
		$this->assignRef('folder',$dest);
		
		$this->assignRef('form',  $this->get('form'));		

		parent::display($tpl);
	}

/**
	 * Method to set up the document properties
	 *
	 * @return void
	 */
	protected function setDocument() 
	{
		$isNew = $this->item->id == 0;
		$document = JFactory::getDocument();
		$document->setTitle($isNew ? JText::_('COM_HELLOWORLD_HELLOWORLD_CREATING') : JText::_('COM_HELLOWORLD_HELLOWORLD_EDITING'));
		$document->addScript(JURI::root() . $this->script);
		$document->addScript(JURI::root() . "/administrator/components/com_sportsmanagement/views/sportsmanagement/submitbutton.js");
		JText::script('COM_HELLOWORLD_HELLOWORLD_ERROR_UNACCEPTABLE');
	}
    
    /**
	 * Setting the toolbar
	 */
	protected function addToolBar() 
	{
		JRequest::setVar('hidemainmenu', true);
		$user = JFactory::getUser();
		$userId = $user->id;
		$isNew = $this->item->id == 0;
		$canDo = sportsmanagementHelper::getActions($this->item->id);
		JToolBarHelper::title($isNew ? JText::_('COM_SPORTSMANAGEMENT_MATCH_NEW') : JText::_('COM_SPORTSMANAGEMENT_MATCH_EDIT'), 'helloworld');
		// Built the actions for new and existing records.
		if ($isNew) 
		{
			// For new records, check the create permission.
			if ($canDo->get('core.create')) 
			{
				JToolBarHelper::apply('match.apply', 'JTOOLBAR_APPLY');
				JToolBarHelper::save('match.save', 'JTOOLBAR_SAVE');
				JToolBarHelper::custom('match.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
			}
			JToolBarHelper::cancel('match.cancel', 'JTOOLBAR_CANCEL');
		}
		else
		{
			if ($canDo->get('core.edit'))
			{
				// We can save the new record
				JToolBarHelper::apply('match.apply', 'JTOOLBAR_APPLY');
				JToolBarHelper::save('match.save', 'JTOOLBAR_SAVE');
 
				// We can save this record, but check the create permission to see if we can return to make a new one.
				if ($canDo->get('core.create')) 
				{
					JToolBarHelper::custom('match.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
				}
			}
			if ($canDo->get('core.create')) 
			{
				JToolBarHelper::custom('match.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
			}
			JToolBarHelper::cancel('match.cancel', 'JTOOLBAR_CLOSE');
		}
	}

}
?>
