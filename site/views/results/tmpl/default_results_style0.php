<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_results_style0.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage results
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\Registry\Registry;
?>
<style>
#sbox-window {
background-color: transparent;

}

</style>
<script>
function example_alertBox( boxText )
{		
	var options = {size: {x: 300, y: 250}};
	SqueezeBox.initialize(options);
	SqueezeBox.setContent('string','Spielnummer: ' + boxText);
			
	
}
</script>

<?php
$nbcols	= 6;
$nbcols_header = 0;
$dates = sportsmanagementViewResults::sortByDate($this->matches);

if($this->config['show_division']){$nbcols++;}
if($this->config['show_match_number']){$nbcols++;}
if($this->config['show_events']){$nbcols++;}
if($this->config['show_match_summary']){$nbcols++;}
if($this->config['show_time']){$nbcols++;}
if($this->config['show_playground'] || $this->config['show_playground_alert']){$nbcols = $nbcols+2;}
if($this->config['show_referee']){$nbcols++;}
if($this->config['result_style']==2){$nbcols++;}
if($this->config['show_attendance_column']){$nbcols++; $nbcols_header++;}

if ( $this->config['show_comments_count'] )
{

if(!JComponentHelper::isEnabled('com_jcomments', true))
{
    $comJcomments = false;
    JError::raiseWarning('Komponentenfehler', JText::_('Die Komponente JComments ist nicht installiert'));
}
else
	{
	$comJcomments = true;   
    $nbcols++;
	$nbcols_header++;

$dispatcher = JDispatcher::getInstance();
$comments = '';
if(file_exists(JPATH_ROOT.'/components/com_jcomments/classes/config.php'))
		{
			require_once JPATH_ROOT.'/components/com_jcomments/classes/config.php';
			require_once JPATH_ROOT.'/components/com_jcomments/jcomments.class.php';
			require_once JPATH_ROOT.'/components/com_jcomments/models/jcomments.php';
		}

/**
 * load sportsmanagement comments plugin files
 */
		JPluginHelper::importPlugin('content','sportsmanagement_comments');

/**
 * get sportsmanagement comments plugin params
 */
		$plugin = JPluginHelper::getPlugin('content', 'sportsmanagement_comments');

	if (is_object($plugin)) {
		$pluginParams = new Registry($plugin->params);
	}
	else {
		$pluginParams = new Registry('');
	}
	$separate_comments 	= $pluginParams->get( 'separate_comments', 0 );

	
    }
}
?>
<div class="row-fluid">   
<table class="<?PHP echo $this->config['table_class']; ?> <?PHP echo $this->config['table_class_responsive']; ?>">
	<?php
	foreach( $dates as $date => $games )
	{
    
    	?>
	<!-- DATE HEADER -->
    <thead>
	<tr >

		<?php
        $timestamp = strtotime($date);
		if ( ($this->config['show_attendance_column']) || ($this->config['show_comments_count'] > 0) )
		{
			?>
			<th colspan="<?php echo $nbcols-$nbcols_header; ?>">
            <?php 
            if ( !$timestamp )
    {
        echo '';
    }
    else
    {
            echo JHtml::date( $date, JText::_('COM_SPORTSMANAGEMENT_RESULTS_GAMES_DATE_DAY'));
            }
                if ($this->config['show_matchday_dateheader']) 
                {
                    echo ' - ' . JText::sprintf( 'COM_SPORTSMANAGEMENT_RESULTS_GAMEDAY_NB',$this->roundcode ); 
                    } 
                    ?>
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
			<th colspan="<?php echo $nbcols; ?>">
            <?php 
            if ( !$timestamp )
    {
        echo '';
    }
    else
    {
            echo JHtml::date( $date, JTExt::_('COM_SPORTSMANAGEMENT_RESULTS_GAMES_DATE_DAY'));
            }
                if ($this->config['show_matchday_dateheader']) 
                {
                    echo ' - ' . JText::sprintf( 'COM_SPORTSMANAGEMENT_RESULTS_GAMEDAY_NB',$this->roundcode ); 
                    } 
                    ?>
            </th>
		<?php
		}
		?>
	</tr>
    </thead>
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
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = $this->project->slug;
$routeparameter['mid'] = $game->slug;
$report_link = sportsmanagementHelperRoute::getSportsmanagementRoute('matchreport',$routeparameter); 
				
			}
			else
			{
			 $routeparameter = array();
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = $this->project->slug;
$routeparameter['mid'] = $game->slug;
$report_link = sportsmanagementHelperRoute::getSportsmanagementRoute('nextmatch',$routeparameter);
				
			}

			$events	= sportsmanagementModelProject::getMatchEvents($game->id,0,0,JFactory::getApplication()->input->getInt('cfg_which_database',0));
			$subs	= sportsmanagementModelProject::getMatchSubstitutions($game->id,JFactory::getApplication()->input->getInt('cfg_which_database',0));

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

	<tr	class=""<?php echo $favStyle; ?>>
		<?php
		if ($this->config['show_match_number'])
		{
			?>
		<!-- show matchnumber -->
		<td width="" class=""><?php
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
		<td width="" class=""><?php
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
		?>
        </td>
		<?php
		}
    
    // diddipoeler    
    if ($this->config['show_match_summary'])
		{
        $imgTitle = $team1->name;
        $imgTitle .= ' - '.$team2->name;
        $imgsummary = 'media/com_sportsmanagement/jl_images/discuss.gif';
        $imgcontent = 'media/com_sportsmanagement/jl_images/information.png';
		  ?>
		<td width="" class="">
        <?PHP
if ( $game->content_id )
{
echo sportsmanagementHelperHtml::getBootstrapModalImage('match_content'.$game->id,
$imgcontent,
$imgTitle,
'20',
JURI::base().'index.php?tmpl=component&option=com_content&view=article&id='.$game->content_id,
$this->modalwidth,
$this->modalheight,
$this->overallconfig['use_jquery_modal']);                
}
        ?>
            </td>
		<?php 	  
		}  
		?>
		<!-- show divisions -->
		<?php
		if($this->config['show_division']) {
			echo '<td width="" class="" nowrap="nowrap">';
			echo sportsmanagementHelperHtml::showDivisonRemark(	$this->teams[$game->projectteam1_id],
			$this->teams[$game->projectteam2_id],
			$this->config,$game->division_id );
			echo '</td>';
		}
		if ($this->config['show_time'])
		{
			?>
		<!-- show matchtime -->
		<td width='' class=''><abbr title='' class='dtstart'> <?php echo sportsmanagementHelperHtml::showMatchTime($game, $this->config, $this->overallconfig, $this->project); ?>
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
			<?php sportsmanagementHelperHtml::showMatchPlayground($match,$this->config); ?>
		</td>
		<?php
		}
