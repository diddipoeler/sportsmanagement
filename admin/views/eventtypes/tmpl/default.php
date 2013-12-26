<?php defined('_JEXEC') or die('Restricted access');

//Ordering allowed ?
$ordering=($this->lists['order'] == 'obj.ordering');

JHtml::_('behavior.tooltip');
JHtml::_('behavior.modal');
?>
<form action="<?php echo $this->request_url; ?>" method="post" id="adminForm" name="adminForm">
	<table>
		<tr>
			<td align='left' width='100%'>
				<?php
				echo JText::_('JSEARCH_FILTER_LABEL');
				?>&nbsp;<input	type="text" name="search" id="search"
								value="<?php echo $this->lists['search']; ?>"
								class="text_area" onchange="$('adminForm').submit();" />
				<button onclick="this.form.submit(); "><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
				<button onclick="document.getElementById('search').value='';this.form.submit(); ">
					<?php
					echo JText::_('JSEARCH_FILTER_CLEAR');
					?>
				</button>
			</td>
			<td nowrap='nowrap' align='right'><?php echo $this->lists['sportstypes'].'&nbsp;&nbsp;'; ?></td>
			<td nowrap='nowrap'><?php echo $this->lists['state']; ?></td>
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
					<th>
						<?php
						echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_EVENTS_STANDARD_NAME_OF_EVENT','obj.name',$this->lists['order_Dir'],$this->lists['order']);
						?>
					</th>
					<th>
						<?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_EVENTS_TRANSLATION'); ?>
					</th>
					<th width="10%">
						<?php
						echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_EVENTS_ICON','obj.icon',$this->lists['order_Dir'],$this->lists['order']);
						?>
					</th>
					<th width="10%">
						<?php
						echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_EVENTS_SPORTSTYPE','obj.sports_type_id',$this->lists['order_Dir'],$this->lists['order']);
						?>
					</th>
					<th width="1%">
						<?php
						echo JHtml::_('grid.sort','JSTATUS','obj.published',$this->lists['order_Dir'],$this->lists['order']);
						?>
					</th>
					<th width="10%">
						<?php
						echo JHtml::_('grid.sort','JGRID_HEADING_ORDERING','obj.ordering',$this->lists['order_Dir'],$this->lists['order']);
						echo JHtml::_('grid.order',$this->items, 'filesave.png', 'eventtypes.saveorder');
						?>
					</th>
					<th width="5%">
						<?php
						echo JHtml::_('grid.sort','JGRID_HEADING_ID','obj.id',$this->lists['order_Dir'],$this->lists['order']);
						?>
					</th>
				</tr>
			</thead>
			<tfoot><tr><td colspan="10"><?php echo $this->pagination->getListFooter(); ?></td></tr></tfoot>
			<tbody>
				<?php
				$k=0;
				for ($i=0,$n=count($this->items); $i < $n; $i++)
				{
					$row=&$this->items[$i];

					$link=JRoute::_('index.php?option=com_sportsmanagement&task=eventtype.edit&id='.$row->id);
					$checked=JHtml::_('grid.checkedout',$row,$i);
					$published=JHtml::_('grid.published',$row,$i,'tick.png','publish_x.png','eventtype.');
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
									$imageTitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_EVENTS_EDIT_DETAILS');
									echo JHtml::_(	'image','administrator/components/com_sportsmanagement/assets/images/edit.png',
													$imageTitle, 'title= "'.$imageTitle.'"');
									?>
								</a>
							</td>
							<?php
						}
						?>
						<td><?php echo $row->name; ?></td>
						<td>
							<?php
							if ($row->name == JText::_($row->name))
							{
								echo '&nbsp;';
							}
							else
							{
								echo JText::_($row->name);
							}
							?>
						</td>
						<td class="center">
							<?php
							$desc=JText::_($row->name);
							echo sportsmanagementHelper::getPictureThumb($row->icon, $desc, 0, 21, 4);
							?>
						</td>
						<td class="center">
							<?php
							echo JText::_(sportsmanagementHelper::getSportsTypeName($row->sports_type_id));
							?>
						</td>
						<td class="center">
							<?php
							echo $published;
							?>
						</td>
						<td class="order">
							<span>
								<?php
								echo $this->pagination->orderUpIcon($i,$i > 0,'eventtypes.orderup','COM_SPORTSMANAGEMENT_GLOBAL_ORDER_UP',$ordering);
								?>
							</span>
							<span>
								<?php
								echo $this->pagination->orderDownIcon($i,$n,$i < $n,'eventtypes.orderdown','COM_SPORTSMANAGEMENT_GLOBAL_ORDER_DOWN',$ordering);
								?>
								<?php
								$disabled=true ? '' : 'disabled="disabled"';
								?>
							</span>
							<input	type="text" name="order[]" size="5" value="<?php echo $row->ordering;?>" <?php echo $disabled ?>
									class="text_area" style="text-align: center" />
						</td>
						<td class="center">
							<?php
							echo $row->id;
							?>
						</td>
					</tr>
					<?php
					$k=1 - $k;
				}
				?>
			</tbody>
		</table>
	</div>
	<input type="hidden" name="task"				value="" />
	<input type="hidden" name="boxchecked"			value="0" />
	<input type="hidden" name="filter_order"		value="<?php echo $this->lists['order']; ?>" />
	<input type="hidden" name="filter_order_Dir"	value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>