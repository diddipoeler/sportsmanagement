<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage match
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.environment.browser');
jimport('joomla.filesystem.file');

// welche joomla version
if(version_compare(JVERSION,'3.0.0','ge')) 
{
JHtml::_('behavior.framework', true);
}
else
{
JHtml::_( 'behavior.mootools' );    
}


/**
 * sportsmanagementViewMatch
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewMatch extends sportsmanagementView
{

	/**
	 * sportsmanagementViewMatch::init()
	 * 
	 * @return
	 */
	public function init ()
	{
		$app = JFactory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
        $user = JFactory::getUser();
        $uri = JFactory::getURI();
		$model = $this->getModel();
        $document = JFactory::getDocument();
        $browser = JBrowser::getInstance();
        $config = JComponentHelper::getParams ( 'com_media' );
        $this->config	= $config ;
$this->request_url	= $uri->toString();
        $project_id	= $app->getUserState( "$option.pid", '0' );
        $this->project_id	= $project_id;
        $default_name_format = '';
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' project_id -> '.$project_id.''),'');
        
        $mdlProject = JModelLegacy::getInstance("Project", "sportsmanagementModel");
	    $projectws = $mdlProject->getProject($this->project_id);
        $this->projectws	= $projectws;
        $this->eventsprojecttime	= $projectws->game_regular_time;
        
        //JFactory::getApplication()->input->setVar('hidemainmenu', true);
        
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
        
		// Assign the Data
		$this->form = $form;
		$this->item = $item;
		$this->script = $script;
        
		//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' item<br><pre>'.print_r($this->item,true).'</pre>'   ),'');
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' getLayout -> '.$this->getLayout().''),'');
        
        $match = $model->getMatchData($this->item->id);
		$extended = sportsmanagementHelper::getExtended($item->extended, 'match');
		$this->extended	= $extended;
        $this->match	= $match;
		$this->cfg_which_media_tool	= JComponentHelper::getParams($option)->get('cfg_which_media_tool',0);
        
        switch ( $this->getLayout() )
        {
        case 'pressebericht';
        case 'pressebericht_3';
        case 'pressebericht_4';
        $this->setLayout('pressebericht');
        break;
	case 'savepressebericht';
	case 'savepressebericht_3';		
	case 'savepressebericht_4';		
	$this->setLayout('savepressebericht');	
	$this->_displaySavePressebericht();		
	break;
        case 'readpressebericht';
        case 'readpressebericht_3';
        case 'readpressebericht_4';
	$this->setLayout('readpressebericht');
        $this->initPressebericht(); 
        break;
        case 'editreferees';
        case 'editreferees_3';
        case 'editreferees_4';
        $this->setLayout('editreferees');
        $this->initEditReferees();
        break;
        case 'editevents';
        case 'editevents_3';
        case 'editevents_4';
        $this->setLayout('editevents');
        $this->initEditEevents();
        break;
        case 'editeventsbb';
        case 'editeventsbb_3';
        case 'editeventsbb_4';
	$this->setLayout('editeventsbb');
        $this->initEditEeventsBB();
        break;
        case 'editstats';
        case 'editstats_3';
        case 'editstats_4';
        $this->setLayout('editstats');  
	$this->initEditStats();
        break;
        case 'editlineup';
        case 'editlineup_3';
        case 'editlineup_4';
        $this->setLayout('editlineup');
	$this->initEditLineup(); 
        break;
        case 'edit';
        case 'edit_3';
        case 'edit_4';
        $this->initEdit(); 
        break;
        case 'picture';
        case 'picture_3';
        case 'picture_4';
	$this->setLayout('picture');
        $this->initPicture();   
        break;
        }

	}
    
    /**
     * sportsmanagementViewMatch::initEditEeventsBB()
     * 
     * @return
     */
    public function initEditEeventsBB()
	{
		$app = JFactory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
		$project_id = $app->getUserState( "$option.pid", '0' );
		$document = JFactory::getDocument();
		$params = JComponentHelper::getParams( $option );
		$default_name_format = $params->get("name_format");

		$model = $this->getModel();
		$teams = $model->getMatchTeams($this->item->id);
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' teams'.'<pre>'.print_r($teams,true).'</pre>' ),'');

		$homeRoster = $model->getTeamPersons($teams->projectteam1_id,FALSE,1);
		//if (count($homeRoster)==0)
//		{
//			$homeRoster=$model->getGhostPlayerbb($teams->projectteam1_id);
//		}
		$awayRoster = $model->getTeamPersons($teams->projectteam2_id, FALSE, 1);
		//if (count($awayRoster)==0)
