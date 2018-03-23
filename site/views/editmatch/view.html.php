<?php

/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage editmatch
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

//jimport('joomla.application.component.view');
// welche joomla version ?
if (version_compare(JVERSION, '3.0.0', 'ge')) {
    jimport('joomla.html.html.bootstrap');
}

/**
 * sportsmanagementViewEditMatch
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2018
 * @version $Id$
 * @access public
 */
class sportsmanagementViewEditMatch extends JViewLegacy 
{

    /**
     * sportsmanagementViewEditMatch::display()
     * 
     * @param mixed $tpl
     * @return void
     */
    function display($tpl = null) {

        $option = JFactory::getApplication()->input->getCmd('option');
        $app = JFactory::getApplication();
        $document = JFactory::getDocument();
        $db = JFactory::getDBO();
        if (version_compare(JSM_JVERSION, '4', 'eq')) {
            $uri = JUri::getInstance();
        } else {
            $uri = JFactory::getURI();
        }
        $user = JFactory::getUser();
        // JInput object
        $jinput = $app->input;
        $model = $this->getModel();

        $this->project_id = $jinput->getInt('p', 0);
        sportsmanagementModelProject::setProjectID($this->project_id);
        //sportsmanagementModelMatch::$_project_id = $this->project_id; 
        //$projectws = $mdlProject->getProject($project_id);
        $projectws = sportsmanagementModelProject::getProject($jinput->getInt('cfg_which_database', 0));
        //sportsmanagementModelMatch::$_project_id = $projectws->id;

        $app->setUserState("$option.pid", $projectws->id);
        $app->setUserState("$option.season_id", $projectws->season_id);

        $this->projectws = $projectws;
        $this->eventsprojecttime = $projectws->game_regular_time;

//        $params         = $app->getParams();
//        $dispatcher = JDispatcher::getInstance();
//        
//        // Get some data from the models
//        $state          = $this->get('State');
        $match = $this->get('Data');
        $extended = sportsmanagementHelper::getExtended($match->extended, 'match');
        $this->extended = $extended;
        $this->match = $match;
        $this->form = $this->get('Form');

        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' match<br><pre>'.print_r($this->match,true).'</pre>'),'');
        //$document->addScript(JURI::base().'components/'.$option.'/assets/js/sm_functions.js');
        //$document->addScript(JURI::base() . 'administrator/components/' . $option . '/assets/js/diddioeler.js');
        //$document->addStyleSheet(JURI::base().'/components/'.$option.'/assets/css/sportsmanagement.css');

        switch ($this->getLayout()) {
            case 'editreferees';
            case 'editreferees_3';
            case 'editreferees_4';
                $this->initEditReferees();
                break;
            case 'edit';
            case 'edit_3';
            case 'edit_4';
                $this->initEditMatch();
                break;
            case 'editstats';
            case 'editstats_3';
            case 'editstats_4';
                $this->initEditStats();
                break;
            case 'editevents';
            case 'editevents_3';
            case 'editevents_4';
                $this->initEditEevents();
                break;
            case 'editlineup';
            case 'editlineup_3';
            case 'editlineup_4';
                $this->initEditLineup();
                break;
        }

        parent::display($tpl);
    }

    /**
     * sportsmanagementViewEditMatch::initEditLineup()
     * 
     * @return
     */
    function initEditLineup() {
        $app = JFactory::getApplication();
        $option = JFactory::getApplication()->input->getCmd('option');
        $document = JFactory::getDocument();
        $default_name_format = '';

        $tid = JFactory::getApplication()->input->getVar('team', '0');
        $this->tid = $tid;
        $match = sportsmanagementModelMatch::getMatchTeams($this->match->id);
        $teamname = ($tid == $match->projectteam1_id) ? $match->team1 : $match->team2;
        $this->teamname = $teamname;

        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' tid'.'<pre>'.print_r($tid,true).'</pre>' ),'');
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' teamname'.'<pre>'.print_r($teamname,true).'</pre>' ),'');
        // get starters
        $starters = sportsmanagementModelMatch::getMatchPersons($tid, 0, $this->match->id, 'player');
        $starters_id = array_keys($starters);

        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' editlineup starters player'.'<pre>'.print_r($starters,true).'</pre>' ),'');
        // get players not already assigned to starter
        $not_assigned = sportsmanagementModelMatch::getTeamPersons($tid, $starters_id, 1);

        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' editlineup not_assigned player'.'<pre>'.print_r($not_assigned,true).'</pre>' ),'');

        $playersoptionsout = array();
        $playersoptionsout[] = JHtml::_('select.option', '0', JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_PLAYER_OUT'));
        $playersoptionsin = array();
        $playersoptionsin[] = JHtml::_('select.option', '0', JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_PLAYER_IN'));

        if (!$not_assigned && !$starters_id) {
            $this->playersoptionsout = $playersoptionsout;
            $this->playersoptionsin = $playersoptionsin;
            JError::raiseWarning(440, '<br />' . JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_NO_PLAYERS_MATCH') . '<br /><br />');
            return;
        }

        $projectpositions = sportsmanagementModelMatch::getProjectPositionsOptions(0, 1, $this->project_id);

        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' editlineup player projectpositions'.'<pre>'.print_r($projectpositions,true).'</pre>' ),'');

        if (!$projectpositions) {
            $this->playersoptionsout = $playersoptionsout;
            $this->playersoptionsin = $playersoptionsin;
            JError::raiseWarning(440, '<br />' . JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_NO_POS') . '<br /><br />');
            return;
        }

        // build select list for not assigned players
        $not_assigned_options = array();
        foreach ((array) $not_assigned AS $p) {
            $not_assigned_options[] = JHtml::_('select.option', $p->value, '[' . $p->jerseynumber . '] ' .
                            sportsmanagementHelper::formatName(null, $p->firstname, $p->nickname, $p->lastname, $default_name_format) .
                            ' - (' . JText::_($p->positionname) . ')');
        }
        $lists['team_players'] = JHtml::_('select.genericlist', $not_assigned_options, 'roster[]', 'style="font-size:12px;height:auto;min-width:15em;" class="inputbox" multiple="true" size="18"', 'value', 'text');

        // build position select
        $selectpositions[] = JHtml::_('select.option', '0', JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_IN_POSITION'));
        $selectpositions = array_merge($selectpositions, sportsmanagementModelMatch::getProjectPositionsOptions(0, 1, $this->project_id));
        $lists['projectpositions'] = JHtml::_('select.genericlist', $selectpositions, 'project_position_id', 'class="inputbox" size="1"', 'posid', 'text', NULL, false, true);

        // build player select
        //$allplayers = $model->getTeamPlayers($tid);
        $allplayers = sportsmanagementModelMatch::getTeamPersons($tid, FALSE, 1);



        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' playersoptionsout'.'<pre>'.print_r($playersoptionsout,true).'</pre>' ),'');

        foreach ((array) $starters AS $player) {
        //foreach ((array)$allplayers AS $player)
            $playersoptionsout[] = JHtml::_('select.option', $player->value, sportsmanagementHelper::formatName(null, $player->firstname, $player->nickname, $player->lastname, $default_name_format) . ' - (' . JText::_($player->positionname) . ')');
        }
        $this->playersoptionsout = $playersoptionsout;

        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' playersoptionsout'.'<pre>'.print_r($playersoptionsout,true).'</pre>' ),'');
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' playersoptionsin'.'<pre>'.print_r($playersoptionsin,true).'</pre>' ),'');

        foreach ((array) $not_assigned AS $player) {
        //foreach ((array)$allplayers AS $player)
            $playersoptionsin[] = JHtml::_('select.option', $player->value, sportsmanagementHelper::formatName(null, $player->firstname, $player->nickname, $player->lastname, $default_name_format) . ' - (' . JText::_($player->positionname) . ')');
        }
        $this->playersoptionsin = $playersoptionsin;

        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' playersoptionsin'.'<pre>'.print_r($playersoptionsin,true).'</pre>' ),'');

        /* 		
          $lists['all_players']=JHtml::_(	'select.genericlist',$playersoptions,'roster[]',
          'id="roster" style="font-size:12px;height:auto;min-width:15em;" class="inputbox" size="4"',
          'value','text');
         */

        // generate selection list for each position
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' projectpositions'.'<pre>'.print_r($projectpositions,true).'</pre>' ),'');
        $starters = array();
        foreach ($projectpositions AS $position_id => $pos) {
            // get players assigned to this position
            $starters[$position_id] = sportsmanagementModelMatch::getRoster($tid, $pos->value, $this->match->id, $pos->text);
        }

        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' editlineup starters player'.'<pre>'.print_r($starters,true).'</pre>' ),'');

        foreach ($starters AS $position_id => $players) {
            $options = array();
            foreach ((array) $players AS $p) {
                $options[] = JHtml::_('select.option', $p->value, '[' . $p->jerseynumber . '] ' .
                                sportsmanagementHelper::formatName(null, $p->firstname, $p->nickname, $p->lastname, $default_name_format));
            }

            $lists['team_players' . $position_id] = JHtml::_('select.genericlist', $options, 'position' . $position_id . '[]', 'style="font-size:12px;height:auto;min-width:15em;" size="4" class="position-starters" multiple="true" ', 'value', 'text');
        }

        $substitutions = sportsmanagementModelMatch::getSubstitutions($tid, $this->match->id);

        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' substitutions'.'<pre>'.print_r($substitutions,true).'</pre>' ),'');

        /**
         * staff positions
         */
        $staffpositions = sportsmanagementModelMatch::getProjectPositionsOptions(0, 2, $this->project_id); // get staff not already assigned to starter
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' editlineup staff projectpositions'.'<pre>'.print_r($staffpositions,true).'</pre>' ),'');
        // assigned staff
        $assigned = sportsmanagementModelMatch::getMatchPersons($tid, 0, $this->match->id, 'staff');
        $assigned_id = array_keys($assigned);

        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' editlineup assigned staff'.'<pre>'.print_r($assigned,true).'</pre>' ),'');
        // not assigned staff
        $not_assigned = sportsmanagementModelMatch::getTeamPersons($tid, $assigned_id, 2);

        // build select list for not assigned
        $not_assigned_options = array();
        foreach ((array) $not_assigned AS $p) {
            $not_assigned_options[] = JHtml::_('select.option', $p->value, sportsmanagementHelper::formatName(null, $p->firstname, $p->nickname, $p->lastname, $default_name_format) . ' - (' . JText::_($p->positionname) . ')');
        }
        $lists['team_staffs'] = JHtml::_('select.genericlist', $not_assigned_options, 'staff[]', 'style="font-size:12px;height:auto;min-width:15em;" size="18" class="inputbox" multiple="true" size="18"', 'value', 'text');

        // generate selection list for each position
        $options = array();
        foreach ($staffpositions AS $position_id => $pos) {
            // get players assigned to this position
            $options = array();
            foreach ($assigned as $staff) {
                if ($staff->position_id == $pos->pposid) {
                //if ($staff->pposid == $pos->pposid)
                    $options[] = JHtml::_('select.option', $staff->team_staff_id, sportsmanagementHelper::formatName(null, $staff->firstname, $staff->nickname, $staff->lastname, $default_name_format));
                }
            }
            $lists['team_staffs' . $position_id] = JHtml::_('select.genericlist', $options, 'staffposition' . $position_id . '[]', 'style="font-size:12px;height:auto;min-width:15em;" size="4" class="position-staff" multiple="true" ', 'value', 'text');
        }

        // build the html select booleanlist
        $myoptions = array();
        $myoptions[] = JHtml::_('select.option', '0', JText::_('JNO'));
        $myoptions[] = JHtml::_('select.option', '1', JText::_('JYES'));
        $lists['captain'] = $myoptions;

        $this->positions = $projectpositions;
        $this->staffpositions = $staffpositions;
        $this->substitutions = $substitutions[$tid];
        $this->starters = $starters;
        $this->lists = $lists;
    }

    /**
     * sportsmanagementViewEditMatch::initEditEevents()
     * 
     * @return
     */
    function initEditEevents() {
        $app = JFactory::getApplication();
        $option = JFactory::getApplication()->input->getCmd('option');
        $document = JFactory::getDocument();
        //$model = $this->getModel();
        $params = JComponentHelper::getParams($option);
        $default_name_dropdown_list_order = $params->get("cfg_be_name_dropdown_list_order", "lastname");
        $default_name_format = $params->get("name_format", 14);

$document->addScript(JURI::base().'components/'.$option.'/assets/js/diddioeler.js');
	    
        //$app->enqueueMessage(JText::_('sportsmanagementViewMatch editevents browser<br><pre>'.print_r($browser,true).'</pre>'   ),'');
        // mannschaften der paarung
        $teams = sportsmanagementModelMatch::getMatchTeams($this->match->id);
        $this->teams = $teams;

        //$app->enqueueMessage(JText::_('sportsmanagementViewMatch editevents teams<br><pre>'.print_r($teams,true).'</pre>'   ),'');

        $teamlist = array();
        $teamlist[] = JHtml::_('select.option', $teams->projectteam1_id, $teams->team1);
        $teamlist[] = JHtml::_('select.option', $teams->projectteam2_id, $teams->team2);
        $lists['teams'] = JHtml::_('select.genericlist', $teamlist, 'team_id', 'onchange="updatePlayerSelect();" class="inputbox select-team" ');
        // events
        $events = sportsmanagementModelMatch::getEventsOptions($this->project_id, 0);
        if (!$events) {
            JError::raiseWarning(440, '<br />' . JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_NO_EVENTS_POS') . '<br /><br />');
            return;
        }
        $eventlist = array();
        $eventlist = array_merge($eventlist, $events);

        $lists['events'] = JHtml::_('select.genericlist', $eventlist, 'event_type_id', 'class="inputbox select-event"');

        //$homeRoster = $model->getTeamPlayers($teams->projectteam1_id);
        //$homeRoster = $model->getMatchPlayers($teams->projectteam1_id,0,$this->item->id);
        $homeRoster = sportsmanagementModelMatch::getMatchPersons($teams->projectteam1_id, 0, $this->match->id, 'player');
        if (count($homeRoster) == 0) {
            //$homeRoster=$model->getGhostPlayer();
        }

        //$awayRoster = $model->getTeamPlayers($teams->projectteam2_id);
        //$awayRoster = $model->getMatchPlayers($teams->projectteam2_id,0,$this->item->id);
        $awayRoster = sportsmanagementModelMatch::getMatchPersons($teams->projectteam2_id, 0, $this->match->id, 'player');
        if (count($awayRoster) == 0) {
            //$awayRoster=$model->getGhostPlayer();
        }
        $rosters = array('home' => $homeRoster, 'away' => $awayRoster);

foreach ($rosters['home'] as $player)
{
	$obj = new stdclass();
	$obj->value = $player->value;
	switch ($default_name_dropdown_list_order)
	{
		case 'lastname':
			$obj->text  = sportsmanagementHelper::formatName(null, $player->firstname, $player->nickname, $player->lastname, $this->default_name_format);
			break;

		case 'firstname':
			$obj->text  = sportsmanagementHelper::formatName(null, $player->firstname, $player->nickname, $player->lastname, $this->default_name_format);
			break;

		case 'position':
			$obj->text  = '('.JText::_($player->positionname).') - '.sportsmanagementHelper::formatName(null, $player->firstname, $player->nickname, $player->lastname, $this->default_name_format);
			break;
	}
	$temp[] = $obj;
}
$lists['homeroster'] = JHtml::_('select.genericlist', $temp, $teams->projectteam1_id, 'style="" size="" class="inputbox" size="1"', 'value', 'text');
foreach ($rosters['away'] as $player)
{
	$obj = new stdclass();
	$obj->value = $player->value;
	switch ($default_name_dropdown_list_order)
	{
		case 'lastname':
			$obj->text  = sportsmanagementHelper::formatName(null, $player->firstname, $player->nickname, $player->lastname, $this->default_name_format);
			break;

		case 'firstname':
			$obj->text  = sportsmanagementHelper::formatName(null, $player->firstname, $player->nickname, $player->lastname, $this->default_name_format);
			break;

		case 'position':
			$obj->text  = '('.JText::_($player->positionname).') - '.sportsmanagementHelper::formatName(null, $player->firstname, $player->nickname, $player->lastname, $this->default_name_format);
			break;
	}
	$temp[] = $obj;
}
$lists['awayroster'] = JHtml::_('select.genericlist', $temp, $teams->projectteam2_id, 'style="" size="1" class="inputbox" size="1"', 'value', 'text');

//$app->enqueueMessage(JText::_('sportsmanagementViewMatch editevents awayRoster<br><pre>'.print_r($lists,true).'</pre>'   ),'');        
        
        $matchCommentary = sportsmanagementModelMatch::getMatchCommentary($this->match->id);
        $matchevents = sportsmanagementModelMatch::getMatchEvents($this->match->id);
        //$document->addScriptDeclaration( $javascript );

        $this->matchevents = $matchevents;
        $this->matchcommentary = $matchCommentary;
        $this->rosters = $rosters;
        $this->lists = $lists;
        $this->default_name_format = $default_name_format;
        $this->default_name_dropdown_list_order = $default_name_dropdown_list_order;
    }

    /**
     * sportsmanagementViewEditMatch::initEditStats()
     * 
     * @return void
     */
    function initEditStats() {
        $teams = sportsmanagementModelMatch::getMatchTeams($this->match->id);

        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' teams<br><pre>'.print_r($teams,true).'</pre>'),'');

        $positions = sportsmanagementModelMatch::getProjectPositionsOptions(0, 1, $this->project_id);
        $staffpositions = sportsmanagementModelMatch::getProjectPositionsOptions(0, 2, $this->project_id);

        //$homeRoster = $model->getTeamPlayers($teams->projectteam1_id);
        $homeRoster = sportsmanagementModelMatch::getMatchPersons($teams->projectteam1_id, 0, $this->match->id, 'player');
        if (count($homeRoster) == 0) {
            //$homeRoster=$model->getGhostPlayer();
        }
        //$awayRoster = $model->getTeamPlayers($teams->projectteam2_id);
        $awayRoster = sportsmanagementModelMatch::getMatchPersons($teams->projectteam2_id, 0, $this->match->id, 'player');
        if (count($awayRoster) == 0) {
            //$awayRoster=$model->getGhostPlayer();
        }

        //$homeStaff = $model->getMatchStaffs($teams->projectteam1_id,0,$this->item->id);
        //$awayStaff = $model->getMatchStaffs($teams->projectteam2_id,0,$this->item->id);
        $homeStaff = sportsmanagementModelMatch::getMatchPersons($teams->projectteam1_id, 0, $this->match->id, 'staff');
        $awayStaff = sportsmanagementModelMatch::getMatchPersons($teams->projectteam2_id, 0, $this->match->id, 'staff');

        // stats
        $stats = sportsmanagementModelMatch::getInputStats($this->project_id);
        if (!$stats) {
            JError::raiseWarning(440, '<br />' . JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_NO_STATS_POS') . '<br /><br />');
            //return;
        }
        $playerstats = sportsmanagementModelMatch::getMatchStatsInput($this->match->id, $teams->projectteam1_id, $teams->projectteam2_id);
        $staffstats = sportsmanagementModelMatch::getMatchStaffStatsInput($this->match->id, $teams->projectteam1_id, $teams->projectteam2_id);

//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' positions<br><pre>'.print_r($positions,true).'</pre>'),'');
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' stats<br><pre>'.print_r($stats,true).'</pre>'),'');
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' playerstats<br><pre>'.print_r($playerstats,true).'</pre>'),'');
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' staffstats<br><pre>'.print_r($staffstats,true).'</pre>'),'');
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' homeStaff<br><pre>'.print_r($homeStaff,true).'</pre>'),'');
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' awayStaff<br><pre>'.print_r($awayStaff,true).'</pre>'),'');

        $this->playerstats = $playerstats;
        $this->staffstats = $staffstats;
        $this->stats = $stats;
        $this->homeStaff = $homeStaff;
        $this->awayStaff = $awayStaff;
        $this->positions = $positions;
        $this->staffpositions = $staffpositions;
        $this->homeRoster = $homeRoster;
        $this->awayRoster = $awayRoster;
        $this->teams = $teams;
    }

    /**
     * sportsmanagementViewEditMatch::initEditMatch()
     * 
     * @return void
     */
    function initEditMatch() {
        $app = JFactory::getApplication();
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' getLayout -> '.$this->getLayout().''),'');  
        $oldmatches [] = JHtml::_('select.option', '0', JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_OLD_MATCH'));
        $res = array();
        $new_match_id = ($this->match->new_match_id) ? $this->match->new_match_id : 0;
        if ($res = sportsmanagementModelMatch::getMatchRelationsOptions($this->project_id, $this->match->id . "," . $new_match_id)) {
            foreach ($res as $m) {
                $m->text = '(' . sportsmanagementHelper::getMatchStartTimestamp($m) . ') - ' . $m->t1_name . ' - ' . $m->t2_name;
            }
            $oldmatches = array_merge($oldmatches, $res);
        }
        $lists ['old_match'] = JHtml::_('select.genericlist', $oldmatches, 'old_match_id', 'class="inputbox" size="1"', 'value', 'text', $this->match->old_match_id);

        // $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' oldmatches<br><pre>'.print_r($oldmatches,true).'</pre>'),'');

        $newmatches [] = JHtml::_('select.option', '0', JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_NEW_MATCH'));
        $res = array();
        $old_match_id = ($this->match->old_match_id) ? $this->match->old_match_id : 0;
        if ($res = sportsmanagementModelMatch::getMatchRelationsOptions($this->project_id, $this->match->id . "," . $old_match_id)) {
            foreach ($res as $m) {
                $m->text = '(' . sportsmanagementHelper::getMatchStartTimestamp($m) . ') - ' . $m->t1_name . ' - ' . $m->t2_name;
            }
            $newmatches = array_merge($newmatches, $res);
        }
        $lists ['new_match'] = JHtml::_('select.genericlist', $newmatches, 'new_match_id', 'class="inputbox" size="1"', 'value', 'text', $this->match->new_match_id);

        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' newmatches<br><pre>'.print_r($newmatches,true).'</pre>'),'');

        $lists['count_result'] = JHtml::_('select.booleanlist', 'count_result', 'class="btn btn-primary"', $this->match->count_result);
        // build the html select booleanlist which team got the won
        $myoptions = array();
        $myoptions[] = JHtml::_('select.option', '0', JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_NO_TEAM'));
        $myoptions[] = JHtml::_('select.option', '1', JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_HOME_TEAM'));
        $myoptions[] = JHtml::_('select.option', '2', JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_AWAY_TEAM'));
        $myoptions[] = JHtml::_('select.option', '3', JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_LOSS_BOTH_TEAMS'));
        $myoptions[] = JHtml::_('select.option', '4', JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_WON_BOTH_TEAMS'));
        $lists['team_won'] = JHtml::_('select.genericlist', $myoptions, 'team_won', 'class="inputbox" size="1"', 'value', 'text', $this->match->team_won);

        $this->lists = $lists;
    }

    /**
     * sportsmanagementViewEditMatch::initEditReferees()
     * 
     * @return
     */
    function initEditReferees() {
// projekt schiedsrichter
        $allreferees = array();
        //$allreferees = $model->getRefereeRoster(0,$this->item->id);
        $allreferees = sportsmanagementModelMatch::getRefereeRoster(0, $this->match->id);
        $inroster = array();
        $projectreferees = array();
        $projectreferees2 = array();

        if (isset($allreferees)) {
            foreach ($allreferees AS $referee) {
                $inroster[] = $referee->value;
            }
        }
        $projectreferees = sportsmanagementModelMatch::getProjectReferees($inroster, $this->project_id);

        if (count($projectreferees) > 0) {
            foreach ($projectreferees AS $referee) {
                $projectreferees2[] = JHtml::_('select.option', $referee->value, sportsmanagementHelper::formatName(null, $referee->firstname, $referee->nickname, $referee->lastname, $default_name_format) .
                                ' - (' . strtolower(JText::_($referee->positionname)) . ')');
            }
        }
        $lists['team_referees'] = JHtml::_('select.genericlist', $projectreferees2, 'roster[]', 'style="font-size:12px;height:auto;min-width:15em;" ' .
                        'class="inputbox" multiple="true" size="' . max(10, count($projectreferees2)) . '"', 'value', 'text');
        // projekt positionen                                                    
        $selectpositions[] = JHtml::_('select.option', '0', JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_REF_FUNCTION'));
        if ($projectpositions = sportsmanagementModelMatch::getProjectPositionsOptions(0, 3, $this->project_id)) {
            $selectpositions = array_merge($selectpositions, $projectpositions);
        }
        $lists['projectpositions'] = JHtml::_('select.genericlist', $selectpositions, 'project_position_id', 'class="inputbox" size="1"', 'value', 'text');

        $squad = array();
        if (!$projectpositions) {
            JError::raiseWarning(440, '<br />' . JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_NO_REF_POS') . '<br /><br />');
            return;
        }

        // generate selection list for each position
        foreach ($projectpositions AS $key => $pos) {
            // get referees assigned to this position
            $squad[$key] = sportsmanagementModelMatch::getRefereeRoster($pos->value, $this->match->id);
        }
        if (count($squad) > 0) {
            foreach ($squad AS $key => $referees) {
                $temp[$key] = array();
                if (isset($referees)) {
                    foreach ($referees AS $referee) {
                        $temp[$key][] = JHtml::_('select.option', $referee->value, sportsmanagementHelper::formatName(null, $referee->firstname, $referee->nickname, $referee->lastname, $default_name_format));
                    }
                }

                $lists['team_referees' . $key] = JHtml::_('select.genericlist', $temp[$key], 'position' . $key . '[]', ' style="font-size:12px;height:auto;min-width:15em;" ' .
                                'class="position-starters" multiple="true" ', 'value', 'text');

                /*
                  $lists['team_referees'.$key]=JHtml::_(	'select.genericlist','position','position'.$key.'[]',
                  ' style="font-size:12px;height:auto;min-width:15em;" '.
                  'class="inputbox position-starters" multiple="true" ',
                  'value','text');
                 */
            }
        }
        $this->positions = $projectpositions;
        $this->lists = $lists;
    }

}

?>
