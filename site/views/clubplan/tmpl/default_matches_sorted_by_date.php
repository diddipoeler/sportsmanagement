<?php 
/** SportsManagement ein Programm zur Verwaltung f�r alle Sportarten
 * @version   1.0.05
 * @file      default_matches_sorted_by_date.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright Copyright: � 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage clubplan
 */

defined('_JEXEC') or die('Restricted access'); ?>

<!-- START: matches -->


	<?php
		//sort matches by dates
		$gamesByDate = Array();
		$pr_id = 0;
		$club_id = JFactory::getApplication()->input->getInt('cid') != -1 ? JFactory::getApplication()->input->getInt('cid') : false;

		$k=0;
		foreach ( $this->matches as $game )
		{
			$gamesByDate[substr( $game->match_date, 0, 10 )][] = $game;
							
		}
		
		foreach ( $gamesByDate as $date => $games )
		{
		
			
			foreach ( $games as $game )
			{
			
				//if date is the same dont show header
				if ( substr( $game->match_date, 0, 10 ) != $pr_id)
				{			

				?>
<table width="96%" align="center" border="0" cellpadding="0" cellspacing="0">				
					<tr class="sectiontableheader">
						<th colspan=16>
							<?php echo JHtml::date($game->match_date,JText::_('COM_SPORTSMANAGEMENT_CLUBPLAN_MATCHDATE'));?>
						</th>
					</tr>
				
				<h3><?php echo $game->roundname;?></h3>
				<?php
				$pr_id = substr( $game->match_date, 0, 10 );
				}			
			


$routeparameter = array();
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = $game->project_slug;
$routeparameter['r'] = $game->round_slug;
$routeparameter['division'] = 0;
$routeparameter['mode'] = 0;
$routeparameter['order'] = '';
$routeparameter['layout'] = '';
$result_link = sportsmanagementHelperRoute::getSportsmanagementRoute('results',$routeparameter);            
$routeparameter = array();
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = $game->project_slug;
$routeparameter['mid'] = $game->match_slug;
$nextmatch_link = sportsmanagementHelperRoute::getSportsmanagementRoute('nextmatch',$routeparameter);            
$routeparameter = array();
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = $game->project_slug;
$routeparameter['tid'] = $game->team1_slug;
$routeparameter['ptid'] = 0;
$teaminfo1_link = sportsmanagementHelperRoute::getSportsmanagementRoute('teaminfo',$routeparameter);            
$routeparameter['tid'] = $game->team2_slug;            
$teaminfo2_link = sportsmanagementHelperRoute::getSportsmanagementRoute('teaminfo',$routeparameter);            
$routeparameter = array();
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = $game->project_slug;
$routeparameter['tid'] = $game->team1_slug;
$teamstats1_link = sportsmanagementHelperRoute::getSportsmanagementRoute('teamstats',$routeparameter);
$routeparameter['tid'] = $game->team2_slug;
$teamstats2_link = sportsmanagementHelperRoute::getSportsmanagementRoute('teamstats',$routeparameter);
$routeparameter = array();
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = $game->project_slug;
$routeparameter['pgid'] = $game->playground_id;
$playground_link = sportsmanagementHelperRoute::getSportsmanagementRoute('playground',$routeparameter);            
		

			$hometeam				= $game;
			$awayteam				= $game;

			$isFavTeam				= false;
			$isFavTeam				= in_array($game->team1_id,$this->favteams);
			$hometeam->name			= $game->tname1;
			$hometeam->team_id		= $game->team1_id;
			$hometeam->id 			= $game->team1_id;
			$hometeam->short_name	= $game->tname1_short;
			$hometeam->middle_name	= $game->tname1_middle;
			$hometeam->project_id	= $game->prid;
			$hometeam->club_id		= $game->t1club_id;
			$hometeam->projectteamid = $game->projectteam1_id;
			$tname1 = sportsmanagementHelper::formatTeamName($hometeam,'clubplan',$this->config,$isFavTeam);

			$isFavTeam				= false;
			$isFavTeam				= in_array($game->team2_id,$this->favteams);
			$awayteam->name			= $game->tname2;
			$awayteam->team_id		= $game->team2_id;
			$awayteam->id 			= $game->team2_id;
			$awayteam->short_name	= $game->tname2_short;
			$awayteam->middle_name	= $game->tname2_middle;
			$awayteam->project_id	= $game->prid;
			$awayteam->club_id		= $game->t2club_id;
			$awayteam->projectteamid = $game->projectteam2_id;
			$tname2 = sportsmanagementHelper::formatTeamName($awayteam,'clubplan',$this->config,$isFavTeam);


			$favStyle = '';
			if ($this->config['highlight_fav'] == 1 && !$club_id) {
				$isFavTeam = in_array($game->team1_id,$this->favteams) ? 1 : in_array($game->team2_id, $this->favteams);
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
			<tr <?php echo $favStyle; ?>>
					
					
					<?php if ($this->config['show_match_nr']==1) { ?>
				<td>
					<?php echo $game->match_number ; ?>
				</td>
					<?php } ;?>
					
				<td nowrap="nowrap">
					<?php
					echo substr($game->match_date,11,5);
					?>
				</td>
					<?php if ($this->config['show_time_present']==1) { ?>
			   <td nowrap="nowrap">
					<?php
					echo $game->time_present;
					?>
				</td>
					<?php } ?>
					<?php if ($this->config['show_league']==1) { ?>							
			   <td>
					<?php
					echo $game->l_name;
					?>
				</td>
					<?php } ?>				
				<td class="td_r">
					<?php if ($this->config['which_link2']==0) { ?>
					<?php
					echo $tname1 ;
					}
					?>
					<?php if ($this->config['which_link2']==1) { ?>
					<?php
					echo JHtml::link($teaminfo1_link,$tname1);
					}
					?>
					<?php if ($this->config['which_link2']==2) { ?>
					<?php
					echo JHtml::link($teamstats1_link,$tname1);
					}
					?>
				</td>
					<?php if ($this->config['show_club_logo']==1) { ?>
				<td>
					<?php echo sportsmanagementModelClubPlan::getClubIconHtmlSimple($game->home_logo_small,1); ?>
				</td>
					<?php } ?>				
				<td>
					-
				</td>
					<?php if ($this->config['show_club_logo']==1) { ?>
				<td>
					<?php echo sportsmanagementModelClubPlan::getClubIconHtmlSimple($game->away_logo_small,1); ?>
				</td>
					<?php } ?>
				<td>
					<?php if ($this->config['which_link2']==0) { ?>
					<?php
					echo $tname2 ;
					}
					?>
					<?php if ($this->config['which_link2']==1) { ?>
					<?php
					echo JHtml::link($teaminfo2_link,$tname2);
					}
					?>
					<?php if ($this->config['which_link2']==2) { ?>
					<?php
					echo JHtml::link($teamstats2_link,$tname2);
					}
					?>
				</td>
					<?php if ($this->config['show_referee']==1) { ?>
				<td>
					<?php
					$matchReferees=&$this->model->getMatchReferees($game->id);
					foreach ($matchReferees AS $matchReferee)
					{
						$referee_link=sportsmanagementHelperRoute::getRefereeRoute($game->project_id,$matchReferee->id);
						echo JHtml::link($referee_link,$matchReferee->firstname." ".$matchReferee->lastname);
						echo '<br />';
					}
					?>
				</td>
					<?php } ;?>
					<?php if ($this->config['show_playground']==1) { ?>
				<td>
					<?php
					echo JHtml::link($playground_link,$game->pl_name);
					?>
				</td>
					<?php } ;?>
					<?php
					$score="";
					if (!$game->alt_decision)
					{
						$e1 =$game->team1_result;
						$e2 =$game->team2_result;
					}
					else
					{
						$e1 =(isset($game->team1_result_decision)) ? $game->team1_result_decision : 'X';
						$e2 =(isset($game->team2_result_decision)) ? $game->team2_result_decision : 'X';
					}
					
					if ($game->cancel==0) {
						$score .= '<td align="center">';
						$score .= $e1;
						$score .= '</td><td align="center">-</td><td align="center">';
						$score .= $e2;
					} else {
						$score .= '<td align="center" valign="top" colspan="3">'.$game->cancel_reason.'</td>';
					}
					echo $score;
					if ($this->config['show_thumbs_picture']==1) {
					   switch ($this->config['type_matches']) {
					   case 1 : // home matches
							$team1=$e1;
							$team2=$e2;
							break;
					   case 2 : // away matches
							$team2=$e1;
							$team1=$e2;
							break;
						default : // home+away matches, but take care of the select club from the menu item to have the icon correct displayed
							if ($game->club1_id == $club_id) {
								$team1=$e1;
								$team2=$e2;
							} else if ($game->club2_id == $club_id) {
								$team1=$e2;
								$team2=$e1;
							} else {
								$team1=$e1;
								$team2=$e2;
							}
					   }
						if(isset($team1) && isset($team2) && ($team1==$team2)) {
							echo '<td align="center" valign="middle">' .
							JHtml::image("media/com_sportsmanagement/jl_images/draw.png",
							"draw.png",
							array("title" => JText::_('COM_SPORTSMANAGEMENT_CLUBPLAN_MATCH_DRAW'))
							)."&nbsp;</td>";
						} else {
							if($team1 > $team2) {
								echo '<td align="center" valign="middle">' .
								JHtml::image("media/com_sportsmanagement/jl_images/thumbs_up.png",
								"thumbs_up.png",
								array("title" => JText::_('COM_SPORTSMANAGEMENT_CLUBPLAN_MATCH_WON'))
								)."&nbsp;</td>";
							} elseif($team2 > $team1) {
								echo '<td align="center" valign="middle">' .
								JHtml::image("media/com_sportsmanagement/jl_images/thumbs_down.png",
								"thumbs_down.png",
								array("title" => JText::_('COM_SPORTSMANAGEMENT_CLUBPLAN_MATCH_LOST'))
								)."&nbsp;</td>";
							}
							else
							{
								echo "<td>&nbsp;</td>";
							}
						}
					}
					?>
				</tr>
			<?php
			$k=1 - $k;
			}
		}
		?>
</table>
<br />
<!-- END: matches -->