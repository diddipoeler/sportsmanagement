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
$dates			= $this->sortByDate();

if($this->config['show_division']){$nbcols++;}
if($this->config['show_match_number']){$nbcols++;}
if($this->config['show_events']){$nbcols++;}
if($this->config['show_match_summary']){$nbcols++;}
if($this->config['show_time']){$nbcols++;}
if($this->config['show_playground'] || $this->config['show_playground_alert']){$nbcols = $nbcols+2;}
if($this->config['show_referee']){$nbcols++;}
if($this->config['result_style']==2){$nbcols++;}
if($this->config['show_attendance_column']){$nbcols++; $nbcols_header++;}

if ($this->config['show_comments_count'] > 0){
	$nbcols++;
	$nbcols_header++;

	require_once (JPATH_ROOT . '/components/com_jcomments/jcomments.class.php');
    require_once (JPATH_ROOT . '/components/com_jcomments/jcomments.config.php');
	require_once (JPATH_ROOT . '/components/com_jcomments/models/jcomments.php');

	// get joomleague comments plugin params
	JPluginHelper::importPlugin( 'joomleague' );

	$plugin	= & JPluginHelper::getPlugin('joomleague', 'comments');

	if (is_object($plugin)) {
		$pluginParams = new JParameter($plugin->params);
	}
	else {
		$pluginParams = new JParameter('');
	}
	$separate_comments 	= $pluginParams->get( 'separate_comments', 0 );
}
?>

