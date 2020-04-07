<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       defaul_massadd.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage matches
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
?>
  

				  <legend><?php echo Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_TITLE', '<i>' . $this->projectws->name . '</i>'); ?></legend>
			<form name='copyform' method='post' style='display:inline' id='copyform'>
				<input type='hidden' name='match_date' value='<?php echo $this->roundws->round_date_first . ' ' . $this->projectws->start_time; ?>' />
				<input type='hidden' name='round_id' value='<?php echo $this->roundws->id; ?>' />
				<input type='hidden' name='project_id' value='<?php echo $this->roundws->project_id; ?>' />
				<input type='hidden' name='act' value='rounds' />
				<input type='hidden' name='task' value='match.copyfrom' />
				<input type='hidden' name='addtype' value='0' id='addtype' />
				<input type='hidden' name='add_match_count' value='0' id='addmatchescount' />
				<?php echo HTMLHelper::_('form.token') . "\n"; ?>
				<table class="<?php echo $this->table_data_class; ?>">
					<thead>
						<tr>
							<th class="nowrap"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_MULTI'); ?></th>
							<th class="nowrap"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_COPY'); ?></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td valign='top' width='50%'>
								<table class="table">
									<tr>
										<td class="key"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_TYPE'); ?></td>
										<td><?php echo $this->lists['createTypes']; ?></td>
									</tr>
									<tr>
										<td colspan='2' >
											<div id='massadd_standard' style='display:block;'>
												<table>
													<tr>
														<td width="100" align="right" class="key"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_NR'); ?></td>
														<td>
															<input type='text' name='tempaddmatchescount' id='tempaddmatchescount' value='0' size='3' class='inputbox' />
														</td>
													</tr>
													<tr>
														<td width="100" align="right" class="key"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_START_HERE'); ?></td>
														<td><?php echo $this->lists['addToRound']; ?></td>
													</tr>
													<tr>
														<td width="100" align="right" class="key"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_AUTO_PUBL'); ?></td>
														<td><?php echo $this->lists['autoPublish']; ?></td>
													</tr>
													<tr>
														<td width="100" align="right" class="key"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_FIRST_MATCHNR'); ?></td>
														<td><input type='text' name='firstMatchNumber' size='4' value='' /></td>
													</tr>
													<tr>
														<td width="100" align="right" class="key"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_STARTTIME'); ?></td>
														<td>
				<?php
				echo HTMLHelper::calendar(
					sportsmanagementHelper::convertDate($this->roundws->round_date_first),
					'match_date', 'match_date',
					'%d-%m-%Y', 'size="10" '
				);
															?>
															&nbsp;
															<input type='text' name='startTime' value='<?php echo $this->projectws->start_time; ?>' size='4' maxlength='5' class='inputbox' />
														</td>
													</tr>
													<tr>
														<td width="100" colspan='2'>
															<input type='submit' value='<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_NEW_MATCHES'); ?>' onclick='return addmatches();' />
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
							<td valign='top' width='50%'>
								<table class="table">
									<tr>
										<td width="100" align="right" class="key"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_COPY2'); ?></td>
										<td><?php echo $this->lists['project_rounds2']; ?></td>
									</tr>
									<tr>
										<td width="100" align="right" class="key"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_DEFAULT_DATE'); ?></td>
										<td>
			<?php
			echo HTMLHelper::calendar(
				sportsmanagementHelper::convertDate($this->roundws->round_date_first),
				'date', 'date',
				'%d-%m-%Y', 'size="10" '
			);
			?>
											&nbsp;
											<input type='text' name='time' value='<?php echo $this->projectws->start_time; ?>' size='4' maxlength='5' class='inputbox' />
										</td>
									</tr>
									<tr>
										<td width="100" align="right" class="key"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_FIRST_MATCHNR'); ?></td>
										<td><input type="text" name="start_match_number" size="4" value="" /></td>
									</tr>
									<tr>
										<td width="100" align="right" class="key"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_ROUND_TITLE'); ?></td>
										<td><input type="text" name="start_round_name" size="50" value="" /></td>
									</tr>
									<tr>
										<td width="100" align="right" class="key">                                      
												<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_CREATE_NEW'); ?>
										</td>
										<td><input type="checkbox" name="create_new" value="1" class="inputbox" checked="checked" /></td>
									</tr>
									<tr>
										<td width="100" align="right" class="key">
												<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_COPY_MIRROR'); ?>
										</td>
										<td>
											<select name="mirror" class="inputbox">
												<option value="0" selected="selected"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_COPY_MATCHES'); ?></option>
												<option value="1"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_MIRROR_HA'); ?></option>
											</select>
										</td>
									</tr>
									<tr>
										<td width="100" colspan='2'>                                                                  
											<input type='submit' value='<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_COPY_MATCHES'); ?>' onclick='copymatches();' />
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</tbody>
				</table>
			</form>

  
