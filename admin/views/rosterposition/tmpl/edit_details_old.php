<?php defined('_JEXEC') or die('Restricted access');
?>
<fieldset class="adminform">
	<legend><?php echo JText::_('COM_JOOMLEAGUE_ADMIN_ROSTERPOSITIONS_LEGEND'); ?>
	</legend>
	<table class="admintable">
		<tr>
			<td width="100" align="right" class="key"><label for="name"><?php echo JText::_('COM_JOOMLEAGUE_ADMIN_ROSTERPOSITIONS_NAME'); ?></label></td>
			<td>
				<input class="text_area" type="text" name="name" id="title" size="32" maxlength="250" value="<?php echo $this->object->name; ?>" />
			</td>
		</tr>

		<tr>
			<td width="100" align="right" class="key"><label for="short_name"><?php echo JText::_('COM_JOOMLEAGUE_ADMIN_ROSTERPOSITIONS_SHORT_NAME'); ?></label></td>
			<td>
			
				
<?php echo $this->lists['project_type']; ?>&nbsp;(<?php echo $this->object->short_name; ?>)				
			</td>
		</tr>		
		
		<tr>
			<td valign="top" align="right" class="key"><label for="ordering"><?php echo JText::_('COM_JOOMLEAGUE_ADMIN_ROSTERPOSITIONS_COUNTRY'); ?></label></td>
			<td><?php echo $this->lists['countries']; ?>&nbsp;<?php echo Countries::getCountryFlag($this->object->country); ?>&nbsp;(<?php echo $this->object->country; ?>)</td>
		</tr>
		<tr>
			<td valign="top" align="right" class="key"><label for="ordering"><?php echo JText::_('COM_JOOMLEAGUE_ADMIN_ROSTERPOSITIONS_ORDERING'); ?></label></td>
			<td><?php echo $this->lists['ordering']; ?></td>
		</tr>

	</table>
  
  <table class="admintable">
					<?php foreach ($this->form->getFieldset('details') as $field): ?>
					<tr>
						<td class="key"><?php echo $field->label; ?></td>
						<td><?php echo $field->input; ?></td>
					</tr>					
					<?php endforeach; ?>
			</table>
      
</fieldset>