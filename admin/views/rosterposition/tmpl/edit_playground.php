<?php 
defined( '_JEXEC' ) or die( 'Restricted access' );

$backgroundimage = JURI::root().'media/com_joomleague/rosterground/spielfeld_578x1050.png';
list($width, $height, $type, $attr) = getimagesize($backgroundimage);

// bildpositionen für die spielsysteme
$bildpositionen = array();
$bildpositionen[4231][0][heim][oben] = 5;
$bildpositionen[4231][0][heim][links] = 233;
$bildpositionen[4231][1][heim][oben] = 113;
$bildpositionen[4231][1][heim][links] = 69;
$bildpositionen[4231][2][heim][oben] = 113;
$bildpositionen[4231][2][heim][links] = 179;
$bildpositionen[4231][3][heim][oben] = 113;
$bildpositionen[4231][3][heim][links] = 288;
$bildpositionen[4231][4][heim][oben] = 113;
$bildpositionen[4231][4][heim][links] = 397;
$bildpositionen[4231][5][heim][oben] = 236;
$bildpositionen[4231][5][heim][links] = 179;
$bildpositionen[4231][6][heim][oben] = 236;
$bildpositionen[4231][6][heim][links] = 288;
$bildpositionen[4231][7][heim][oben] = 318;
$bildpositionen[4231][7][heim][links] = 69;
$bildpositionen[4231][8][heim][oben] = 318;
$bildpositionen[4231][8][heim][links] = 233;
$bildpositionen[4231][9][heim][oben] = 318;
$bildpositionen[4231][9][heim][links] = 397;
$bildpositionen[4231][10][heim][oben] = 400;
$bildpositionen[4231][10][heim][links] = 233;

?>

<?php

echo "<div style=\"background-image:url('".$backgroundimage."');background-position:left;position:relative;height:".$height."px;width:".$width."px;\">";


$testlauf = 0;
$picture = JURI::root().'images/com_joomleague/database/placeholders/placeholder_150_2.png';
$schemahome = $bildpositionen[4231];
foreach ( $schemahome as $key => $value )
{
?>

<div style="position:absolute; width:103px; left:<?PHP echo $value['heim']['links']; ?>px; top:<?PHP echo $value['heim']['oben']; ?>px; text-align:center;">

<img class="bild_s" style="width:60px;" id="img_<?PHP echo $testlauf; ?>" src="<?PHP echo $picture; ?>" alt="" /><br />


</div>
                                      
<?PHP
$testlauf++;
}
echo "</div>";
?>

