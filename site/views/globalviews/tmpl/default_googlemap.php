<?php
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
* OHNE JEDE GEWÄHRLEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/ 

defined('_JEXEC') or die('Restricted access');
$this->view = JFactory::getApplication()->input->getCmd('view');
//echo $this->kmlfile.'<br>';
//echo JURI::root(true).'<br>';
//echo JURI::root().'<br>';
//$this->kmlfile = 'test-club.kml';

switch ($this->view)
{
case 'ranking':
//echo '<pre>'.print_r($this->allteams,true).'</pre><br>';
foreach ( $this->allteams as $row )
{
// team_name
//$values[]['latLng'] = '['.$row->latitude.','.$row->longitude.'], data:'.$row->team_name;
//$values[][data] = '['.$row->team_name.']';
$latitude = $row->latitude;
$longitude = $row->longitude;
//echo 'latitude  -> '.$latitude .'<br>';
//echo 'longitude -> '.$longitude .'<br>';
}
$icon = 'http://maps.google.com/mapfiles/marker_green.png';
//echo json_encode($values);

break;
case 'clubinfo':
$latitude = $this->club->latitude;
$longitude = $this->club->longitude;
$icon = 'http://maps.google.com/mapfiles/kml/pal2/icon49.png';
break;
case 'playground':
$latitude = $this->playground->latitude;
$longitude = $this->playground->longitude;
$icon = 'http://maps.google.com/mapfiles/kml/pal2/icon39.png';
break;

}

?>

<div class="row-fluid">
<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
<h4>
<?php echo JText::_('COM_SPORTSMANAGEMENT_GMAP_DIRECTIONS'); ?>
</h4>
	
<?php
/**
 * welche joomla version 
 * und ist seo eingestellt
 */
if(version_compare(JVERSION,'3.0.0','ge')) 
{
$sef = JFactory::getConfig()->get('sef', false);
}
else
{
$sef = JFactory::getConfig()->getValue('config.sef', false);
}


//echo 'sef -> '.$sef .'<br>';
//echo 'plugin_googlemap3 -> '.JPluginHelper::isEnabled( 'system', 'plugin_googlemap3' ).'<br>';
        
