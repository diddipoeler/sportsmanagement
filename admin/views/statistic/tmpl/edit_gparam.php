<?php defined('_JEXEC') or die('Restricted access');
?>
	<?php foreach ($this->form->getFieldsets('baseparams') as $fieldset): ?>
		<fieldset class="adminform">
			<legend>
				<?php
				echo JText::_($fieldset->label);
				?>
			</legend>
			<table class="admintable">
				<?php
				// render is defined in joomla\libraries\joomla\html\parameter.php
				foreach ($this->form->getFieldset($fieldset->name) as $field):
				?>
				<tr>
					<td class="key"><?php echo $field->label; ?></td>
					<td><?php echo $field->input; ?></td>
				</tr>
				<?php endforeach; ?>
			</table>
		</fieldset>
	<?php endforeach; ?>