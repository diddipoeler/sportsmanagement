<?php 
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      default_data.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage persons
 */

defined('_JEXEC') or die('Restricted access');
jimport('joomla.filesystem.file');
JHtml::_('behavior.modal');
$user		= JFactory::getUser();
$userId		= $user->get('id');
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
					
					<th>
						<?php
						echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_PERSONS_F_NAME','pl.firstname',$this->sortDirection,$this->sortColumn);
						?>
					</th>
					<th>
						<?php
						echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_PERSONS_N_NAME','pl.nickname',$this->sortDirection,$this->sortColumn);
						?>
					</th>
					<th>
						<?php
						echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_PERSONS_L_NAME','pl.lastname',$this->sortDirection,$this->sortColumn);
						?>
					</th>
					<th><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_IMAGE'); ?>
					</th>
					<th>
						<?php
						echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_PERSONS_BIRTHDAY','pl.birthday',$this->sortDirection,$this->sortColumn);
						?>
					</th>
                    
                    <th class="title">
						<?php
						echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_AGEGROUP','ag.name',$this->sortDirection,$this->sortColumn);
						?>
					</th>
                    
					<th>
						<?php
						echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_PERSONS_NATIONALITY','pl.country',$this->sortDirection,$this->sortColumn);
						?>
					</th>
					<th>
						<?php
						echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_PERSONS_POSITION','pl.position_id',$this->sortDirection,$this->sortColumn);
						?>
					</th>
					<th>
					<?php
						echo JHtml::_('grid.sort','JSTATUS','pl.published',$this->sortDirection,$this->sortColumn);
						?>
					</th>
					<th class="nowrap">
						<?php
						echo JHtml::_('grid.sort','JGRID_HEADING_ID','pl.id',$this->sortDirection,$this->sortColumn);
						?>
					</th>
				</tr>
			</thead>
			<tfoot><tr><td colspan='6'><?php echo $this->pagination->getListFooter(); ?></td>
            <td colspan='6'>
            <?php echo $this->pagination->getResultsCounter();?>
            </td>
            </tr></tfoot>
			<tbody>
				<?php
				$k=0;
				for ($i=0,$n=count($this->items); $i < $n; $i++)
				{
					$row = &$this->items[$i];
					if (($row->firstname != '!Unknown') && ($row->lastname != '!Player')) // Ghostplayer for match-events
					{
						$link       = JRoute::_('index.php?option=com_sportsmanagement&task=person.edit&id='.$row->id);
						$canEdit	= $this->user->authorise('core.edit','com_sportsmanagement');
                        $canCheckin = $this->user->authorise('core.manage','com_checkin') || $row->checked_out == $this->user->get ('id') || $row->checked_out == 0;
                        $checked = JHtml::_('jgrid.checkedout', $i, $this->user->get ('id'), $row->checked_out_time, 'persons.', $canCheckin);
                        $canChange  = $this->user->authorise('core.edit.state', 'com_sportsmanagement.person.' . $row->id) && $canCheckin;

						?>
						<tr class="<?php echo "row$k"; ?>">
							<td class="center">
                        <?php
                        echo $this->pagination->getRowOffset($i);
                        ?>
                        </td>
                        <td class="center">
                        <?php 
                        echo JHtml::_('grid.id', $i, $row->id);  
                        ?>
                        </td>
							<?php
								$inputappend='';
								?>
								<td class="center">
                                <?php if ($row->checked_out) : ?>
						<?php echo JHtml::_('jgrid.checkedout', $i, $row->editor, $row->checked_out_time, 'persons.', $canCheckin); ?>
					<?php endif; ?>
					<?php if ($canEdit) : ?>
						<a href="<?php echo JRoute::_('index.php?option=com_sportsmanagement&task=person.edit&id='.(int) $row->id); ?>">
							<?php echo $this->escape($row->firstname.' '.$row->lastname); ?></a>
					<?php else : ?>
							<?php echo $this->escape($row->firstname.' '.$row->lastname); ?>
					<?php endif; ?>
							
								<input	<?php echo $inputappend; ?> type="text" size="15"
										class="form-control form-control-inline" name="firstname<?php echo $row->id; ?>"
										value="<?php echo stripslashes(htmlspecialchars($row->firstname)); ?>"
										onchange="document.getElementById('cb<?php echo $i; ?>').checked=true" />
							</td>
							<td class="center">
								<input	<?php echo $inputappend; ?> type="text" size="15"
										class="form-control form-control-inline" name="nickname<?php echo $row->id; ?>"
										value="<?php echo stripslashes(htmlspecialchars($row->nickname)); ?>"
										onchange="document.getElementById('cb<?php echo $i; ?>').checked=true" />
							</td>
							<td class="center">
								<input	<?php echo $inputappend; ?> type="text" size="15"
										class="form-control form-control-inline" name="lastname<?php echo $row->id; ?>"
										value="<?php echo stripslashes(htmlspecialchars($row->lastname)); ?>"
										onchange="document.getElementById('cb<?php echo $i; ?>').checked=true" />
							</td>
							<td class="center">
								<?php
                                $picture = ( $row->picture == sportsmanagementHelper::getDefaultPlaceholder("player") ) ? 'information.png' : 'ok.png'; 
                                $imageTitle = ( $row->picture == sportsmanagementHelper::getDefaultPlaceholder("player") ) ? JText::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_DEFAULT_IMAGE') : JText::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_IMAGE');
                                
echo JHtml::_(	'image','administrator/components/com_sportsmanagement/assets/images/'.$picture,
$imageTitle,'title= "'.$imageTitle.'"');
$playerName = sportsmanagementHelper::formatName(null ,$row->firstname, $row->nickname, $row->lastname, 0);
echo sportsmanagementHelper::getBootstrapModalImage('collapseModallogo_person'.$row->id,JURI::root().$row->picture,$playerName,'20',JURI::root().$row->picture); 
								?>
							</td>
							<td class="nowrap" class="center">
								<?php
								$append='';
                                $date1 = sportsmanagementHelper::convertDate($row->birthday, 1); 
                                if (($date1 == '00-00-0000') || ($date1 == ''))
				{
				$append=' style="background-color:#FFCCCC;" ';
				$date1 = '';
				}
/**
 * das wurde beim kalender geändert
 * $attribs = array(
 *			'onChange' => "alert('it works')",
 *			"showTime" => 'false',
 *			"todayBtn" => 'true',
 *			"weekNumbers" => 'false',
 *			"fillTable" => 'true',
 *			"singleHeader" => 'false',
 *		);
 *	echo JHtml::_('calendar', JFactory::getDate()->format('Y-m-d'), 'date', 'date', '%Y-%m-%d', $attribs); ?>
 */

                                
                                $attribs = array(
			'onChange' => "document.getElementById('cb".$i."').checked=true",
		);                          
                echo JHtml::calendar($date1,
				'birthday'.$row->id,
				'birthday'.$row->id,
				'%d-%m-%Y',
				$attribs);                         
//								}
								?>
							</td>
                            
                            <td class="center">
                        <?php 
                        //echo JText::_($row->agegroup); 
                        $inputappend = '';
                        $append = ' style="background-color:#bbffff"';
									echo JHtml::_('select.genericlist',
												$this->lists['agegroup'],
												'agegroup'.$row->id,
												$inputappend.'class="form-control form-control-inline" size="1" onchange="document.getElementById(\'cb' .
												$i.'\').checked=true"'.$append,
												'value','text',$row->agegroup_id);
                        ?>
                        </td>
                        
							<td class="nowrap" class="center">
								<?php
								$append='';
								if (empty($row->country))
                                {
                                    $append=' background-color:#FFCCCC;';
                                    }
								echo JHtmlSelect::genericlist($this->lists['nation'],
															'country'.$row->id,
															$inputappend.' class="form-control form-control-inline" style="width:140px; '.$append.'" onchange="document.getElementById(\'cb'.$i.'\').checked=true"',
															'value',
															'text',
															$row->country);
								?>
							</td>
							<td class="nowrap" class="center">
								<?php
								$append='';
								if (empty($row->position_id)){$append=' background-color:#FFCCCC;';}
								echo JHtmlSelect::genericlist($this->lists['positions'],
															'position'.$row->id,
															$inputappend.'class="form-control form-control-inline" style="width:140px; '.$append.'" onchange="document.getElementById(\'cb'.$i.'\').checked=true"',
															'value',
															'text',
															$row->position_id);
                                ?>
							</td>
							<td class="center">
                            <div class="btn-group">
            <?php echo JHtml::_('jgrid.published', $row->published, $i, 'persons.', $canChange, 'cb'); ?>
            <?php // Create dropdown items and render the dropdown list.
								if ($canChange)
								{
									JHtml::_('actionsdropdown.' . ((int) $row->published === 2 ? 'un' : '') . 'archive', 'cb' . $i, 'persons');
									JHtml::_('actionsdropdown.' . ((int) $row->published === -2 ? 'un' : '') . 'trash', 'cb' . $i, 'persons');
									echo JHtml::_('actionsdropdown.render', $this->escape($row->firstname.' '.$row->lastname));
								}
								?>
            </div>	
                            
                            
                            
                            </td>
							<td class="center"><?php echo $row->id; ?></td>
						</tr>
						<?php
						$k=1 - $k;
					}
				}
				?>
			</tbody>
		</table>
	</div>
