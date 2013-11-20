<?php 
/**
* @copyright	Copyright (C) 2007-2012 JoomLeague.net. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

defined('_JEXEC') or die('Restricted access');
?>
		<fieldset class='adminform'>
			<legend>
				<?php
				echo JText::sprintf('Predictiongame settings for project [%1$s]','<i>'.$this->pred_project->project_name.'</i>');
				?>
			</legend>
			<table class='admintable' border='0'>
				<tr>
					<td nowrap='nowrap' class='key' style='text-align:right;'>
		 				<label for='league'>
							<?php
							echo JText::_('Publish Prediction Project?');
							?>
						</label>
					</td>
					<td colspan='3'>
						<?php
						echo $this->lists['published'];
						?>
					</td>
				</tr>
				
        <tr>
					<td nowrap='nowrap' class='key' style='text-align:right;'>
						<label for="name">
							<?php
							echo JText::_('Prediction game mode');
							?>
						</label>
					</td>
					<td colspan='3'>
						<?php
						echo $this->lists['mode'];
						?>
					</td>
				</tr>
        
				<tr>
					<td nowrap='nowrap' class='key' style='text-align:right;'>
						<label for="admins">
							<?php
							echo JText::_('Evaluation of prediction ranking');
							?>
						</label>
					</td>
					<td colspan='3'>
						<?php
						echo $this->lists['overview'];
						?>
					</td>
				</tr>
				<tr>
					<td nowrap='nowrap' class='key' style='text-align:right;'>
		 				<label for='league'>
							<?php
							echo JText::_('Prediction with Joker?');
							?>
						</label>
					</td>
					<td colspan='3'>
						<?php
						echo $this->lists['use_joker'];
						?>
					</td>
				</tr>
				<tr>
					<td colspan='4'>&nbsp;</td>
				</tr>
				<tr>
					<td nowrap='nowrap' style='text-align:right;'>
						&nbsp;
					</td>
					<td colspan='3' class='key' style='text-align:center;'>
		 				<label for='league'>
						<?php
							echo JText::_('Tipp Calculation');
						?>
						</label>
					</td>
				</tr>
				<tr>
					<td nowrap='nowrap' style='text-align:right;'>&nbsp;</td>
					<td class='key' style='text-align:center;'>
						<?php
							echo JText::_('Calculation without Joker');
						?>
					</td>
					<td class='key' width='5%'>&nbsp;</td>
					<td class='key' style='text-align:center;'>
						<?php
							echo JText::_('Calculation with Joker');
						?>
					</td>
				</tr>
				<tr>
					<td nowrap='nowrap' class='key' style='text-align:right;'>
		 				<label for='league'>
							<?php
							echo JText::_('Points for correct prediction');
							?>
						</label>
					</td>
					<td style='text-align:center;'>
						<input	type='text' name='points_correct_result' size='5' class='inputbox'
								value='<?php echo $this->pred_project->points_correct_result; ?>' disabled='disabled' />
					</td>
					<td width='5%'>
						&nbsp;
					</td>
					<td style='text-align:center;'>
						<input	type='text' name='points_correct_result_joker' size='5' class='inputbox'
								value='<?php echo $this->pred_project->points_correct_result_joker; ?>' disabled='disabled' />
					</td>
				</tr>
				<tr>
					<td nowrap='nowrap' class='key' style='text-align:right;'>
		 				<label for='league'>
							<?php
							echo JText::_('Points for correct margin');
							?>
						</label>
					</td>
					<td style='text-align:center;'>
						<input	type='text' name='points_correct_diff' size='5' class='inputbox'
								value='<?php echo $this->pred_project->points_correct_diff; ?>' disabled='disabled' />
					</td>
					<td width='5%'>
						&nbsp;
					</td>
					<td style='text-align:center;'>
						<input	type='text' name='points_correct_diff_joker' size='5' class='inputbox'
								value='<?php echo $this->pred_project->points_correct_diff_joker; ?>' disabled='disabled' />
					</td>
				</tr>
				<tr>
					<td nowrap='nowrap' class='key' style='text-align:right;'>
		 				<label for='league'>
							<?php
							echo JText::_('Points for draw difference');
							?>
						</label>
					</td>
					<td style='text-align:center;'>
						<input	type='text' name='points_correct_draw' size='5' class='inputbox'
								value='<?php echo $this->pred_project->points_correct_draw; ?>' disabled='disabled' />
					</td>
					<td width='5%'>
						&nbsp;
					</td>
					<td style='text-align:center;'>
						<input	type='text' name='points_correct_draw_joker' size='5' class='inputbox'
								value='<?php echo $this->pred_project->points_correct_draw_joker; ?>' disabled='disabled' />
					</td>
				</tr>
				<tr>
					<td nowrap='nowrap' class='key' style='text-align:right;'>
		 				<label for='league'>
							<?php
							echo JText::_('Points for correct tendency');
							?>
						</label>
					</td>
					<td style='text-align:center;'>
						<input	type='text' name='points_correct_tendence' size='5' class='inputbox'
								value='<?php echo $this->pred_project->points_correct_tendence; ?>' disabled='disabled' />
					</td>
					<td width='5%'>
						&nbsp;
					</td>
					<td style='text-align:center;'>
						<input	type='text' name='points_correct_tendence_joker' size='5' class='inputbox'
								value='<?php echo $this->pred_project->points_correct_tendence_joker; ?>' disabled='disabled' />
					</td>
				</tr>
				<tr>
					<td nowrap='nowrap' class='key' style='text-align:right;'>
		 				<label for='league'>
							<?php
							echo JText::_('Points for wrong prediction');
							?>
						</label>
					</td>
					<td style='text-align:center;'>
						<input	type='text' name='points_tipp' size='5' class='inputbox'
								value='<?php echo $this->pred_project->points_tipp; ?>' disabled='disabled' />
					</td>
					<td width='5%'>
						&nbsp;
					</td>
					<td style='text-align:center;'>
						<input	type='text' name='points_tipp_joker' size='5' class='inputbox'
								value='<?php echo $this->pred_project->points_tipp_joker; ?>' disabled='disabled' />
					</td>
				</tr>
				<tr>
					<td colspan='4'>&nbsp;</td>
				</tr>
				<tr>
					<td nowrap='nowrap' class='key' style='text-align:right;'>
		 				<label for='league'>
							<?php
							echo JText::_('Limit Joker predictions?');
							?>
						</label>
					</td>
					<td colspan='3'>
						<?php
						echo $this->lists['joker_limit'];
						?>
					</td>
				</tr>
				<tr>
					<td nowrap='nowrap' class='key' style='text-align:right;'>
		 				<label for='league'>
							<?php
							echo JText::_('Number of Joker predictions');
							?>
						</label>
					</td>
					<td colspan='3'>
						<input	type='text' name='joker_limit' size='5' class='inputbox'
								value='<?php echo $this->pred_project->joker_limit; ?>' disabled='disabled' />
					</td>
				</tr>
				<tr>
					<td nowrap='nowrap' class='key' style='text-align:right;'>
		 				<label for='league'>
							<?php
							echo JText::_('Pick the champion?');
							?>
						</label>
					</td>
					<td colspan='3'>
						<?php
						echo $this->lists['use_champ'];
						?>
					</td>
				</tr>
				
        <tr>
					<td nowrap='nowrap' class='key' style='text-align:right;'>
		 				<label for='league'>
							<?php
							echo JText::_('Points for picking the champion');
							?>
						</label>
					</td>
					<td colspan='3'>
						<input	type='text' name='points_tipp_champ' size='5' class='inputbox'
								value='<?php echo $this->pred_project->points_tipp_champ; ?>' disabled='disabled' />
					</td>
				</tr>
        
        <tr>
					<td nowrap='nowrap' class='key' style='text-align:right;'>
						<label for="league_teams">
							<?php
							echo JText::_('JL_PREDGAME_LEAGUE_TEAMS_CHAMPION');
							?>
						</label>
					</td>
					<td colspan='3'>
						<?php
						echo $this->lists['league_teams'];
						?>
					</td>
				</tr>
        
			</table>
		</fieldset>