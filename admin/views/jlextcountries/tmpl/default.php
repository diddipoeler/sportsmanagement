<?php defined('_JEXEC') or die('Restricted access');

//Ordering allowed ?
$ordering=($this->lists['order'] == 'objcountry.ordering');

JHTML::_('behavior.tooltip');
JHTML::_('behavior.modal');
?>
<form action="<?php echo $this->request_url; ?>" method="post" id="adminForm" name="adminForm">
	<table>
		<tr>
			<td align="left" width="100%">
				<?php
				echo JText::_('JSEARCH_FILTER_LABEL');
				?>&nbsp;<input	type="text" name="search" id="search"
								value="<?php echo $this->lists['search']; ?>"
								class="text_area" onchange="$('adminForm').submit(); " />
				<button onclick="this.form.submit(); "><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
				<button onclick="document.getElementById('search').value='';this.form.submit(); ">
					<?php
					echo JText::_('JSEARCH_FILTER_CLEAR');
					?>
				</button>
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
					<th>
						<?php
						echo JHTML::_('grid.sort','COM_SPORTSMANAGEMENT_GLOBAL_NAME','objcountry.name',$this->lists['order_Dir'],$this->lists['order']);
						?>
					</th>
					<th width="5" style="vertical-align: top; "><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_COUNTRY_FLAG'); ?></th>
					<th>
						<?php
						echo JHTML::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_COUNTRY_EDIT_ALPHA2','objcountry.alpha2',$this->lists['order_Dir'],$this->lists['order']);
						?>
					</th>
					<th width="10%">
						<?php
						echo JHTML::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_COUNTRY_EDIT_ALPHA3','objcountry.alpha3',$this->lists['order_Dir'],$this->lists['order']);
						?>
					</th>
					
					<th width="10%">
						<?php
						echo JHTML::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_COUNTRY_EDIT_ITU','objcountry.itu',$this->lists['order_Dir'],$this->lists['order']);
						?>
					</th>
					<th width="10%">
						<?php
						echo JHTML::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_COUNTRY_EDIT_FIPS','objcountry.fips',$this->lists['order_Dir'],$this->lists['order']);
						?>
					</th>
					<th width="10%">
						<?php
						echo JHTML::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_COUNTRY_EDIT_IOC','objcountry.ioc',$this->lists['order_Dir'],$this->lists['order']);
						?>
					</th>
					<th width="10%">
						<?php
						echo JHTML::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_COUNTRY_EDIT_FIFA','objcountry.fifa',$this->lists['order_Dir'],$this->lists['order']);
						?>
					</th>
					<th width="10%">
						<?php
						echo JHTML::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_COUNTRY_EDIT_DS','objcountry.ds',$this->lists['order_Dir'],$this->lists['order']);
						?>
					</th>
					<th width="10%">
						<?php
						echo JHTML::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_COUNTRY_EDIT_WMO','objcountry.wmo',$this->lists['order_Dir'],$this->lists['order']);
						?>
					</th>
					
					<th width="10%">
						<?php
						echo JHTML::_('grid.sort','JGRID_HEADING_ORDERING','objcountry.ordering',$this->lists['order_Dir'],$this->lists['order']);
						echo JHTML::_('grid.order',$this->items, 'filesave.png', 'jlextcountries.saveorder');
						?>
					</th>
					<th width="20">
						<?php echo JHTML::_('grid.sort','JGRID_HEADING_ID','objcountry.id',$this->lists['order_Dir'],$this->lists['order']); ?>
					</th>
				</tr>
			</thead>
			<tfoot><tr><td colspan="15"><?php echo $this->pagination->getListFooter(); ?></td></tr></tfoot>
			<tbody>
				<?php
				$k=0;
				for ($i=0,$n=count($this->items); $i < $n; $i++)
				{
					$row =& $this->items[$i];
					$link=JRoute::_('index.php?option=com_sportsmanagement&task=jlextcountry.edit&id='.$row->id);
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
									$imageTitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_COUNTRIES_EDIT_DETAILS');
									echo JHTML::_(	'image','administrator/components/com_sportsmanagement/assets/images/edit.png',
													$imageTitle,'title= "'.$imageTitle.'"');
									?>
								</a>
							</td>
							<?php
						}
						?>
						<td><?php echo $row->name; ?></td>
						<td><?php echo JHTML::_(	'image',$row->picture,
													JText::_($row->name),'title= "'.JText::_($row->name).'"');; ?></td>
						<td><?php echo $row->alpha2; ?></td>
						<td><?php echo $row->alpha3; ?></td>
						
						<td><?php echo $row->itu; ?></td>
						<td><?php echo $row->fips; ?></td>
						<td><?php echo $row->ioc; ?></td>
						<td><?php echo $row->fifa; ?></td>
						<td><?php echo $row->ds; ?></td>
						<td><?php echo $row->wmo; ?></td>
						
						<td class="order">
							<span>
								<?php echo $this->pagination->orderUpIcon($i,$i > 0,'jlextcountry.orderup','JGRID_HEADING_ORDERING_UP',$ordering); ?>
							</span>
							<span>
								<?php echo $this->pagination->orderDownIcon($i,$n,$i < $n,'jlextcountry.orderdown','JGRID_HEADING_ORDERING_DOWN',$ordering); ?>
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
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="" />
	<?php echo JHTML::_('form.token')."\n"; ?>
</form>