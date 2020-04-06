<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage editmatch
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\View\HtmlView;

// welche joomla version ?
if (version_compare(JVERSION, '3.0.0', 'ge')) {
    jimport('joomla.html.html.bootstrap');
}

/**
 * sportsmanagementViewEditMatch
 *
 * @package
 * @author    Dieter Plöger
 * @copyright 2018
 * @version   $Id$
 * @access    public
 */
class sportsmanagementViewEditMatch extends sportsmanagementView
{


    /**
     * sportsmanagementViewEditMatch::init()
     *
     * @return void
     */
    function init()
    {

        $option = Factory::getApplication()->input->getCmd('option');
        $app = Factory::getApplication();
        $document = Factory::getDocument();
        $db = Factory::getDBO();
        if (version_compare(JSM_JVERSION, '4', 'eq')) {
            $this->uri = Uri::getInstance();
        } else {
            $this->uri = Factory::getURI();
        }
        $user = Factory::getUser();
        // JInput object
        $jinput = $app->input;
        $model = $this->getModel();

        $this->project_id = $jinput->getInt('p', 0);
        sportsmanagementModelProject::setProjectID($this->project_id);
        $projectws = sportsmanagementModelProject::getProject($jinput->getInt('cfg_which_database', 0));
        //$projectws = sportsmanagementModelProject::getProject($this->project_id);

        $app->setUserState("$option.pid", $projectws->id);
        $app->setUserState("$option.season_id", $projectws->season_id);

        $this->projectws = $projectws;
        $this->eventsprojecttime = $projectws->game_regular_time;
        $this->useeventtime = $projectws->useeventtime;

        // Get some data from the models
        $match = $this->get('Data');
        $extended = sportsmanagementHelper::getExtended($match->extended, 'match');
        $this->extended = $extended;
        $this->match = $match;
        $this->form = $this->get('Form');

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

        //parent::display($tpl);
    }

