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
* OHNE JEDE GEW�HELEISTUNG, bereitgestellt; sogar ohne die implizite
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

<table id="clubicons<?php echo $module->id;?>" class="<?php echo $params->get('table_class', 'table');?>">
<tr>
<?php
		$cnt = 0;
		$after = 0;
		$rest = count($data);
		foreach($data->ranking AS $k => $value)
		{
			$val = $data->teams[$k];
			$append = ($params->get('teamlink', 0) == 5 AND $params->get('newwindow', 0) == 1) ?
				' target="_blank"': '';
			?>
<td class="">
            <?PHP
  		if (!empty($val['link'])) { echo '<a href="'.$val['link'].'"'.$append.'>';}
      echo $val['logo'];
      if (!empty($val['link'])) { echo '</a>';}
      ?>
</td>
      <?PHP
      $cnt++;
      $modulo = intval($cnt%$params->get('iconsperrow', 20));
      if ($modulo == 0)
      {
        ?>
</tr><tr>
        <?PHP
      }


		}
?>
</tr>
</table>