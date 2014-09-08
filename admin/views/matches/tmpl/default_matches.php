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
// welche joomla version
if(version_compare(JVERSION,'3.0.0','ge')) 
{
JHtml::_('behavior.framework', true);
}
else
{
JHtml::_( 'behavior.mootools' );    
}
$modalheight = JComponentHelper::getParams($this->option)->get('modal_popup_height', 600);
$modalwidth = JComponentHelper::getParams($this->option)->get('modal_popup_width', 900);
?>
<style>
fieldset input, 
fieldset textarea, 
fieldset select, 
fieldset img, 
fieldset button {
    float: none;
    margin: 5px 5px 5px 0;
    width: auto;
}
</style>
<div id="editcell">
<!--	<fieldset class="adminform"> -->
		<legend><?php echo JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_TITLE2','<i>'.$this->roundws->name.'</i>','<i>'.$this->projectws->name.'</i>'); ?></legend>
		<?php echo $this->loadTemplate('roundselect'); ?>
        	 
		<!-- Start games list -->
		<form action="<?php echo $this->request_url; ?>" method="post" id='adminForm' name='adminForm'>
			<?php
			$colspan = ($this->projectws->allow_add_time) ? 19 : 18;
			?>
			<table class="<?php echo $this->table_data_class; ?>">
				<thead>
					<tr>
						<th width="5" ><?php echo count($this->matches).'/'.$this->pagination->total; ?></th>
						<th width="20" >
							<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
						</th>
						<th width="20" >&nbsp;</th>
            <?php
            if ( JComponentHelper::getParams($this->option)->get('cfg_be_extension_single_match',0) )
            {
            ?>
            <th width="20" ><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_SINGLE_MATCH'); ?></th>
            <?php
            }
            ?>
						<th width="20" >
							<?php echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MATCHNR','mc.match_number',$this->sortDirection,$this->sortColumn); ?>
						</th>
						<th class="title" >
							<?php echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_MATCHES_DATE','mc.match_date',$this->sortDirection,$this->sortColumn); ?>
						</th>
						<th class="title" ><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_TIME'); ?></th>
						<th class="title" ><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_MD_ATT' ); ?></th>
						<?php 
							if($this->projectws->project_type=='DIVISIONS_LEAGUE') {
								$colspan++;
						?>
						<th >
							<?php 
								echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_MATCHES_DIVISION','divhome.id',$this->sortDirection,$this->sortColumn);
								echo '<br>'.JHtml::_(	'select.genericlist',
													$this->lists['divisions'],
													'filter_division',
													'class="inputbox" size="1" onchange="window.location.href=window.location.href.split(\'&division=\')[0]+\'&division=\'+this.value"',
													'value','text', $this->state->get('filter.division'));
								
							?>
						</th>
						<?php 
							}
						?>
						<th class="title" nowrap="nowrap" ><?php echo JTEXT::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_CHANGE_ROUNDLIST'); ?></th>
						<th class="title" ><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_HOME_TEAM'); ?></th>
						<th class="title" ><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_AWAY_TEAM'); ?></th>
						<th style="  "><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_RESULT'); ?></th>
						<?php
						if ($this->projectws->allow_add_time)
						{
							?>
							<th style="text-align:center;  "><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_RESULT_TYPE'); ?></th>
							<?php
						}
						?>
                        <th class="title" ><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_RESULT_TYPE'); ?></th>
                        
                        <th class="title" ><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_RESULT_ARTICLE'); ?></th>
                        
						<th class="title" ><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_EVENTS'); ?></th>
						<th class="title" ><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_STATISTICS'); ?></th>
						<th class="title" ><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_REFEREE'); ?></th>
						<th width="1%" ><?php echo JText::_('JSTATUS'); ?></th>
						<th width="1%" class="title" >
							<?php echo JHtml::_('grid.sort','JGRID_HEADING_ID','mc.id',$this->sortDirection,$this->sortColumn); ?>
						</th>
					</tr>
				</thead>
                
				<tfoot>
                <tr>
                <td colspan="<?php echo $colspan - 3; ?>"><?php echo $this->pagination->getListFooter(); ?></td>
                <td colspan="3"><?php echo $this->pagination->getResultsCounter();?></td>
                </tr>
                </tfoot>
				
                
                <tbody>
					<?php
					$k=0;
					for ($i=0,$n=count($this->matches); $i < $n; $i++)
					{
						$row		=& $this->matches[$i];
						$checked	= JHtml::_('grid.checkedout',$row,$i,'id');
						$published	= JHtml::_('grid.published',$row,$i,'tick.png','publish_x.png','matches.');

						list($date,$time) = explode(" ",$row->match_date);
						$time = strftime("%H:%M",strtotime($time));
						?>
						<tr class="<?php echo "row$k"; ?>">
						<?php if(($row->cancel)>0)
								{
									$style="text-align:center;  background-color: #FF9999;";
								}
								else
								{
									$style="text-align:center; ";
								}
								?>
							<td style="<?php echo $style;?>">
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
								<a	rel="{handler: 'iframe',size: {x: <?php echo $modalwidth; ?>,y: <?php echo $modalheight; ?>}}"
									href="index.php?option=com_sportsmanagement&tmpl=component&view=match&layout=edit&id=<?php echo $row->id; ?>"
									 class="modal">
									<?php
									echo JHtml::_(	'image','administrator/components/com_sportsmanagement/assets/images/edit.png',
													JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_EDIT_DETAILS'),'title= "' .
													JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_EDIT_DETAILS').'"');
									?>
								</a>
                                <?PHP
                                //$pcture_link = 'index.php?option=com_sportsmanagement&tmpl=component&view=match&layout=picture&id='.$row->id;
                                $pcture_link = 'index.php?option=com_media&view=images&tmpl=component&asset=com_sportsmanagement&author=&folder=com_sportsmanagement/database/matchreport/'.$row->id;
                                //$pcture_link = 'index.php?option=com_media&view=images&tmpl=component&asset=com_sportsmanagement&author=&folder=com_sportsmanagement/database/matchreport/';
                                ?>
								<a	rel="{handler: 'iframe',size: {x: <?php echo $modalwidth; ?>,y: <?php echo $modalheight; ?>}}"
									href="<?php echo $pcture_link; ?>"
									 class="modal">
									<?php
									echo JHtml::_(	'image','administrator/components/com_sportsmanagement/assets/images/link.png',
													JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_EDIT_MATCHPICTURE'),'title= "' .
													JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_EDIT_MATCHPICTURE').'"');
									?>
								</a>
                                
              <?php
							// diddipoeler einzelsportart
            
            //if ( JComponentHelper::getParams('com_sportsmanagement')->get('cfg_be_extension_single_match',0) )
            if ( $this->projectws->project_art_id == 2 )
            {
            
							?>
              
							<a	rel="{handler: 'iframe',size: {x: <?php echo $modalwidth; ?>,y: <?php echo $modalheight; ?>}}"
									href="index.php?option=com_sportsmanagement&view=jlextindividualsportes&tmpl=component&id=<?php echo $row->id; ?>&team1=<?php echo $row->projectteam1_id; ?>&team2=<?php echo $row->projectteam2_id; ?>&rid=<?php echo $row->round_id; ?>  "
									 class="modal"
									 title="<?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_EDIT_SINGLE_SPORT'); ?>">
									 <?php
									 
								 	$image = 'players_add.png';
								 	$title=  '';
								 echo JHtml::_(	'image','administrator/components/com_sportsmanagement/assets/images/'.$image,
													 JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_EDIT_SINGLE_SPORT'),
													 'title= "' .$title. '"');
													 
										
									 									 ?>
								</a>
							
              <?php
            }
            ?>                  
                                
                                
							</td>
              
							<td class="center">
								<input onchange="document.getElementById('cb<?php echo $i; ?>').checked=true" type="text" name="match_number<?php echo $row->id; ?>"
										value="<?php echo $row->match_number; ?>" size="6" tabindex="1" class="inputbox" />
							</td>
							<td class="center" nowrap="nowrap">
								<?php
								echo JHtml::calendar(	sportsmanagementHelper::convertDate($date),
														'match_date'.$row->id,
														'match_date'.$row->id,
														'%d-%m-%Y',
														'size="9"  tabindex="2" ondblclick="copyValue(\'match_date\')" onchange="document.getElementById(\'cb'.$i.'\').checked=true"');
								?>
							</td>
							<td class="left"  nowrap="nowrap">

								<input ondblclick="copyValue('match_time')" onchange="document.getElementById('cb<?php echo $i; ?>').checked=true" type="text" name="match_time<?php echo $row->id; ?>"
										value="<?php echo $time; ?>" size="4" maxlength="5" tabindex="3" class="inputbox" />

								<a	href="javascript:void(0)"
									onclick="switchMenu('present<?php echo $row->id; ?>')">&nbsp;
									<?php echo JHtml::_(	'image','administrator/components/com_sportsmanagement/assets/images/arrow_open.png',
															JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_PRESENT'),
															'title= "'.JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_PRESENT').'"');
									?>
								</a><br />
								<span id="present<?php echo $row->id; ?>" style="display: none">
									<br />
										<input ondblclick="copyValue('time_present')" onchange="document.getElementById('cb<?php echo $i; ?>').checked=true" type="text" name="time_present<?php echo $row->id; ?>"
												value="<?php echo $row->time_present; ?>" size="4" maxlength="5" tabindex="3" class="inputbox" title="<?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_PRESENT'); ?>" />		
										<?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_PRESENT_SHORT'); ?>			
								</span>
							</td>
							<td class="center">
								<input onchange="document.getElementById('cb<?php echo $i; ?>').checked=true" type="text" name="crowd<?php echo $row->id; ?>"
										value="<?php echo $row->crowd; ?>" size="4" maxlength="5" tabindex="4" class="inputbox" />
							</td>
							<?php 
								if($this->projectws->project_type=='DIVISIONS_LEAGUE') 
                {
                $append='';
							  $append.=' onchange="document.getElementById(\'cb'.$i.'\').checked=true" ';
							?>
							<td class="center">
								<?php
							echo JHtml::_(	'select.genericlist',$this->lists['divisions'],'division_id'.$row->id,
												'class="inputbox select-division_id" size="1"'.$append,'value','text',$row->division_id);
							?>
                <?php 
                //echo $row->divhome; 
                ?>
							</td>
							<?php 
								} 
							?>
							 
              <?php
              $append='';
							$append.=' onchange="document.getElementById(\'cb'.$i.'\').checked=true" ';
              ?>
              <td style="text-align:center; ">
								<?php
							echo JHtml::_(	'select.genericlist',$this->lists['project_change_rounds'],'round_id'.$row->id,
												'class="inputbox select-round_id" size="1"'.$append,'value','text',$row->round_id);
							?>
							</td>
              
							<td class="right"  nowrap="nowrap">
								<a	onclick="handleRosterIconClick(<?php echo $this->prefill; ?>, this, '<?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_PREFILL_LAST_ROSTER_ALERT'); ?>', '<?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_PREFILL_PROJECTTEAM_PLAYERS_ALERT')?>')"
									rel="{handler: 'iframe',size: {x: <?php echo $modalwidth; ?>,y: <?php echo $modalheight; ?>}}"
									href="index.php?option=com_sportsmanagement&tmpl=component&view=match&layout=editlineup&match_date=<?php echo $date; ?>&id=<?php echo $row->id; ?>&team=<?php echo $row->projectteam1_id; ?>&prefill="
									 class="modal openroster-team1<?php echo $row->id; ?>"
									 title="<?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_EDIT_LINEUP_HOME'); ?>">
									 <?php
									 if($row->homeplayers_count==0 || $row->homestaff_count==0 ) {
									 	$image = 'players_add.png';
									 } else {
									 	$image = 'players_edit.png';
									 }
									 $title=  ' '.JText::_('COM_SPORTSMANAGEMENT_F_PLAYERS').': ' .$row->homeplayers_count. ', ' . 
													 ' '.JText::_('COM_SPORTSMANAGEMENT_F_TEAM_STAFF').': ' .$row->homestaff_count . ' ';
									
									echo '<sub>'.$row->homeplayers_count.'</sub> ';
												 

									 echo JHtml::_(	'image','administrator/components/com_sportsmanagement/assets/images/'.$image,
													 JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_EDIT_LINEUP_HOME'),
													 'title= "' .$title. '"');
													 
									 echo '<sub>'.$row->homestaff_count.'</sub> ';	
									 									 ?>
								</a>
								<?php
								$append='';
								if ($row->projectteam1_id == 0)
								{
									$append=' style="background-color:#bbffff"';
								}
								$append.=' onchange="document.getElementById(\'cb'.$i.'\').checked=true" ';
								echo JHtml::_(	'select.genericlist',$this->lists['teams_'+$row->divhomeid],'projectteam1_id'.$row->id,
												'class="inputbox select-hometeam" size="1"'.$append,'value','text',$row->projectteam1_id);
								?>
							</td>
							<td class="left"  nowrap="nowrap">
								<?php
								$append='';
								if ($row->projectteam2_id == 0)
								{
									$append=' style="background-color:#bbffff"';
								}
								$append.=' onchange="document.getElementById(\'cb'.$i.'\').checked=true" ';
								echo JHtml::_(	'select.genericlist',$this->lists['teams_'+$row->divhomeid],'projectteam2_id'.$row->id,
												'class="inputbox select-awayteam" size="1"'.$append,'value','text',$row->projectteam2_id);
								?>
								<a	onclick="handleRosterIconClick(<?php echo $this->prefill; ?>, this, '<?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_PREFILL_LAST_ROSTER_ALERT'); ?>', '<?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_PREFILL_PROJECTTEAM_PLAYERS_ALERT')?>')"
									rel="{handler: 'iframe',size: {x: <?php echo $modalwidth; ?>,y: <?php echo $modalheight; ?>}}"
									href="index.php?option=com_sportsmanagement&tmpl=component&view=match&layout=editlineup&id=<?php echo $row->id;?>&team=<?php echo $row->projectteam2_id;?>&prefill="
									class="modal open-starting-away"
									title="<?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_EDIT_LINEUP_AWAY'); ?>">
									 <?php
									 if($row->awayplayers_count==0 || $row->awaystaff_count==0 ) {
									 	$image = 'players_add.png';
									 } else {
									 	$image = 'players_edit.png';
									 }
									 $title=' '.JText::_('COM_SPORTSMANAGEMENT_F_PLAYERS').': ' .$row->awayplayers_count. ', ' . 
													 ' '.JText::_('COM_SPORTSMANAGEMENT_F_TEAM_STAFF').': ' .$row->awaystaff_count;
													 
									 echo '<sub>'.$row->awayplayers_count.'</sub> ';
									 echo JHtml::_(	'image','administrator/components/com_sportsmanagement/assets/images/'.$image,
													 JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_EDIT_LINEUP_AWAY'),
													 'title= "' .$title. '"');
									 echo '<sub>'.$row->awaystaff_count.'</sub> ';	
								
									  ?>
								</a>
							</td>
							<td class="left"  nowrap="nowrap">
								<input onchange="document.getElementById('cb<?php echo $i; ?>').checked=true" <?php if($row->alt_decision==1) echo "class=\"subsequentdecision\" title=\"".JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_SUB_DECISION')."\"" ?> type="text" name="team1_result<?php echo $row->id; ?>"
										value="<?php echo $row->team1_result; ?>" size="2" tabindex="5" class="inputbox" /> : 
								<input onchange="document.getElementById('cb<?php echo $i; ?>').checked=true" <?php if($row->alt_decision==1) echo "class=\"subsequentdecision\" title=\"".JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_SUB_DECISION')."\"" ?> type="text" name="team2_result<?php echo $row->id; ?>"
										value="<?php echo $row->team2_result; ?>" size="2" tabindex="5" class="inputbox" />
								
                                <?PHP
                                if ( $this->projectws->project_art_id == 2 )
                                {
                                ?>
                                <br />MP
                                <input onchange="document.getElementById('cb<?php echo $i; ?>').checked=true" <?php if($row->alt_decision==1) echo "class=\"subsequentdecision\" title=\"".JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_SUB_DECISION')."\"" ?> type="text" name="team1_single_matchpoint<?php echo $row->id; ?>"
										value="<?php echo $row->team1_single_matchpoint; ?>" size="2" tabindex="5" class="inputbox" /> : 
								<input onchange="document.getElementById('cb<?php echo $i; ?>').checked=true" <?php if($row->alt_decision==1) echo "class=\"subsequentdecision\" title=\"".JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_SUB_DECISION')."\"" ?> type="text" name="team2_single_matchpoint<?php echo $row->id; ?>"
										value="<?php echo $row->team2_single_matchpoint; ?>" size="2" tabindex="5" class="inputbox" />
                                
                                <br />MS
                                <input onchange="document.getElementById('cb<?php echo $i; ?>').checked=true" <?php if($row->alt_decision==1) echo "class=\"subsequentdecision\" title=\"".JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_SUB_DECISION')."\"" ?> type="text" name="team1_single_sets<?php echo $row->id; ?>"
										value="<?php echo $row->team1_single_sets; ?>" size="2" tabindex="5" class="inputbox" /> : 
								<input onchange="document.getElementById('cb<?php echo $i; ?>').checked=true" <?php if($row->alt_decision==1) echo "class=\"subsequentdecision\" title=\"".JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_SUB_DECISION')."\"" ?> type="text" name="team2_single_sets<?php echo $row->id; ?>"
										value="<?php echo $row->team2_single_sets; ?>" size="2" tabindex="5" class="inputbox" />
                                
                                <br />MG
                                <input onchange="document.getElementById('cb<?php echo $i; ?>').checked=true" <?php if($row->alt_decision==1) echo "class=\"subsequentdecision\" title=\"".JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_SUB_DECISION')."\"" ?> type="text" name="team1_single_games<?php echo $row->id; ?>"
										value="<?php echo $row->team1_single_games; ?>" size="2" tabindex="5" class="inputbox" /> : 
								<input onchange="document.getElementById('cb<?php echo $i; ?>').checked=true" <?php if($row->alt_decision==1) echo "class=\"subsequentdecision\" title=\"".JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_SUB_DECISION')."\"" ?> type="text" name="team2_single_games<?php echo $row->id; ?>"
										value="<?php echo $row->team2_single_games; ?>" size="2" tabindex="5" class="inputbox" />                
                                <?PHP
                                }
                                ?>
                                
                                <a	href="javascript:void(0)"
									onclick="switchMenu('part<?php echo $row->id; ?>')">&nbsp;
									<?php echo JHtml::_(	'image','administrator/components/com_sportsmanagement/assets/images/arrow_open.png',
															JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_PERIOD_SCORES'),
															'title= "'.JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_PERIOD_SCORES').'"');
									?>
								</a>
                                
                                <?PHP
                                if ( $row->alt_decision == 1 )
                                {
                                    echo JHtml::_(	'image','administrator/components/com_sportsmanagement/assets/images/user_edit.png',
															JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_AD_SUB_DEC'),
															'title= "'.JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_AD_SUB_DEC').'"');

                                }
                                ?>
                                
                                
                                <br />
								<span id="part<?php echo $row->id; ?>" style="display: none">
									<br />
									<?php
									
                                    if ( $this->projectws->use_legs )
                                    {
                                    $partresults1=explode(";",$row->team1_result_split);
									$partresults2=explode(";",$row->team2_result_split);
									for ($x=0; $x < ($this->projectws->game_parts); $x++)
									{
										 ?>

										<input	onchange="document.getElementById('cb<?php echo $i; ?>').checked=true" onchange="document.getElementById(\'cb'<?php echo $i; ?>'\').checked=true" type="text" style="font-size: 9px;"
												name="team1_result_split<?php echo $row->id;?>[]"
												value="<?php echo (isset($partresults1[$x])) ? $partresults1[$x] : ''; ?>"
												size="2" tabindex="6" class="inputbox" /> : 
										<input	onchange="document.getElementById('cb<?php echo $i; ?>').checked=true" onchange="document.getElementById(\'cb'<?php echo $i; ?>'\').checked=true" type="text" style="font-size: 9px;"
												name="team2_result_split<?php echo $row->id; ?>[]"
												value="<?php echo (isset($partresults2[$x])) ? $partresults2[$x] : ''; ?>"
												size="2" tabindex="6" class="inputbox" />
										<?php
										echo '&nbsp;&nbsp;'.($x+1).".<br />";
									}    
                                    }
                                    else
                                    {
                                    $partresults1=explode(";",$row->team1_result_split);
									$partresults2=explode(";",$row->team2_result_split);
									for ($x=0; $x < ($this->projectws->game_parts); $x++)
									{
										 ?>

										<input	onchange="document.getElementById('cb<?php echo $i; ?>').checked=true" onchange="document.getElementById(\'cb'<?php echo $i; ?>'\').checked=true" type="text" style="font-size: 9px;"
												name="team1_result_split<?php echo $row->id;?>[]"
												value="<?php echo (isset($partresults1[$x])) ? $partresults1[$x] : ''; ?>"
												size="2" tabindex="6" class="inputbox" /> : 
										<input	onchange="document.getElementById('cb<?php echo $i; ?>').checked=true" onchange="document.getElementById(\'cb'<?php echo $i; ?>'\').checked=true" type="text" style="font-size: 9px;"
												name="team2_result_split<?php echo $row->id; ?>[]"
												value="<?php echo (isset($partresults2[$x])) ? $partresults2[$x] : ''; ?>"
												size="2" tabindex="6" class="inputbox" />
										<?php
										echo '&nbsp;&nbsp;'.($x+1).".<br />";
									}
                                    }
                                    
                                    
									if ($this->projectws->allow_add_time == 1)
									{
										 ?>

										<input onchange="document.getElementById('cb<?php echo $i; ?>').checked=true" type="text" style="font-size: 9px;" name="team1_result_ot<?php echo $row->id;?>"
											value="<?php echo (isset($row->team1_result_ot)) ? $row->team1_result_ot : '';?>"
											size="2" tabindex="7" class="inputbox" /> : 
										<input onchange="document.getElementById('cb<?php echo $i; ?>').checked=true" type="text" style="font-size: 9px;" name="team2_result_ot<?php echo $row->id;?>"
											value="<?php echo (isset($row->team2_result_ot)) ? $row->team2_result_ot : '';?>"
											size="2" tabindex="7" class="inputbox" />
										<?php echo '&nbsp;&nbsp;OT:<br />';?>

										<input onchange="document.getElementById('cb<?php echo $i; ?>').checked=true" type="text" style="font-size: 9px;" name="team1_result_so<?php echo $row->id;?>"
											value="<?php echo (isset($row->team1_result_so)) ? $row->team1_result_so : '';?>"
											size="2" tabindex="8" class="inputbox" /> : 
										<input onchange="document.getElementById('cb<?php echo $i; ?>').checked=true" type="text" style="font-size: 9px;" name="team2_result_so<?php echo $row->id;?>"
											value="<?php echo (isset($row->team2_result_so)) ? $row->team2_result_so : '';?>"
											size="2" tabindex="8" class="inputbox" />
										<?php echo '&nbsp;&nbsp;SO:<br />';?>
										<?php
									}
									?>
								</span>
							</td>
							<?php
							if ($this->projectws->allow_add_time)
							{
								?>
								<td>
									<?php
                                    $appendselect =' onchange="document.getElementById(\'cb'.$i.'\').checked=true" ';
									echo JHtml::_(	'select.genericlist',$this->lists['match_result_type'],
													'match_result_type'.$row->id,'class="inputbox" size="1" '.$appendselect,'value','text',
													$row->match_result_type);
									?>
								</td>
								<?php
							}
							?>
                            
                            <td class="center">
                            	<?php
                                if ( $this->selectlist )
                                {
                                if (array_key_exists('result_type', $this->selectlist)) 
                                {
                                    $appendselect =' onchange="document.getElementById(\'cb'.$i.'\').checked=true" ';
                                    echo JHtml::_(	'select.genericlist',$this->selectlist['result_type'],
													'result_type'.$row->id,'class="inputbox" size="1" '.$appendselect,'value','text',
													$row->result_type);
    
                                }
                                else                               
                                {                           
								echo $row->result_type;
                                }
                                }
                                else                               
                                {                           
								echo $row->result_type;
                                }
								?>
                            
                            </td>
                            
                            <td class="center">
                            <?php
                                
                                    $appendselect =' onchange="document.getElementById(\'cb'.$i.'\').checked=true" ';
                                    echo JHtml::_(	'select.genericlist',$this->lists['articles'],
													'content_id'.$row->id,'class="inputbox" size="1" '.$appendselect,'value','text',
													$row->content_id);
    
                        

                                	?>
                            </td>
                            
							<td class="center">
                            
                            <a	rel="{handler: 'iframe',size: {x: <?php echo $modalwidth; ?>,y: <?php echo $modalheight; ?>}}"
									 href="index.php?option=com_sportsmanagement&tmpl=component&view=match&layout=pressebericht&id=<?php echo $row->id; ?>"
									 class="modal open-pressebericht"
									 title="<?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_EDIT_PRESSEBERICHT'); ?>">
									 <?php
									 echo JHtml::_(	'image','administrator/components/com_sportsmanagement/assets/images/link.png',
													 JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_EDIT_PRESSEBERICHT'),'title= "'.JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_EDIT_PRESSEBERICHT').'"');
									 ?>
								</a>
                                
								<a	rel="{handler: 'iframe',size: {x: <?php echo $modalwidth; ?>,y: <?php echo $modalheight; ?>}}"
									 href="index.php?option=com_sportsmanagement&tmpl=component&view=match&layout=editevents&id=<?php echo $row->id; ?>"
									 class="modal open-editevents"
									 title="<?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_EDIT_EVENTS'); ?>">
									 <?php
									 echo JHtml::_(	'image','administrator/components/com_sportsmanagement/assets/images/events.png',
													 JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_EDIT_EVENTS'),'title= "'.JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_EDIT_EVENTS').'"');
									 ?>
								</a>
								<?php
								//end statistics
								//start add several events in one operation:
								?>
								<a	rel="{handler: 'iframe',size: {x: <?php echo $modalwidth; ?>,y: <?php echo $modalheight; ?>}}"
									 href="index.php?option=com_sportsmanagement&tmpl=component&view=match&layout=editeventsbb&id=<?php echo $row->id; ?>"
                                     class="modal open-editeventsbb"
									 title="<?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_EDIT_SBBEVENTS'); ?>">
									 <?php
									 echo JHtml::_(	'image','administrator/components/com_sportsmanagement/assets/images/teams.png',
													 JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_EDIT_SBBEVENTS'),'title= "'.JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_EDIT_SBBEVENTS').'"');
								?>
								</a>
								<?php
								//end several events
								?>
							</td>
							<td class="center">
								<?php
								//start statistics:
								?>
								<a	rel="{handler: 'iframe',size: {x: <?php echo $modalwidth; ?>,y: <?php echo $modalheight; ?>}}"
									 href="index.php?option=com_sportsmanagement&tmpl=component&view=match&layout=editstats&id=<?php echo $row->id; ?>"
									 class="modal open-editstats"
									 title="<?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_EDIT_STATS'); ?>">
									 <?php
									 echo JHtml::_(	'image','administrator/components/com_sportsmanagement/assets/images/calc16.png',
													 JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_EDIT_STATS'),'title= "'.JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_EDIT_STATS').'"');
								?>
								</a>								
							</td>
							<td class="center">
								<a	rel="{handler: 'iframe',size: {x: <?php echo $modalwidth; ?>,y: <?php echo $modalheight; ?>}}"
									href="index.php?option=com_sportsmanagement&tmpl=component&view=match&layout=editreferees&id=<?php echo $row->id; ?>&team=<?php echo $row->team1; ?>"
									 class="modal open-editreferees"
									 title="<?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_EDIT_REFEREES'); ?>">
									 <?php
									 if($row->referees_count==0) {
									 	$image = 'players_add.png';
									 } else {
									 	$image = 'icon-16-Referees.png';
									 }
									 $title= $row->referees_count;
									 echo JHtml::_(	'image','administrator/components/com_sportsmanagement/assets/images/'.$image,
													 JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_EDIT_REFEREES'),
													 'title= "'. $title. '"') ;
									 echo '<sub>'.$row->referees_count.'</sub> ';	
									 ?>
								</a>
							</td>
							<td class="center">
								<?php
								echo $published;
								?>
							</td>
							<td class="center">
								<?php
								echo $row->id;
								?>
							</td>
						</tr>
						<?php
						$k=1 - $k;
					}
					?>
				</tbody>
			</table>
				
			<?php $dValue=$this->roundws->round_date_first.' '.$this->projectws->start_time; ?>
			
			<input type='hidden' name='match_date' value='<?php echo $dValue; ?>' />
            <input type='hidden' name='use_legs' value='<?php echo $this->projectws->use_legs; ?>' />
			<input type='hidden' name='boxchecked' value='0' />
			<input type='hidden' name='search_mode' value='<?php echo $this->lists['search_mode']; ?>' />
			<input type='hidden' name='filter_order' value='<?php echo $this->sortColumn; ?>' />
			<input type='hidden' name='filter_order_Dir' value='' />
			<input type='hidden' name='rid' value='<?php echo $this->roundws->id; ?>' />
			<input type='hidden' name='project_id' value='<?php echo $this->roundws->project_id; ?>' />
			<input type='hidden' name='act' value='' />
			<input type='hidden' name='task' value='' />
			<?php echo JHtml::_('form.token')."\n"; ?>
		</form>
<!--	</fieldset> -->
</div>
