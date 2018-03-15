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
* OHNE JEDE GEWÄHELEISTUNG, bereitgestellt; sogar ohne die implizite
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


		<!-- Alt decision table START -->
			<fieldset class="adminform">
				<legend><?php echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_AD' );?>
				</legend>
				<table class='admintable'>
					<?php 
                    
                    foreach ($this->form->getFieldset('altdecision') as $field):
                    ?>
					<tr>
						<td class="key"><?php echo $field->label; ?></td>
						<td><?php echo $field->input; ?></td>
					</tr>					
					<?php endforeach; ?>
					<tr>
						<td class="key"><?php echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_AD_SUB_DEC' );?></td>
						<td colspan="2">
							<select	name="alt_decision" id="alt_decision">
								<option	value="0"<?php if ( $this->match->alt_decision == 0 ){echo ' selected="selected"'; } ?>>
									<?php echo JText::_('JNO');?>
								</option>
								<option	value="1"<?php if ($this->match->alt_decision==1) echo ' selected="selected"' ?>>
									<?php echo JText::_( 'JYES' );?>
								</option>
							</select>
						</td>
					</tr>
					<tr>
						<td colspan="3">
							<div id="alt_decision_enter" style="display:<?php echo ( $this->match->alt_decision == 0 ) ? 'none' : 'block'; ?>">
								<table class='adminForm' cellpadding='0' cellspacing='7' border='0'>
									<tr>
										<td class="key"><?php echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_AD_NEW_SCORE' ).' ' .$this->match->hometeam; ?></td>
										<td>
											<input	type="text" class="inputbox" id="team1_result_decision" name="team1_result_decision"
													size="3"
													value="<?php if ($this->match->alt_decision == 1) if (isset($this->match->team1_result_decision)) echo $this->match->team1_result_decision; else echo 'X'; ?>" <?php if ($this->match->alt_decision == 0) echo 'DISABLED '; ?>/>
										</td>
									</tr>
									<tr>
										<td class="key"><?php echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_AD_NEW_SCORE' ).' ' .$this->match->awayteam;?></td>
										<td>
											<input	type="text" class="inputbox" id="team2_result_decision" name="team2_result_decision"
													size="3" value="<?php
													if ( $this->match->alt_decision == 1 ) if ( isset( $this->match->team2_result_decision ) ) echo $this->match->team2_result_decision; else echo 'X'; ?>" <?php
													if ( $this->match->alt_decision == 0 ) echo 'DISABLED '; ?>/>
										</td>
									</tr>
									<tr>
										<td class="key"><?php echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_AD_REASON_NEW_SCORE' );?></td>
										<?php
										if ( is_null( $this->match->team1_result ) or ( $this->match->alt_decision == 0 ) )
										{
											$disinfo = 'DISABLED ';
										}
										?>
										<td>
											<input	type="text" class="inputbox" id="decision_info" name="decision_info" size="30"
													value="<?php if ( $this->match->alt_decision == 1 ){echo $this->match->decision_info;}?>" <?php
													if ( $this->match->alt_decision == 0 ){echo 'DISABLED ';} ?>/>
										</td>
									</tr>
									<tr>
										<td class="key"><?php echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_AD_TEAM_WON' );?></td>
										<td><?php echo $this->lists['team_won']; ?></td>
									</tr>
								</table>
							</div>
						</td>
					</tr>
				</table>
			</fieldset>