<?php 
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      default_data.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage projectreferees
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
				<tfoot>
                <tr>
                <td colspan='8'>
                <?php echo $this->pagination->getListFooter(); ?>
                </td>
                <td colspan="4">
            <?php echo $this->pagination->getResultsCounter(); ?>
            </td>
                </tr>
                </tfoot>
				<tbody>
					<?php
					$k=0;
					for ($i=0,$n=count($this->items); $i < $n; $i++)
					{
						$row =& $this->items[$i];
						$link = JRoute::_('index.php?option=com_sportsmanagement&task=projectreferee.edit&id='.$row->id.'&pid='.$row->project_id.'&person_id='.$row->person_id  );
						//$checked = JHtml::_('grid.checkedout',$row,$i);
                        $canEdit	= $this->user->authorise('core.edit','com_sportsmanagement');
                        $canCheckin = $this->user->authorise('core.manage','com_checkin') || $row->checked_out == $this->user->get ('id') || $row->checked_out == 0;
                        $checked = JHtml::_('jgrid.checkedout', $i, $this->user->get ('id'), $row->checked_out_time, 'projectreferees.', $canCheckin);
                        
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
								echo JHtml::_('grid.id', $i, $row->id);
								?>
							</td>
							
								<td class="center">
                                <?php
                            if ($row->checked_out) : ?>
										<?php echo JHtml::_('jgrid.checkedout', $i, $this->user->get ('id'), $row->checked_out_time, 'projectreferees.', $canCheckin); ?>
									<?php 
                                    endif; 
                                    if ($canEdit && !$row->checked_out ) :
                                    ?>	
									<a href="<?php echo $link; ?>">
										<?php
										$imageTitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_PREF_EDIT_DETAILS');
										echo JHtml::_(	'image','administrator/components/com_sportsmanagement/assets/images/edit.png',
														$imageTitle,
														'title= "'.$imageTitle.'"');
										?>
									</a>
                                    <?php 
                                    endif; 
                                    ?>
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
								else
								{
									$playerName = sportsmanagementHelper::formatName(null ,$row->firstname, $row->nickname, $row->lastname, 0);
									$picture = JURI::root().$row->picture;
									//echo sportsmanagementHelper::getPictureThumb($picture, $playerName, 0, 21, 4);
echo sportsmanagementHelper::getBootstrapModalImage('collapseModalplayerpicture'.$row->id,$picture,$playerName,'20',$picture);                                                   									
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
										value="<?php echo $row->ordering; ?>" <?php echo $disabled; ?> class="form-control form-control-inline"
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
