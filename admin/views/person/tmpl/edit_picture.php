<?php defined('_JEXEC') or die('Restricted access');

?>

		<fieldset class="adminform">
			<legend><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_PERSON_PICTURE' );?>
			</legend>
			<table class="admintable">
					<?php 
                    //foreach ($this->form->getFieldset($this->cfg_which_media_tool) as $field): 
                    foreach ($this->form->getFieldset('picture') as $field):
                    ?>
					<tr>
						<td class="key"><?php echo $field->label; ?></td>
						<td><?php echo $field->input; ?></td>
					</tr>					
					<?php endforeach; ?>
			</table>
		</fieldset>