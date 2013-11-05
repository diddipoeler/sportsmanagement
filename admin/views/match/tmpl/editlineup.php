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
<form id="startingSquadsForm" name="startingSquadsForm" method="post">
	<fieldset>
		<div class="fltrt">
			<button type="button" onclick="Joomla.submitform('match.saveroster', this.form);">
				<?php echo JText::_('JAPPLY');?></button>
			<button type="button" onclick="$('close').value=1; Joomla.submitform('match.saveroster', this.form);">
				<?php echo JText::_('JSAVE');?></button>
			<button id="cancel" type="button" onclick="<?php echo JRequest::getBool('refresh', 0) ? 'window.parent.location.href=window.parent.location.href;' : '';?>  window.parent.SqueezeBox.close();">
				<?php echo JText::_('JCANCEL');?></button>
		</div>
		<div class="configuration" >
			<?php echo JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_MATCH_ELU_TITLE',$this->teamname); ?>
		</div>
	</fieldset>
	<div class="clear"></div>
	<div id="lineup">
		<?php
		// focus on players tab 
		$startOffset = 1
		;
		echo JHtml::_('tabs.start','tabs', array('startOffset'=>$startOffset));
		echo JHtml::_('tabs.panel',JText::_('COM_SPORTSMANAGEMENT_TABS_SUBST'), 'panel1');
		echo $this->loadTemplate('substitutions');
		
		echo JHtml::_('tabs.panel',JText::_('COM_SPORTSMANAGEMENT_TABS_PLAYERS'), 'panel2');
		echo $this->loadTemplate('players');
		
		echo JHtml::_('tabs.panel',JText::_('COM_SPORTSMANAGEMENT_TABS_STAFF'), 'panel3');
		echo $this->loadTemplate('staff');
		
        echo JHTML::_('tabs.panel',JText::_('COM_SPORTSMANAGEMENT_TABS_PLAYER_TRIKOT_NUMBERS'), 'panel4');
		echo $this->loadTemplate('players_trikot_numbers');
        
		echo JHtml::_('tabs.end');
		?>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="view" value="match" />
		<input type="hidden" name="close" id="close" value="0" />
		<input type="hidden" name="cid[]" value="<?php echo $this->match->id; ?>" />
		<input type="hidden" name="changes_check" value="0" id="changes_check" />
		<input type="hidden" name="option" value="" id="" />
		<input type="hidden" name="team" value="<?php echo $this->tid; ?>" id="team" />
		<input type="hidden" name="positionscount" value="<?php echo count($this->positions); ?>" id="positioncount"	/>
		<?php echo JHtml::_('form.token')."\n"; ?>
	</div>
</form>