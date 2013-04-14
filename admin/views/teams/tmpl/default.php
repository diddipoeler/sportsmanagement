<?php defined('_JEXEC') or die('Restricted access');

//Ordering allowed ?
$ordering=($this->lists['order'] == 't.ordering');

JHTML::_('behavior.tooltip');
JHTML::_('behavior.modal');
?>
<script>

	function searchTeam(val,key)
	{
		var f = $('adminForm');
		if(f)
		{
		f.elements['search'].value=val;
		f.elements['search_mode'].value= 'matchfirst';
		f.submit();
		}
	}

</script>
<form action="<?php echo $this->request_url; ?>" method="post" id="adminForm" name="adminForm">
	<table>
		<tr>
			<td align="left" width="100%">
				<?php
				echo JText::_('COM_SPORTSMANAGEMENT_GLOBAL_FILTER');
				?>&nbsp;<input	type="text" name="search" id="search"
								value="<?php echo $this->lists['search']; ?>"
								class="text_area" onchange="$('adminForm').submit(); " />
				<button onclick="this.form.submit(); "><?php echo JText::_('COM_SPORTSMANAGEMENT_GLOBAL_GO'); ?></button>
				<button onclick="document.getElementById('search').value='';this.form.submit(); ">
					<?php echo JText::_('COM_SPORTSMANAGEMENT_GLOBAL_RESET'); ?>
				</button>
			</td>
			<td align="center" colspan="4">
				<?php
				for ($i=65; $i < 91; $i++)
				{
					printf("<a href=\"javascript:searchTeam('%s')\">%s</a>&nbsp;&nbsp;&nbsp;&nbsp;",chr($i),chr($i));
				}
				?>
			</td>
		</tr>
	</table>
	<div id="editcell">
		<table class="adminlist">
			<thead>
				<tr>
					<th width="5"><?php echo JText::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM'); ?></th>
					<th width="20">
						<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
					</th>
					<th width="20">&nbsp;</th>
					<th class="left">
						<?php
						echo JHTML::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_TEAMS_NAME','t.name',$this->lists['order_Dir'],$this->lists['order']);
						?>
					</th>
					<th class="left">
						<?php
						echo JHTML::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_TEAMS_CLUBNAME','c.name',$this->lists['order_Dir'],$this->lists['order']);
						?>
					</th>
					<th>
						<?php
						echo JHTML::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_TEAMS_WEBSITE','t.website',$this->lists['order_Dir'],$this->lists['order']);
						?>
					</th>
					<th>
						<?php
						echo JHTML::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_TEAMS_ML_NAME','t.middle_name',$this->lists['order_Dir'],$this->lists['order']);
						?>
					</th>
					<th>
						<?php
						echo JHTML::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_TEAMS_S_NAME','t.short_name',$this->lists['order_Dir'],$this->lists['order']);
						?>
					</th>
					<th>
						<?php
						echo JHTML::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_TEAMS_INFO','t.info',$this->lists['order_Dir'],$this->lists['order']);
						?>
					</th>
					<th>
						<?php
						echo JHTML::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_TEAMS_PICTURE','t.picture',$this->lists['order_Dir'],$this->lists['order']);
						?>
					</th>
					<th width="10%">
						<?php
						echo JHTML::_('grid.sort','COM_SPORTSMANAGEMENT_GLOBAL_ORDER','t.ordering',$this->lists['order_Dir'],$this->lists['order']);
						echo JHTML::_('grid.order',$this->items, 'filesave.png', 'teams.saveorder');
						?>
					</th>
					<th width="5%">
						<?php
						echo JHTML::_('grid.sort','COM_SPORTSMANAGEMENT_GLOBAL_ID','t.id',$this->lists['order_Dir'],$this->lists['order']);
						?>
					</th>
				</tr>
			</thead>
			<tfoot><tr><td colspan="12"><?php echo $this->pagination->getListFooter(); ?></td></tr></tfoot>
			<tbody>
				<?php
				$k=0;
				for ($i=0,$n=count($this->items); $i < $n; $i++)
				{
					$row =& $this->items[$i];
					$link=JRoute::_('index.php?option=com_sportsmanagement&task=team.edit&id='.$row->id);
					$checked=JHTML::_('grid.checkedout',$row,$i);
					?>
					<tr class="<?php echo "row$k"; ?>">
						<td class="center"><?php echo $this->pagination->getRowOffset($i); ?></td>
						<td class="center"><?php echo $checked; ?></td>
						<?php
						if (JTable::isCheckedOut($this->user->get('id'),$row->checked_out))
						{
							$inputappend=' disabled="disabled"';
							?><td class="center">&nbsp;</td><?php
						}
						else
						{
							$inputappend='';
							?>
							<td class="center">
								<a href="<?php echo $link; ?>">
									<?php
									$imageTitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMS_EDIT_DETAILS');
									echo JHTML::_(	'image','administrator/components/com_sportsmanagement/assets/images/edit.png',
													$imageTitle,'title= "'.$imageTitle.'"');
									?>
								</a>
							</td>
							<?php
						}
						?>
						<td><?php echo $row->name; ?></td>
						<td><?php echo (empty($row->clubname)) ? '<span style="color:red;">'.JText::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMS_NO_CLUB').'</span>' : $row->clubname; ?></td>
						<td>
							<?php
							if ($row->website != '')
							{
								echo '<a href="'.$row->website.'" target="_blank">';
							}
							echo $row->website;
							if ($row->website != '')
							{
								echo '</a>';
							}
							?>
						</td>
						<td><?php echo $row->middle_name; ?></td>
						<td class="center"><?php echo $row->short_name; ?></td>
						<td><?php echo $row->info; ?></td>
						<td class="center">
							<?php
							if ($row->picture == '')
							{
								$imageTitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMS_NO_IMAGE');
								echo JHTML::_(	'image','administrator/components/com_sportsmanagement/assets/images/error.png',
												$imageTitle,'title= "'.$imageTitle.'"');
							}
							elseif ($row->picture == sportsmanagementHelper::getDefaultPlaceholder("team"))
							{
								$imageTitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMS_DEFAULT_IMAGE');
								echo JHTML::_(	'image','administrator/components/com_sportsmanagement/assets/images/information.png',
				  								$imageTitle,'title= "'.$imageTitle.'"');
							} else {
								if (JFile::exists(JPATH_SITE.DS.$row->picture)) {
									$imageTitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMS_CUSTOM_IMAGE');
									echo JHTML::_(	'image','administrator/components/com_sportsmanagement/assets/images/ok.png',
													$imageTitle,'title= "'.$imageTitle.'"');
								} else {
									$imageTitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMS_NO_IMAGE');
									echo JHTML::_(	'image','administrator/components/com_sportsmanagement/assets/images/delete.png',
													$imageTitle,'title= "'.$imageTitle.'"');
								}
							}
							?>
						</td>
						<td class="order">
							<span>
								<?php echo $this->pagination->orderUpIcon($i,$i > 0 ,'team.orderup','COM_SPORTSMANAGEMENT_GLOBAL_ORDER_UP',true); ?>
							</span>
							<span>
								<?php echo $this->pagination->orderDownIcon($i,$n,$i < $n,'team.orderdown','COM_SPORTSMANAGEMENT_GLOBAL_ORDER_DOWN',true); ?>
								<?php $disabled=true ?	'' : 'disabled="disabled"'; ?>
							</span>
							<input	type="text" name="order[]" size="5" value="<?php echo $row->ordering;?>" <?php echo $disabled; ?>
									class="text_area" style="text-align: center" />
						</td>
						<td class="center"><?php echo $row->id; ?></td>
					</tr>
					<?php
					$k=1 - $k;
				}
				?>
			</tbody>
		</table>
	</div>

	<input type="hidden" name="search_mode"			value="<?php echo $this->lists['search_mode']; ?>" />
	<input type="hidden" name="task"				value="" />
	<input type="hidden" name="boxchecked"			value="0" />
	<input type="hidden" name="filter_order"		value="<?php echo $this->lists['order']; ?>" />
	<input type="hidden" name="filter_order_Dir"	value="" />
	<?php echo JHTML::_('form.token'); ?>
</form>