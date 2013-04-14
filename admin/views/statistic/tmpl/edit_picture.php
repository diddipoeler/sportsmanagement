<?php defined('_JEXEC') or die('Restricted access');
?>

		<fieldset class="adminform">
			<legend><?php echo JText::_( 'COM_JOOMLEAGUE_ADMIN_STAT_PIC' );?>
			</legend>
			<table class="admintable">
				<tr>
					<td class="key"><?php echo $this->form->getField('icon')->label; ?></td>
					<td><?php echo $this->form->getField('icon')->input; ?></td>
				</tr>
			</table>
		</fieldset>