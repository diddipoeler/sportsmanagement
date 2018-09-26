<?php 
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
JHtml::_('behavior.tooltip');
?>
<!-- import the functions to move the events between selection lists  -->
<?php
//$version = urlencode(sportsmanagementHelper::getVersion());
//echo JHtml::script('projectposition.js','administrator/components/com_sportsmanagement/assets/js/');
?>




<form  action="<?php echo JRoute::_('index.php?option=com_sportsmanagement');?>" id='component-form' method='post' style='display:inline' name='adminform' >
	<div class="col50">

    <fieldset>
		<div class="fltrt">
			<button type="button" onclick="Joomla.submitform('projectpositions.store', this.form)">
				<?php echo Text::_('JSAVE');?></button>
			<button id="cancel" type="button" onclick="<?php echo JFactory::getApplication()->input->getBool('refresh', 0) ? 'window.parent.location.href=window.parent.location.href;' : '';?>  window.parent.SqueezeBox.close();">
				<?php echo Text::_('JCANCEL');?></button>
		</div>
	</fieldset>
    
		<fieldset class="adminform">
			<legend><?php echo Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_EDIT_LEGEND','<i>'.$this->project->name.'</i>');?></legend>
			<table class="<?php echo $this->table_data_class; ?>">
			<thead>
				<tr>
					<th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_EDIT_AVAILABLE'); ?></th>
					<th width="20"></th>
					<th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_EDIT_ASSIGNED'); ?></th>
					
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
		<input type="hidden" name="pid" value="<?php echo $this->project->id; ?>" />
		<input type="hidden" name="task" value="" />
        <input type='hidden' name='project_id' value='<?php echo $this->project->id; ?>' />
            <input type="hidden" name="component" value="com_sportsmanagement" />
	</div>
	<?php echo JHtml::_('form.token')."\n"; ?>
</form>