<?php defined('_JEXEC') or die('Restricted access');
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
</script>
<form action="<?php echo $this->request_url; ?>" method="post" id="adminForm">
	<fieldset class="adminform">
		<legend>
			<?php
			switch ($this->type)
			{
				case 2	:
							{
								echo JText::sprintf('Assign Person(s) as Referee to project [%1$s]','<i>'.$this->prj_name.'</i>');
							}
							break;

				case 1	:
							{
								echo JText::sprintf(	'Assign Person(s) as Staff to [%1$s] in project [%2$s]',
														'<i>'.$this->team_name.'</i>',
														'<i>'.$this->prj_name.'</i>');
							}
							break;
				default	:
				case 0	:
							{
								echo JText::sprintf(	'Assign Person(s) as Player to [%1$s] in project [%2$s]',
														'<i>'.$this->team_name.'</i>',
														'<i>'.$this->prj_name.'</i>');
							}
							break;
			}
			?>
		</legend>
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
			<table class="<?php echo $this->table_data_class; ?>">
				<thead>
					<tr>
					<th width="5" style="vertical-align: top; "><?php echo JText::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM'); ?></th>
					<th width="20" style="vertical-align: top; ">
						<input  type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
					</th>
						<th class="title" class="nowrap">
							<?php echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_PERSONS_L_NAME','pl.lastname',$this->sortDirection,$this->sortColumn); ?>
						</th>
						<th class="title" class="nowrap">
							<?php echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_PERSONS_F_NAME','pl.firstname',$this->sortDirection,$this->sortColumn); ?>
						</th>
						<th class="title" class="nowrap">
							<?php echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_PERSONS_N_NAME','pl.nickname',$this->sortDirection,$this->sortColumn); ?>
						</th>
						
            <th class="title" class="nowrap">
							<?php echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_PERSONS_INFO','pl.info',$this->sortDirection,$this->sortColumn); ?>
						</th>
						
						<th class="title" class="nowrap"><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_IMAGE'); ?></th>
						<th class="title" class="nowrap">
							<?php echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_PERSONS_BIRTHDAY','pl.birthday',$this->sortDirection,$this->sortColumn); ?>
						</th>
						<th class="title" class="nowrap">
							<?php echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_PERSONS_NATIONALITY','pl.country',$this->sortDirection,$this->sortColumn); ?>
						</th>
						<th width="1%" class="nowrap">
							<?php echo JHtml::_('grid.sort','JGRID_HEADING_ID','pl.id',$this->sortDirection,$this->sortColumn); ?>
						</th>

            					
						
					</tr>
				</thead>
				<tfoot><tr><td colspan="10"><?php echo $this->pagination->getListFooter(); ?></td></tr></tfoot>
				<tbody>
					<?php
					$k=0;
					for ($i=0,$n=count($this->items); $i < $n; $i++)
					{
						$row =& $this->items[$i];
						if (($row->firstname != '!Unknown') && ($row->lastname != '!Player')) // Ghostplayer for match-events
						{
							$checked=JHtml::_('grid.checkedout',$row,$i);
							?>
							<tr class="<?php echo "row$k"; ?>">
								<td class="center"><?php echo $this->pagination->getRowOffset($i); ?></td>
								<td class="center"><?php echo $checked; ?></td>
								<td><?php echo $row->lastname; ?></td>
								<td><?php echo $row->firstname; ?></td>
								<td><?php echo $row->nickname; ?></td>
								
								<td><?php echo $row->info; ?></td>
								
								<td class="center">
									<?php
									if ($row->picture == '')
									{
										$imageTitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_NO_IMAGE');
										echo JHtml::_(	'image','administrator/components/com_sportsmanagement/assets/images/delete.png',
														$imageTitle,'title= "'.$imageTitle.'"');

									}
									elseif ($row->picture == sportsmanagementHelper::getDefaultPlaceholder("player"))
									{
											$imageTitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_DEFAULT_IMAGE');
											echo JHtml::_(	'image','administrator/components/com_sportsmanagement/assets/images/information.png',
															$imageTitle,'title= "'.$imageTitle.'"');
									}
									elseif ($row->picture == !'')
									{
									$playerName = sportsmanagementHelper::formatName(null ,$row->firstname, $row->nickname, $row->lastname, 0);
									echo sportsmanagementHelper::getPictureThumb($row->picture, $playerName, 0, 21, 4);
									}
									?>
								</td>
								<td class="nowrap" class="center"><?php echo sportsmanagementHelper::convertDate($row->birthday); ?></td>
								<td class="nowrap" class="center"><?php echo JSMCountries::getCountryFlag($row->country); ?></td>
								<td align="center"><?php echo $row->id; ?></td>			
							</tr>
							<?php
							$k=1 - $k;
						}
					}
					?>
				</tbody>
			</table>
		</div>
	</fieldset>
	<input type="hidden" name="search_mode"			value="<?php echo $this->lists['search_mode']; ?>" id="search_mode" />
	<input type="hidden" name="task"				value="" />
	<input type="hidden" name="boxchecked"			value="0" />
	<input type="hidden" name="type"				value="<?php echo $this->type; ?>" />
	<input type="hidden" name="project_team_id"		value="<?php echo $this->project_team_id; ?>" />
	<input type="hidden" name="filter_order"		value="<?php echo $this->sortColumn; ?>" />
	<input type="hidden" name="filter_order_Dir"	value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>