<?php
/**
 * @copyright	Copyright (C) 2005-2013 JoomLeague.net. All rights reserved.
 * @license	GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */
/**
 * EditeventsBB view
 *
 * @package	Joomleague
 * @since 0.1
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
JHTML::_( 'behavior.tooltip' );

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
<form method="post" name="statsform" id="statsform">
	<div id="jlstatsform">
	<fieldset>
		<div class="fltrt">
			<button type="button" onclick="Joomla.submitform('match.savestats', this.form);">
				<?php echo JText::_('JAPPLY');?></button>
			<button type="button" onclick="$('close').value=1; Joomla.submitform('match.savestats', this.form);">
				<?php echo JText::_('JSAVE');?></button>
			<button id="cancel" type="button" onclick="<?php echo JRequest::getBool('refresh', 0) ? 'window.parent.location.href=window.parent.location.href;' : '';?>  window.parent.SqueezeBox.close();">
				<?php echo JText::_('JCANCEL');?></button>
		</div>
		<div class="configuration" >
			Stats
		</div>
	</fieldset>
	<div class="clear"></div>
		<?php
		echo JHTML::_('tabs.start','tabs', array('useCookie'=>1));
		echo JHTML::_('tabs.panel',JText::_($this->teams->team1), 'panel1');
		echo $this->loadTemplate('home');
		
		echo JHTML::_('tabs.panel',JText::_($this->teams->team2), 'panel2');
		echo $this->loadTemplate('away');
		
		echo JHTML::_('tabs.end');
		?>
		
		<input type="hidden" name="option" value="com_joomleague" />
		<input type="hidden" name="tmpl" value="component" />
		<input type="hidden" name="close" id="close" value="0" />
		<input type="hidden" name="task" id="match.savestats" value="" />
		<input type="hidden" name="project_id"	value="<?php echo $this->match->project_id; ?>" />
		<input type="hidden" name="match_id"	value="<?php echo $this->match->id; ?>" />
		<input type="hidden" name="boxchecked" value="0" />
		
		<?php echo JHTML::_( 'form.token' ); ?>
	</div>
</form>
<div style="clear: both"></div>
