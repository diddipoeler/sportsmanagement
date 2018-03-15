<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
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
* OHNE JEDE GEWÄHRLEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.tooltip');
//JHtml::_('behavior.formvalidation');

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
			<button type="button" onclick="Joomla.submitform('rounds.massadd', this.form)">
				<?php echo JText::_('JSAVE');?></button>
			<button id="cancel" type="button" onclick="<?php echo JFactory::getApplication()->input->getBool('refresh', 0) ? 'window.parent.location.href=window.parent.location.href;' : '';?>  window.parent.SqueezeBox.close();">
				<?php echo JText::_('JCANCEL');?></button>
		</div>
		
	</fieldset>
    
			<input type='hidden' name='project_id' value='<?php echo $this->project->id; ?>' />
			<input type='hidden' name='task' value='' />
			<?php echo JHtml::_('form.token')."\n"; ?>
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