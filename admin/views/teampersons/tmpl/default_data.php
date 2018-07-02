<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_data.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage teampersons
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
JHtml::_('behavior.modal');

?>
        
		<div class="table-responsive">
			<table class="<?php echo $this->table_data_class; ?>">
				<thead>
					<tr>
						<th width="">
							<?php
							echo JText::_( 'COM_SPORTSMANAGEMENT_GLOBAL_NUM' );
							?>
						</th>
						<th width="">
							<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
						</th>
						<th width="">
							&nbsp;
						</th>
						<th>
							<?php
							echo JHtml::_( 'grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_NAME', 'ppl.lastname', $this->sortDirection, $this->sortColumn );
							?>
						</th>
						<th>
						<?php
						echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_PERSONS_NATIONALITY','pl.country',$this->sortDirection,$this->sortColumn);
						?>
</th>
						<th>
							<?php
							echo JHtml::_( 'grid.sort', 'PID', 'tp.person_id', $this->sortDirection, $this->sortColumn );
							?>
						</th>
						<th>
							<?php
                            echo JHtml::_( 'grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_IMAGE', 'ppl.picture', $this->sortDirection, $this->sortColumn );
							?>
						</th>
                        <?PHP
                        if ( $this->_persontype == 1 )
		                {
                        ?>
                        <th width="">
							<?php
                            echo JHtml::_( 'grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_MARKET_VALUE', 'tp.market_value', $this->sortDirection, $this->sortColumn );
							?>
						</th>
						<th width="">
							<?php
                            echo JHtml::_( 'grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_SHIRTNR', 'tp.jerseynumber', $this->sortDirection, $this->sortColumn );
							?>
						</th>
                        <?PHP
                        }
                        ?>
						<th width="">
							<?php
							echo JHtml::_( 'grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_POS', 'ppl.position_id', $this->sortDirection, $this->sortColumn );
							?>
						</th>
						<th>
							<?php
							echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_STATUS' );
							?>
						</th>
						<th>
						<?php
						echo JHtml::_('grid.sort','JSTATUS','ppl.published',$this->sortDirection,$this->sortColumn);
						?></th>
						<th width="">
							<?php
							//echo JHtml::_( 'grid.sort', 'COM_SPORTSMANAGEMENT_GLOBAL_ORDER', 'ppl.ordering', $this->sortDirection, $this->sortColumn );
                            echo JHtml::_( 'grid.sort', 'JGRID_HEADING_ORDERING', 'ppl.ordering', $this->sortDirection, $this->sortColumn );
							echo JHtml::_( 'grid.order', $this->items, 'filesave.png', 'teampersons.saveorder' );
							?>
						</th>
						<th width="">
							<?php
							echo JHtml::_( 'grid.sort', 'JGRID_HEADING_ID', 'ppl.id', $this->sortDirection, $this->sortColumn );
							?>
						</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<td colspan="9">
							<?php
							echo $this->pagination->getListFooter();
							?>
						</td>
                        <td colspan="4">
            <?php echo $this->pagination->getResultsCounter(); ?>
            </td>
					</tr>
				</tfoot>
				<tbody>
					<?php

					$k = 0;
					for ( $i = 0, $n = count( $this->items ); $i < $n; $i++ )
					{
						$row =& $this->items[$i];
						$link			= JRoute::_('index.php?option=com_sportsmanagement&task=teamperson.edit&project_team_id=' .
													$row->projectteam_id . '&person_id=' . $row->id. '&id=' . $row->tpid. '&pid=' .$this->project->id.'&team_id='.$this->team_id.'&persontype='.$this->_persontype);
						$canEdit	= $this->user->authorise('core.edit','com_sportsmanagement');
                        $canCheckin = $this->user->authorise('core.manage','com_checkin') || $row->checked_out == $this->user->get ('id') || $row->checked_out == 0;
						$checked = JHtml::_('jgrid.checkedout', $i, $this->user->get ('id'), $row->checked_out_time, 'teampersons.', $canCheckin);
						$inputappend	= '';
                    $canChange  = $this->user->authorise('core.edit.state', 'com_sportsmanagement.teamperson.' . $row->id) && $canCheckin;						
						?>
						<tr class="<?php echo "row$k"; ?>">
							<td class="center">
								<?php
								echo $this->pagination->getRowOffset( $i );
								?>
							</td>
							<td class="center">
								<?php
								echo JHtml::_('grid.id', $i, $row->id);
                                //echo JHtml::_('grid.id', $i, $row->tpid);
								?>
							</td>
							
                            	<td>
<?php if ($row->checked_out) : ?>
						<?php echo JHtml::_('jgrid.checkedout', $i, $row->editor, $row->checked_out_time, 'teampersons.', $canCheckin); ?>
					<?php endif; ?>
                    
                    <?php if ($canEdit && !$row->checked_out ) : ?>
						
						
									<a href="<?php echo $link; ?>">
										<?php
										$imageTitle = JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_EDIT_DETAILS' );
										echo JHtml::_(	'image', 'administrator/components/com_sportsmanagement/assets/images/edit.png',
														$imageTitle,
														'title= "' . $imageTitle . '"' );
										?>
									</a>
										

									
					<?php else : ?>
							<?php //echo $this->escape($row->name); ?>
					<?php endif; ?> 
								</td>
                            
                            
                            
                            
                            
                            <?php
                                                      
                            
							
							?>
							<td>
								<?php echo sportsmanagementHelper::formatName(null, $row->firstname, $row->nickname, $row->lastname, 0) ?>
							</td>
							
<td class="nowrap" class="center">
<?php
$append = '';
$inputappend = '';
if (empty($row->country))
{
$append=' background-color:#FFCCCC;';
}
echo JHtmlSelect::genericlist($this->lists['nation'],
		'country'.$row->person_id,
		$inputappend.' class="form-control form-control-inline" style="width:140px; '.$append.'" onchange="document.getElementById(\'cb'.$i.'\').checked=true"',
		'value',
		'text',
		$row->country);
		?>
</td>
							
							
							<td class="center">
								<?php
								echo $row->person_id;
								?>
							</td>
							<td class="center">
								<?php
								if ( $row->season_picture == '' )
								{
									$imageTitle = JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_NO_IMAGE' );
									echo JHtml::_('image','administrator/components/com_sportsmanagement/assets/images/delete.png',
										$imageTitle,
										'title= "' . $imageTitle . '"' );

								}
								elseif ( $row->season_picture == sportsmanagementHelper::getDefaultPlaceholder("player") )
								{
									$imageTitle = JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_DEFAULT_IMAGE' );
									echo JHtml::_('image','administrator/components/com_sportsmanagement/assets/images/information.png',
										$imageTitle,
										'title= "' . $imageTitle . '"' );
?>
<a href="<?php echo JURI::root().$row->season_picture;?>" title="<?php echo $imageTitle;?>" class="modal">
<img src="<?php echo JURI::root().$row->season_picture;?>" alt="<?php echo $imageTitle;?>" width="20" height="30"  />
</a>
<?PHP   								
                                }
								elseif ( $row->season_picture == !'')
								{
									$playerName = sportsmanagementHelper::formatName(null ,$row->firstname, $row->nickname, $row->lastname, 0);
?>
<a href="<?php echo JURI::root().$row->season_picture;?>" title="<?php echo $playerName;?>" class="modal">
<img src="<?php echo JURI::root().$row->season_picture;?>" alt="<?php echo $playerName;?>" width="20" height="30"  />
</a>
<?PHP 								
                                
                                
                                }
								?>
							</td>
                            <?PHP
                            if ( $this->_persontype == 1 )
		                      {
                            ?>
              <td class="center">
								<input<?php echo $inputappend; ?>	type="text" size="4" class="form-control form-control-inline"
																	name="market_value<?php echo $row->id; ?>"
																	value="<?php echo $row->market_value; ?>"
																	onchange="document.getElementById('cb<?php echo $i; ?>').checked=true" />
							</td>
							<td class="center">
								<input<?php echo $inputappend; ?>	type="text" size="4" class="form-control form-control-inline"
																	name="jerseynumber<?php echo $row->id; ?>"
																	value="<?php echo $row->jerseynumber; ?>"
																	onchange="document.getElementById('cb<?php echo $i; ?>').checked=true" />
							</td>
                            <?PHP
                            }
                            ?>
							<td class="nowrap" class="center">
								<?php
								if ( $row->project_position_id != 0 )
								{
									$selectedvalue = $row->project_position_id;
									$append = '';
								}
								else
								{
									$selectedvalue = 0;
									$append = ' style="background-color:#FFCCCC"';
								}
								if ( $append != '' )
								{
									?>
									<script language="javascript">document.getElementById('cb<?php echo $i; ?>').checked=true;</script>
									<?php
								}
								if ( $row->project_position_id == 0 )
								{
									$append=' style="background-color:#FFCCCC"';
								
// einen vorschlag generieren
$mdlPerson = JModelLegacy::getInstance("Person", "sportsmanagementModel");
$project_person = $mdlPerson->getPerson($row->person_id);
$position_id = $project_person->position_id;
//echo '<pre>'.print_r($position_id,true).'</pre>';

//build the html options for position
$position_ids = array();        
$mdlPositions = JModelLegacy::getInstance('Positions', 'sportsmanagementModel');
$project_ref_positions = $mdlPositions->getProjectPositions($this->project_id, $this->_persontype);
if ($project_ref_positions) {
            $position_ids = array_merge($position_ids, $project_ref_positions);
        }
foreach($position_ids as $items => $item) {
    if($item->position_id == $position_id) {
        $selectedvalue = $item->value;
    }
}
								
								}
								echo JHtml::_( 'select.genericlist', $this->lists['project_position_id'], 'project_position_id' . $row->id, $inputappend . 'class="form-control form-control-inline" size="1" onchange="document.getElementById(\'cb' . $i . '\').checked=true"' . $append, 'value', 'text', $selectedvalue );

                                ?>
                                <input type="hidden" name="position_id<?php echo $row->id; ?>"	value="<?php echo $row->position_id; ?>" />
                                <input type="hidden" name="person_id<?php echo $row->id; ?>"	value="<?php echo $row->tpid; ?>" />
                                <input type="hidden" name="tpid[<?php echo $row->id; ?>]"	value="<?php echo $row->tpid; ?>" />
                                
							</td>
							<td class="nowrap" class="center">
								<?php
								//$row->injury = 1;
								//$row->suspension = 1;
								//$row->away = 1;
								if ( $row->injury > 0 )
								{
									$imageTitle = JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_INJURED' );
									echo JHtml::_('image','administrator/components/com_sportsmanagement/assets/images/injured.gif',
										$imageTitle,
										'title= "' . $imageTitle . '"' );
								}
								if ( $row->suspension > 0 )
								{
									$imageTitle = JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_SUSPENDED' );
									echo JHtml::_('image','administrator/components/com_sportsmanagement/assets/images/suspension.gif',
										$imageTitle,
										'title= "' . $imageTitle . '"' );
								}
								if ( $row->away > 0 )
								{
									$imageTitle = JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_AWAY' );
									echo JHtml::_('image','administrator/components/com_sportsmanagement/assets/images/away.gif',
										$imageTitle,
										'title= "' . $imageTitle . '"' );
								}
						if ( !$row->injury &&
						   !$row->suspension &&
						   !$row->away )
						{
						$imageTitle = JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS' );
						echo JHtml::_('image','administrator/components/com_sportsmanagement/assets/images/players.png',
							$imageTitle,
							'title= "' . $imageTitle . '"' );	
						}
								?>
								&nbsp;
							</td>
							<td class="center">
<div class="btn-group">
            <?php echo JHtml::_('jgrid.published', $row->published, $i, 'teampersons.', $canChange, 'cb'); ?>
            <?php // Create dropdown items and render the dropdown list.
								if ($canChange)
								{
									JHtml::_('actionsdropdown.' . ((int) $row->published === 2 ? 'un' : '') . 'archive', 'cb' . $i, 'teampersons');
									JHtml::_('actionsdropdown.' . ((int) $row->published === -2 ? 'un' : '') . 'trash', 'cb' . $i, 'teampersons');
									echo JHtml::_('actionsdropdown.render', $this->escape($row->name));
								}
								?>
            </div>								
							</td>
							<td class="order">
								<span>
									<?php
									echo $this->pagination->orderUpIcon( $i, $i > 0, 'teampersons.orderup', 'JLIB_HTML_MOVE_UP', true );
									?>
								</span>
								<span>
									<?php
									echo $this->pagination->orderDownIcon( $i, $n, $i < $n, 'teampersons.orderdown', 'JLIB_HTML_MOVE_DOWN', true );
									?>
								</span>
								<?php
								$disabled = true ?	'' : 'disabled="disabled"';
								?>
								<input	type="text" name="order[]" size="5" value="<?php echo $row->ordering; ?>" <?php echo $disabled; ?>
										onchange="document.getElementById('cb<?php echo $i; ?>').checked=true"
                                        class="form-control form-control-inline" style="text-align: center; " />
							</td>
							<td class="center">
								<?php
								echo $row->tpid;
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
	
