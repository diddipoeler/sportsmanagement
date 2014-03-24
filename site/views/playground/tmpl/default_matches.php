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

defined( '_JEXEC' ) or die( 'Restricted access' ); ?>

<?php

if ( $this->games )
#if (1==1)
{
	?>
	<!-- Playground next games -->
	<div id="jlg_plgndnextgames">

<h2><?php echo JText::_('COM_SPORTSMANAGEMENT_PLAYGROUND_NEXT_GAMES'); ?></h2>
		<div class="venuecontent map">
					<table width="96%" align="center" border="0" cellpadding="0" cellspacing="0">
						<?php
						//sort games by dates
						$gamesByDate = Array();
						foreach ( $this->games as $game )
						{
							$gamesByDate[substr( $game->match_date, 0, 10 )][] = $game;
						}
						// $teams = $this->project->getTeamsFromMatches( $this->games );

						$colspan = 5;
						if ($this->config['show_logo'] == 1) 
                        {
							$colspan = 7;
						}

						foreach ( $gamesByDate as $date => $games )
						{
							?>
							<tr>
								<td align="left" colspan="<?php echo $colspan; ?>" class="sectiontableheader">
									<?php
									echo JHtml::date($date, JText::_('COM_SPORTSMANAGEMENT_GLOBAL_MATCHDAYDATE'));
									?>
								</td>
							</tr>
							<?php
							foreach ( $games as $game )
							{
								$home = $this->gamesteams[$game->team1];
								$away = $this->gamesteams[$game->team2];
								?>
								<tr class="sectiontableentry1">
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
									if ($this->config['show_logo'] == 1) {
										//$model = $this->getModel();
										$home_logo = sportsmanagementModelteam::getTeamLogo($home->id);
										$away_logo = sportsmanagementModelteam::getTeamLogo($away->id);
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
									<td class="nowrap">-</td>
									<?php
									if ($this->config['show_logo'] == 1) {
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
	</div>
	<!-- End of playground next games -->
	<?php
}
?>