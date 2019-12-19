<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_data.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage treetos
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

?>
<div id="table-responsive">
		<legend><?php echo Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_TREETOS_TITLE','<i>','<i>'.$this->projectws->name.'</i>'); ?></legend>
		
			<?php
			$colspan= 11;
			?>
			<table class="<?php echo $this->table_data_class; ?>" >
				<thead>
					<tr>
						<th id="treetos-total" width="5" style="vertical-align: top; "><?php echo count($this->items).'/'.$this->pagination->total; ?></th>
						<th id="treetos-checkall" width="20" style="vertical-align: top; ">
							<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
						</th>
						<th id="treetos-name" class="title" nowrap="nowrap" style="vertical-align: top; "><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETOS_NAME'); ?></th>
						<?php
						if ($this->projectws->project_type == 'DIVISIONS_LEAGUE')
						{
							$colspan++;
							?><th id="treetos-division" class="title" style="vertical-align:top; " nowrap="nowrap">
								<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETO_DIVISION');
									echo '<br>'.HTMLHelper::_(	'select.genericlist',
														$this->lists['divisions'],
														'division',
														'class="inputbox" size="1" onchange="window.location.href=window.location.href.split(\'&division=\')[0]+\'&division=\'+this.value"',
														'value','text', $this->division);
								?>
							</th><?php
						}
						?>
						<th id="treetos-tiefe" class="title" nowrap="nowrap" style="vertical-align: top; "><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETOS_DEPTH'); ?></th>
			
						<th id="treetos-hide" style="text-align: center; vertical-align: top; "><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETOS_HIDE'); ?></th>
						<th id="treetos-status" width="1%" nowrap="nowrap" style="vertical-align: top; "><?php echo Text::_('JSTATUS'); ?></th>
						<th id="treetos-id" class="title" nowrap="nowrap" style="vertical-align: top; "><?php echo Text::_('JGRID_HEADING_ID'); ?></th>
					</tr>
				</thead>
				<tfoot><tr><td colspan="<?php echo $colspan; ?>"><?php echo $this->pagination->getListFooter(); ?></td></tr></tfoot>
				<tbody>
					<?php
					$k=0;
					for ($i=0,$n=count($this->items); $i < $n; $i++)
					{
						$row		= $this->items[$i];
						$checked	= HTMLHelper::_('grid.checkedout',$row,$i,'id');
                        $published = HTMLHelper::_('grid.published',$row,$i,'tick.png','publish_x.png','treetos.');
					?>
						<tr class="<?php echo "row$k"; ?>">
							<td id="treetos-total" style="text-align:center; ">
								<?php
								echo $this->pagination->getRowOffset($i);
								?>
							</td>
							<td id="treetos-checkall" style="text-align:center; ">
								<?php
								echo $checked;
								?>
								<a
									href="index.php?option=com_sportsmanagement&task=treeto.edit&id=<?php echo $row->id; ?>&pid=<?php echo $this->project_id; ?>">
									<?php
									echo HTMLHelper::_(	'image','administrator/components/com_sportsmanagement/assets/images/edit.png',
													Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETOS_EDIT_DETAILS'),'title= "' .
													Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETOS_EDIT_DETAILS').'"');
									?>
								</a>
								<?php
								if( $row->leafed == 0)
								{ ?>
								<a
									href="index.php?option=com_sportsmanagement&task=treetos.genNode&id=<?php echo $row->id; ?>&pid=<?php echo $this->project_id; ?>">
								<?php
									echo HTMLHelper::_(	'image','administrator/components/com_sportsmanagement/assets/images/update.png',
													Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETOS_GENERATE'),'title= "' .
													Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETOS_GENERATE').'"');
									?>
								</a><?php
								}
								else
								{ ?>
								<a
									href="index.php?option=com_sportsmanagement&view=treetonodes&task=treetonode.display&tid=<?php echo $row->id; ?>&pid=<?php echo $this->project_id; ?>">
									<?php
									echo HTMLHelper::_(	'image','administrator/components/com_sportsmanagement/assets/images/icon-16-Tree.png',
													Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETOS_EDIT_TREE'),'title= "' .
													Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETOS_EDIT_TREE').'"');
									?>
								</a>
								<?php
								}
								?>
							</td>
							<td id="treetos-name" style="text-align:center; ">
								<?php
								echo $row->name;
								?>
							</td>
							<?php
							if ($this->projectws->project_type == 'DIVISIONS_LEAGUE')
							{
								?>
								<td id="treetos-division" nowrap="nowrap" style="text-align:center;">
									<?php
									$append='';
									if ($row->division_id == 0)
									{
										$append=' style="background-color:#bbffff"';
									}
									echo HTMLHelper::_(	'select.genericlist',
													$this->lists['divisions'],
													'division_id'.$row->id,
													$append.'class="inputbox" size="1" onchange="document.getElementById(\'cb' .
													$i.'\').checked=true"'.$append,
													'value','text',$row->division_id);
									?>
								</td>
								<?php
							}
							?>
							<td id="treetos-tiefe" style="text-align:center; ">
								<?php
								echo $row->tree_i;
								?>
							</td>
					
							<td id="treetos-hide" style="text-align:center; ">
								<?php
								echo $row->hide;
								?>
							</td>
							<td id="treetos-published" style="text-align:center; ">
								<?php
								echo $published;
								?>
							</td>
							<td id="treetos-id" style="text-align:center; ">
								<?php
								echo $row->id;
								?>
							</td>
						</tr>
						<?php
						$k=1 - $k;
					}
					?>
				</tbody>
			</table>
</div>
