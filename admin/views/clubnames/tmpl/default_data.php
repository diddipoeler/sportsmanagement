<?php 
/** Joomla Sports Management ein Programm zur Verwaltung für alle Sportarten
* @version 1.0.26
* @file		components/sportsmanagement/views/results/tmpl/default.php
* @author diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
* @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license This file is part of Joomla Sports Management.
*
* Joomla Sports Management is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Joomla Sports Management is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Joomla Sports Management. If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von Joomla Sports Management.
*
* Joomla Sports Management ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* Joomla Sports Management wird in der Hoffnung, dass es nützlich sein wird, aber
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
JHtml::_( 'behavior.tooltip' );
//Ordering allowed ?
//$ordering=($this->sortColumn == 'dv.ordering');
$templatesToLoad = array('footer','listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

?>

<!--	<fieldset class="adminform"> -->
		
		

		<div class="table-responsive">
			<table class="<?php echo $this->table_data_class; ?>">
				<thead>
					<tr>
						<th width="5" style="vertical-align: top; ">
							<?php
							echo JText::_( 'COM_SPORTSMANAGEMENT_GLOBAL_RESET' );
							?>
						</th>
						<th width="20" style="vertical-align: top; ">
							<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
						</th>
						<th width="20" style="vertical-align: top; ">
							&nbsp;
						</th>
						<th style="vertical-align: top; ">
							<?php
							echo JHtml::_( 'grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_CLUBNAMES_NAME', 'obj.name', $this->sortDirection, $this->sortColumn );
							?>
						</th>
						<th style="vertical-align: top; ">
							<?php
							echo JHtml::_( 'grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_CLUBNAMES_L_NAME', 'obj.name_long', $this->sortDirection, $this->sortColumn );
							?>
						</th>
						<th >
						<?php
						echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_LEAGUES_COUNTRY','obj.country',$this->sortDirection,$this->sortColumn);
						?>
					</th>
                        
                        
                        
						<th>
					<?php
						echo JHtml::_('grid.sort','JSTATUS','obj.published',$this->sortDirection,$this->sortColumn);
						?>
					</th>
                        <th width="85" style="vertical-align: top; ">
							<?php
							echo JHtml::_( 'grid.sort', 'JGRID_HEADING_ORDERING', 'obj.ordering', $this->sortDirection, $this->sortColumn );
							echo '<br />';
							echo JHtml::_('grid.order',$this->items, 'filesave.png', 'clubnames.saveorder');
							?>
						</th>
                        
						<th style="vertical-align: top; ">
							<?php
							echo JHtml::_( 'grid.sort', 'JGRID_HEADING_ID', 'obj.id', $this->sortDirection, $this->sortColumn );
							?>
						</th>
					</tr>
				</thead>

				<tfoot>
					<tr>
						<td colspan="8">
							<?php
							echo $this->pagination->getListFooter();
							?>
						</td>
                        <td colspan='2'>
            <?php echo $this->pagination->getResultsCounter();?>
            </td>
					</tr>
				</tfoot>

				<tbody>
				<?php
					$k = 0;
					for ( $i = 0, $n = count( $this->items ); $i < $n; $i++ )
					{
						$row =& $this->items[$i];
						$link = JRoute::_( 'index.php?option=com_sportsmanagement&task=clubname.edit&id=' . $row->id );
                        $canEdit	= $this->user->authorise('core.edit','com_sportsmanagement');
                        $canCheckin = $this->user->authorise('core.manage','com_checkin') || $row->checked_out == $this->user->get ('id') || $row->checked_out == 0;
                        $checked = JHtml::_('jgrid.checkedout', $i, $this->user->get ('id'), $row->checked_out_time, 'clubnames.', $canCheckin);
                        $canChange  = $this->user->authorise('core.edit.state', 'com_sportsmanagement.clubname.' . $row->id) && $canCheckin;
						?>
						<tr class="<?php echo "row$k"; ?>">
							<td style="text-align:center; ">
								<?php echo $this->pagination->getRowOffset( $i ); ?>
							</td>
							<td style="text-align:center; ">
								<?php echo JHtml::_('grid.id', $i, $row->id); ?>
							</td>
							<?php
							
								$inputappend = '';
								?>
								<td style="text-align:center; ">
                                <?php
                            if ($row->checked_out) : ?>
										<?php echo JHtml::_('jgrid.checkedout', $i, $this->user->get ('id'), $row->checked_out_time, 'clubnames.', $canCheckin); ?>
									<?php else: ?>
									<a href="<?php echo $link; ?>">
										<?php
										$imageTitle = JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_CLUBNAME_EDIT_DETAILS' );
										echo JHtml::_(	'image', 'administrator/components/com_sportsmanagement/assets/images/edit.png',
														$imageTitle,
														'title= "' . $imageTitle . '"' );
										?>
									</a>
                                    <?php endif; ?>
								</td>
								<?php
							
							?>
							<td>
								<?php
								echo $row->name;
								?>
                                
							</td>
							<td>
								<?php
								echo $row->name_long;
								?>
							</td>
							<td class="center">
                        <?php 
                        echo JSMCountries::getCountryFlag($row->country); 
                        $append =' onchange="document.getElementById(\'cb'.$i.'\').checked=true" ';
                        echo JHtml::_(	'select.genericlist',$this->lists['nation'],'country'.$row->id,
												'class="form-control form-control-inline" size="1"'.$append,'value','text',$row->country);
                        ?>
                        </td>
                           
                            
                            <td class="center">
                             <div class="btn-group">
            <?php echo JHtml::_('jgrid.published', $row->published, $i, 'clubnames.', $canChange, 'cb'); ?>
            <?php 
            // Create dropdown items and render the dropdown list.
								if ($canChange)
								{
									JHtml::_('actionsdropdown.' . ((int) $row->published === 2 ? 'un' : '') . 'archive', 'cb' . $i, 'clubnames');
									JHtml::_('actionsdropdown.' . ((int) $row->published === -2 ? 'un' : '') . 'trash', 'cb' . $i, 'clubnames');
									echo JHtml::_('actionsdropdown.render', $this->escape($row->name));
								}
								?>
            </div>
                            
                            </td>
							<td class="order">
								<span>
									<?php
									echo $this->pagination->orderUpIcon( $i, $i > 0 , 'clubnames.orderup', 'COM_SPORTSMANAGEMENT_GLOBAL_ORDER_UP', true );
									?>
								</span>
								<span>
									<?php
									echo $this->pagination->orderDownIcon( $i, $n, $i < $n, 'clubnames.orderdown', 'COM_SPORTSMANAGEMENT_GLOBAL_ORDER_DOWN', true );
									$disabled = true ?  '' : 'disabled="disabled"';
									?>
								</span>
								<input  type="text" name="order[]" size="5" value="<?php echo $row->ordering;?>" <?php echo $disabled; ?>
										class="form-control form-control-inline" style="text-align: center" />
							</td>
							<td style="text-align:center; ">
								<?php
								echo $row->id;
								?>
							</td>
						</tr>
						<?php
						$k = 1 - $k;
					}
					?>
				</tbody>
			</table>
		</div> 
<!--	</fieldset> --> 
