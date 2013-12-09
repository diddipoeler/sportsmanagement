<?php
/**
 * @copyright  Copyright (C) 2005-2013 JoomLeague.net. All rights reserved.
 * @license    GNU/GPL,see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License,and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */
defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
$params = $this->form->getFieldsets('params');


//echo 'sportsmanagementViewMatch _displayEditReferees project_id<br><pre>'.print_r($this->project_id,true).'</pre>';
//echo 'sportsmanagementViewMatch _displayEditReferees item->id<br><pre>'.print_r($this->item->id,true).'</pre>';
//echo 'sportsmanagementViewMatch _displayEditReferees lists<br><pre>'.print_r($this->lists,true).'</pre>';

?>
<?php
//save and close 
$close = JRequest::getInt('close',0);
if($close == 1) {
	?><script>
	window.addEvent('domready', function() {
		$('cancel').onclick();	
	});
	</script>
	<?php 
}
?>
<div id="lineup">
	<form  action="<?php echo JRoute::_('index.php?option=com_sportsmanagement');?>" id='component-form' method='post' style='display:inline' name='adminform' >
	<fieldset>
		<div class="fltrt">
			<button type="button" onclick="jQuery('select.position-starters option').prop('selected', 'selected');Joomla.submitform('matches.saveReferees', this.form);">
				<?php echo JText::_('JAPPLY');?></button>
			<button type="button" onclick="$('close').value=1; jQuery('select.position-starters option').prop('selected', 'selected'); Joomla.submitform('matches.saveReferees', this.form);">
				<?php echo JText::_('JSAVE');?></button>
			<button id="cancel" type="button" onclick="<?php echo JRequest::getBool('refresh', 0) ? 'window.parent.location.href=window.parent.location.href;' : '';?>  window.parent.SqueezeBox.close();">
				<?php echo JText::_('JCANCEL');?></button>
		</div>
		<div class="configuration" >
			<?php echo JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_MATCH_ER_TITLE'); ?>
		</div>
	</fieldset>
	<div class="clear"></div>
		<fieldset class="adminform">
			<legend><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_ER_DESCR'); ?></legend>
			<table class='adminlist'>
			<thead>
				<tr>
					<th>
					<?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_ER_REFS'); ?>
					</th>
					<th>
					<?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_ER_ASSIGNED'); ?>
					</th>					
				</tr>
			</thead>			
				<tr>
					<td style="text-align:center; ">
						<?php
						// echo select list of non assigned players from team roster
						echo $this->lists['team_referees'];
						?>
					</td>
					<td style="text-align:center; vertical-align:top; ">
						<table>
							<?php
							foreach ($this->positions AS $key => $pos)
							{
								?>
								<tr>
									<td style='text-align:center; vertical-align:middle; '>
										<!-- left / right buttons -->
										<br />
										
                                        
                                        <input id="moveright" type="button" value="Move Right" onclick="move_list_items('roster','position<?php echo $key;?>');" />
                                        <input id="moveleft" type="button" value="Move Left" onclick="move_list_items('position<?php echo $key;?>','roster');" />
                                        
									</td>
									<td>
										<!-- player affected to this position -->
										<b><?php echo JText::_($pos->text); ?></b><br />
										<?php echo $this->lists['team_referees'.$key];?>
									</td>
									<td style='text-align:center; vertical-align:middle; '>
										<!-- up/down buttons -->
										<br />
										<input	type="button" id="moveup-<?php echo $key;?>" class="inputbox move-up"
												value="<?php echo JText::_('COM_SPORTSMANAGEMENT_GLOBAL_UP'); ?>" /><br />
										<input	type="button" id="movedown-<?php echo $key;?>" class="inputbox move-down"
												value="<?php echo JText::_('JGLOBAL_DOWN'); ?>" />
									</td>
								</tr>
								<?php
							}
							?>
						</table>
					</td>
				</tr>
			</table>
		</fieldset>
		<br/>
		<br/>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="view" value="" />
		<input type="hidden" name="close" id="close" value="0" />
		<input type="hidden" name="id" value="<?php echo $this->item->id; ?>" />
		<input type="hidden" name="project_id" value="<?php echo $this->project_id; ?>" />
		<input type="hidden" name="changes_check" value="0" id="changes_check" />
		
		<input type="hidden" name="positionscount" value="<?php echo count($this->positions); ?>" id="positioncount" />
        <input type="hidden" name="component" value="com_sportsmanagement" />
		<?php echo JHtml::_('form.token')."\n"; ?>
	</form>
</div>
