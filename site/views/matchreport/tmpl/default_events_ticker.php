<?php 
/** SportsManagement ein Programm zur Verwaltung f?r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: ? 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license                This file is part of SportsManagement.
*
* SportsManagement is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* SportsManagement is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with SportsManagement.  If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von SportsManagement.
*
* SportsManagement ist Freie Software: Sie k?nnen es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder sp?teren
* ver?ffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es n?tzlich sein wird, aber
* OHNE JEDE GEW?HELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gew?hrleistung der MARKTF?HIGKEIT oder EIGNUNG F?R EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License f?r weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

defined( '_JEXEC' ) or die( 'Restricted access' ); 
?>
<!-- START of match events -->

<h2><?php echo JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_EVENTS'); ?></h2>		

<table class="table" border="0">

		<thead>
	        <tr class="sectiontableheader">
	            <th style="text-align:center"><?php echo JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_EVENT_TIME'); ?></th>
	            <th colspan=3><?php echo JText::_('COM_SPORTSMANAGEMENT_EDIT_EVENTS_EVENT'); ?></th>
	        </tr>
	    </thead>
			
        <?php
        $k = 0;
        
        $IconArr = array(0 => '');
        $TextArr = array(0 => '');
        foreach ( $this->eventtypes as $event )
        {
        $TextArr[$event->id] =  $event->name;
        $IconArr[$event->id] =  $event->icon;
        }
	
	//get sort order from fes
	global $sortEventsDesc;
	$sortEventsDesc = isset($this->config['sort_events_desc']) ? $this->config['sort_events_desc'] : '1';

	if ($this->config['show_substitutions']==1) {
	  $matchevents = array_merge($this->matchevents,$this->substitutes);
	  usort($matchevents, "compareMatchEventsSubs");
	} else {
	  $matchevents = $this->matchevents;
	}
        
        foreach ($matchevents AS $me)
            {
                if (!array_key_exists('in_out_time', $me)) 
                {
                ?>
                    
                    <tr class="" id="event-<?php echo $me->event_id; ?>">
                    
                    <?php
                    //Icon
                    $pic_tab=$IconArr[$me->event_type_id];
                    $eventname=JText::_($TextArr[$me->event_type_id]);
                    
                    //Time
                    $prefix = '';
                    if ($this->config['show_event_minute'] == 1 && $me->event_time > 0)
                    {
                        $prefix = str_pad($me->event_time, 2 ,'0', STR_PAD_LEFT);
                    }
                    echo '<td class="tcenter">' . $prefix . '</td>';
                    
                    
                    if ($me->event_type_id > 0) {
                        
                        if ($pic_tab == 'images/com_sportsmanagement/database/events/event.gif')
                        {
                            $txt_tab = $eventname;
                        }
                        else
                        {
                            $imgTitle = $eventname;
                            $imgTitle2 = array(' title' => $imgTitle, ' alt' => $imgTitle, ' style' => 'max-height:40px;');
                            $txt_tab = JHtml::image($pic_tab,$imgTitle,$imgTitle2);
                        }
                        echo  '<td class="ecenter">' . $txt_tab . '</td>';
                        
                        //Teamname
                        if ($me->ptid == $this->match->projectteam1_id) { 
                            if ($me->playerid != 0) {
                            $teamname = '';
                            } else {
                            $teamname = ' ' . $this->team1->middle_name;
                            }
                            $eventteam = $this->team1;
                        } else { 
                            if ($me->playerid != 0) {
                            $teamname = '';
                            } else {
                            $teamname = ' ' . $this->team2->middle_name;
                            }
                            $eventteam = $this->team2;
                        }
                        
                        //Club
                        echo '<td class="ecenter">' . sportsmanagementModelProject::getClubIconHtml($eventteam,1) . '</td>';
                        
                        //Event
                        // only show event sum and match notice when set to on in template cofig
                        $sum_notice = "";
                        if($this->config['show_event_sum'] == 1 || $this->config['show_event_notice'] == 1)
                        {
                            if (($this->config['show_event_sum'] == 1 && $me->event_sum > 0) || ($this->config['show_event_notice'] == 1 && strlen($me->notice) > 0))
                            {
                                $sum_notice .= ' (';
                                    if ($this->config['show_event_sum'] == 1 && $me->event_sum > 0)
                                    {
                                        $sum_notice .= $me->event_sum;
                                    }
                                    if (($this->config['show_event_sum'] == 1 && $me->event_sum > 0) && ($this->config['show_event_notice'] == 1 && strlen($me->notice) > 0))
                                    {
                                        $sum_notice .= ' | ';
                                    }
                                    if ($this->config['show_event_notice'] == 1 && strlen($me->notice) > 0)
                                    {
                                        $sum_notice .= $me->notice;
                                    }
                                $sum_notice .= ')';
                            }
                        }
                        
                        $match_player=sportsmanagementHelper::formatName('', $me->firstname1, $me->nickname1, $me->lastname1, $this->config["name_format"]);
                        
                        if ($this->config['event_link_player'] == 1 && $me->playerid != 0)
                        {
                            $player_link = sportsmanagementHelperRoute::getPlayerRoute($this->project->slug,$me->team_id,$me->playerid);
                            $match_player = JHtml::link($player_link,$match_player);
                        }                         
                        
                        echo '<td>' . $eventname . $sum_notice . ' - ' . $match_player . $teamname . '</td>';
                    }
                    else
                    {
                        //EventType 0, therefore text comment
                        $txt_tab = JHtml::image('media/com_sportsmanagement/jl_images/discuss.gif','','');
                        echo '<td colspan="" style="text-align:center">'.$txt_tab.'</td>';
                        echo '<td colspan= style="text-align:center">...</td>';
                        
                        if ($me->event_sum != '') {
                        $commentType = substr($me->event_sum, 0, 1);
                        }else{
                        $commentType = 0;
                        }
                        
                        switch ($commentType) {
                            case 2:
                                /** Before match, between periods or after match */
                                echo '<td class="tickerStyle2">' . $me->notes . '</td>';
                                break;
                            case 1:
                                /** Standard text comment */
                                echo '<td class="tickerStyle1">' . $me->notes . '</td>';
                                break;
		                }
                    }    
                echo '</tr>';
                //$k = 1 - $k;
                } 
                else
                {
		
//echo ' me<br><pre>'.print_r($me,true).'</pre>';        
        ?>
                    
                    <tr class="" id="event-tpidin<?php echo $me->teamplayer_id; ?>-tpidout<?php echo $me->in_for; ?>">
                    
                    <?php
                    //Icon
                    //$pic_tab=$IconArr[$me->event_type_id];
                    //$eventname=JText::_($TextArr[$me->event_type_id]);
		    
		    $pic_time = 'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/playtime.gif';
		    $pic_out = 'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/out.png';
		    $pic_in = 'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/in.png';
                    
                    //Time
                    $prefix = '';
                    if ($this->config['show_event_minute'] == 1 && $me->in_out_time > 0)
                    {
                        $prefix = str_pad($me->in_out_time, 2 ,'0', STR_PAD_LEFT);
                    }
                    echo '<td class="tcenter">' . $prefix . '</td>';
                    
                        
		    $imgTitle_in = JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_SUBSTITUTION_WENT_OUT');
		    $imgTitle_in2 = array(' title' => $imgTitle_in);
		    $imgTitle_out = JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_SUBSTITUTION_CAME_IN');
		    $imgTitle_out2 = array(' title' => $imgTitle_out);
		    $txt_tab_out = JHtml::image($pic_out,$imgTitle_in,$imgTitle_in2).'&nbsp;';
		    $txt_tab_in = JHtml::image($pic_in,$imgTitle_out,$imgTitle_out2).'&nbsp;';
                        
		    echo  '<td class="ecenter">' . $txt_tab_out . '</td>';
                        
                        //Teamname
                        if ($me->ptid == $this->match->projectteam1_id) { 
                            if ($me->teamplayer_id != 0) {
                            $teamname = '';
                            } else {
                            $teamname = ' ' . $this->team1->middle_name;
                            }
                            $eventteam = $this->team1;
                        } else { 
                            if ($me->playerid != 0) {
                            $teamname = '';
                            } else {
                            $teamname = ' ' . $this->team2->middle_name;
                            }
                            $eventteam = $this->team2;
                        }
                        
                        //Club
                        echo '<td class="ecenter">' . sportsmanagementModelProject::getClubIconHtml($eventteam,1) . '</td>';
                        
                        //Subs out
			$outName = sportsmanagementHelper::formatName(null, $me->out_firstname, $me->out_nickname, $me->out_lastname, $this->config["name_format"]);
			if($outName != '') {
			      $isFavTeam = in_array( $me->team_id, explode(",",$this->project->fav_team));

			  if ($this->config['event_link_player'] == 1) {
				if ( ($this->config['show_player_profile_link'] == 1) || (($this->config['show_player_profile_link'] == 2) && ($isFavTeam)) )
				{
				    $outName = JHtml::link(sportsmanagementHelperRoute::getPlayerRoute($this->project->slug,$me->ptid,$me->out_person_id),$outName);
				} else {
				    $outName = $outName;
				}

				if($me->out_position!='') {
					$outName .= '&nbsp;('.JText::_($me->out_position).')';
				}
			  }
			}
			
			echo '<td>' . $outName. '</td>';
			echo '</tr>';
			?>
			
			 <tr class="" id="event-tpidin<?php echo $me->teamplayer_id; ?>-tpidout<?php echo $me->in_for; ?>">
			 <?php
			echo '<td>&nbsp;</td>';
			echo '<td class="ecenter">' . $txt_tab_in . '</td>';
                        echo '<td class="ecenter">' . sportsmanagementModelProject::getClubIconHtml($eventteam,1) . '</td>';
                        
                        //Subs in
			$inName = sportsmanagementHelper::formatName(null, $me->firstname, $me->nickname, $me->lastname, $this->config["name_format"]);
			if($inName != '') {
			      $isFavTeam = in_array( $me->team_id, explode(",",$this->project->fav_team));

			  if ($this->config['event_link_player'] == 1) {
				if ( ($this->config['show_player_profile_link'] == 1) || (($this->config['show_player_profile_link'] == 2) && ($isFavTeam)) )
				{
				    $inName = JHtml::link(sportsmanagementHelperRoute::getPlayerRoute($this->project->slug,$me->ptid,$me->out_person_id),$inName);
				} else {
				    $inName = $inName;
				}

				if($me->out_position!='') {
					$inName .= '&nbsp;('.JText::_($me->in_position).')';
				}
			  }
			}
			
			echo '<td>' . $inName. '</td>';
			echo '<td>&nbsp;</td>';    
                echo '</tr>';
                }
            $k = 1 - $k;
            }
            	
function compareMatchEventsSubs($a, $b)
{
    global $sortEventsDesc;
    $a_value = null;
    $b_value = null;

    if (isset($a->event_time)) {
        $a_value = $a->event_time;
    } elseif (isset($a->in_out_time)) {
        $a_value = $a->in_out_time;
    }

    if (isset($b->event_time)) {
        $b_value = $b->event_time;
    } elseif (isset($b->in_out_time)) {
        $b_value = $b->in_out_time;
    }

    //if ($a_value === null || $b_value === null) {
    //    throw new Exception("No time attribute found or object is null.");
    //}

    if ($sortEventsDesc == 1) {
      if ($a_value < $b_value) {
	  return 1;
      } elseif ($a_value == $b_value) {
	  return 0;
      } else {
	  return -1;
      }
    } else {
      if ($a_value > $b_value) {
	  return 1;
      } elseif ($a_value == $b_value) {
	  return 0;
      } else {
	  return -1;
      }
    }

}
            ?>
			
</table>
<!-- END of match events -->
<br />