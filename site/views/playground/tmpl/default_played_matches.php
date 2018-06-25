<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_played_matches.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage playground
 */
defined( '_JEXEC' ) or die( 'Restricted access' ); 
?>

<?php

if ( $this->playedgames )
{
	?>
	<!-- Playground played games -->
<h2><?php echo JText::_('COM_SPORTSMANAGEMENT_PLAYGROUND_PREV_GAMES'); ?></h2>
		<div class="row-fluid">
					<table class="<?php echo $this->config['matches_table_class']; ?>" >
						<?php
						//sort games by dates
						$gamesByDate = Array();
						foreach ( $this->playedgames as $game )
						{
							$gamesByDate[substr( $game->match_date, 0, 10 )][] = $game;
						}
						$colspan = 5;
						if ( $this->config['show_logo'] ) 
                        {
							$colspan = 7;
						}
						foreach ( $gamesByDate as $date => $games )
						{
							?>
							<tr>
								<td align="left" colspan="<?php echo $colspan; ?>" class="">
									<?php
									echo JHtml::date($date, JText::_('COM_SPORTSMANAGEMENT_GLOBAL_MATCHDAYDATE'));
									?>
								</td>
							</tr>
							<?php
							foreach ( $games as $game )
							{
								$home = $this->playedgamesteams[$game->team1];
								$away = $this->playedgamesteams[$game->team2];
								?>
								<tr class="">
									<td>
										<?php
										echo substr( $game->match_date, 11, 5 );
										?>
									</td>
									<td class="nowrap">
										<?php
										echo $game->project_name;
										?>
									</td>
									<?php
									if ( $this->config['show_logo'] ) {
										$home_logo = sportsmanagementModelteam::getTeamLogo($home->id,$this->config['show_logo_small']);
										$away_logo = sportsmanagementModelteam::getTeamLogo($away->id,$this->config['show_logo_small']);
										$teamA = '<td align="right" valign="top" class="nowrap">';
										$teamA .= " " . sportsmanagementModelProject::getClubIconHtml( $home_logo[0], 1 );
										$teamA .= '</td>';
										echo $teamA;
									}
									?>
									<td class="nowrap">
										<?php
										echo $home->name;
										?>
									</td>
									<td class="nowrap">
                                    <?php echo $game->team1_result; ?>
                                    -
                                    <?php echo $game->team2_result; ?>
                                    </td>
									<?php
									if ( $this->config['show_logo'] ) {
										$teamB = '<td align="right" valign="top" class="nowrap">';
										$teamB .= " " . sportsmanagementModelProject::getClubIconHtml( $away_logo[0], 1 );
										$teamB .= '</td>';
										echo $teamB;
									}
									?>
									<td class="nowrap">
										<?php
										echo $away->name;
										?>
									</td>
								</tr>
								<?php
							}
						}
						?>
					</table>
			</div>
	
	<!-- End of playground played games -->
	<?php
}
?>
