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
?>
<fieldset class="adminform">	
	<legend><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_POSITION_EVENTS_LEGEND'); ?></legend>
	<table class="admintable">
		<tr>
			<td style="width:auto;"><b><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_POSITION_EXISTING_EVENTS'); ?></b><br /><?php echo $this->lists['events']; ?></td>
			<td style="width:auto;">
				<input  type="button" class="inputbox"
						onclick="move_list_items('eventslist','position_eventslist');jQuery('#position_eventslist option').prop('selected', true);"
						value="&gt;&gt;" />
				<br /><br />
				<input  type="button" class="inputbox"
						onclick="move_list_items('position_eventslist','eventslist');jQuery('#position_eventslist option').prop('selected', true);"
						value="&lt;&lt;" />
			</td>
			<td style="width:auto;"><b><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_POSITION_ASSIGNED_EVENTS_TO_POS'); ?></b><br /><?php echo $this->lists['position_events']; ?></td>
			<td align='center' style="width:auto;">
				<input  type="button" class="inputbox"
						onclick="move_up('position_eventslist');jQuery('#position_eventslist option').prop('selected', true);"
						value="<?php echo JText::_('COM_SPORTSMANAGEMENT_GLOBAL_UP'); ?>" />
				<br /><br />
				<input type="button" class="inputbox"
					   onclick="move_down('position_eventslist');jQuery('#position_eventslist option').prop('selected', true);"
					   value="<?php echo JText::_('COM_SPORTSMANAGEMENT_GLOBAL_DOWN'); ?>" />
			</td>
			<td style="width:auto;">
			<fieldset class="adminform">
					<?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_POSITION_EVENTS_HINT'); ?>
			</fieldset>
			</td>
		</tr>
	</table>
</fieldset>
