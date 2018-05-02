<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_plan.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage teamplan
 */

defined('_JEXEC') or die('Restricted access');

if ($this->config['show_comments_count'] == 1 || $this->config['show_comments_count'] == 2)
{
	require_once (JPATH_ROOT . '/components/com_sportsmanagement/jcomments.class.php');
	require_once (JPATH_ROOT . '/components/com_sportsmanagement/jcomments.config.php');
	require_once (JPATH_ROOT . '/components/com_sportsmanagement/models/jcomments.php');

	// get joomleague comments plugin params
	JPluginHelper::importPlugin( 'joomleague' );

	$plugin				= & JPluginHelper::getPlugin('joomleague', 'comments');

	$pluginParams = is_object($plugin) ? new JParameter($plugin->params) : new JParameter('');
	$separate_comments 	= $pluginParams->get( 'separate_comments', 0 );
}
?>
<!-- <a name="jl_top" id="jl_top"></a> -->
<?php
if (!empty($this->matches))
{
$teamid = JFactory::getApplication()->input->getInt('tid');
$nbcols = 0;
?>
<div class="table-responsive">   
<table class="<?php echo $this->config['table_class']; ?>">
	<thead>
	<tr >
		<?php
		if ($this->config['show_events'])
		{
			?>
		<th>&nbsp;</th>
		<?php
			$nbcols++;
		}
		?>

		<?php
		if ($this->config['show_matchday'])
		{
			?>
		<th><?php echo JText::_('COM_SPORTSMANAGEMENT_TEAMPLAN_MATCHDAY'); ?></th>
		<?php
			$nbcols++;
		}
		?>

		<?php
		if ($this->config['show_match_number'])
		{
			?>
		<th><?php echo '&nbsp;'.JText::_('NUM').'&nbsp;'; ?></th>
		<?php
			$nbcols++;
		}
		?>

		<?php
		if (($this->project->project_type=='DIVISIONS_LEAGUE') && ($this->config['show_division']) )
		{
			?>
		<th><?php echo JText::_('COM_SPORTSMANAGEMENT_TEAMPLAN_DIVISION'); ?></th>
		<?php
			$nbcols++;
		}
		?>

		<?php
		if (($this->config['show_playground'] || $this->config['show_playground_alert']))
		{
			?>
		<th><?php echo JText::_('COM_SPORTSMANAGEMENT_TEAMPLAN_PLAYGROUND'); ?></th>
		<?php
			$nbcols++;
		}
		?>

		<?php
		if ($this->config['show_date'])
		{
			?>
		<th><?php echo JText::_('COM_SPORTSMANAGEMENT_TEAMPLAN_EDIT_DATE'); ?></th>
		<?php
			$nbcols++;
		}
		?>

		<?php
		if ($this->config['show_time'])
		{
			?>
		<th><?php echo JText::_('COM_SPORTSMANAGEMENT_TEAMPLAN_EDIT_TIME'); ?></th>
		<?php
			$nbcols++;
		}
		?>

		<?php
		if ($this->config['show_time_present'])
		{
			?>
		<th><?php echo JText::_('COM_SPORTSMANAGEMENT_TEAMPLAN_TIME_PRESENT'); ?></th>
		<?php
			$nbcols++;
		}

		switch ($this->config['result_style'])
		{
			case 1 :

				// Show home team marker
				echo '<th class="right">';
				if ($this->config['show_home_guest_team_marker'] && !$this->config['switch_home_guest'])
				{
					echo JText::_('COM_SPORTSMANAGEMENT_TEAMPLAN_HOME_TEAM');
				}
				elseif ($this->config['show_home_guest_team_marker'] && $this->config['switch_home_guest'])
				{
					echo JText::_('COM_SPORTSMANAGEMENT_TEAMPLAN_AWAY_TEAM');
				}
				$nbcols++;
				echo '</th>';

				// Create space for logo home team
				if ( $this->config['show_logo_small'] )
				{
					echo '<th class="right">&nbsp;</th>';
					$nbcols++;
				}

				// Create room for the score to be displayed
				echo '<th>'.JText::_('COM_SPORTSMANAGEMENT_TEAMPLAN_RESULT').'</th>';
				$nbcols++;

				// Create space for logo guest team
				if ( $this->config['show_logo_small'] )
				{
					echo '<th class="left">&nbsp;</th>';
					$nbcols++;
				}

				// Show guest team marker
				echo '<th class="left">';
				if ($this->config['show_home_guest_team_marker'] && !$this->config['switch_home_guest'])
				{
					echo JText::_('COM_SPORTSMANAGEMENT_TEAMPLAN_AWAY_TEAM');
				}
				elseif ($this->config['show_home_guest_team_marker'] && $this->config['switch_home_guest'])
				{
					echo JText::_('COM_SPORTSMANAGEMENT_TEAMPLAN_HOME_TEAM');
				}
				$nbcols++;
				echo '</th>';
				break;
			default :
			case 0 :

				// Show home team marker
				echo '<th class="right">';
				if ($this->config['show_home_guest_team_marker'] && !$this->config['switch_home_guest'])
				{
					echo JText::_('COM_SPORTSMANAGEMENT_TEAMPLAN_HOME_TEAM');
				}
				elseif ($this->config['show_home_guest_team_marker'] && $this->config['switch_home_guest'])
				{
					echo JText::_('COM_SPORTSMANAGEMENT_TEAMPLAN_AWAY_TEAM');
				}
				$nbcols++;
				echo '</th>';

				// Create space for logo home team
				if ($this->config['show_logo_small'])
				{
					echo '<th class="right">&nbsp;</th>';
					$nbcols++;
				}

				echo '<th>&nbsp;</th>';
				$nbcols++;

				// Create space for logo guest team
				if ($this->config['show_logo_small'])
				{
					echo '<th class="left">&nbsp;</th>';
					$nbcols++;
				}

				echo '<th class="left">';
				// Show guest team marker
				if ($this->config['show_home_guest_team_marker'] && !$this->config['switch_home_guest'])
				{
					echo JText::_('COM_SPORTSMANAGEMENT_TEAMPLAN_AWAY_TEAM');
				}
				elseif ($this->config['show_home_guest_team_marker'] && $this->config['switch_home_guest'])
				{
					echo JText::_('COM_SPORTSMANAGEMENT_TEAMPLAN_HOME_TEAM');
				}
				$nbcols++;
				echo '</th>';

				echo '<th class="center">'.JText::_('COM_SPORTSMANAGEMENT_TEAMPLAN_RESULT').'</th>';
				$nbcols++;
				break;
		}
		?>

		<?php
		if ( $this->config['show_referee'] )
		{
			?>
		<th><?php echo JText::_('COM_SPORTSMANAGEMENT_TEAMPLAN_REFEREE'); ?></th>
		<?php
			$nbcols++;
		}
		?>

		<?php
		if ( $this->config['show_thumbs_picture'] & ($teamid>0))
		{
			?>
		<th>&nbsp;</th>
		<?php
			$nbcols++;
		}
		?>

		<?php
		if ( $this->config['show_matchreport_column'])
		{
			?>
		<th><?php echo JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_PAGE_TITLE'); ?></th>
		<?php
			$nbcols++;
		}
		?>

		<?php
		if ( $this->config['show_attendance_column'])
		{
			?>
		<th><?php echo JText::_('COM_SPORTSMANAGEMENT_TEAMPLAN_ATTENDANCE'); ?></th>
		<?php
			$nbcols++;
		}
		?>

		<?php
		if ($this->config['show_comments_count'] == 1 || $this->config['show_comments_count'] == 2)
		{
			?>
		<th><?php echo JText::_('COM_SPORTSMANAGEMENT_TEAMPLAN_COMMENTS'); ?></th>
		<?php
			$nbcols++;
		}
		?>
	</tr>
	</thead>
	<?php

	$k		= 0;
	$counter	= 1;
	$round_date	= '';

	foreach( $this->matches as $match )
	{
		$hometeam = $this->teams[$match->projectteam1_id];
		$home_projectteam_id = $hometeam->projectteamid;

		$guestteam = $this->teams[$match->projectteam2_id];
		$guest_projectteam_id = $guestteam->projectteamid;

		if ( $match->team1 == $this->favteams )
		{
			$highlight = "highlight";
		}
		else
		{
			$highlight = '';
		}

		if (!empty($this->ptid))
		{
			$home = $hometeam->name;
			if ($match->team1==$this->ptid)
			{
				$home = sprintf('%s',$hometeam->name);
			}
		}
		else
		{
			$home = $hometeam->name;
			if ($match->team2)
			{
				$home = sprintf('%s',$hometeam->name);
			}
		}

		if (!empty($this->ptid))
		{
			$away = $guestteam->name;
			if ($match->team2==$this->ptid)
			{
				$away = sprintf('%s',$guestteam->name);
			}
		}
		else
		{
			$away = $guestteam->name;
			if ($match->team2)
			{
				$away = sprintf('%s',$guestteam->name);
			}
		}

		$homeclub = $hometeam->club_id;
		$awayclub = $guestteam->club_id;

		$favStyle = '';
		if ( $this->config['highlight_fav'] && !$teamid ) {
			$isFavTeam = in_array($hometeam->id,$this->favteams) ? 1 : in_array($guestteam->id, $this->favteams);
			if ( $isFavTeam && $this->project->fav_team_highlight_type == 1 )
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
		}

		?>
	<tr class="<?php echo $highlight; ?>"<?php echo $favStyle; ?>>
                <?php

/**
 * start events
 */

		if ($this->config['show_events'])
		{
			?>
		<td width='5' class='ko'>
		<?php
 			$events	= sportsmanagementModelProject::getMatchEvents($match->id,0,0,JFactory::getApplication()->input->getInt('cfg_which_database',0));
 			$subs	= sportsmanagementModelProject::getMatchSubstitutions($match->id,JFactory::getApplication()->input->getInt('cfg_which_database',0));

			if ($this->config['use_tabs_events']) {
			    $hasEvents = (count($events) + count($subs) > 0 && $this->config['show_events']);
			} else {
			 
/**
 * no subs are shown when not using tabs for displaying events so don't check for that
 */             
			    $hasEvents = (count($events) > 0 && $this->config['show_events']);
			}

			if ($hasEvents)
			{
				$link = "javascript:void(0);";
				$img = JHtml::image('media/com_sportsmanagement/jl_images/events.png', 'events.png');
				$params = array("title"   => JText::_('COM_SPORTSMANAGEMENT_TEAMPLAN_EVENTS'),
								"onclick" => 'switchMenu(\'info'.$match->id.'\');return false;');
				echo JHtml::link($link,$img,$params);
			}
		?>
		</td>
		<?php
		}
		else
		{
			$hasEvents = false;
		}
		// end events
		?>

		<?php
		if ($this->config['show_matchday'])
		{
		?>
			<td width='5%'>
			<?php
$routeparameter = array();
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = $this->project->slug;
$routeparameter['r'] = $match->round_slug;
$routeparameter['division'] = $match->division_slug;
$routeparameter['mode'] = 0;
$routeparameter['order'] = '';
$routeparameter['layout'] = '';
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('results',$routeparameter);
			
			echo JHtml::link($link,$match->roundcode);
			?>
			</td>
		<?php
		}
		?>

		<?php
		if ($this->config['show_match_number'])
		{
		?>
		    <td>
			<?php if (empty($match->match_number)){$match->match_number='-';} echo $match->match_number; ?>
		    </td>
		<?php
		}
		if (($this->project->project_type=='DIVISIONS_LEAGUE') && ($this->config['show_division']))
		{
		?>
		    <td>
			<?php echo sportsmanagementHelperHtml::showDivisonRemark($hometeam,$guestteam,$this->config,$match->division_slug); ?>
		    </td>
		<?php
		}
		if (($this->config['show_playground'] || $this->config['show_playground_alert']))
		{
		?>
		    <td>
			<?php sportsmanagementHelperHtml::showMatchPlayground($match,$this->config); ?>
		    </td>
		<?php
		}
		
		?>

		<?php
		if ($this->config['show_date'])
		{
			?>
			<td width="10%"><?php
			if (!strstr($match->match_date,"0000-00-00"))
			{
				echo JHtml::date($match->match_date, JText::_('COM_SPORTSMANAGEMENT_GLOBAL_CALENDAR_DATE'));
			}
			else
			{
				echo "&nbsp;";
			}
			?>
			</td>
		<?php
		}
		?>

		<?php
		if ($this->config['show_time'])
		{
			?>
		<td width="10%"><?php echo sportsmanagementHelperHtml::showMatchTime(	$match,
		$this->config,
		$this->overallconfig,
		$this->project); ?></td>
		<?php
		}
		?>

		<?php
		if ($this->config['show_time_present'])
		{
			?>
		<td width='10%'><?php if (empty($match->time_present)){$match->time_present='-';} echo $match->time_present; ?></td>
		<?php
		}
		?>

		<?php

/**
 * Define some variables which will be used
 */
 
		$teamA	= '';
		$teamB	= '';
		$score	= "";

/**
 * Check if the home and guest team should be switched arround
 */

		if ($this->config['switch_home_guest']) {
			$class1 = 'left';
			$class2 = 'right';
		} else {
			$class1	= 'right';
			$class2	= 'left';
		}
		if ($this->config['show_teamplan_link']) 
        {
            $routeparameter = array();
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = $this->project->slug;
$routeparameter['tid'] = $hometeam->team_slug;
$routeparameter['division'] = $match->division_slug;
$routeparameter['mode'] = 0;
$routeparameter['ptid'] = 0;
$homelink = sportsmanagementHelperRoute::getSportsmanagementRoute('teamplan',$routeparameter);
$routeparameter['tid'] = $guestteam->team_slug;
$routeparameter['division'] = $match->division_slug;
$routeparameter['mode'] = 0;
$routeparameter['ptid'] = 0;
$awaylink = sportsmanagementHelperRoute::getSportsmanagementRoute('teamplan',$routeparameter);

		} else {
			$homelink = null;
			$awaylink = null;
		}
		$isFavTeam = in_array($hometeam->id,$this->favteams);
		$home = sportsmanagementHelper::formatTeamName($hometeam, "g".$match->id."t".$hometeam->id, $this->config, $isFavTeam, $homelink);

		$teamA .= '<td class="'.$class1.'">'.$home.'</td>';

/**
 * Check if the user wants to show the club logo or country flag
 */
 
		switch ($this->config['show_logo_small'])
		{
			case 1 :
				{
					$teamA .= '<td class="'.$class1.'">';
					$teamA .= ' '.sportsmanagementModelProject::getClubIconHtml($hometeam,
						1,
						0,
						'logo_small',
						JFactory::getApplication()->input->getInt('cfg_which_database',0),
						$match->id,
						$this->modalwidth,
						$this->modalheight,
						$this->overallconfig['use_jquery_modal']);
					$teamA .= '</td>';

					$teamB .= '<td class="'.$class2.'">';
					$teamB .= sportsmanagementModelProject::getClubIconHtml($guestteam,
						1,
						0,
						'logo_small',
						JFactory::getApplication()->input->getInt('cfg_which_database',0),
						$match->id,
						$this->modalwidth,
						$this->modalheight,
						$this->overallconfig['use_jquery_modal']).' ';
					$teamB .= '</td>';
				}
				break;
                
            case 5 :
            {
					$teamA .= '<td class="'.$class1.'">';
					$teamA .= ' '.sportsmanagementModelProject::getClubIconHtml($hometeam,
						1,
						0,
						'logo_middle',
						JFactory::getApplication()->input->getInt('cfg_which_database',0),
						$match->id,
						$this->modalwidth,
						$this->modalheight,
						$this->overallconfig['use_jquery_modal']);
					$teamA .= '</td>';

					$teamB .= '<td class="'.$class2.'">';
					$teamB .= sportsmanagementModelProject::getClubIconHtml($guestteam,
						1,
						0,
						'logo_middle',
						JFactory::getApplication()->input->getInt('cfg_which_database',0),
						$match->id,
						$this->modalwidth,
						$this->modalheight,
						$this->overallconfig['use_jquery_modal']).' ';
					$teamB .= '</td>';
				}
            break;
            
            case 6 :
            {
					$teamA .= '<td class="'.$class1.'">';
					$teamA .= ' '.sportsmanagementModelProject::getClubIconHtml($hometeam,
						1,
						0,
						'logo_big',
						JFactory::getApplication()->input->getInt('cfg_which_database',0),
						$match->id,
						$this->modalwidth,
						$this->modalheight,
						$this->overallconfig['use_jquery_modal']);
					$teamA .= '</td>';

					$teamB .= '<td class="'.$class2.'">';
					$teamB .= sportsmanagementModelProject::getClubIconHtml($guestteam,
						1,
						0,
						'logo_big',
						JFactory::getApplication()->input->getInt('cfg_which_database',0),
						$match->id,
						$this->modalwidth,
						$this->modalheight,
						$this->overallconfig['use_jquery_modal']).' ';
					$teamB .= '</td>';
				}
            break;

			case 2 :
				{
					$teamA .= '<td class="'.$class1.'">';
					$teamA .= JSMCountries::getCountryFlag($hometeam->country);
					$teamA .= '</td>';

					$teamB .= '<td class="'.$class2.'">';
					$teamB .= JSMCountries::getCountryFlag($guestteam->country);
					$teamB .= '</td>';
				}
				break;

			case 3:
				{
					$teamA .= '<td class="'.$class1.'">';
					$teamA .= sportsmanagementHelper::getPictureThumb($hometeam->picture,
										$hometeam->name,
										$this->config['team_picture_width'],
										'auto',1);

					$teamA .= '</td>';

					$teamB .= '<td class="'.$class2.'">';
					$teamB .= sportsmanagementHelper::getPictureThumb($guestteam->picture,
										$guestteam->name,
										$this->config['team_picture_width'],
										'auto',1);
					$teamB .= '</td>';
				}
				break;
		}

		$seperator ='<td width="10">'.$this->config['seperator'].'</td>';

		$isFavTeam = in_array($guestteam->id, $this->favteams);
		$away = sportsmanagementHelper::formatTeamName($guestteam,"g".$match->id."t".$guestteam->id,$this->config, $isFavTeam, $awaylink);
		
		$teamB .= '<td class="'.$class2.'">'.$away.'</td>';

		if (!$match->cancel)
		{

/**
 * In case show_part_results is true, then first check if the part results are available;
 * 'No part results available' occurs when teamX_result_split ONLY consists of zero or more ";"
 * (zero for projects with a single playing period, one or more for projects with two or more playing periods)
 */
            
            $team1_result_split_present = preg_match('/^;*$/', $match->team1_result_split) == 0;
            $team2_result_split_present = preg_match('/^;*$/', $match->team2_result_split) == 0;

            if ($this->config['switch_home_guest'])
                {
                    $result = $match->team2_result.'&nbsp;'.$this->config['seperator'].'&nbsp;'.$match->team1_result;

                    $part_results_left = explode(";", $match->team2_result_split);
                    $part_results_right = explode(";", $match->team1_result_split);

                    $leftResultOT	= $match->team2_result_ot;
                    $rightResultOT	= $match->team1_result_ot;
                    $leftResultSO	= $match->team2_result_so;
                    $rightResultSO	= $match->team1_result_so;
                    $leftResultDEC	= $match->team2_result_decision;
                    $rightResultDEC	= $match->team1_result_decision;
                }
                else
                {
                    $result = $match->team1_result.'&nbsp;'.$this->config['seperator'].'&nbsp;'.$match->team2_result;

                    $part_results_left = explode(";", $match->team1_result_split);
                    $part_results_right = explode(";", $match->team2_result_split);

                    $rightResultOT	= $match->team2_result_ot;
                    $leftResultOT	= $match->team1_result_ot;
                    $rightResultSO	= $match->team2_result_so;
                    $leftResultSO	= $match->team1_result_so;
                    $rightResultDEC	= $match->team2_result_decision;
                    $leftResultDEC	= $match->team1_result_decision;
                }

            $SOTresult = '';
            $SOTtolltip = '';

            switch ($match->match_result_type)
            {
                case 2 :
                    {
                        if ($this->config['result_style']==1){
                            $result .= '<br />';
                        } else {
                            $result .= ' ';
                        }
                        $result .= '('.JText::_('COM_SPORTSMANAGEMENT_RESULTS_SHOOTOUT');
                        $result .= ')';

                        if (isset($leftResultOT))
                            {
                                        $OTresultS = $leftResultOT . '&nbsp;' . $this->config['seperator'] . '&nbsp;' . $rightResultOT;
                                        $SOTresult .= '<br /><span class="hasTip" title="' . JText::_('COM_SPORTSMANAGEMENT_RESULTS_OVERTIME2') .'::' . $OTresultS . '" >' . $OTresultS . '</span>';
                                        $SOTtolltip = ' | ' . $OTresultS;
                            }
                        if (isset($leftResultSO))
                            {
                                        $SOresultS = $leftResultSO . '&nbsp;' . $this->config['seperator'] . '&nbsp;' . $rightResultSO;
                                        $SOTresult .= '<br /><span class="hasTip" title="' . JText::_('COM_SPORTSMANAGEMENT_RESULTS_SHOOTOUT2') .'::' . $SOresultS . '" >' . $SOresultS . '</span>';
                                        $SOTtolltip = ' | ' . $SOresultS;
                            }
                    }
                    break;

                case 1 :
                    {
                        if ($this->config['result_style']==1){
                            $result .= '<br />';
                        }else{
                            $result .= ' ';
                        }

                        $result .= '('.JText::_('COM_SPORTSMANAGEMENT_RESULTS_OVERTIME');
                        $result .= ')';

                        if (isset($leftResultOT))
                            {
                                        $OTresultS = $leftResultOT . '&nbsp;' . $this->config['seperator'] . '&nbsp;' . $rightResultOT;
                                        $SOTresult .= '<br /><span class="hasTip" title="' . JText::_('COM_SPORTSMANAGEMENT_RESULTS_OVERTIME2') .'::' . $OTresultS . '" >' . $OTresultS . '</span>';
                                        $SOTtolltip = ' | ' . $OTresultS ;
                            }
                    }
                    break;
            }

            //Link
$routeparameter = array();                    
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = $this->project->slug;
$routeparameter['mid'] = $match->match_slug;            
            if (isset($match->team1_result))
                {
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('matchreport',$routeparameter);
                    
            } 
            else 
            {
                    $link = sportsmanagementHelperRoute::getSportsmanagementRoute('nextmatch',$routeparameter);
                }

            $ResultsTooltipTitle = $result;
            $result = JHtml::link($link,$result);

            $ResultsTooltipTp = '( ';
            $PartResult = '';

            if ($team1_result_split_present && $team2_result_split_present)
            {
            //Part results
            if (!is_array($part_results_left))  { $part_results_left = array($part_results_left); }
            if (!is_array($part_results_right)) { $part_results_right = array($part_results_right); }

            for ($i = 0; $i < count($part_results_left); $i++)
            {
                if (isset($part_results_left[$i]))
                    {
                                $resultS = $part_results_left[$i] . '&nbsp;' . $this->config['seperator'] . '&nbsp;' . $part_results_right[$i];
                                $whichPeriod = $i + 1;
                                $PartResult .= '<br /><span class="hasTip" title="' . JText::sprintf( 'COM_SPORTSMANAGEMENT_NPART',  "$whichPeriod")  .'::' . $resultS . '" >' . $resultS . '</span>';
                                if ($i != 0) {
                                $ResultsTooltipTp .= ' | ' . $resultS;
                                } else {
                                $ResultsTooltipTp .= $resultS;
                                }
                    }
            }
            }

            $ResultsTooltipTp .= $SOTtolltip . ' )';

            if ($team1_result_split_present && $team2_result_split_present)
            {
                if ($this->config['show_part_results'])
                    {
                        $result .= $PartResult . $SOTresult;
                    }
                else
                    {
                        //No need to show a tooltip if the parts are shown anyways
                        $result = '<span class="hasTip" title="' .$ResultsTooltipTitle . '::' . $ResultsTooltipTp . '" >' . $result . '</span>';
                    }
            }

            if ($match->alt_decision)
            {
                $result='<b style="color:red;">';
                $result .= $leftResultDEC.'&nbsp;'.$this->config['seperator'].'&nbsp;'.$rightResultDEC;
                $result .= '</b>';

            }

			$score = "<td class='center'>".$result.'</td>';
		}
		else
		{
			$score='<td>'.JText::_($match->cancel_reason).'</td>';
		}

		switch ($this->config['result_style'])
		{
			case 1 :
				{
					if ($this->config['switch_home_guest'])
					{
						echo $teamB.$score.$teamA;
					}
					else
					{
						echo $teamA.$score.$teamB;
					}
				}
				break;

			default;
			case 0 :
				{
					if ($this->config['switch_home_guest'])
					{
						echo $teamB.$seperator.$teamA.$score;
					}
					else
					{
						echo $teamA.$seperator.$teamB.$score;
					}
				}
				break;
		}
		?>

		<?php
		if ($this->config['show_referee'])
		{
			?>
		<td><?php
		if ((isset($match->referees)) && (count($match->referees)>0))
		{
			if ($this->project->teams_as_referees)
			{
				$output='';
				$toolTipTitle=JText::_('COM_SPORTSMANAGEMENT_TEAMPLAN_REF_TOOLTIP');
				$toolTipText='';

				for ($i=0; $i<count($match->referees); $i++)
				{
					if ($match->referees[$i]->referee_name != '')
					{
						$output .= $match->referees[$i]->referee_name;
						$toolTipText .= $match->referees[$i]->referee_name.'&lt;br /&gt;';
					}
					else
					{
						$output .= '-';
						$toolTipText .= '-&lt;br /&gt;';
					}
				}
				if ($this->config['show_referee']==1)
				{
					echo $output;
				}
				elseif ($this->config['show_referee']==2)
				{
				?>
					<span class='hasTip' title='<?php echo $toolTipTitle; ?> :: <?php echo $toolTipText; ?>'>
					<img src='<?php echo JURI::root(); ?>media/com_sportsmanagement/jl_images/icon-16-Referees.png' alt='' title='' /> </span>
				<?php
				}
			}
			else
			{
				$output='';
				$toolTipTitle=JText::_('COM_SPORTSMANAGEMENT_TEAMPLAN_REF_TOOLTIP');
				$toolTipText='';

				for ($i=0; $i<count($match->referees); $i++)
				{
					if ($match->referees[$i]->referee_lastname != '' && $match->referees[$i]->referee_firstname)
					{
						$output .= '<span class="hasTip" title="'.JText::_('COM_SPORTSMANAGEMENT_TEAMPLAN_REF_FUNCTION').'::'.$match->referees[$i]->referee_position_name.'">';
						$ref=$match->referees[$i]->referee_lastname. ','.$match->referees[$i]->referee_firstname;
						$toolTipText .= $ref.' ('.$match->referees[$i]->referee_position_name.')'.'&lt;br /&gt;';
						if ($this->config['show_referee_link'])
						{
							$link=sportsmanagementHelperRoute::getRefereeRoute($this->project->slug,$match->referees[$i]->referee_id,3);
							$ref=JHtml::link($link,$ref);
						}
						$output .= $ref;
						$output .= '</span>';

						if (($i + 1) < count($match->referees))
						{
							$output .= ' - ';
						}
					}
					else
					{
						$output .= '-';
					}
				}

				if ($this->config['show_referee']==1)
				{
					echo $output;
				}
				elseif ($this->config['show_referee']==2)
				{
					?> <span class='hasTip'
			title='<?php echo $toolTipTitle; ?> :: <?php echo $toolTipText; ?>'>
		<img
			src='<?php echo JURI::root(); ?>media/com_sportsmanagement/jl_images/icon-16-Referees.png'
			alt='' title='' /> </span> <?php
				}
			}
		}
		else
		{
			echo '-';
		}
		?></td>
		<?php
		}
		?>

		<?php if (($this->config['show_thumbs_picture']) & ($teamid>0)): ?>
		<td><?php echo sportsmanagementHelperHtml::getThumbUpDownImg($match, $this->ptid); ?></td>
		<?php endif; ?>

		<?php
		if ($this->config['show_matchreport_column'])
		{
			?>
		<td><?php
		if (!$match->cancel) {
            //$link=sportsmanagementHelperRoute::getNextMatchRoute($this->project->slug,$match->id);
            if (isset($match->team1_result))
			{
				if ($this->config['show_matchreport_image']) {
					$href_text = JHtml::image($this->config['matchreport_image'], JText::_('COM_SPORTSMANAGEMENT_TEAMPLAN_VIEW_MATCHREPORT'));
				} else {
					$href_text = JText::_('COM_SPORTSMANAGEMENT_TEAMPLAN_VIEW_MATCHREPORT');
				}
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = $this->project->slug;
$routeparameter['mid'] = $match->id;
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('matchreport',$routeparameter);				
				$viewReport = JHtml::link($link, $href_text);
				echo $viewReport;
			}
			else
			{
				if ($this->config['show_matchreport_image']) {
					$href_text = JHtml::image($this->config['matchpreview_image'], JText::_('COM_SPORTSMANAGEMENT_TEAMPLAN_VIEW_MATCHPREVIEW'));
				} else {
					$href_text = JText::_('COM_SPORTSMANAGEMENT_TEAMPLAN_VIEW_MATCHPREVIEW');
				}
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = $this->project->slug;
$routeparameter['mid'] = $match->id;
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('nextmatch',$routeparameter);
				$viewPreview = JHtml::link($link, $href_text);
				echo $viewPreview;
			}
		}
		?></td>
		<?php
		}
		?>

		<?php
		if ($this->config['show_attendance_column'])
		{
			?>
		<td class="center"><?php if ($match->crowd == 0){$match->crowd='';} echo $match->crowd; ?></td>
		<?php
		}
		?>

		<?php
		if ($this->config['show_comments_count'] == 1 || $this->config['show_comments_count'] == 2)
		{
			?>
		<td class="center"><?php


			if ($separate_comments) {
				// Comments integration trigger when separate_comments in plugin is set to yes/1
				if (isset($match->team1_result))
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
			$options['object_id']		= (int) $match->id;
			$options['object_group']	= $joomleage_comments_object_group;
			$options['published']		= 1;

			$count = JCommentsModel::getCommentsCount($options);

			if ($count == 1) {
				$imgTitle		= $count.' '.JText::_('COM_SPORTSMANAGEMENT_TEAMPLAN_COMMENTS_COUNT_SINGULAR');
				if ($this->config['show_comments_count'] == 1) {
					$href_text		= JHtml::image( JURI::root().'media/com_sportsmanagement/jl_images/discuss_active.gif', $imgTitle, array(' title' => $imgTitle,' border' => 0,' style' => 'vertical-align: middle'));
				} elseif ($this->config['show_comments_count'] == 2) {
					$href_text		= '<span title="'. $imgTitle .'">('.$count.')</span>';
				}
				//Link
	            if (isset($match->team1_result))
	            {
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = $this->project->slug;
$routeparameter['mid'] = $match->id;
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('matchreport',$routeparameter);
					
	            } else {
					$link=sportsmanagementHelperRoute::getNextMatchRoute($this->project->slug,$match->id).'#comments';
	            }
				$viewComment	= JHtml::link($link, $href_text);
				echo $viewComment;
			}
			elseif ($count > 1) {
				$imgTitle	= $count.' '.JText::_('COM_SPORTSMANAGEMENT_TEAMPLAN_COMMENTS_COUNT_PLURAL');
				if ($this->config['show_comments_count'] == 1) {
					$href_text		= JHtml::image( JURI::root().'media/com_sportsmanagement/jl_images/discuss_active.gif', $imgTitle, array(' title' => $imgTitle,' border' => 0,' style' => 'vertical-align: middle'));
				} elseif ($this->config['show_comments_count'] == 2) {
					$href_text		= '<span title="'. $imgTitle .'">('.$count.')</span>';
				}
				//Link
	            if (isset($match->team1_result))
	            {
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = $this->project->slug;
$routeparameter['mid'] = $match->id;
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('matchreport',$routeparameter);
					
	            } else {
					$link=sportsmanagementHelperRoute::getNextMatchRoute($this->project->slug,$match->id).'#comments';
	            }
				$viewComment	= JHtml::link($link, $href_text);
				echo $viewComment;
			}
			else {
				$imgTitle	= JText::_('COM_SPORTSMANAGEMENT_TEAMPLAN_COMMENTS_COUNT_NOCOMMENT');
				if ($this->config['show_comments_count'] == 1) {
					$href_text		= JHtml::image( JURI::root().'media/com_sportsmanagement/jl_images/discuss.gif', $imgTitle, array(' title' => $imgTitle,' border' => 0,' style' => 'vertical-align: middle'));
				} elseif ($this->config['show_comments_count'] == 2) {
					$href_text		= '<span title="'. $imgTitle .'">('.$count.')</span>';
				}
				//Link
	            if (isset($match->team1_result))
	            {
	            $routeparameter = array();
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = $this->project->slug;
$routeparameter['mid'] = $match->id;
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('matchreport',$routeparameter);
					
	            } else {
					$link=sportsmanagementHelperRoute::getNextMatchRoute($this->project->slug,$match->id).'#comments';
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
	<!-- Show icon for editing events in edit mode -->
	<tr class="events <?php echo ($k == 0) ? '' : 'alt'; ?>">
		<td colspan="<?php echo $nbcols; ?>">
		<div id="info<?php echo $match->id; ?>" style="display: none;">
		<table class='matchreport' border='0'>
			<tr>
				<td><?php
				echo $this->showEventsContainerInResults(
												$match,
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
	$counter++;
	}
	?>
</table>
</div>
	<?php
}
else
{
	?>
<h3><?php echo JText::_('COM_SPORTSMANAGEMENT_TEAMPLAN_NO_MATCHES'); ?></h3>
	<?php
}
?>
