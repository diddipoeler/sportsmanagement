<?php defined('_JEXEC') or die('Restricted access');
?>
<fieldset class="adminform">
	<legend><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_COUNTRY_LEGEND'); ?>
	</legend>
	<table class="admintable">
		<tr>
			<td class="key"><?php echo $this->form->getLabel('name'); ?></td>
			<td><?php echo $this->form->getInput('name'); ?></td>
		</tr>
		<tr>
			<td class="key"><?php echo $this->form->getLabel('alpha2'); ?></td>
			<td><?php echo $this->form->getInput('alpha2'); ?></td>
		</tr>
		<tr>
			<td class="key"><?php echo $this->form->getLabel('alpha3'); ?></td>
			<td><?php echo $this->form->getInput('alpha3'); ?></td>
		</tr>		
		<tr>
			<td class="key"><?php echo $this->form->getLabel('itu'); ?></td>
			<td><?php echo $this->form->getInput('itu'); ?></td>
		</tr>
		
        <tr>
			<td class="key"><?php echo $this->form->getLabel('fips'); ?></td>
			<td><?php echo $this->form->getInput('fips'); ?></td>
		</tr>
		<tr>
			<td class="key"><?php echo $this->form->getLabel('ioc'); ?></td>
			<td><?php echo $this->form->getInput('ioc'); ?></td>
		</tr>
		<tr>
			<td class="key"><?php echo $this->form->getLabel('fifa'); ?></td>
			<td><?php echo $this->form->getInput('fifa'); ?></td>
		</tr>
		<tr>
			<td class="key"><?php echo $this->form->getLabel('ds'); ?></td>
			<td><?php echo $this->form->getInput('ds'); ?></td>
		</tr>
		<tr>
			<td class="key"><?php echo $this->form->getLabel('wmo'); ?></td>
			<td><?php echo $this->form->getInput('wmo'); ?></td>
		</tr>
		
		
		
	</table>
</fieldset>