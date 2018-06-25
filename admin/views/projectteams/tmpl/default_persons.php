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
jimport('joomla.filesystem.file');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.modal');
$app = JFactory::getApplication();

?>
	<div id="editcell">
		<fieldset class="adminform">
			<legend><?php echo JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_LEGEND','<i>'.$this->project->name.'</i>'); ?></legend>
			<?php $cell_count=22; ?>
			<table class="<?php echo $this->table_data_class; ?>">
				<thead>
					<tr>
						<th width="5"><?php echo JText::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM'); ?></th>
						<th width="20">
							<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
						</th>
						<th width="20">&nbsp;</th>
						<th>
							<?php echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_TEAMNAME','t.lastname',$this->sortDirection,$this->sortColumn); ?>
							<a href="mailto:<?php
											$first_dest=1;
											foreach ($this->projectteam as $r)
											{
												if (($r->club_id) > 0 and strlen($r->club_email) > 0)
												{
													if (!$first_dest)
													{
														echo ",%20".str_replace(" ","%20",$r->club_email);
													}
													else
													{
														$first_dest=0;
														echo str_replace(" ","%20",$r->club_email);
													}
												}
											}
											?>?subject=[<?php echo $app->getCfg('sitename'); ?>]">
								<?php
								$imageFile='administrator/components/com_sportsmanagement/assets/images/mail.png';
								$imageTitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_SEND_MAIL_TEAMS');
								$imageParams='title= "'.$imageTitle.'"';
								$image=JHtml::image($imageFile,$imageTitle,$imageParams);
								$linkParams='';
								//echo JHtml::link($link3,$image);
								echo $image;
								?>
							</a>
						</th>
						
						<th>
							<?php echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_ADMIN','tl.admin',$this->sortDirection,$this->sortColumn); ?>
							<a href="mailto:<?php
											$first_dest=1;
											foreach ($this->projectteam as $r)
											{
												if (strlen($r->editor) > 0 and strlen($r->email) > 0)
												{
													if (!$first_dest)
													{
														echo ",%20".str_replace(" ","%20",$r->email);
													}
													else
													{
														$first_dest=0;
														echo str_replace(" ","%20",$r->email);
													}
												}
											}
											?>?subject=[<?php echo $app->getCfg('sitename'); ?>]">
								<?php
								$imageFile='administrator/components/com_sportsmanagement/assets/images/mail.png';
								$imageTitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_SEND_MAIL_ADMINS');
								$imageParams='title= "'.$imageTitle.'"';
								$image=JHtml::image($imageFile,$imageTitle,$imageParams);
								$linkParams='';
								//echo JHtml::link($link3,$image);
								echo $image;
								?></a>
						</th>
						<?php
						if ($this->project->project_type == 'DIVISIONS_LEAGUE')
						{
							$cell_count++;
							?><th>
								<?php echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_DIVISION','d.name',$this->sortDirection,$this->sortColumn);
									echo '<br>'.JHtml::_(	'select.genericlist',
														$this->lists['divisions'],
														'division',
														'class="inputbox" size="1" onchange="window.location.href=window.location.href.split(\'&division=\')[0]+\'&division=\'+this.value"',
														'value','text', $this->division);
//echo $this->lists['divisions'];
								?>
							</th><?php
						}
						?>
						<th>
							<?php echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_PICTURE','tl.picture',$this->sortDirection,$this->sortColumn); ?>
						</th>
						<th><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_INITIAL_POINTS'); ?></th>
						<th><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_MA'); ?></th>
						<th><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_PLUS_P'); ?></th>
						<th><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_MINUS_P'); ?></th>
                        <th><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_PENALTY_P'); ?></th>
						<th><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_W'); ?></th>
						<th><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_D'); ?></th>
						<th><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_L'); ?></th>
						<th><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_HG'); ?></th>
						<th><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_GG'); ?></th>
						<th><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_DG'); ?></th>
                        
                        <th><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_IS_IN_SCORE'); ?></th>
                        <th><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_USE_FINALLY'); ?></th>
                        
                        
						<th width="1%">
							<?php echo JHtml::_('grid.sort','TID','team_id',$this->sortDirection,$this->sortColumn); ?>
						</th>
						<th width="1%">
							<?php echo JHtml::_('grid.sort','JGRID_HEADING_ID','tl.id',$this->sortDirection,$this->sortColumn); ?>
						</th>
					</tr>
				</thead>
				<tfoot><tr><td colspan="<?php echo $cell_count; ?>"><?php echo $this->pagination->getListFooter(); ?></td></tr></tfoot>
				<tbody>
					<?php
					$k=0;
					for ($i=0, $n=count($this->projectteam); $i < $n; $i++)
					{
						$row = &$this->projectteam[$i];
						$link1=JRoute::_('index.php?option=com_sportsmanagement&task=projectteam.edit&id='.$row->id.'&pid='.$this->project->id."&team_id=".$row->team_id );
						$link2=JRoute::_('index.php?option=com_sportsmanagement&view=teamplayers&project_team_id='.$row->id."&team_id=".$row->team_id.'&pid='.$this->project->id);
						$link3=JRoute::_('index.php?option=com_sportsmanagement&view=teamstaffs&project_team_id='.$row->id."&team_id=".$row->team_id.'&pid='.$this->project->id);
						$checked=JHtml::_('grid.checkedout',$row,$i);
						?>
						<tr class="<?php echo "row$k"; ?>">
							<td class="center"><?php echo $this->pagination->getRowOffset($i); ?></td>
							<td class="center"><?php echo $checked;?></td>
							<?php
							if ($this->table->isCheckedOut($this->user->get('id'),$row->checked_out))
							{
								$inputappend=' disabled="disabled"';
								?><td class="center">&nbsp;</td><?php
							}
							else
							{
								$inputappend='';
								?>
								<td style="text-align:center; "><?php
									$imageFile='administrator/components/com_sportsmanagement/assets/images/edit.png';
									$imageTitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_EDIT_DETAILS');
									$imageParams='title= "'.$imageTitle.'"';
									$image=JHtml::image($imageFile,$imageTitle,$imageParams);
									$linkParams='';
									echo JHtml::link($link1,$image);
									?></td>
								<?php
							}
							?>
							<td><?php echo $row->name; ?></td>
							
							
							<td class="center"><?php echo $row->editor; ?></td>
							<?php
							if ($this->project->project_type == 'DIVISIONS_LEAGUE')
							{
								?>
								<td class="nowrap" class="center">
									<?php
									$append='';
									if ($row->division_id == 0)
									{
										$append=' style="background-color:#bbffff"';
									}
									echo JHtml::_(	'select.genericlist',
													$this->lists['divisions'],
													'division_id'.$row->id,
													$inputappend.'class="inputbox" size="1" onchange="document.getElementById(\'cb' .
													$i.'\').checked=true"'.$append,
													'value','text',$row->division_id);
									?>
								</td>
								<?php
							}
							?>
							<td class="center">
								<?php
								if (empty($row->picture) || !JFile::exists(JPATH_SITE.DS.$row->picture))
								{
									$imageTitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_NO_IMAGE').$row->picture;
									echo JHtml::image(	'administrator/components/com_sportsmanagement/assets/images/delete.png',
														$imageTitle,'title= "'.$imageTitle.'"');
								}
								elseif ($row->picture == sportsmanagementHelper::getDefaultPlaceholder("team"))
								{
									$imageTitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_DEFAULT_IMAGE');
									echo JHtml::image('administrator/components/com_sportsmanagement/assets/images/information.png',
														$imageTitle,'title= "'.$imageTitle.'"');
								}
								else
								{
									$imageTitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_CUSTOM_IMAGE');
									$imageParams=array();
									$imageParams['title']=$imageTitle ;
									$imageParams['height']=30;
									//$imageParams['width'] =40;
									echo JHtml::image($row->picture,$imageTitle,$imageParams);
								}
								?>
							</td>
							<td class="center">
								<input<?php echo $inputappend; ?>	type="text" size="1" class="inputbox"
																	name="start_points<?php echo $row->id; ?>"
																	value="<?php echo $row->start_points; ?>"
																	onchange="document.getElementById('cb<?php echo $i; ?>').checked=true" />
							</td>
							<td class="center">
								<input<?php echo $inputappend; ?>	type="text" size="2" class="inputbox"
																	name="matches_finally<?php echo $row->id; ?>"
																	value="<?php echo $row->matches_finally; ?>"
																	onchange="document.getElementById('cb<?php echo $i; ?>').checked=true" />
							</td>
							<td class="center">
								<input<?php echo $inputappend; ?>	type="text" size="2" class="inputbox"
																	name="points_finally<?php echo $row->id; ?>"
																	value="<?php echo $row->points_finally; ?>"
																	onchange="document.getElementById('cb<?php echo $i; ?>').checked=true" />
							</td>
							<td class="center">
								<input<?php echo $inputappend; ?>	type="text" size="2" class="inputbox"
																	name="neg_points_finally<?php echo $row->id; ?>"
																	value="<?php echo $row->neg_points_finally; ?>"
																	onchange="document.getElementById('cb<?php echo $i; ?>').checked=true" />
							</td>
                            <td class="center">
								<input<?php echo $inputappend; ?>	type="text" size="2" class="inputbox"
																	name="penalty_points<?php echo $row->id; ?>"
																	value="<?php echo $row->penalty_points; ?>"
																	onchange="document.getElementById('cb<?php echo $i; ?>').checked=true" />
							</td>
                            
							<td class="center">
								<input<?php echo $inputappend; ?>	type="text" size="2" class="inputbox"
																	name="won_finally<?php echo $row->id; ?>"
																	value="<?php echo $row->won_finally; ?>"
																	onchange="document.getElementById('cb<?php echo $i; ?>').checked=true" />
							</td>
							<td class="center">
								<input<?php echo $inputappend; ?>	type="text" size="2" class="inputbox"
																	name="draws_finally<?php echo $row->id; ?>"
																	value="<?php echo $row->draws_finally; ?>"
																	onchange="document.getElementById('cb<?php echo $i; ?>').checked=true" />
							</td>
							<td class="center">
								<input<?php echo $inputappend; ?>	type="text" size="2" class="inputbox"
																	name="lost_finally<?php echo $row->id; ?>"
																	value="<?php echo $row->lost_finally; ?>"
																	onchange="document.getElementById('cb<?php echo $i; ?>').checked=true" />
							</td>
							<td class="center">
								<input<?php echo $inputappend; ?>	type="text" size="2" class="inputbox"
																	name="homegoals_finally<?php echo $row->id; ?>"
																	value="<?php echo $row->homegoals_finally; ?>"
																	onchange="document.getElementById('cb<?php echo $i; ?>').checked=true" />
							</td>
							<td class="center">
								<input<?php echo $inputappend; ?>	type="text" size="2" class="inputbox"
																	name="guestgoals_finally<?php echo $row->id; ?>"
																	value="<?php echo $row->guestgoals_finally; ?>"
																	onchange="document.getElementById('cb<?php echo $i; ?>').checked=true" />
							</td>
							<td class="center">
								<input<?php echo $inputappend; ?>	type="text" size="2" class="inputbox"
																	name="diffgoals_finally<?php echo $row->id; ?>"
																	value="<?php echo $row->diffgoals_finally; ?>"
																	onchange="document.getElementById('cb<?php echo $i; ?>').checked=true" />
							</td>
                            
                            <td class="center">
									<?php
                                    $append=' style="background-color:#bbffff"';
									echo JHtml::_(	'select.genericlist',
													$this->lists['is_in_score'],
													'is_in_score'.$row->id,
													$inputappend.'class="inputbox" size="1" onchange="document.getElementById(\'cb' .
													$i.'\').checked=true"'.$append,
													'value','text',$row->is_in_score);
									?>
								</td>
                            <td class="center">
									<?php
                                    $append=' style="background-color:#bbffff"';
									echo JHtml::_(	'select.genericlist',
													$this->lists['use_finally'],
													'use_finally'.$row->id,
													$inputappend.'class="inputbox" size="1" onchange="document.getElementById(\'cb' .
													$i.'\').checked=true"'.$append,
													'value','text',$row->use_finally);
									?>
								</td>
                            
                            
							<td class="center"><?php echo $row->team_id; ?></td>
							<td class="center"><?php echo $row->id; ?></td>
						</tr>
						<?php
						$k=(1-$k);
					}
					?>
				</tbody>

			</table>
		</fieldset>
	</div>
	