<?php defined('_JEXEC') or die('Restricted access');

//Ordering allowed ?
$ordering=($this->lists['order'] == 'obj.ordering');

JHtml::_('behavior.tooltip');
JHtml::_('behavior.modal');
$templatesToLoad = array('footer');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
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
            <td class='nowrap' align='right'><?php echo $this->lists['nation2'].'&nbsp;&nbsp;'; ?></td>
            <td class="nowrap" align="right"><?php echo $this->lists['sportstypes'].'&nbsp;&nbsp;'; ?></td>
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
						echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_AGEGROUPS_NAME','obj.name',$this->lists['order_Dir'],$this->lists['order']);
						?>
					</th>
					<th>
						<?php
						echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_AGEGROUPS_ALIAS','obj.alias',$this->lists['order_Dir'],$this->lists['order']);
						?>
					</th>
                    
                    <th>
						<?php
						echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_AGEGROUP_AGE_FROM','obj.age_from',$this->lists['order_Dir'],$this->lists['order']);
						?>
					</th>
                    <th>
						<?php
						echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_AGEGROUP_AGE_TO','obj.age_to',$this->lists['order_Dir'],$this->lists['order']);
						?>
					</th>
                    <th>
						<?php
						echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_AGEGROUP_SHORT_DEADLINE_DAY','obj.deadline_day',$this->lists['order_Dir'],$this->lists['order']);
						?>
					</th>
                    <th width="10%">
						<?php
						echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_AGEGROUP_COUNTRY','obj.country',$this->lists['order_Dir'],$this->lists['order']);
						?>
					</th>
                    
                    <th><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_IMAGE'); ?>
					</th>
                    
					<th class="title">
						<?php
						echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_AGEGROUP_SPORTSTYPE','obj.sportstype_id',$this->lists['order_Dir'],$this->lists['order']);
						?>
					</th>
                    
					<th width="10%">
						<?php
						echo JHtml::_('grid.sort','JGRID_HEADING_ORDERING','obj.ordering',$this->lists['order_Dir'],$this->lists['order']);
						echo JHtml::_('grid.order',$this->items, 'filesave.png', 'agegroups.saveorder');
						?>
					</th>
					<th width="20">
						<?php echo JHtml::_('grid.sort','JGRID_HEADING_ID','obj.id',$this->lists['order_Dir'],$this->lists['order']); ?>
					</th>
				</tr>
			</thead>
			<tfoot><tr><td colspan="13"><?php echo $this->pagination->getListFooter(); ?></td></tr></tfoot>
			<tbody>
				<?php
				$k=0;
				for ($i=0,$n=count($this->items); $i < $n; $i++)
				{
					$row =& $this->items[$i];
					$link = JRoute::_('index.php?option=com_sportsmanagement&task=agegroup.edit&id='.$row->id);
					$checked = JHtml::_('grid.checkedout',$row,$i);
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
									$imageTitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_AGEGROUPS_EDIT_DETAILS');
									echo JHtml::_(	'image','administrator/components/com_sportsmanagement/assets/images/edit.png',
													$imageTitle,'title= "'.$imageTitle.'"');
									?>
								</a>
							</td>
							<?php
						}
						?>
						<td><?php echo $row->name; ?></td>
						<td><?php echo $row->alias; ?></td>
                        
                        <td><?php echo $row->age_from; ?></td>
                        <td><?php echo $row->age_to; ?></td>
                        <td><?php echo $row->deadline_day; ?></td>
						<td class="center"><?php echo JSMCountries::getCountryFlag($row->country); ?></td>
                        
                        <td class="center">
								<?php
								if (empty($row->picture) || !JFile::exists(JPATH_SITE.DS.$row->picture))
								{
									$imageTitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_NO_IMAGE').$row->picture;
									echo JHtml::_(	'image','administrator/components/com_sportsmanagement/assets/images/delete.png',
													$imageTitle,'title= "'.$imageTitle.'"');
								}
								elseif ($row->picture == sportsmanagementHelper::getDefaultPlaceholder("player"))
								{
									$imageTitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_DEFAULT_IMAGE');
									echo JHtml::_(	'image','administrator/components/com_sportsmanagement/assets/images/information.png',
													$imageTitle,'title= "'.$imageTitle.'"');
								}
								else
								{
									//$playerName = sportsmanagementHelper::formatName(null ,$row->firstname, $row->nickname, $row->lastname, 0);
									//echo sportsmanagementHelper::getPictureThumb($row->picture, $playerName, 0, 21, 4);
?>                                    
<a href="<?php echo JURI::root().$row->picture;?>" title="<?php echo $row->name;?>" class="modal">
<img src="<?php echo JURI::root().$row->picture;?>" alt="<?php echo $row->name;?>" width="20" />
</a>
<?PHP
								}
								?>
							</td>
                            
                        <td class="center"><?php echo JText::_($row->sportstype); ?></td>
                        
						<td class="order">
							<span>
								<?php echo $this->pagination->orderUpIcon($i,$i > 0,'agegroup.orderup','JLIB_HTML_MOVE_UP',$ordering); ?>
							</span>
							<span>
								<?php echo $this->pagination->orderDownIcon($i,$n,$i < $n,'agegroup.orderdown','JLIB_HTML_MOVE_DOWN',$ordering); ?>
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
	<?php echo JHtml::_('form.token')."\n"; ?>
</form>
<?PHP
echo "<div>";
echo $this->loadTemplate('footer');
echo "</div>";
?>   