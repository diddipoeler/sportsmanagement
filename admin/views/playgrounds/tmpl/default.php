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
$ordering=($this->sortColumn == 'v.ordering');

JHtml::_('behavior.tooltip');
JHtml::_('behavior.modal');
$templatesToLoad = array('footer');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>
<script>

	function searchPlayground(val,key)
	{
		var f=$('adminForm');
		if(f)
		{
		f.elements['filter_search'].value=val;
		
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
				<button onclick="document.getElementById('filter_search').value='';this.form.submit(); "><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
			</td>
            <td nowrap='nowrap' align='right'><?php echo $this->lists['nation2'].'&nbsp;&nbsp;'; ?></td>
			
            <td align="center" colspan="4">
				<?php
                $startRange = JComponentHelper::getParams(JRequest::getCmd('option'))->get('character_filter_start_hex', '0');
		$endRange = JComponentHelper::getParams(JRequest::getCmd('option'))->get('character_filter_end_hex', '0');
		for ($i=$startRange; $i <= $endRange; $i++)
		{
			
            //printf("<a href=\"javascript:searchPerson('%s')\">%s</a>&nbsp;&nbsp;&nbsp;&nbsp;",chr($i),chr($i));
            printf("<a href=\"javascript:searchPlayground('%s')\">%s</a>&nbsp;&nbsp;&nbsp;&nbsp;",'&#'.$i.';','&#'.$i.';');
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
						echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_PLAYGROUNDS_NAME','v.name',$this->sortDirection,$this->sortColumn);
						?>
					</th>
					<th>
						<?php
						echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_PLAYGROUNDS_S_NAME','v.short_name',$this->sortDirection,$this->sortColumn);
						?>
					</th>
					<th>
						<?php
						echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_PLAYGROUNDS_CLUBNAME','club',$this->sortDirection,$this->sortColumn);
						?>
					</th>
					<th>
						<?php
						echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_PLAYGROUNDS_CAPACITY','v.max_visitors',$this->sortDirection,$this->sortColumn);
						?>
					</th>
					<th>
						<?php
						echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_PLAYGROUNDS_IMAGE','v.picture',$this->sortDirection,$this->sortColumn);
						?>
					</th>
                    
                    <th width=""><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_PLAYGROUNDS_CITY'); ?></th>
                    <th width=""><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_PLAYGROUNDS_LATITUDE'); ?></th>
                    <th width=""><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_PLAYGROUNDS_LONGITUDE'); ?></th>
                    
                    <th width="20">
						<?php echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_PLAYGROUNDS_COUNTRY','v.country',$this->sortDirection,$this->sortColumn); ?>
					</th>
                    
                    
                    
					<th width="10%">
						<?php
						echo JHtml::_('grid.sort','JGRID_HEADING_ORDERING','v.ordering',$this->sortDirection,$this->sortColumn);
						echo JHtml::_('grid.order',$this->items, 'filesave.png', 'playgrounds.saveorder');
						?>
					</th>
					<th width="5%">
						<?php echo JHtml::_('grid.sort','JGRID_HEADING_ID','v.id',$this->sortDirection,$this->sortColumn); ?>
					</th>
				</tr>
			</thead>
			<tfoot><tr><td colspan="9"><?php echo $this->pagination->getListFooter(); ?></td>
            <td colspan='6'>
            <?php echo $this->pagination->getResultsCounter();?>
            </td>
            </tr></tfoot>
			<tbody>
				<?php
				$k=0;
				for ($i=0,$n=count($this->items); $i < $n; $i++)
				{
					$row =& $this->items[$i];
					$link=JRoute::_('index.php?option=com_sportsmanagement&task=playground.edit&id='.$row->id);
					$checked=JHtml::_('grid.checkedout',$row,$i);
					?>
					<tr class="<?php echo "row$k"; ?>">
						<td class="center"><?php echo $this->pagination->getRowOffset($i); ?></td>
						<td class="center"><?php echo $checked; ?></td>
						<?php
						if (JTable::isCheckedOut($this->user->get('id'),$row->checked_out))
						{
							$inputappend=' disabled="disabled" ';
							?><td class="center">&nbsp;</td><?php
						}
						else
						{
							$inputappend='';
							?>
							<td class="center">
								<a href="<?php echo $link; ?>">
									<?php
									$imageTitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_PLAYGROUNDS_EDIT_DETAILS');
									echo JHtml::_(	'image','administrator/components/com_sportsmanagement/assets/images/edit.png',
													$imageTitle,'title= "'.$imageTitle.'"');
									?>
								</a>
							</td>
							<?php
						}
						?>
						<td><?php echo $row->name; ?></td>
						<td class="center"><?php echo $row->short_name; ?></td>
						<td><?php echo $row->club; ?></td>
						<td class="center"><?php echo $row->max_visitors; ?></td>
						<td width="5%" class="center">
							<?php
							if ($row->picture == '')
							{
								$imageTitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_PLAYGROUNDS_NO_IMAGE');
								echo JHtml::_('image',JUri::base().'/components/com_sportsmanagement/assets/images/delete.png',
												$imageTitle,'title= "'.$imageTitle.'"');

							}
							elseif($row->picture == sportsmanagementHelper::getDefaultPlaceholder("team"))
								{
									$imageTitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_PLAYGROUNDS_DEFAULT_IMAGE');
									echo JHtml::_(	'image',JUri::base() .'/components/com_sportsmanagement/assets/images/information.png',
													$imageTitle,'title= "'.$imageTitle.'"');
                                                    
?>
<a href="<?php echo JUri::root().$row->picture;?>" title="<?php echo $imageTitle;?>" class="modal">
<img src="<?php echo JUri::root().$row->picture;?>" alt="<?php echo $imageTitle;?>" width="20" />
</a>
<?PHP                                                      
								}
								elseif($row->picture !== '')
									{
										$imageTitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_PLAYGROUNDS_CUSTOM_IMAGE');
										echo JHtml::_('image',JUri::base().'/components/com_sportsmanagement/assets/images/ok.png',
														$imageTitle,'title= "'.$imageTitle.'"');
?>
<a href="<?php echo JUri::root().$row->picture;?>" title="<?php echo $imageTitle;?>" class="modal">
<img src="<?php echo JUri::root().$row->picture;?>" alt="<?php echo $imageTitle;?>" width="20" />
</a>
<?PHP                                                          
									}
									?>
						</td>
                        
                        <td class=""><?php echo $row->city; ?></td>
                        <td class=""><?php echo $row->latitude; ?></td>
                        <td class=""><?php echo $row->longitude; ?></td>
                        
                        <td class="center"><?php echo JSMCountries::getCountryFlag($row->country); ?></td>
                        
						<td class="order">
							<span>
								<?php echo $this->pagination->orderUpIcon($i,$i > 0,'playgrounds.orderup','JLIB_HTML_MOVE_UP',true); ?>
							</span>
							<span>
								<?php echo $this->pagination->orderDownIcon($i,$n,$i < $n,'playgrounds.orderdown','JLIB_HTML_MOVE_DOWN',true); ?>
								<?php $disabled=true ?  '' : 'disabled="disabled"'; ?>
							</span>
							<input  type="text" name="order[]" size="5" value="<?php echo $row->ordering;?>" <?php echo $disabled ?>
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
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->sortColumn; ?>" />
	<input type="hidden" name="filter_order_Dir" value="" />
	<?php echo JHtml::_('form.token')."\n"; ?>
</form>
<?PHP
echo "<div>";
echo $this->loadTemplate('footer');
echo "</div>";
?>   