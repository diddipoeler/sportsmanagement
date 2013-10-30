<?php defined('_JEXEC') or die('Restricted access');
?>		
			<fieldset class="adminform">
				<legend>
					<?php
                    echo JText::sprintf(	'COM_JOOMLEAGUE_ADMIN_P_TEAM_TITLE_TRAINING',
												'<i>' . $this->project_team->name . '</i>',
												'<i>' . $this->projectws->name . '</i>');
					
					?>
				</legend>
				<table class='admintable'>
					<tr>
						<td class='key' nowrap='nowrap'>
							<?php echo JText::_('COM_JOOMLEAGUE_ADMIN_P_TEAM_ADD_NEW'); ?>&nbsp;<input type='checkbox' name='add_trainingData' id='add' value='' onchange='javascript:submitbutton("projectteam.apply");' />
						</td>
						<td class='key' style='text-align:center;' width='5%'><?php echo JText::_('COM_JOOMLEAGUE_ADMIN_P_TEAM_DAY'); ?></td>
						<td class='key' style='text-align:center;' width='5%'><?php echo JText::_('COM_JOOMLEAGUE_ADMIN_P_TEAM_STARTTIME'); ?></td>
						<td class='key' style='text-align:center;' width='5%'><?php echo JText::_('COM_JOOMLEAGUE_ADMIN_P_TEAM_ENDTIME'); ?></td>
						<td class='key' style='text-align:center;'><?php echo JText::_('COM_JOOMLEAGUE_ADMIN_P_TEAM_PLACE'); ?></td>
						<td class='key' style='text-align:center;'><?php echo JText::_('COM_JOOMLEAGUE_ADMIN_P_TEAM_NOTES'); ?></td>
					</tr>
					<?php
					if (!empty($this->trainingData))
					{
						?>
						<input type='hidden' name='tdCount' value='<?php echo count($this->trainingData); ?>' />
						<?php
						foreach ($this->trainingData AS $td)
						{
							$hours=($td->time_start / 3600); $hours=(int)$hours;
							$mins=(($td->time_start - (3600*$hours)) / 60); $mins=(int)$mins;
							$startTime=sprintf('%02d',$hours).':'.sprintf('%02d',$mins);
							$hours=($td->time_end / 3600); $hours=(int)$hours;
							$mins=(($td->time_end - (3600*$hours)) / 60); $mins=(int)$mins;
							$endTime=sprintf('%02d',$hours).':'.sprintf('%02d',$mins);
							?>
							<tr>
								<td class='key' nowrap='nowrap'>
									<?php echo JText::_('COM_JOOMLEAGUE_GLOBAL_DELETE');?>&nbsp;<input type='checkbox' name='delete_<?php echo $td->id; ?>' value='' onchange='javascript:submitbutton("projectteam.apply");' />
								</td>
								<td nowrap='nowrap' width='5%'><?php echo $this->lists['dayOfWeek'][$td->id]; ?></td>
								<td nowrap='nowrap' width='5%'>
									<input class='text' type='text' name='time_start_<?php echo $td->id; ?>' size='8' maxlength='5' value='<?php echo $startTime; ?>' />
								</td>
								<td nowrap='nowrap' width='5%'>
									<input class='text' type='text' name='time_end_<?php echo $td->id; ?>' size='8' maxlength='5' value='<?php echo $endTime; ?>' />
								</td>
								<td>
									<input class='text' type='text' name='place_<?php echo $td->id; ?>' size='40' maxlength='255' value='<?php echo $td->place; ?>' />
								</td>
								<td>
									<textarea class='text_area' name='notes_<?php echo $td->id; ?>' rows='3' cols='40' value='' /><?php echo $td->notes; ?></textarea>
									<input type='hidden' name='tdids[]' value='<?php echo $td->id; ?>' />
								</td>
							</tr>
							<?php
						}
					}
					?>
				</table>
			</fieldset>