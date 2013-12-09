<?php defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.tooltip');JHtml::_('behavior.modal');
?>
<script>
	function searchTemplate(val,key)
	{
		var f = $('adminForm');
		if(f){
			f.elements['search'].value=val;
			f.elements['search_mode'].value= 'matchfirst';
			f.submit();
		}
	}
</script>
<div id="editcell">
	<fieldset class="adminform">
		<legend><?php echo JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_TEMPLATES_LEGEND','<i>'.$this->projectws->name.'</i>'); ?></legend>
		<?php if ($this->projectws->master_template){echo $this->loadTemplate('import');} ?>
		<form action="index.php?option=com_sportsmanagement&view=templates" method="post" id="adminForm"  name="adminForm">
			<table class="adminlist">
				<thead>
					<tr>
						<th width="5"><?php echo JText::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM'); ?></th>
						<th width="20">
							<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
						</th>
						<th width="20">&nbsp;</th>
						<th>
							<?php echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_TEMPLATES_TEMPLATE','tmpl.template',$this->lists['order_Dir'],$this->lists['order']); ?>
						</th>
						<th>
							<?php echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_TEMPLATES_DESCR','tmpl.template',$this->lists['order_Dir'],$this->lists['order']); ?>
						</th>
						<th>
							<?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_TEMPLATES_TYPE'); ?>
						</th>
						<th>
							<?php echo JHtml::_('grid.sort','JGRID_HEADING_ID','tmpl.id',$this->lists['order_Dir'],$this->lists['order']); ?>
						</th>
					</tr>
				</thead>
				<tfoot><tr><td colspan="9"><?php echo $this->pagination->getListFooter(); ?></td></tr></tfoot>
				<tbody>
					<?php
					$k=0;
					for ($i=0, $n=count($this->templates); $i < $n; $i++)
					{
						$row =& $this->templates[$i];
						$link1=JRoute::_('index.php?option=com_sportsmanagement&task=template.edit&id='.$row->id);
						$checked=JHtml::_('grid.checkedout',$row,$i);
						?>
						<tr class="<?php echo "row$k"; ?>">
							<td class="center"><?php echo $this->pagination->getRowOffset($i); ?></td>
							<td class="center"><?php echo $checked; ?></td>
							<td><?php
								$imageFile='administrator/components/com_sportsmanagement/assets/images/edit.png';
								$imageTitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_TEMPLATES_EDIT_DETAILS');
								$imageParams='title= "'.$imageTitle.'"';
								$image=JHtml::image($imageFile,$imageTitle,$imageParams);
								$linkParams='';
								echo JHtml::link($link1,$image);
								?></td>
							<td><?php echo $row->template; ?></td>
							<td><?php echo JText::_($row->title); ?></td>
							<td><?php
								echo '<span style="font-weigth:bold; color:';
								echo ($row->isMaster) ? 'red; ">'.JText::_('COM_SPORTSMANAGEMENT_ADMIN_TEMPLATES_MASTER') : 'green;">&nbsp;'.JText::_('COM_SPORTSMANAGEMENT_ADMIN_TEMPLATES_INDEPENDENT');
								echo '</span>';
								?></td>
							<td class="center"><?php
								echo $row->id;
								?><input type='hidden' name='isMaster[<?php echo $row->id; ?>]' value='<?php echo $row->isMaster; ?>' /><?php ?></td>
						</tr>
						<?php
						$k=1 - $k;
					}
					?>
				</tbody>
			</table>
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="boxchecked" value="0" />
			<input type="hidden" name="filter_order_Dir" value="" />
			<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
			<input type="hidden" name="search_mode" value="<?php echo $this->lists['search_mode'];?>" />
			<?php echo JHtml::_('form.token')."\n"; ?>
		</form>
	</fieldset>
</div>