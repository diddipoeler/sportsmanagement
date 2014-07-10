<?php
/**
 * @copyright	Copyright (C) 2005-2013 JoomLeague.net. All rights reserved.
 * @license		GNU/GPL,see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License,and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

defined('_JEXEC') or die('Restricted access');

// &tmpl=component&view=match&layout=editlineup
/*
var baseajaxurl='<?php echo JUri::root();?>administrator/index.php?option=com_sportsmanagement&<?php echo JUtility::getToken() ?>=1';
var baseajaxurl='<?php echo JUri::root();?>administrator/index.php?option=com_sportsmanagement';
*/

?>
<script type="text/javascript">
<!--
// url for ajax
var baseajaxurl='<?php echo JUri::root();?>administrator/index.php?option=com_sportsmanagement';
var teamid=<?php echo $this->tid; ?>;
var matchid=<?php echo $this->item->id; ?>;

var projecttime=<?php echo $this->eventsprojecttime; ?>;
// We need to setup some text variables for translation
var str_delete="<?php echo JText::_('JACTION_DELETE'); ?>";
//-->
</script>
<!-- SUBSTITUTIONS START -->
<div id="io">
	<!-- Don't remove this "<div id"ajaxresponse"></div> as it is neede for ajax changings -->
	<div id="ajaxresponse" >ajax</div>
		<fieldset class="adminform">
			<legend><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_ELUSUBST_SUBST'); ?></legend>
			<table class='adminlist' id="substitutions">
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
								<input	id="deletesubst-<?php echo $substitution->id; ?>" type="button" class="inputbox button-delete-subst"
										value="<?php echo JText::_('JTOOLBAR_REMOVE'); ?>" />
							</td>
						</tr>
						<?php
						$k=(1-$k);
					}
					?>
					<tr id="row-new">
						<td><?php echo JHtml::_('select.genericlist',$this->playersoptionsout,'out','class="inputbox player-out"'); ?></td>
						<td><?php echo JHtml::_('select.genericlist',$this->playersoptionsin,'in','class="inputbox player-in"'); ?></td>
						<td><?php echo $this->lists['projectpositions']; ?></td>
						<td><input type="text" size="3" id="in_out_time" name="in_out_time" class="inputbox" /></td>
                        
                        
                        
						<td>
							<input id="save-new-subst" type="button" class="inputbox button-save-subst" value="<?php echo JText::_('JTOOLBAR_APPLY'); ?>" />
                            
						</td>
					</tr>
				</tbody>
			</table>
		</fieldset>
</div>
<!-- SUBSTITUTIONS END -->
