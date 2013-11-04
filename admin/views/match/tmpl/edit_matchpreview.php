<?php  
defined( '_JEXEC' ) or die( 'Restricted access' );
?>			
<fieldset class="adminform">
	<legend><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_MP'); ?>
	</legend>
	<table class="admintable">
		<tr>
			<td class="key"><?php echo $this->form->getLabel('preview'); ?></td>
			<td><?php echo $this->form->getInput('preview'); ?></td>
		</tr>
	</table>
</fieldset>		