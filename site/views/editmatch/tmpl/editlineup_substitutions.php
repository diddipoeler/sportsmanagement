<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      editlineup_substitutions.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage editmatch
 */

defined('_JEXEC') or die('Restricted access');
$savenewsubst = array();
$savenewsubst[] = $this->match->id;
$savenewsubst[] = $this->tid;
$savenewsubst[] = $this->eventsprojecttime;
$savenewsubst[] = "'".JRoute::_(JURI::base().'index.php?option=com_sportsmanagement')."'";

$baseurl = "'".JRoute::_(JURI::base().'index.php?option=com_sportsmanagement')."'";
?>

<!-- SUBSTITUTIONS START -->
<div id="io">
	<!-- nicht löschen -->
	<div id="ajaxresponse" ></div>
		<fieldset class="adminform">
			<legend><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_ELUSUBST_SUBST'); ?></legend>
			<table class='adminlist' id="table-substitutions">
				<thead>
					<tr>
						<th>
							<?php
							echo JHtml::_('image','administrator/components/com_sportsmanagement/assets/images/out.png',JText::_('Out'));
							echo '&nbsp;'.JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_ELUSUBST_OUT');
							?>
						</th>
						<th>
							<?php
							echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_ELUSUBST_IN').'&nbsp;';
							echo JHtml::_('image','administrator/components/com_sportsmanagement/assets/images/in.png',JText::_('In'));
							?>
						</th>
						<th><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_ELUSUBST_POS'); ?></th>
						<th><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_ELUSUBST_TIME'); ?></th>
						<th>&nbsp;</th>
					</tr>
				</thead>
				<tbody>
					<?php
					$k=0;
                    if ( isset($this->substitutions) )
                    {
					for ($i=0; $i < count($this->substitutions); $i++)
					{
						$substitution=$this->substitutions[$i];
						?>
						<tr id="sub-<?php echo $substitution->id; ?>" class="<?php echo "row$k"; ?>">
							<td>
								<?php 
								if($substitution->came_in==2) {
									echo sportsmanagementHelper::formatName(null, $substitution->firstname, $substitution->nickname, $substitution->lastname, 0);
								} else {
									echo sportsmanagementHelper::formatName(null, $substitution->out_firstname, $substitution->out_nickname, $substitution->out_lastname, 0);
								} 
								?>
							</td>
							<td>
								<?php 
								if($substitution->came_in==1) {
									echo sportsmanagementHelper::formatName(null, $substitution->firstname, $substitution->nickname, $substitution->lastname, 0);
								} 
								?>
							</td>
							<td>
								<?php echo JText::_($substitution->in_position); ?>
							</td>
							<td>
								<?php
								$time=(!is_null($substitution->in_out_time) && $substitution->in_out_time > 0) ? $substitution->in_out_time : '--';
								echo $time;
								?>
							</td>
							<td>
		<input onclick="delete_subst(<?php echo $substitution->id; ?>,<?php echo $baseurl; ?>)" id="deletesubst-<?php echo $substitution->id; ?>" type="button" class="inputbox button-delete-subst"
										value="<?php echo JText::_('JACTION_DELETE'); ?>" />
							</td>
						</tr>
						<?php
						$k=(1-$k);
					}
                    }
					?>
					<tr id="row-new">
						<td><?php echo JHtml::_('select.genericlist',$this->playersoptionsout,'out','class="inputbox player-out"'); ?></td>
						<td><?php echo JHtml::_('select.genericlist',$this->playersoptionsin,'in','class="inputbox player-in"'); ?></td>
						<td><?php echo $this->lists['projectpositions']; ?></td>
						<td><input type="text" size="3" id="in_out_time" name="in_out_time" class="inputbox" /></td>
                        
                        
                        
						<td>
<input id="save-new-subst" onclick="save_new_subst(<?php echo implode(",",$savenewsubst); ?>)" type="button" class="inputbox button-save-subst" value="<?php echo JText::_('JSAVE'); ?>" />
                            
						</td>
					</tr>
				</tbody>
			</table>
		</fieldset>
</div>
<!-- SUBSTITUTIONS END -->
