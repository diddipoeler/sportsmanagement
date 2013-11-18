<?php 
defined('_JEXEC') or die('Restricted access');

// relative
// absolute
// width-60 fltlft
?>
<div style="position:relative;" class="width-60 fltlft">
		<fieldset class="adminform">
			<legend><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_CLUB_DETAILS' );?>
			</legend>
			<table class="admintable">
				<?php foreach ($this->form->getFieldset('details') as $field): ?>
					<tr>
						<td class="key"><?php echo $field->label; ?></td>
						<td><?php echo $field->input; ?></td>
					</tr>					
					<?php endforeach; ?>
			</table>
		</fieldset>
</div>        