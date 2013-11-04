<?php 
defined('_JEXEC') or die('Restricted access');

JHTML::_('behavior.tooltip');
?>
<!-- import the functions to move the events between selection lists  -->
<?php
//$version = urlencode(sportsmanagementHelper::getVersion());
echo JHTML::script('projectposition.js','administrator/components/com_sportsmanagement/assets/js/');
?>
<form action="index.php" method="post" id="adminForm">
	<div class="col50">
		<fieldset class="adminform">
			<legend><?php echo JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_EDIT_LEGEND','<i>'.$this->project->name.'</i>');?></legend>
			<table class="adminlist">
			<thead>
				<tr>
					<th><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_EDIT_AVAILABLE'); ?></th>
					<th width="20"></th>
					<th><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_EDIT_ASSIGNED'); ?></th>
					
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
		
		<input type="hidden" name="pid" value="<?php echo $this->project_id; ?>" />
		<input type="hidden" name="task" value="" />
	</div>
	<?php echo JHTML::_('form.token')."\n"; ?>
</form>