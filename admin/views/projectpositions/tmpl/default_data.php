<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
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
* OHNE JEDE GEWÄHRLEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

defined('_JEXEC') or die('Restricted access');
$templatesToLoad = array('footer','listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
JHtml::_('behavior.tooltip');
JHtml::_('behavior.modal');
?>


	<div id="editcell">
<!--		<fieldset class="adminform"> -->
			<legend><?php echo JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_LEGEND','<i>'.$this->project->name.'</i>'); ?></legend>
			<table class="<?php echo $this->table_data_class; ?>">
				<thead>
					<tr>
						<th width="5"><?php echo JText::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM'); ?></th>
						<th>
							<?php echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_STANDARD_NAME_OF_POSITION','po.name',$this->sortDirection,$this->sortColumn); ?>
						</th>
						<th><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_TRANSLATION'); ?></th>
						<th>
							<?php echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_PARENTNAME','po.parent_id',$this->sortDirection,$this->sortColumn); ?>
						</th>
						<th width="20">
							<?php echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_PLAYER_POSITION','persontype',$this->sortDirection,$this->sortColumn); ?>
						</th>
						<th width="20">
							<?php echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_STAFF_POSITION','persontype',$this->sortDirection,$this->sortColumn); ?>
						</th>
						<th width="20">
							<?php echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_REFEREE_POSITION','persontype',$this->sortDirection,$this->sortColumn); ?>
						</th>
						<th width="20">
							<?php echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_CLUBSTAFF_POSITION','persontype',$this->sortDirection,$this->sortColumn); ?>
						</th>
						<?php
						/*
						?>
						<th class="title">
							<?php echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_SPORTSTYPE','po.sports_type_id',$this->sortDirection,$this->sortColumn); ?>
						</th>
						<?php
						*/
						?>
						<th width="5%"><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_HAS_EVENTS'); ?></th>
						<th width="5%"><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_HAS_STATS'); ?></th>
						<th width="1%">
							<?php echo JHtml::_('grid.sort','PID','po.id',$this->sortDirection,$this->sortColumn); ?>
						</th>
						<th width="1%">
							<?php echo JHtml::_('grid.sort','JGRID_HEADING_ID','pt.id',$this->sortDirection,$this->sortColumn); ?>
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
<!--		</fieldset> -->
	</div>
