<?php defined('_JEXEC') or die('Restricted access');
?>
<fieldset class="adminform">
	<legend><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_AGEGROUP_LEGEND'); ?>
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
			<td class="key"><?php echo $this->form->getLabel('info'); ?></td>
			<td><?php echo $this->form->getInput('info'); ?></td>
		</tr>		
		<tr>
			<td class="key"><?php echo $this->form->getLabel('notes'); ?></td>
			<td><?php echo $this->form->getInput('notes'); ?></td>
		</tr>
		
        <tr>
			<td class="key"><?php echo $this->form->getLabel('age_from'); ?></td>
			<td><?php echo $this->form->getInput('age_from'); ?></td>
		</tr>
        <tr>
			<td class="key"><?php echo $this->form->getLabel('age_to'); ?></td>
			<td><?php echo $this->form->getInput('age_to'); ?></td>
		</tr>
        <tr>
			<td class="key"><?php echo $this->form->getLabel('deadline_day'); ?></td>
			<td><?php echo $this->form->getInput('deadline_day'); ?></td>
		</tr>
        
        
	</table>
</fieldset>