//--------------------------------------------------------------------------------------------------------------
		if ($this->config['result_style']==0)
		{
	
    switch ($this->config['show_logo_small'])
    {
    case 0:
    case 1:
    case 2:
    case 5:
    case 6:
    case 7:
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
				<?php 
                echo sportsmanagementViewResults::getTeamClubIcon($team1,
                $this->config['show_logo_small'],
                array('class' => 'teamlogo'),
                $this->modalwidth,
$this->modalheight,
$this->overallconfig['use_jquery_modal']); 
                ?>
			</td>
			<td>
				<?php
					$isFavTeam = in_array($team1->id, $this->favteams);
					echo sportsmanagementHelper::formatTeamName($team1,'g'.$game->id,$this->config,$isFavTeam,NULL,JFactory::getApplication()->input->getInt('cfg_which_database',0) );
				?>
			</td>
			<td width='<?PHP echo $width;?>'>
				<?php 
                echo sportsmanagementViewResults::getTeamClubIcon($team2, $this->config['show_logo_small'], array('class' => 'teamlogo'),
                $this->modalwidth,
$this->modalheight,
$this->overallconfig['use_jquery_modal']); 
                ?>
			</td>
			<td>
				<?php
					$isFavTeam = in_array($team2->id, $this->favteams);
					echo sportsmanagementHelper::formatTeamName($team2,'g'.$game->id,$this->config,$isFavTeam,NULL,JFactory::getApplication()->input->getInt('cfg_which_database',0));
				?>
			</td>
			<!-- show match score -->
			<td width='' class='score'>
				<?php 
        echo sportsmanagementViewResults::formatResult($this->teams[$game->projectteam1_id],$this->teams[$game->projectteam2_id],$game,$report_link,$this->config); 
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
			<td class=''>
				<?php
					$isFavTeam = in_array($team1->id, $this->favteams);
					echo sportsmanagementHelper::formatTeamName($team1,'g'.$game->id,$this->config,$isFavTeam);
				?>
			</td>
			<td width=''>
				<?php echo sportsmanagementViewResults::getTeamClubIcon($team1,
                 $this->config['show_logo_small'],
                  array('class' => 'teamlogo'),
                $this->modalwidth,
$this->modalheight,
$this->overallconfig['use_jquery_modal']); ?>
			</td>
			<!-- show match score -->
			<td width='' class='' nowrap='nowrap'>
				<?php
					echo '&nbsp;';
					echo sportsmanagementViewResults::formatResult($this->teams[$game->projectteam1_id],$this->teams[$game->projectteam2_id],$game,$report_link,$this->config);
					echo '&nbsp;';
				?>
			</td>
			<td width=''>
				<?php echo sportsmanagementViewResults::getTeamClubIcon($team2, $this->config['show_logo_small'], array('class' => 'teamlogo'),
                $this->modalwidth,
$this->modalheight,
$this->overallconfig['use_jquery_modal']); ?>
			</td>
			<td class=''>
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
			<td class=''>
				<?php
					$isFavTeam = in_array($team1->id, $this->favteams);
					echo sportsmanagementHelper::formatTeamName($team1,'g'.$game->id,$this->config,$isFavTeam);
				?>
			</td>
			<td width=''>
				<?php echo sportsmanagementViewResults::getTeamClubIcon($team1, $this->config['show_logo_small'], array('class' => 'teamlogo'),
                $this->modalwidth,
$this->modalheight,
$this->overallconfig['use_jquery_modal']); ?>
			</td>
			<td width=''>
			-
			</td>
			<td width=''>
				<?php echo sportsmanagementViewResults::getTeamClubIcon($team2, $this->config['show_logo_small'], array('class' => 'teamlogo'),
                $this->modalwidth,
$this->modalheight,
$this->overallconfig['use_jquery_modal']); ?>
			</td>
			<td class=''>
				<?php
					$isFavTeam = in_array($team2->id, $this->favteams);
					echo sportsmanagementHelper::formatTeamName($team2,'g'.$game->id,$this->config,$isFavTeam);
				?>
			</td>
			<!-- show match score -->
			<td width='' class='' nowrap='nowrap'>
				<?php
					echo '&nbsp;';
					echo sportsmanagementViewResults::formatResult($this->teams[$game->projectteam1_id],$this->teams[$game->projectteam2_id],$game,$report_link,$this->config);
					echo '&nbsp;';
				?>
			</td>
			<?php
		}
//--------------------------------------------------------------------------------------------------------------
	?>

		<!-- show hammer if there is a alternative decision of the score -->
		<td width="" class="">
		<?php sportsmanagementViewResults::showReportDecisionIcons($game); ?>
		</td>

		<?php
		if($this->config['show_referee'])
		{
			?>
		<!-- show matchreferees icon with tooltip -->
			<td width="" class="">
			<?php sportsmanagementViewResults::showMatchRefereesAsTooltip($game,$this->project,$this->config); ?>
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
			<?php sportsmanagementHelperHtml::showMatchPlayground($game,$this->config); ?>
			</td>
		<?php
		}
		?>

		<?php
		if ($this->config['show_attendance_column'])
		{
			?>
		<!-- show attendance -->
			<td class=""><?php
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
		if ($this->config['show_comments_count'] > 0 && $comJcomments )
		{
			?>
		<!-- show comments -->
		<td class=""><?php

			if ($separate_comments) {
				// Comments integration trigger when separate_comments in plugin is set to yes/1
				if (isset($game->team1_result))
				{
					$sportsmanagement_comments_object_group = 'com_sportsmanagement_matchreport';
				}
				else {
					$sportsmanagement_comments_object_group = 'com_sportsmanagement_nextmatch';
				}
			}
			else {
				// Comments integration trigger when separate_comments in plugin is set to no/0
				$sportsmanagement_comments_object_group = 'com_sportsmanagement';
			}

			$options 					= array();
			$options['object_id']		= (int) $game->id;
			$options['object_group']	= $sportsmanagement_comments_object_group;
			$options['published']		= 1;

			$count = JCommentsModel::getCommentsCount($options);

			if ($count == 1) 
            {
				$imgTitle = $count.' '.JText::_('COM_SPORTSMANAGEMENT_RESULTS_COMMENTS_COUNT_SINGULAR');
				if ($this->config['show_comments_count'] == 1) 
                {
					$href_text = JHtml::image( JURI::root().'media/com_sportsmanagement/jl_images/discuss_active.gif', $imgTitle, array(' title' => $imgTitle,' border' => 0,' style' => 'vertical-align: middle'));
				} 
                elseif ($this->config['show_comments_count'] == 2) 
                {
					$href_text = '<span title="'. $imgTitle .'">('.$count.')</span>';
				}
				//Link
				if (isset($game->team1_result))
				{
				$routeparameter = array();
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = $this->project->slug;
$routeparameter['mid'] = $game->slug;
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('matchreport',$routeparameter); 
					
				}
				else
				{
$routeparameter = array();
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = $this->project->slug;
$routeparameter['mid'] = $game->slug;
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('nextmatch',$routeparameter);
				}
				$viewComment = JHtml::link($link, $href_text);
				echo $viewComment;
			}
			elseif ($count > 1) 
            {
				$imgTitle = $count.' '.JText::_('COM_SPORTSMANAGEMENT_RESULTS_COMMENTS_COUNT_PLURAL');
				if ($this->config['show_comments_count'] == 1) 
                {
					$href_text = JHtml::image( JURI::root().'media/com_sportsmanagement/jl_images/discuss_active.gif', $imgTitle, array(' title' => $imgTitle,' border' => 0,' style' => 'vertical-align: middle'));
				} 
                elseif ($this->config['show_comments_count'] == 2) 
                {
					$href_text = '<span title="'. $imgTitle .'">('.$count.')</span>';
				}
				//Link
				if (isset($game->team1_result))
				{
				$routeparameter = array();
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = $this->project->slug;
$routeparameter['mid'] = $game->slug;
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('matchreport',$routeparameter);
					
				}
				else
				{
$routeparameter = array();
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = $this->project->slug;
$routeparameter['mid'] = $game->slug;
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('nextmatch',$routeparameter);
				}
				$viewComment = JHtml::link($link, $href_text);
				echo $viewComment;
			}
			else 
            {
				$imgTitle	= JText::_('COM_SPORTSMANAGEMENT_RESULTS_COMMENTS_COUNT_NOCOMMENT');
				if ($this->config['show_comments_count'] == 1) {
					$href_text		= JHtml::image( JURI::root().'media/com_sportsmanagement/jl_images/discuss.gif', $imgTitle, array(' title' => $imgTitle,' border' => 0,' style' => 'vertical-align: middle'));
				} elseif ($this->config['show_comments_count'] == 2) {
					$href_text		= '<span title="'. $imgTitle .'">('.$count.')</span>';
				}
				//Link
				if (isset($game->team1_result))
				{
				$routeparameter = array();
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = $this->project->slug;
$routeparameter['mid'] = $game->slug;
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('matchreport',$routeparameter);
					
				}
				else
				{
$routeparameter = array();
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = $this->project->slug;
$routeparameter['mid'] = $game->slug;
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('nextmatch',$routeparameter);					
				}
				$viewComment = JHtml::link($link, $href_text);
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
		<div id="info<?php echo $game->id; ?>" style="display: none;" class="resultsevents jsmeventsshowhide" >
		<table class='table' >
			<tr>
				<td><?php
				echo sportsmanagementViewResults::showEventsContainerInResults($game,$this->projectevents,$events,$subs,$this->config,$this->project);
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
</div>
<br />
