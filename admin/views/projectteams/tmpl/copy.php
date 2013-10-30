<?php defined('_JEXEC') or die('Restricted access');

?>

<form action="<?php echo $this->request_url; ?>" method="post" id="adminForm">
	
	<fieldset>
	<legend><?php echo JText::_('COM_JOOMLEAGUE_ADMIN_PROJECTTEAMS_COPY_DEST')?></legend>
	<table class="admintable">
		<tr>
			<td class="key">
				<label for="dest"><?php echo JText::_('COM_JOOMLEAGUE_ADMIN_PROJECTTEAMS_SELECT_PROJECT' ).':'; ?></label>
			</td>
			<td>
				<?php echo $this->lists['projects']; ?>
			</td>
		</tr>
	</table>
	</fieldset>
	
	<?php foreach ($this->ptids as $ptid): ?>
	<input type="hidden" name="ptids[]" value="<?php echo $ptid; ?>"/>
	<?php endforeach; ?>
	<input type="hidden" name="task" value="" />
</form>