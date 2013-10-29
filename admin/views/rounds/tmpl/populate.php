<?php defined('_JEXEC') or die('Restricted access');
JHTML::_('behavior.tooltip');
?>

<form method="post" id="adminForm" action="<?php echo $this->request_url; ?>">
	<fieldset class='adminform'>	
	<fieldset class="adminform">
	<?php echo Jtext::_('COM_JOOMLEAGUE_ADMIN_ROUNDS_POPULATE_DESC'); ?>
	</fieldset>
	<legend><?php echo JText::sprintf('COM_JOOMLEAGUE_ADMIN_ROUNDS_POPULATE_LEGEND','<i>'.$this->projectws->name.'</i>'); ?></legend>
		<?php echo JHTML::_('form.token')."\n"; ?>
		<table class='admintable'>
		<tbody>
		
		<tr>
			<td nowrap='nowrap' class="key hasTip" title="<?php echo JText::_('COM_JOOMLEAGUE_ADMIN_ROUNDS_POPULATE_TYPE_LABEL').'::'.JText::_('COM_JOOMLEAGUE_ADMIN_ROUNDS_POPULATE_TYPE_TIP'); ?>">
				<label for="scheduling"><?php echo JText::_('COM_JOOMLEAGUE_ADMIN_ROUNDS_POPULATE_TYPE_LABEL'); ?></label>
			</td>
			<td><?php echo $this->lists['scheduling']; ?></td>
		</tr>
		
		<tr>
			<td nowrap='nowrap' class="key hasTip" title="<?php echo JText::_('COM_JOOMLEAGUE_ADMIN_ROUNDS_POPULATE_STARTTIME_LABEL').'::'.JText::_('COM_JOOMLEAGUE_ADMIN_ROUNDS_POPULATE_STARTTIME_TIP'); ?>">
				<label for="time"><?php echo JText::_('COM_JOOMLEAGUE_ADMIN_ROUNDS_POPULATE_STARTTIME_LABEL'); ?></label>
			</td>
			<td><input type="text" name="time" value="20:00"/></td>
		</tr>
		
		<tr>
			<td nowrap='nowrap' class="key hasTip" title="<?php echo JText::_('COM_JOOMLEAGUE_ADMIN_ROUNDS_POPULATE_ROUNDS_INTERVAL_LABEL').'::'.JText::_('COM_JOOMLEAGUE_ADMIN_ROUNDS_POPULATE_ROUNDS_INTERVAL_TIP'); ?>">
				<label for="interval"><?php echo JText::_('COM_JOOMLEAGUE_ADMIN_ROUNDS_POPULATE_ROUNDS_INTERVAL_LABEL'); ?></label>
			</td>
			<td><input type="text" name="interval" value="7"/></td>
		</tr>
		
		<tr>
			<td nowrap='nowrap' class="key hasTip" title="<?php echo JText::_('COM_JOOMLEAGUE_ADMIN_ROUNDS_POPULATE_STARTDATE_LABEL').'::'.JText::_('COM_JOOMLEAGUE_ADMIN_ROUNDS_POPULATE_STARTDATE_TIP'); ?>">
				<label for="start"><?php echo JText::_('COM_JOOMLEAGUE_ADMIN_ROUNDS_POPULATE_STARTDATE_LABEL'); ?></label>
			</td>
			<td><?php echo JHTML::calendar(strftime('%Y-%m-%d'), 'start', 'start', '%Y-%m-%d'); ?></td>
		</tr>
		
		<tr>
			<td nowrap='nowrap' class="key hasTip" title="<?php echo JText::_('COM_JOOMLEAGUE_ADMIN_ROUNDS_POPULATE_NEW_ROUND_NAME_LABEL').'::'.JText::_('COM_JOOMLEAGUE_ADMIN_ROUNDS_POPULATE_NEW_ROUND_NAME_TIP'); ?>">
				<label for="roundname"><?php echo JText::_('COM_JOOMLEAGUE_ADMIN_ROUNDS_POPULATE_NEW_ROUND_NAME_LABEL'); ?></label>
			</td>
			<td><input type="text" name="roundname" value="<?php echo JText::_('COM_JOOMLEAGUE_ADMIN_ROUNDS_POPULATE_NEW_ROUND_NAME'); ?>"/></td>
		</tr>
		
		<tr>
			<td nowrap='nowrap' class="key hasTip" title="<?php echo JText::_('COM_JOOMLEAGUE_ADMIN_ROUNDS_POPULATE_TEAMS_ORDER_LABEL').'::'.JText::_('COM_JOOMLEAGUE_ADMIN_ROUNDS_POPULATE_TEAMS_ORDER_TIP'); ?>">
				<label for="roundname"><?php echo JText::_('COM_JOOMLEAGUE_ADMIN_ROUNDS_POPULATE_TEAMS_ORDER_LABEL'); ?></label>
			</td>
			<td>
				<?php echo $this->lists['teamsorder']; ?>
				<div id="ordering_buttons">
					<button type="button" id="buttonup"><?php echo JText::_('COM_JOOMLEAGUE_ADMIN_ROUNDS_POPULATE_TEAMS_ORDER_UP')?></button>
					<button type="button" id="buttondown"><?php echo JText::_('COM_JOOMLEAGUE_ADMIN_ROUNDS_POPULATE_TEAMS_ORDER_DOWN')?></button>
				</div>
			</td>
		</tr>
		
		</tbody></table>
	</fieldset>
	
	<input type="hidden" name="task" value="" />
	<input type='hidden' name='project_id' value='<?php echo $this->projectws->id; ?>' />
</form>
