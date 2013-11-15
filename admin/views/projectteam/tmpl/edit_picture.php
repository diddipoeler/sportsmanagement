<?php 
defined('_JEXEC') or die('Restricted access');
?>

		<fieldset class="adminform">
			<legend>
            <?php echo JText::sprintf(	'COM_SPORTSMANAGEMENT_ADMIN_P_TEAM_TITLE_PIC',
												'<i>' . $this->project_team->name . '</i>',
												'<i>' . $this->project->name . '</i>'); ?>
			</legend>
			<table class="admintable">
					<?php foreach ($this->form->getFieldset('picture') as $field): ?>
					<tr>
						<td class="key"><?php echo $field->label; ?></td>
						<td><?php echo $field->input; ?></td>
					</tr>					
					<?php endforeach; ?>
			</table>
		</fieldset>