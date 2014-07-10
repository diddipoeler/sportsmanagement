<?php 
/** Joomla Sports Management ein Programm zur Verwaltung für alle Sportarten
* @version 1.0.26
* @file		components/sportsmanagement/views/results/tmpl/default.php
* @author diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
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
$templatesToLoad = array('footer');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
JHtml::_( 'behavior.tooltip' );
//Ordering allowed ?
$ordering=($this->sortColumn == 'dv.ordering');
$templatesToLoad = array('footer');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

?>
<form action="<?php echo $this->request_url; ?>" method="post" id="adminForm" name="adminForm">
	<fieldset class="adminform">
		<legend>
			<?php
			echo JText::sprintf(	'COM_SPORTSMANAGEMENT_ADMIN_DIVS_TITLE2',
									'<i>' . $this->projectws->name . '</i>' );
			?>
		</legend>
		<table>
			<tr>
				<td align="left" width="100%">
					<?php
					echo JText::_( 'JSEARCH_FILTER_LABEL' );
					?>&nbsp;<input	type="text" name="filter_search" id="filter_search"
								value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
								class="text_area" onchange="$('adminForm').submit(); " />
                                
					<button onclick="this.form.submit(); ">
						<?php
						echo JText::_( 'JSEARCH_FILTER_SUBMIT' );
						?>
					</button>
					<button onclick="document.getElementById('filter_search').value='';this.form.submit(); ">
						<?php
						echo JText::_( 'JSEARCH_FILTER_CLEAR' );
						?>
					</button>
				</td>
				<td class="nowrap">
					<?php
						echo $this->lists['state'];
					?>
					&nbsp;
				</td>
			</tr>
		</table>

		<div id="editcell">
			<table class="adminlist">
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
						<th class="title" style="vertical-align: top; ">
							<?php
							echo JHtml::_( 'grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_DIVS_NAME', 'dv.name', $this->sortDirection, $this->sortColumn );
							?>
						</th>
						<th class="title" style="vertical-align: top; ">
							<?php
							echo JHtml::_( 'grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_DIVS_S_NAME', 'dv.shortname', $this->sortDirection, $this->sortColumn );
							?>
						</th>
						<th class="title" style="vertical-align: top; ">
							<?php
							echo JHtml::_( 'grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_DIVS_PARENT_NAME', 'parent_name', $this->sortDirection, $this->sortColumn );
							?>
						</th>
                        
                        <th class="title" style="vertical-align: top; ">
							<?php
							echo JHtml::_( 'grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PERSONS_IMAGE', 'dv.picture', $this->sortDirection, $this->sortColumn );
							?>
						</th>
                        
						<th>
					<?php
						echo JHtml::_('grid.sort','JSTATUS','dv.published',$this->sortDirection,$this->sortColumn);
						?>
					</th>
                        <th width="85" style="vertical-align: top; ">
							<?php
							echo JHtml::_( 'grid.sort', 'JGRID_HEADING_ORDERING', 'dv.ordering', $this->sortDirection, $this->sortColumn );
							echo '<br />';
							echo JHtml::_('grid.order',$this->items, 'filesave.png', 'divisions.saveorder');
							?>
						</th>
                        
						<th style="vertical-align: top; ">
							<?php
							echo JHtml::_( 'grid.sort', 'JGRID_HEADING_ID', 'dv.id', $this->sortDirection, $this->sortColumn );
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
						$row		=& $this->items[$i];
						$link 		= JRoute::_( 'index.php?option=com_sportsmanagement&task=division.edit&id=' . $row->id );
						$checked 	= JHtml::_( 'grid.checkedout',   $row, $i );
                        $published  = JHtml::_('grid.published',$row,$i, 'tick.png','publish_x.png','divisions.');
						?>
						<tr class="<?php echo "row$k"; ?>">
							<td style="text-align:center; ">
								<?php echo $this->pagination->getRowOffset( $i ); ?>
							</td>
							<td style="text-align:center; ">
								<?php echo $checked; ?>
							</td>
							<?php
							if ( JTable::isCheckedOut( $this->user->get( 'id' ), $row->checked_out ) )
							{
								$inputappend = ' disabled="disabled"';
								?>
								<td style="text-align:center; ">
									&nbsp;
								</td>
								<?php
							}
							else
							{
								$inputappend = '';
								?>
								<td style="text-align:center; ">
									<a href="<?php echo $link; ?>">
										<?php
										$imageTitle = JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DIVS_EDIT_DETAILS' );
										echo JHtml::_(	'image', 'administrator/components/com_sportsmanagement/assets/images/edit.png',
														$imageTitle,
														'title= "' . $imageTitle . '"' );
										?>
									</a>
								</td>
								<?php
							}
							?>
							<td>
								<?php
								echo $row->name;
								?>
							</td>
							<td>
								<?php
								echo $row->shortname;
								?>
							</td>
							<td>
								<?php
								echo $row->parent_name;
								?>
							</td>
                            <td>
                            <?php
								if (empty($row->picture) || !JFile::exists(JPATH_SITE.DS.$row->picture))
								{
									$imageTitle = JText::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_NO_IMAGE').$row->picture;
									echo JHtml::_(	'image','administrator/components/com_sportsmanagement/assets/images/delete.png',
													$imageTitle,'title= "'.$imageTitle.'"');
								}
								elseif ($row->picture == sportsmanagementHelper::getDefaultPlaceholder("player"))
								{
									$imageTitle = JText::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_DEFAULT_IMAGE');
									echo JHtml::_(	'image','administrator/components/com_sportsmanagement/assets/images/information.png',
													$imageTitle,'title= "'.$imageTitle.'"');
								}
								else
								{
									//$playerName = sportsmanagementHelper::formatName(null ,$row->firstname, $row->nickname, $row->lastname, 0);
									//echo sportsmanagementHelper::getPictureThumb($row->picture, $playerName, 0, 21, 4);
?>                                    
<a href="<?php echo JUri::root().$row->picture;?>" title="<?php echo $row->name;?>" class="modal">
<img src="<?php echo JUri::root().$row->picture;?>" alt="<?php echo $row->name;?>" width="20" />
</a>
<?PHP
								}
								?>
                            </td>
                            
                            <td class="center"><?php echo $published; ?></td>
							<td class="order">
								<span>
									<?php
									echo $this->pagination->orderUpIcon( $i, $i > 0 , 'divisions.orderup', 'COM_SPORTSMANAGEMENT_GLOBAL_ORDER_UP', true );
									?>
								</span>
								<span>
									<?php
									echo $this->pagination->orderDownIcon( $i, $n, $i < $n, 'divisions.orderdown', 'COM_SPORTSMANAGEMENT_GLOBAL_ORDER_DOWN', true );
									$disabled = true ?  '' : 'disabled="disabled"';
									?>
								</span>
								<input  type="text" name="order[]" size="5" value="<?php echo $row->ordering;?>" <?php echo $disabled; ?>
										class="text_area" style="text-align: center" />
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
	</fieldset>

	<input type="hidden" name="task"				value="" />
	<input type="hidden" name="boxchecked"			value="0" />
	<input type="hidden" name="filter_order"		value="<?php echo $this->sortColumn; ?>" />
	<input type="hidden" name="filter_order_Dir"	value="" />
	<?php echo JHtml::_( 'form.token' ); ?>
</form>
<?PHP
echo "<div>";
echo $this->loadTemplate('footer');
echo "</div>";
?>   