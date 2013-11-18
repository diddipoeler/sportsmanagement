<?php defined('_JEXEC') or die('Restricted access');

//Ordering allowed ?
$ordering=($this->lists['order'] == 'a.ordering');

JHTML::_('behavior.tooltip');
JHTML::_('behavior.modal');


?>
<script>

	function searchClub(val,key)
	{
		var f = $('adminForm');
		if(f)
		{
		f.elements['search'].value=val;
		
		f.submit();
		}
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
					printf("<a href=\"javascript:searchClub('%s')\">%s</a>&nbsp;&nbsp;&nbsp;&nbsp;",chr($i),chr($i));
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
					<th width="20"><input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" /></th>
					<th width="50">&nbsp;</th>
					<th class="title">
						<?php echo JHTML::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_CLUBS_NAME_OF_CLUB','a.name',$this->lists['order_Dir'],$this->lists['order']); ?>
					</th>
					<th>
						<?php echo JHTML::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_CLUBS_WEBSITE','a.website',$this->lists['order_Dir'],$this->lists['order']); ?>
					</th>
					<th width="20">
						<?php echo JHTML::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_CLUBS_L_LOGO','a.logo_big',$this->lists['order_Dir'],$this->lists['order']); ?>
					</th>
					<th width="20">
						<?php echo JHTML::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_CLUBS_M_LOGO','a.logo_middle',$this->lists['order_Dir'],$this->lists['order']); ?>
					</th>
					<th width="20">
						<?php echo JHTML::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_CLUBS_S_LOGO','a.logo_small',$this->lists['order_Dir'],$this->lists['order']); ?>
					</th>
					<th width="20">
						<?php echo JHTML::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_CLUBS_COUNTRY','a.country',$this->lists['order_Dir'],$this->lists['order']); ?>
					</th>
					<th width="10%">
						<?php
						echo JHTML::_('grid.sort','JGRID_HEADING_ORDERING','a.ordering',$this->lists['order_Dir'],$this->lists['order']);
						echo JHTML::_('grid.order',$this->items, 'filesave.png', 'clubs.saveorder');
						?>
					</th>
					<th width="1%">
						<?php echo JHTML::_('grid.sort','JGRID_HEADING_ID','a.id',$this->lists['order_Dir'],$this->lists['order']); ?>
					</th>
				</tr>
			</thead>
			<tfoot><tr><td colspan="11"><?php echo $this->pagination->getListFooter(); ?></td></tr></tfoot>
			<tbody>
				<?php
				$k=0;
				for ($i=0,$n=count($this->items); $i < $n; $i++)
				{
					$row =& $this->items[$i];
					$link=JRoute::_('index.php?option=com_sportsmanagement&task=club.edit&id='.$row->id);
					$link2=JRoute::_('index.php?option=com_sportsmanagement&view=teams&club_id='.$row->id);
					$checked= JHTML::_('grid.checkedout',$row,$i);
					?>
					<tr class="<?php echo "row$k"; ?>">
						<td class="center"><?php echo $this->pagination->getRowOffset($i); ?></td>
						<td class="center"><?php echo $checked; ?></td>
						<?php
                        $checkedOut	= !($row->checked_out == 0 || $row->checked_out == $this->user->get('id'));
						if ($checkedOut)
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
									$imageTitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_CLUBS_EDIT_DETAILS');
									echo JHTML::_(	'image','administrator/components/com_sportsmanagement/assets/images/edit.png',
													$imageTitle,'title= "'.$imageTitle.'"');
									?>
								</a>
                                <a href="<?php echo $link2; ?>">
									<?php
									$imageTitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_CLUBS_SHOW_TEAMS');
									echo JHTML::_(	'image','administrator/components/com_sportsmanagement/assets/images/icon-16-Teams.png',
													$imageTitle,'title= "'.$imageTitle.'"');
									?>
								</a>
							</td>
							<?php
						}
						?>
						<td><?php echo $row->name; ?></td>
						<td>
							<?php
							if ($row->website != ''){echo '<a href="'.$row->website.'" target="_blank">';}
							echo $row->website;
							if ($row->website != ''){echo '</a>';}
							?>
						</td>
						<td class="center">
							<?php
							if ($row->logo_big == '')
							{
								$imageTitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_CLUBS_NO_IMAGE');
								echo JHTML::_(	'image','administrator/components/com_sportsmanagement/assets/images/information.png',
												$imageTitle,'title= "'.$imageTitle.'"');

							}
							elseif ($row->logo_big == sportsmanagementHelper::getDefaultPlaceholder("clublogobig"))
							{
								$imageTitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_CLUBS_DEFAULT_IMAGE');
								echo JHTML::_(	'image','administrator/components/com_sportsmanagement/assets/images/information.png',
												$imageTitle,'title= "'.$imageTitle.'"');
							} else {
								if (JFile::exists(JPATH_SITE.DS.$row->logo_big)) {
									$imageTitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_CLUBS_CUSTOM_IMAGE');
									echo JHTML::_(	'image','administrator/components/com_sportsmanagement/assets/images/ok.png',
													$imageTitle,'title= "'.$imageTitle.'"');
								} else {
									$imageTitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_CLUBS_NO_IMAGE');
									echo JHTML::_(	'image','administrator/components/com_sportsmanagement/assets/images/delete.png',
													$imageTitle,'title= "'.$imageTitle.'"');
								}
							}
							?>
						</td>
						<td class="center">
							<?php
							if ($row->logo_middle == '')
							{
								$imageTitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_CLUBS_NO_IMAGE');
								echo JHTML::_(	'image','administrator/components/com_sportsmanagement/assets/images/information.png',
												$imageTitle,'title= "'.$imageTitle.'"');
							}
							elseif ($row->logo_middle == sportsmanagementHelper::getDefaultPlaceholder("clublogomedium"))
							{
								$imageTitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_CLUBS_DEFAULT_IMAGE');
								echo JHTML::_(	'image','administrator/components/com_sportsmanagement/assets/images/information.png',
												$imageTitle,'title= "'.$imageTitle.'"');
							} else {
								if (JFile::exists(JPATH_SITE.DS.$row->logo_middle)) {
									$imageTitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_CLUBS_CUSTOM_IMAGE');
									echo JHTML::_(	'image','administrator/components/com_sportsmanagement/assets/images/ok.png',
													$imageTitle,'title= "'.$imageTitle.'"');
								} else {
									$imageTitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_CLUBS_NO_IMAGE');
									echo JHTML::_(	'image','administrator/components/com_sportsmanagement/assets/images/delete.png',
													$imageTitle,'title= "'.$imageTitle.'"');
								}
							}
							?>
						</td>
						<td class="center">
							<?php
							if ($row->logo_small == '')
							{
								$imageTitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_CLUBS_NO_IMAGE');
								echo JHTML::_(	'image','administrator/components/com_sportsmanagement/assets/images/information.png',
												$imageTitle,'title= "'.$imageTitle.'"');
							}
							elseif ($row->logo_small == sportsmanagementHelper::getDefaultPlaceholder("clublogosmall"))
							{
								$imageTitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_CLUBS_DEFAULT_IMAGE');
								echo JHTML::_(	'image','administrator/components/com_sportsmanagement/assets/images/information.png',
				  								$imageTitle,'title= "'.$imageTitle.'"');
							} else {
								if (JFile::exists(JPATH_SITE.DS.$row->logo_small)) {
									$imageTitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_CLUBS_CUSTOM_IMAGE');
									echo JHTML::_(	'image','administrator/components/com_sportsmanagement/assets/images/ok.png',
													$imageTitle,'title= "'.$imageTitle.'"');
								} else {
									$imageTitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_CLUBS_NO_IMAGE');
									echo JHTML::_(	'image','administrator/components/com_sportsmanagement/assets/images/delete.png',
													$imageTitle,'title= "'.$imageTitle.'"');
								}
							}
							?>
						</td>
						<td class="center"><?php echo Countries::getCountryFlag($row->country); ?></td>
						<td class="order">
							<span>
								<?php echo $this->pagination->orderUpIcon($i,$i > 0 ,'clubs.orderup','JLIB_HTML_MOVE_UP',true); ?>
							</span>
							<span>
								<?php echo $this->pagination->orderDownIcon($i,$n,$i < $n,'clubs.orderdown','JLIB_HTML_MOVE_DOWN',true);
								$disabled=true ?  '' : 'disabled="disabled"';
								?>
							</span>
							<input  type="text" name="order[]" size="5" value="<?php echo $row->ordering;?>" <?php echo $disabled; ?>
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
	<input type="hidden" name="search_mode" value="<?php echo $this->lists['search_mode']; ?>" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="" />
	<?php echo JHTML::_('form.token')."\n"; ?>
</form>