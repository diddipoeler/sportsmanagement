<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       default_massad.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage jlextindividualsportes
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

?>

	<div id="editcell">
		<fieldset class="adminform">
			<legend><?php echo Text::sprintf('COM_SPORTSMANAGEMENT_MATCHES_MASSADD_TITLE', '<i>' . $this->projectws->name . '</i>'); ?></legend>
			<form name='copyform' method='post' style='display:inline' id='copyform'>
				<input type='hidden' name='match_date' value='<?php echo $this->roundws->round_date_first . ' ' . $this->projectws->start_time; ?>' />
				<input type='hidden' name='round_id' value='<?php echo $this->roundws->id; ?>' />
				<input type='hidden' name='project_id' value='<?php echo $this->roundws->project_id; ?>' />
				<input type='hidden' name='act' value='rounds' />
				<input type='hidden' name='task' value='copyfrom' />
				<input type='hidden' name='addtype' value='0' id='addtype' />
				<input type='hidden' name='add_match_count' value='0' id='addmatchescount' />
				<?php echo HTMLHelper::_('form.token') . "\n"; ?>
				<table class='adminlist' border='0'>
					<thead>
						<tr>
							<th nowrap="nowrap"><?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHES_MASSADD_MULTI'); ?></th>
							<th nowrap="nowrap"><?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHES_MASSADD_COPY'); ?></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td valign='top' width='40%'>
								<table border='0'>
									<tr>
										<td style='text-align:right; vertical-align:top; font-weight:bold; '><?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHES_MASSADD_TYPE'); ?></td>
										<td style='text-align:left; vertical-align:top; '><?php echo $this->lists['createTypes']; ?></td>
									</tr>
									<tr>
										<td colspan='2' >
											<div id='massadd_standard' style='display:block;'>
												<table>
													<tr>
														<td style='text-align:right; vertical-align:top; font-weight:bold; '><?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHES_MASSADD_NR'); ?></td>
														<td style='text-align:left; vertical-align:top; '>
															<input type='text' name='tempaddmatchescount' id='tempaddmatchescount' value='0' size='3' class='inputbox' />
														</td>
													</tr>
													<tr>
														<td style='text-align:right; vertical-align:top; font-weight:bold; '><?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHES_MASSADD_START_HERE'); ?></td>
														<td style='text-align:left; vertical-align:top; '><?php echo $this->lists['addToRound']; ?></td>
													</tr>
													<tr>
														<td style='text-align:right; vertical-align:top; font-weight:bold; '><?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHES_MASSADD_AUTO_PUBL'); ?></td>
														<td style='text-align:left; vertical-align:top; '><?php echo $this->lists['autoPublish']; ?></td>
													</tr>
													<tr>
														<td style='text-align:right; vertical-align:top; font-weight:bold; '><?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHES_MASSADD_FIRST_MATCHNR'); ?></td>
														<td style='text-align:left; vertical-align:top; '><input type='text' name='firstMatchNumber' size='4' value='' /></td>
													</tr>
													<tr>
														<td style='text-align:right; vertical-align:top; font-weight:bold; '><?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHES_MASSADD_STARTTIME'); ?></td>
														<td>
				<?php
				echo HTMLHelper::calendar(
					JoomleagueHelper::convertDate($this->roundws->round_date_first),
					'match_date', 'match_date',
					'%d-%m-%Y', 'size="10" '
				);
															?>
															&nbsp;
															<input type='text' name='startTime' value='<?php echo $this->projectws->start_time; ?>' size='4' maxlength='5' class='inputbox' />
														</td>
													</tr>
													<tr>
														<td style='text-align:right; vertical-align:top; font-weight:bold; ' colspan='2'>
															<input type='submit' value='<?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHES_MASSADD_NEW_MATCHES'); ?>' onclick='return addmatches();' />
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
								<table border='0'>
									<tr>
										<td valign="top" align="right"><b><?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHES_MASSADD_COPY2'); ?></b></td>
										<td valign="top" align="left"><?php echo $this->lists['project_rounds2']; ?></td>
									</tr>
									<tr>
										<td valign="top" align="right"><b><?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHES_MASSADD_DEFAULT_DATE'); ?></b></td>
										<td>
			<?php
			echo HTMLHelper::calendar(
				JoomleagueHelper::convertDate($this->roundws->round_date_first),
				'date', 'date',
				'%d-%m-%Y', 'size="10" '
			);
			?>
											&nbsp;
											<input type='text' name='time' value='<?php echo $this->projectws->start_time; ?>' size='4' maxlength='5' class='inputbox' />
										</td>
									</tr>
									<tr>
										<td valign="top" align="right"><b><?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHES_MASSADD_FIRST_MATCHNR'); ?></b></td>
										<td><input type="text" name="start_match_number" size="4" value="" /></td>
									</tr>
									<tr>
										<td valign="top" align="right"><b>
											<acronym title="<?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHES_MASSADD_ACRONYM_CREATE_NEW'); ?>">
												<?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHES_MASSADD_CREATE_NEW'); ?>
											</acronym></b>
										</td>
										<td><input type="checkbox" name="create_new" value="1" class="inputbox" checked="checked" /></td>
									</tr>
									<tr>
										<td valign="top" align="right"><b>
											<acronym title="<?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHES_MASSADD_ACRONYM_MIRROR'); ?>">
												<?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHES_MASSADD_COPY_MIRROR'); ?>
											</acronym></b>
										</td>
										<td>
											<select name="mirror" class="inputbox">
												<option value="0" selected="selected"><?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHES_MASSADD_COPY_MATCHES'); ?></option>
												<option value="1"><?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHES_MASSADD_MIRROR_HA'); ?></option>
											</select>
											<br /><br />
											<input type='submit' value='<?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHES_MASSADD_COPY_MATCHES'); ?>' onclick='copymatches();' />
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</tbody>
				</table>
			</form>
		</fieldset>
	</div>
