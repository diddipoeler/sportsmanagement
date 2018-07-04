<?php 
/** SportsManagement ein Programm zur Verwaltung f�r alle Sportarten
 * @version   1.0.05
 * @file      default_playerstats.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: � 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage player
 */

defined('_JEXEC') or die('Restricted access'); 

$picture_path_sport_type_name = 'images/com_sportsmanagement/database/events';
$colspan = 1;
            
?>
<!-- Player stats History START -->
<h2><?php	echo JText::_('COM_SPORTSMANAGEMENT_PERSON_PERSONAL_STATISTICS');	?></h2>
<table class="<?PHP echo $this->config['player_table_class']; ?> table-responsive " >
	<tr>
		<td>
		<table class="<?PHP echo $this->config['player_table_class']; ?> table-responsive" >
			<thead>
			<tr class="sectiontableheader">
				<th class="td_l" class="nowrap"><?php echo JText::_('COM_SPORTSMANAGEMENT_PERSON_COMPETITION'); ?></th>
                <?PHP
                if ( $this->config['show_plstats_team'] )
	{
	   $colspan++;
	   ?>
				<th class="td_l" class="nowrap"><?php echo JText::_('COM_SPORTSMANAGEMENT_PERSON_TEAM'); ?></th>
                <?PHP
                }
                if ( $this->config['show_plstats_ppicture'] )
	{
	   $colspan++;
                ?>
                <th class="td_l" class="nowrap"><?php echo JText::_('COM_SPORTSMANAGEMENT_TEAMINFO_PLAYERS_PICTURE'); ?></th>
                 <?PHP
                }
                ?>
				<th class="td_c">
                <?php
				$imageTitle = JText::_('COM_SPORTSMANAGEMENT_PERSON_PLAYED');
                $picture = $picture_path_sport_type_name.'/played.png';
                if ( !curl_init($picture) )
{
$picture = sportsmanagementHelper::getDefaultPlaceholder("icon");
}

				echo JHtml::image($picture,$imageTitle,array(' title' => $imageTitle));
				?></th>
				<?php
				if ($this->config['show_substitution_stats'])
				{
					if ((isset($this->overallconfig['use_jl_substitution'])) && ($this->overallconfig['use_jl_substitution']==1))
					{
						?>
				<th class="td_c"><?php
				$imageTitle = JText::_('COM_SPORTSMANAGEMENT_PERSON_STARTROSTER');
                $picture = $picture_path_sport_type_name.'/startroster.png';
                if ( !curl_init($picture) )
{
$picture = sportsmanagementHelper::getDefaultPlaceholder("icon");
}
				echo JHtml::image($picture,$imageTitle,array(' title' => $imageTitle));
				?></th>
				<th class="td_c"><?php
				$imageTitle = JText::_('COM_SPORTSMANAGEMENT_PERSON_IN');
                $picture = $picture_path_sport_type_name.'/in.png';
                if ( !curl_init($picture) )
{
$picture = sportsmanagementHelper::getDefaultPlaceholder("icon");
}
				echo JHtml::image($picture,$imageTitle,array(' title' => $imageTitle));
				?></th>
				<th class="td_c"><?php
				$imageTitle = JText::_('COM_SPORTSMANAGEMENT_PERSON_OUT');
                $picture = $picture_path_sport_type_name.'/out.png';
                if ( !curl_init($picture) )
{
$picture = sportsmanagementHelper::getDefaultPlaceholder("icon");
}
				echo JHtml::image($picture,$imageTitle,array(' title' => $imageTitle));
				?></th>
                
                <?PHP
                // gespielte zeit
                ?>
                <th class="td_c"><?php
				$imageTitle = JText::_('COM_SPORTSMANAGEMENT_PLAYED_TIME');
                $picture = $picture_path_sport_type_name.'/uhr.png';
                if ( !curl_init($picture) )
{
$picture = sportsmanagementHelper::getDefaultPlaceholder("icon");
}
				echo JHtml::image($picture,$imageTitle,array('title'=> $imageTitle,'height'=> 11));
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
				$iconPath = $eventtype->icon;
				if (!strpos(" ".$iconPath,"/"))
                {
                    $iconPath = "images/com_sportsmanagement/database/events/".$iconPath;
                    }
                    if ( !curl_init($iconPath) )
{
$iconPath = sportsmanagementHelper::getDefaultPlaceholder("icon");
}
                    
				echo JHtml::image($iconPath,
					JText::_($eventtype->name),
					array(	"title" => JText::_($eventtype->name),
						"align" => "top",
						"hspace" => "2"));
				?></th>
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
				<th class="td_c"><?php echo !empty($stat) ? $stat->getImage() : ""; ?></th>
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
      

			if (count($this->historyPlayer) > 0)
			{
				foreach ($this->historyPlayer as $player_hist)
				{
					$model = $this->getModel();
					$this->inoutstat = $model->getInOutStats($player_hist->project_id, $player_hist->ptid, $player_hist->tpid);
                    
                    // gespielte zeit
                    if ( !isset($this->overallconfig['person_events']) )
                    {
                        $this->overallconfig['person_events'] = NULL;
                    }
                    
                    $timePlayed = $model->getTimePlayed($player_hist->tpid,$this->project->game_regular_time,NULL,$this->overallconfig['person_events'],$player_hist->project_id);
                                    
                    $routeparameter = array();
       $routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
       $routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
       $routeparameter['p'] = $player_hist->project_slug;
       $routeparameter['tid'] = $player_hist->team_slug;
       $routeparameter['pid'] = $this->person->slug;
            
                    $link1 = sportsmanagementHelperRoute::getSportsmanagementRoute('player',$routeparameter);
                     $routeparameter = array();
       $routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
       $routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
       $routeparameter['p'] = $player_hist->project_slug;
       $routeparameter['tid'] = $player_hist->team_slug;
       $routeparameter['ptid'] = 0;
					$link2 = sportsmanagementHelperRoute::getSportsmanagementRoute('teaminfo',$routeparameter);
					?>
			<tr class="">
				<td class="td_l" nowrap="nowrap">
                <?php
                if ( $this->config['show_project_logo'] ) 
                            { 
if ( !curl_init( $player_hist->project_picture ) )
				{
					$player_hist->project_picture = sportsmanagementHelper::getDefaultPlaceholder("clublogobig");
				}
                                                
echo sportsmanagementHelperHtml::getBootstrapModalImage('playerstatsproject'.$player_hist->project_id.'-'.$player_hist->team_id,
$player_hist->project_picture,
$player_hist->project_name,
'20',
'',
$this->modalwidth,
$this->modalheight,
$this->overallconfig['use_jquery_modal']); 
                }
                echo JHtml::link($link1,$player_hist->project_name); 
                ?>
				</td>
     <?PHP           
                if ( $this->config['show_plstats_team'] )
	{
	   ?>
				<td class="td_l" class="nowrap">
				<?php
                if ( $this->config['show_team_logo'] ) 
                            { 
if ( !curl_init( $player_hist->club_picture ) )
				{
					$player_hist->club_picture = sportsmanagementHelper::getDefaultPlaceholder("clublogobig");
				}                                
echo sportsmanagementHelperHtml::getBootstrapModalImage('playerstatsteam'.$player_hist->project_id.'-'.$player_hist->team_id,
$player_hist->club_picture,
$player_hist->team_name,
'20',
'',
$this->modalwidth,
$this->modalheight,
$this->overallconfig['use_jquery_modal']);                                
                            }    

			if ( $this->config['show_team_picture'] ) 
                            { 
echo sportsmanagementHelperHtml::getBootstrapModalImage('playerstatsteampicture'.$player_hist->project_id.'-'.$player_hist->team_id,
$player_hist->team_picture,
$player_hist->team_name,
'40',
'',
$this->modalwidth,
$this->modalheight,
$this->overallconfig['use_jquery_modal']);                                				
				
			}
			
                    if ( $this->config['show_playerstats_teamlink'] ) 
                    {
						echo JHtml::link($link2,$player_hist->team_name);
					} 
                    else 
                    {
						echo $player_hist->team_name;
					}
                    
                     
				?>
				</td>
                <?PHP
                }
                if ( $this->config['show_plstats_ppicture'] )
	{
                
                ?>
                <td>
                <?PHP
echo sportsmanagementHelperHtml::getBootstrapModalImage('playerstats'.$player_hist->project_id.'-'.$player_hist->team_id,
$player_hist->season_picture,
$player_hist->team_name,
'50',
'',
$this->modalwidth,
$this->modalheight,
$this->overallconfig['use_jquery_modal']);
                ?>
                </td>
                <?PHP
                }
                ?>
				<!-- Player stats History - played start -->
				<td class="td_c"><?php
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
                        echo ($this->inoutstat->started > 0 ? $this->inoutstat->started : $this->overallconfig['zero_events_value']);
						?></td>
						<!-- Player stats History - substitution in start -->
						<td class="td_c"><?php
						$career['in'] += $this->inoutstat->sub_in;
                        echo ($this->inoutstat->sub_in > 0 ? $this->inoutstat->sub_in : $this->overallconfig['zero_events_value']);
						?></td>
						<!-- Player stats History - substitution out start -->
						<td class="td_c"><?php
						$career['out'] += $this->inoutstat->sub_out;
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
				
				<td ptid="<?php echo $player_hist->ptid; ?>"  id="<?php echo $eventtype->id; ?>" title="<?php echo $player_hist->project_id; ?>" class="td_c"><?php echo ($stat > 0) ? $stat : $this->overallconfig['zero_events_value']; ?></td>
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
                                echo ($this->projectstats[$stat->id][$player_hist->project_id][$player_hist->ptid] <> 0 ? $this->projectstats[$stat->id][$player_hist->project_id][$player_hist->ptid] : $this->overallconfig['zero_events_value']);
							} else {
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
				<td class="td_r" colspan="<?php echo $colspan; ?>  "><b><?php echo JText::_('COM_SPORTSMANAGEMENT_PERSON_CAREER_TOTAL'); ?></b></td>
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
                                    echo ($this->projectstats[$stat->id]['totals'] <> 0 ? $this->projectstats[$stat->id]['totals'] : $this->overallconfig['zero_events_value']);
								}
								else	// In case there are no stats for the player
								{
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
