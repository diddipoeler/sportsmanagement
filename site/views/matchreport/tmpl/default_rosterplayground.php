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

defined( '_JEXEC' ) or die( 'Restricted access' );
$startfade = $this->config['roster_playground_player_fade'];

if ( $this->config['roster_playground_player_jquery_fade'] )
{
$div_display ="none";    
?>
<script>
jQuery(document).ready(function() {
setTimeout(function(){    
<?php
foreach ($this->matchplayers as $player)
{
?>    
jQuery("#<?PHP echo $player->person_id; ?>").delay(<?PHP echo $startfade; ?>).slideToggle("slow");
<?php
$startfade += $this->config['roster_playground_player_fade'];    
}    
?>
}, 2000);
});    
</script>
<?php
}
else
{
    $div_display ="";
}

if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
{

$my_text = 'formation1 <pre>'.print_r($this->formation1,true).'</pre>';
$my_text .= 'formation2 <pre>'.print_r($this->formation2,true).'</pre>';
$my_text .= 'extended2 <pre>'.print_r($this->extended2,true).'</pre>';
$my_text .= 'schemahome <pre>'.print_r($this->schemahome,true).'</pre>';
$my_text .= 'schemaaway <pre>'.print_r($this->schemaaway,true).'</pre>';
$my_text .= 'matchplayerpositions <pre>'.print_r($this->matchplayerpositions,true).'</pre>';
$my_text .= 'matchplayers <pre>'.print_r($this->matchplayers,true).'</pre>';
$my_text .= 'match <pre>'.print_r($this->match,true).'</pre>';
$my_text .= 'overallconfig <pre>'.print_r($this->overallconfig,true).'</pre>';
$my_text .= 'config <pre>'.print_r($this->config,true).'</pre>';
sportsmanagementHelper::setDebugInfoText(__METHOD__,__FUNCTION__,'sportsmanagementViewMatchReportdefault_rosterplayground',__LINE__,$my_text);

}



$favteams1 = explode(",",$this->project->fav_team);
$favteams = array();

for ($a=0; $a < sizeof($favteams1);$a++ )
{
$favteams[$favteams1[$a]] = $favteams1[$a];
}


?>

<div class="flash">
<table align="center" style="width: 100% ;" border="0">
<tr>
<td colspan="5" align="center">
<?php
// diddipoeler schema der mannschaften
$schemahome = '';
$schemaguest = '';

if ( $this->config['roster_playground_use_fav_teams'] )
{
foreach( $favteams as $key => $value )
{

if ( $value == $this->team1->id )
{
$schemahome = $this->formation1;
}
else if ( $value == $this->team2->id )
{
$schemaguest = $this->formation2;
}

}
}
else
{
$schemahome = $this->formation1;
$schemaguest = $this->formation2;
}

//$backgroundimage = 'media/com_sportsmanagement/rosterground/spielfeld_578x1050.png';
$backgroundimage = 'media/com_sportsmanagement/rosterground/'.$this->config['roster_playground_select'];

list($width, $height, $type, $attr) = getimagesize($backgroundimage);
$spielfeldhaelfte = $height / 2;

if ( $schemahome  && $schemaguest )
{
// heim und gast
//echo "<div id=\"heimgast\" style=\"background-image:url('".$backgroundimage."');background-position:left;position:relative;height:".$height."px;width:".$width."px;\">";
echo "<div id=\"heimgast\" style=\"background-position:left;position:relative;height:".$height."px;width:".$width."px;\">";
echo "<img class=\"bild_s\" style=\"width:".$width."px;\" src=\"".$backgroundimage."\" alt=\"\" >";
}
else if ( !$schemahome && $schemaguest )
{
// nur gast
?>
<style>
#gast{
clip:rect(<?PHP echo $spielfeldhaelfte; ?>px <?PHP echo $width; ?>px <?PHP echo $height; ?>px 0px);
height:<?PHP echo $height; ?>px;
width:<?PHP echo $width; ?>px;
top: -<?PHP echo $spielfeldhaelfte; ?>px;
overflow:hidden;
position:relative;
}
</style>
<?PHP
//echo "<div id=\"gast\" style=\"background-image:url('".$backgroundimage."');background-position:left;position:relative;height:".$height."px;width:".$width."px;top:-".$spielfeldhaelfte."px;overflow: hidden;\">";
echo "<div id=\"gast\" >";
echo "<img class=\"bild_s\" style=\"width:".$width."px;\" src=\"".$backgroundimage."\" alt=\"\" >";
}
else if ( $schemahome && !$schemaguest )
{
// nur heim
?>
<style>
#heim {
clip:rect(0px <?PHP echo $width; ?>px <?PHP echo $spielfeldhaelfte; ?>px 0px);
height:<?PHP echo $spielfeldhaelfte; ?>px;
width:<?PHP echo $width; ?>px;

overflow:hidden;
position:relative;
}
</style>
<?PHP
//echo "<div id=\"heim\"  style=\"background-image:url('".$backgroundimage."');background-position:left;position:relative;height:".$height."px;width:".$width."px;overflow: hidden;\">";
echo "<div id=\"heim\" >";
echo "<img class=\"bild_s\" style=\"width:".$width."px;\" src=\"".$backgroundimage."\" alt=\"\" >";
}
else
{
// garnichts angegeben
//echo "<div id=\"nichts\" style=\"background-image:url('".$backgroundimage."');background-position:left;position:relative;height:".$height."px;width:".$width."px;\">";
echo "<div id=\"nichts\" style=\"background-position:left;position:relative;height:".$height."px;width:".$width."px;\">";
echo "<img class=\"bild_s\" style=\"width:".$width."px;\" src=\"".$backgroundimage."\" alt=\"\" >";
}

