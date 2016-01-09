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
JHtml::_('behavior.tooltip');
JHtml::_('behavior.modal');

?>

		<table class="<?php echo $this->table_data_class; ?>">
			<thead>
				<tr>
					<th width=""><?php echo JText::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM'); ?></th>
					<th width="40" class="">
						<input  type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
					</th>
					
					<th class="title">
						<?php
						echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_NAME_OF_PROJECT','p.name',$this->sortDirection,$this->sortColumn);
						?>
					</th>
					<th class="title">
						<?php
						echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_LEAGUE','l.name',$this->sortDirection,$this->sortColumn);
						?>
					</th>
                    <th width="">
						<?php
						echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_LEAGUES_COUNTRY','l.country',$this->sortDirection,$this->sortColumn);
						?>
					</th>
					<th class="title">
						<?php
						echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SEASON','s.name',$this->sortDirection,$this->sortColumn);
						?>
					</th>
					<th class="title">
						<?php
						echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SPORTSTYPE','st.name',$this->sortDirection,$this->sortColumn);
						?>
					</th>
                    
                    <th class="title">
						<?php
						echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_AGEGROUP','ag.name',$this->sortDirection,$this->sortColumn);
						?>
					</th>
                    
					<th class="title">
						<?php
						echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_PROJECTTYPE','p.project_type',$this->sortDirection,$this->sortColumn);
						?>
					</th>
                    <th><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_IMAGE'); ?>
					</th>
					<th width="" class="title">
						<?php
						echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_GAMES');
						?>
					</th>
                    <th class="title">
						<?php
						echo JText::_('COM_SPORTSMANAGEMENT_D_MENU_TEAMS');
						?>
					</th>
                    <th width="" class="title">
						<?php
						echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_ROUND');
						?>
					</th>
                    <th width="" class="title">
						<?php
						echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_DIVISION');
						?>
					</th>
                    <th width="" class="title">
						<?php
						echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_USER_FIELD');
						?>
					</th>
                    
                    
					<th width="" class="title">
						<?php
						echo JHtml::_('grid.sort','JSTATUS','p.published',$this->sortDirection,$this->sortColumn);
						?>
					</th>
					<th width="" class="title">
						<?php
						echo JHtml::_('grid.sort','JGRID_HEADING_ORDERING','p.ordering',$this->sortDirection,$this->sortColumn);
						echo JHtml::_('grid.order', $this->items, 'filesave.png', 'projects.saveorder');
						?>
					</th>
					<th width="" class="title">
						<?php
						echo JHtml::_('grid.sort','JGRID_HEADING_ID','p.id',$this->sortDirection,$this->sortColumn);
						?>
					</th>
                    
                    <th width="" class="title">
						<?php
						echo JText::_('JGLOBAL_FIELD_MODIFIED_LABEL');
						?>
					</th>
                    <th width="" class="title">
						<?php
						echo JText::_('JGLOBAL_FIELD_MODIFIED_BY_LABEL');
						?>
					</th>
                    
				</tr>
			</thead>
			<tfoot>
            <tr>
            <td colspan='15'><?php echo $this->pagination->getListFooter(); ?>
            </td>
            <td colspan="6"><?php echo $this->pagination->getResultsCounter(); ?>
            </td>
            </tr>
            </tfoot>
			<tbody>
				<?php
				$k=0;
				for ($i=0,$n=count($this->items); $i < $n; $i++)
				{
					$row =& $this->items[$i];

					$link = JRoute::_('index.php?option=com_sportsmanagement&task=project.edit&id='.$row->id);
					$link2 = JRoute::_('index.php?option=com_sportsmanagement&view=projects&task=project.display&id='.$row->id);
					$link2panel = JRoute::_('index.php?option=com_sportsmanagement&task=project.edit&layout=panel&pid='.$row->id.'&stid='.$row->sports_type_id.'&id='.$row->id   );
                    $link2teams = JRoute::_('index.php?option=com_sportsmanagement&view=projectteams&pid='.$row->id.'&id='.$row->id   );
                    
                    $link2rounds = JRoute::_('index.php?option=com_sportsmanagement&view=rounds&pid='.$row->id );
                    $link2divisions = JRoute::_('index.php?option=com_sportsmanagement&view=divisions&pid='.$row->id );
                    
                    $canEdit	= $this->user->authorise('core.edit','com_sportsmanagement');
                    $canCheckin = $this->user->authorise('core.manage','com_checkin') || $row->checked_out == $this->user->get ('id') || $row->checked_out == 0;

					$checked = JHtml::_('jgrid.checkedout', $i, $this->user->get ('id'), $row->checked_out_time, 'projects.', $canCheckin);
					$published = JHtml::_('grid.published',$row,$i,'tick.png','publish_x.png','projects.');
					?>
					<tr class="<?php echo "row$k"; ?>">
						<td class="center"><?php echo $this->pagination->getRowOffset($i); ?></td>
						<td width="40" class=""><?php echo JHtml::_('grid.id', $i, $row->id); ?>
						<?php
                        						
							$inputappend = '';
							?>
							
                            <?php
                            if ( ( $row->checked_out != $this->user->get ('id') ) && $row->checked_out ) : ?>
										<?php echo JHtml::_('jgrid.checkedout', $i, $this->user->get ('id'), $row->checked_out_time, 'projects.', $canCheckin); ?>
									<?php else: ?>
								<a href="<?php echo $link; ?>">
                                <?php
									$imageTitle = JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_EDIT_DETAILS');
									echo JHtml::_(	'image','administrator/components/com_sportsmanagement/assets/images/edit.png',
													$imageTitle,'title= "'.$imageTitle.'"');
									?>
                                
									
								</a>
							<?php
						endif;
						?>
                            </td>
							
						<td>
							<?php
							
								?><a href="<?php echo $link2panel; ?>"><?php echo $row->name; ?></a><?php
							
							?>
                            <p class="smallsub">
						<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($row->alias));?></p>
						</td>
						<td><?php echo $row->league; ?></td>
                        <td class="center"><?php echo JSMCountries::getCountryFlag($row->country); ?></td>
						<td class="center"><?php echo $row->season; ?></td>
                        
						<td class="center">
                        <?php 
                        echo JText::_($row->sportstype); 
                        ?>
                        </td>
                        
                        <td class="center">
                        <?php 
                        //echo JText::_($row->agegroup); 
                        $inputappend = '';
                        $append = ' style="background-color:#bbffff"';
									echo JHtml::_(	'select.genericlist',
													$this->lists['agegroup'],
													'agegroup'.$row->id,
													$inputappend.'class="inputbox" size="1" onchange="document.getElementById(\'cb' .
													$i.'\').checked=true"'.$append,
													'value','text',$row->agegroup_id);
                        ?>
                        </td>
						<td class="center">
                        <?php 
                        $inputappend = '';
                        $append = ' style="background-color:#bbffff"';
									echo JHtml::_(	'select.genericlist',
													$this->lists['project_type'],
													'project_type'.$row->id,
													$inputappend.'class="inputbox" size="1" onchange="document.getElementById(\'cb' .
													$i.'\').checked=true"'.$append,
													'value','text',$row->project_type);
                        ?>
                        </td>
						
                        <td class="center">
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
<a href="<?php echo JURI::root().$row->picture;?>" title="<?php echo $row->name;?>" class="modal">
<img src="<?php echo JURI::root().$row->picture;?>" alt="<?php echo $row->name;?>" width="20" />
</a>
<?PHP
								}
								?>
							</td>
                        
                        
                        <td class="center">
							<?php if ($row->current_round): ?>
								<?php echo JHtml::link('index.php?option=com_sportsmanagement&view=matches&pid='.$row->id.'&rid='. $row->current_round,
								                       JHtml::image(JUri::root().'administrator/components/com_sportsmanagement/assets/images/icon-16-Matchdays.png', JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_GAMES_DETAILS'))); ?>
							<?php endif; ?>
						</td>
						<td class="center">
                        <a href="<?php echo $link2teams; ?>"><?php echo $row->proteams; ?></a>
                        </td>
                        
                        <td class="center">
                        <a href="<?php echo $link2rounds; ?>"><?php echo $this->modelround->getRoundsCount($row->id); ?></a>
                        </td>
                        
                        <td class="center">
                        <a href="<?php echo $link2divisions; ?>"><?php echo $this->modeldivision->getProjectDivisionsCount($row->id); ?></a>
                        </td>
                        
                        <td class="center">
                        <?php 
                        echo $row->user_field; 
                        echo JHtml::link('index.php?option=com_sportsmanagement&view='. $row->user_field.'&pid='.$row->id,
								                       JHtml::image(JUri::root().'administrator/components/com_sportsmanagement/assets/images/information.png', JText::_($row->user_field)));
                        
                        ?>
                        </td>
                        
                        <td class="center"><?php echo $published; ?></td>
						<td class="order">
							<span><?php echo $this->pagination->orderUpIcon($i,$i > 0 ,'projects.orderup','JLIB_HTML_MOVE_UP',true); ?></span>
							<span><?php echo $this->pagination->orderDownIcon($i,$n,$i < $n,'projects.orderdown','JLIB_HTML_MOVE_DOWN',true); ?></span>
							<?php $disabled=true ?  '' : 'disabled="disabled"';	?>
							<input type="text" name="order[]" size="5" value="<?php echo $row->ordering;?>" <?php echo $disabled ?> class="text_area" style="text-align: center" />
						</td>
						<td class="center"><?php echo $row->id; ?></td>
                        <td class="center"><?php echo $row->modified; ?></td>
                        <td class="center"><?php echo $row->username; ?></td>
					</tr>
					<?php
					$k=1 - $k;
				}
				?>
			</tbody>
		</table>
<!--	</div> -->
