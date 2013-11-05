<?php
/**
 * @copyright  Copyright (C) 2013 fussballineuropa.de All rights reserved.
 * @license    GNU/GPL,see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License,and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */
defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.tooltip');
//JHtml::_('behavior.formvalidation');

//$params = $this->form->getFieldsets('params');
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

<div id='alt_massadd_enter' style='display:block'>
	<fieldset class='adminform'>
		<legend><?php echo JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_MASSADD_LEGEND','<i>'.$this->project->name.'</i>'); ?></legend>
		<form  action="index.php?option=com_sportsmanagement&view=rounds" id='component-form' method='post' style='display:inline' name='adminform' >
        
        <fieldset>
		<div class="fltrt">
			<button type="button" onclick="this.form.submit();window.parent.SqueezeBox.close();">
				<?php echo JText::_('JSAVE');?></button>
			<button id="cancel" type="button" onclick="<?php echo JRequest::getBool('refresh', 0) ? 'window.parent.location.href=window.parent.location.href;' : '';?>  window.parent.SqueezeBox.close();">
				<?php echo JText::_('JCANCEL');?></button>
		</div>
		
	</fieldset>
    
			<input type='hidden' name='project_id' value='<?php echo $this->project->is; ?>' />
			<input type='hidden' name='task' value='round.massadd' />
			<?php echo JHTML::_('form.token')."\n"; ?>
			<table class='admintable'><tbody><tr>
				<td class='key' nowrap='nowrap'><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_MASSADD_COUNT'); ?></td>
				<td><input type='text' name='add_round_count' id='add_round_count' value='0' size='3' class='inputbox' /></td>
				
			</tr></tbody></table>
		</form>
	</fieldset>
</div>

<?PHP

?>