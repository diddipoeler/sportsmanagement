<?php 
defined('_JEXEC') or die('Restricted access');
jimport('joomla.filesystem.file');

$user		= JFactory::getUser();
$userId		= $user->get('id');

?>
<script>
	function searchPerson(val)
	{
		var f = $('adminForm');
		if(f)
		{
			f.elements['search'].value=val;
			f.elements['search_mode'].value= 'matchfirst';
			f.submit();
		}
	}

	function onupdatebirthday(cal)
	{
		$($(cal.params.inputField).getProperty('cb')).setProperty('checked','checked');
	}
</script>
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
			<td align="center" colspan="4">
				<?php
				for ($i=65; $i < 91; $i++)
				{
					printf("<a href=\"javascript:searchPerson('%s')\">%s</a>&nbsp;&nbsp;&nbsp;&nbsp;",chr($i),chr($i));
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
					<th>
						<?php
						echo JHTML::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_PERSONS_F_NAME','pl.firstname',$this->lists['order_Dir'],$this->lists['order']);
						?>
					</th>
					<th>
						<?php
						echo JHTML::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_PERSONS_N_NAME','pl.nickname',$this->lists['order_Dir'],$this->lists['order']);
						?>
					</th>
					<th>
						<?php
						echo JHTML::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_PERSONS_L_NAME','pl.lastname',$this->lists['order_Dir'],$this->lists['order']);
						?>
					</th>
					<th><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_IMAGE'); ?>
					</th>
					<th>
						<?php
						echo JHTML::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_PERSONS_BIRTHDAY','pl.birthday',$this->lists['order_Dir'],$this->lists['order']);
						?>
					</th>
					<th>
						<?php
						echo JHTML::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_PERSONS_NATIONALITY','pl.country',$this->lists['order_Dir'],$this->lists['order']);
						?>
					</th>
					<th>
						<?php
						echo JHTML::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_PERSONS_POSITION','pl.position_id',$this->lists['order_Dir'],$this->lists['order']);
						?>
					</th>
					<th>
					<?php
						echo JHTML::_('grid.sort','JSTATUS','pl.published',$this->lists['order_Dir'],$this->lists['order']);
						?>
					</th>
					<th class="nowrap">
						<?php
						echo JHTML::_('grid.sort','JGRID_HEADING_ID','pl.id',$this->lists['order_Dir'],$this->lists['order']);
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
					$row=&$this->items[$i];
					if (($row->firstname != '!Unknown') && ($row->lastname != '!Player')) // Ghostplayer for match-events
					{
						$link       = JRoute::_('index.php?option=com_sportsmanagement&task=person.edit&id='.$row->id);
						$checked    = JHTML::_('grid.checkedout',$row,$i);
                        $canCheckin	= $user->authorise('core.manage','com_checkin') || $row->checked_out == $userId || $row->checked_out == 0;
						$is_checked = JTable::isCheckedOut($this->user->get('id'),$row->checked_out);
                        $published  = JHTML::_('grid.published',$row,$i, 'tick.png','publish_x.png','persons.');
						?>
						<tr class="<?php echo "row$k"; ?>">
							<td class="center"><?php echo $this->pagination->getRowOffset($i); ?></td>
							<td class="center"><?php echo $checked; ?></td>
							<?php
							if ($is_checked)
							{
								$inputappend=' disabled="disabled" ';
								?><td class="center">
                                	<?php if ($row->checked_out) : ?>
						<?php echo JHTML::_('grid.checkedout', $row, $i, $row->checked_out_time, 'persons.', $canCheckin); ?>
					<?php endif; ?>
                    </td><?php
							}
							else
							{
								$inputappend='';
								?>
								<td class="center">
									<a href="<?php echo $link; ?>">
										<?php
										$imageTitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_EDIT_DETAILS');
										echo JHTML::_(	'image','administrator/components/com_sportsmanagement/assets/images/edit.png',
														$imageTitle,'title= "'.$imageTitle.'"');
										?>
									</a>
								</td>
								<?php
							}
							?>
							<td class="center">
								<input	<?php echo $inputappend; ?> type="text" size="15"
										class="inputbox" name="firstname<?php echo $row->id; ?>"
										value="<?php echo stripslashes(htmlspecialchars($row->firstname)); ?>"
										onchange="document.getElementById('cb<?php echo $i; ?>').checked=true" />
							</td>
							<td class="center">
								<input	<?php echo $inputappend; ?> type="text" size="15"
										class="inputbox" name="nickname<?php echo $row->id; ?>"
										value="<?php echo stripslashes(htmlspecialchars($row->nickname)); ?>"
										onchange="document.getElementById('cb<?php echo $i; ?>').checked=true" />
							</td>
							<td class="center">
								<input	<?php echo $inputappend; ?> type="text" size="15"
										class="inputbox" name="lastname<?php echo $row->id; ?>"
										value="<?php echo stripslashes(htmlspecialchars($row->lastname)); ?>"
										onchange="document.getElementById('cb<?php echo $i; ?>').checked=true" />
							</td>
							<td class="center">
								<?php
								if (empty($row->picture) || !JFile::exists(JPATH_SITE.DS.$row->picture))
								{
									$imageTitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_NO_IMAGE').$row->picture;
									echo JHTML::_(	'image','administrator/components/com_sportsmanagement/assets/images/delete.png',
													$imageTitle,'title= "'.$imageTitle.'"');
								}
								elseif ($row->picture == sportsmanagementHelper::getDefaultPlaceholder("player"))
								{
									$imageTitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_DEFAULT_IMAGE');
									echo JHTML::_(	'image','administrator/components/com_sportsmanagement/assets/images/information.png',
													$imageTitle,'title= "'.$imageTitle.'"');
								}
								else
								{
									$playerName = sportsmanagementHelper::formatName(null ,$row->firstname, $row->nickname, $row->lastname, 0);
									echo sportsmanagementHelper::getPictureThumb($row->picture, $playerName, 0, 21, 4);
								}
								?>
							</td>
							<td class="nowrap" class="center">
								<?php
								$append='';
								if ($row->birthday == '0000-00-00')
								{
									$append=' style="background-color:#FFCCCC;"';
								}
								if ($is_checked)
								{
									echo $row->birthday;
								}
								else
								{
									echo $this->calendar(	sportsmanagementHelper::convertDate($row->birthday),
															'birthday'.$row->id,
															'birthday'.$row->id,
															'%d-%m-%Y',
															'size="10" '.$append.' cb="cb'.$i.'"',
															'onupdatebirthday',
															$i);
								}
								?>
							</td>
							<td class="nowrap" class="center">
								<?php
								$append='';
								if (empty($row->country)){$append=' background-color:#FFCCCC;';}
								echo JHTMLSelect::genericlist(	$this->lists['nation'],
																'country'.$row->id,
																$inputappend.' class="inputbox" style="width:140px; '.$append.'" onchange="document.getElementById(\'cb'.$i.'\').checked=true"',
																'value',
																'text',
																$row->country);
								?>
							</td>
							<td class="nowrap" class="center">
								<?php
								$append='';
								if (empty($row->position_id)){$append=' background-color:#FFCCCC;';}
								echo JHTMLSelect::genericlist(	$this->lists['positions'],
																'position'.$row->id,
																$inputappend.'class="inputbox" style="width:140px; '.$append.'" onchange="document.getElementById(\'cb'.$i.'\').checked=true"',
																'value',
																'text',
																$row->position_id);
								?>
							</td>
							<td class="center"><?php echo $published; ?></td>
							<td class="center"><?php echo $row->id; ?></td>
						</tr>
						<?php
						$k=1 - $k;
					}
				}
				?>
			</tbody>
		</table>
	</div>
	<input type="hidden" name="search_mode" value="<?php echo $this->lists['search_mode'];?>" id="search_mode" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="" />
	<?php echo JHTML::_('form.token')."\n"; ?>
</form>