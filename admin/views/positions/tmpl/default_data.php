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
$ordering=($this->sortColumn == 'po.ordering');

JHtml::_('behavior.tooltip');
JHtml::_('behavior.modal');
$templatesToLoad = array('footer','listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>

	<div id="editcell">
		<table class="<?php echo $this->table_data_class; ?>">
			<thead>
				<tr>
					<th width="5"><?php echo JText::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM'); ?></th>
					<th width="20">
						<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
					</th>
					<th width="20">&nbsp;</th>
					<th>
						<?php
						echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_POSITIONS_STANDARD_NAME_OF_POSITION','po.name',$this->sortDirection,$this->sortColumn);
						?>
					</th>
					<th><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_POSITIONS_TRANSLATION'); ?></th>
					<th>
						<?php
						echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_POSITIONS_PARENTNAME','po.parent_id',$this->sortDirection,$this->sortColumn);
						?>
					</th>
					<th>
						<?php
						echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_POSITIONS_SPORTSTYPE','po.sports_type_id',$this->sortDirection,$this->sortColumn);
						?>
					</th>
					<th>
						<?php
						echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_POSITIONS_PERSON_TYPE','po.persontype',$this->sortDirection,$this->sortColumn);
						?>
					</th>
					<th width="5%"><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_POSITIONS_HAS_EVENTS'); ?>
					</th>
					<th width="5%"><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_POSITIONS_HAS_STATS'); ?>
					</th>
					<th width="5%">
						<?php
						echo JHtml::_('grid.sort','JSTATUS','po.published',$this->sortDirection,$this->sortColumn);
						?>
					</th>
					<th width="10%">
						<?php
						echo JHtml::_('grid.sort','JGRID_HEADING_ORDERING','po.ordering',$this->sortDirection,$this->sortColumn);
						echo JHtml::_('grid.order', $this->items, 'filesave.png', 'positions.saveorder');
						?>
					</th>
					<th width="5%">
						<?php echo JHtml::_('grid.sort','JGRID_HEADING_ID','po.id',$this->sortDirection,$this->sortColumn); ?>
					</th>
				</tr>
			</thead>
			<tfoot><tr><td colspan="9"><?php echo $this->pagination->getListFooter(); ?></td>
            <td colspan="4"><?php echo $this->pagination->getResultsCounter(); ?>
            
            </td>
            </tr></tfoot>
			<tbody>
			<?php
			$k=0;
			for ($i=0,$n=count($this->items); $i < $n; $i++)
			{
				$row =& $this->items[$i];
				$link=JRoute::_('index.php?option=com_sportsmanagement&task=position.edit&id='.$row->id);
				$checked=JHtml::_('grid.checkedout',$row,$i);
				$published=JHtml::_('grid.published',$row,$i,'tick.png','publish_x.png','positions.');
				?>
				<tr class="<?php echo 'row'.$k; ?>">
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
						?><td class="center">
							<a href="<?php echo $link; ?>">
								<?php
								$imageTitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_POSITIONS_EDIT_DETAILS');
								echo JHtml::_(	'image','administrator/components/com_sportsmanagement/assets/images/edit.png',
												$imageTitle,'title= "'.$imageTitle.'"');
								?>
							</a>
						</td><?php
					}
					?>
					<td><?php echo $row->name; ?></td>
					<td>
						<?php
						if ($row->name == JText::_($row->name))
						{
							echo '&nbsp;';
						}
						else
						{
							echo JText::_($row->name);
						}
						?>
					</td>
					<td>
						<?php
							echo JHtml::_('select.genericlist',$this->lists['parent_id'],'parent_id'.$row->id,''.'class="inputbox" size="1" onchange="document.getElementById(\'cb'.$i.'\').checked=true"','value','text',$row->parent_id);
						?>
					</td>
					<td class="center"><?php echo JText::_(sportsmanagementHelper::getSportsTypeName($row->sports_type_id)); ?></td>
					<td class="center"><?php echo JText::_(sportsmanagementHelper::getPosPersonTypeName($row->persontype)); ?></td>
					<td class="center">
						<?php
						if ($row->countEvents == 0)
						{
							$imageTitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_POSITIONS_NO_EVENTS');
							echo JHtml::_(	'image','administrator/components/com_sportsmanagement/assets/images/error.png',
											$imageTitle,'title= "'.$imageTitle.'"');
						}
						else
						{
							$imageTitle=JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_POSITIONS_NR_EVENTS',$row->countEvents);
							echo JHtml::_(	'image','administrator/components/com_sportsmanagement/assets/images/ok.png',
											$imageTitle,'title= "'.$imageTitle.'"');
						}
						?>
					</td>
					<td class="center">
						<?php
						if ($row->countStats == 0)
						{
							$imageTitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_POSITIONS_NO_STATISTICS');
							echo JHtml::_(	'image','administrator/components/com_sportsmanagement/assets/images/error.png',
											$imageTitle,'title= "'.$imageTitle.'"');
						}
						else
						{
							$imageTitle=JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_POSITIONS_NR_STATISTICS',$row->countStats);
							echo JHtml::_(	'image','administrator/components/com_sportsmanagement/assets/images/ok.png',
											$imageTitle,'title= "'.$imageTitle.'"');
						}
						?>
					</td>
					<td class="center"><?php echo $published; ?></td>
					<td class="order">
						<span>
							<?php echo $this->pagination->orderUpIcon($i,$i > 0 ,'positions.orderup','JLIB_HTML_MOVE_UP',true); ?>
						</span>
						<span>
							<?php echo $this->pagination->orderDownIcon($i,$n,$i < $n,'positions.orderdown','JLIB_HTML_MOVE_DOWN',true); ?>
							<?php
							$disabled=true ? '' : 'disabled="disabled"';
							?>
						</span>
						<input  type="text" name="order[]" size="2"
								value="<?php echo $row->ordering; ?>" <?php echo $disabled ?>
								class="text_area" style="text-align: center" />
					</td>
					<td align="center"><?php echo $row->id; ?></td>
				</tr>
				<?php
				$k=1 - $k;
			}
			?>
			</tbody>
		</table>
	</div>
