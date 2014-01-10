<?php 
defined('_JEXEC') or die('Restricted access');
$templatesToLoad = array('footer');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
JHtml::_('behavior.tooltip');
JHtml::_('behavior.modal');
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
			<td class="nowrap" align="right"><?php echo $this->lists['sportstypes'].'&nbsp;&nbsp;'; ?></td>
			<td class="nowrap" align="right"><?php echo $this->lists['leagues'].'&nbsp;&nbsp;'; ?></td>
			<td class="nowrap" align="right"><?php echo $this->lists['seasons'].'&nbsp;&nbsp;'; ?></td>
			<td class="nowrap" align="right"><?php echo $this->lists['state']; ?></td>
		</tr>
	</table>
	<div id="editcell">
		<table class="adminlist">
			<thead>
				<tr>
					<th width="1%"><?php echo JText::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM'); ?></th>
					<th width="5%" class="title">
						<input  type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
					</th>
					<th width="20">&nbsp;</th>
					<th class="title">
						<?php
						echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_NAME_OF_PROJECT','p.name',$this->lists['order_Dir'],$this->lists['order']);
						?>
					</th>
					<th class="title">
						<?php
						echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_LEAGUE','l.name',$this->lists['order_Dir'],$this->lists['order']);
						?>
					</th>
					<th class="title">
						<?php
						echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SEASON','s.name',$this->lists['order_Dir'],$this->lists['order']);
						?>
					</th>
					<th class="title">
						<?php
						echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SPORTSTYPE','st.name',$this->lists['order_Dir'],$this->lists['order']);
						?>
					</th>
					<th class="title">
						<?php
						echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_PROJECTTYPE','p.project_type',$this->lists['order_Dir'],$this->lists['order']);
						?>
					</th>
					<th width="5%" class="title">
						<?php
						echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_GAMES');
						?>
					</th>
					<th width="5%" class="title">
						<?php
						echo JHtml::_('grid.sort','JSTATUS','p.published',$this->lists['order_Dir'],$this->lists['order']);
						?>
					</th>
					<th width="10%" class="title">
						<?php
						echo JHtml::_('grid.sort','JGRID_HEADING_ORDERING','p.ordering',$this->lists['order_Dir'],$this->lists['order']);
						echo JHtml::_('grid.order', $this->items, 'filesave.png', 'projects.saveorder');
						?>
					</th>
					<th width="5%" class="title">
						<?php
						echo JHtml::_('grid.sort','JGRID_HEADING_ID','p.id',$this->lists['order_Dir'],$this->lists['order']);
						?>
					</th>
				</tr>
			</thead>
			<tfoot><tr><td colspan='12'><?php echo $this->pagination->getListFooter(); ?></td></tr></tfoot>
			<tbody>
				<?php
				$k=0;
				for ($i=0,$n=count($this->items); $i < $n; $i++)
				{
					$row =& $this->items[$i];

					$link=JRoute::_('index.php?option=com_sportsmanagement&task=project.edit&id='.$row->id);
					$link2=JRoute::_('index.php?option=com_sportsmanagement&view=projects&task=project.display&id='.$row->id);
					$link2panel=JRoute::_('index.php?option=com_sportsmanagement&task=project.edit&layout=panel&pid='.$row->id.'&stid='.$row->sports_type_id.'&id='.$row->id   );

					$checked    = JHtml::_('grid.checkedout',$row,$i);
					$published  = JHtml::_('grid.published',$row,$i,'tick.png','publish_x.png','projects.');
					?>
					<tr class="<?php echo "row$k"; ?>">
						<td class="center"><?php echo $this->pagination->getRowOffset($i); ?></td>
						<td width="5%" class="center"><?php echo $checked; ?></td>
						<?php
						if (JTable::isCheckedOut($this->user->get ('id'),$row->checked_out))
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
									<img src="<?php echo JUri::root();?>/administrator/components/com_sportsmanagement/assets/images/edit.png"
										 border="0"
										 alt="<?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_EDIT_DETAILS');?>"
										 title="<?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_EDIT_DETAILS');?>" />
								</a>
							</td>
							<?php
						}
						?>
						<td>
							<?php
							if (JTable::isCheckedOut($this->user->get('id'),$row->checked_out))
							{
								echo $row->name;
							}
							else
							{
								?><a href="<?php echo $link2panel; ?>"><?php echo $row->name; ?></a><?php
							}
							?>
						</td>
						<td><?php echo $row->league; ?></td>
						<td class="center"><?php echo $row->season; ?></td>
						<td class="center"><?php echo JText::_($row->sportstype); ?></td>
						<td class="center"><?php echo JText::_($row->project_type); ?></td>
						<td class="center">
							<?php if ($row->current_round): ?>
								<?php echo JHtml::link('index.php?option=com_sportsmanagement&view=matches&pid='.$row->id.'&rid='. $row->current_round,
								                       JHtml::image(JUri::root().'administrator/components/com_sportsmanagement/assets/images/icon-16-Matchdays.png', JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_GAMES_DETAILS'))); ?>
							<?php endif; ?>
						</td>
						<td class="center"><?php echo $published; ?></td>
						<td class="order">
							<span><?php echo $this->pagination->orderUpIcon($i,$i > 0 ,'projects.orderup','JLIB_HTML_MOVE_UP',true); ?></span>
							<span><?php echo $this->pagination->orderDownIcon($i,$n,$i < $n,'projects.orderdown','JLIB_HTML_MOVE_DOWN',true); ?></span>
							<?php $disabled=true ?  '' : 'disabled="disabled"';	?>
							<input type="text" name="order[]" size="5" value="<?php echo $row->ordering;?>" <?php echo $disabled ?> class="text_area" style="text-align: center" />
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

	
	<input type="hidden" name="task"				value="" />
	<input type="hidden" name="boxchecked"			value="0" />
	<input type="hidden" name="filter_order"		value="<?php echo $this->lists['order']; ?>" />
	<input type="hidden" name="filter_order_Dir"	value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
<?PHP
echo "<div>";
echo $this->loadTemplate('footer');
echo "</div>";
?>   