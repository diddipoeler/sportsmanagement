<?php 
defined('_JEXEC') or die('Restricted access');
$templatesToLoad = array('footer');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
JHtml::_('behavior.tooltip');
JHtml::_('behavior.modal');
?>

<form action="<?php echo $this->request_url; ?>" method="post" id="adminForm" name="adminForm">
	<div id="editcell">
		<fieldset class="adminform">
			<legend><?php echo JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_LEGEND','<i>'.$this->project->name.'</i>'); ?></legend>
			<table class="adminlist">
				<thead>
					<tr>
						<th width="5"><?php echo JText::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM'); ?></th>
						<th>
							<?php echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_STANDARD_NAME_OF_POSITION','po.name',$this->lists['order_Dir'],$this->lists['order']); ?>
						</th>
						<th><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_TRANSLATION'); ?></th>
						<th>
							<?php echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_PARENTNAME','po.parent_id',$this->lists['order_Dir'],$this->lists['order']); ?>
						</th>
						<th width="20">
							<?php echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_PLAYER_POSITION','persontype',$this->lists['order_Dir'],$this->lists['order']); ?>
						</th>
						<th width="20">
							<?php echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_STAFF_POSITION','persontype',$this->lists['order_Dir'],$this->lists['order']); ?>
						</th>
						<th width="20">
							<?php echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_REFEREE_POSITION','persontype',$this->lists['order_Dir'],$this->lists['order']); ?>
						</th>
						<th width="20">
							<?php echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_CLUBSTAFF_POSITION','persontype',$this->lists['order_Dir'],$this->lists['order']); ?>
						</th>
						<?php
						/*
						?>
						<th class="title">
							<?php echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_SPORTSTYPE','po.sports_type_id',$this->lists['order_Dir'],$this->lists['order']); ?>
						</th>
						<?php
						*/
						?>
						<th width="5%"><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_HAS_EVENTS'); ?></th>
						<th width="5%"><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_HAS_STATS'); ?></th>
						<th width="1%">
							<?php echo JHtml::_('grid.sort','PID','po.id',$this->lists['order_Dir'],$this->lists['order']); ?>
						</th>
						<th width="1%">
							<?php echo JHtml::_('grid.sort','JGRID_HEADING_ID','pt.id',$this->lists['order_Dir'],$this->lists['order']); ?>
						</th>
					</tr>
				</thead>
				<tfoot><tr><td colspan='12'><?php echo $this->pagination->getListFooter(); ?></td></tr></tfoot>
				<tbody>
					<?php
					$k=0;
					for ($i=0, $n=count($this->positiontool); $i < $n; $i++)
					{
						$row =& $this->positiontool[$i];
						$imageFileOk='administrator/components/com_sportsmanagement/assets/images/ok.png';
						?>
						<tr class="<?php echo "row$k"; ?>">
							<td class="center"><?php echo $this->pagination->getRowOffset($i); ?></td>
							<td><?php echo $row->name; ?></td>
							<td><?php if ($row->name != JText::_($row->name)){echo JText::_($row->name);} ?></td>
							<td><?php echo JText::_($row->parent_name); ?></td>
							<td class="center">
								<?php
								if ($row->persontype == 1)
								{
									$imageTitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_PLAYER_POSITION');
									$imageParams='title= "'.$imageTitle.'"';
									echo JHtml::image($imageFileOk,$imageTitle,$imageParams);
								}
								?>
							</td>
							<td class="center">
								<?php
								if ($row->persontype == 2)
								{
									$imageTitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_STAFF_POSITION');
									$imageParams='title= "'.$imageTitle.'"';
									echo JHtml::image($imageFileOk,$imageTitle,$imageParams);
								}
								?>
							</td>
							<td class="center">
								<?php
								if ($row->persontype == 3)
								{
									$imageTitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_REFEREE_POSITION');
									$imageParams='title= "'.$imageTitle.'"';
									echo JHtml::image($imageFileOk,$imageTitle,$imageParams);
								}
								?>
							</td>
							<td class="center">
								<?php
								if ($row->persontype == 4)
								{
									$imageTitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_CLUBSTAFF_POSITION');
									$imageParams='title= "'.$imageTitle.'"';
									echo JHtml::image($imageFileOk,$imageTitle,$imageParams);
								}
								?>
							</td>
							<?php
							/*
							?>
							<td class="center"><?php echo JText::_(sportsmanagementHelper::getSportsTypeName($row->sports_type_id)); ?></td>
							<?php
							*/
							?>
							<td class="center">
								<?php
								if ($row->countEvents == 0)
								{
									$imageFile='administrator/components/com_sportsmanagement/assets/images/error.png';
									$imageTitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_NO_EVENTS');
									$imageParams='title= "'.$imageTitle.'"';
									echo JHtml::image($imageFile,$imageTitle,$imageParams);
								}
								else
								{
									$imageTitle=JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_NR_EVENTS',$row->countEvents);
									$imageParams='title= "'.$imageTitle.'"';
									echo JHtml::image($imageFileOk,$imageTitle,$imageParams);
								}
								?>
							</td>
							<td class="center">
								<?php
								if ($row->countStats == 0)
								{
									$imageFile='administrator/components/com_sportsmanagement/assets/images/error.png';
									$imageTitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_NO_STATISTICS');
									$imageParams='title= "'.$imageTitle.'"';
									echo JHtml::image($imageFile,$imageTitle,$imageParams);
								}
								else
								{
									$imageTitle=JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_NR_STATISTICS',$row->countStats);
									$imageParams='title= "'.$imageTitle.'"';
									echo JHtml::image($imageFileOk,$imageTitle,$imageParams);
								}
								?>
							</td>
							<td class="center"><?php echo $row->id; ?></td>
							<td class="center"><?php echo $row->positiontoolid; ?></td>
						</tr>
						<?php
						$k=1 - $k;
					}
					?>
				</tbody>
			</table>
		</fieldset>
	</div>
	
    <input type="hidden" name="pid" value="<?php echo $this->project->id; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="" />
	<?php echo JHtml::_('form.token')."\n"; ?>
</form>
<?PHP
echo "<div>";
echo $this->loadTemplate('footer');
echo "</div>";
?>   