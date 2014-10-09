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
$templatesToLoad = array('footer','listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
// welche joomla version
if(version_compare(JVERSION,'3.0.0','ge')) 
{
JHtml::_('behavior.framework', true);
}
else
{
JHtml::_( 'behavior.mootools' );    
}
?>

<!-- 	<fieldset class="adminform"> -->
		<legend>
			<?php
			echo JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_PREF_TITLE2','<i>'.$this->project->name.'</i>');
			?>
		</legend>
		
		<div id="editcell">
			<table class="<?php echo $this->table_data_class; ?>">
				<thead>
					<tr>
						<th width="5">
							<?php
							echo JText::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM');
							?>
						</th>
						<th width="20">
							<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
						</th>
						<th width="20">
							&nbsp;
						</th>
						<th>
							<?php
							echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_PREF_NAME','p.lastname',$this->sortDirection,$this->sortColumn);
							?>
						</th>
						<th width="20">
							<?php
							echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_PREF_PID');
							?>
						</th>
						<th>
							<?php
							echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_PREF_IMAGE');
							?>
						</th>
						<th>
							<?php
							echo JHtml::_('grid.sort',JText::_('COM_SPORTSMANAGEMENT_ADMIN_PREF_POS'),'pref.project_position_id',$this->sortDirection,$this->sortColumn);
							?>
						</th>
						<th>
						<?php
						echo JHtml::_('grid.sort','JSTATUS','pref.published',$this->sortDirection,$this->sortColumn);
						?>
						</th>
						<th width="10%">
							<?php
							echo JHtml::_('grid.sort',JText::_('JGRID_HEADING_ORDERING'),'pref.ordering',$this->sortDirection,$this->sortColumn);
							echo JHtml::_('grid.order',$this->items, 'filesave.png', 'projectreferees.saveorder');
							?>
						</th>
						<th width="5%">
							<?php
							echo JHtml::_('grid.sort','JGRID_HEADING_ID','p.id',$this->sortDirection,$this->sortColumn);
							?>
						</th>
					</tr>
				</thead>
				<tfoot><tr><td colspan='12'><?php echo $this->pagination->getListFooter(); ?></td></tr></tfoot>
				<tbody>
					<?php
					$k=0;
					for ($i=0,$n=count($this->items); $i < $n; $i++)
					{
						$row =& $this->items[$i];
						$link = JRoute::_('index.php?option=com_sportsmanagement&task=projectreferee.edit&id='.$row->id.'&pid='.$row->project_id.'&person_id='.$row->person_id  );
						$checked = JHtml::_('grid.checkedout',$row,$i);
                        $canCheckin = $this->user->authorise('core.manage','com_checkin') || $row->checked_out == $this->user->get ('id') || $row->checked_out == 0;
						$inputappend='';
						?>
						<tr class="<?php echo "row$k"; ?>">
							<td class="center">
								<?php
								echo $this->pagination->getRowOffset($i);
								?>
							</td>
							<td class="center">
								<?php
								echo $checked;
								?>
							</td>
							
								<td class="center">
                                <?php
                            if ($row->checked_out) : ?>
										<?php echo JHtml::_('jgrid.checkedout', $i, $this->user->get ('id'), $row->checked_out_time, 'projectreferees.', $canCheckin); ?>
									<?php endif; ?>	
									<a href="<?php echo $link; ?>">
										<?php
										$imageTitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_PREF_EDIT_DETAILS');
										echo JHtml::_(	'image','administrator/components/com_sportsmanagement/assets/images/edit.png',
														$imageTitle,
														'title= "'.$imageTitle.'"');
										?>
									</a>
								</td>
								<?php
							
							?>
							<td>
								<?php echo sportsmanagementHelper::formatName(null, $row->firstname, $row->nickname, $row->lastname, 1) ?>
							</td>
							<td class="center">
								<?php
								echo $row->person_id;
								?>
							</td>
							<td class="center">
								<?php
								if ($row->picture == '')
								{
									$imageTitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_PREF_NO_IMAGE');
									echo JHtml::_(	'image',
													'administrator/components/com_sportsmanagement/assets/images/delete.png',
													$imageTitle,
													'title= "'.$imageTitle.'"');

								}
								elseif ($row->picture == sportsmanagementHelper::getDefaultPlaceholder("player"))
								{
										$imageTitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_PREF_DEFAULT_IMAGE');
										echo JHtml::_(	'image',
														'administrator/components/com_sportsmanagement/assets/images/information.png',
														$imageTitle,
														'title= "'.$imageTitle.'"');
								}
								elseif ($row->picture == !'')
								{
									$playerName = sportsmanagementHelper::formatName(null ,$row->firstname, $row->nickname, $row->lastname, 0);
									$picture=JPATH_SITE.DS.$row->picture;
									echo sportsmanagementHelper::getPictureThumb($picture, $playerName, 0, 21, 4);
								}
								?>
							</td>
							<td class="center">
								<?php
								if ($row->project_position_id != 0)
								{
									$selectedvalue=$row->project_position_id;
									$append='';
								}
								else
								{
									$selectedvalue=0;
									$append=' style="background-color:#FFCCCC"';
								}

								if ($append != '')
								{
									?>
									<script language="javascript">document.getElementById('cb<?php echo $i; ?>').checked=true;</script>
									<?php
								}

								if ($row->project_position_id == 0)
								{
									$append=' style="background-color:#FFCCCC"';
								}

								echo JHtml::_('select.genericlist',
												$this->lists['project_position_id'],'project_position_id'.$row->id,
												$inputappend.'class="inputbox" size="1" onchange="document.getElementById(\'cb'.$i.'\').checked=true"'.$append,
												'value','text',$selectedvalue);
								?>
							</td>
							<td class="center">
								<?php
								echo JHtml::_('grid.published',$row,$i, 'tick.png','publish_x.png','projectreferees.');
								?>
							</td>
							<td class="order">
								<span>
									<?php
									echo $this->pagination->orderUpIcon($i,$i > 0,'projectreferees.orderup','JLIB_HTML_MOVE_UP',true);
									?>
								</span>
								<span>
									<?php
									echo $this->pagination->orderDownIcon($i,$n,$i < $n,'projectreferees.orderdown','JLIB_HTML_MOVE_DOWN',true);
									?>
								</span>
								<?php
								$disabled=true ?	'' : 'disabled="disabled"';
								?>
								<input	type="text" name="order[]" size="5"
										value="<?php echo $row->ordering; ?>" <?php echo $disabled; ?> class="text_area"
										style="text-align: center; " />
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
<!--	</fieldset> -->
