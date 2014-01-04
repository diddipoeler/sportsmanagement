<?php 
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

if ( $this->show_debug_info )
{
echo 'this->formation1<br /><pre>~' . print_r($this->formation1,true) . '~</pre><br />';
echo 'this->formation2<br /><pre>~' . print_r($this->formation2,true) . '~</pre><br />';
echo 'this->extended2<br /><pre>~' . print_r($this->extended2,true) . '~</pre><br />';

echo 'this->schemahome<br /><pre>~' . print_r($this->schemahome,true) . '~</pre><br />';
echo 'this->schemaaway<br /><pre>~' . print_r($this->schemaaway,true) . '~</pre><br />';

echo 'this->matchplayerpositions<br /><pre>~' . print_r($this->matchplayerpositions,true) . '~</pre><br />';
echo 'this->matchplayers<br /><pre>~' . print_r($this->matchplayers,true) . '~</pre><br />';
echo 'this->match<br /><pre>~' . print_r($this->match,true) . '~</pre><br />';

echo 'this->overallconfig<br /><pre>~' . print_r($this->overallconfig,true) . '~</pre><br />';
echo 'this->config<br /><pre>~' . print_r($this->config,true) . '~</pre><br />';
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

//$backgroundimage = 'media/com_joomleague/rosterground/spielfeld_578x1050.png';
$backgroundimage = 'media/com_joomleague/rosterground/'.$this->config['roster_playground_select'];

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
<a href="<?php echo $this->team1_club->logo_big;?>" alt="<?php echo $this->team1_club->name;?>" title="<?php echo $this->team1_club->name;?>" class="highslide" onclick="return hs.expand(this)">
<img class="bild_s" style="width:<?PHP echo $this->config['roster_playground_team_picture_width']; ?>px;" src="<?PHP echo $this->team1_club->logo_big; ?>" alt="" /><br />
</a>
</div>
<?PHP
}

if ( $schemaguest )
{
?>
<div style="position:absolute; width:103px; left:0px; top:950px; text-align:center;">
<a href="<?php echo $this->team2_club->logo_big;?>" alt="<?php echo $this->team2_club->name;?>" title="<?php echo $this->team2_club->name;?>" class="highslide" onclick="return hs.expand(this)">
<img class="bild_s" style="width:<?PHP echo $this->config['roster_playground_team_picture_width']; ?>px;" src="<?PHP echo $this->team2_club->logo_big; ?>" alt="" /><br />
</a>
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

if ( $this->show_debug_info )
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
if ( !file_exists( $picture ) )
{
$picture = $player->ppic;    
if ( !file_exists( $picture ) )
{
$picture = JoomleagueHelper::getDefaultPlaceholder("player");
}
}

if ( $this->show_debug_info )
{
echo 'this->heim person_id<br /> ~' . $player->person_id . ' ~<br />';
echo 'this->heim lastname<br /> ~' . $player->lastname . ' ~<br />';
echo 'this->heim firstname<br /> ~' . $player->firstname . ' ~<br />';
echo 'this->heim picture<br /> ~' . $picture . ' ~<br />';
}

?>

<div id="<?php echo $player->person_id;?>" style="display:<?php echo $div_display;?>;position:absolute; width:103px; left:<?PHP echo $this->schemahome[$schemahome][$testlauf]['heim']['links']; ?>px; top:<?PHP echo $this->schemahome[$schemahome][$testlauf]['heim']['oben']; ?>px; text-align:center;">
<a href="<?php echo $picture;?>" alt="<?php echo $player->lastname;?>" title="<?php echo $player->lastname;?>" class="highslide" onclick="return hs.expand(this)">
<img id="<?php echo $player->person_id;?>" class="bild_s" style="width:<?PHP echo $this->config['roster_playground_player_picture_width']; ?>px; " src="<?PHP echo $picture; ?>" alt="" /><br />
</a>
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

if ( $this->show_debug_info )
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
if ( !file_exists( $picture ) )
{
$picture = $player->ppic;    
if ( !file_exists( $picture ) )
{    
$picture = JoomleagueHelper::getDefaultPlaceholder("player");
}
}

?>

<div id="<?php echo $player->person_id;?>" style="display:<?php echo $div_display;?>;position:absolute; width:103px; left:<?PHP echo $this->schemaaway[$schemaguest][$testlauf]['gast']['links']; ?>px; top:<?PHP echo $this->schemaaway[$schemaguest][$testlauf]['gast']['oben']; ?>px; text-align:center;">
<a href="<?php echo $picture;?>" alt="<?php echo $player->lastname;?>" title="<?php echo $player->lastname;?>" class="highslide" onclick="return hs.expand(this)">
<img id="<?php echo $player->person_id;?>" class="bild_s" style="width:<?PHP echo $this->config['roster_playground_player_picture_width']; ?>px;" src="<?PHP echo $picture; ?>" alt="" /><br />
</a>
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

/*
echo 'this->matchplayerpositions<br /><pre>~' . print_r($this->matchplayerpositions,true) . '~</pre><br />';
echo 'this->personCount<br /><pre>~' . print_r($personCount,true) . '~</pre><br />';
echo 'this->matchplayers<br /><pre>~' . print_r($this->matchplayers,true) . '~</pre><br />';
*/

?>
</td>
</tr>
</table>
</div>