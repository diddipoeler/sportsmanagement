<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_data.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage projectpositions
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
$templatesToLoad = array('footer','listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);


?>


	<div id="editcell">
<!--		<fieldset class="adminform"> -->
			<legend><?php echo Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_LEGEND','<i>'.$this->project->name.'</i>'); ?></legend>
			<table class="<?php echo $this->table_data_class; ?>">
				<thead>
					<tr>
						<th width="5"><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM'); ?></th>
						<th>
							<?php echo HTMLHelper::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_STANDARD_NAME_OF_POSITION','po.name',$this->sortDirection,$this->sortColumn); ?>
						</th>
						<th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_TRANSLATION'); ?></th>
						<th>
							<?php echo HTMLHelper::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_PARENTNAME','po.parent_id',$this->sortDirection,$this->sortColumn); ?>
						</th>
						<th width="20">
							<?php echo HTMLHelper::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_PLAYER_POSITION','persontype',$this->sortDirection,$this->sortColumn); ?>
						</th>
						<th width="20">
							<?php echo HTMLHelper::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_STAFF_POSITION','persontype',$this->sortDirection,$this->sortColumn); ?>
						</th>
						<th width="20">
							<?php echo HTMLHelper::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_REFEREE_POSITION','persontype',$this->sortDirection,$this->sortColumn); ?>
						</th>
						<th width="20">
							<?php echo HTMLHelper::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_CLUBSTAFF_POSITION','persontype',$this->sortDirection,$this->sortColumn); ?>
						</th>
						<?php
						/*
						?>
						<th class="title">
							<?php echo HTMLHelper::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_SPORTSTYPE','po.sports_type_id',$this->sortDirection,$this->sortColumn); ?>
						</th>
						<?php
						*/
						?>
						<th width="5%"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_HAS_EVENTS'); ?></th>
						<th width="5%"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_HAS_STATS'); ?></th>
						<th width="1%">
							<?php echo HTMLHelper::_('grid.sort','PID','po.id',$this->sortDirection,$this->sortColumn); ?>
						</th>
						<th width="1%">
							<?php echo HTMLHelper::_('grid.sort','JGRID_HEADING_ID','pt.id',$this->sortDirection,$this->sortColumn); ?>
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
							<td><?php if ($row->name != Text::_($row->name)){echo Text::_($row->name);} ?></td>
							<td><?php echo Text::_($row->parent_name); ?></td>
							<td class="center">
								<?php
								if ($row->persontype == 1)
								{
									$imageTitle=Text::_('COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_PLAYER_POSITION');
									$imageParams='title= "'.$imageTitle.'"';
									echo HTMLHelper::image($imageFileOk,$imageTitle,$imageParams);
								}
								?>
							</td>
							<td class="center">
								<?php
								if ($row->persontype == 2)
								{
									$imageTitle=Text::_('COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_STAFF_POSITION');
									$imageParams='title= "'.$imageTitle.'"';
									echo HTMLHelper::image($imageFileOk,$imageTitle,$imageParams);
								}
								?>
							</td>
							<td class="center">
								<?php
								if ($row->persontype == 3)
								{
									$imageTitle=Text::_('COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_REFEREE_POSITION');
									$imageParams='title= "'.$imageTitle.'"';
									echo HTMLHelper::image($imageFileOk,$imageTitle,$imageParams);
								}
								?>
							</td>
							<td class="center">
								<?php
								if ($row->persontype == 4)
								{
									$imageTitle=Text::_('COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_CLUBSTAFF_POSITION');
									$imageParams='title= "'.$imageTitle.'"';
									echo HTMLHelper::image($imageFileOk,$imageTitle,$imageParams);
								}
								?>
							</td>
							<?php
							/*
							?>
							<td class="center"><?php echo Text::_(sportsmanagementHelper::getSportsTypeName($row->sports_type_id)); ?></td>
							<?php
							*/
							?>
							<td class="center">
								<?php
								if ($row->countEvents == 0)
								{
									$imageFile='administrator/components/com_sportsmanagement/assets/images/error.png';
									$imageTitle=Text::_('COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_NO_EVENTS');
									$imageParams='title= "'.$imageTitle.'"';
									echo HTMLHelper::image($imageFile,$imageTitle,$imageParams);
								}
								else
								{
									$imageTitle=Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_NR_EVENTS',$row->countEvents);
									$imageParams='title= "'.$imageTitle.'"';
									echo HTMLHelper::image($imageFileOk,$imageTitle,$imageParams);
								}
								?>
							</td>
							<td class="center">
								<?php
								if ($row->countStats == 0)
								{
									$imageFile='administrator/components/com_sportsmanagement/assets/images/error.png';
									$imageTitle=Text::_('COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_NO_STATISTICS');
									$imageParams='title= "'.$imageTitle.'"';
									echo HTMLHelper::image($imageFile,$imageTitle,$imageParams);
								}
								else
								{
									$imageTitle=Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_NR_STATISTICS',$row->countStats);
									$imageParams='title= "'.$imageTitle.'"';
									echo HTMLHelper::image($imageFileOk,$imageTitle,$imageParams);
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