<table class='fixtures-results'>
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
            //echo JHtml::date( $date, JText::_('COM_SPORTSMANAGEMENT_RESULTS_GAMES_DATE_MONTH'));
            echo JHtml::date( $date, JText::_('COM_SPORTSMANAGEMENT_RESULTS_GAMES_DATE_DAY'));
                if ($this->config['show_matchday_dateheader']) {
                    echo ' - ' . JText::sprintf( 'COM_SPORTSMANAGEMENT_RESULTS_GAMEDAY_NB',$this->roundcode ); } ?>
            </th>
            <?php
            if ($this->config['show_attendance_column']) {
				?>
				<th class="right"><?php echo JText::_( 'COM_SPORTSMANAGEMENT_RESULTS_ATTENDANCE' ); ?></th>
			<?php
			}
            if ($this->config['show_comments_count'] > 0) {
				?>
				<th class="center"><?php echo JText::_( 'COM_SPORTSMANAGEMENT_RESULTS_COMMENTS' ); ?></th>
			<?php
			}

		} else {
			?>
			<th colspan="<?php echo $nbcols; ?>"><?php echo JHtml::date( $date, JTExt::_('COM_SPORTSMANAGEMENT_RESULTS_GAMES_DATE_DAY'));
                if ($this->config['show_matchday_dateheader']) {
                    echo ' - ' . JText::sprintf( 'COM_SPORTSMANAGEMENT_RESULTS_GAMEDAY_NB',$this->roundcode ); } ?>
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
		$this->assignRef('game', $game);
		if ($game->published)
		{
			if (isset($game->team1_result))
			{
				$report_link = sportsmanagementHelperRoute::getMatchReportRoute($this->project->slug, $game->slug);
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
	<tr	class="<?php echo ($k == 0)? 'sectiontableentry1' : 'sectiontableentry2'; ?>"<?php echo $favStyle; ?>>
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
			$img = JHtml::image('media/com_sportsmanagement/jl_images/events.png', 'events.png');
			$params = array("title"   => JText::_('COM_SPORTSMANAGEMENT_TEAMPLAN_EVENTS'),
							"onclick" => 'switchMenu(\'info'.$game->id.'\');return false;');
			echo JHtml::link($link,$img,$params);
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
			$img = JHtml::image('media/com_sportsmanagement/jl_images/discuss.gif', 'discuss.gif');
			$params = array("title"   => JText::_('COM_SPORTSMANAGEMENT_TEAMPLAN_EVENTS'),
							"onclick" => 'example_alertBox(\''.$game->id.'\');');
			echo JHtml::link($link,$img,$params);
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
			$this->config );
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
			<?php sportsmanagementHelperHtml::showMatchPlayground($match); ?>
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
				<?php echo $this->formatResult($this->teams[$game->projectteam1_id],$this->teams[$game->projectteam2_id],$game,$report_link); ?>
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
					echo $this->formatResult($this->teams[$game->projectteam1_id],$this->teams[$game->projectteam2_id],$game,$report_link);
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
					echo $this->formatResult($this->teams[$game->projectteam1_id],$this->teams[$game->projectteam2_id],$game,$report_link);
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
			<?php $this->showMatchRefereesAsTooltip($game); ?>
			</td>
		<?php
		}
		?>

		<?php
		if (($this->config['show_playground'] || $this->config['show_playground_alert']))
		{
			?>
		<!-- show only playground or playgroundalert if playgrund differs from normal -->
			<td>
			<?php sportsmanagementHelperHtml::showMatchPlayground($game); ?>
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

			if ($separate_comments) {
				// Comments integration trigger when separate_comments in plugin is set to yes/1
				if (isset($game->team1_result))
				{
					$joomleage_comments_object_group = 'com_sportsmanagement_matchreport';
				}
				else {
					$joomleage_comments_object_group = 'com_sportsmanagement_nextmatch';
				}
			}
			else {
				// Comments integration trigger when separate_comments in plugin is set to no/0
				$joomleage_comments_object_group = 'com_sportsmanagement';
			}

			$options 					= array();
			$options['object_id']		= (int) $game->id;
			$options['object_group']	= $joomleage_comments_object_group;
			$options['published']		= 1;

			$count = JCommentsModel::getCommentsCount($options);

			if ($count == 1) {
				$imgTitle		= $count.' '.JText::_('COM_SPORTSMANAGEMENT_RESULTS_COMMENTS_COUNT_SINGULAR');
				if ($this->config['show_comments_count'] == 1) {
					$href_text		= JHtml::image( JUri::root().'media/com_sportsmanagement/jl_images/discuss_active.gif', $imgTitle, array(' title' => $imgTitle,' border' => 0,' style' => 'vertical-align: middle'));
				} elseif ($this->config['show_comments_count'] == 2) {
					$href_text		= '<span title="'. $imgTitle .'">('.$count.')</span>';
				}
				//Link
				if (isset($game->team1_result))
				{
					$link = sportsmanagementHelperRoute::getMatchReportRoute($this->project->slug, $game->slug).'#comments';
				}
				else
				{
					$link = sportsmanagementHelperRoute::getNextMatchRoute($this->project->slug, $game->slug).'#comments';
				}
				$viewComment	= JHtml::link($link, $href_text);
				echo $viewComment;
			}
			elseif ($count > 1) {
				$imgTitle	= $count.' '.JText::_('COM_SPORTSMANAGEMENT_RESULTS_COMMENTS_COUNT_PLURAL');
				if ($this->config['show_comments_count'] == 1) {
					$href_text		= JHtml::image( JUri::root().'media/com_sportsmanagement/jl_images/discuss_active.gif', $imgTitle, array(' title' => $imgTitle,' border' => 0,' style' => 'vertical-align: middle'));
				} elseif ($this->config['show_comments_count'] == 2) {
					$href_text		= '<span title="'. $imgTitle .'">('.$count.')</span>';
				}
				//Link
				if (isset($game->team1_result))
				{
					$link = sportsmanagementHelperRoute::getMatchReportRoute($this->project->slug, $game->slug).'#comments';
				}
				else
				{
					$link = sportsmanagementHelperRoute::getNextMatchRoute($this->project->slug, $game->slug).'#comments';
				}
				$viewComment	= JHtml::link($link, $href_text);
				echo $viewComment;
			}
			else {
				$imgTitle	= JText::_('COM_SPORTSMANAGEMENT_RESULTS_COMMENTS_COUNT_NOCOMMENT');
				if ($this->config['show_comments_count'] == 1) {
					$href_text		= JHtml::image( JUri::root().'media/com_sportsmanagement/jl_images/discuss.gif', $imgTitle, array(' title' => $imgTitle,' border' => 0,' style' => 'vertical-align: middle'));
				} elseif ($this->config['show_comments_count'] == 2) {
					$href_text		= '<span title="'. $imgTitle .'">('.$count.')</span>';
				}
				//Link
				if (isset($game->team1_result))
				{
					$link = sportsmanagementHelperRoute::getMatchReportRoute($this->project->slug, $game->slug).'#comments';
				}
				else
				{
					$link = sportsmanagementHelperRoute::getNextMatchRoute($this->project->slug, $game->slug).'#comments';
				}
				$viewComment	= JHtml::link($link, $href_text);
				echo $viewComment;
			}
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
												$this->config );
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
