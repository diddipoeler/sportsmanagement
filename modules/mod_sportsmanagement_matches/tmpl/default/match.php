<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.41
* @file                match.php
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
<div id="modJLML<?php echo $module->id.'_row'.$cnt;?>" class="<?php echo $styleclass;?> jlmlmatchholder">
<!--jlml-mod<?php echo $module->id.'nr'.$cnt;?> start-->
  <?php
if ($heading != $lastheading) {
?><div class="contentheading">
      <?php echo $heading;?>
    </div>
  <?php
}
  if ($show_pheading) {
?><div class="<?php echo $params->get('heading_style');?>">
      <?php echo $pheading;?>
    </div>
<?php
}
?>
<table class="table">
    <tr class="<?php echo $styleclass;?>">   
      <td colspan="3">
      <?php
      if (!empty($match['location'])) echo '<span style="white-space:nowrap;">'.$match['location'].'</span> ';
      echo ' <span style="white-space:nowrap;">'.$match['date'].'</span> '
      .' <span style="white-space:nowrap;">'.$match['time'].'</span> ';
      if (isset($match['meeting'])) echo' <span style="white-space:nowrap;">'.$match['meeting'].'</span> ';
      ?>

      </td>
    </tr>
    <tr class="<?php echo $styleclass;?>">
      <td class="jlmlteamcol">
      <?php
    if (!empty($match['hometeam']['logo'])) {
      echo '<img src="'.$match['hometeam']['logo']['src'].'" alt="'.$match['hometeam']['logo']['alt'].'" title="'.$match['hometeam']['logo']['alt'].'" '.$match['hometeam']['logo']['append'].' />';
      ?><br />
      <?php
    }
      if($params->get('show_names') == 1) { echo $match['hometeam']['name']; }
      if (!empty($match['homeover'])) echo $match['homeover'];
      ?>
      </td>
      <td class="jlmlResults">
        <span class="jlmlResults">
    <?php
    
    if ( $match['resultpenalty'] )
    {
    echo $match['resultpenalty'];    
    }
    elseif ( $match['resultovertime'] )
    {
    echo $match['resultovertime'];    
    }
    else
    {
    echo $match['result']; 
    }
    
    ?>
      </span>
    <?php
      if ($match['reportlink'] OR $match['statisticlink'] OR $match['nextmatchlink']) {  ?>
      <span class="jlmlMatchLinks">
    <?php
      if ($match['reportlink']) { echo $match['reportlink']; }
      if ($match['statisticlink']) { echo $match['statisticlink']; }
      if ($match['nextmatchlink']) { echo $match['nextmatchlink']; }
    ?>
      </span>
    <?php } ?>
  </td>
  <td class="jlmlteamcol">
      <?php
    if (!empty($match['awayteam']['logo'])) {
      echo '<img src="'.$match['awayteam']['logo']['src'].'" alt="'.$match['awayteam']['logo']['alt'].'" title="'.$match['awayteam']['logo']['alt'].'" '.$match['awayteam']['logo']['append'].' />';
      ?><br />
      <?php
    }
      if($params->get('show_names') == 1) { echo $match['awayteam']['name']; }
      if (!empty($match['awayover'])) echo $match['awayover'];
      ?>
    </td>
 </tr>
 <?php
  if (!empty($match['partresults'])) {?>
    <tr class="<?php echo $styleclass;?>">
      <td colspan="3"><?php echo $match['partresults'];?>

      </td>
    </tr>
      <?php
    }
  ?>
  <?php
  if (!empty($match['referee']) OR !empty($match['crowd'])) { ?>
    <tr class="<?php echo $styleclass;?>">
      <td colspan="3">
      <?php 
      echo $match['referee'] . ' '. $match['spectators'];
      ?>
      </td>
    </tr>
<?php
}
  if (!empty($match['notice'])) { ?>
    <tr class="<?php echo $styleclass;?>">
      <td colspan="3">
      <?php 
      echo $match['notice'];
      ?>
    </td>
  </tr>

<?php
}
?>
</table>
<?php
if ($match['ajax']) echo $match['ajax'];
$limit = (int) $params->get("limit"); 
if($limit>1) {
?>
<hr style="width:100%;display:block;clear:both;margin-top:10px;" />
<?php } ?>
<!--jlml-mod<?php echo $module->id.'nr'.$cnt;?> end-->
</div>
<?php
 if($ajax && $ajaxmod==$module->id){ exit(); } ?>