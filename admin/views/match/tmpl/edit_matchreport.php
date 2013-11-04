<?php 
defined( '_JEXEC' ) or die( 'Restricted access' );
?>		
<fieldset class="adminform">
	<legend><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_MR'); ?>
	</legend>
	<table class="admintable">
		<tr>
			<td class="key"><?php echo $this->form->getLabel('show_report'); ?></td>
			<td><?php echo $this->form->getInput('show_report'); ?></td>
		</tr>
		<tr>
			<td class="key"><?php echo $this->form->getLabel('summary'); ?></td>
			<td><?php echo $this->form->getInput('summary'); ?></td>
		</tr>	
	</table>
</fieldset>		