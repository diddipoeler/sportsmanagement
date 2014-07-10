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

defined( '_JEXEC' ) or die( 'Restricted access' ); ?>
<!-- START of match events -->

<h2>
<?php 
echo JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_EVENTS'); 

if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
{
$visible = 'text';    
echo '<br />eventtypes<pre>~' . print_r($this->eventtypes,true) . '~</pre><br />';
}
else
{
$visible = 'hidden';
}

?>
</h2>		

<table class="matchreport" border="0">
    <tr>
        <td>
            <?php 
            $result='';
		    $txt_tab='';
            // Make event tabs with JPane integrated function in Joomla 1.5 API
			$result = JPane::getInstance('tabs',array('startOffset'=>0));
			echo $result->startPane('pane');
			foreach ($this->eventtypes AS $event)
			{
				$pic_tab = $event->icon;
				if ($pic_tab == '/events/event.gif')
				{
					$txt_tab = JText::_($event->name);
				}
				else
				{
					$imgTitle=JText::_($event->name);
					$imgTitle2=array(' title' => $imgTitle, ' alt' => $imgTitle, ' style' => 'max-height:40px;');
					$txt_tab=JHtml::image(JUri::root().$pic_tab,$imgTitle,$imgTitle2);
				}

				echo $result->startPanel($txt_tab,$event->id);
				echo '<table class="matchreport">';
				echo '<tr>';
				echo '<td class="list">';
				echo '<ul>';
				foreach ($this->matchevents AS $me)
				{
					if ($me->event_type_id==$event->id && $me->ptid==$this->match->projectteam1_id)
					{
						echo '<li class="list">';
						
						if ($this->config['show_event_minute'] == 1 && $me->event_time > 0)
						{
						    $prefix = str_pad($me->event_time, 2 ,'0', STR_PAD_LEFT)."' ";
						} else {
						    $prefix = null;
						} 
						
						$match_player = sportsmanagementHelper::formatName($prefix, $me->firstname1, $me->nickname1, $me->lastname1, $this->config["name_format"]);
                        if ($this->config['event_link_player'] == 1 && $me->playerid != 0)
                        {
                            $player_link=sportsmanagementHelperRoute::getPlayerRoute($this->project->slug,$me->team_id,$me->playerid);
                            $match_player = JHtml::link($player_link,$match_player);
                        }
                        echo $match_player;

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
						echo $sum_notice;

						echo '</li>';
					}
				}
				echo '</ul>';
				echo '</td>';
				echo '<td class="list">';
				echo '<ul>';
				foreach ($this->matchevents AS $me)
				{
					if ($me->event_type_id==$event->id && $me->ptid==$this->match->projectteam2_id)
					{
						echo '<li class="list">';

						if ($this->config['show_event_minute'] == 1 && $me->event_time > 0)
						{
						    $prefix = str_pad($me->event_time, 2 ,'0', STR_PAD_LEFT)."' ";
						} else {
						    $prefix = null;
						}

						$match_player = sportsmanagementHelper::formatName($prefix, $me->firstname1, $me->nickname1, $me->lastname1, $this->config["name_format"]);
						if ($this->config['event_link_player'] == 1 && $me->playerid != 0)
						{
							$player_link=sportsmanagementHelperRoute::getPlayerRoute($this->project->slug,$me->team_id,$me->playerid);
							$match_player = JHtml::link($player_link,$match_player);
						}
						echo $match_player;

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
						echo $sum_notice;

						echo '</li>';
					}
				}
				echo '</ul>';
				echo '</td>';
				echo '</tr>';
				echo '</table>';
				echo $result->endPanel();
			}
			echo $result->endPane();
            ?>
        </td>
    </tr>
</table>
<!-- END of match events -->
<br />