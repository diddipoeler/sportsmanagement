<?php 
defined('_JEXEC') or die('Restricted access');

JHTML::_('behavior.tooltip');
?>
<!-- import the functions to move the events between selection lists  -->
<?php
//$version = urlencode(sportsmanagementHelper::getVersion());
//echo JHTML::script('projectposition.js','administrator/components/com_sportsmanagement/assets/js/');
?>

<script type="text/javascript">
function moveSelectedItems(source, destination){
	var selected = $(source+' option:selected').remove();
	var sorted = $.makeArray($(destination+' option').add(selected)).sort(function(a,b){
		return $(a).text() > $(b).text() ? 1:-1;
	});
	$(destination).empty().append(sorted);
}

$(document).ready(function(){
	$('#t1add').click(function(){
		moveSelectedItems('#positionslist', '#project_positionslist');
	});
	$('#t1remove').click(function(){
		moveSelectedItems('#project_positionslist', '#positionslist');
	});
	$('#t1addAll').click(function(){
		$('#positionslist option').attr('selected', 'true');
		moveSelectedItems('#positionslist', '#project_positionslist');
	});
	$('#t1removeAll').click(function(){
		$('#project_positionslist option').attr('selected', 'true');
		moveSelectedItems('#project_positionslist', '#positionslist');
	});
});
</script>


<form  action="<?php echo JRoute::_('index.php?option=com_sportsmanagement');?>" id='component-form' method='post' style='display:inline' name='adminform' >
	<div class="col50">

    <fieldset>
		<div class="fltrt">
			<button type="button" onclick="Joomla.submitform('projectpositions.store', this.form)">
				<?php echo JText::_('JSAVE');?></button>
			<button id="cancel" type="button" onclick="<?php echo JRequest::getBool('refresh', 0) ? 'window.parent.location.href=window.parent.location.href;' : '';?>  window.parent.SqueezeBox.close();">
				<?php echo JText::_('JCANCEL');?></button>
		</div>
	</fieldset>
    
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
<input id="t1add" type="button" value=">" />
<input id="t1addAll" type="button" value=">>" />
<input id="t1removeAll"  type="button" value="<<" />
<input id="t1remove" type="button" value="<" />
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
	<?php echo JHTML::_('form.token')."\n"; ?>
</form>