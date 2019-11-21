<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_gameshistory.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage player
 */

defined('_JEXEC') or die('Restricted access'); 
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
$picture_path_sport_type_name = 'images/com_sportsmanagement/database/events';

?>
<!-- Player stats History START -->
<div class="<?php echo $this->divclassrow;?> table-responsive" id="playergameshistory">
<?php
if (count($this->games))
{
	?>
<h2><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_GAMES_HISTORY'); ?></h2>
<table class="<?PHP echo $this->config['history_table_class']; ?>" >
	<tr>
		<td>
		<table id="playergameshistorytable" class="<?PHP echo $this->config['history_table_class']; ?>">
			<thead>
				<tr class="">
					<th class="" colspan="6"><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_GAMES'); ?></th>
					<?php
					if ( $this->config['show_substitution_stats'] && $this->overallconfig['use_jl_substitution'] )
					{
						?>
					<th class=""><?php
					$imageTitle = Text::_('COM_SPORTSMANAGEMENT_PERSON_STARTROSTER');
                    $picture = $picture_path_sport_type_name.'/startroster.png';
                   
					echo HTMLHelper::image($picture,$imageTitle,array(' title' => $imageTitle));
					?></th>
					<th class=""><?php
					$imageTitle = Text::_('COM_SPORTSMANAGEMENT_PERSON_IN');
                    $picture = $picture_path_sport_type_name.'/in.png';
                    
					echo HTMLHelper::image($picture,$imageTitle,array(' title' => $imageTitle));
					?></th>
					<th class=""><?php
					$imageTitle = Text::_('COM_SPORTSMANAGEMENT_PERSON_OUT');
                    $picture = $picture_path_sport_type_name.'/out.png';
                   
					echo HTMLHelper::image($picture,$imageTitle,array(' title' => $imageTitle));
					?></th>
                    
                    <th class=""><?php
				$imageTitle=Text::_('COM_SPORTSMANAGEMENT_PLAYED_TIME');
                $picture = $picture_path_sport_type_name.'/uhr.png';
                
				echo HTMLHelper::image($picture,$imageTitle,array('title'=> $imageTitle,'height'=> 11));
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
					<th class=""><?php
					$iconPath = $eventtype->icon;
					if ( !strpos(" ".$iconPath,"/") )
					{
						$iconPath = "images/com_sportsmanagement/database/events/".$iconPath;
					}

					echo HTMLHelper::image($iconPath,Text::_($eventtype->name),
					array(	"title" => Text::_($eventtype->name),
					"align" => "top",
					'width'=> 30,
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
/**
 * 							do not show statheader when there are no stats
 */
							if (!empty($stat)) {
								try{  
							    if ($stat->showInPlayer()) {
							?>
					<th class=""><?php echo $stat->getImage(); ?></th>
					<?php
							    }
								}
catch (Exception $e)
{
    $this->app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' '.$stat), 'error');
}
							}
						}
					}
                    if ($this->config['show_player_market_value'] )
                    {
                    ?>
					<th class="td_c"><?php echo Text::_('COM_SPORTSMANAGEMENT_EURO_MARKET_VALUE'); ?></th>
					<?php    
                    }
                    
					?>
				</tr>
			</thead>
			<tbody>
			<?php
			$k = 0;
			$total = array();
			$total['startRoster'] = 0;
			$total['in'] = 0;
			$total['out'] = 0;
            $total['playedtime'] = 0;
			$total_event_stats = array();
		
            foreach ($this->games as $game)
			{
			 $routeparameter = array();
$routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = Factory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = $this->project->slug;
$routeparameter['mid'] = $game->match_slug;
$report_link = sportsmanagementHelperRoute::getSportsmanagementRoute('matchreport',$routeparameter); 
$routeparameter = array();
$routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = Factory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = $this->project->slug;
$routeparameter['tid'] = $this->teams[$game->projectteam1_id]->team_slug;
$routeparameter['ptid'] = 0;
$teaminfo_home_link = sportsmanagementHelperRoute::getSportsmanagementRoute('teaminfo',$routeparameter);				
$routeparameter['tid'] = $this->teams[$game->projectteam2_id]->team_slug;				
$teaminfo_away_link = sportsmanagementHelperRoute::getSportsmanagementRoute('teaminfo',$routeparameter);
				
				// gespielte zeit
                $model = $this->getModel();
                if ( !isset($this->overallconfig['person_events']) )
                {
                    $this->overallconfig['person_events'] = NULL;
                }
                $timePlayed = $model->getTimePlayed($this->teamPlayer->id,$this->project->game_regular_time,$game->id,$this->overallconfig['person_events'],$game->project_id);
                ?>
				<tr class="">
					<td class="">
					<?php
$jdate = Factory::getDate($game->match_date);
$jdate->setTimezone(new DateTimeZone($this->project->timezone));
$body = $jdate->format('l, d. F Y H:i'); 		    
					echo HTMLHelper::link($report_link,$body);
					?>
					</td>
					<td class="<?php if ($game->projectteam_id == $game->projectteam1_id) echo " playerteam"; ?>">
						<?php 
						if ( $this->config['show_gameshistory_teamlink'] ) 
                        {
echo sportsmanagementHelperHtml::getBootstrapModalImage('gameshistory'.$game->id.'-'.$game->projectteam1_id,
$game->home_logo,
$game->home_name,
'20',
'',
$this->modalwidth,
$this->modalheight,
$this->overallconfig['use_jquery_modal']);							
                            echo HTMLHelper::link($teaminfo_home_link, $this->teams[$game->projectteam1_id]->name); 
						} 
                        else 
                        {
echo sportsmanagementHelperHtml::getBootstrapModalImage('gameshistory'.$game->id.'-'.$game->projectteam1_id,
$game->home_logo,
$game->home_name,
'20',
'',
$this->modalwidth,
$this->modalheight,
$this->overallconfig['use_jquery_modal']);							
                            echo $this->teams[$game->projectteam1_id]->name;
						}
						?>
					</td>
					<td class=""><?php echo $game->team1_result; ?></td>
					<td class=""><?php echo $this->overallconfig['seperator']; ?></td>
					<td class=""><?php echo $game->team2_result; ?></td>
					<td class="<?php if ($game->projectteam_id == $game->projectteam2_id) echo " playerteam"; ?>">
						<?php 
						if ( $this->config['show_gameshistory_teamlink'] ) 
                        {
echo sportsmanagementHelperHtml::getBootstrapModalImage('gameshistory'.$game->id.'-'.$game->projectteam2_id,
$game->away_logo,
$game->away_name,
'20',
'',
$this->modalwidth,
$this->modalheight,
$this->overallconfig['use_jquery_modal']);                            
							echo HTMLHelper::link($teaminfo_away_link, $this->teams[$game->projectteam2_id]->name); 
						} 
                        else 
                        {
echo sportsmanagementHelperHtml::getBootstrapModalImage('gameshistory'.$game->id.'-'.$game->projectteam2_id,
$game->away_logo,
$game->away_name,
'20',
'',
$this->modalwidth,
$this->modalheight,
$this->overallconfig['use_jquery_modal']);                            
							echo $this->teams[$game->projectteam2_id]->name;
						}
						?>
					</td>
					<?php
					if ($this->config['show_substitution_stats'] && $this->overallconfig['use_jl_substitution']==1)
					{
						?>
					<td class=""><?php
					$total['startRoster'] += $game->started;
                    echo ($game->started > 0 ? $game->started : $this->overallconfig['zero_events_value']);
					?></td>
					<td class=""><?php
					$total['in'] += $game->sub_in;
                    echo ($game->sub_in > 0 ? $game->sub_in : $this->overallconfig['zero_events_value']);
					?></td>
					<td class=""><?php
					$total['out'] += $game->sub_out;
                    echo ($game->sub_out > 0 ? $game->sub_out : $this->overallconfig['zero_events_value']);
					?></td>
                    <td class=""><?php
					$total['playedtime'] += $timePlayed;
					echo ($timePlayed) ;
					?></td>
                    
					<?php
					}
					if ($this->config['show_career_events_stats'] && isset($this->AllEvents) )
					{
						foreach($this->AllEvents as $eventtype)
						{
							?>
					<td class=""><?php
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
/**
 * 						as only matches are shown here where the player was part of, output a 0 i.s.o. a '-'
 */
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
/**
 * 							do not show statheader when there are no stats
 */
							if (!empty($stat)) { 
							    if ($stat->showInPlayer()) {
							?>
					<td class="hasTip" title="<?php echo $stat->name; ?>"><?php
								if (isset($stat->gamesstats[$game->id]))
								{
									echo $stat->gamesstats[$game->id]->value;
								}
								else
								{
/**
 * 									as only matches are shown here where the player was part of, output a 0 i.s.o. a '-'
 */
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
					<td class=" hasTip" title="<?php echo number_format($game->market_value,0, ",", "."); ?>">
                    <?php    
                    }
					?>
				</tr>
				<?php
				$k=(1-$k);
			}
			?>
				<tr class="career_stats_total">
					<td class="" colspan="6"><b><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_GAMES_TOTAL'); ?></b></td>
					<?php
					if ($this->config['show_substitution_stats'] && $this->overallconfig['use_jl_substitution']==1)
					{
					?>
					<td class=""><?php echo ($total['startRoster'] > 0 ? $total['startRoster'] : $this->overallconfig['zero_events_value']); ?></td>
					<td class=""><?php echo ($total['in'] > 0 ? $total['in'] : $this->overallconfig['zero_events_value']); ?></td>
					<td class=""><?php echo ($total['out'] > 0 ? $total['out'] : $this->overallconfig['zero_events_value']); ?></td>
                    <td class=""><?php echo ($total['playedtime'] ) ; ?></td>
					<?php
					}
					if ($this->config['show_career_events_stats'])
					{
						if (count($this->AllEvents))
						{
							foreach($this->AllEvents as $eventtype)
							{
								?>
					<td class=""><?php echo $total_event_stats[$eventtype->id]; ?></td>
					<?php
							}
						}
					}
					if ($this->config['show_career_stats'] && is_array($this->gamesstats))
					{
						foreach ($this->gamesstats as $stat)
						{
/**
 * 							do not show statheader when there are no stats
 */
							if (!empty($stat)) { 
							    if ( $stat->showInPlayer() && isset($stat->gamesstats['totals']) ) {
							?>
							    
					<td class="hasTip" title="<?php echo $stat->name; ?>">
					<?php 
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
</div>
