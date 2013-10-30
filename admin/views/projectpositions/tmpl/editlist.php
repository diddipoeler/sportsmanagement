<?php defined('_JEXEC') or die('Restricted access');

JHTML::_('behavior.tooltip');
?>
<!-- import the functions to move the events between selection lists  -->
<?php
$version = urlencode(JoomleagueHelper::getVersion());
echo JHTML::script('JL_eventsediting.js?v='.$version,'administrator/components/com_joomleague/assets/js/');
?>
<form action="index.php" method="post" id="adminForm">
	<div class="col50">
		<fieldset class="adminform">
			<legend><?php echo JText::sprintf('COM_JOOMLEAGUE_ADMIN_P_POSITION_EDIT_LEGEND','<i>'.$this->projectws->name.'</i>');?></legend>
			<table class="adminlist">
			<thead>
				<tr>
					<th><?php echo JText::_('COM_JOOMLEAGUE_ADMIN_P_POSITION_EDIT_AVAILABLE'); ?></th>
					<th width="20"></th>
					<th><?php echo JText::_('COM_JOOMLEAGUE_ADMIN_P_POSITION_EDIT_ASSIGNED'); ?></th>
					
				</tr>
			</thead>
				<tr>		
					<td><?php echo $this->lists['positions']; ?></td>				
					<td style="text-align:center;">
						&nbsp;&nbsp;
						<input	type="button" class="inputbox"
								onclick="handleLeftToRight()"
								value="&gt;&gt;" />
						&nbsp;&nbsp;<br />&nbsp;&nbsp;
					 	<input	type="button" class="inputbox"
					 			onclick="handleRightToLeft()"
								value="&lt;&lt;" />
						&nbsp;&nbsp;
					</td>
					<td><?php echo $this->lists['project_positions']; ?></td>
				</tr>
			</table>
		</fieldset>
		<div class="clr"></div>
		<input type="hidden" name="positionschanges_check" value="0" id="positionschanges_check" />
		<input type="hidden" name="option" value="com_joomleague" />
		<input type="hidden" name="cid[]" value="<?php echo $this->projectws->id; ?>" />
		<input type="hidden" name="task" value="" />
	</div>
	<?php echo JHTML::_('form.token')."\n"; ?>
</form>