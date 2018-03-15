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
JHtml::_('behavior.modal');
$templatesToLoad = array('footer','listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>
<script language="javascript">
window.addEvent('domready',function(){
	$$('table.adminlist tr').each(function(el){
		var cb;
		if (cb=el.getElement("input[name^=cid]")) {
			el.getElement("input[name^=roundcode]").addEvent('change',function(){
				if (isNaN(this.value)) {
					alert(Joomla.JText._('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_CSJS_MSG_NOTANUMBER'));
					return false;
				}
			});
		}
	});
});
</script>
<div id='alt_massadd_enter' style='display:<?php echo ($this->massadd == 0) ? 'none' : 'block'; ?>'>
	<fieldset class='adminform'>
		<legend><?php echo JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_MASSADD_LEGEND','<i>'.$this->project->name.'</i>'); ?></legend>
		<form id='copyform' method='post' style='display:inline' id='copyform'>
			<input type='hidden' name='project_id' value='<?php echo $this->project->id; ?>' />
			<input type='hidden' name='task' value='round.copyfrom' />
			<?php echo JHtml::_('form.token')."\n"; ?>
			<table class='admintable'><tbody><tr>
				<td class='key' nowrap='nowrap'><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_MASSADD_COUNT'); ?></td>
				<td><input type='text' name='add_round_count' id='add_round_count' value='0' size='3' class='inputbox' /></td>
				<td><input type='submit' class='button' value='<?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_MASSADD_SUBMIT_BUTTON'); ?>' onclick='this.form.submit();' /></td>
			</tr></tbody></table>
		</form>
	</fieldset>
</div>

<form action="<?php echo $this->request_url; ?>" method="post" id="adminForm" name="adminForm">
<?PHP
if (!$this->massadd)
{
echo $this->loadTemplate('joomla_version');
}
?>
<input type="hidden" name="pid" value="<?php echo $this->project->id; ?>" />
<input type="hidden" name="next_roundcode" value="<?php echo count($this->matchday) + 1; ?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="filter_order" value="<?php echo $this->sortColumn; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->sortDirection; ?>" />
<?php echo JHtml::_('form.token')."\n"; ?>
<?php echo $this->table_data_div; ?>
</form>
<div>
<?php
echo $this->loadTemplate('footer');
?>
</div>
