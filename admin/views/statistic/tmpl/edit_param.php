<?php defined('_JEXEC') or die('Restricted access');
?>
	<?php foreach ($this->form->getFieldsets('params') as $fieldset): ?>
		<fieldset class="adminform">
			<legend>
				<?php
				echo JText::_($fieldset->label);
				?>
			</legend>
			<?php if ($fieldset->description): ?>
			<div class="fs-description">
				<?php echo JText::_($fieldset->description); ?>
			</div>
			<?php endif; ?>
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