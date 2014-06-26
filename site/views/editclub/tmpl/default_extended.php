<?php 
defined('_JEXEC') or die('Restricted access');


foreach ($this->extended->getFieldsets() as $fieldset)
{
	?>
	
	<legend><?php echo JText::_($fieldset->name); ?></legend>
	<?php
	$fields = $this->extended->getFieldset($fieldset->name);
	
	if(!count($fields)) {
		echo JText::_('COM_JOOMLEAGUE_GLOBAL_NO_PARAMS');
	}
	?>
			<fieldset class="adminform">
			<legend><?php echo JText::_($fieldset->name); ?></legend>
			<table class='adminForm' border='0'>
			<?php
	foreach ($fields as $field)
	{
		?>
				<tr>
					<td class="td_r"><?php echo $field->label;?></td>
					<td><?php echo $field->input;?></td>
		       	</tr>
		       	<?php
	}
	?>
    </table>
	</fieldset>
	<?php
}
?>
