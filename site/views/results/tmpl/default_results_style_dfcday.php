<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_results_style_dfcday.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @subpackage results
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
?>

<script>
function example_alertBox( boxText )
{		
	var options = {size: {x: 300, y: 250}};
	SqueezeBox.initialize(options);
	SqueezeBox.setContent('string','nummer: ' + boxText);
			
	
}
</script>

<?php
$nbcols			= 7;
$nbcols_header	= 0;
$dates			= $this->sortByDate($this->matches);

if($this->config['show_division']){$nbcols++;}
if($this->config['show_match_number']){$nbcols++;}
if($this->config['show_events']){$nbcols++;}
if($this->config['show_match_summary']){$nbcols++;}
if($this->config['show_time']){$nbcols++;}
if($this->config['show_playground'] || $this->config['show_playground_alert']){$nbcols++;}
if($this->config['show_referee']){$nbcols++;}
if($this->config['result_style']==2){$nbcols++;}
if($this->config['show_attendance_column']){$nbcols++; $nbcols_header++;}

if ($this->config['show_comments_count'] > 0){
	$commmentsInstance = sportsmanagementModelComments::CreateInstance($this->config);
	$nbcols++;
	$nbcols_header++;
}
?>

<table class="<?PHP echo $this->config['table_class']; ?>">
	<?php
	foreach( $dates as $date => $games )
	{
		?>
	<!-- DATE HEADER -->
	<tr class="sectiontableheader">

		<?php
		if ( ($this->config['show_attendance_column']) || ($this->config['show_comments_count'] > 0) )
		{
			?>
			<th colspan="<?php echo $nbcols-$nbcols_header; ?>"><?php
            echo HTMLHelper::date( $date, Text::_('COM_SPORTSMANAGEMENT_RESULTS_GAMES_DATE_DAY'));
                if ($this->config['show_matchday_dateheader']) {
                    echo ' - ' . Text::sprintf( 'COM_SPORTSMANAGEMENT_RESULTS_GAMEDAY_NB',$this->roundcode ); } ?>
            </th>
            <?php
            if ($this->config['show_attendance_column']) {
				?>
				<th class="right"><?php echo Text::_( 'COM_SPORTSMANAGEMENT_RESULTS_ATTENDANCE' ); ?></th>
			<?php
			}
            if ($this->config['show_comments_count'] > 0) {
				?>
				<th class="center"><?php echo Text::_( 'COM_SPORTSMANAGEMENT_RESULTS_COMMENTS' ); ?></th>
			<?php
			}

		} else {
			?>
			<th colspan="<?php echo $nbcols; ?>"><?php echo HTMLHelper::date( $date, Text::_('COM_SPORTSMANAGEMENT_RESULTS_GAMES_DATE_DAY'));
                if ($this->config['show_matchday_dateheader']) {
                    echo ' - ' . Text::sprintf( 'COM_SPORTSMANAGEMENT_RESULTS_GAMEDAY_NB',$this->roundcode ); } ?>
            </th>
		<?php
		}
		?>
	</tr>
	<!-- DATE HEADER END-->
	<!-- GAMES -->
	<?php
	$k = 0;

	foreach( $games as $game )
	{
		$this->game = $game;
		if ($game->published)
		{
			if (isset($game->team1_result))
			{
			$routeparameter = array();
$routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = Factory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = $this->project->slug;
$routeparameter['mid'] = $game->slug;
$report_link = sportsmanagementHelperRoute::getSportsmanagementRoute('matchreport',$routeparameter);
				
			}
			else
			{
				$report_link = sportsmanagementHelperRoute::getNextMatchRoute($this->project->slug, $game->slug);
			}

			$events	= sportsmanagementModelProject::getMatchEvents($game->id);
			$subs	= sportsmanagementModelProject::getMatchSubstitutions($game->id);

			if ($this->config['use_tabs_events']) {
			    $hasEvents = (count($events) + count($subs) > 0 && $this->config['show_events']);
			} else {
			    //no subs are shown when not using tabs for displaying events so don't check for that
			    $hasEvents = (count($events) > 0 && $this->config['show_events']);
			}

			?>
			<!-- Format teams correctly -->
			<?php
			if($this->config['switch_home_guest']){
				$team1 = $this->teams[$game->projectteam2_id];
				$team2 = $this->teams[$game->projectteam1_id];

			}
			else
			{
				$team1 = $this->teams[$game->projectteam1_id];
				$team2 = $this->teams[$game->projectteam2_id];
			}
			$favStyle 	= '';
			$color		= '';
			$isFavTeam = in_array($team1->id, $this->favteams) ? 1 : in_array($team2->id, $this->favteams);
			if ( $isFavTeam && $this->project->fav_team_highlight_type == 1 && $this->config['highlight_fav'] == 1 )
			{
				if( trim( $this->project->fav_team_color ) != "" )
				{
					$color = trim($this->project->fav_team_color);
				}
				$format = "%s";
				$favStyle = ' style="';
				$favStyle .= ($this->project->fav_team_text_bold != '') ? 'font-weight:bold;' : '';
				$favStyle .= (trim($this->project->fav_team_text_color) != '') ? 'color:'.trim($this->project->fav_team_text_color).';' : '';
				$favStyle .= ($color != '') ? 'background-color:' . $color . ';' : '';
				if ($favStyle != ' style="')
				{
				  $favStyle .= '"';
				}
				else {
				  $favStyle = '';
				}
			}
			?>

	<!--<tr class="result<?php // echo ($k == 0) ? '' : ' alt'; ?><?php //echo ($hasEvents ? ' hasevents':''); ?>">-->
	<tr	<?php echo $favStyle; ?>>
		<?php
		if ($this->config['show_match_number'])
		{
			?>
		<!-- show matchnumber -->
		<td width="5" class="ko"><?php
		if ( $game->match_number>0 )
		{
			echo $game->match_number;
		}
		else
		{
			echo "&nbsp;";
		}
		?></td>
		<?php
		}
		?>

		<?php
		if ($this->config['show_events'])
		{
			?>
		<!-- show matcheventsimage -->
		<td width="5" class="ko"><?php
		if ($hasEvents)
		{
			$link = "javascript:void(0);";
			$img = HTMLHelper::image('media/com_sportsmanagement/jl_images/events.png', 'events.png');
			$params = array("title"   => Text::_('COM_SPORTSMANAGEMENT_TEAMPLAN_EVENTS'),
							"onclick" => 'switchMenu(\'info'.$game->id.'\');return false;');
			echo HTMLHelper::link($link,$img,$params);
		}
		else
		{
			echo "&nbsp;";
		}
		?></td>
		<?php
		}
  
    // diddipoeler  
    if ($this->config['show_match_summary'])
		{
		  ?>
		<td width="5" class="ko">
        <?php
$link = "javascript:void(0);";
			$img = HTMLHelper::image('media/com_sportsmanagement/jl_images/discuss.gif', 'discuss.gif');
			$params = array("title"   => Text::_('COM_SPORTSMANAGEMENT_TEAMPLAN_EVENTS'),
							"onclick" => 'example_alertBox(\''.$game->id.'\');');
			echo HTMLHelper::link($link,$img,$params);
            ?></td>
		<?php 	
		}
		?>
		<!-- show divisions -->
		<?php
		if($this->config['show_division']) {
			echo '<td width="5" class="ko" nowrap="nowrap">';
			echo sportsmanagementHelperHtml::showDivisonRemark(	$this->teams[$game->projectteam1_id],
			$this->teams[$game->projectteam2_id],
			$this->config,$game->division_id );
			echo '</td>';
		}
		if ($this->config['show_time'])
		{
			?>
		<!-- show matchtime -->
		<td width='5' class='ko'><abbr title='' class='dtstart'> <?php echo sportsmanagementHelperHtml::showMatchTime($game, $this->config, $this->overallconfig, $this->project); ?>
		</abbr></td>
		<?php
		}
		?>

		<?php
		if (($this->config['show_playground'] || $this->config['show_playground_alert']))
		{
		?>
		<!-- show playground -->
		<td>
			<?php sportsmanagementHelperHtml::showMatchPlayground($game, $this->config); ?>
		</td>
		<?php
		}
//--------------------------------------------------------------------------------------------------------------
		//$this->config['result_style']=2;
		if ($this->config['result_style']==0)
		{
	
    switch ($this->config['show_logo_small'])
    {
    case 0:
    case 1:
    case 2:
    $width = '20';
    break;
    case 3:
    case 4:
    $width = '40';
    break;
  
    }
        
		?>
			<!-- show team-icons and/or -names -->
			<td width='<?PHP echo $width;?>'>
				<?php echo $this->getTeamClubIcon($team1, $this->config['show_logo_small'], array('class' => 'teamlogo')); ?>
			</td>
			<td>
				<?php
					$isFavTeam = in_array($team1->id, $this->favteams);
					echo sportsmanagementHelper::formatTeamName($team1,'g'.$game->id,$this->config,$isFavTeam );
				?>
			</td>
			<td width='<?PHP echo $width;?>'>
				<?php echo $this->getTeamClubIcon($team2, $this->config['show_logo_small'], array('class' => 'teamlogo')); ?>
			</td>
			<td>
				<?php
					$isFavTeam = in_array($team2->id, $this->favteams);
					echo sportsmanagementHelper::formatTeamName($team2,'g'.$game->id,$this->config,$isFavTeam);
				?>
			</td>
			<!-- show match score -->
			<td width='10' class='score'>
				<?php
        echo $this->formatResult($this->teams[$game->projectteam1_id],$this->teams[$game->projectteam2_id],$game,$report_link,$this->config);
        ?>
			</td>
				<?php
		}
//--------------------------------------------------------------------------------------------------------------
	?>
	<?php
//--------------------------------------------------------------------------------------------------------------
		if ($this->config['result_style']==1)
		{
			?>
			<!-- show team-icons and/or -names -->
			<td class='t-right'>
				<?php
					$isFavTeam = in_array($team1->id, $this->favteams);
					echo sportsmanagementHelper::formatTeamName($team1,'g'.$game->id,$this->config,$isFavTeam);
				?>
			</td>
			<td width='20'>
				<?php echo $this->getTeamClubIcon($team1, $this->config['show_logo_small'], array('class' => 'teamlogo')); ?>
			</td>
			<!-- show match score -->
			<td width='5' class='score' nowrap='nowrap'>
				<?php
					echo '&nbsp;';
					echo $this->formatResult($this->teams[$game->projectteam1_id],$this->teams[$game->projectteam2_id],$game,$report_link,$this->config);
					echo '&nbsp;';
				?>
			</td>
			<td width='20'>
				<?php echo $this->getTeamClubIcon($team2, $this->config['show_logo_small'], array('class' => 'teamlogo')); ?>
			</td>
			<td class='t-left'>
				<?php
					$isFavTeam = in_array($team2->id, $this->favteams);
					echo sportsmanagementHelper::formatTeamName($team2,'g'.$game->id,$this->config,$isFavTeam);
				?>
			</td>
			<?php
		}
//--------------------------------------------------------------------------------------------------------------
	?>
	<?php
//--------------------------------------------------------------------------------------------------------------
		if ($this->config['result_style']==2)
		{
			?>
			<!-- show team-icons and/or -names -->
			<td class='t-right'>
				<?php
					$isFavTeam = in_array($team1->id, $this->favteams);
					echo sportsmanagementHelper::formatTeamName($team1,'g'.$game->id,$this->config,$isFavTeam);
				?>
			</td>
			<td width='20'>
				<?php echo $this->getTeamClubIcon($team1, $this->config['show_logo_small'], array('class' => 'teamlogo')); ?>
			</td>
			<td width='5'>
			-
			</td>
			<td width='20'>
				<?php echo $this->getTeamClubIcon($team2, $this->config['show_logo_small'], array('class' => 'teamlogo')); ?>
			</td>
			<td class='t-left'>
				<?php
					$isFavTeam = in_array($team2->id, $this->favteams);
					echo sportsmanagementHelper::formatTeamName($team2,'g'.$game->id,$this->config,$isFavTeam);
				?>
			</td>
			<!-- show match score -->
			<td width='5' class='score' nowrap='nowrap'>
				<?php
					echo '&nbsp;';
					echo $this->formatResult($this->teams[$game->projectteam1_id],$this->teams[$game->projectteam2_id],$game,$report_link,$this->config);
					echo '&nbsp;';
				?>
			</td>
			<?php
		}
//--------------------------------------------------------------------------------------------------------------
	?>

		<!-- show hammer if there is a alternative decision of the score -->
		<td width="5" class="ko">
		<?php $this->showReportDecisionIcons($game); ?>
		</td>

		<?php
		if($this->config['show_referee'])
		{
			?>
		<!-- show matchreferees icon with tooltip -->
			<td width="5" class="referees">
			<?php sportsmanagementViewResults::showMatchRefereesAsTooltip($game,$this->project,$this->config); ?>
			</td>
		<?php
		}
		?>

		<?php
		if ($this->config['show_attendance_column'])
		{
			?>
		<!-- show attendance -->
			<td class="t-right"><?php
			if ( $game->crowd > 0 )
			{
				echo $game->crowd;
			}
			else
			{
				echo '&nbsp;';
			}
			?>
			</td>
			<?php
		}
		?>

		<?php
		if ($this->config['show_comments_count'] > 0)
		{
			?>
		<!-- show comments -->
		<td class="center"><?php
			echo $commmentsInstance->showMatchCommentIcon($game,$team1, $team2,$this->config, $this->project);
		?></td>
		<?php
		}
		?>

	</tr>

	<?php
	if ($hasEvents)
	{
		?>
	<!-- show icon for editing events in edit mode -->
	<tr class="events <?php echo ($k == 0) ? '' : 'alt'; ?>">
		<td colspan="<?php echo $nbcols; ?>">
		<div id="info<?php echo $game->id; ?>" style="display: none;">
		<table class='matchreport' border='0'>
			<tr>
				<td><?php
				echo $this->showEventsContainerInResults(
												$game,
												$this->projectevents,
												$events,
												$subs,
												$this->config,
                        $this->project );
				?></td>
			</tr>
		</table>
		</div>
		</td>
	</tr>
	<?php
	}

	$k = 1 - $k;
		}
	}
	?>
	<!-- GAMES END -->
	<?php
	}
	?>
</table>

<br />
