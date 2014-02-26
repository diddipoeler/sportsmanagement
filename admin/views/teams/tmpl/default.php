<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license                This file is part of SportsManagement.
*
* SportsManagement is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* SportsManagement is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with SportsManagement.  If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von SportsManagement.
*
* SportsManagement ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es nützlich sein wird, aber
* OHNE JEDE GEWÄHELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

defined('_JEXEC') or die('Restricted access');

//Ordering allowed ?
$ordering=($this->sortColumn == 't.ordering');

JHtml::_('behavior.tooltip');
JHtml::_('behavior.modal');
$templatesToLoad = array('footer');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>
<script>

	function searchTeam(val,key)
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
				?>&nbsp;<input	type="text" name="filter_search" id="filter_search"
								value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
								class="text_area" onchange="$('adminForm').submit(); " />
                                
				<button onclick="this.form.submit(); "><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
				<button onclick="document.getElementById('filter_search').value='';this.form.submit(); ">
					<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>
				</button>
			</td>
			<td align="center" colspan="4">
				<?php
				for ($i=65; $i < 91; $i++)
				{
					printf("<a href=\"javascript:searchTeam('%s')\">%s</a>&nbsp;&nbsp;&nbsp;&nbsp;",chr($i),chr($i));
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
					<th class="left">
						<?php
						echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_TEAMS_NAME','t.name',$this->sortDirection,$this->sortColumn);
						?>
					</th>
					<th class="left">
						<?php
						echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_TEAMS_CLUBNAME','c.name',$this->sortDirection,$this->sortColumn);
						?>
					</th>
					<th>
						<?php
						echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_TEAMS_WEBSITE','t.website',$this->sortDirection,$this->sortColumn);
						?>
					</th>
					<th>
						<?php
						echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_TEAMS_ML_NAME','t.middle_name',$this->sortDirection,$this->sortColumn);
						?>
					</th>
					<th>
						<?php
						echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_TEAMS_S_NAME','t.short_name',$this->sortDirection,$this->sortColumn);
						?>
					</th>
					<th>
						<?php
						echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_TEAMS_INFO','t.info',$this->sortDirection,$this->sortColumn);
						?>
					</th>
                    <th class="title">
						<?php
						echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SPORTSTYPE','st.name',$this->sortDirection,$this->sortColumn);
						?>
					</th>
					<th>
						<?php
						echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_TEAMS_PICTURE','t.picture',$this->sortDirection,$this->sortColumn);
						?>
					</th>
					<th width="10%">
						<?php
						echo JHtml::_('grid.sort','JGRID_HEADING_ORDERING','t.ordering',$this->sortDirection,$this->sortColumn);
						echo JHtml::_('grid.order',$this->items, 'filesave.png', 'teams.saveorder');
						?>
					</th>
					<th width="5%">
						<?php
						echo JHtml::_('grid.sort','JGRID_HEADING_ID','t.id',$this->sortDirection,$this->sortColumn);
						?>
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
					$link=JRoute::_('index.php?option=com_sportsmanagement&task=team.edit&id='.$row->id);
					$checked=JHtml::_('grid.checkedout',$row,$i);
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
									$imageTitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMS_EDIT_DETAILS');
									echo JHtml::_(	'image','administrator/components/com_sportsmanagement/assets/images/edit.png',
													$imageTitle,'title= "'.$imageTitle.'"');
									?>
								</a>
							</td>
							<?php
						}
						?>
						<td><?php echo $row->name; ?></td>
						<td><?php echo (empty($row->clubname)) ? '<span style="color:red;">'.JText::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMS_NO_CLUB').'</span>' : $row->clubname; ?></td>
						<td>
							<?php
							if ($row->website != '')
							{
								echo '<a href="'.$row->website.'" target="_blank">';
							}
							echo $row->website;
							if ($row->website != '')
							{
								echo '</a>';
							}
							?>
						</td>
						<td><?php echo $row->middle_name; ?></td>
						<td class="center"><?php echo $row->short_name; ?></td>
						<td><?php echo $row->info; ?></td>
                        <td class="center"><?php echo JText::_($row->sportstype); ?></td>
						<td class="center">
							<?php
							if ($row->picture == '')
							{
								$imageTitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMS_NO_IMAGE');
								echo JHtml::_(	'image','administrator/components/com_sportsmanagement/assets/images/error.png',
												$imageTitle,'title= "'.$imageTitle.'"');
							}
							elseif ($row->picture == sportsmanagementHelper::getDefaultPlaceholder("team"))
							{
								$imageTitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMS_DEFAULT_IMAGE');
								echo JHtml::_(	'image','administrator/components/com_sportsmanagement/assets/images/information.png',
				  								$imageTitle,'title= "'.$imageTitle.'"');
?>
<a href="<?php echo JURI::root().$row->picture;?>" title="<?php echo $imageTitle;?>" class="modal">
<img src="<?php echo JURI::root().$row->picture;?>" alt="<?php echo $imageTitle;?>" width="20" />
</a>
<?PHP                                                 
							} else {
								if (JFile::exists(JPATH_SITE.DS.$row->picture)) {
									$imageTitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMS_CUSTOM_IMAGE');
									echo JHtml::_(	'image','administrator/components/com_sportsmanagement/assets/images/ok.png',
													$imageTitle,'title= "'.$imageTitle.'"');
?>
<a href="<?php echo JURI::root().$row->picture;?>" title="<?php echo $imageTitle;?>" class="modal">
<img src="<?php echo JURI::root().$row->picture;?>" alt="<?php echo $imageTitle;?>" width="20" />
</a>
<?PHP                                                     
								} else {
									$imageTitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMS_NO_IMAGE');
									echo JHtml::_(	'image','administrator/components/com_sportsmanagement/assets/images/delete.png',
													$imageTitle,'title= "'.$imageTitle.'"');
								}
							}
							?>
						</td>
						<td class="order">
							<span>
								<?php echo $this->pagination->orderUpIcon($i,$i > 0 ,'teams.orderup','JLIB_HTML_MOVE_UP',true); ?>
							</span>
							<span>
								<?php echo $this->pagination->orderDownIcon($i,$n,$i < $n,'teams.orderdown','JLIB_HTML_MOVE_DOWN',true); ?>
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

	<input type="hidden" name="search_mode"			value="<?php echo $this->lists['search_mode']; ?>" />
	<input type="hidden" name="task"				value="" />
	<input type="hidden" name="boxchecked"			value="0" />
	<input type="hidden" name="filter_order"		value="<?php echo $this->sortColumn; ?>" />
	<input type="hidden" name="filter_order_Dir"	value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
<?PHP
echo "<div>";
echo $this->loadTemplate('footer');
echo "</div>";
?>   