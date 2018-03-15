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

defined('_JEXEC') or die('Restricted access');
?>
	
		
			<legend><?php echo JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_TITLE','<i>'.$this->projectws->name.'</i>'); ?></legend>
			<form name='copyform' method='post' style='display:inline' id='copyform'>
				<input type='hidden' name='match_date' value='<?php echo $this->roundws->round_date_first.' '.$this->projectws->start_time; ?>' />
				<input type='hidden' name='round_id' value='<?php echo $this->roundws->id; ?>' />
				<input type='hidden' name='project_id' value='<?php echo $this->roundws->project_id; ?>' />
				<input type='hidden' name='act' value='rounds' />
				<input type='hidden' name='task' value='match.copyfrom' />
				<input type='hidden' name='addtype' value='0' id='addtype' />
				<input type='hidden' name='add_match_count' value='0' id='addmatchescount' />
				<?php echo JHtml::_('form.token')."\n"; ?>
				<table class="<?php echo $this->table_data_class; ?>">
					<thead>
						<tr>
							<th class="nowrap"><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_MULTI'); ?></th>
							<th class="nowrap"><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_COPY'); ?></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td valign='top' width='50%'>
								<table class="admintable">
									<tr>
										<td class="key"><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_TYPE'); ?></td>
										<td><?php echo $this->lists['createTypes']; ?></td>
									</tr>
									<tr>
										<td colspan='2' >
											<div id='massadd_standard' style='display:block;'>
												<table>
													<tr>
														<td width="100" align="right" class="key"><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_NR'); ?></td>
														<td>
															<input type='text' name='tempaddmatchescount' id='tempaddmatchescount' value='0' size='3' class='inputbox' />
														</td>
													</tr>
													<tr>
														<td width="100" align="right" class="key"><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_START_HERE'); ?></td>
														<td><?php echo $this->lists['addToRound']; ?></td>
													</tr>
													<tr>
														<td width="100" align="right" class="key"><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_AUTO_PUBL'); ?></td>
														<td><?php echo $this->lists['autoPublish']; ?></td>
													</tr>
													<tr>
														<td width="100" align="right" class="key"><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_FIRST_MATCHNR'); ?></td>
														<td><input type='text' name='firstMatchNumber' size='4' value='' /></td>
													</tr>
													<tr>
														<td width="100" align="right" class="key"><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_STARTTIME'); ?></td>
														<td>
															<?php
															echo JHtml::calendar(	sportsmanagementHelper::convertDate($this->roundws->round_date_first),
																					'match_date','match_date',
																					'%d-%m-%Y','size="10" ');
                                                           ?>
                                                            &nbsp;
															<input type='text' name='startTime' value='<?php echo $this->projectws->start_time; ?>' size='4' maxlength='5' class='inputbox' />
														</td>
													</tr>
													<tr>
														<td width="100" colspan='2'>
															<input type='submit' value='<?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_NEW_MATCHES'); ?>' onclick='return addmatches();' />
														</td>
													</tr>
												</table>
											</div>
											<div id='massadd_type2' style='display:none;'>
											</div>
										</td>
									</tr>
								</table>
							</td>
							<td valign='top'>
								<table class="admintable">
									<tr>
										<td width="100" align="right" class="key"><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_COPY2'); ?></td>
										<td><?php echo $this->lists['project_rounds2']; ?></td>
									</tr>
									<tr>
										<td width="100" align="right" class="key"><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_DEFAULT_DATE'); ?></td>
										<td>
											<?php
											echo JHtml::calendar(	sportsmanagementHelper::convertDate($this->roundws->round_date_first),
																	'date','date',
																	'%d-%m-%Y','size="10" ');
											?>
											&nbsp;
											<input type='text' name='time' value='<?php echo $this->projectws->start_time; ?>' size='4' maxlength='5' class='inputbox' />
										</td>
									</tr>
									<tr>
										<td width="100" align="right" class="key"><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_FIRST_MATCHNR'); ?></td>
										<td><input type="text" name="start_match_number" size="4" value="" /></td>
									</tr>
									<tr>
										<td width="100" align="right" class="key">										
												<?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_CREATE_NEW'); ?>
										</td>
										<td><input type="checkbox" name="create_new" value="1" class="inputbox" checked="checked" /></td>
									</tr>
									<tr>
										<td width="100" align="right" class="key">
												<?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_COPY_MIRROR'); ?>
										</td>
										<td>
											<select name="mirror" class="inputbox">
												<option value="0" selected="selected"><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_COPY_MATCHES'); ?></option>
												<option value="1"><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_MIRROR_HA'); ?></option>
											</select>
										</td>
									</tr>
									<tr>
										<td width="100" colspan='2'>																	
											<input type='submit' value='<?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_COPY_MATCHES'); ?>' onclick='copymatches();' />
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</tbody>
				</table>
			</form>

	
