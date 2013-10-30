<?php defined('_JEXEC') or die('Restricted access');
?>

		<fieldset class="adminform">
			<legend>
            <?php  
            echo JText::sprintf('COM_JOOMLEAGUE_ADMIN_TEAMPLAYER_EXT_TITLE',
										JoomleagueHelper::formatName(null, $this->project_player->firstname, $this->project_player->nickname, $this->project_player->lastname, 0),
										'<i>' . $this->teamws->name . '</i>', '<i>' . $this->projectws->name . '</i>' );                                    
                                                
                                                ?>
			</legend>
<?php            
foreach ($this->extended->getFieldsets() as $fieldset)
{
	?>
	<fieldset class="adminform">
	<legend><?php echo JText::_($fieldset->name); ?></legend>
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