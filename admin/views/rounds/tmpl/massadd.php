<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      massadd.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage rounds
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('behavior.tooltip');


//$params = $this->form->getFieldsets('params');
//save and close 
$close = JFactory::getApplication()->input->getInt('close',0);
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
		<form  action="<?php echo JRoute::_('index.php?option=com_sportsmanagement');?>" id='component-form' method='post' style='display:inline' name='adminform' >
        
        <fieldset>
		<div class="fltrt">
			<button id="save" type="button" onclick="Joomla.submitform('rounds.massadd', this.form)">
				<?php echo JText::_('JSAVE');?></button>
			<button id="cancel" type="button" onclick="Joomla.submitform('rounds.cancel', this.form)">
				<?php echo JText::_('JCANCEL');?></button>
		</div>
		
	</fieldset>
    
			<input type='hidden' name='project_id' value='<?php echo $this->project->id; ?>' />
			<input type='hidden' name='task' value='' />
			<?php echo HTMLHelper::_('form.token')."\n"; ?>
			<table class='admintable'><tbody><tr>
				<td class='key' nowrap='nowrap'><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_MASSADD_COUNT'); ?></td>
				<td>
                <input type='text' name='add_round_count' id='add_round_count' value='0' size='3' class='inputbox' />
                </td>
				
			</tr></tbody></table>
            <input type='hidden' name='project_id' value='<?php echo $this->project->id; ?>' />
            <input type="hidden" name="component" value="com_sportsmanagement" />
            
		</form>
	</fieldset>
</div>

<?PHP

?>
