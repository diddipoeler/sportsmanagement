<?php 
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      assignplayers.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage persons
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

JHtml::_('behavior.tooltip');
jimport('joomla.filesystem.file');
$user		= JFactory::getUser();
$userId		= $user->get('id');

?>
<script>
	function searchPerson(val)
	{
        var s= document.getElementById("filter_search");
        s.value = val;
        Joomla.submitform('', this.form)
	}

	
</script>
<form action="<?php echo JRoute::_('index.php?option=com_sportsmanagement&view=persons&layout=assignplayers&tmpl=component&type='.$this->type);?>" method="post" id="adminForm" name="adminForm">
<!-- <fieldset> -->
		<div class="fltrt">
			<button type="button" onclick="Joomla.submitform('persons.assign', this.form)">
				<?php echo JText::_('JSAVE');?></button>
			<button id="cancel" type="button" onclick="Joomla.submitform('persons.close', this.form)">
				<?php echo JText::_('JCANCEL');?></button>
		</div>
<!--	</fieldset> -->
    
	<table class="table">
		<tr>
			<td align="left" width="100%">
				<?php
				echo JText::_('JSEARCH_FILTER_LABEL');
				?>&nbsp;<input	type="text" name="filter_search" id="filter_search"
								value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
								class="text_area" onchange="$('adminForm').submit(); " />
				<button onclick="this.form.submit(); "><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
				<button onclick="document.getElementById('filter_search').value='';this.form.submit(); ">
					<?php
					echo JText::_('JSEARCH_FILTER_CLEAR');
					?>
				</button>
			</td>
            
            <td class="nowrap" align="right"><?php echo $this->lists['agegroup2']; ?></td>
            
			<td align="center" colspan="4">
				<?php
				$startRange = JComponentHelper::getParams(JFactory::getApplication()->input->getCmd('option'))->get('character_filter_start_hex', '0');
		$endRange = JComponentHelper::getParams(JFactory::getApplication()->input->getCmd('option'))->get('character_filter_end_hex', '0');
		for ($i=$startRange; $i <= $endRange; $i++)
		{
            printf("<a href=\"javascript:searchPerson('%s')\">%s</a>&nbsp;&nbsp;&nbsp;&nbsp;",'&#'.$i.';','&#'.$i.';');
			}
				?>
			</td>
		</tr>
	</table>
	<div id="editcell">
		<table class="<?php echo $this->table_data_class; ?>">
			<thead>
				<tr>
					<th width="5"><?php echo JText::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM'); ?></th>
					<th width="20">
						<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
					</th>
					<th width="20">&nbsp;</th>
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
			<tfoot><tr><td colspan='12'><?php echo $this->pagination->getListFooter(); ?></td></tr></tfoot>
			<tbody>
				<?php
				$k=0;
				for ($i=0,$n=count($this->items); $i < $n; $i++)
				{
					$row=&$this->items[$i];
					if (($row->firstname != '!Unknown') && ($row->lastname != '!Player')) // Ghostplayer for match-events
					{
						$link       = JRoute::_('index.php?option=com_sportsmanagement&task=person.edit&id='.$row->id);
						//$checked    = JHtml::_('grid.checkedout',$row,$i);
                        
                        $canEdit	= $this->user->authorise('core.edit','com_sportsmanagement');
                        $canCheckin	= $user->authorise('core.manage','com_checkin') || $row->checked_out == $userId || $row->checked_out == 0;
                        $checked = JHtml::_('jgrid.checkedout', $i, $this->user->get ('id'), $row->checked_out_time, 'persons.', $canCheckin);
                        
						$is_checked = $this->table->isCheckedOut($this->user->get('id'),$row->checked_out);
                        $published  = JHtml::_('grid.published',$row,$i, 'tick.png','publish_x.png','persons.');
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
							if ($is_checked)
							{
								$inputappend=' disabled="disabled" ';
							}
                            else
                            {
                                $inputappend='';
                            }	
                                
                                
                                ?>
                                <td class="center">
                                <?php if ($row->checked_out) : ?>
						<?php 
                        //echo JHtml::_('jgrid.checkedout', $i, $row->editor, $row->checked_out_time, 'persons.', $canCheckin); 
                        ?>
					<?php endif; ?>	
								
								</td>

							<td class="center">
								<input	<?php echo $inputappend; ?> type="text" size="15"
										class="inputbox" name="firstname<?php echo $row->id; ?>"
										value="<?php echo stripslashes(htmlspecialchars($row->firstname)); ?>"
										onchange="document.getElementById('cb<?php echo $i; ?>').checked=true" />
							</td>
							<td class="center">
								<input	<?php echo $inputappend; ?> type="text" size="15"
										class="inputbox" name="nickname<?php echo $row->id; ?>"
										value="<?php echo stripslashes(htmlspecialchars($row->nickname)); ?>"
										onchange="document.getElementById('cb<?php echo $i; ?>').checked=true" />
							</td>
							<td class="center">
								<input	<?php echo $inputappend; ?> type="text" size="15"
										class="inputbox" name="lastname<?php echo $row->id; ?>"
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
								if ($row->birthday == '0000-00-00')
								{
									$append=' style="background-color:#FFCCCC;"';
								}
								if ($is_checked)
								{
									echo $row->birthday;
								}
								else
								{
									echo $this->calendar(	sportsmanagementHelper::convertDate($row->birthday),
															'birthday'.$row->id,
															'birthday'.$row->id,
															'%d-%m-%Y',
															'size="10" '.$append.' cb="cb'.$i.'"',
															'onupdatebirthday',
															$i);
								}
								?>
							</td>
							<td class="nowrap" class="center">
								<?php
								$append='';
								if (empty($row->country)){$append=' background-color:#FFCCCC;';}
								echo JHtmlSelect::genericlist(	$this->lists['nation'],
																'country'.$row->id,
																$inputappend.' class="inputbox" style="width:140px; '.$append.'" onchange="document.getElementById(\'cb'.$i.'\').checked=true"',
																'value',
																'text',
																$row->country);
								?>
							</td>
							<td class="nowrap" class="center">
								<?php
								$append='';
								if (empty($row->position_id)){$append=' background-color:#FFCCCC;';}
								echo JHtmlSelect::genericlist(	$this->lists['positions'],
																'position'.$row->id,
																$inputappend.'class="inputbox" style="width:140px; '.$append.'" onchange="document.getElementById(\'cb'.$i.'\').checked=true"',
																'value',
																'text',
																$row->position_id);
								?>
							</td>
							<td class="center"><?php echo $published; ?></td>
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
	<input type="hidden" name="search_mode" value="<?php echo $this->lists['search_mode'];?>" id="search_mode" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->sortColumn; ?>" />
	<input type="hidden" name="filter_order_Dir" value="" />
    <input type="hidden" name="type" value="<?php echo $this->type; ?>" />
    <input type="hidden" name="persontype" value="<?php echo $this->persontype; ?>" />
    <input type="hidden" name="component" value="com_sportsmanagement" />
	<?php echo JHtml::_('form.token')."\n"; ?>
</form>
