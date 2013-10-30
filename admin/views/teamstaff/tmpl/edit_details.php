<?php defined('_JEXEC') or die('Restricted access');
?>		
		
			<fieldset class="adminform">
			<legend>
				<?php
				echo JText::sprintf(	'COM_JOOMLEAGUE_ADMIN_TEAMSTAFF_DETAILS_TITLE',
				  JoomleagueHelper::formatName(null, $this->project_teamstaff->firstname, $this->project_teamstaff->nickname, $this->project_teamstaff->lastname, 0),
				  $this->teamws->name, $this->projectws->name);
				?>
			</legend>
			<table class="admintable" border="0">
				<tr>
					<td width="20%" valign="top" align="right" class="key">
						<label for="position">
							<?php
								echo JText::_( 'COM_JOOMLEAGUE_ADMIN_TEAMSTAFF_POS' );
							?>
						</label>
					</td>
					<td colspan="7">
						<?php
						echo $this->lists['projectpositions'];
						?>
					</td>
				</tr>

				<tr>
					<td valign="top" align="right" class="key">
						<?php
						echo JText::_( 'COM_JOOMLEAGUE_ADMIN_TEAMSTAFF_INJURED' );
						?>
					</td>
					<td class="nowrap">
						<fieldset class="radio">
							<?php
							echo $this->lists['injury'];
							?>
						</fieldset>
					</td>
					<td valign="top" align="right" class="key">
						<?php
						echo JText::_( 'COM_JOOMLEAGUE_ADMIN_TEAMSTAFF_INJURY_DATE' );
						?>
					</td>
					<td>
						<?php
						echo $this->lists['injury_date'];
						?>
					</td>
					<td valign="top" align="right" class="key">
						<?php
						echo JText::_( 'COM_JOOMLEAGUE_ADMIN_TEAMSTAFF_INJURY_END' );
						?>
					</td>
					<td>
						<?php
						echo $this->lists['injury_end'];
						?>
					</td>
					<td valign="top" align="right" class="key">
						<?php
						echo JText::_( 'COM_JOOMLEAGUE_ADMIN_TEAMSTAFF_INJURY_TYPE' );
						?>
					</td>
					<td>
						<input	class="text_area" type="text" name="injury_detail" id="title" size="32" maxlength="250"
						value="<?php echo $this->project_teamstaff->injury_detail; ?>" />
					</td>
				</tr>

				<tr>
					<td valign="top" align="right" class="key">
						<?php
						echo JText::_( 'COM_JOOMLEAGUE_ADMIN_TEAMSTAFF_SUSPENDED' );
						?>
					</td>
					<td class="nowrap">
						<fieldset class="radio">
							<?php
							echo $this->lists['suspension'];
							?>
						</fieldset>
					</td>
					<td valign="top" align="right" class="key">
						<?php
						echo JText::_( 'COM_JOOMLEAGUE_ADMIN_TEAMSTAFF_SUSPENSION_DATE' );
						?>
					</td>
					<td>
						<?php
						echo $this->lists['suspension_date'];
						?>
					</td>
					<td valign="top" align="right" class="key">
						<?php
						echo JText::_( 'COM_JOOMLEAGUE_ADMIN_TEAMSTAFF_SUSPENSION_END' );
						?>
					</td>
					<td>
						<?php
						echo $this->lists['suspension_end'];
						?>
					</td>
					<td valign="top" align="right" class="key">
						<?php
						echo JText::_( 'COM_JOOMLEAGUE_ADMIN_TEAMSTAFF_SUSPENSION_TYPE' );
						?>
					</td>
					<td>
						<input	class="text_area" type="text" name="suspension_detail" id="title" size="32" maxlength="250"
								value="<?php echo $this->project_teamstaff->suspension_detail; ?>" />
					</td>
				</tr>

				<tr>
					<td valign="top" align="right" class="key">
						<?php
						echo JText::_( 'COM_JOOMLEAGUE_ADMIN_TEAMSTAFF_AWAY' );
						?>
					</td>
					<td class="nowrap">
						<fieldset class="radio">
							<?php
							echo $this->lists['away'];
							?>
						</fieldset>
					</td>
					<td valign="top" align="right" class="key">
						<?php
						echo JText::_( 'COM_JOOMLEAGUE_ADMIN_TEAMSTAFF_AWAY_DATE' );
						?>
					</td>
					<td>
						<?php
						echo $this->lists['away_date'];
						?>
					</td>
					<td valign="top" align="right" class="key">
						<?php
						echo JText::_( 'COM_JOOMLEAGUE_ADMIN_TEAMSTAFF_AWAY_END' );
						?>
					</td>
					<td>
						<?php
						echo $this->lists['away_end'];
						?>
					</td>
					<td valign="top" align="right" class="key">
						<?php
						echo JText::_( 'COM_JOOMLEAGUE_ADMIN_TEAMSTAFF_AWAY_TYPE' );
						?>
					</td>
					<td>
						<input	class="text_area" type="text" name="away_detail" id="title" size="32" maxlength="250"
								value="<?php echo $this->project_teamstaff->away_detail; ?>" />
					</td>
				</tr>

			</table>
		</fieldset>