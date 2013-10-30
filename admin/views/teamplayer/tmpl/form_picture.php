<?php defined('_JEXEC') or die('Restricted access');

?>

		<fieldset class="adminform">
			<legend>
			<?php echo JText::sprintf('COM_JOOMLEAGUE_ADMIN_TEAMPLAYER_PIC_TITLE',
										JoomleagueHelper::formatName(null, $this->project_player->firstname, $this->project_player->nickname, $this->project_player->lastname, 0),
										'<i>' . $this->teamws->name . '</i>', '<i>' . $this->projectws->name . '</i>' );
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