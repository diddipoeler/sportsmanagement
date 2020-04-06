<?php 
/**
* 
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage teamplan
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;

/**
 * sportsmanagementViewTeamPlan
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewTeamPlan extends sportsmanagementView
{

    /**
     * sportsmanagementViewTeamPlan::init()
     * 
     * @return void
     */
    function init()
    {
            
        $this->document->addScript(Uri::root(true).'/components/'.$this->option.'/assets/js/smsportsmanagement.js');
        $this->document->addStyleSheet(Uri::base().'components/'.$this->option.'/assets/css/modalwithoutjs.css');
        
        sportsmanagementHelperHtml::$project = $this->project;
        
        if ($this->config['show_date_image'] ) {
            $this->document->addStyleSheet(Uri::base().'components/'.$this->option.'/assets/css/calendar.css');
        }    
        
        if (isset($this->project)) {
            $this->rounds = sportsmanagementModelProject::getRounds($this->config['plan_order'], sportsmanagementModelTeamPlan::$cfg_which_database);
            $this->teams = sportsmanagementModelProject::getTeamsIndexedByPtid(0, 'name', sportsmanagementModelTeamPlan::$cfg_which_database);
            $this->favteams = sportsmanagementModelProject::getFavTeams(sportsmanagementModelTeamPlan::$cfg_which_database);
            $this->division = sportsmanagementModelTeamPlan::getDivision();
            $this->ptid = sportsmanagementModelTeamPlan::getProjectTeamId();
            $this->projectevents = sportsmanagementModelProject::getProjectEvents(0, sportsmanagementModelTeamPlan::$cfg_which_database);
            $this->matches = sportsmanagementModelTeamPlan::getMatches($this->config);
            $this->matches_refering = sportsmanagementModelTeamPlan::getMatchesRefering($this->config);
            $this->matchesperround = sportsmanagementModelTeamPlan::getMatchesPerRound($this->config, $this->rounds);
        }
   
        // Set page title
        if (empty($this->ptid)) {
            $pageTitle = (!empty($this->project->id)) ? $this->project->name : '';
        }
        else
        {
            if (isset($this->project) && $this->ptid) {
                $pageTitle = $this->teams[$this->ptid]->name;
            }
            else{$pageTitle='';
            }
        }
        $this->document->setTitle(Text::sprintf('COM_SPORTSMANAGEMENT_TEAMPLAN_PAGE_TITLE', $pageTitle));
       
        if (!isset($this->config['table_class']) ) {
            $this->config['table_class'] = 'table';
        }

    }

    /**
     * returns html for events in tabs
     *
     * @param  object match
     * @param  array project events
     * @param  array match events
     * @param  aray match substitutions
     * @param  array                    $config
     * @return string
     */
    function showEventsContainerInResults($matchInfo,$projectevents,$matchevents,$substitutions=null,$config)
    {
        $output = '';
        $result = '';

        if ($this->config['use_tabs_events'] ) {
            $iPanel = 1; 
              $selector = 'teamplan'; 
              echo HTMLHelper::_('bootstrap.startTabSet', $selector, array('active'=>'panel'.$iPanel)); 

            // Size of the event icons in the tabs (when used)
            $width = 20; $height = 20; $type = 4;
             // Never show event text or icon for each event list item (info already available in tab)
            $showEventInfo = 0;

            $cnt = 0;
            foreach ($projectevents AS $event)
            {
                //display only tabs with events
                foreach ($matchevents AS $me)
                {
                    $cnt=0;
                    if ($me->event_type_id == $event->id) {
                        $cnt++;
                        break;
                    }
                }
                if($cnt==0) {continue;
                }
                
                if ($this->config['show_events_with_icons'] == 1) {
                    // Event icon as thumbnail on the tab (a placeholder icon is used when the icon does not exist)
                    $imgTitle = Text::_($event->name);
                    $tab_content = sportsmanagementHelper::getPictureThumb($event->icon, $imgTitle, $width, $height, $type);
                }
                else
                {
                    $tab_content = Text::_($event->name);
                }

                $output .= HTMLHelper::_('bootstrap.addTab', $selector, 'panel'.$iPanel++, $tab_content);
                $output .= '<table class="matchreport" border="0">';
                $output .= '<tr>';

                // Home team events
                $output .= '<td class="list">';
                $output .= '<ul class="list-inline">';
                foreach ($matchevents AS $me)
                {
                    $output .= self::_formatEventContainerInResults($me, $event, $matchInfo->projectteam1_id, $showEventInfo);
                }
                $output .= '</ul>';
                $output .= '</td>';

                // Away team events
                $output .= '<td class="list">';
                $output .= '<ul class="list-inline">';
                foreach ($matchevents AS $me)
                {
                    $output .= self::_formatEventContainerInResults($me, $event, $matchInfo->projectteam2_id, $showEventInfo);
                }
                $output .= '</ul>';
                $output .= '</td>';
                $output .= '</tr>';
                $output .= '</table>';
                $output .= HTMLHelper::_('bootstrap.endTab');
            }

            if (!empty($substitutions) ) {
                if ($this->config['show_events_with_icons'] ) {
                    // Event icon as thumbnail on the tab (a placeholder icon is used when the icon does not exist)
                    $imgTitle = Text::_('COM_SPORTSMANAGEMENT_IN_OUT');
                    $pic_tab    = 'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/subst.png';
                    $tab_content = sportsmanagementHelper::getPictureThumb($pic_tab, $imgTitle, $width, $height, $type);
                }
                else
                {
                    $tab_content = Text::_('COM_SPORTSMANAGEMENT_IN_OUT');
                }

                $pic_time    = Uri::root().'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/playtime.gif';
                $pic_out    = Uri::root().'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/out.png';
                $pic_in        = Uri::root().'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/in.png';
                $imgTime = HTMLHelper::image($pic_time, Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_SUBSTITUTION_MINUTE'), array(' title' => Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_SUBSTITUTION_MINUTE')));
                $imgOut  = HTMLHelper::image($pic_out, Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_SUBSTITUTION_WENT_OUT'), array(' title' => Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_SUBSTITUTION_WENT_OUT')));
                $imgIn   = HTMLHelper::image($pic_in, Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_SUBSTITUTION_CAME_IN'), array(' title' => Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_SUBSTITUTION_CAME_IN')));

                $output .= HTMLHelper::_('bootstrap.addTab', $selector, 'panel'.$iPanel++, $tab_content);
                $output .= '<table class="matchreport" border="0">';
                $output .= '<tr>';
                $output .= '<td class="list">';
                $output .= '<ul class="list-inline">';
                foreach ($substitutions AS $subs)
                {
                    $output .= self::_formatSubstitutionContainerInResults($subs, $matchInfo->projectteam1_id, $imgTime, $imgOut, $imgIn);
                }
                $output .= '</ul>';
                $output .= '</td>';
                $output .= '<td class="list">';
                $output .= '<ul class="list-inline">';
                foreach ($substitutions AS $subs)
                {
                    $output .= self::_formatSubstitutionContainerInResults($subs, $matchInfo->projectteam2_id, $imgTime, $imgOut, $imgIn);
                }
                $output .= '</ul>';
                $output .= '</td>';
                $output .= '</tr>';
                $output .= '</table>';
                $output .= HTMLHelper::_('bootstrap.endTab');
            }
            echo HTMLHelper::_('bootstrap.endTabSet');
        }
        else
        {
            $showEventInfo = ($this->config['show_events_with_icons'] == 1) ? 1 : 2;
            $output .= '<table class="matchreport" border="0">';
            $output .= '<tr>';

            // Home team events
            $output .= '<td class="list-left">';
            $output .= '<ul class="list-inline">';
            foreach ((array) $matchevents AS $me)
            {
                if ($me->ptid == $matchInfo->projectteam1_id) {
                    $output .= self::_formatEventContainerInResults($me, $projectevents[$me->event_type_id], $matchInfo->projectteam1_id, $showEventInfo);
                }
            }
            $output .= '</ul>';
            $output .= '</td>';

            // Away team events
            $output .= '<td class="list-right">';
            $output .= '<ul class="list-inline">';
            foreach ($matchevents AS $me)
            {
                if ($me->ptid == $matchInfo->projectteam2_id) {
                    $output .= self::_formatEventContainerInResults($me, $projectevents[$me->event_type_id], $matchInfo->projectteam2_id, $showEventInfo);
                }
            }
            $output .= '</ul>';
            $output .= '</td>';
            $output .= '</tr>';
            $output .= '</table>';
        }

        return $output;
    }
    
    
    /**
     * sportsmanagementViewTeamPlan::_formatEventContainerInResults()
     * 
     * @param  mixed $matchevent
     * @param  mixed $event
     * @param  mixed $projectteamId
     * @param  mixed $showEventInfo
     * @return
     */
    function _formatEventContainerInResults($matchevent, $event, $projectteamId, $showEventInfo)
    {
        // Meaning of $showEventInfo:
        // 0 : do not show event as text or as icon in a list item
        // 1 : show event as icon in a list item (before the time)
        // 2 : show event as text in a list item (after the time)
        $output='';
        if ($matchevent->event_type_id == $event->id && $matchevent->ptid == $projectteamId) {
            $output .= '<li class="list-inline-item">';
            if ($showEventInfo == 1) {
                // Size of the event icons in the tabs
                $width = 20; $height = 20; $type = 4;
                $imgTitle = Text::_($event->name);
                $icon = sportsmanagementHelper::getPictureThumb($event->icon, $imgTitle, $width, $height, $type);

                $output .= $icon;
            }

            $event_minute = str_pad($matchevent->event_time, 2, '0', STR_PAD_LEFT);
            if ($this->config['show_event_minute'] && $matchevent->event_time > 0) {
                $output .= '<b>'.$event_minute.'\'</b> ';
            } 

            if ($showEventInfo == 2) {
                $output .= Text::_($event->name).' ';
            }

            if (strlen($matchevent->firstname1.$matchevent->lastname1) > 0) {
                $output .= sportsmanagementHelper::formatName(null, $matchevent->firstname1, $matchevent->nickname1, $matchevent->lastname1, $this->config["name_format"]);
            }
            else
            {
                $output .= Text :: _('COM_SPORTSMANAGEMENT_UNKNOWN_PERSON');
            }
            
            // only show event sum and match notice when set to on in template cofig
            if($this->config['show_event_sum'] || $this->config['show_event_notice'] == 1) {
                if (( $this->config['show_event_sum'] && $matchevent->event_sum > 0) || ( $this->config['show_event_notice'] && strlen($matchevent->notice) > 0)) {
                    $output .= ' (';
                    if ($this->config['show_event_sum'] && $matchevent->event_sum > 0) {
                        $output .= $matchevent->event_sum;
                    }
                    if (( $this->config['show_event_sum'] && $matchevent->event_sum > 0) && ( $this->config['show_event_notice'] && strlen($matchevent->notice) > 0)) {
                        $output .= ' | ';
                    }
                    if ($this->config['show_event_notice'] && strlen($matchevent->notice) > 0) {
                        $output .= $matchevent->notice;
                    }
                    $output .= ')';
                }
            }
            
            $output .= '</li>';
        }
        return $output;
    }

    /**
     * sportsmanagementViewTeamPlan::_formatSubstitutionContainerInResults()
     * 
     * @param  mixed $subs
     * @param  mixed $projectteamId
     * @param  mixed $imgTime
     * @param  mixed $imgOut
     * @param  mixed $imgIn
     * @return
     */
    function _formatSubstitutionContainerInResults($subs,$projectteamId,$imgTime,$imgOut,$imgIn)
    {
        $output='';
        if ($subs->ptid == $projectteamId) {
            $output .= '<li class="list-inline-item">';
            // $output .= $imgTime;
            $output .= '&nbsp;'.$subs->in_out_time.'. '.Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_SUBSTITUTION_MINUTE');
            $output .= '<br />';

            $output .= $imgOut;
            $output .= '&nbsp;'.sportsmanagementHelper::formatName(null, $subs->out_firstname, $subs->out_nickname, $subs->out_lastname, $this->config["name_format"]);
            $output .= '&nbsp;('.Text :: _($subs->out_position).')';
            $output .= '<br />';

            $output .= $imgIn;
            $output .= '&nbsp;'.sportsmanagementHelper::formatName(null, $subs->firstname, $subs->nickname, $subs->lastname, $this->config["name_format"]);
            $output .= '&nbsp;('.Text :: _($subs->in_position).')';
            $output .= '<br /><br />';
            $output .= '</li>';
        }
        return $output;
    }
}
?>
