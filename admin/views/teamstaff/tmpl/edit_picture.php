<?php 
defined('_JEXEC') or die('Restricted access');

?>

		<fieldset class="adminform">
			<legend>
            <?php 
            echo JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_TEAMSTAFF_PIC_TITLE',
										sportsmanagementHelper::formatName(null, $this->project_person->firstname, $this->project_person->nickname, $this->project_person->lastname, 0),
										'<i>' . $this->teamws->name . '</i>', '<i>' . $this->project->name . '</i>' );
            ?>
			</legend>
			<table class="admintable">
					<?php foreach ($this->form->getFieldset($this->cfg_which_media_tool) as $field): ?>
					<tr>
						<td class="key"><?php echo $field->label; ?></td>
						<td><?php echo $field->input; ?></td>
					</tr>					
					<?php endforeach; ?>
			</table>
		</fieldset>		