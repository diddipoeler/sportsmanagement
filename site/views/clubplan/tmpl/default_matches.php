<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage clubplan
 * @file       default_matches.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;

?>
<!-- START: matches -->
<div class="<?php echo $this->divclassrow;?> table-responsive" id="clubplanmatches">
<table class="<?php echo $this->config['table_class']; ?>">
<?php
if ($this->config['type_matches'] != 0) {
?>
	<tr class="sectiontableheader">
		<?php if ( $this->config['show_matchday'] ) { ?>
		<th><?php echo Text::_('COM_SPORTSMANAGEMENT_CLUBPLAN_MATCHDAY'); ?></th>
		<?php } ;?>
		<?php if ( $this->config['show_match_nr'] ) { ?>
		<th><?php echo Text::_('COM_SPORTSMANAGEMENT_CLUBPLAN_MATCH_NR'); ?></th>
		<?php } ;?>		
		<?php if ( $this->config['show_match_date'] ) { ?>
		<th><?php echo Text::_('COM_SPORTSMANAGEMENT_CLUBPLAN_DATE');?></th>
		<?php } ;?>
		<?php if ( $this->config['show_match_time'] ) { ?>
		<th><?php echo Text::_('COM_SPORTSMANAGEMENT_CLUBPLAN_TIME'); ?></th>
		<?php } ;?>
		<?php if ( $this->config['show_time_present'] ) { ?>
		<th><?php echo Text::_('COM_SPORTSMANAGEMENT_CLUBPLAN_TIME_PRESENT'); ?></th>
		<?php } ;?>
		<?php if ( $this->config['show_league'] ) { ?>		
		<th><?php echo Text::_('COM_SPORTSMANAGEMENT_CLUBPLAN_LEAGUE'); ?></th>
		<?php } ;?>		
		<?php if ( $this->config['show_club_logo'] ) { ?>
		<th></th>
		<?php } ?>
		<th>&nbsp;</th>
		<th>&nbsp;</th>
		<?php if ( $this->config['show_club_logo'] ) { ?>
		<th>&nbsp;</th>
		<?php } ?>
		<th>&nbsp;</th>
		<?php if ( $this->config['show_referee'] ) { ?>
		<th><?php echo Text::_('COM_SPORTSMANAGEMENT_CLUBPLAN_REFEREE'); ?></th>
		<?php } ;?>
		<?php if ( $this->config['show_playground'] ) { ?>
		<th><?php echo Text::_('COM_SPORTSMANAGEMENT_CLUBPLAN_PLAYGROUND'); ?></th>
		<?php } ;?>
		<th colspan=3 align="center"><?php echo Text::_('COM_SPORTSMANAGEMENT_CLUBPLAN_RESULT'); ?></th>
		<?php if ( $this->config['show_thumbs_picture'] ) { ?>
		<th align="center">&nbsp;</th>
		<?php } ;?>
	</tr>
<?php
}
		$k   = 0;
		$cnt = 0;
		$club_id = Factory::getApplication()->input->getInt('cid') != -1 ? Factory::getApplication()->input->getInt('cid') : false;
		$prevDate = '';
		foreach ($this->matches as $game)
		{
			if ($this->config['type_matches'] == 0) {
			   $gameDate = strftime("%Y-%m-%d",strtotime($game->match_date));
			   if ($gameDate != $prevDate) {
				?>
					<tr class="sectiontableheader">
						<th colspan="16">
							<?php echo HTMLHelper::date($game->match_date, Text::_('COM_SPORTSMANAGEMENT_CLUBPLAN_MATCHDATE'));?>
						</th>
					</tr>
				<?php
					$prevDate = $gameDate;
				}
			}

$routeparameter = array();
$routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = Factory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = $game->project_slug;
$routeparameter['r'] = $game->round_slug;
$routeparameter['division'] = 0;
$routeparameter['mode'] = 0;
$routeparameter['order'] = '';
$routeparameter['layout'] = '';
$result_link = sportsmanagementHelperRoute::getSportsmanagementRoute('results',$routeparameter);          
$routeparameter = array();
$routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = Factory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = $game->project_slug;
$routeparameter['mid'] = $game->match_slug;
$nextmatch_link = sportsmanagementHelperRoute::getSportsmanagementRoute('nextmatch',$routeparameter);          
$routeparameter = array();
$routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = Factory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = $game->project_slug;
$routeparameter['tid'] = $game->team1_slug;
$routeparameter['ptid'] = $game->projectteam1_slug;
$teaminfo1_link = sportsmanagementHelperRoute::getSportsmanagementRoute('teaminfo',$routeparameter);          
$routeparameter['tid'] = $game->team2_slug;  
$routeparameter['ptid'] = $game->projectteam2_slug;      
$teaminfo2_link = sportsmanagementHelperRoute::getSportsmanagementRoute('teaminfo',$routeparameter);          
$routeparameter = array();
$routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = Factory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = $game->project_slug;
$routeparameter['tid'] = $game->team1_slug;
$teamstats1_link = sportsmanagementHelperRoute::getSportsmanagementRoute('teamstats',$routeparameter);
$routeparameter['tid'] = $game->team2_slug;
$teamstats2_link = sportsmanagementHelperRoute::getSportsmanagementRoute('teamstats',$routeparameter);
$routeparameter = array();
$routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = Factory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = $game->project_slug;
$routeparameter['pgid'] = $game->playground_id;
$playground_link = sportsmanagementHelperRoute::getSportsmanagementRoute('playground',$routeparameter);
          
			$favs = sportsmanagementHelper::getProjectFavTeams($game->project_id);
			$favteams = explode(",",$favs->fav_team);

			if ($this->config['which_link2']==1) {
				$link1 = $teaminfo1_link;
				$link2 = $teaminfo2_link;
			} else if ($this->config['which_link2']==2) {
				$link1 = $teamstats1_link;
				$link2 = $teamstats2_link;
			} else {
				$link1 = null;
				$link2 = null;
			}
			$hometeam               = $game;
			$awayteam               = $game;
			
			$isFavTeam              = false;
			$isFavTeam              = in_array($game->team1_id,$favteams);
            $hometeam->projectteam_slug = $game->projectteam1_slug;
			$hometeam->name         = $game->tname1;
			$hometeam->team_id      = $game->team1_id;
			$hometeam->id           = $game->team1_id;
			$hometeam->short_name   = $game->tname1_short;
			$hometeam->middle_name  = $game->tname1_middle;
			$hometeam->project_id   = $game->prid;
			$hometeam->club_id      = $game->t1club_id;
			$hometeam->projectteamid = $game->projectteam1_id;
			$hometeam->club_slug    = $game->club1_slug;
			$hometeam->team_slug    = $game->team1_slug;
			$tname1 = sportsmanagementHelper::formatTeamName($hometeam,'clubplanhome'.$cnt++,$this->config,$isFavTeam, $link1);
			
			$isFavTeam              = false;
			$isFavTeam              = in_array($game->team2_id,$favteams);
            $awayteam->projectteam_slug = $game->projectteam2_slug;
			$awayteam->name         = $game->tname2;
			$awayteam->team_id      = $game->team2_id;
			$awayteam->id           = $game->team2_id;
			$awayteam->short_name   = $game->tname2_short;
			$awayteam->middle_name  = $game->tname2_middle;
			$awayteam->project_id   = $game->prid;
			$awayteam->club_id      = $game->t2club_id;
			$awayteam->projectteamid = $game->projectteam2_id;
			$awayteam->club_slug    = $game->club2_slug;
			$awayteam->team_slug    = $game->team2_slug;
			$tname2 = sportsmanagementHelper::formatTeamName($awayteam,'clubplanaway'.$cnt++,$this->config,$isFavTeam, $link2);

			$favStyle = '';
			if ($this->config['highlight_fav'] == 1 && !$club_id) {
				$isFavTeam = in_array($game->team1_id,$favteams) || in_array($game->team2_id, $favteams);
				if ( $isFavTeam && $favs->fav_team_highlight_type == 1 )
				{
					if( trim( $favs->fav_team_color ) != "" )
					{
						$color = trim($favs->fav_team_color);
					}
					$format = "%s";
					$favStyle = ' style="';
					$favStyle .= ($favs->fav_team_text_bold != '') ? 'font-weight:bold;' : '';
					$favStyle .= (trim($favs->fav_team_text_color) != '') ? 'color:'.trim($favs->fav_team_text_color).';' : '';
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
			<tr class=""<?php echo $favStyle; ?>>
					<?php if ( $this->config['show_matchday'] ) { ?>
				<td>
					<?php if ($this->config['which_link']==0) { ?>
					<?php
					echo $game->roundcode ;
					}
					?>
					<?php if ($this->config['which_link']==1) { ?>
					<?php
					echo HTMLHelper::link($result_link,$game->roundcode);
					}
					?>
					<?php if ($this->config['which_link']==2) { ?>
					<?php
					echo HTMLHelper::link($nextmatch_link,$game->roundcode);
					}
					?>
				</td>
					<?php } ;?>
					
					<?php if ( $this->config['show_match_nr'] ) { ?>
				<td>
					<?php echo $game->match_number ; ?>
				</td>
					<?php } ;?>
				
				<?php if ( $this->config['show_match_date'] ) { ?>
				<td>
					<?php
					echo HTMLHelper::date($game->match_date, Text::_('COM_SPORTSMANAGEMENT_CLUBPLAN_MATCHDATE'));
					?>
				</td>
					<?php } ;?>
					
				<?php if ( $this->config['show_match_time'] ) { ?>
				<td nowrap="nowrap">
					<?php
					echo sportsmanagementHelperHtml::showMatchTime($game, $this->config, $this->overallconfig, $this->project);
					?>
				</td>
					<?php } ;?>
					
				<?php if ( $this->config['show_time_present'] ) { ?>
				<td nowrap="nowrap">
					<?php
					echo $game->time_present;
					?>
				</td>
					<?php } ?>
					
				<?php if ( $this->config['show_league'] ) { ?>							
				<td>
					<?php echo $game->l_name; ?>
				</td>
					<?php } ?>				
				<td class="td_r">
					<?php
						echo $tname1;
					?>
				</td>
					<?php if ( $this->config['show_club_logo'] )
                    {
					   $picture = 'home_'.$this->config['team_picture'];
                       ?>
				<td>
                <?PHP
                echo sportsmanagementHelperHtml::getBootstrapModalImage('clubplan'.$game->match_id.'-'.$game->team1_id,
                $game->$picture,
                $game->tname1,
                '20',
                '',
$this->modalwidth,
$this->modalheight,
$this->overallconfig['use_jquery_modal']);
                ?>
              
                </td>
					<?php
                    }
                    ?>				
				<td>
					-
				</td>
					<?php
                    if ( $this->config['show_club_logo'] )
                    {
					   $picture = 'away_'.$this->config['team_picture'];
                       ?>
				<td>
                <?PHP
                echo sportsmanagementHelperHtml::getBootstrapModalImage('clubplan'.$game->match_id.'-'.$game->team2_id,
                $game->$picture,
                $game->tname2,
                '20',
                '',
$this->modalwidth,
$this->modalheight,
$this->overallconfig['use_jquery_modal']);
                ?>
                </td>
					<?php
                    }
                    ?>
				<td>
					<?php
						echo $tname2;
					?>
				</td>
					<?php if ( $this->config['show_referee'] ) { ?>
				<td>
					<?php
					$matchReferees = $this->model->getMatchReferees($game->match_id);
					foreach ($matchReferees AS $matchReferee)
					{
$routeparameter = array();
$routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = Factory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = $game->project_id;
$routeparameter['pid'] = $matchReferee->id;
$referee_link = sportsmanagementHelperRoute::getSportsmanagementRoute('referee',$routeparameter);						
//$referee_link = sportsmanagementHelperRoute::getRefereeRoute($game->project_id,$matchReferee->id);
						echo HTMLHelper::link($referee_link,$matchReferee->firstname." ".$matchReferee->lastname);
						echo '<br />';
					}
					?>
				</td>
					<?php } ;?>
					<?php if ( $this->config['show_playground'] ) { ?>
				<td>
					<?php
					echo HTMLHelper::link($playground_link,$game->pl_name);
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
					if ( $this->config['show_thumbs_picture'] ) {
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
							HTMLHelper::image("media/com_sportsmanagement/jl_images/draw.png",
							"draw.png",
							array("title" => Text::_('COM_SPORTSMANAGEMENT_CLUBPLAN_MATCH_DRAW'))
							)."&nbsp;</td>";
						} else {
							if($team1 > $team2) {
								echo '<td align="center" valign="middle">' .
								HTMLHelper::image("media/com_sportsmanagement/jl_images/thumbs_up.png",
								"thumbs_up.png",
								array("title" => Text::_('COM_SPORTSMANAGEMENT_CLUBPLAN_MATCH_WON'))
								)."&nbsp;</td>";
							} elseif($team2 > $team1) {
								echo '<td align="center" valign="middle">' .
								HTMLHelper::image("media/com_sportsmanagement/jl_images/thumbs_down.png",
								"thumbs_down.png",
								array("title" => Text::_('COM_SPORTSMANAGEMENT_CLUBPLAN_MATCH_LOST'))
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
		} ;
		?>
</table>
</div>
<!-- END: matches -->
