<?php defined( '_JEXEC' ) or die( 'Restricted access' );

JHtml::_( 'behavior.tooltip' );

// Set toolbar items for the page
JToolbarHelper::title( JText::_( JText::_( 'DFB-Keys Mass-Add' ) ) );

$edit        = JFactory::getApplication()->input->getVar('edit',true);
$text = !$edit ? JText::_( 'New' ) : JText::_( 'Edit' );

JToolbarHelper::save();

if (!$edit)
{
    JToolbarHelper::cancel();
}
else
{
    // for existing items the button is renamed `close` and the apply button is showed
    JToolbarHelper::apply();
    JToolbarHelper::cancel( 'cancel', 'Close' );
}
JToolbarHelper::help( 'screen.joomleague', true );

$uri     =& JFactory::getURI();

DEFINE('_COM_SPORTSMANAGEMENT_ADMIN_EDIT_LIST_DFBKEY_HINT1','Nachdem die Mannschaften dem Projekt zugeordnet wurden, k�nnen die Schl�sselzahlen vergeben werden. Im Augenblick d�rfen noch keine Spielpaarungen pro Spieltag vorhanden sein, sonst kommt es zu doppelten
Eintr�gen !');

DEFINE('_COM_SPORTSMANAGEMENT_ADMIN_EDIT_LIST_DFBKEY_HINT11','1-L hei�t der neue Spielschl�ssel, der seit dem 15.Mai 2003 in der Spielansetzungssoftware des DFBnet hinterlegt ist und dort f�r die Spielplanung bereit steht. Der Ansetzungsschl�ssel ist so aufgebaut, dass alle Staffelgr��en harmonisch zueinander passen.
Der neue Ansetzungsschl�ssel ber�cksichtigt ebenfalls die Anforderung, dass unterschiedliche Staffelgr��en den letzten Spieltag gemeinsam haben. Die Synchronisation erfolgt �ber den Rahmenplan wobei der Synchronisationspunkt der 1. Schl�sseltag ist, der den Letzen Spieltag bildet (1-L ). Im Folgenden k�nnen Sie sich den Spielschl�ssel und eine detaillierte Beschreibung �ber dessen Eigenschaften ansehen. ');

DEFINE('_COM_SPORTSMANAGEMENT_ADMIN_EDIT_LIST_DFBKEY_HINT2','Hier werden die Datumseingaben f�r die Spieltage verarbeitet.');

DEFINE('_COM_SPORTSMANAGEMENT_ADMIN_EDIT_LIST_DFBKEY_HINT3','Hier werden die Spielpaarungen des ersten Spieltages zugeordnet.');
DEFINE('_COM_SPORTSMANAGEMENT_ADMIN_EDIT_LIST_DFBKEY_HINT4','<b>Bitte hier im Augenblick keine �nderungen vornehmen!</b>.');

?>
<link rel="stylesheet" type="text/css" media="all" href="<?php echo $mosConfig_live_site;?>/includes/js/calendar/calendar-mos.css" title="green" />
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:10000;"></div>


<script language="javascript" type="text/javascript" src="<?=$mosConfig_live_site;?>/includes/js/overlib_mini.js"></script>

<!-- import the calendar script -->
<script language="javascript" type="text/javascript" src="<?php echo $mosConfig_live_site;?>/includes/js/calendar/calendar_mini.js"></script>
<!-- import the language module -->
<script language="javascript" type="text/javascript" src="<?php echo $mosConfig_live_site;?>/includes/js/calendar/lang/calendar-en.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo $mosConfig_live_site;?>/includes/js/overlib_mini.js"></script>



<form action="index2.php" method="post" name="adminForm">
<div id="right">
<div id="rightpad">

<!-- Header START -->
<div id="step">
<div class="t">
<div class="t">
<div class="t"></div>
</div>
</div>
<div class="m" align="left">
<div class="far-right">
<div class="button1-left"><div class="help"><a href="http://www.joomleague.de/handbuch.html" alt="<?php echo _COM_SPORTSMANAGEMENT_ADMIN_MANUAL;?>" title="<?php echo _COM_SPORTSMANAGEMENT_ADMIN_MANUAL;?>" target="blank"><?php echo _COM_SPORTSMANAGEMENT_ADMIN_MANUAL;?></a></div></div>
<div class="button1-left"><div class="forum"><a href="http://www.joomleague.de/forum/" alt="<?php echo _COM_SPORTSMANAGEMENT_ADMIN_FORUM;?>" title="<?php echo _COM_SPORTSMANAGEMENT_ADMIN_FORUM;?>" target="blank"><?php echo _COM_SPORTSMANAGEMENT_ADMIN_FORUM;?></a></div></div>
<div class="button1-left"><div class="about"><a href="http://www.joomleague.de" alt="<?php echo _COM_SPORTSMANAGEMENT_ADMIN_INFO;?>" title="<?php echo _COM_SPORTSMANAGEMENT_ADMIN_INFO;?>" target="blank"><?php echo _COM_SPORTSMANAGEMENT_ADMIN_INFO;?></a></div></div>
</div>
<div class="button1-left"><div class="blank"><a href="index2.php?option=com_joomleague"></a></div></div><span class="step"><?php echo $project->name;?>

<?php
//echo _COM_SPORTSMANAGEMENT_ADMIN_EDIT_LIST_projectteam_LIST_projectteam; COM_SPORTSMANAGEMENT_ADMIN_EDIT_LIST_projectteam_LIST_projectteam= - Teams
echo "DFB-Schl�ssel";
?>


</span>
</div>
<div class="b">
<div class="b">
<div class="b"></div>
</div>
</div>
</div>
<!-- Header END -->

<div id="installer">

<!-- Title START -->
<div class="t">
<div class="t">
<div class="t"></div>
</div>
</div>
<div class="m" align="left">
<h2>

<?php
echo "DFB-Schl�ssel";
?>


</h2>
<!-- Titel END -->

<!-- Info START -->
<div id="step">
<div class="t">
<div class="t">
<div class="t"></div>
</div>
</div>
<div class="m" align="left">
<table class="content" cellpadding="4">
<tr>

<td rowspan="6" >
<img align="top" src="components/com_joomleague/images/1L.gif" alt="Schl�ssel" title"Schl�ssel">
</td>

<td><?php echo _COM_SPORTSMANAGEMENT_ADMIN_EDIT_LIST_DFBKEY_HINT1;?>
</td>
</tr>

<tr>
<td><?php echo _COM_SPORTSMANAGEMENT_ADMIN_EDIT_LIST_DFBKEY_HINT11;?>
</td>
</tr>

<tr>
<td>
<a href="http://www.dfbnet.org/downloads/Eigenschaften_L1-Spielschluessel.pdf" target="NEW" >Beschreibung</a>
</td>
</tr>

<tr>
<td>
<a href="http://www.dfbnet.org/downloads/SZ_1-L.pdf" target="NEW" >Spielschl�ssel</a>
</td>
</tr>

<tr>
<td>
<a href="http://www.dfbnet.org/downloads/SZ_1-LGegenueberstellung.pdf" target="NEW" >Gegen�berstellung Schl�sseltage, Spieltage und Tage im Rahmenplan</a>
</td>
</tr>

<tr>
<td>
<a href="http://www.fussballineuropa.de" target="NEW" >Copyright (C) 2007 Dieter Pl�ger</a>
</td>
</tr>

</table>
</div>


<div class="b">
<div class="b">
<div class="b"></div>
</div>
</div>
</div>
<!-- Info END -->

<!-- Content START -->
<div class="install-text2">
<table cellpadding="4" cellspacing="0" border="0" width="100%" class="adminlist2">
<?php

$k = 0;

/**
* echo "<pre>";
* print_r($_POST);
* echo "</pre>";
*/

$gesendet = mosGetParam( $_POST, 'gesendet', 0 ) ;


/**
* echo "projekt ->".$project->id."<br>";
*/


$database->setQuery( "SELECT jtj.team_id as id, jt.name as name FROM #__joomleague_project_team as jtj,
#__joomleague_team as jt where jtj.project_id = $project->id and jtj.team_id = jt.id order by jt.name");

//$database->setQuery( "SELECT jtj.team_id as id FROM #__joomleague_project_team as jtj where project_id = $project->id ");

$rows = $database->loadObjectList();

$anzmannschaften = count( $rows );

if ($anzmannschaften % 2 != 0) {
$dfbschluessel = $anzmannschaften + 1;
// echo "Der Wert der Variablen \$zahl ist ungerade";
} else {
// echo "Der Wert der Variablen \$zahl ist gerade";
$dfbschluessel = $anzmannschaften;
}


/**
* echo "wir haben -> ".$dfbschluessel." mannschaften <br>";

* foreach ($rows AS $row)
* {
* echo "nummer -> ". $row->id ." name -> ". $row->name ."<br>";
* //echo "nummer -> ". $row->id ."<br>";
* }
*/


// #############################################################################
if ( $gesendet == 0 )
{

?>

<!-- Info START -->
</tr>
</table>
</div>
<tr>
<td>
<div id="step">
<div class="t">
<div class="t">
<div class="t"></div>
</div>
</div>

<div class="m" align="left">

<table class="content" cellpadding="">
<tr>
<td><?php echo _COM_SPORTSMANAGEMENT_ADMIN_EDIT_LIST_DFBKEY_HINT2;?>
</td>
</tr>
</table>

</div>
<div class="b">
<div class="b">
<div class="b"></div>
</div>
</div>
</div>
</td>
</tr>
<!-- Info END -->


<?PHP
// zum schl�ssel alle spieltage holen
$database->setQuery( "SELECT spieltag FROM #__joomleague_dfb where schluessel = '$dfbschluessel'
group by spieltag order by spieltag ");
$rowsdfb = $database->loadObjectList();


echo "<table class=\"adminlist2\">" ;

?>
<thead>
<tr>
<th class="title" width="" nowrap="nowrap"><?php echo "";?></th>
<th class="title" width="" nowrap="nowrap"><?php echo _SPIELTAG;?></th>
<th class="title" width="" nowrap="nowrap"><?php echo _SPIELTAG_NAME;?></th>
<th class="title" width="" nowrap="nowrap"><?php echo _SPIELTAG_VON;?></th>
<th class="title" width="" nowrap="nowrap"><?php echo _SPIELTAG_BIS;?></th>
<th class="title" width="" nowrap="nowrap"><?php echo _ANSTOSSZEIT;?></th>
</tr>
</thead>

<?PHP


foreach($rowsdfb as $rowdfb)
{
echo "<tr>";

// gibt es den spieltag schon
$database->setQuery( "SELECT id as roundid FROM #__joomleague_round WHERE
roundcode = '$rowdfb->spieltag' and project_id = $project->id ");

$spieltagid = $database->loadResult();

if ( $spieltagid == 0 )
{
$row->round_date_first = "0000-00-00";
$row->round_date_last = "0000-00-00" ;
// spieltag nich da
?>
<td><input type="hidden" name="roundid_<?php echo $rowdfb->spieltag; ?>" value="0" /></td>
<?PHP
}
else
{
// spieltag vorhanden
//$row = $database->loadObjectList();
$row = new Round( $database );
$row->load($spieltagid);
?>
<td><input type="hidden" name="roundid_<?php echo $rowdfb->spieltag; ?>" value="<?php echo $row->id; ?>" /></td>
<?PHP

if ( $row->round_date_last == "0000-00-00" )
{
$row->round_date_last = $row->round_date_first;
}

}

?>

<td><input type="" name="roundcode_<?php echo $rowdfb->spieltag; ?>" value="<?php echo $rowdfb->spieltag; ?>" /></td>
<td><input type="" name="name_<?php echo $rowdfb->spieltag; ?>" value="<?php echo $rowdfb->spieltag.". Spieltag"; ?>" /></td>


<td>
<input class="inputbox" type="text" name="rounddatefirst_<?php echo $rowdfb->spieltag; ?>" id="rounddatefirst_<?php echo $rowdfb->spieltag; ?>" size="20" maxlength="20" value="<?php echo $row->round_date_first;?>" /><input type="reset" class="button" value="..." onclick="return showCalendar('rounddatefirst_<?php echo $rowdfb->spieltag; ?>', 'y-mm-dd');" />
</td>

<td>
<input class="inputbox" type="text" name="rounddatelast_<?php echo $rowdfb->spieltag; ?>" id="rounddatelast_<?php echo $rowdfb->spieltag; ?>" size="20" maxlength="20" value="<?php echo $row->round_date_last;?>" /><input type="reset" class="button" value="..." onclick="return showCalendar('rounddatelast_<?php echo $rowdfb->spieltag; ?>', 'y-mm-dd');" />
</td>

<?PHP
// anstosszeit


echo "<td><select name=\"anstoss_$rowdfb->spieltag\" >\n";
echo "<option value='00:00:00'>00:00:00:</option>\n";
echo "<option value='00:15:00'>00:15:00:</option>\n";
echo "<option value='00:30:00'>00:30:00:</option>\n";
echo "<option value='00:45:00'>00:45:00:</option>\n";
echo "<option value='01:00:00'>01:00:00:</option>\n";
echo "<option value='01:15:00'>01:15:00:</option>\n";
echo "<option value='01:30:00'>01:30:00:</option>\n";
echo "<option value='01:45:00'>01:45:00:</option>\n";
echo "<option value='02:00:00'>02:00:00:</option>\n";
echo "<option value='02:15:00'>02:15:00:</option>\n";
echo "<option value='02:30:00'>02:30:00:</option>\n";
echo "<option value='02:45:00'>02:45:00:</option>\n";
echo "<option value='03:00:00'>03:00:00:</option>\n";
echo "<option value='03:15:00'>03:15:00:</option>\n";
echo "<option value='03:30:00'>03:30:00:</option>\n";
echo "<option value='03:45:00'>03:45:00:</option>\n";
echo "<option value='04:00:00'>04:00:00:</option>\n";
echo "<option value='04:15:00'>04:15:00:</option>\n";
echo "<option value='04:30:00'>04:30:00:</option>\n";
echo "<option value='04:45:00'>04:45:00:</option>\n";
echo "<option value='05:00:00'>05:00:00:</option>\n";
echo "<option value='05:15:00'>05:15:00:</option>\n";
echo "<option value='05:30:00'>05:30:00:</option>\n";
echo "<option value='05:45:00'>05:45:00:</option>\n";
echo "<option value='06:00:00'>06:00:00:</option>\n";
echo "<option value='06:15:00'>06:15:00:</option>\n";
echo "<option value='06:30:00'>06:30:00:</option>\n";
echo "<option value='06:45:00'>06:45:00:</option>\n";
echo "<option value='07:00:00'>07:00:00:</option>\n";
echo "<option value='07:15:00'>07:15:00:</option>\n";
echo "<option value='07:30:00'>07:30:00:</option>\n";
echo "<option value='07:45:00'>07:45:00:</option>\n";
echo "<option value='08:00:00'>08:00:00:</option>\n";
echo "<option value='08:15:00'>08:15:00:</option>\n";
echo "<option value='08:30:00'>08:30:00:</option>\n";
echo "<option value='08:45:00'>08:45:00:</option>\n";
echo "<option value='09:00:00'>09:00:00:</option>\n";
echo "<option value='09:15:00'>09:15:00:</option>\n";
echo "<option value='09:30:00'>09:30:00:</option>\n";
echo "<option value='09:45:00'>09:45:00:</option>\n";
echo "<option value='10:00:00'>10:00:00:</option>\n";
echo "<option value='10:15:00'>10:15:00:</option>\n";
echo "<option value='10:30:00'>10:30:00:</option>\n";
echo "<option value='10:45:00'>10:45:00:</option>\n";
echo "<option value='11:00:00'>11:00:00:</option>\n";
echo "<option value='11:15:00'>11:15:00:</option>\n";
echo "<option value='11:30:00'>11:30:00:</option>\n";
echo "<option value='11:45:00'>11:45:00:</option>\n";
echo "<option value='12:00:00'>12:00:00:</option>\n";
echo "<option value='12:15:00'>12:15:00:</option>\n";
echo "<option value='12:30:00'>12:30:00:</option>\n";
echo "<option value='12:45:00'>12:45:00:</option>\n";
echo "<option value='13:00:00'>13:00:00:</option>\n";
echo "<option value='13:15:00'>13:15:00:</option>\n";
echo "<option value='13:30:00'>13:30:00:</option>\n";
echo "<option value='13:45:00'>13:45:00:</option>\n";
echo "<option value='14:00:00'>14:00:00:</option>\n";
echo "<option value='14:15:00'>14:15:00:</option>\n";
echo "<option value='14:30:00'>14:30:00:</option>\n";
echo "<option value='14:45:00'>14:45:00:</option>\n";
echo "<option value='15:00:00'>15:00:00:</option>\n";
echo "<option value='15:15:00'>15:15:00:</option>\n";
echo "<option value='15:30:00'>15:30:00:</option>\n";
echo "<option value='15:45:00'>15:45:00:</option>\n";
echo "<option value='16:00:00'>16:00:00:</option>\n";
echo "<option value='16:15:00'>16:15:00:</option>\n";
echo "<option value='16:30:00'>16:30:00:</option>\n";
echo "<option value='16:45:00'>16:45:00:</option>\n";
echo "<option value='17:00:00'>17:00:00:</option>\n";
echo "<option value='17:15:00'>17:15:00:</option>\n";
echo "<option value='17:30:00'>17:30:00:</option>\n";
echo "<option value='17:45:00'>17:45:00:</option>\n";
echo "<option value='18:00:00'>18:00:00:</option>\n";
echo "<option value='18:15:00'>18:15:00:</option>\n";
echo "<option value='18:30:00'>18:30:00:</option>\n";
echo "<option value='18:45:00'>18:45:00:</option>\n";
echo "<option value='19:00:00'>19:00:00:</option>\n";
echo "<option value='19:15:00'>19:15:00:</option>\n";
echo "<option value='19:30:00'>19:30:00:</option>\n";
echo "<option value='19:45:00'>19:45:00:</option>\n";
echo "<option value='20:00:00'>20:00:00:</option>\n";
echo "<option value='20:15:00'>20:15:00:</option>\n";
echo "<option value='20:30:00'>20:30:00:</option>\n";
echo "<option value='20:45:00'>20:45:00:</option>\n";
echo "<option value='21:00:00'>21:00:00:</option>\n";
echo "<option value='21:15:00'>21:15:00:</option>\n";
echo "<option value='21:30:00'>21:30:00:</option>\n";
echo "<option value='21:45:00'>21:45:00:</option>\n";
echo "<option value='22:00:00'>22:00:00:</option>\n";
echo "<option value='22:15:00'>22:15:00:</option>\n";
echo "<option value='22:30:00'>22:30:00:</option>\n";
echo "<option value='22:45:00'>22:45:00:</option>\n";
echo "<option value='23:00:00'>23:00:00:</option>\n";
echo "<option value='23:15:00'>23:15:00:</option>\n";
echo "<option value='23:30:00'>23:30:00:</option>\n";
echo "<option value='23:45:00'>23:45:00:</option>\n";


echo "</select></td>";


echo "</tr>";
}
echo "</table>" ;


?>

<!-- Info START -->
<tr>
<td>
<div id="step">
<div class="t">
<div class="t">
<div class="t"></div>
</div>
</div>

<div class="m" align="left">

<table class="content" cellpadding="">
<tr>
<td><?php echo _COM_SPORTSMANAGEMENT_ADMIN_EDIT_LIST_DFBKEY_HINT3;?>
</td>
</tr>
</table>

</div>
<div class="b">
<div class="b">
<div class="b"></div>
</div>
</div>
</div>
</td>
</tr>
<!-- Info END -->


<?PHP

// zum schl�ssel den ersten dfb spieltag holen
$database->setQuery( "SELECT * FROM #__joomleague_dfb where schluessel = '$dfbschluessel' and spieltag = '1'
order by spielnummer");
$rowsdfb = $database->loadObjectList();

// gibt es den ersten spieltag schon im project ?
$database->setQuery( "SELECT jr.id FROM #__joomleague_match as jm,
#__joomleague_round as jr
where jm.project_id = $project->id
and jm.project_id = jr.project_id
and jr.roundcode = '1' ");

$schonda = $database->loadResult();

if ( $schonda <> 0 )
{
echo "1 Spieltag schon vorhanden $schonda<br>"    ;
$database->setQuery( "SELECT match_id,team1,team2,match_number
FROM #__joomleague_match
where round_id = $schonda
 ");

$rowspaar = $database->loadObjectList();

/**
* // mannschaften zum spieltag
* foreach ($rowspaar AS $rowpaar)
* {
* echo "rowpaar->team1 ".$rowpaar->team1." rowpaar->team2 ".$rowpaar->team2." rowpaar->match_number ".$rowpaar->match_number."<br>" ;
* }
*/

/**
* // vorhandene mannschaften
* foreach ($rows AS $row)
* {
* echo "row->id ".$row->id." row->name ".$row->name."<br>" ;
* }
*/

}
else
{
echo "1 Spieltag nicht vorhanden <br>"    ;
}


echo "<table class=\"adminlist2\">" ;
echo "<thead>";
echo "<tr><th class=\"title\">Schl�ssel</th><th>Spieltag</th><th>HeimNR</th><th>Verein</th><th>GastNr</th><th>Verein</th><th>Spielnummer</th></tr>";
echo "</thead>";

foreach($rowsdfb as $rowdfb) {
echo "<tr><td>".$rowdfb->schluessel."</td>";
echo "<td>".$rowdfb->spieltag."</td>";
//echo $rowligen->paarung;
$teile = explode(",", $rowdfb->paarung);
echo "<td>".$teile[0]."</td>"; // Teil1

$laenge = $dfbschluessel + 1;

$string_zahl = sprintf ( "%03d",  is_int($rowdfb->spielnummer) ? $rowdfb->spielnummer : $rowdfb->spielnummer*1) ;
$a = $string_zahl;

echo "<td><select name=\"chooseteam_$teile[0]\" size=\"$laenge\">\n";
echo "<option value='0' >SPIELFREI</option>\n";

foreach ($rows AS $row)
{
echo "<option ";

if ( $schonda <> 0 )
{
// wenn es die heim-mannschaft schon gibt selektieren
foreach ($rowspaar AS $rowpaar)
{
//echo "row->id ".$row->id." rowpaar->team1 ".$rowpaar->team1."<br>" ;
if ( $row->id == $rowpaar->team1 && $a == $rowpaar->match_number )
{
echo "selected ";
}
}
}

echo "value='". $row->id ."'>". $row->name ."</option>\n";
}

echo "</select></td>";

echo "<td>".$teile[1]."</td>"; // Teil2
echo "<td><select name=\"chooseteam_$teile[1]\" size=\"$laenge\" >\n";
echo "<option value='0'>SPIELFREI</option>\n";

foreach ($rows AS $row)
{
echo "<option ";

if ( $schonda <> 0 )
{
// wenn es die gast-mannschaft schon gibt selektieren
foreach ($rowspaar AS $rowpaar)
{
if ( $row->id == $rowpaar->team2 && $a == $rowpaar->match_number )
{
echo "selected ";
}
}
}

echo "value='". $row->id ."'>". $row->name ."</option>\n";
}

echo "</select></td>";


echo "<td>".$rowdfb->spielnummer."</td></tr>";


}
echo "</table>" ;

// #############################################################################
?>
</tr>
</table>
</div>

<div class="clr"></div>
</div>
<div class="b">
<div class="b">
<div class="b"></div>
</div>
</div>
</div>
</div>
</div>
<div class="clr"></div>
<tr><td><input type="hidden" name="id" value="<?php echo $row->id;?>" /></td></tr>
<tr><td><input type="hidden" name="option" value="<?php echo $option; ?>" /></td></tr>
<tr><td><input type="hidden" name="act" value="<?php echo $act; ?>" /></td></tr>
<tr><td><input type="hidden" name="task" value="" /></td></tr>
<tr><td><input type="hidden" name="boxchecked" value="0" /></td></tr>
<tr><td><input type="hidden" name="gesendet" value="1" /></td></tr>

<tr><td colspan="2"><input type="submit" value="<? echo _SPIELPLAN_ERSTELLEN;?>"></td></tr>
</div>
</table>

<?PHP
}




if ( $gesendet == 1 )
{

/**
* echo "<pre>";
* print_r($_POST);
* echo "</pre>";
*/

?>
<!-- Info START -->
</tr>
</table>
</div>
<tr>
<td >
<div id="step">
<div class="t">
<div class="t">
<div class="t"></div>
</div>
</div>

<div class="m" align="left">

<table class="content" cellpadding="">
<tr>
<td><?php echo _COM_SPORTSMANAGEMENT_ADMIN_EDIT_LIST_DFBKEY_HINT4;?>
</td>
</tr>
</table>

</div>
<div class="b">
<div class="b">
<div class="b"></div>
</div>
</div>
</div>
</td>
</tr>
<!-- Info END -->

<?PHP

$lfdnummer = 1;
foreach($_POST as $key => $element)
{
if (substr($key,0,10)=="chooseteam")
{
$tempteams=explode ("_",$key);
$chooseteam[$tempteams[1]]=$element;
/**
* echo $element."<br>";
* echo $chooseteam[$lfdnummer]."<br>";
*/
// $assignedteams[$tempteams[1]]=$element;
$lfdnummer++;
}

}




foreach($_POST as $key => $element)
{
if (substr($key,0,7)=="roundid")
{
$temp=explode ("_",$key);
$tempspieltage[0][$temp[1]]["id"] = $element;
}


if (substr($key,0,7)=="anstoss")
{
$temp=explode ("_",$key);
$tempspieltage[0][$temp[1]]["anstoss"] = $element;
}


if (substr($key,0,9)=="roundcode")
{
$temp=explode ("_",$key);
$tempspieltage[0][$temp[1]]["roundcode"] = $element;
}

if (substr($key,0,4)=="name")
{
$temp=explode ("_",$key);
$tempspieltage[0][$temp[1]]["name"] = $element;
}

if (substr($key,0,14)=="rounddatefirst")
{
$temp=explode ("_",$key);
$tempspieltage[0][$temp[1]]["round_date_first"] = $element;
}

if (substr($key,0,13)=="rounddatelast")
{
$temp=explode ("_",$key);
$tempspieltage[0][$temp[1]]["round_date_last"] = $element;
}

}


for ($a=1; $a <= count($tempspieltage[0]);$a++)
{

if ( $tempspieltage[0][$a]["round_date_last"] == "0000-00-00" || empty($tempspieltage[0][$a]["round_date_last"]) )
{
$tempspieltage[0][$a]["round_date_last"] = $tempspieltage[0][$a]["round_date_first"];
}

/**
* echo $tempspieltage[0][$a]["id"].$tempspieltage[0][$a]["roundcode"].$tempspieltage[0][$a]["name"].$tempspieltage[0][$a]["round_date_first"].$tempspieltage[0][$a]["round_date_last"]."<br>";
*/

$row = new Round( $database );
$row->load( $tempspieltage[0][$a]["id"] );
$row->roundcode = $tempspieltage[0][$a]["roundcode"];
$row->name = $tempspieltage[0][$a]["name"];
$row->round_date_first = $tempspieltage[0][$a]["round_date_first"];
$row->round_date_last = $tempspieltage[0][$a]["round_date_last"];

$row->project_id = $project->id;

$row->store();

}
//echo print_r($tempspieltage);


// alle spieltage selektieren
$database->setQuery( "SELECT * FROM #__joomleague_dfb where schluessel = '$dfbschluessel'
order by spielnummer");

$rowsligen = $database->loadObjectList();

echo "<table class=\"adminlist2\">" ;
echo "<thead>";
//echo "<tr><th>Schl�ssel</th><th>Spieltag</th><th>HeimNR</th><th>HeimID</th><th>HeimVerein</th><th>GastNr</th><th>GastID</th><th>Verein</th><th>Spielnummer</th><th>Datum</th><th>Uhrzeit</th></tr>";
echo "<tr><th class=\"title\">Schl�ssel</th><th>Spieltag</th><th>HeimNR</th><th></th><th>HeimVerein</th><th>GastNr</th><th></th><th>Verein</th><th>Spielnummer</th><th>Datum</th><th>Uhrzeit</th></tr>";
echo "</thead>";

foreach($rowsligen as $rowligen) {

echo "<tr><td>".$rowligen->schluessel."</td>";
?>
<td><input type="" name="spieltag_<?php echo $rowligen->spielnummer; ?>" value="<?php echo $rowligen->spieltag; ?>" /></td>
<?PHP
//echo "<td>".$rowligen->spieltag."</td>";
$teile = explode(",", $rowligen->paarung);
echo "<td>".$teile[0]."</td>"; // Teil1

//echo "<td>".$chooseteam[$teile[0]]."</td>";
?>
<td><input type="hidden" name="homeid_<?php echo $rowligen->spielnummer; ?>" value="<?php echo $chooseteam[$teile[0]]; ?>" /></td>
<?PHP

$heimnummer = $chooseteam[$teile[0]];
$database->setQuery( "SELECT name FROM #__joomleague_team WHERE
id='$heimnummer' ");

$heim = $database->loadResult();

echo "<td>".$heim."</td>";

echo "<td>".$teile[1]."</td>"; // Teil1

//echo "<td>".$chooseteam[$teile[1]]."</td>";
?>
<td><input type="hidden" name="awayid_<?php echo $rowligen->spielnummer; ?>" value="<?php echo $chooseteam[$teile[1]]; ?>" /></td>
<?PHP


$gastnummer = $chooseteam[$teile[1]];
$database->setQuery( "SELECT name FROM #__joomleague_team WHERE
id='$gastnummer' ");

$gast = $database->loadResult();

echo "<td>".$gast."</td>";

//echo "<td>".$rowligen->spielnummer."</td></tr>";

?>
<td><input type="" name="spielnummer_<?php echo $rowligen->spielnummer; ?>" value="<?php echo $rowligen->spielnummer; ?>" /></td>
<td><input type="" name="spieldatum_<?php echo $rowligen->spielnummer; ?>" value="<?php echo $tempspieltage[0][$rowligen->spieltag]["round_date_first"]; ?>" /></td>

<td><input type="" name="uhrzeit_<?php echo $rowligen->spielnummer; ?>" value="<?php echo $tempspieltage[0][$rowligen->spieltag]["anstoss"]; ?>"/></td>
<?PHP



}
echo "</table>" ;

?>

</tr>
</table>
</div>

<div class="clr"></div>
</div>
<div class="b">
<div class="b">
<div class="b"></div>
</div>
</div>
</div>
</div>
</div>
<div class="clr"></div>

<tr><td><input type="hidden" name="id" value="<?php echo $row->id;?>" /></td></tr>
<tr><td><input type="hidden" name="option" value="<?php echo $option; ?>" /></td></tr>
<tr><td><input type="hidden" name="act" value="<?php echo $act; ?>" /></td></tr>
<tr><td><input type="hidden" name="task" value="" /></td></tr>
<tr><td><input type="hidden" name="boxchecked" value="0" /></td></tr>
<tr><td><input type="hidden" name="gesendet" value="2" /></td></tr>

<tr><td colspan="2"><input type="submit" value="<? echo _SPIELPLAN_SPEICHERN;?>"></td></tr>
</div>
</table>



<?PHP


}

// jetzt werden die paarungen verarbeitet und eingef�gt
if ( $gesendet == 2 )
{

echo "</tr>";
echo "</table>";
echo "</div>";

/**
* echo "<pre>";
* print_r($_POST);
* echo "</pre>";
*/

// spielplan-array erstellen
foreach($_POST as $key => $element)
{
if (substr($key,0,8)=="spieltag")
{
$temp=explode ("_",$key);
$tempspielplan[0][$temp[1]]["spieltag"] = $element;
}

if (substr($key,0,6)=="homeid")
{
$temp=explode ("_",$key);
$tempspielplan[0][$temp[1]]["homeid"] = $element;
}

if (substr($key,0,6)=="awayid")
{
$temp=explode ("_",$key);
$tempspielplan[0][$temp[1]]["awayid"] = $element;
}

if (substr($key,0,11)=="spielnummer")
{
$temp=explode ("_",$key);
$tempspielplan[0][$temp[1]]["spielnummer"] = $element;
}

if (substr($key,0,10)=="spieldatum")
{
$temp=explode ("_",$key);
$tempspielplan[0][$temp[1]]["spieldatum"] = $element;
}

if (substr($key,0,7)=="uhrzeit")
{
$temp=explode ("_",$key);
$tempspielplan[0][$temp[1]]["uhrzeit"] = $element;
}


}

//print_r($tempspielplan);
//echo "<br>";

//echo "eintr�ge -> ".count($tempspielplan[0])."<br>";

/**
* $spieltag[] = mosGetParam( $_POST, 'spieltag', 0 ) ;
* $homeid[] = mosGetParam( $_POST, 'homeid', 0 ) ;
* $awayid[] = mosGetParam( $_POST, 'awayid', 0 ) ;
* $spielnummer[] = mosGetParam( $_POST, 'spielnummer', 0 ) ;
* $spieldatum[] = mosGetParam( $_POST, 'spieldatum', 0 ) ;
* $uhrzeit[] = mosGetParam( $_POST, 'uhrzeit', 0 ) ;
*/

// alles in ordnung jetzt die spieltage anlegen
// zum schl�ssel die spieltage holen
$database->setQuery( "SELECT spieltag FROM #__joomleague_dfb where schluessel = '$dfbschluessel'
group by spieltag order by spieltag ");
$rowsdfb = $database->loadObjectList();

// anfang dfb-schl�ssel
foreach($rowsdfb as $rowdfb) {
//echo "".$rowdfb->spieltag."<br>";

// gibt es den spieltag schon ?
$database->setQuery( "SELECT id as roundid FROM #__joomleague_round WHERE
roundcode = '$rowdfb->spieltag' and project_id = $project->id ");

$spieltagid = $database->loadResult();

if ( $spieltagid == 0 )
{
//echo "".$rowdfb->spieltag." nicht vorhanden also anlegen<br>";

/**
* $row = new Round( $database );
* $row->load(0);
* $row->roundcode = $rowdfb->spieltag;
* $row->name = $rowdfb->spieltag.". Spieltag";
* $row->project_id = $project->id;

* $row->store();

* $roundid=$database->insertid();
*/

//echo "roundid ".$roundid." anlegt<br>";

}
else
{
$roundid=$spieltagid;
//echo "".$rowdfb->spieltag." mit id ".$roundid." vorhanden <br>";
}

// anfang durchlauf spielplan
//for ($a=0; $a < count($spieltag[0]);$a++)
for ($a=1; $a <= count($tempspielplan[0]);$a++)
{

$string_zahl = sprintf ( "%03d",  is_int($a) ? $a : $a*1) ;
$a = $string_zahl;

/**
* echo "string_zahl ->".$string_zahl."spielplan array -> ".$tempspielplan[0][$a]["spieltag"];
* echo $tempspielplan[0][$a]["homeid"];
* echo $tempspielplan[0][$a]["awayid"];
* echo $tempspielplan[0][$a]["spielnummer"];
* echo $tempspielplan[0][$a]["spieldatum"];
* echo $tempspielplan[0][$a]["uhrzeit"]."<br>";
* echo "durchlauf ->".$a." dfb-spieltag -> ".$rowdfb->spieltag."<br>";
*/

if ( $tempspielplan[0][$a]["spieltag"] == $rowdfb->spieltag )
{

/**
* echo "gefunden -> ".$tempspielplan[0][$a]["spieltag"];
* echo $tempspielplan[0][$a]["homeid"];
* echo $tempspielplan[0][$a]["awayid"];
* echo $tempspielplan[0][$a]["spielnummer"];
* echo $tempspielplan[0][$a]["spieldatum"];
* echo $tempspielplan[0][$a]["uhrzeit"]."<br>";
*/

/**
* echo $spieltag[0][$a];
* echo $homeid[0][$a];
* echo $awayid[0][$a];
* echo $spielnummer[0][$a];
* echo $spieldatum[0][$a];
* echo $uhrzeit[0][$a]."<br>";
*/

// ###########################################################
// �berpr�fung
// gibt es die paarung schon ?
$team1 = $tempspielplan[0][$a]["homeid"];
$team2 = $tempspielplan[0][$a]["awayid"];
$database->setQuery( "SELECT match_id as matchid FROM #__joomleague_match
WHERE team1 = '$team1'
and team2 = '$team2'
and project_id = $project->id ");

$matchid = $database->loadResult();

// ###########################################################



/**
* $row = new Match( $database );
* $row->load(0);
*/

$row = new Match( $database );
$row->load( $matchid );


if ( $matchid <> 0 )
{
// spiel vorhanden datum und uhrzeit update
list($datum, $uhrzeit)=split(" ", $row->match_date);

if ( $datum == '0000-00-00' )
{
$datum = $tempspielplan[0][$a]["spieldatum"];
}
if ( $uhrzeit == '00:00:00')
{
$uhrzeit = $tempspielplan[0][$a]["uhrzeit"];
}

$row->match_date = $datum." ".$uhrzeit;

}
else
{
// spiel nicht vorhanden datum und uhrzeit setzen
$row->match_date = $tempspielplan[0][$a]["spieldatum"]." ".$tempspielplan[0][$a]["uhrzeit"];
}

/**
* $row->match_number = $spielnummer[0][$a];
* $row->team1 = $homeid[0][$a];
* $row->team2 = $awayid[0][$a];
* $row->project_id = $project->id;
* $row->round_id = $roundid;
* $row->match_date = $spieldatum[0][$a]." ".$uhrzeit[0][$a];
*/

$row->match_number = $tempspielplan[0][$a]["spielnummer"];
$row->team1 = $tempspielplan[0][$a]["homeid"];
$row->team2 = $tempspielplan[0][$a]["awayid"];
$row->project_id = $project->id;
$row->round_id = $roundid;
//$row->match_date = $tempspielplan[0][$a]["spieldatum"]." ".$tempspielplan[0][$a]["uhrzeit"];

$row->count_result = 1;
$row->published = 1;

// nur einf�gen wenn heim und gast nicht 0 sind
if ( $tempspielplan[0][$a]["homeid"] == 0 || $tempspielplan[0][$a]["awayid"] == 0 || empty($tempspielplan[0][$a]["homeid"]) || empty($tempspielplan[0][$a]["awayid"]) )
{
// kein einf�gen
}
else
{

if ( $matchid <> 0 )
{
$row->store();
}
else
{
// noch nicht speichern
$row->store();
}


/**
* </tr>
* </table>
* </div>
*/

}

//echo "alterspieltag -> ".$alterspieltag." dfb spieltag -> ".$rowdfb->spieltag."<br>";
if ( $alterspieltag <> $rowdfb->spieltag )
{
$alterspieltag = $rowdfb->spieltag;

/**
* </tr>
* </table>
* </div>
*/

?>
<!-- Info START -->

<tr>
<td>
<div id="step">

<div class="t">
<div class="t">
<div class="t"></div>
</div>
</div>

<div class="m" align="left">

<table width="100%" class="content" cellpadding="">
<tr>
<td><img align="top" src="components/com_joomleague/images/ok.png" alt="OK" title"OK"> Spieltag <?php echo $rowdfb->spieltag; ?> wurde eingef�gt !
</td>
</tr>
</table>

</div>
<div class="b">
<div class="b">
<div class="b"></div>
</div>
</div>

</div>

</td>
</tr>
<!-- Info END -->

<?PHP
}
else
{
$alterspieltag = $rowdfb->spieltag;
}

}

}
// ende durchlauf spielplan

//$alterspieltag = $rowdfb->spieltag;

}
// ende dfb-schl�ssel

}


?>


</form>
</div>
</table>