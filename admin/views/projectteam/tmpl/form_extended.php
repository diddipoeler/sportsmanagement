<?php defined('_JEXEC') or die('Restricted access');

?>

		<fieldset class="adminform">
			<legend>
            <?php echo JText::sprintf(	'COM_JOOMLEAGUE_ADMIN_P_TEAM_TITLE_EXT',
												'<i>' . $this->project_team->name . '</i>',
												'<i>' . $this->projectws->name . '</i>'); ?>
			</legend>
<?php            
foreach ($this->extended->getFieldsets() as $fieldset)
{
	?>
	<fieldset class="adminform">
	<legend><?php echo JText::_($fieldset->name); ?>
    </legend>
	<?php
	$fields = $this->extended->getFieldset($fieldset->name);
	
	if(!count($fields)) {
		echo JText::_('COM_JOOMLEAGUE_GLOBAL_NO_PARAMS');
	}
	
	foreach ($fields as $field)
	{
		echo $field->label;
       	echo $field->input;
	}
	?>
	</fieldset>
	<?php
}
?>
</fieldset>