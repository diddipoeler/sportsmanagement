<?php 


defined('_JEXEC') or die('Restricted access');

JHtml::_('behavior.tooltip');
?>
<div id="table-responsive">
<!--	<fieldset class="adminform"> -->
		<legend><?php echo JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_TREETOS_TITLE','<i>','<i>'.$this->projectws->name.'</i>'); ?></legend>
		
			<?php
			$colspan= 11;
			?>
			<table class="<?php echo $this->table_data_class; ?>" >
				<thead>
					<tr>
						<th width="5" style="vertical-align: top; "><?php echo count($this->items).'/'.$this->pagination->total; ?></th>
						<th width="20" style="vertical-align: top; ">
							<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
						</th>
					<!--	<th width="20" style="vertical-align: top; ">&nbsp;</th>
						<th width="20" style="vertical-align: top; ">&nbsp;</th> -->
						<th class="title" nowrap="nowrap" style="vertical-align: top; "><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_TREETOS_NAME'); ?></th>
						<?php
						if ($this->projectws->project_type == 'DIVISIONS_LEAGUE')
						{
							$colspan++;
							?><th class="title" style="vertical-align:top; " nowrap="nowrap">
								<?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_TREETO_DIVISION');
									echo '<br>'.JHtml::_(	'select.genericlist',
														$this->lists['divisions'],
														'division',
														'class="inputbox" size="1" onchange="window.location.href=window.location.href.split(\'&division=\')[0]+\'&division=\'+this.value"',
														'value','text', $this->division);
								?>
							</th><?php
						}
						?>
						<th class="title" nowrap="nowrap" style="vertical-align: top; "><?php echo JTEXT::_('COM_SPORTSMANAGEMENT_ADMIN_TREETOS_DEPTH'); ?></th>
			
						<th style="text-align: center; vertical-align: top; "><?php echo JTEXT::_('COM_SPORTSMANAGEMENT_ADMIN_TREETOS_HIDE'); ?></th>
						<th width="1%" nowrap="nowrap" style="vertical-align: top; "><?php echo JTEXT::_('JSTATUS'); ?></th>
						<th class="title" nowrap="nowrap" style="vertical-align: top; "><?php echo JTEXT::_('JGRID_HEADING_ID'); ?></th>
					</tr>
				</thead>
				<tfoot><tr><td colspan="<?php echo $colspan; ?>"><?php echo $this->pagination->getListFooter(); ?></td></tr></tfoot>
				<tbody>
					<?php
					$k=0;
					for ($i=0,$n=count($this->items); $i < $n; $i++)
					{
						$row		= $this->items[$i];
						$checked	= JHtml::_('grid.checkedout',$row,$i,'id');
						//$published	= JHtml::_('grid.published',$row,$i,'tick.png','publish_x.png','treeto.');
                        $published = JHtml::_('grid.published',$row,$i,'tick.png','publish_x.png','treetos.');
					?>
						<tr class="<?php echo "row$k"; ?>">
							<td style="text-align:center; ">
								<?php
								echo $this->pagination->getRowOffset($i);
								?>
							</td>
							<td style="text-align:center; ">
								<?php
								echo $checked;
								?>
					<!--		</td>
							<td style="text-align:center;"> -->
								<a
									href="index.php?option=com_sportsmanagement&task=treeto.edit&id=<?php echo $row->id; ?>&pid=<?php echo $this->project_id; ?>">
									<?php
									echo JHtml::_(	'image','administrator/components/com_sportsmanagement/assets/images/edit.png',
													JText::_('COM_SPORTSMANAGEMENT_ADMIN_TREETOS_EDIT_DETAILS'),'title= "' .
													JText::_('COM_SPORTSMANAGEMENT_ADMIN_TREETOS_EDIT_DETAILS').'"');
									?>
								</a>
						<!--	</td>
							<td style="text-align:center;"> -->
								<?php
								if( $row->leafed == 0)
								{ ?>
								<a
									href="index.php?option=com_sportsmanagement&task=treetos.genNode&id=<?php echo $row->id; ?>&pid=<?php echo $this->project_id; ?>">
								<?php
									echo JHtml::_(	'image','administrator/components/com_sportsmanagement/assets/images/update.png',
													JText::_('COM_SPORTSMANAGEMENT_ADMIN_TREETOS_GENERATE'),'title= "' .
													JText::_('COM_SPORTSMANAGEMENT_ADMIN_TREETOS_GENERATE').'"');
									?>
								</a><?php
								}
								else
								{ ?>
								<a
									href="index.php?option=com_sportsmanagement&view=treetonodes&task=treetonode.display&tid=<?php echo $row->id; ?>&pid=<?php echo $this->project_id; ?>">
									<?php
									echo JHtml::_(	'image','administrator/components/com_sportsmanagement/assets/images/icon-16-Tree.png',
													JText::_('COM_SPORTSMANAGEMENT_ADMIN_TREETOS_EDIT_TREE'),'title= "' .
													JText::_('COM_SPORTSMANAGEMENT_ADMIN_TREETOS_EDIT_TREE').'"');
									?>
								</a>
								<?php
								}
								?>
							</td>
							<td style="text-align:center; ">
								<?php
								echo $row->name;
								?>
							</td>
							<?php
							if ($this->projectws->project_type == 'DIVISIONS_LEAGUE')
							{
								?>
								<td nowrap="nowrap" style="text-align:center;">
									<?php
									$append='';
									if ($row->division_id == 0)
									{
										$append=' style="background-color:#bbffff"';
									}
									echo JHtml::_(	'select.genericlist',
													$this->lists['divisions'],
													'division_id'.$row->id,
													$append.'class="inputbox" size="1" onchange="document.getElementById(\'cb' .
													$i.'\').checked=true"'.$append,
													'value','text',$row->division_id);
									?>
								</td>
								<?php
							}
							?>
							<td style="text-align:center; ">
								<?php
								echo $row->tree_i;
								?>
							</td>
					
							<td style="text-align:center; ">
								<?php
								echo $row->hide;
								?>
							</td>
							<td style="text-align:center; ">
								<?php
								echo $published;
								?>
							</td>
							<td style="text-align:center; ">
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
<!--	</fieldset> -->
</div>
			