    /**
     * sportsmanagementViewEditMatch::initEditLineup()
     *
     * @return
     */
    function initEditLineup()
    {
        $app = Factory::getApplication();
        $option = Factory::getApplication()->input->getCmd('option');
        $document = Factory::getDocument();
        $default_name_format = '';

        $tid = Factory::getApplication()->input->getVar('team', '0');
        $this->tid = $tid;
        $match = sportsmanagementModelMatch::getMatchTeams($this->match->id);
        $teamname = ($tid == $match->projectteam1_id) ? $match->team1 : $match->team2;
        $this->teamname = $teamname;

        // get starters
        $starters = sportsmanagementModelMatch::getMatchPersons($tid, 0, $this->match->id, 'player');
        $starters_id = array_keys($starters);

        // get players not already assigned to starter
        $not_assigned = sportsmanagementModelMatch::getTeamPersons($tid, $starters_id, 1);

        $playersoptionsout = array();
        $playersoptionsout[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_PLAYER_OUT'));
        $playersoptionsin = array();
        $playersoptionsin[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_PLAYER_IN'));

        if (!$not_assigned && !$starters_id) {
            $this->playersoptionsout = $playersoptionsout;
            $this->playersoptionsin = $playersoptionsin;
            Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_NO_PLAYERS_MATCH'), Log::WARNING, 'jsmerror');
            return;
        }

        $projectpositions = sportsmanagementModelMatch::getProjectPositionsOptions(0, 1, $this->project_id);

        if (!$projectpositions) {
            $this->playersoptionsout = $playersoptionsout;
            $this->playersoptionsin = $playersoptionsin;
            Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_NO_POS'), Log::WARNING, 'jsmerror');
            return;
        }

        // build select list for not assigned players
        $not_assigned_options = array();
        foreach ((array) $not_assigned AS $p) {
            $not_assigned_options[] = HTMLHelper::_(
                'select.option', $p->value, '[' . $p->jerseynumber . '] ' .
                            sportsmanagementHelper::formatName(null, $p->firstname, $p->nickname, $p->lastname, $default_name_format) .
                ' - (' . Text::_($p->positionname) . ')'
            );
        }
        $lists['team_players'] = HTMLHelper::_('select.genericlist', $not_assigned_options, 'roster[]', 'style="font-size:12px;height:auto;min-width:15em;" class="inputbox" multiple="true" size="18"', 'value', 'text');

        // build position select
        $selectpositions[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_IN_POSITION'));
        $selectpositions = array_merge($selectpositions, sportsmanagementModelMatch::getProjectPositionsOptions(0, 1, $this->project_id));
        $lists['projectpositions'] = HTMLHelper::_('select.genericlist', $selectpositions, 'project_position_id', 'class="inputbox" size="1"', 'posid', 'text', null, false, true);

        // build player select
        $allplayers = sportsmanagementModelMatch::getTeamPersons($tid, false, 1);

        foreach ((array) $starters AS $player) {
            $playersoptionsout[] = HTMLHelper::_('select.option', $player->value, sportsmanagementHelper::formatName(null, $player->firstname, $player->nickname, $player->lastname, $default_name_format) . ' - (' . Text::_($player->positionname) . ')');
        }
        $this->playersoptionsout = $playersoptionsout;

        foreach ((array) $not_assigned AS $player) {
            $playersoptionsin[] = HTMLHelper::_('select.option', $player->value, sportsmanagementHelper::formatName(null, $player->firstname, $player->nickname, $player->lastname, $default_name_format) . ' - (' . Text::_($player->positionname) . ')');
        }
        $this->playersoptionsin = $playersoptionsin;

        // generate selection list for each position
        $starters = array();
        foreach ($projectpositions AS $position_id => $pos) {
            // get players assigned to this position
            $starters[$position_id] = sportsmanagementModelMatch::getRoster($tid, $pos->value, $this->match->id, $pos->text);
        }

        foreach ($starters AS $position_id => $players) {
            $options = array();
            foreach ((array) $players AS $p) {
                $options[] = HTMLHelper::_(
                    'select.option', $p->value, '[' . $p->jerseynumber . '] ' .
                    sportsmanagementHelper::formatName(null, $p->firstname, $p->nickname, $p->lastname, $default_name_format)
                );
            }

            $lists['team_players' . $position_id] = HTMLHelper::_('select.genericlist', $options, 'position' . $position_id . '[]', 'style="font-size:12px;height:auto;min-width:15em;" size="4" class="position-starters" multiple="true" ', 'value', 'text');
        }

        $substitutions = sportsmanagementModelMatch::getSubstitutions($tid, $this->match->id);

        /**
         * staff positions
         */
        $staffpositions = sportsmanagementModelMatch::getProjectPositionsOptions(0, 2, $this->project_id); // get staff not already assigned to starter
        // assigned staff
        $assigned = sportsmanagementModelMatch::getMatchPersons($tid, 0, $this->match->id, 'staff');
        $assigned_id = array_keys($assigned);

        // not assigned staff
        $not_assigned = sportsmanagementModelMatch::getTeamPersons($tid, $assigned_id, 2);

        // build select list for not assigned
        $not_assigned_options = array();
        foreach ((array) $not_assigned AS $p) {
            $not_assigned_options[] = HTMLHelper::_('select.option', $p->value, sportsmanagementHelper::formatName(null, $p->firstname, $p->nickname, $p->lastname, $default_name_format) . ' - (' . Text::_($p->positionname) . ')');
        }
        $lists['team_staffs'] = HTMLHelper::_('select.genericlist', $not_assigned_options, 'staff[]', 'style="font-size:12px;height:auto;min-width:15em;" size="18" class="inputbox" multiple="true" size="18"', 'value', 'text');

        // generate selection list for each position
        $options = array();
        foreach ($staffpositions AS $position_id => $pos) {
            // get players assigned to this position
            $options = array();
            foreach ($assigned as $staff) {
                if ($staff->position_id == $pos->pposid) {
                    $options[] = HTMLHelper::_('select.option', $staff->team_staff_id, sportsmanagementHelper::formatName(null, $staff->firstname, $staff->nickname, $staff->lastname, $default_name_format));
                }
            }
            $lists['team_staffs' . $position_id] = HTMLHelper::_('select.genericlist', $options, 'staffposition' . $position_id . '[]', 'style="font-size:12px;height:auto;min-width:15em;" size="4" class="position-staff" multiple="true" ', 'value', 'text');
        }

        // build the html select booleanlist
        $myoptions = array();
        $myoptions[] = HTMLHelper::_('select.option', '0', Text::_('JNO'));
        $myoptions[] = HTMLHelper::_('select.option', '1', Text::_('JYES'));
        $lists['captain'] = $myoptions;

        $document->addScript(Uri::base().'administrator/components/'.$option.'/assets/js/diddioeler.js');
        $javascript = "\n";
        $javascript .= "var baseajaxurl = '".Uri::root()."index.php?option=com_sportsmanagement';". "\n";     
        $javascript .= "var matchid = ".$this->match->id.";" . "\n";
        $javascript .= "var teamid = ".$this->tid.";" . "\n";
        $javascript .= "var projecttime = ".$this->eventsprojecttime.";" . "\n";
        $javascript .= "var useeventtime = " . $this->useeventtime . ";" . "\n";
        $javascript .= "var str_delete = '".Text::_('JACTION_DELETE')."';" . "\n";
        $javascript .= 'jQuery(document).ready(function() {' . "\n";
        $javascript .= "updatePlayerSelect();". "\n";
        $javascript .= "jQuery('#team_id').change(updatePlayerSelect);". "\n";
        $javascript .= '  });' . "\n";
        $javascript .= "\n";  
        $document->addScriptDeclaration($javascript);
      
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
    function initEditEevents()
    {
        $app = Factory::getApplication();
        $option = Factory::getApplication()->input->getCmd('option');
        $document = Factory::getDocument();
        $params = ComponentHelper::getParams($option);
        $default_name_dropdown_list_order = $params->get("cfg_be_name_dropdown_list_order", "lastname");
        $default_name_format = $params->get("name_format", 14);
  
        // mannschaften der paarung
        $teams = sportsmanagementModelMatch::getMatchTeams($this->match->id);
        $this->teams = $teams;

        $teamlist = array();
        $teamlist[] = HTMLHelper::_('select.option', $teams->projectteam1_id, $teams->team1);
        $teamlist[] = HTMLHelper::_('select.option', $teams->projectteam2_id, $teams->team2);
        $lists['teams'] = HTMLHelper::_('select.genericlist', $teamlist, 'team_id', 'onchange="updatePlayerSelect();" class="inputbox select-team" ');
        // events
        $events = sportsmanagementModelMatch::getEventsOptions($this->project_id, 0);
        if (!$events) {
            Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_NO_EVENTS_POS'), Log::WARNING, 'jsmerror');
            return;
        }
        $eventlist = array();
        $eventlist = array_merge($eventlist, $events);

        $lists['events'] = HTMLHelper::_('select.genericlist', $eventlist, 'event_type_id', 'class="inputbox select-event"');

        $homeRoster = sportsmanagementModelMatch::getMatchPersons($teams->projectteam1_id, 0, $this->match->id, 'player');
        if (count($homeRoster) == 0) {

        }

        $awayRoster = sportsmanagementModelMatch::getMatchPersons($teams->projectteam2_id, 0, $this->match->id, 'player');
        if (count($awayRoster) == 0) {

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
                $obj->text  = '('.Text::_($player->positionname).') - '.sportsmanagementHelper::formatName(null, $player->firstname, $player->nickname, $player->lastname, $this->default_name_format);
                break;
}
                    $temp[] = $obj;
        }
        $lists['homeroster'] = HTMLHelper::_('select.genericlist', $temp, $teams->projectteam1_id, 'style="" size="" class="inputbox" size="1"', 'value', 'text');
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
                $obj->text  = '('.Text::_($player->positionname).') - '.sportsmanagementHelper::formatName(null, $player->firstname, $player->nickname, $player->lastname, $this->default_name_format);
                break;
}
                    $temp[] = $obj;
        }
        $lists['awayroster'] = HTMLHelper::_('select.genericlist', $temp, $teams->projectteam2_id, 'style="" size="1" class="inputbox" size="1"', 'value', 'text');
     
        $matchCommentary = sportsmanagementModelMatch::getMatchCommentary($this->match->id);
        $matchevents = sportsmanagementModelMatch::getMatchEvents($this->match->id);
        //$document->addScriptDeclaration( $javascript );

        $document->addScript(Uri::base().'administrator/components/'.$option.'/assets/js/diddioeler.js');      

        $javascript = "\n";
        $javascript .= "var baseajaxurl = '".Uri::root()."index.php?option=com_sportsmanagement';". "\n";      
        $javascript .= "var matchid = ".$this->match->id.";" . "\n";
        $javascript .= "var useeventtime = " . $this->useeventtime . ";" . "\n";      
        $javascript .= "var projecttime = ".$this->eventsprojecttime.";" . "\n";
        $javascript .= "var str_delete = '".Text::_('JACTION_DELETE')."';" . "\n";
        $javascript .= "\n";
  
        $document->addScriptDeclaration($javascript);      
      
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
    function initEditStats()
    {
        $teams = sportsmanagementModelMatch::getMatchTeams($this->match->id);

        $positions = sportsmanagementModelMatch::getProjectPositionsOptions(0, 1, $this->project_id);
        $staffpositions = sportsmanagementModelMatch::getProjectPositionsOptions(0, 2, $this->project_id);

        $homeRoster = sportsmanagementModelMatch::getMatchPersons($teams->projectteam1_id, 0, $this->match->id, 'player');
        if (count($homeRoster) == 0) {

        }

        $awayRoster = sportsmanagementModelMatch::getMatchPersons($teams->projectteam2_id, 0, $this->match->id, 'player');
        if (count($awayRoster) == 0) {

        }

        $homeStaff = sportsmanagementModelMatch::getMatchPersons($teams->projectteam1_id, 0, $this->match->id, 'staff');
        $awayStaff = sportsmanagementModelMatch::getMatchPersons($teams->projectteam2_id, 0, $this->match->id, 'staff');

        // stats
        $stats = sportsmanagementModelMatch::getInputStats($this->project_id);
        if (!$stats) {
            Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_NO_STATS_POS'), Log::WARNING, 'jsmerror');
        }
        $playerstats = sportsmanagementModelMatch::getMatchStatsInput($this->match->id, $teams->projectteam1_id, $teams->projectteam2_id);
        $staffstats = sportsmanagementModelMatch::getMatchStaffStatsInput($this->match->id, $teams->projectteam1_id, $teams->projectteam2_id);

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
    function initEditMatch()
    {
        $app = Factory::getApplication();

        $oldmatches [] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_OLD_MATCH'));
        $res = array();
        $new_match_id = ($this->match->new_match_id) ? $this->match->new_match_id : 0;
        if ($res = sportsmanagementModelMatch::getMatchRelationsOptions($this->project_id, $this->match->id . "," . $new_match_id)) {
            foreach ($res as $m) {
                if (is_object($m->match_date) ) {
                          $m->text = '(' . sportsmanagementHelper::getMatchStartTimestamp($m) . ') - ' . $m->t1_name . ' - ' . $m->t2_name;
                }
                else
                {
                    $m->text = '(' . ') - ' . $m->t1_name . ' - ' . $m->t2_name;              
                }
            }
            $oldmatches = array_merge($oldmatches, $res);
        }
        $lists ['old_match'] = HTMLHelper::_('select.genericlist', $oldmatches, 'old_match_id', 'class="inputbox" size="1"', 'value', 'text', $this->match->old_match_id);

        $newmatches [] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_NEW_MATCH'));
        $res = array();
        $old_match_id = ($this->match->old_match_id) ? $this->match->old_match_id : 0;
        if ($res = sportsmanagementModelMatch::getMatchRelationsOptions($this->project_id, $this->match->id . "," . $old_match_id)) {
            foreach ($res as $m) {
                if (is_object($m->match_date) ) {
                          $m->text = '(' . sportsmanagementHelper::getMatchStartTimestamp($m) . ') - ' . $m->t1_name . ' - ' . $m->t2_name;
                }
                else
                {
                    $m->text = '(' . ') - ' . $m->t1_name . ' - ' . $m->t2_name;              
                }
            }
            $newmatches = array_merge($newmatches, $res);
        }
        $lists ['new_match'] = HTMLHelper::_('select.genericlist', $newmatches, 'new_match_id', 'class="inputbox" size="1"', 'value', 'text', $this->match->new_match_id);

        $lists['count_result'] = HTMLHelper::_('select.booleanlist', 'count_result', 'class="btn btn-primary"', $this->match->count_result);
        // build the html select booleanlist which team got the won
        $myoptions = array();
        $myoptions[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_NO_TEAM'));
        $myoptions[] = HTMLHelper::_('select.option', '1', Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_HOME_TEAM'));
        $myoptions[] = HTMLHelper::_('select.option', '2', Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_AWAY_TEAM'));
        $myoptions[] = HTMLHelper::_('select.option', '3', Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_LOSS_BOTH_TEAMS'));
        $myoptions[] = HTMLHelper::_('select.option', '4', Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_WON_BOTH_TEAMS'));
        $lists['team_won'] = HTMLHelper::_('select.genericlist', $myoptions, 'team_won', 'class="inputbox" size="1"', 'value', 'text', $this->match->team_won);

        $this->lists = $lists;
    }

    /**
     * sportsmanagementViewEditMatch::initEditReferees()
     *
     * @return
     */
    function initEditReferees()
    {
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
                $projectreferees2[] = HTMLHelper::_(
                    'select.option', $referee->value, sportsmanagementHelper::formatName(null, $referee->firstname, $referee->nickname, $referee->lastname, $default_name_format) .
                    ' - (' . strtolower(Text::_($referee->positionname)) . ')'
                );
            }
        }
        $lists['team_referees'] = HTMLHelper::_(
            'select.genericlist', $projectreferees2, 'roster[]', 'style="font-size:12px;height:auto;min-width:15em;" ' .
            'class="inputbox" multiple="true" size="' . max(10, count($projectreferees2)) . '"', 'value', 'text'
        );
        // projekt positionen                                                  
        $selectpositions[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_REF_FUNCTION'));
        if ($projectpositions = sportsmanagementModelMatch::getProjectPositionsOptions(0, 3, $this->project_id)) {
            $selectpositions = array_merge($selectpositions, $projectpositions);
        }
        $lists['projectpositions'] = HTMLHelper::_('select.genericlist', $selectpositions, 'project_position_id', 'class="inputbox" size="1"', 'value', 'text');

        $squad = array();
        if (!$projectpositions) {
            Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_NO_REF_POS'), Log::WARNING, 'jsmerror');
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
                        $temp[$key][] = HTMLHelper::_('select.option', $referee->value, sportsmanagementHelper::formatName(null, $referee->firstname, $referee->nickname, $referee->lastname, $default_name_format));
                    }
                }

                $lists['team_referees' . $key] = HTMLHelper::_(
                    'select.genericlist', $temp[$key], 'position' . $key . '[]', ' style="font-size:12px;height:auto;min-width:15em;" ' .
                    'class="position-starters" multiple="true" ', 'value', 'text'
                );

                /*
                  $lists['team_referees'.$key]=HTMLHelper::_(	'select.genericlist','position','position'.$key.'[]',
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
