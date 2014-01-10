<?php 
defined('_JEXEC') or die('Restricted access'); 

if ( $this->show_debug_info )
{
echo 'GAMES_HISTORY gamesstats<br /><pre>~' . print_r($this->gamesstats,true) . '~</pre><br />';
echo 'GAMES_HISTORY config<br /><pre>~' . print_r($this->config,true) . '~</pre><br />';
echo 'GAMES_HISTORY games<br /><pre>~' . print_r($this->games,true) . '~</pre><br />';
}

?>
<!-- Player stats History START -->
<?php
if (count($this->games))
{
	?>
<h2><?php echo JText::_('COM_SPORTSMANAGEMENT_PERSON_GAMES_HISTORY'); ?></h2>
<table width="96%" align="center" border="0" cellpadding="0"
	cellspacing="0">
	<tr>
		<td>
		<table id="gameshistory">
			<thead>
				<tr class="sectiontableheader">
					<th class="td_l" colspan="6"><?php echo JText::_('COM_SPORTSMANAGEMENT_PERSON_GAMES'); ?></th>
					<?php
					if ($this->config['show_substitution_stats'] && $this->overallconfig['use_jl_substitution'] == 1)
					{
						?>
					<th class="td_c"><?php
					$imageTitle=JText::_('COM_SPORTSMANAGEMENT_PERSON_STARTROSTER');
					echo JHTML::image(	'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/startroster.png',
					$imageTitle,array(' title' => $imageTitle));
					?></th>
					<th class="td_c"><?php
					$imageTitle=JText::_('COM_SPORTSMANAGEMENT_PERSON_IN');
					echo JHTML::image(	'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/in.png',
					$imageTitle,array(' title' => $imageTitle));
					?></th>
					<th class="td_c"><?php
					$imageTitle=JText::_('COM_SPORTSMANAGEMENT_PERSON_OUT');
					echo JHTML::image(	'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/out.png',
					$imageTitle,array(' title' => $imageTitle));
					?></th>
                    
                    <th class="td_c"><?php
				$imageTitle=JText::_('COM_SPORTSMANAGEMENT_PLAYED_TIME');
				echo JHTML::image(	'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/uhr.png',
				$imageTitle,array('title'=> $imageTitle,'height'=> 11));
		?></th>
        
					<?php
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
					if (!strpos(" ".$iconPath,"/"))
					{
						$iconPath="images/com_sportsmanagement/database/events/".$iconPath;
					}
					echo JHTML::image(	$iconPath,JText::_($eventtype->name),
					array(	"title" => JText::_($eventtype->name),
																		"align" => "top",
																		"hspace" => "2"));
					?></th>
					<?php
							}
						}
					}
					if ($this->config['show_career_stats'] && is_array($this->gamesstats))
					{
						foreach ($this->gamesstats as $stat)
						{

							//do not show statheader when there are no stats
							if (!empty($stat)) {
							    if ($stat->showInPlayer()) {
							?>
					<th class="td_c"><?php echo $stat->getImage(); ?></th>
					<?php
							    }
							}
						}
					}
                    if ($this->config['show_player_market_value'] )
                    {
                    ?>
					<th class="td_c"><?php echo JText::_('COM_SPORTSMANAGEMENT_EURO_MARKET_VALUE'); ?></th>
					<?php    
                    }
                    
					?>
				</tr>
			</thead>
			<tbody>
			<?php
			$k=0;
			$total=array();
			$total['startRoster']=0;
			$total['in']=0;
			$total['out']=0;
            $total['playedtime']=0;
			$total_event_stats=array();
            
            //echo ' games<br><pre>'.print_r($this->games,true).'</pre><br>';
			
            foreach ($this->games as $game)
			{
				$report_link = sportsmanagementHelperRoute::getMatchReportRoute($this->project->slug,$game->id);
				$teaminfo_home_link = sportsmanagementHelperRoute::getTeamInfoRoute($this->project->slug,$this->teams[$game->projectteam1_id]->team_id);
				$teaminfo_away_link = sportsmanagementHelperRoute::getTeamInfoRoute($this->project->slug,$this->teams[$game->projectteam2_id]->team_id);
				// gespielte zeit
                $model = $this->getModel();
                $timePlayed = 0;
                $this->assign('timePlayed',$model->getTimePlayed($this->teamPlayer->id,$this->project->game_regular_time,$game->id,$this->overallconfig['person_events']));
                $timePlayed  = $this->timePlayed;
                
//                echo ' teamPlayer->id<br><pre>'.print_r($this->teamPlayer->id,true).'</pre><br>';
//                echo ' game->id<br><pre>'.print_r($game->id,true).'</pre><br>';
//                echo ' timePlayed<br><pre>'.print_r($timePlayed,true).'</pre><br>';
                
                ?>
				<tr class="<?php echo ($k==0)? $this->config['style_class1'] : $this->config['style_class2']; ?>">
					<td class="td_l"><?php
					echo JHTML::link($report_link,strftime($this->config['games_date_format'],strtotime($game->match_date)));
					?></td>
					<td class="td_r<?php if ($game->projectteam_id == $game->projectteam1_id) echo " playerteam"; ?>">
						<?php 
						if ($this->config['show_gameshistory_teamlink'] == 1) {
							echo JHTML::link($teaminfo_home_link, $this->teams[$game->projectteam1_id]->name); 
						} else {
							echo $this->teams[$game->projectteam1_id]->name;
						}
						?>
					</td>
					<td class="td_r"><?php echo $game->team1_result; ?></td>
					<td class="td_c"><?php echo $this->overallconfig['seperator']; ?></td>
					<td class="td_l"><?php echo $game->team2_result; ?></td>
					<td class="td_l<?php if ($game->projectteam_id == $game->projectteam2_id) echo " playerteam"; ?>">
						<?php 
						if ($this->config['show_gameshistory_teamlink'] == 1) {
							echo JHTML::link($teaminfo_away_link, $this->teams[$game->projectteam2_id]->name); 
						} else {
							echo $this->teams[$game->projectteam2_id]->name;
						}
						?>
					</td>
					<?php
					if ($this->config['show_substitution_stats'] && $this->overallconfig['use_jl_substitution']==1)
					{
						?>
					<td class="td_c"><?php
					$total['startRoster'] += $game->started;
					//echo ($game->started) ;
                    echo ($game->started > 0 ? $game->started : $this->overallconfig['zero_events_value']);
					?></td>
					<td class="td_c"><?php
					$total['in'] += $game->sub_in;
					//echo ($game->sub_in) ;
                    echo ($game->sub_in > 0 ? $game->sub_in : $this->overallconfig['zero_events_value']);
					?></td>
					<td class="td_c"><?php
					$total['out'] += $game->sub_out;
					//echo ($game->sub_out) ;
                    echo ($game->sub_out > 0 ? $game->sub_out : $this->overallconfig['zero_events_value']);
					?></td>
                    
                    <td class="td_c"><?php
					$total['playedtime'] += $timePlayed;
					echo ($timePlayed) ;
					?></td>
                    
					<?php
					}
					if ($this->config['show_career_events_stats'])
					{
						foreach($this->AllEvents as $eventtype)
						{
							?>
					<td class="td_c"><?php
					if(!isset($total_event_stats[$eventtype->id]))
					{
						$total_event_stats[$eventtype->id]=0;
					}
					if(isset($this->gamesevents[$game->id][$eventtype->id]))
					{
						$total_event_stats[$eventtype->id] += $this->gamesevents[$game->id][$eventtype->id];
						echo $this->gamesevents[$game->id][$eventtype->id];
					}
					else
					{
						// as only matches are shown here where the player was part of, output a 0 i.s.o. a '-'
						//echo 0;
                        echo $this->overallconfig['zero_events_value'];
					}
					?></td>
					<?php
						}
					}
					if ($this->config['show_career_stats'] && is_array($this->gamesstats))
					{
						foreach ($this->gamesstats as $stat)
						{
							//do not show statheader when there are no stats
							if (!empty($stat)) { 
							    if ($stat->showInPlayer()) {
							?>
					<td class="td_c hasTip" title="<?php echo $stat->name; ?>"><?php
								if (isset($stat->gamesstats[$game->id]))
								{
									echo $stat->gamesstats[$game->id]->value;
								}
								else
								{
									// as only matches are shown here where the player was part of, output a 0 i.s.o. a '-'
									//echo 0;
                                    echo $this->overallconfig['zero_events_value'];
								}
					?></td>
					<?php
							    }
							}
						}
					}
                    if ($this->config['show_player_market_value'] )
                    {
                    ?>
					<td class="td_r hasTip" title="<?php echo number_format($game->market_value,0, ",", "."); ?>">
                    <?php    
                    }
					?>
				</tr>
				<?php
				$k=(1-$k);
			}
			?>
				<tr class="career_stats_total">
					<td class="td_r" colspan="6"><b><?php echo JText::_('COM_SPORTSMANAGEMENT_PERSON_GAMES_TOTAL'); ?></b></td>
					<?php
					if ($this->config['show_substitution_stats'] && $this->overallconfig['use_jl_substitution']==1)
					{
					?>
					<td class="td_c"><?php echo ($total['startRoster'] > 0 ? $total['startRoster'] : $this->overallconfig['zero_events_value']); ?></td>
					<td class="td_c"><?php echo ($total['in'] > 0 ? $total['in'] : $this->overallconfig['zero_events_value']); ?></td>
					<td class="td_c"><?php echo ($total['out'] > 0 ? $total['out'] : $this->overallconfig['zero_events_value']); ?></td>
                    <td class="td_c"><?php echo ($total['playedtime'] ) ; ?></td>
					<?php
					}
					if ($this->config['show_career_events_stats'])
					{
						if (count($this->AllEvents))
						{
							foreach($this->AllEvents as $eventtype)
							{
								?>
					<td class="td_c"><?php echo $total_event_stats[$eventtype->id]; ?></td>
					<?php
							}
						}
					}
					if ($this->config['show_career_stats'] && is_array($this->gamesstats))
					{
						foreach ($this->gamesstats as $stat)
						{
							//do not show statheader when there are no stats
							if (!empty($stat)) { 
							    if ( $stat->showInPlayer() && isset($stat->gamesstats['totals']) ) {
							?>
							    
					<td class="td_c hasTip" title="<?php echo $stat->name; ?>">
					<?php 
                    //echo $stat->gamesstats['totals']->value;
                    echo ($stat->gamesstats['totals']->value > 0 ? $stat->gamesstats['totals']->value : $this->overallconfig['zero_events_value']); 
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

<?php
}
?>
