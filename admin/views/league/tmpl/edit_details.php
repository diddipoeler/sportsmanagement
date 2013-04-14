<?php defined('_JEXEC') or die('Restricted access');
?>
<fieldset class="adminform">
	<legend><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_LEAGUE_LEGEND'); ?>
	</legend>
	<table class="admintable">
		<tr>
			<td class="key"><?php echo $this->form->getLabel('name'); ?></td>
			<td><?php echo $this->form->getInput('name'); ?></td>
		</tr>
		<tr>
			<td class="key"><?php echo $this->form->getLabel('middle_name'); ?></td>
			<td><?php echo $this->form->getInput('middle_name'); ?></td>
		</tr>
		<tr>
			<td class="key"><?php echo $this->form->getLabel('short_name'); ?></td>
			<td><?php echo $this->form->getInput('short_name'); ?></td>
		</tr>		
		<tr>
			<td class="key"><?php echo $this->form->getLabel('alias'); ?></td>
			<td><?php echo $this->form->getInput('alias'); ?></td>
		</tr>
		<tr>
			<td class="key"><?php echo $this->form->getLabel('country'); ?></td>
			<td><?php echo $this->form->getInput('country'); ?>&nbsp;<?php echo Countries::getCountryFlag($this->object->country); ?>&nbsp;(<?php echo $this->object->country; ?>)</td>
		</tr>
        <tr>
			<td class="key"><?php echo $this->form->getLabel('associations'); ?></td>
			<td><?php echo $this->form->getInput('associations'); ?></td>
		</tr>
	</table>
</fieldset>