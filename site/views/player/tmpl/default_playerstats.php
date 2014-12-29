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

defined('_JEXEC') or die('Restricted access'); 

if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
{
echo 'PERSON_PERSONAL_STATISTICS stats<br /><pre>~' . print_r($this->stats,true) . '~</pre><br />';
}

?>

<!-- Player stats History START -->
<h2><?php	echo JText::_('COM_SPORTSMANAGEMENT_PERSON_PERSONAL_STATISTICS');	?></h2>
<table align="center" border="0" cellpadding="0" cellspacing="0" class="table">
	<tr>
		<td>
		<table id="playercareer">
			<thead>
			<tr class="sectiontableheader">
				<th class="td_l" class="nowrap"><?php echo JText::_('COM_SPORTSMANAGEMENT_PERSON_COMPETITION'); ?></th>
				<th class="td_l" class="nowrap"><?php echo JText::_('COM_SPORTSMANAGEMENT_PERSON_TEAM'); ?></th>
				<th class="td_c"><?php
				$imageTitle=JText::_('COM_SPORTSMANAGEMENT_PERSON_PLAYED');
				echo JHtml::image(	'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/played.png',
				$imageTitle,array(' title' => $imageTitle,' width' => 20,' height' => 20));
				?></th>
				<?php
				if ($this->config['show_substitution_stats'])
				{
					if ((isset($this->overallconfig['use_jl_substitution'])) && ($this->overallconfig['use_jl_substitution']==1))
					{
						?>
				<th class="td_c"><?php
				$imageTitle=JText::_('COM_SPORTSMANAGEMENT_PERSON_STARTROSTER');
				echo JHtml::image(	'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/startroster.png',
				$imageTitle,array(' title' => $imageTitle));
				?></th>
				<th class="td_c"><?php
				$imageTitle=JText::_('COM_SPORTSMANAGEMENT_PERSON_IN');
				echo JHtml::image(	'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/in.png',
				$imageTitle,array(' title' => $imageTitle));
				?></th>
				<th class="td_c"><?php
				$imageTitle=JText::_('COM_SPORTSMANAGEMENT_PERSON_OUT');
				echo JHtml::image(	'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/out.png',
				$imageTitle,array(' title' => $imageTitle));
				?></th>
                
                <?PHP
                // gespielte zeit
                ?>
                <th class="td_c"><?php
				$imageTitle=JText::_('COM_SPORTSMANAGEMENT_PLAYED_TIME');
				echo JHtml::image(	'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/uhr.png',
				$imageTitle,array('title'=> $imageTitle,'height'=> 11));
		?></th>
        
				<?php
					}
				}
				if ($this->config['show_career_events_stats'])
				{
					if (count($this->AllEvents))
					{
						foreach($this->AllEvents as $eventtype)
						{
				?>
				<th class="td_c"><?php
				$iconPath=$eventtype->icon;
				if (!strpos(" ".$iconPath,"/")){$iconPath="images/com_sportsmanagement/database/events/".$iconPath;}
				echo JHtml::image($iconPath,
					JText::_($eventtype->name),
					array(	"title" => JText::_($eventtype->name),
						"align" => "top",
						"hspace" => "2"));
				?>&nbsp;</th>
				<?php
						}
					}
				}
				if ($this->config['show_career_stats'])
				{
					foreach ($this->stats as $stat)
					{
						//do not show statheader when there are no stats
						if (!empty($stat)) {
							if ($stat->showInPlayer()) {
						
				?>
				<th class="td_c"><?php echo !empty($stat) ? $stat->getImage() : ""; ?>&nbsp;</th>
				<?php 			}
						}
					}
				}
				?>
			</tr>
			</thead>
			<tbody>
			<?php
			$k=0;
			$career = array();
			$career['played'] = 0;
			$career['started'] = 0;
			$career['in'] = 0;
			$career['out'] = 0;
            $career['playedtime'] = 0;
			$player = JModelLegacy::getInstance("Person","sportsmanagementModel");
            
            if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
            {
            echo ' games<br><pre>'.print_r($this->historyPlayer,true).'</pre><br>';
            }

			if (count($this->historyPlayer) > 0)
			{
				foreach ($this->historyPlayer as $player_hist)
				{
					$model = $this->getModel();
					$this->assign('inoutstat',$model->getInOutStats($player_hist->project_id, $player_hist->ptid, $player_hist->tpid));
                    
                    //$this->assign('inoutstat',sportsmanagementModelRoster::_getTeamInOutStats($player_hist->project_id, $player_hist->ptid, $player_hist->tpid));
					
                    //$this->assign('inoutstat',$player->getInOutStats($player_hist->project_id, $player_hist->ptid, $player_hist->tpid));
                    
                    // gespielte zeit
                    if ( !isset($this->overallconfig['person_events']) )
                    {
                        $this->overallconfig['person_events'] = NULL;
                    }
                    $timePlayed = 0;
                    $this->assign('timePlayed',$model->getTimePlayed($player_hist->tpid,$this->project->game_regular_time,NULL,$this->overallconfig['person_events']));
                    $timePlayed  = $this->timePlayed;
            
                    $link1=sportsmanagementHelperRoute::getPlayerRoute($player_hist->project_slug,$player_hist->team_slug,$this->person->slug);
					$link2=sportsmanagementHelperRoute::getTeamInfoRoute($player_hist->project_slug,$player_hist->team_slug);
					?>
			<tr class="<?php echo ($k==0)? $this->config['style_class1'] : $this->config['style_class2']; ?>">
				<td class="td_l" nowrap="nowrap"><?php echo JHtml::link($link1,$player_hist->project_name); ?>
				</td>
				<td class="td_l" class="nowrap">
				<?php
					if ($this->config['show_playerstats_teamlink'] == 1) {
						echo JHtml::link($link2,$player_hist->team_name);
					} else {
						echo $player_hist->team_name;
					} 
				?>
				</td>
				<!-- Player stats History - played start -->
				<td class="td_c"><?php
				//echo ($this->inoutstat->played > 0) ? $this->inoutstat->played : '0';
                echo ($this->inoutstat->played > 0) ? $this->inoutstat->played : $this->overallconfig['zero_events_value'];
				$career['played'] += $this->inoutstat->played;
				?></td>
				<?php
				if ($this->config['show_substitution_stats'])
				{
					//substitution system
					if ((isset($this->overallconfig['use_jl_substitution']) && ($this->overallconfig['use_jl_substitution']==1)))
					{
						?>
						<!-- Player stats History - startroster start -->
						<td class="td_c"><?php
						$career['started'] += $this->inoutstat->started;
						//echo ($this->inoutstat->started);
                        echo ($this->inoutstat->started > 0 ? $this->inoutstat->started : $this->overallconfig['zero_events_value']);
						?></td>
						<!-- Player stats History - substitution in start -->
						<td class="td_c"><?php
						$career['in'] += $this->inoutstat->sub_in;
						//echo ($this->inoutstat->sub_in );
                        echo ($this->inoutstat->sub_in > 0 ? $this->inoutstat->sub_in : $this->overallconfig['zero_events_value']);
						?></td>
						<!-- Player stats History - substitution out start -->
						<td class="td_c"><?php
						$career['out'] += $this->inoutstat->sub_out;
						//echo ($this->inoutstat->sub_out) ;
                        echo ($this->inoutstat->sub_out > 0 ? $this->inoutstat->sub_out : $this->overallconfig['zero_events_value']);
						?></td>
                        
                        <!-- Player stats History - played time -->
						<td class="td_c"><?php
						$career['playedtime'] += $timePlayed;
						echo ($timePlayed) ;
						?></td>
                        
						<?php
					}
				}
				?>
				<!-- Player stats History - allevents start -->
				<?php
				if ($this->config['show_career_events_stats'])
				{
					// stats per project
					if (count($this->AllEvents))
					{
						foreach($this->AllEvents as $eventtype)
						{
							$stat = $player->getPlayerEvents($eventtype->id, $player_hist->project_id, $player_hist->ptid);
							?>
				
				<td class="td_c"><?php echo ($stat > 0) ? $stat : $this->overallconfig['zero_events_value']; ?></td>
                <?php
						}
					}
				}
				if ($this->config['show_career_stats'])
				{
					foreach ($this->stats as $stat)
					{
						//do not show when there are no stats
						if (!empty($stat)) {
						    if ($stat->showInPlayer()) {    
				?>
				<td class="td_c hasTip" title="<?php echo JText::_($stat->name); ?>">
				<?php
							if(isset($this->projectstats[$stat->id][$player_hist->project_id][$player_hist->ptid])) {
								//echo $this->projectstats[$stat->id][$player_hist->project_id][$player_hist->ptid];
                                echo ($this->projectstats[$stat->id][$player_hist->project_id][$player_hist->ptid] > 0 ? $this->projectstats[$stat->id][$player_hist->project_id][$player_hist->ptid] : $this->overallconfig['zero_events_value']);
							} else {
								//echo 0;
                                echo $this->overallconfig['zero_events_value'];
							}
						    }
						}
				?></td>
				<?php
					}
				}
				?>
				<!-- Player stats History - allevents end -->
			</tr>
			<?php
			$k=(1-$k);
				}
			}
			?>
			<tr class="career_stats_total">
				<td class="td_r" colspan="2"><b><?php echo JText::_('COM_SPORTSMANAGEMENT_PERSON_CAREER_TOTAL'); ?></b></td>
				<td class="td_c"><?php echo $career['played']; ?></td>
				<?php //substitution system
				if	($this->config['show_substitution_stats'] && isset($this->overallconfig['use_jl_substitution']) &&
				($this->overallconfig['use_jl_substitution']==1))
				{
					?>
				<td class="td_c"><?php echo ($career['started'] > 0 ? $career['started'] : $this->overallconfig['zero_events_value']); ?></td>
				<td class="td_c"><?php echo ($career['in'] > 0 ? $career['in'] : $this->overallconfig['zero_events_value']); ?></td>
				<td class="td_c"><?php echo ($career['out'] > 0 ? $career['out'] : $this->overallconfig['zero_events_value']); ?></td>
                
                <td class="td_c"><?php echo ($career['playedtime'] ); ?></td>
				<?php
				}
				?>
				<?php // stats per project
				if ($this->config['show_career_events_stats'])
				{
					if (count($this->AllEvents))
					{
						foreach($this->AllEvents as $eventtype)
						{
							if (isset($player_hist))
							{
								$total=$player->getPlayerEvents($eventtype->id);
							}
							else
							{
								$total='';
							}
							?>
				<td class="td_c">
                <?php 
                //echo (($total) ? $total : 0);
                echo (($total) ? $total : $this->overallconfig['zero_events_value']); 
                ?>
                </td>
				<?php
						}
					}
				}
				if ($this->config['show_career_stats'])
				{
					foreach ($this->stats as $stat)
					{
						if(!empty($stat)) {
						    if ($stat->showInPlayer()) {
						?>
							<td class="td_c" title="<?php echo JText::_($stat->name); ?>">
							<?php
								if (isset($this->projectstats) &&
								    array_key_exists($stat->id, $this->projectstats))
								{
									//echo $this->projectstats[$stat->id]['totals'];
                                    //echo 'hallo';
                                    echo ($this->projectstats[$stat->id]['totals'] > 0 ? $this->projectstats[$stat->id]['totals'] : $this->overallconfig['zero_events_value']);
								}
								else	// In case there are no stats for the player
								{
									//echo 0;
                                    echo $this->overallconfig['zero_events_value'];
								}
							?>
							</td>
						<?php
						    }
						}
					}
				}
				?>
			</tr>
			</tbody>
		</table>
		</td>
	</tr>
</table>

<!-- Player stats History END -->
