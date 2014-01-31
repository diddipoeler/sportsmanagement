<?php 
/** SportsManagement ein Programm zur Verwaltung f?r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: ? 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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
* SportsManagement ist Freie Software: Sie k?nnen es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder sp?teren
* ver?ffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es n?tzlich sein wird, aber
* OHNE JEDE GEW?HELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gew?hrleistung der MARKTF?HIGKEIT oder EIGNUNG F?R EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License f?r weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

defined( '_JEXEC' ) or die( 'Restricted access' ); ?>

<table class="contentpaneopen">
	<tr>
		<td class="contentheading">
		<?php
		if ( $this->config['show_team_shortform'] == 1 && !empty($this->team->short_name))
		{
			echo '&nbsp;' . JText::sprintf( 'COM_SPORTSMANAGEMENT_ROSTER_TITLE2', $this->team->name, $this->team->short_name );
		}
		else
		{
			echo '&nbsp;' . JText::sprintf( 'COM_SPORTSMANAGEMENT_ROSTER_TITLE', $this->team->name );
		}
		?>
		</td>
        
        
        
		<form name='resultsRoundSelector' method='post'>
		<input type='hidden' name='option' value='com_sportsmanagement' />
        <?php
        if ( $this->config['show_drop_down_menue'] )
        {
        echo "<td>".JHtml::_('select.genericlist', $this->lists['type'], 'type' , 'class="inputbox" size="1" onchange="this.form.submit();" ', 'value', 'text', $this->type )."</td>";
        echo "<td>".JHtml::_('select.genericlist', $this->lists['typestaff'], 'typestaff' , 'class="inputbox" size="1" onchange="this.form.submit();" ', 'value', 'text', $this->typestaff )."</td>";
        }
        ?>
        </form>
	</tr>
</table>
<br />
