<?php defined('_JEXEC') or die('Restricted access');
?>
<fieldset class="adminform">
	<legend><?php echo JText::_('COM_JOOMLEAGUE_ADMIN_ROSTERPOSITIONS_LEGEND'); ?>
	</legend>
	<table class="admintable">
	<tr>
			<td class="key"><?php echo $this->form->getLabel('name'); ?></td>
			<td><?php echo $this->form->getInput('name'); ?></td>
		</tr>
		
		<tr>
			<td class="key"><?php echo $this->form->getLabel('short_name'); ?></td>
			<td><?php echo $this->form->getInput('short_name'); ?></td>
		</tr>		
        
        <tr>
			<td class="key"><?php echo $this->form->getLabel('players'); ?></td>
			<td><?php echo $this->form->getInput('players'); ?></td>
		</tr>
		
		<tr>
			<td class="key"><?php echo $this->form->getLabel('country'); ?></td>
			<td><?php echo $this->form->getInput('country'); ?>&nbsp;<?php echo Countries::getCountryFlag($this->item->country); ?>&nbsp;(<?php echo $this->item->country; ?>)</td>
		</tr>
        <tr>
			<td class="key"><?php echo $this->form->getLabel('picture'); ?></td>
			<td><?php echo $this->form->getInput('picture'); ?></td>
		</tr>	

</table>      
</fieldset>