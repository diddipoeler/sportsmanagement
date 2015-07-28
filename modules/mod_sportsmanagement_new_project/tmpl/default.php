<?php



defined('_JEXEC') or die('Restricted access');

$list = modJSMNewProjectHelper::getData();
//JHTML::_( 'behavior.mootools' ); 
jimport('joomla.html.pane');


/*
echo '<pre>';
print_r($list);
echo '</pre>';
*/

echo JHtml::_('sliders.start' , 'neueligen', array('useCookie'=>1)   );
echo JHtml::_('sliders.panel', JText::_('Neue Ligen'), 'neueligen' );
?>



<table width="100%" class="">

<?PHP

$zeile = 0;
echo '<tr>';

if ( sizeof($list) == 0 )
{
echo '<td width="" >';
echo '<b>Heute gibt es leider keine neuen Ligen!</b>';
echo '</td>';
}
else
{

echo '<td colspan="2" width="" >';
echo '<b>Wir haben '.sizeof($list).' neue/aktualisierte Ligen !</b>';
echo '</td>';

foreach ( $list as $row )
{

if( $zeile <  10 ) 
{ 


if( $zeile % 2 == 0 ) 
{ 
 // Zahl ist gerade 
echo '</tr><tr>';
} 
else 
{ 
 // Zahl ist ungerade 
echo '</tr><tr>';
} 


/*
echo '<td>';
echo $zeile;
echo '</td>';
*/

echo '<td width="" >';
echo JSMCountries::getCountryFlag( $row->country );
echo '</td>';

echo '<td>';

$createroute = array(	"option" => "com_sportsmanagement",
							"view" => "resultsranking",
							"p" => $row->id,
              "r" => $row->roundcode );

$query = sportsmanagementHelperRoute::buildQuery( $createroute );
$link = JRoute::_( 'index.php?' . $query, false );
echo JHTML::link( $link, JText::_( $row->name.' - ( '.$row->liganame.' )'  ) );
//echo JHTML::link( $link, JText::_( $row->name  ) );
                            
echo '</td>';
$zeile++;
}

}

}
echo '</tr>';



?>

</table>

<?PHP


//$pane =& JPane::getInstance('sliders', array('allowAllClose' => true, 'startOffset'=>2,  'startTransition' => true));
//echo $pane->startPane("content-pane");
//$title = JText::_('Es sind mehr neue Ligen vorhanden ! Bitte klick mich an !');
//echo $pane->startPanel($title, 'jfcpanel-panel-'.'ligen');


?>
<div style="float: left;">
<table width="100%" class="">
<?PHP

if ( sizeof($list)  )
{
echo '<tr>';

$lfdnummer = 0;

foreach ( $list as $row )
{

if( $zeile >  9 ) 
{ 

if( $lfdnummer >  9 ) 
{ 

if( $zeile % 2 == 0 ) 
{ 
 // Zahl ist gerade 
echo '</tr><tr>';
} 
/*
else 
{ 
 // Zahl ist ungerade 
echo '<tr>';
} 
*/

echo '<td width="" >';
echo JSMCountries::getCountryFlag( $row->country );
echo '</td>';
echo '<td>';
$createroute = array(	"option" => "com_sportsmanagement",
							"view" => "resultsranking",
							"p" => $row->id,
              "r" => $row->roundcode );
$query = sportsmanagementHelperRoute::buildQuery( $createroute );
$link = JRoute::_( 'index.php?' . $query, false );
echo JHTML::link( $link, JText::_( $row->name.' - ( '.$row->liganame.' )'  ) );
echo '</td>';
}
$zeile++;
}
$lfdnummer++;
}

echo '</tr>';
}
?>
</table>
</div>
<?php
echo JHtml::_('sliders.end');

//echo $pane->endPanel();
//echo $pane->endPane();

/*
//1st Parameter: Specify 'tabs' as appearance 
//2nd Parameter: Starting with third tab as the default (zero based index)
//open one!
$pane =& JPane::getInstance('tabs', array('startOffset'=>2)); 
echo $pane->startPane( 'pane' );
echo $pane->startPanel( 'Example Panel 1', 'panel1' );
echo "This is panel1";
echo $pane->endPanel();
echo $pane->startPanel( 'Example Panel 2', 'panel2' );
echo "This is panel2";
echo $pane->endPanel();
echo $pane->startPanel( 'Example Panel 3', 'panel3' );
echo "This is panel3";
echo $pane->endPanel();
echo $pane->endPane();
*/

?>