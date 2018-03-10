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

/*
echo 'starters<pre>'.print_r($this->starters,true).'</pre><br>';
echo 'positions<pre>'.print_r($this->positions,true).'</pre><br>';
echo 'substitutions<pre>'.print_r($this->substitutions,true).'</pre><br>';
*/

?>
<fieldset class="adminform">
<legend><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_ELUP_TRIKOT_NUMBER'); ?></legend>
<?php    
if ( isset($this->positions) )
{  
foreach ($this->positions AS $position_id => $pos)
		{
		?>
<fieldset class="adminform">
<legend><?php echo JText::_($pos->text); ?></legend>
<table>    
    <?PHP
    // get players assigned to this position
    foreach ($this->starters[$position_id] AS $player)
		{
		//echo ''.$player->firstname.'-'.$player->lastname.'-'.$player->jerseynumber.'-'.$player->trikot_number.'<br>';
		?>
		<tr>
		
    <td><?php echo $player->firstname; ?>
    </td>
    
    <td><?php echo $player->lastname; ?>
    </td>
    
    <td><?php echo $player->jerseynumber; ?>
    </td>
    
    <td><input type='' name='trikot_number[<?php echo $player->value;?>]' value="<?php echo $player->trikot_number; ?>" />
    </td>
<td>
<?PHP    
    $append=' style="background-color:#bbffff"';
									echo JHtml::_(	'select.genericlist',
													$this->lists['captain'],
													'captain['.$player->value.']',
													'class="inputbox" size="1" '.$append,
													'value','text',$player->captain);
?> 
</td>                                                   	
		</tr>
		<?PHP
    }
		
    ?>
    </table>
    </fieldset>   
    <?PHP	
		}
}

?>      
</fieldset>      