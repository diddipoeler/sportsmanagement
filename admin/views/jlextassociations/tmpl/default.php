<?php defined('_JEXEC') or die('Restricted access');

$user =& JFactory::getUser();

//Ordering allowed ?
$ordering=($this->lists['order'] == 'objassoc.ordering');

JHTML::_('behavior.tooltip');
JHTML::_('behavior.modal');
?>
<form action="<?php echo $this->request_url; ?>" method="post" name="adminForm" id="adminForm">
	<table>
		<tr>
			<td align="left" width="100%">
				<?php
				echo JText::_('JSEARCH_FILTER_LABEL');
				?>&nbsp;<input	type="text" name="search" id="search"
								value="<?php echo $this->lists['search']; ?>"
								class="text_area" onchange="document.adminForm.submit(); " />
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
					<th width="5" style="vertical-align: top; "><?php echo JText::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM'); ?></th>
					<th width="20" style="vertical-align: top; ">
						<input  type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->items); ?>);" />
					</th>
					<th width="20" style="vertical-align: top; ">&nbsp;</th>
					<th class="title" nowrap="nowrap" style="vertical-align: top; ">
						<?php
						echo JHTML::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_ASSOCIATIONS_NAME','objassoc.name',$this->lists['order_Dir'],$this->lists['order']);
						?>
					</th>
					<th class="title" nowrap="nowrap" style="vertical-align: top; ">
						<?php
						echo JHTML::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_ASSOCIATIONS_SHORT_NAME','objassoc.name',$this->lists['order_Dir'],$this->lists['order']);
						?>
					</th>					
					<th width="10%" class="title" style="vertical-align: top; ">
						<?php
						echo JHTML::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_ASSOCIATIONS_COUNTRY','objassoc.country',$this->lists['order_Dir'],$this->lists['order']);
						?>
					</th>
          
          <th width="5" style="vertical-align: top; "><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_ASSOCIATIONS_FLAG'); ?></th>
          
					<th width="85" nowrap="nowrap" style="vertical-align: top; ">
						<?php
						echo JHTML::_('grid.sort','JGRID_HEADING_ORDERING','objassoc.ordering',$this->lists['order_Dir'],$this->lists['order']);
						echo '<br />';
						//echo JHTML::_('grid.order',$this->items);
						echo JHTML::_('grid.order',$this->items, 'filesave.png', 'jlextassociations.saveorder');
						?>
					</th>
					<th width="20" style="vertical-align: top; ">
						<?php echo JHTML::_('grid.sort','JGRID_HEADING_ID','objassoc.id',$this->lists['order_Dir'],$this->lists['order']); ?>
					</th>
				</tr>
			</thead>
			<tfoot><tr><td colspan="9"><?php echo $this->pagination->getListFooter(); ?></td></tr></tfoot>
			<tbody>
				<?php
				$k=0;
				for ($i=0,$n=count($this->items); $i < $n; $i++)
				{
					$row =& $this->items[$i];
					$link=JRoute::_('index.php?option=com_sportsmanagement&task=jlextassociation.edit&id='.$row->id);
					$checked=JHTML::_('grid.checkedout',$row,$i);
					?>
					<tr class="<?php echo "row$k"; ?>">
						<td style="text-align:center; "><?php echo $this->pagination->getRowOffset($i); ?></td>
						<td style="text-align:center; "><?php echo $checked; ?></td>
						<?php
						if (JTable::isCheckedOut($this->user->get('id'),$row->checked_out))
						{
							$inputappend=' disabled="disabled"';
							?><td style="text-align:center; ">&nbsp;</td><?php
						}
						else
						{
							$inputappend='';
							?>
							<td style="text-align:center; ">
								<a href="<?php echo $link; ?>">
									<?php
									$imageTitle=JText::_('JL_ADMIN_ASSOCIATIONS_EDIT_DETAILS');
									echo JHTML::_(	'image','administrator/components/com_sportsmanagement/assets/images/edit.png',
													$imageTitle,'title= "'.$imageTitle.'"');
									?>
								</a>
							</td>
							<?php
						}
						?>
						<td><?php echo $row->name; ?></td>
						<td><?php echo $row->short_name; ?></td>
						<td style="text-align:center; "><?php echo Countries::getCountryFlag($row->country); ?></td>
						<td style="text-align:center; ">
            <?php
            $path = JURI::root().$row->assocflag;
          $attributes='';
					$html = '<img src="'.$path.'" alt="'.$row->name.'" ';
		      $html .= 'title="'.$row->name.'" '.$attributes.' />';
					echo $html;
            ?>
            </td>
            <td class="order">
							<span>
								<?php echo $this->pagination->orderUpIcon($i,$i > 0,'jlextassociation.orderup','JLIB_HTML_MOVE_UP',$ordering); ?>
							</span>
							<span>
								<?php echo $this->pagination->orderDownIcon($i,$n,$i < $n,'jlextassociation.orderdown','JLIB_HTML_MOVE_DOWN',$ordering); ?>
								<?php $disabled=true ?	'' : 'disabled="disabled"'; ?>
							</span>
							<input	type="text" name="order[]" size="5" value="<?php echo $row->ordering;?>" <?php echo $disabled; ?>
									class="text_area" style="text-align: center" />
						</td>
						<td style="text-align:center; "><?php echo $row->id; ?></td>
					</tr>
					<?php
					$k=1 - $k;
				}
				?>
			</tbody>
		</table>
	</div>
	
	
  
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="" />
	<?php echo JHTML::_('form.token')."\n"; ?>
</form>