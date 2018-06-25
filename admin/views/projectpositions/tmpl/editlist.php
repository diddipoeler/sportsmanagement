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
JHtml::_('behavior.tooltip');
?>




<form  action="<?php echo JRoute::_('index.php?option=com_sportsmanagement');?>" id='component-form' method='post' style='display:inline' name='adminform' >
	<div class="col50">

    <fieldset>
		<div class="fltrt">
			<button type="button" onclick="jQuery('select#project_positionslist > option').prop('selected', 'selected');Joomla.submitform('projectpositions.store', this.form)">
				<?php echo JText::_('JSAVE');?></button>
			<button id="cancel" type="button" onclick="<?php echo JFactory::getApplication()->input->getBool('refresh', 0) ? 'window.parent.location.href=window.parent.location.href;' : '';?>  window.parent.SqueezeBox.close();">
				<?php echo JText::_('JCANCEL');?></button>
		</div>
	</fieldset>
    
		<fieldset class="adminform">
			<legend><?php echo JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_EDIT_LEGEND','<i>'.$this->project->name.'</i>');?></legend>
			<table class="<?php echo $this->table_data_class; ?>">
			<thead>
				<tr>
					<th><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_EDIT_AVAILABLE'); ?></th>
					<th width="20"></th>
					<th><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_EDIT_ASSIGNED'); ?></th>
					
				</tr>
			</thead>
				<tr>		
					<td><?php echo $this->lists['positions']; ?></td>				
					<td style="text-align:center;">
<input id="moveright" type="button" value="Move Right" onclick="move_list_items('positionslist','project_positionslist');" />
<input id="moverightall" type="button" value="Move Right All" onclick="move_list_items_all('positionslist','project_positionslist');" />
<input id="moveleft" type="button" value="Move Left" onclick="move_list_items('project_positionslist','positionslist');" />
<input id="moveleftall" type="button" value="Move Left All" onclick="move_list_items_all('project_positionslist','positionslist');" />

					</td>
					<td><?php echo $this->lists['project_positions']; ?></td>
				</tr>
			</table>
		</fieldset>
		<div class="clr"></div>
		<input type="hidden" name="positionschanges_check" value="0" id="positionschanges_check" />
		<input type="hidden" name="pid" value="<?php echo $this->project->id; ?>" />
		<input type="hidden" name="task" value="" />
        <input type='hidden' name='project_id' value='<?php echo $this->project->id; ?>' />
            <input type="hidden" name="component" value="com_sportsmanagement" />
	</div>
	<?php echo JHtml::_('form.token')."\n"; ?>
</form>