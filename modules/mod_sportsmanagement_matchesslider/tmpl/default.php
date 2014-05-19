<?PHP
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

//echo ' matches<br><pre>'.print_r($slidermatches,true).'</pre>';
//echo ' projectid<br><pre>'.print_r($projectid,true).'</pre>';

?>

<div id="scroller">
  
  
<?PHP
foreach( $slidermatches as $match )
{
    
?>  
  <div class="section">
    <div class="hp-highlight" >
    <div class="feature-headline">
    <h1>
    <a href="<?PHP echo $link;  ?>" title="">
    <?PHP
//echo $match->match_date;
echo JHTML::_('date', $match->match_date, $params->get('dateformat'), null);
echo ' ';
echo JHTML :: _('date', $match->match_date, $params->get('timeformat'), null);
?>
</a>
</h1>
<p style="text-align: center;">
<?PHP
echo '<img style="float: left;" src="'.$match->logohome.'" alt="'.$match->teamhome.'"  width="'.$params->get('xsize').'" title="'.$match->teamhome.'" '.$match->teamhome.' />';

echo ''.$match->team1_result;
echo ' - ';
echo $match->team2_result.'';

echo '<img style="float: right;" src="'.$match->logoaway.'" alt="'.$match->teamaway.'" width="'.$params->get('xsize').'" title="'.$match->teamaway.'" '.$match->teamaway.' />';
?>
</p>

<p style="text-align: center;"> 
<?PHP
echo $match->teamhome;
echo ' - ';
echo $match->teamaway;
?>
</p>
   
    </div>
    </div>
  </div>
<?PHP
}
?>
  
</div>    