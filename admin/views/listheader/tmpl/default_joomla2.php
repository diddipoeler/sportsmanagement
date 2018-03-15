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
$view = JFactory::getApplication()->input->getCmd('view', 'cpanel');

?>
<div id="j-main-container" class="span10">

<?PHP
if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
{
echo $this->loadTemplate('debug');
}
?>
<table>
		<tr>
			<td align="left" width="100%">
				<?php
				echo JText::_('JSEARCH_FILTER_LABEL');
				?>&nbsp;<input	type="text" name="filter_search" id="filter_search"
								value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
								class="text_area" onchange="$('adminForm').submit(); " />
                                
				<button onclick="this.form.submit(); "><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
				<button onclick="document.getElementById('filter_search').value='';this.form.submit(); ">
					<?php
					echo JText::_('JSEARCH_FILTER_CLEAR');
					?>
				</button>
			</td>
            
            <?PHP
            
            switch ($view)
            {
            case 'projectteams':
               
            ?>
            <td nowrap='nowrap' align='right'><?php echo $this->lists['nationpt'].'&nbsp;&nbsp;'; ?></td>
            <?PHP
            
            break;
            default:    
            if ( isset($this->lists) )
            {
            foreach ( $this->lists as $key  => $value)
            {
            if ( !is_array($value) )
            {    
            ?>
            <td nowrap='nowrap' align='right'><?php echo $this->lists[$key].'&nbsp;&nbsp;'; ?></td>
            <?PHP
            }
            }
            }
            break;
            }
            
            switch ($view)
            {
            case 'projects':
            case 'persons':
            case 'predictiongames':
            ?>
            <td nowrap='nowrap' align='right'>
            <select name="filter_published" id="filter_published" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
				<?php 
                echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.state'), true);
                ?>
			</select>
            </td>
            <?PHP
            break;    
            case 'smquotes':
            ?>
            <td nowrap='nowrap' align='right'>
            <select name="filter_category_id" id="filter_category_id" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_CATEGORY');?></option>
				<?php 
                echo JHtml::_('select.options', JHtml::_('category.options', 'com_sportsmanagement'), 'value', 'text', $this->state->get('filter.category_id'));
                ?>
			</select>
            </td>
            <td nowrap='nowrap' align='right'>
            <select name="filter_published" id="filter_published" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
				<?php 
                echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.state'), true);
                ?>
			</select>
            </td>
            <?PHP
            break;
            }
            
            ?>
            
            <?PHP
            switch ($view)
            {
                case 'leagues':
                case 'jlextcountries':
            ?>
			<td align="center" colspan="4">
				<?php
                $startRange = JComponentHelper::getParams(JFactory::getApplication()->input->getCmd('option'))->get('character_filter_start_hex', '0');
		$endRange = JComponentHelper::getParams(JFactory::getApplication()->input->getCmd('option'))->get('character_filter_end_hex', '0');
		for ($i=$startRange; $i <= $endRange; $i++)
		{
            printf("<a href=\"javascript:searchPerson('%s')\">%s</a>&nbsp;&nbsp;&nbsp;&nbsp;",'&#'.$i.';','&#'.$i.';');
			}
				
				?>
			</td>
            <?PHP
            break;
            
            default:
            break;
            }
            
            
            
            ?>
		</tr>
	</table>
  