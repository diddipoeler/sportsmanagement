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

?>

<table id="clubicons<?php echo $module->id;?>" cellpadding="0" cellspacing="0"
	class="modjsmclubicons">
	<tr>
<?php
		$cnt = 0;
		$after = 0;
		$rest = count($data);
		foreach($data->ranking AS $k => $ficken)
		{
			$val = $data->teams[$k];
			$append = ($params->get('teamlink', 0) == 5 AND $params->get('newwindow', 0) == 1) ?
				' target="_blank"': '';
			echo '<td class="modjlclubicons">';
  		if (!empty($val['link'])) { echo '<a href="'.$val['link'].'"'.$append.'>';}
      echo $val['logo'];
      if (!empty($val['link'])) { echo '</a>';}
      echo '</td>';
      $cnt++;
      $modulo = intval($cnt%$params->get('iconsperrow', 20));
      if ($modulo == 0)
      {
        echo '</tr><tr>';
        $rest = $rest - $params->get('iconsperrow', 20);
        if ($rest < $params->get('iconsperrow', 20))
        {
          $before = floor(($params->get('iconsperrow', 20)-$rest) / 2);
          $after = $params->get('iconsperrow', 20) - $before - $rest;
          for ($x=0;$x < $before;$x++) echo '<td>&nbsp;</td>';
        }
      }
      if ($cnt == count($data) && $after > 0)
      {
        for ($x=0;$x < $after;$x++) echo '<td>&nbsp;</td>';
      }

		}
?>
	</tr>
</table>