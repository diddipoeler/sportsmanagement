<?php
/**
* @copyright	Copyright (C) 2013 fussballineuropa.de All rights reserved.
* @license	GNU/GPL,see LICENSE.php
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

/**
 * Match Form
 *
 * @author diddipoeler
 * @package	 SportManagement
 * @since 0.1
 */
?>
<div id="matchdetails">
	<form method="post" id="adminForm">
		<!-- Score Table START -->
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
			<fieldset>
				<div class="fltrt">
					<button type="button" onclick="Joomla.submitform('match.savedetails');">
						<?php echo JText::_('JAPPLY');?></button>
					<button type="button" onclick="$('close').value=1; Joomla.submitform('match.savedetails');">
						<?php echo JText::_('JSAVE');?></button>
					<button id="cancel" type="button" onclick="<?php echo JRequest::getBool('refresh', 0) ? 'window.parent.location.href=window.parent.location.href;' : '';?>  window.parent.SqueezeBox.close();">
						<?php echo JText::_('JCANCEL');?></button>
				</div>
				<div class="configuration" >
					<?php echo JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_TITLE',$this->match->hometeam,$this->match->awayteam); ?>
				</div>
			</fieldset>
		<?php
		// focus matchreport tab when the match was already played
		$startOffset = 0;
		if (strtotime($this->match->match_date) < time() )
		{
			$startOffset = 4;
		}
		echo JHTML::_('tabs.start','tabs', array('startOffset'=>$startOffset));
		echo JHTML::_('tabs.panel',JText::_('COM_SPORTSMANAGEMENT_TABS_MATCHPREVIEW'), 'panel1');
		echo $this->loadTemplate('matchpreview');
		
		echo JHTML::_('tabs.panel',JText::_('COM_SPORTSMANAGEMENT_TABS_MATCHDETAILS'), 'panel2');
		echo $this->loadTemplate('matchdetails');
		
		echo JHTML::_('tabs.panel',JText::_('COM_SPORTSMANAGEMENT_TABS_SCOREDETAILS'), 'panel3');
		echo $this->loadTemplate('scoredetails');
		
		echo JHTML::_('tabs.panel',JText::_('COM_SPORTSMANAGEMENT_TABS_ALTDECISION'), 'panel4');
		echo $this->loadTemplate('altdecision');
		
		echo JHTML::_('tabs.panel',JText::_('COM_SPORTSMANAGEMENT_TABS_MATCHREPORT'), 'panel5');
		echo $this->loadTemplate('matchreport');
		
		echo JHTML::_('tabs.panel',JText::_('COM_SPORTSMANAGEMENT_TABS_MATCHRELATION'), 'panel6');
		echo $this->loadTemplate('matchrelation');
		
		echo JHTML::_('tabs.panel',JText::_('COM_SPORTSMANAGEMENT_TABS_EXTENDED'), 'panel7');
		echo $this->loadTemplate('matchextended');
		
// 		echo JHTML::_('tabs.panel',JText::_('COM_SPORTSMANAGEMENT_TABS_MATCHPICTURE'), 'panel8');
// 		echo $this->loadTemplate('matchpicture');
		
		echo JHTML::_('tabs.end');
		
		?>
		<!-- Additional Details Table END -->
		<div class="clr"></div>
		<input type="hidden" name="option" value="com_sportsmanagement"/>
		<input type="hidden" name="task" value=""/>
		<input type="hidden" name="close" id="close" value="0"/>
		<input type="hidden" name="id" value="<?php echo $this->match->id; ?>"/>
		<?php echo JHTML::_('form.token')."\n"; ?>
	</div>
</form>