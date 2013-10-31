<?php defined('_JEXEC') or die('Restricted access');
?>

<fieldset class="adminform">
	<legend>
		<?php
		echo JText::sprintf(	'COM_SPORTSMANAGEMENT_ADMIN_P_REF_DESCR',
				sportsmanagementHelper::formatName(null, $this->item->firstname, $this->item->nickname, $this->item->lastname, 0),
				$this->project->name);
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
