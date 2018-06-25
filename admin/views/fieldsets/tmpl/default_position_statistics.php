<?php 
/** SportsManagement ein Programm zur Verwaltung f�r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
* @copyright        Copyright: � 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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
* SportsManagement ist Freie Software: Sie k�nnen es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder sp�teren
* ver�ffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es n�tzlich sein wird, aber
* OHNE JEDE GEW�HRLEISTUNG, bereitgestellt; sogar ohne die implizite
* Gew�hrleistung der MARKTF�HIGKEIT oder EIGNUNG F�R EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License f�r weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

defined('_JEXEC') or die('Restricted access');
?>
<fieldset class="adminform">
	<legend><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_POSITION_STATISTICS_LEGEND'); ?></legend>
	<table class="admintable">
		<tr>
			<td style="width:auto;"><b><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_POSITION_EXISTING_STATISTICS'); ?></b><br /><?php echo $this->lists['statistic']; ?></td>
			<td style="width:auto;">
				<input  type="button" class="inputbox"
						onclick="move_list_items('statistic','position_statistic');"
						value="&gt;&gt;" />
				<br /><br />
				<input  type="button" class="inputbox"
						onclick="move_list_items('position_statistic','statistic');"
						value="&lt;&lt;" />
			</td>
			<td style="width:auto;"><b><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_POSITION_ASSIGNED_STATS_TO_POS'); ?></b><br /><?php echo $this->lists['position_statistic']; ?></td>
			<td align='center' style="width:auto;">
				<input  type="button" class="inputbox"
						onclick="$('statschanges_check').value=1;moveOptionUp('position_statistic');"
						value="<?php echo JText::_('COM_SPORTSMANAGEMENT_GLOBAL_UP'); ?>" />
				<br /><br />
				<input type="button" class="inputbox"
					   onclick="$('statschanges_check').value=1;moveOptionDown('position_statistic');"
					   value="<?php echo JText::_('COM_SPORTSMANAGEMENT_GLOBAL_DOWN'); ?>" />
			</td>
			<td style="width:auto;">
			<fieldset class="adminform">
					<?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_POSITION_STATS_HINT'); ?>
			</fieldset>
			</td>			
		</tr>
	</table>
</fieldset>