//		{
//			$awayRoster=$model->getGhostPlayerbb($teams->projectteam2_id);
//		}
		
        //$project_model = $this->getModel('projectws');
        
		// events
		$events = $model->getEventsOptions($project_id, $this->item->id);
		if (!$events)
		{
			JError::raiseWarning(440,'<br />'.JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_NO_EVENTS_POS').'<br /><br />');
			return;
		}

		$this->homeRoster	= $homeRoster;
		$this->awayRoster	= $awayRoster;
		$this->teams	= $teams;
		$this->events	= $events;
        
        // diddipoeler
        //$this->assignRef('eventsprojecttime',$project_model->_data->game_regular_time);

		$this->addToolbar_Editeventsbb();
        
        $this->setLayout('editeventsbb');		   
    }   
    
    /**
     * sportsmanagementViewMatch::initEdit()
     * 
     * @return void
     */
    public function initEdit()
	{
		$app = JFactory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
	$document = JFactory::getDocument();
    $model = $this->getModel();
    $project_id = $app->getUserState( "$option.pid", '0' );
    
    //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' item<br><pre>'.print_r($this->item,true).'</pre>'),'Notice');
    
    // match relation tab
		//$mdlMatch = JModelLegacy::getInstance ( 'match', 'JoomleagueModel' );
		$oldmatches [] = JHtml::_ ( 'select.option', '0', JText::_ ( 'COM_SPORTSMANAGEMENT_ADMIN_MATCH_OLD_MATCH' ) );
		$res = array ();
		$new_match_id = ($this->item->new_match_id) ? $this->item->new_match_id : 0;
		if ($res = $model->getMatchRelationsOptions ( $project_id, $this->item->id . "," . $new_match_id )) {
			foreach ( $res as $m ) {
				$m->text = '(' . sportsmanagementHelper::getMatchStartTimestamp ( $m ) . ') - ' . $m->t1_name . ' - ' . $m->t2_name;
			}
			$oldmatches = array_merge ( $oldmatches, $res );
		}
		$lists ['old_match'] = JHtml::_ ( 'select.genericlist', $oldmatches, 'old_match_id', 'class="inputbox" size="1"', 'value', 'text', $this->item->old_match_id );

		$newmatches [] = JHtml::_ ( 'select.option', '0', JText::_ ( 'COM_SPORTSMANAGEMENT_ADMIN_MATCH_NEW_MATCH' ) );
		$res = array ();
		$old_match_id = ($this->item->old_match_id) ? $this->item->old_match_id : 0;
		if ($res = $model->getMatchRelationsOptions ( $project_id, $this->item->id . "," . $old_match_id )) {
			foreach ( $res as $m ) {
				$m->text = '(' . sportsmanagementHelper::getMatchStartTimestamp ( $m ) . ') - ' . $m->t1_name . ' - ' . $m->t2_name;
			}
			$newmatches = array_merge ( $newmatches, $res );
		}
		$lists ['new_match'] = JHtml::_ ( 'select.genericlist', $newmatches, 'new_match_id', 'class="inputbox" size="1"', 'value', 'text', $this->item->new_match_id );

    //$match = $model->getMatchTeams($this->item->id);
		$lists['count_result'] = JHtml::_('select.booleanlist','count_result','class="radio btn-group btn-group-yesno"',$this->item->count_result);

//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' match<br><pre>'.print_r($match,true).'</pre>'),'Notice');
        
        // build the html select booleanlist which team got the won
        $myoptions = array();
        $myoptions[] = JHtml::_('select.option', '0', JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_NO_TEAM'));
        $myoptions[] = JHtml::_('select.option', '1', JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_HOME_TEAM'));
        $myoptions[] = JHtml::_('select.option', '2', JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_AWAY_TEAM'));
        $myoptions[] = JHtml::_('select.option', '3', JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_LOSS_BOTH_TEAMS'));
        $myoptions[] = JHtml::_('select.option', '4', JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_WON_BOTH_TEAMS'));
        $lists['team_won'] = JHtml::_('select.genericlist', $myoptions, 'team_won', 'class="inputbox" size="1"', 'value', 'text', $this->item->team_won);
        
        $this->lists	= $lists;
        $this->setLayout('edit');
    
    }
    
    /**
     * sportsmanagementViewMatch::initPicture()
     * 
     * @return void
     */
    public function initPicture()
	{
		$jinput = JFactory::getApplication()->input;
        $option = $jinput->getCmd('option');
		$document = JFactory::getDocument();
		$model = $this->getModel();
    

        $this->setLayout('picture');
    
    }
    
    /**
     * sportsmanagementViewMatch::initPressebericht()
     * 
     * @return
     */
    public function initPressebericht()
	{
		$app = JFactory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
		$document = JFactory::getDocument();
		$model = $this->getModel();
    
		$csv_file = $model->getPressebericht(); 
		$this->csv	= $csv_file; 
		$matchnumber = $model->getPresseberichtMatchnumber($csv_file);    
		$this->matchnumber	= $matchnumber;
		if ( $matchnumber )
			{
				$readplayers = $model->getPresseberichtReadPlayers($csv_file);  
				$this->csvplayers = $model->csv_player;   
				$this->csvinout	= $model->csv_in_out;
				$this->csvcards	= $model->csv_cards;
				$this->csvstaff	= $model->csv_staff;
			}

//build the html options for position
		$position_id[] = JHtml::_( 'select.option', '0', JText::_( 'COM_SPORTSMANAGEMENT_GLOBAL_SELECT_POSITION' ) );
		if ( $res = $model->getProjectPositionsOptions(0,1,$this->project_id) )
		{
			foreach( $res as $pos )
			{
			$pos->text = JText::_( $pos->text );
			$pos->value = $pos->posid;
			}

			$position_id = array_merge( $position_id, $res );
		}
		$lists['project_position_id'] = $position_id;
        $lists['inout_position_id'] = $position_id;
		unset( $position_id );
        
        $position_id[] = JHtml::_( 'select.option', '0', JText::_( 'COM_SPORTSMANAGEMENT_GLOBAL_SELECT_POSITION' ) );
		if ( $res = $model->getProjectPositionsOptions(0,2,$this->project_id) )
		{
			foreach( $res as $pos )
			{
			$pos->text = JText::_( $pos->text );
			$pos->value = $pos->posid;
			}

			$position_id = array_merge( $position_id, $res );
		}
		$lists['project_staff_position_id'] = $position_id;
		unset( $position_id );
        
        // events
		$events = $model->getEventsOptions($this->project_id, $matchnumber);
		if (!$events)
		{
			JError::raiseWarning(440,'<br />'.JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_NO_EVENTS_POS').'<br /><br />');
			return;
		}
		$eventlist = array();
        $eventlist[] = JHtml::_( 'select.option', '0', JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_SELECT_EVENT' ) );
		$eventlist = array_merge($eventlist, $events);
		
        $lists['events'] = $eventlist;
        unset( $eventlist );
        // build the html select booleanlist
        $myoptions = array();
	$myoptions[] = JHtml::_( 'select.option', '0', JText::_( 'JNO' ) );
	$myoptions[] = JHtml::_( 'select.option', '1', JText::_( 'JYES' ) );
        $lists['startaufstellung'] = $myoptions;
	    
        $this->lists = $lists;
    	$this->setLayout('readpressebericht');
    
    }
    
    /**
     * sportsmanagementViewMatch::initEditStats()
     * 
     * @return
     */
    public function initEditStats()
	{
		$app = JFactory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
		$document = JFactory::getDocument();
		$model = $this->getModel();
        $lists = array();
    
		$document->addScript(JURI::base().'components/'.$option.'/assets/js/sm_functions.js');
        $document->addScript(JURI::base().'components/'.$option.'/assets/js/editmatchstats.js');
        $teams = $model->getMatchTeams($this->item->id);
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' teams<br><pre>'.print_r($teams,true).'</pre>'),'');
        
        $positions = $model->getProjectPositionsOptions(0, 1,$this->project_id);
		$staffpositions = $model->getProjectPositionsOptions(0, 2,$this->project_id);
        
        //$homeRoster = $model->getTeamPlayers($teams->projectteam1_id);
        $homeRoster = $model->getMatchPersons($teams->projectteam1_id, 0, $this->item->id, 'player');
		if (count($homeRoster) == 0)
		{
			//$homeRoster=$model->getGhostPlayer();
		}
		//$awayRoster = $model->getTeamPlayers($teams->projectteam2_id);
        $awayRoster = $model->getMatchPersons($teams->projectteam2_id, 0, $this->item->id, 'player');
		if (count($awayRoster) == 0)
		{
			//$awayRoster=$model->getGhostPlayer();
		}
        
        //$homeStaff = $model->getMatchStaffs($teams->projectteam1_id,0,$this->item->id);
		//$awayStaff = $model->getMatchStaffs($teams->projectteam2_id,0,$this->item->id);
        $homeStaff = $model->getMatchPersons($teams->projectteam1_id, 0, $this->item->id, 'staff');
		$awayStaff = $model->getMatchPersons($teams->projectteam2_id, 0, $this->item->id, 'staff');
        
        // stats
		$stats = $model->getInputStats($this->project_id);
		if (!$stats)
		{
			JError::raiseWarning(440,'<br />'.JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_NO_STATS_POS').'<br /><br />');
			//return;
		}
		$playerstats = $model->getMatchStatsInput($this->item->id, $teams->projectteam1_id, $teams->projectteam2_id);
		$staffstats = $model->getMatchStaffStatsInput($this->item->id, $teams->projectteam1_id, $teams->projectteam2_id);
        
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' positions<br><pre>'.print_r($positions,true).'</pre>'),'');
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' stats<br><pre>'.print_r($stats,true).'</pre>'),'');
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' playerstats<br><pre>'.print_r($playerstats,true).'</pre>'),'');
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' staffstats<br><pre>'.print_r($staffstats,true).'</pre>'),'');
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' homeStaff<br><pre>'.print_r($homeStaff,true).'</pre>'),'');
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' awayStaff<br><pre>'.print_r($awayStaff,true).'</pre>'),'');
        
        $this->playerstats	= $playerstats;
		$this->staffstats	= $staffstats;
        $this->stats	= $stats;
        $this->homeStaff	= $homeStaff;
		$this->awayStaff	= $awayStaff;
        $this->positions	= $positions;
		$this->staffpositions	= $staffpositions;
        $this->homeRoster	= $homeRoster;
		$this->awayRoster	= $awayRoster;
        $this->teams	= $teams;
        $this->lists	= $lists;
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' teams<br><pre>'.print_r($this->teams,true).'</pre>'),'');
        
        $this->setLayout('editstats');
    }
    
    /**
     * sportsmanagementViewMatch::initEditEevents()
     * 
     * @return
     */
    public function initEditEevents()
	{
		$app = JFactory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
	$document = JFactory::getDocument();
    $model = $this->getModel();
    $params = JComponentHelper::getParams ( $option );
    $default_name_dropdown_list_order = $params->get ( "cfg_be_name_dropdown_list_order", "lastname" );
    $default_name_format = $params->get ( "name_format", 14 );
    
    $document->addScript(JURI::base().'components/'.$option.'/assets/js/sm_functions.js');
        //$document->addScript(JURI::base().'components/'.$option.'/assets/js/editevents.js');
        $document->addScript(JURI::base().'components/'.$option.'/assets/js/diddioeler.js');
        $document->addStyleSheet(JURI::base().'/components/'.$option.'/assets/css/sportsmanagement.css');
        
        $javascript = "\n";
        //$javascript .= "var baseajaxurl = '".JUri::root()."administrator/index.php?option=com_sportsmanagement&".JSession::getFormToken()."=1';" . "\n";
$javascript .= "var baseajaxurl = '".JUri::root()."administrator/index.php?option=com_sportsmanagement';". "\n";	    
        $javascript .= "var matchid = ".$this->item->id.";" . "\n";
        $javascript .= "var projecttime = ".$this->eventsprojecttime.";" . "\n";
        $javascript .= "var str_delete = '".JText::_('JACTION_DELETE')."';" . "\n";
        
        $javascript .= 'jQuery(document).ready(function() {' . "\n";
        $javascript .= "updatePlayerSelect();". "\n";
        $javascript .= "jQuery('#team_id').change(updatePlayerSelect);". "\n";
        $javascript .= '  });' . "\n";
        $javascript .= "\n";
        
       
        //$app->enqueueMessage(JText::_('sportsmanagementViewMatch editevents browser<br><pre>'.print_r($browser,true).'</pre>'   ),'');
        
        // mannschaften der paarung
       	$teams = $model->getMatchTeams($this->item->id);
        
        //$app->enqueueMessage(JText::_('sportsmanagementViewMatch editevents teams<br><pre>'.print_r($teams,true).'</pre>'   ),'');
        
		$teamlist=array();
		$teamlist[]=JHtml::_('select.option', $teams->projectteam1_id, $teams->team1);
		$teamlist[]=JHtml::_('select.option', $teams->projectteam2_id, $teams->team2);
		$lists['teams'] = JHtml::_('select.genericlist', $teamlist, 'team_id', 'class="inputbox select-team" ');
        // events
		$events = $model->getEventsOptions($this->project_id, 0);
		if (!$events)
		{
			JError::raiseWarning(440,'<br />'.JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_NO_EVENTS_POS').'<br /><br />');
			return;
		}
		$eventlist = array();
		$eventlist = array_merge($eventlist,$events);

		$lists['events'] = JHtml::_('select.genericlist', $eventlist, 'event_type_id', 'class="inputbox select-event"');
        
        //$homeRoster = $model->getTeamPlayers($teams->projectteam1_id);
        //$homeRoster = $model->getMatchPlayers($teams->projectteam1_id,0,$this->item->id);
        $homeRoster = $model->getMatchPersons($teams->projectteam1_id, 0, $this->item->id, 'player');
		if (count($homeRoster) == 0)
		{
			//$homeRoster=$model->getGhostPlayer();
		}
		
        //$awayRoster = $model->getTeamPlayers($teams->projectteam2_id);
        //$awayRoster = $model->getMatchPlayers($teams->projectteam2_id,0,$this->item->id);
        $awayRoster = $model->getMatchPersons($teams->projectteam2_id, 0, $this->item->id, 'player');
		if (count($awayRoster) == 0)
		{
			//$awayRoster=$model->getGhostPlayer();
		}
		$rosters = array('home' => $homeRoster, 'away' => $awayRoster);
        
        $matchCommentary = $model->getMatchCommentary($this->item->id);
        $matchevents = $model->getMatchEvents($this->item->id);
        $document->addScriptDeclaration( $javascript );
        
        $this->matchevents	= $matchevents;
        $this->matchcommentary	= $matchCommentary;
        $this->teams	= $teams;
        $this->rosters	= $rosters;
        $this->lists	= $lists;
        $this->default_name_format	= $default_name_format;
        $this->default_name_dropdown_list_order	= $default_name_dropdown_list_order;
        
        $this->setLayout('editevents');
        
    
    }
    
    /**
     * sportsmanagementViewMatch::initEditLineup()
     * 
     * @return
     */
    public function initEditLineup()
	{
		$app = JFactory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
	$document = JFactory::getDocument();
    $model = $this->getModel();
    $default_name_format = '';
    
    $document->addStyleSheet(JURI::base().'/components/'.$option.'/assets/css/sportsmanagement.css');
        $document->addScript(JURI::base().'components/'.$option.'/assets/js/sm_functions.js');  
        $document->addScript(JURI::base().'components/'.$option.'/assets/js/diddioeler.js');
        //$document->addScript(JURI::base().'components/'.$option.'/assets/js/editlineup.js');
        $tid = JFactory::getApplication()->input->getVar('team','0');
        $match = $model->getMatchTeams($this->item->id);
        $teamname = ($tid == $match->projectteam1_id) ? $match->team1 : $match->team2;
        
        // get starters
		$starters = $model->getMatchPersons($tid, 0, $this->item->id, 'player');
        $starters_id = array_keys($starters);
 
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' editlineup starters player'.'<pre>'.print_r($starters,true).'</pre>' ),'');
        

		// get players not already assigned to starter
        $not_assigned = $model->getTeamPersons($tid, $starters_id, 1);
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' editlineup not_assigned player'.'<pre>'.print_r($not_assigned,true).'</pre>' ),'');
        
		if (!$not_assigned && !$starters_id)
		{
			JError::raiseWarning(440,'<br />'.JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_NO_PLAYERS_MATCH').'<br /><br />');
			return;
		}

		$projectpositions = $model->getProjectPositionsOptions(0, 1, $this->project_id);
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' editlineup player projectpositions'.'<pre>'.print_r($projectpositions,true).'</pre>' ),'');
        		
        if (!$projectpositions)
		{
			JError::raiseWarning(440,'<br />'.JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_NO_POS').'<br /><br />');
			return;
		}

		// build select list for not assigned players
		$not_assigned_options = array();
		foreach ((array) $not_assigned AS $p)
		{
			$not_assigned_options[] = JHtml::_( 'select.option',$p->value,'['.$p->jerseynumber.'] '.
			  									sportsmanagementHelper::formatName(null, $p->firstname, $p->nickname, $p->lastname, $default_name_format) .
			  									' - ('.JText::_($p->positionname).')');
		}
		$lists['team_players'] = JHtml::_(	'select.genericlist', $not_assigned_options, 'roster[]',
											'style="font-size:12px;height:auto;min-width:15em;" class="inputbox" multiple="true" size="18"',
											'value', 'text');

		// build position select
		$selectpositions[] = JHtml::_('select.option', '0', JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_IN_POSITION'));
		$selectpositions = array_merge($selectpositions,$model->getProjectPositionsOptions(0, 1, $this->project_id));
		$lists['projectpositions'] = JHtml::_('select.genericlist', $selectpositions, 'project_position_id', 'class="inputbox" size="1"', 'posid', 'text', NULL, false, true);
		
        // build player select
		//$allplayers = $model->getTeamPlayers($tid);
        $allplayers = $model->getTeamPersons($tid, FALSE, 1);
		
        $playersoptionsout = array();
		$playersoptionsout[] = JHtml::_('select.option', '0', JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_PLAYER_OUT'));
		
        foreach ((array)$starters AS $player)
        //foreach ((array)$allplayers AS $player)
		{
			$playersoptionsout[] = JHtml::_('select.option', $player->value, 
			sportsmanagementHelper::formatName(null, $player->firstname, $player->nickname, $player->lastname, $default_name_format).' - ('.JText::_($player->positionname).')');
		}
        
        $playersoptionsin = array();
		$playersoptionsin[] = JHtml::_('select.option', '0', JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_PLAYER_IN'));
		
        foreach ((array)$not_assigned AS $player)
        //foreach ((array)$allplayers AS $player)
		{
			$playersoptionsin[] = JHtml::_('select.option', $player->value, 
			sportsmanagementHelper::formatName(null, $player->firstname, $player->nickname, $player->lastname, $default_name_format).' - ('.JText::_($player->positionname).')');
		}

/*		
        $lists['all_players']=JHtml::_(	'select.genericlist',$playersoptions,'roster[]',
										'id="roster" style="font-size:12px;height:auto;min-width:15em;" class="inputbox" size="4"',
										'value','text');
*/

		// generate selection list for each position
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' projectpositions'.'<pre>'.print_r($projectpositions,true).'</pre>' ),'');
		$starters = array();
		foreach ($projectpositions AS $position_id => $pos)
		{
			// get players assigned to this position
			$starters[$position_id] = $model->getRoster($tid, $pos->value,$this->item->id,$pos->text);
		}
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' editlineup starters player'.'<pre>'.print_r($starters,true).'</pre>' ),'');

		foreach ($starters AS $position_id => $players)
		{
			$options = array();
			foreach ((array) $players AS $p)
			{
				$options[] = JHtml::_('select.option', $p->value, '['.$p->jerseynumber.'] '. 
					sportsmanagementHelper::formatName(null, $p->firstname, $p->nickname, $p->lastname, $default_name_format));
			}

			$lists['team_players'.$position_id] = JHtml::_(	'select.genericlist',$options,'position'.$position_id.'[]',
															'style="font-size:12px;height:auto;min-width:15em;" size="4" class="position-starters" multiple="true" ',
															'value', 'text');
		}

		$substitutions = $model->getSubstitutions($tid, $this->item->id);
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' substitutions'.'<pre>'.print_r($substitutions,true).'</pre>' ),'');

		/**
		 * staff positions
		 */
		$staffpositions = $model->getProjectPositionsOptions(0, 2, $this->project_id);	// get staff not already assigned to starter
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' editlineup staff projectpositions'.'<pre>'.print_r($staffpositions,true).'</pre>' ),'');

		// assigned staff
        $assigned = $model->getMatchPersons($tid, 0, $this->item->id, 'staff');
		$assigned_id = array_keys($assigned);
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' editlineup assigned staff'.'<pre>'.print_r($assigned,true).'</pre>' ),'');
        
        
		// not assigned staff
        $not_assigned = $model->getTeamPersons($tid, $assigned_id, 2);

		// build select list for not assigned
		$not_assigned_options = array();
		foreach ((array) $not_assigned AS $p)
		{
			$not_assigned_options[] = JHtml::_('select.option',$p->value, 
				sportsmanagementHelper::formatName(null, $p->firstname, $p->nickname, $p->lastname, $default_name_format).' - ('.JText::_($p->positionname).')');
		}
		$lists['team_staffs'] = JHtml::_(	'select.genericlist', $not_assigned_options,'staff[]', 
										'style="font-size:12px;height:auto;min-width:15em;" size="18" class="inputbox" multiple="true" size="18"',
										'value', 'text');

		// generate selection list for each position
		$options = array();
		foreach ($staffpositions AS $position_id => $pos)
		{
			// get players assigned to this position
			$options = array();
			foreach ($assigned as $staff)
			{
				if ($staff->position_id == $pos->pposid)
                //if ($staff->pposid == $pos->pposid)
				{
					$options[] = JHtml::_('select.option', $staff->team_staff_id, 
						sportsmanagementHelper::formatName(null, $staff->firstname, $staff->nickname, $staff->lastname, $default_name_format));
				}
			}
			$lists['team_staffs'.$position_id] = JHtml::_(	'select.genericlist', $options, 'staffposition'.$position_id.'[]', 
															'style="font-size:12px;height:auto;min-width:15em;" size="4" class="position-staff" multiple="true" ',
															'value', 'text');
		}
        
        // build the html select booleanlist
        $myoptions = array();
		$myoptions[] = JHtml::_( 'select.option', '0', JText::_( 'JNO' ) );
		$myoptions[] = JHtml::_( 'select.option', '1', JText::_( 'JYES' ) );
        $lists['captain'] = $myoptions;


        $this->positions	= $projectpositions;
		$this->staffpositions	= $staffpositions;
		$this->substitutions	= $substitutions[$tid];
		$this->playersoptionsin	= $playersoptionsin;
        $this->playersoptionsout	= $playersoptionsout;
        $this->tid	= $tid;
		$this->teamname	= $teamname;
        $this->starters	= $starters;
        $this->lists	= $lists;
        
        
        $javascript = "\n";
        //$javascript .= "var baseajaxurl = '".JUri::root()."administrator/index.php?option=com_sportsmanagement&".JHtml::_('form.token')."=1';" . "\n";
        $javascript .= "var baseajaxurl = '".JUri::root()."administrator/index.php?option=com_sportsmanagement';". "\n";	   
	$javascript .= "var matchid = ".$this->item->id.";" . "\n";
        $javascript .= "var teamid = ".$this->tid.";" . "\n";
        $javascript .= "var projecttime = ".$this->eventsprojecttime.";" . "\n";
        $javascript .= "var str_delete = '".JText::_('JACTION_DELETE')."';" . "\n";
        $document->addScriptDeclaration( $javascript );
        
        $this->setLayout('editlineup');
    }
    
    /**
     * sportsmanagementViewMatch::initEditReferees()
     * 
     * @return
     */
    public function initEditReferees()
	{
		$app = JFactory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
	$document = JFactory::getDocument();
    $model = $this->getModel();
    $default_name_format = '';
    
	$document->addScript(JURI::base().'components/'.$option.'/assets/js/sm_functions.js');
        $document->addScript(JURI::base().'components/'.$option.'/assets/js/startinglineup.js');
        // projekt schiedsrichter
		$allreferees = array();
		//$allreferees = $model->getRefereeRoster(0,$this->item->id);
        $allreferees = $model->getRefereeRoster(0, $this->item->id);
		$inroster = array();
		$projectreferees = array();
		$projectreferees2 = array();

		if (isset($allreferees))
		{
			foreach ($allreferees AS $referee) {
				$inroster[] = $referee->value;
			}
		}
		$projectreferees = $model->getProjectReferees($inroster, $this->project_id);

		if (count($projectreferees) > 0)
		{
			foreach ($projectreferees AS $referee)
			{
				$projectreferees2[]=JHtml::_('select.option', $referee->value,
				  sportsmanagementHelper::formatName(null, $referee->firstname, $referee->nickname, $referee->lastname, $default_name_format) .
				  ' - ('.strtolower(JText::_($referee->positionname)).')');
			}
		}
		$lists['team_referees'] = JHtml::_(	'select.genericlist', $projectreferees2,'roster[]', 
											'style="font-size:12px;height:auto;min-width:15em;" ' .
											'class="inputbox" multiple="true" size="'.max(10,count($projectreferees2)).'"', 
											'value', 'text');
        // projekt positionen                                                    
  		$selectpositions[] = JHtml::_('select.option', '0', JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_REF_FUNCTION'));
		if ($projectpositions = $model->getProjectPositionsOptions(0, 3, $this->project_id))
		{
			$selectpositions = array_merge($selectpositions,$projectpositions);
		}
		$lists['projectpositions'] = JHtml::_('select.genericlist', $selectpositions, 'project_position_id', 'class="inputbox" size="1"', 'value', 'text');

		$squad = array();
		if (!$projectpositions)
		{
			JError::raiseWarning(440,'<br />'.JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_NO_REF_POS').'<br /><br />');
			return;
		}

		// generate selection list for each position
		foreach ($projectpositions AS $key => $pos)
		{
			// get referees assigned to this position
			$squad[$key] = $model->getRefereeRoster($pos->value, $this->item->id);
		}
        
		if (count($squad) > 0)
		{
			foreach ($squad AS $key => $referees)
			{
				$temp[$key] = array();
				if (isset($referees))
				{
					foreach ($referees AS $referee)
					{
						$temp[$key][] = JHtml::_('select.option',$referee->value, 
							sportsmanagementHelper::formatName(null, $referee->firstname, $referee->nickname, $referee->lastname, $default_name_format));
					}
				}
                
				$lists['team_referees'.$key] = JHtml::_(	'select.genericlist', $temp[$key], 'position'.$key.'[]', 
														' style="font-size:12px;height:auto;min-width:15em;" '.
														'class="position-starters" multiple="true" ', 
														'value','text');
                                     
                                                        
			}
		}
        
        //$app->enqueueMessage(JText::_('sportsmanagementViewMatch editreferees positions<br><pre>'.print_r($projectpositions,true).'</pre>'   ),'');
        //$app->enqueueMessage(JText::_('sportsmanagementViewMatch editreferees lists<br><pre>'.print_r($lists,true).'</pre>'   ),'');
        
		$this->positions	= $projectpositions;
		$this->lists	= $lists;
		
		$this->setLayout('editreferees');
    }   
 
 

    
    /**
     * sportsmanagementViewMatch::_displaySavePressebericht()
     * 
     * @param mixed $tpl
     * @return void
     */
    function _displaySavePressebericht()
    {
	$app = JFactory::getApplication();
	$jinput = $app->input;
	$option = $jinput->getCmd('option');
	$document = JFactory::getDocument();
$post = $app->input->post->getArray(array());
// $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' post<br><pre>'.print_r($post,true).'</pre>'),''); 	    
    $project_id = $app->getUserState( "$option.pid", '0' );;
    $model = $this->getModel();
    $csv_file_save = $model->savePressebericht($post);
    
    $this->importData	= $model->_success_text;
        
   // parent::display($tpl);    
    }
    
    /**
     * sportsmanagementViewMatch::_displayPressebericht()
     * 
     * @param mixed $tpl
     * @return
     */
    function _displayPressebericht()
    {
        $app = JFactory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
		$document = JFactory::getDocument();
        $project_id = $app->getUserState( "$option.pid", '0' );;
        $config = JComponentHelper::getParams ( 'com_media' );
        $this->config	= $config;
        
//$app->enqueueMessage(JText::_('displayPressebericht<br><pre>'.print_r($this->_datas['match'],true).'</pre>'   ),'');     
//    $document->addScript(JURI::root() . 'administrator/components/com_joomleague/assets/js/jquery.csv-0.71.js');       
//    $document->addScript(JURI::root() . 'administrator/components/com_joomleague/assets/js/jquery.csv.js');
$model = $this->getModel();
$csv_file = $model->getPressebericht(); 
$this->csv	= $csv_file; 
$matchnumber = $model->getPresseberichtMatchnumber($csv_file);    
$this->matchnumber	= $matchnumber;
if ( $matchnumber )
{
$readplayers = $model->getPresseberichtReadPlayers($csv_file);  
$this->csvplayers = $model->csv_player;   
$this->csvinout	= $model->csv_in_out;
$this->csvcards	= $model->csv_cards;
$this->csvstaff	= $model->csv_staff;
}

//build the html options for position
		$position_id[] = JHtml::_( 'select.option', '0', JText::_( 'COM_SPORTSMANAGEMENT_GLOBAL_SELECT_POSITION' ) );
		if ( $res = $model->getProjectPositionsOptions(0, 1) )
		{
			$position_id = array_merge( $position_id, $res );
		}
		$lists['project_position_id'] = $position_id;
        $lists['inout_position_id'] = $position_id;
		unset( $position_id );
        
        $position_id[] = JHtml::_( 'select.option', '0', JText::_( 'COM_SPORTSMANAGEMENT_GLOBAL_SELECT_POSITION' ) );
		if ( $res = $model->getProjectPositionsOptions(0,2) )
		{
			$position_id = array_merge( $position_id, $res );
		}
		$lists['project_staff_position_id'] = $position_id;
		unset( $position_id );
        
        // events
		$events = $model->getEventsOptions($project_id);
		if (!$events)
		{
			JError::raiseWarning(440,'<br />'.JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_NO_EVENTS_POS').'<br /><br />');
			return;
		}
		$eventlist = array();
        $eventlist[] = JHtml::_( 'select.option', '0', JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_SELECT_EVENT' ) );
		$eventlist = array_merge($eventlist, $events);
		
        $lists['events'] = $eventlist;
        unset( $eventlist );
        
	// build the html select booleanlist
        $myoptions = array();
	$myoptions[] = JHtml::_( 'select.option', '0', JText::_( 'JNO' ) );
	$myoptions[] = JHtml::_( 'select.option', '1', JText::_( 'JYES' ) );
        $lists['startaufstellung'] = $myoptions;
	    
        $this->lists = $lists;
 
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
		JToolbarHelper::title( JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_MATCH_EEBB_TITLE' ),'events' );
		JToolbarHelper::apply( 'match.saveeventbb' );
		JToolbarHelper::divider();
		JToolbarHelper::back( 'back', 'index.php?option=com_joomleague&view=matches&task=match.display' );
		//JLToolBarHelper::onlinehelp();	
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
		// Get a refrence of the page instance in joomla
		$document	= JFactory::getDocument();
        // Set toolbar items for the page
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
        $document->addCustomTag($stylelink);
        
		//JFactory::getApplication()->input->setVar('hidemainmenu', true);
		$jinput = JFactory::getApplication()->input;
        $jinput->set('hidemainmenu', true);
		$user = JFactory::getUser();
		$userId = $user->id;
		$isNew = $this->item->id == 0;
		$canDo = sportsmanagementHelper::getActions($this->item->id);
		JToolbarHelper::title($isNew ? JText::_('COM_SPORTSMANAGEMENT_MATCH_NEW') : JText::_('COM_SPORTSMANAGEMENT_MATCH_EDIT'), 'match');
		// Built the actions for new and existing records.
		if ($isNew) 
		{
			// For new records, check the create permission.
			if ($canDo->get('core.create')) 
			{
				JToolbarHelper::apply('match.apply', 'JTOOLBAR_APPLY');
				JToolbarHelper::save('match.save', 'JTOOLBAR_SAVE');
				JToolbarHelper::custom('match.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
			}
			JToolbarHelper::cancel('match.cancel', 'JTOOLBAR_CANCEL');
		}
		else
		{
			if ($canDo->get('core.edit'))
			{
				// We can save the new record
				JToolbarHelper::apply('match.apply', 'JTOOLBAR_APPLY');
				JToolbarHelper::save('match.save', 'JTOOLBAR_SAVE');
 
				// We can save this record, but check the create permission to see if we can return to make a new one.
				if ($canDo->get('core.create')) 
				{
					JToolbarHelper::custom('match.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
				}
			}
			if ($canDo->get('core.create')) 
			{
				JToolbarHelper::custom('match.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
			}
			JToolbarHelper::cancel('match.cancel', 'JTOOLBAR_CLOSE');
		}
	}

}
?>
