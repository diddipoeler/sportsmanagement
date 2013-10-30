<?php defined('_JEXEC') or die('Restricted access');
?>			
		<fieldset class="adminform">
			<legend>
				<?php
				echo JText::sprintf('COM_JOOMLEAGUE_ADMIN_TEAMPLAYER_DETAILS_TITLE',
				  JoomleagueHelper::formatName(null, $this->project_player->firstname, $this->project_player->nickname, $this->project_player->lastname, 0),
				  '<i>' . $this->teamws->name . '</i>', '<i>' . $this->projectws->name . '</i>' );
				?>
			</legend>
			<table class="admintable">
				<tr>
					<td width="20%" valign="top" align="right" class="key">
						<label for="position">
							<?php
								echo JText::_( 'COM_JOOMLEAGUE_ADMIN_TEAMPLAYER_POS' );
							?>
						</label>
					</td>
					<td>
						<?php
						echo $this->lists['projectpositions'];
						?>
					</td>
					<td width="20%" valign="top" align="right" class="key">
						<?php
						echo JText::_( 'COM_JOOMLEAGUE_ADMIN_TEAMPLAYER_JERSEYNR' );
						?>
					</td>
					<td colspan="1">
						<input	class="inputbox" type="text" name="jerseynumber" size="5" maxlength="6"
								value="<?php echo $this->project_player->jerseynumber; ?>" />
					</td>
                    
                    <td width="20%" valign="top" align="right" class="key">
						<?php
						echo JText::_( 'COM_JOOMLEAGUE_ADMIN_TEAMPLAYER_MARKET_VALUE' );
						?>
					</td>
					<td colspan="7">
						<input	class="inputbox" type="text" name="market_value" size="20" maxlength="20"
								value="<?php echo $this->project_player->market_value; ?>" />
					</td>
                    
				</tr>

				<tr>
					<td valign="top" align="right" class="key">
						<?php
						echo JText::_( 'COM_JOOMLEAGUE_ADMIN_TEAMPLAYER_INJURED' );
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
						echo JText::_( 'COM_JOOMLEAGUE_ADMIN_TEAMPLAYER_INJURY_DATE' );
						?>
					</td>
					<td>
						<?php
						echo $this->lists['injury_date'];
						?>
					</td>
					<td valign="top" align="right" class="key">
						<?php
						echo JText::_( 'COM_JOOMLEAGUE_ADMIN_TEAMPLAYER_INJURY_END' );
						?>
					</td>
					<td>
						<?php
						echo $this->lists['injury_end'];
						?>
					</td>
					<td valign="top" align="right" class="key">
						<?php
						echo JText::_( 'COM_JOOMLEAGUE_ADMIN_TEAMPLAYER_INJURY_TYPE' );
						?>
					</td>
					<td>
						<input	class="text_area" type="text" name="injury_detail" id="title" size="32" maxlength="250"
						value="<?php echo $this->project_player->injury_detail; ?>" />
					</td>
				</tr>

				<tr>
					<td valign="top" align="right" class="key">
						<?php
						echo JText::_( 'COM_JOOMLEAGUE_ADMIN_TEAMPLAYER_SUSPENDED' );
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
						echo JText::_( 'COM_JOOMLEAGUE_ADMIN_TEAMPLAYER_SUSPENSION_DATE' );
						?>
					</td>
					<td>
						<?php
						echo $this->lists['suspension_date'];
						?>
					</td>
					<td valign="top" align="right" class="key">
						<?php
						echo JText::_( 'COM_JOOMLEAGUE_ADMIN_TEAMPLAYER_SUSPENSION_END' );
						?>
					</td>
					<td>
						<?php
						echo $this->lists['suspension_end'];
						?>
					</td>
					<td valign="top" align="right" class="key">
						<?php
						echo JText::_( 'COM_JOOMLEAGUE_ADMIN_TEAMPLAYER_SUSPENSION_TYPE' );
						?>
					</td>
					<td>
						<input	class="text_area" type="text" name="suspension_detail" id="title" size="32" maxlength="250"
								value="<?php echo $this->project_player->suspension_detail; ?>" />
					</td>
				</tr>

				<tr>
					<td valign="top" align="right" class="key">
						<?php
						echo JText::_( 'COM_JOOMLEAGUE_ADMIN_TEAMPLAYER_AWAY' );
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
						echo JText::_( 'COM_JOOMLEAGUE_ADMIN_TEAMPLAYER_AWAY_DATE' );
						?>
					</td>
					<td>
						<?php
						echo $this->lists['away_date'];
						?>
					</td>
					<td valign="top" align="right" class="key">
						<?php
						echo JText::_( 'COM_JOOMLEAGUE_ADMIN_TEAMPLAYER_AWAY_END' );
						?>
					</td>
					<td>
						<?php
						echo $this->lists['away_end'];
						?>
					</td>
					<td valign="top" align="right" class="key">
						<?php
						echo JText::_( 'COM_JOOMLEAGUE_ADMIN_TEAMPLAYER_AWAY_TYPE' );
						?>
					</td>
					<td>
						<input	class="text_area" type="text" name="away_detail" id="title" size="32" maxlength="250"
								value="<?php echo $this->project_player->away_detail; ?>" />
					</td>
				</tr>

			</table>
		</fieldset>