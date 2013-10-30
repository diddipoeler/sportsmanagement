<?php defined('_JEXEC') or die('Restricted access');
?>

		<fieldset class="adminform">
			<legend>
			<?php echo JText::sprintf('COM_JOOMLEAGUE_ADMIN_TEAMSTAFF_DESCR_TITLE',
										JoomleagueHelper::formatName(null, $this->project_teamstaff->firstname, $this->project_teamstaff->nickname, $this->project_teamstaff->lastname, 0),
										'<i>' . $this->teamws->name . '</i>', '<i>' . $this->projectws->name . '</i>' );
			?>
			</legend>
			<table class="admintable">
					<?php foreach ($this->form->getFieldset('description') as $field): ?>
					<tr>
						<td class="key"><?php echo $field->label; ?></td>
						<td><?php echo $field->input; ?></td>
					</tr>					
					<?php endforeach; ?>
			</table>
		</fieldset>