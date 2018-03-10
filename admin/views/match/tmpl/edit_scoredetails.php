<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
* @copyright        Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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
* SportsManagement ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es nützlich sein wird, aber
* OHNE JEDE GEWÄHRLEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/


defined( '_JEXEC' ) or die( 'Restricted access' );
?>			
		<fieldset class="adminform">
			<legend>
				<?php
				echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_SD' );
				?>
			</legend>
			<br/>
			<table class='admintable'>
				<tr>

					<th>
						<?php
						echo $this->match->hometeam;
						?>
					</th>
					<th>
						&nbsp;
					</th>
					<th align="left">
						<?php
						echo $this->match->awayteam;
						?>
					</th>
				</tr>
				<!-- Header team names END -->
				<!-- match legs -->
				<?php
				if ( $this->projectws->use_legs == 1 )
				{
					?>
					<tr>
						<td>
							<?php
							if ( $this->table_config['alternative_legs'] == '' )
							{
								echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_SD_SETS' );
							}
							else
							{
								echo $this->table_config['alternative_legs'];
							}
							?>:
						</td>
						<td>
							<input	type="text" name="team1_legs" value="<?php echo $this->match->team1_legs; ?>" size="3"
									tabindex="100" class="inputbox" />
						</td>
						<td align="center">:</td>
						<td>
							<input	type="text" name="team2_legs" value="<?php echo $this->match->team2_legs; ?>" size="3"
									tabindex="101" class="inputbox" />
						</td>
					</tr>
					<?php
				}
				?>
				<!-- END match legs -->
                
				<!-- Bonus points -->
				<tr>
					<td class="key">
						<label>
							<?php
							echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_SD_BONUS' );
							?>
						</label>
					</td>
					<td>
						<input	type="text" name="team1_bonus" value="<?php echo $this->match->team1_bonus;?>" size="3" class="inputbox" />
					</td>
					<td align="center">:</td>
					<td>
						<input	type="text" name="team2_bonus" value="<?php echo $this->match->team2_bonus;?>" size="3" class="inputbox" />
					</td>
				</tr>
				<!-- Bonus points END -->

			<!-- Score Table END -->
			<!-- Additional Details Table START -->
				<!-- Result notice -->
				<tr>
					<td class="key">
						<label for="match_result_detail">
							<?php
							echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_SD_SCORE_NOTICE' );
							?>
						</label>
					</td>
					<td colspan='3'>
						<input	type="text" name="match_result_detail" value="<?php echo $this->match->match_result_detail; ?>" size="40"
								class="inputbox" />
					</td>
				</tr>
				<!-- Result notice END -->

			</table>
		</fieldset>