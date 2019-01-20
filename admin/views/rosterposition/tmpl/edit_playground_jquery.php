<?php 
defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\CMS\Uri\Uri;

$backgroundimage = Uri::root().'media/com_sportsmanagement/rosterground/'.$this->item->picture;
list($width, $height, $type, $attr) = getimagesize($backgroundimage);
$picture = Uri::root().'images/com_sportsmanagement/database/placeholders/placeholder_150_2.png';


?>
<style type="text/css">
    #draggable { width: 100px; height: 70px; background: silver; }
  </style>

<div id="start">
<input type='text' id='text' value='' />
</div>

<div id="stop">
spieler verschieben
</div>

<?php
echo "<div id=\"roster\"   style=\"background-image:url('".$backgroundimage."');background-position:left;position:relative;height:".$height."px;width:".$width."px;\">";
$schemahome = $this->bildpositionen[$this->item->name];
$testlauf = 1;
foreach ( $schemahome as $key => $value )
{
//<div id="draggable">
?>  

<div id="draggable_<?PHP echo $testlauf; ?>" style="position:absolute; width:103px; left:<?PHP echo $value['heim']['links']; ?>px; top:<?PHP echo $value['heim']['oben']; ?>px; text-align:center;">
<img class="bild_s" style="width:60px;" id="img_<?PHP echo $testlauf; ?>" src="<?PHP echo $picture; ?>" alt="" /><br />
</div>
<?php
$testlauf++;
}
echo "</div>";
?>