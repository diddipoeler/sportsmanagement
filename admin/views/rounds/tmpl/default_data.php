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
JHtml::_('behavior.tooltip');
JHtml::_('behavior.modal');
$templatesToLoad = array('footer','listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>
<div class="table-responsive">

<!--	<div id="editcell"> -->
	<!--	<fieldset class="adminform"> -->
			<legend><?php echo JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_LEGEND','<i>'.$this->project->name.'</i>'); ?></legend>
			<table class="<?php echo $this->table_data_class; ?>">
				<thead>
					<tr>
						<th width="1%"><?php echo JText::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM'); ?></th>
						<th width="1%"><input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" /></th>
					<!--	<th width="20">&nbsp;</th> -->
 						<th width="20"><?php echo JHtml::_( 'grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_ROUND_NR', 'r.roundcode', $this->sortDirection, $this->sortColumn ); ?></th>
                        <th width="20"><?php echo JHtml::_( 'grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_ROUND_TITLE', 'r.name', $this->sortDirection, $this->sortColumn ); ?></th>
                        
                        <th width="20"><?php echo JHtml::_( 'grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_STARTDATE', 'r.round_date_first', $this->sortDirection, $this->sortColumn ); ?></th>
                        
						<th width="1%">&nbsp;</th>
                        <th width="20"><?php echo JHtml::_( 'grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_ENDDATE', 'r.round_date_last', $this->sortDirection, $this->sortColumn ); ?></th>
                        
						<th width="10%"><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_EDIT_MATCHES'); ?></th>
						<th width="20"><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_PUBLISHED_CHECK'); ?></th>
						<th width="20"><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_RESULT_CHECK'); ?></th>
                        <th width="20"><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_TOURNEMENT'); ?></th>
						<th width="5%" class="title">
						<?php
						echo JHtml::_('grid.sort','JSTATUS','r.published',$this->sortDirection,$this->sortColumn);
						?>
					</th>
            <th width="5%"><?php echo JHtml::_( 'grid.sort', 'JGRID_HEADING_ID', 'r.id', $this->sortDirection, $this->sortColumn ); ?></th>
					</tr>
				</thead>
                
				<tfoot>
                <tr>
                <td colspan="11"><?php echo $this->pagination->getListFooter(); ?></td>
                <td colspan="3"><?php echo $this->pagination->getResultsCounter();?></td>
                </tr>
                </tfoot>
                
				<tbody>
					<?php
					$k=0;
					for ($i=0,$n=count($this->matchday); $i < $n; $i++)
					{
						$row =& $this->matchday[$i];
						$link1 = JRoute::_('index.php?option=com_sportsmanagement&task=round.edit&id='.$row->id.'&pid='.$this->project->id);
						$link2 = JRoute::_('index.php?option=com_sportsmanagement&view=matches&rid='.$row->id.'&pid='.$this->project->id);
                        $canEdit	= $this->user->authorise('core.edit','com_sportsmanagement');
                        $canCheckin = $this->user->authorise('core.manage','com_checkin') || $row->checked_out == $this->user->get ('id') || $row->checked_out == 0;
                        $checked = JHtml::_('jgrid.checkedout', $i, $this->user->get ('id'), $row->checked_out_time, 'rounds.', $canCheckin);
                        $canChange  = $this->user->authorise('core.edit.state', 'com_sportsmanagement.round.' . $row->id) && $canCheckin;
						?>
						<tr class="<?php echo "row$k"; ?>">
							<td class="center">
                            <?php 
                            echo $this->pagination->getRowOffset($i); ?>
                            </td>
							<td class="center">
                            <?php echo JHtml::_('grid.id', $i, $row->id); ?>
                          <!--  </td> -->
						<!--	<td class="center"> -->
                            
                            <?php
                            if ($row->checked_out) : ?>
										<?php echo JHtml::_('jgrid.checkedout', $i, $this->user->get ('id'), $row->checked_out_time, 'rounds.', $canCheckin); ?>
									<?php endif; ?>
                            <?php
                            if ($canEdit && !$row->checked_out ) :
								$imageTitle = JText::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_EDIT_DETAILS');
								$imageFile = 'administrator/components/com_sportsmanagement/assets/images/edit.png';
								$imageParams = "title='$imageTitle'";
								echo JHtml::link($link1,JHtml::image($imageFile,$imageTitle,$imageParams));
                            endif;    
							?>
                            </td>
							<td class="center">
								<input tabindex="1" type="text" style="text-align: center" size="5" class="form-control form-control-inline" name="roundcode<?php echo $row->id; ?>" value="<?php echo $row->roundcode; ?>" onchange="document.getElementById('cb<?php echo $i; ?>').checked=true" />
							</td>
							<td class="center">
								<input tabindex="2" type="text" size="30" maxlength="64" class="form-control form-control-inline" name="name<?php echo $row->id; ?>" value="<?php echo $row->name; ?>" onchange="document.getElementById('cb<?php echo $i; ?>').checked=true" />
							<p class="smallsub">
						<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($row->alias));?></p>
                            </td>
							<td class="center">
								<?php

/**
 * das wurde beim kalender geändert
  $attribs = array(
			'onChange' => "alert('it works')",
			"showTime" => 'false',
			"todayBtn" => 'true',
			"weekNumbers" => 'false',
			"fillTable" => 'true',
			"singleHeader" => 'false',
		);
	echo JHtml::_('calendar', JFactory::getDate()->format('Y-m-d'), 'date', 'date', '%Y-%m-%d', $attribs); ?>
 */

                                
                                $attribs = array(
			'onChange' => "document.getElementById('cb".$i."').checked=true",
		);
								$date1 = sportsmanagementHelper::convertDate($row->round_date_first, 1);
								$append = '';
								if (($date1 == '00-00-0000') || ($date1 == ''))
								{
									$append = ' style="background-color:#FFCCCC;" ';
                                    $date1 = '';
								}
								echo JHtml::calendar($date1,
													'round_date_first'.$row->id,
													'round_date_first'.$row->id,
													'%d-%m-%Y', 
                                                    $attribs);
								?>
							</td>
							<td class="center">&nbsp;-&nbsp;</td>
							<td class="center">
                            <?php
								$date2 = sportsmanagementHelper::convertDate($row->round_date_last, 1);
								$append = '';
								if (($date2 == '00-00-0000') || ($date2 == ''))
								{
									$append = ' style="background-color:#FFCCCC;"';
                                    $date2 = '';
								}
								echo JHtml::calendar($date2,
													'round_date_last'.$row->id,
													'round_date_last'.$row->id,
													'%d-%m-%Y',
                                                    $attribs);
								?>
                                </td>
							<td class="center" class="nowrap">
                            <?php
								$link2Title=JText::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_EDIT_MATCHES_LINK');
								$link2Params="title='$link2Title'";
								echo JHtml::link($link2,$link2Title,$link2Params);
					  			?>
                                  </td>
							<td class="center" class="nowrap">
                            <?php
								if (($row->countUnPublished == 0) && ($row->countMatches > 0))
								{
									$imageTitle=JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_ALL_PUBLISHED',$row->countMatches);
									$imageFile='administrator/components/com_sportsmanagement/assets/images/ok.png';
									$imageParams="title='$imageTitle'";
									echo JHtml::image($imageFile,$imageTitle,$imageParams);
								}
								else
								{
									if ($row->countMatches == 0)
									{
										$imageTitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_ANY_MATCHES');
									}
									else
									{
										$imageTitle=JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_PUBLISHED_NR',$row->countUnPublished);
									}
									$imageFile='administrator/components/com_sportsmanagement/assets/images/error.png';
									$imageParams="title='$imageTitle'";
									echo JHtml::image($imageFile,$imageTitle,$imageParams);
								}
								?>
                                </td>
					  		<td class="center" class="nowrap">
                              <?php
								if (($row->countNoResults == 0) && ($row->countMatches > 0))
								{
									$imageTitle=JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_ALL_RESULTS',$row->countMatches);
									$imageFile='administrator/components/com_sportsmanagement/assets/images/ok.png';
									$imageParams="title='$imageTitle'";
									echo JHtml::image($imageFile,$imageTitle,$imageParams);
								}
								else
								{
									if ($row->countMatches == 0)
									{
										$imageTitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_ANY_MATCHES');
									}
									else
									{
										$imageTitle=JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_RESULTS_MISSING',$row->countNoResults);
									}
									$imageFile='administrator/components/com_sportsmanagement/assets/images/error.png';
									$imageParams="title='$imageTitle'";
									echo JHtml::image($imageFile,$imageTitle,$imageParams);
								}
								?>
                                </td>
                                                                
                                <td class="center">
									<?php
                                    $append=' style="background-color:#bbffff"';
									echo JHtml::_(	'select.genericlist',
													$this->lists['tournementround'],
													'tournementround'.$row->id,
													'class="form-control form-control-inline" size="1" onchange="document.getElementById(\'cb' .
													$i.'\').checked=true"'.$append,
													'value','text',$row->tournement);
									?>
								</td>
                                
                <td class="center">
<div class="btn-group">
            <?php echo JHtml::_('jgrid.published', $row->published, $i, 'rounds.', $canChange, 'cb'); ?>
            <?php 
            // Create dropdown items and render the dropdown list.
								if ($canChange)
								{
									JHtml::_('actionsdropdown.' . ((int) $row->published === 2 ? 'un' : '') . 'archive', 'cb' . $i, 'rounds');
									JHtml::_('actionsdropdown.' . ((int) $row->published === -2 ? 'un' : '') . 'trash', 'cb' . $i, 'rounds');
									echo JHtml::_('actionsdropdown.render', $this->escape($row->name));
								}
								?>
            </div>
                
                
                </td>
							<td class="center"><?php echo $row->id; ?></td>
						</tr>
						<?php
						$k=1 - $k;
					}
					?>
				</tbody>
			</table>
	<!--	</fieldset> -->
	</div> 
	