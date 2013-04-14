<?php 
defined( '_JEXEC' ) or die( 'Restricted access' );
?>		
<fieldset class="adminform">
	<legend><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_POSITION_DETAILS_LEGEND'); ?>
	</legend>
	<table class="admintable">
		<tr>
			<td class="key"><?php echo $this->form->getLabel('name'); ?></td>
			<td><?php echo $this->form->getInput('name'); ?></td>
		</tr>
		<tr>
			<td class="key"><?php echo $this->form->getLabel('alias'); ?></td>
			<td><?php echo $this->form->getInput('alias'); ?></td>
		</tr>	
		<tr>
			<td class="key"><?php echo $this->form->getLabel('sports_type_id'); ?></td>
			<td><?php echo $this->form->getInput('sports_type_id'); ?></td>
		</tr>	
		<tr>
			<td class="key"><?php echo $this->form->getLabel('published'); ?></td>
			<td><?php echo $this->form->getInput('published'); ?></td>
		</tr>			
		<tr>
			<td class="key"><?php echo $this->form->getLabel('persontype'); ?></td>
			<td><?php echo $this->form->getInput('persontype'); ?></td>
		</tr>	
				<tr>
					<td class="key"><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_POSITION_P_POSITION'); ?></td>
					<td><?php echo $this->lists['parents']; ?></td>
				</tr>		
	</table>
</fieldset>	