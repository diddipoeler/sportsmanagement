<?php defined('_JEXEC')or die('Restricted access');
?>
		<fieldset class="adminform">
			<legend>
            <?php
                    echo JText::sprintf(	'COM_JOOMLEAGUE_ADMIN_P_TEAM_TITLE_DETAILS',
												'<i>' . $this->project_team->name . '</i>',
												'<i>' . $this->projectws->name . '</i>');
					
					?>
            
            
            </legend>
			<table class="admintable">
				<tr>
					<td class="key"><?php echo $this->form->getLabel('admin'); ?></td>
					<td><?php echo $this->form->getInput('admin'); ?></td>
				</tr>
				
				<?php if ($this->projectws->project_type == 'DIVISIONS_LEAGUE') :?>
				<tr>
					<td class="key"><?php echo JText::_('COM_JOOMLEAGUE_ADMIN_P_TEAM_DIV');	?></td>
					<td>
						<?php 
						$inputappend='';
						if ($this->project_team->division_id == 0)
						{
									$inputappend=' style="background-color:#bbffff"';
						}
						echo JHTML::_(	'select.genericlist',
										$this->lists['divisions'],
										'division_id',
										$inputappend.'class="inputbox" size="1"',
										'value','text', $this->project_team->division_id);
						?>
					</td>
				</tr>
				<?php endif;?>
				<tr>
					<td class="key"><?php echo $this->form->getLabel('standard_playground'); ?></td>
					<td><?php echo $this->form->getInput('standard_playground'); ?></td>
				</tr>
				<tr>
					<td class="key"><?php echo $this->form->getLabel('is_in_score'); ?></td>
					<td><?php echo $this->form->getInput('is_in_score'); ?></td>
				</tr>
				</table>
			</fieldset>				
				
			<fieldset class="adminform">
				<table class="admintable">				
				<tr>
					<td class="key"><?php echo $this->form->getLabel('start_points'); ?></td>
					<td><?php echo $this->form->getInput('start_points'); ?></td>
				</tr>	
				<tr>
					<td class="key"><?php echo $this->form->getLabel('reason'); ?></td>
					<td><?php echo $this->form->getInput('reason'); ?></td>
				</tr>							          					
				</table>
			</fieldset>

			</fieldset>			
			<fieldset class="adminform">
				<table class="admintable">
				<tr>
					<td class="key"><?php echo $this->form->getLabel('use_finally'); ?></td>
					<td><?php echo $this->form->getInput('use_finally'); ?></td>
				</tr>
				<tr>
					<td class="key"><?php echo $this->form->getLabel('matches_finally'); ?></td>
					<td><?php echo $this->form->getInput('matches_finally'); ?></td>
				</tr>
				<tr>
					<td class="key"><?php echo $this->form->getLabel('points_finally'); ?></td>
					<td><?php echo $this->form->getInput('points_finally'); ?></td>
				</tr>
				<tr>
					<td class="key"><?php echo $this->form->getLabel('neg_points_finally'); ?></td>
					<td><?php echo $this->form->getInput('neg_points_finally'); ?></td>
				</tr>
                <tr>
					<td class="key"><?php echo $this->form->getLabel('penalty_points'); ?></td>
					<td><?php echo $this->form->getInput('penalty_points'); ?></td>
				</tr>
				<tr>
					<td class="key"><?php echo $this->form->getLabel('won_finally'); ?></td>
					<td><?php echo $this->form->getInput('won_finally'); ?></td>
				</tr>
				<tr>
					<td class="key"><?php echo $this->form->getLabel('draws_finally'); ?></td>
					<td><?php echo $this->form->getInput('draws_finally'); ?></td>
				</tr>
				<tr>
					<td class="key"><?php echo $this->form->getLabel('lost_finally'); ?></td>
					<td><?php echo $this->form->getInput('lost_finally'); ?></td>
				</tr>
				<tr>
					<td class="key"><?php echo $this->form->getLabel('homegoals_finally'); ?></td>
					<td><?php echo $this->form->getInput('homegoals_finally'); ?></td>
				</tr>
				<tr>
					<td class="key"><?php echo $this->form->getLabel('guestgoals_finally'); ?></td>
					<td><?php echo $this->form->getInput('guestgoals_finally'); ?></td>
				</tr>
				<tr>
					<td class="key"><?php echo $this->form->getLabel('diffgoals_finally'); ?></td>
					<td><?php echo $this->form->getInput('diffgoals_finally'); ?></td>
				</tr>						
				</table>
			</fieldset>
		</fieldset>