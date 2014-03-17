<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
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
* OHNE JEDE GEWÄHELEISTUNG, bereitgestellt; sogar ohne die implizite
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
JHtml::_('behavior.framework');

//$listOrder	= $this->escape($this->state->get('list.ordering'));
//$listDirn	= $this->escape($this->state->get('list.direction'));

?>
<script language="javascript" type="text/javascript">
function tableOrdering( order, dir, task )
{
        var form = document.adminForm;

        form.filter_order.value = order;
        form.filter_order_Dir.value = dir;
        document.adminForm.submit( task );
}
</script>

<form name="adminForm" id="adminForm" action="<?php echo htmlspecialchars(JFactory::getURI()->toString());?>" method="post">
	<fieldset class="filters">
	<legend class="hidelabeltxt"><?php echo JText::_('JGLOBAL_FILTER_LABEL'); ?></legend>
	<div class="filter-search">

		<label class="filter_search-lbl" for="filter_search"><?php echo JText::_('COM_SPORTSMANAGEMENT_FILTER_LABEL').'&#160;'; ?></label>
		<input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->filter); ?>" class="inputbox" onchange="document.getElementById('adminForm').submit();" />



		<button type="submit" class="button"><?php echo JText::_('JGLOBAL_FILTER_BUTTON'); ?></button>
        <button class="button" onclick="document.getElementById('filter_search').value='';this.form.submit(); ">
					<?php
					echo JText::_('JSEARCH_FILTER_CLEAR');
					?>
				</button>
        <td nowrap='nowrap' align='right'><?php echo $this->lists['nation2'].'&nbsp;&nbsp;'; ?></td>
	</div>

	
<input type="hidden" name="filter_order" value="<?php echo $this->sortColumn; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->sortDirection; ?>" />
<input type="hidden" name="limitstart" value="" />
        
    <div class="display-limit">
			<?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?>&#160;
			<?php echo $this->pagination->getLimitBox(); ?>
		</div>
        
	</fieldset>

	<?php echo $this->loadTemplate('items'); ?>
</form>