//echo "<div style=\"background-image:url('".$backgroundimage."');background-position:left;position:relative;height:".$height."px;width:".$width."px;\">";


// positionen aus der rostertabelle benutzen
?>

<table class="taktischeaufstellung" summary="Taktische Aufstellung">
<tr>

</tr>
<tr>
<td>

<?PHP
// die logos
if ( $schemahome )
{
?>
<div style="position:absolute; width:103px; left:0px; top:0px; text-align:center;">



<?PHP
//echo JHtml::image($this->team1_club->logo_big, $this->team1_club->name, array('title' => $this->team1_club->name,'class' => "img-rounded" )); 
echo sportsmanagementHelperHtml::getBootstrapModalImage('rosterplaygroundteamhome',$this->team1_club->logo_big,$this->team1_club->name,'50');     
?>


</div>
<?PHP
}

if ( $schemaguest )
{
    
?>
<div style="position:absolute; width:103px; left:0px; top:950px; text-align:center;">


<?PHP
//echo JHtml::image($this->team2_club->logo_big, $this->team2_club->name, array('title' => $this->team2_club->name,'class' => "img-rounded" ));    
echo sportsmanagementHelperHtml::getBootstrapModalImage('rosterplaygroundteamaway',$this->team2_club->logo_big,$this->team2_club->name,'50');  
?>



</div>
<?PHP
}

if ( $schemahome )
{
// hometeam
$testlauf = 0;
foreach ($this->matchplayerpositions as $pos)
		{
			$personCount=0;
			foreach ($this->matchplayers as $player)
			{
				if ($player->pposid == $pos->pposid)
				{
					$personCount++;
				}
			}

if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
{
echo 'this->heim personCount<br /><pre>~' . print_r($personCount,true) . '~</pre><br />';
}

if ($personCount > 0)
{

foreach ($this->matchplayers as $player)
{

if ( $player->pposid == $pos->pposid && $player->ptid == $this->match->projectteam1_id )
{
// player->ppic = person picture
// player->picture = teamplay picture
$picture = $player->picture;
if ( !curl_init( $picture ) )
{
$picture = $player->ppic;    
if ( !curl_init( $picture ) )
{
$picture = sportsmanagementHelper::getDefaultPlaceholder("player");
}
}

if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
{
echo 'this->heim person_id<br /> ~' . $player->person_id . ' ~<br />';
echo 'this->heim lastname<br /> ~' . $player->lastname . ' ~<br />';
echo 'this->heim firstname<br /> ~' . $player->firstname . ' ~<br />';
echo 'this->heim picture<br /> ~' . $picture . ' ~<br />';
}


?>

<div id="<?php echo $player->person_id;?>" style="display:<?php echo $div_display;?>;position:absolute; width:103px; left:<?PHP echo $this->schemahome[$schemahome][$testlauf]['heim']['links']; ?>px; top:<?PHP echo $this->schemahome[$schemahome][$testlauf]['heim']['oben']; ?>px; text-align:center;">





<?PHP
echo sportsmanagementHelperHtml::getBootstrapModalImage('rosterplaygroundperson'.$player->person_id,$picture,$player->lastname,$this->config['roster_playground_player_picture_width']);     
?>
 


<a class="link" href=""><font color="white"><?PHP echo $player->lastname." "; ?></font></a>
</div>
                                      
<?PHP
$testlauf++;
}

}

}

}
}

if ( $schemaguest )
{
// guestteam
$testlauf = 0;
foreach ($this->matchplayerpositions as $pos)
		{
			$personCount=0;
			foreach ($this->matchplayers as $player)
			{
				if ($player->pposid == $pos->pposid)
				{
					$personCount++;
				}
			}

if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
{
echo 'this->gast personCount<br /><pre>~' . print_r($personCount,true) . '~</pre><br />';
}

if ($personCount > 0)
{			

foreach ($this->matchplayers as $player)
{

if ( $player->pposid == $pos->pposid && $player->ptid == $this->match->projectteam2_id )
{
// player->ppic = person picture
// player->picture = teamplay picture
$picture = $player->picture;
if ( !curl_init( $picture ) )
{
$picture = $player->ppic;    
if ( !curl_init( $picture ) )
{    
$picture = sportsmanagementHelper::getDefaultPlaceholder("player");
}
}

?>

<div id="<?php echo $player->person_id;?>" style="display:<?php echo $div_display;?>;position:absolute; width:103px; left:<?PHP echo $this->schemaaway[$schemaguest][$testlauf]['gast']['links']; ?>px; top:<?PHP echo $this->schemaaway[$schemaguest][$testlauf]['gast']['oben']; ?>px; text-align:center;">

<?PHP
//echo JHtml::image($picture, $player->lastname, array('title' => $player->lastname,'class' => "img-rounded" ));  
echo sportsmanagementHelperHtml::getBootstrapModalImage('rosterplaygroundperson'.$player->person_id,$picture,$player->lastname,$this->config['roster_playground_player_picture_width']);    
?>

<a class="link" href=""><font color="white"><?PHP echo $player->lastname." "; ?></font></a>
</div>
                                      
<?PHP
$testlauf++;
}

}

}

}	

}
?>

</td>
</tr>
</table>

<?PHP 

                            
echo "</div>";



?>
</td>
</tr>
</table>
</div>
