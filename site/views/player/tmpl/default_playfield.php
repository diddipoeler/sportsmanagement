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

defined( '_JEXEC' ) or die( 'Restricted access' ); ?>

<table>
<tr>
<td width="50%">
<h2><?php echo '&nbsp;' . JText::_( 'COM_SPORTSMANAGEMENT_PERSON_PLAYFIELD' ); ?></h2>

<?php
					
$backimage = 'images/com_sportsmanagement/database/person_playground/' . $this->teamPlayer->position_name . '.png'; 					
$hauptimage = 'images/com_sportsmanagement/database/person_playground/hauptposition.png';
$nebenimage = 'images/com_sportsmanagement/database/person_playground/nebenposition.png';

echo 'person_playground <pre>'.print_r($backimage,true).'</pre>';
echo 'person_position <pre>'.print_r($this->person_position,true).'</pre>';
echo 'position_name <pre>'.print_r($this->teamPlayer->position_name,true).'</pre>';

if ( $this->person_position )
{
?>
<div style="position:relative;height:170px;background-image:url(<?PHP echo $backimage;?>);background-repeat:no-repeat;">
<img src="<?PHP echo $hauptimage;?>" class="<?PHP echo $this->person_position;?>" alt="<?PHP echo $this->teamPlayer->position_name; ?>" title="<?PHP echo $this->teamPlayer->position_name; ?>" />

<?PHP


if ( isset($this->person_parent_positions) )
{

if ( is_array($this->person_parent_positions) )
{
foreach ( $this->person_parent_positions as $key => $value)
{
?>
<img src="<?PHP echo $nebenimage;?>" class="<?PHP echo $value;?>" alt="Nebenposition" title="Nebenposition" />
<?PHP
}
}
else
{
?>
<img src="<?PHP echo $nebenimage;?>" class="<?PHP echo $this->person_parent_positions;?>" alt="Nebenposition" title="Nebenposition" />
<?PHP
}

}
?>
</div>
<?PHP
}


?>
</td>
</tr>
</table>