if ( ( !JPluginHelper::isEnabled( 'system', 'plugin_googlemap3' ) ) || ( JPluginHelper::isEnabled( 'system', 'plugin_googlemap3' ) && $sef ) )
//if ( !JPluginHelper::isEnabled( 'system', 'plugin_googlemap3' ) )
{
// JError::raiseWarning(500,JText::_('COM_SPORTSMANAGEMENT_ADMIN_GOOGLEMAP_NOT_ENABLED'));

$this->document->addScript('https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places');
$this->document->addScript(JURI::root(true).'/administrator/components/com_sportsmanagement/assets/js/gmap3.min.js');

//$this->document->addScript('https://maps.googleapis.com/maps/api/js&sensor=false');
//$this->document->addScript(JURI::root(true).'/administrator/components/com_sportsmanagement/assets/js/gmap3-7.min.js');


//$this->document->addScript('http://maps.google.com/maps/api/js?language=de');
//$this->document->addScript('https://maps.googleapis.com/maps/api/js?v=3.21&sensor=false&language=de');	
//$this->document->addScript('https://maps.googleapis.com/maps/api/js?v=3.21&language=de');		
//$this->document->addScript('https://cdn.jsdelivr.net/gmap3/7.2.0/gmap3.min.js');


switch ($this->view)
{
case 'clubinfo':
case 'playground':
?>
<div id="map" style="width:50%;height:600px;float: left;"></div>
<div id="pano" style="width:50%;height:600px;float: left;"></div>
<?php
break;
default:
?>
<div id="map-canvas" style="width:100%;height:800px;"></div>
<?php
break;
}

        
?>






    
<script type="text/javascript">
<?PHP
switch ($this->view)
{
case 'clubinfo':
case 'playground':
?>

//var fenway = new google.maps.LatLng(<?PHP echo $latitude; ?>,<?PHP echo $longitude; ?>);


/*
https://developers.google.com/maps/documentation/javascript/3.exp/reference#StreetViewPanoramaOptions
https://developers.google.com/maps/documentation/javascript/3.exp/reference#StreetViewPov
https://developers.google.com/maps/documentation/javascript/examples/streetview-embed?hl=de
*/
jQuery(document).ready(function()  {

//jQuery('#map-canvas').gmap3({
//  map:{
//    options:{
//      zoom: 14, 
//      mapTypeId: google.maps.MapTypeId.HYBRID , 
//      streetViewControl: true, 
//      center: fenway 
//    }
//  },    
//  streetviewpanorama:{
//    options:{
//      container: jQuery(document.createElement("div")).addClass("googlemap").insertAfter( jQuery('#map-canvas') ),
//      opts:{
//        position: fenway,
//        visible: true,
//        pov: {
//          heading: 34,
//          pitch: 10
//        }
//      }
//    }
//  }
//});


var fenway2 = {lat: <?PHP echo $latitude; ?>, lng: <?PHP echo $longitude; ?>};
        var map = new google.maps.Map(document.getElementById('map'), {
          center: fenway2,
          mapTypeControl: true,
mapTypeId: 'satellite',
          zoom: 14
        });
        var panorama = new google.maps.StreetViewPanorama(
            document.getElementById('pano'), {
              position: fenway2,
              pov: {
                heading: 34,
                pitch: 10
              }
            });
        map.setStreetView(panorama);


});


<?PHP
break;
default:
$map_markes = array();

$zaehler = 1;
foreach ( $this->allteams as $row )
{

$latitude = $row->latitude;
$longitude = $row->longitude;
if ( !empty($latitude) )
{
//$map_markes[] = "{lat:".$latitude.", lng:".$longitude.", data:'Paris !'}";
$map_markes[] = "[".$zaehler.",".$latitude.",".$longitude.",'".$row->team_name."']";
$zaehler++;
}

}


//echo 'map_markes <br><pre>'.print_r($this->allteams,true).'</pre>';
$comma_separated = implode(",", $map_markes);



?>

var locations = [<?PHP echo $comma_separated;?>];

var map;
        var str = '[';
        for (i = 0; i < locations.length; i++) {
          str += '{ "lat" :"' + locations[i][1] + '","lng" :"' + locations[i][2] + '","data" :"<div class=Your_Class><h4><a href=Your_Link_To_Marker>' + locations[i][3] + '</a></h4></div>"},';
        }
        str = str.substring(0, str.length - 1);
        str += ']';
        str = JSON.parse(str);
jQuery(document).ready(function() {       
        jQuery('#map-canvas').gmap3({
          marker: {
            values: str,
              options: {
                icon: 'http://maps.google.com/mapfiles/kml/pal2/icon49.png',
                //icon: new google.maps.MarkerImage("marker.png"),
              },
              events: {
                click: function (marker, event, context) {
                  map = jQuery('#map-canvas').gmap3("get"),
                    infowindow = jQuery('#map-canvas').gmap3({ get: { name: "infowindow" } });
                  if (infowindow) {
                    infowindow.open(map, marker);
                    infowindow.setContent(context.data);
                  } else {
                    jQuery('#map-canvas').gmap3({
                    infowindow: {
                      anchor: marker,
                      options: { content: context.data }
                    }
                  });
                }
              },
            }
          },
          map: {
            options: {
              //zoom: 14,
              //mapTypeId: google.maps.MapTypeId.ROADMAP,
              draggable: true,
              mapTypeId: google.maps.MapTypeId.HYBRID ,
              scrollwheel: true,//Make It false To Stop Map Zooming By Scroll
              streetViewControl: true
            },
          },
          autofit:{}
        });
});
     
         
  
  
<?PHP

break;

}
?>

</script>
<style>
.gmap3{
width: 100%;
height: 570px;
}
</style>


<?PHP                
}
else
{
$plugin = JPluginHelper::getPlugin('system', 'plugin_googlemap3');
$paramsPlugin = new JRegistry($plugin->params);

//echo 'kml<br><pre>'.print_r($this->kmlpath,true).'</pre>';
//echo 'plugin_googlemap3<br><pre>'.print_r($paramsPlugin,true).'</pre>';

$params  = "{mosmap kml[0]='".'tmp'.DS.$this->kmlfile."'}";
echo JHtml::_('content.prepare', $params);
  
}
            
            
?>
</div>
</div>
