<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      deafault_googlemap.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage globalviews
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;

$this->view = Factory::getApplication()->input->getCmd('view');

if ( $this->config['use_which_map'] )
{
$this->document->addScript('https://unpkg.com/leaflet@1.3.4/dist/leaflet.js');
$this->document->addStyleSheet('https://unpkg.com/leaflet@1.3.4/dist/leaflet.css');	

switch ( $this->mapconfig['default_map_type'] )
{
case 'G_NORMAL_MAP':  
$map_type = 'http://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}';  
break;    
case 'G_SATELLITE_MAP': 
$map_type = 'http://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}';   
break;
case 'G_HYBRID_MAP':  
$map_type = 'http://{s}.google.com/vt/lyrs=s,h&x={x}&y={y}&z={z}';  
break;
case 'G_TERRAIN_MAP': 
$map_type = 'http://{s}.google.com/vt/lyrs=p&x={x}&y={y}&z={z}';   
break;
}
	
	
?>	
<h4>
<?php echo Text::_('COM_SPORTSMANAGEMENT_GMAP_DIRECTIONS'); ?>
</h4>
<div id="map" style="height: <?php echo $this->mapconfig['map_height']; ?>px; margin-top: 50px; position: relative;">
</div>
<?php
switch ($this->view)
{
case 'clubinfo':	
?>
<script>
  
     var planes = [
         ["<?php echo $this->club->name; ?>",<?php echo $this->club->latitude; ?>,<?php echo $this->club->longitude; ?>]
         ];
  
         var map = L.map('map').setView([<?php echo $this->club->latitude; ?>,<?php echo $this->club->longitude; ?>], 8);
         mapLink =
             '<a href="http://openstreetmap.org">OpenStreetMap</a>';
         L.tileLayer(
             '<?php echo $map_type; ?>', {
             attribution: '&copy; ' + mapLink + ' Contributors',
             maxZoom: <?php echo $this->mapconfig['map_zoom']; ?>,
             }).addTo(map);
  
         for (var i = 0; i < planes.length; i++) {
             marker = new L.marker([planes[i][1],planes[i][2]])
                 .bindPopup(planes[i][0])
                 .addTo(map);
         }
              
     </script>
<?php
break;
case 'ranking':
foreach ( $this->allteams as $row )
{

$latitude = $row->latitude;
$longitude = $row->longitude;
	
if ( !empty($latitude) && $latitude != '0.00000000' )
{
$row->team_name= str_replace($find, $replace, $row->team_name);
// logo_big
$map_markes[] = "['".$row->team_name.'<br>'.JHtml::_('image',$row->logo_big,$row->team_name,array('width' => '50') )."',".$latitude.",".$longitude.",'".$row->team_name."','".$row->logo_big."']";
$map_bounds[] = "[".$latitude.",".$longitude."]";
$zaehler++;
}

}

$comma_separated = implode(",", $map_markes);
$comma_bounds = implode(",", $map_bounds);
?>
<script>
  
     var planes = [
         <?php echo $comma_separated; ?>
         ];
  
         var map = L.map('map').setView([<?php echo $latitude; ?>,<?php echo $longitude; ?>], 8);
         mapLink =
             '<a href="http://openstreetmap.org">OpenStreetMap</a>';
         L.tileLayer(
             '<?php echo $map_type; ?>', {
             attribution: '&copy; ' + mapLink + ' Contributors',
             maxZoom: <?php echo $this->mapconfig['map_zoom']; ?>,
             }).addTo(map);
  
         for (var i = 0; i < planes.length; i++) {
             marker = new L.marker([planes[i][1],planes[i][2]])
                 .bindPopup(planes[i][0])
                 .addTo(map);
         }
         map.fitBounds([<?php echo $comma_bounds; ?>]);     
     </script>
<?php

break;		
}		
?>	


<?php
}
else
{

switch ($this->view)
{
case 'ranking':
//echo '<pre>'.print_r($this->allteams,true).'</pre><br>';
//foreach ( $this->allteams as $row )
//{
// team_name
//$values[]['latLng'] = '['.$row->latitude.','.$row->longitude.'], data:'.$row->team_name;
//$values[][data] = '['.$row->team_name.']';
//$latitude = $row->latitude;
//$longitude = $row->longitude;
//echo 'latitude  -> '.$latitude .'<br>';
//echo 'longitude -> '.$longitude .'<br>';
//}
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

<div class="<?php echo $this->divclassrow;?>" id="jsmgooglemap">
<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
<h4>
<?php echo Text::_('COM_SPORTSMANAGEMENT_GMAP_DIRECTIONS'); ?>
</h4>
	
<?php
/**
 * welche joomla version 
 * und ist seo eingestellt
 */
if(version_compare(JVERSION,'3.0.0','ge')) 
{
$sef = Factory::getConfig()->get('sef', false);
}
else
{
$sef = Factory::getConfig()->getValue('config.sef', false);
}


//echo 'sef -> '.$sef .'<br>';
//echo 'plugin_googlemap3 -> '.JPluginHelper::isEnabled( 'system', 'plugin_googlemap3' ).'<br>';
        
if ( ( !JPluginHelper::isEnabled( 'system', 'plugin_googlemap3' ) ) || ( JPluginHelper::isEnabled( 'system', 'plugin_googlemap3' ) && $sef ) )
//if ( !JPluginHelper::isEnabled( 'system', 'plugin_googlemap3' ) )
{


$this->document->addScript('https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places');
$this->document->addScript(Uri::root(true).'/administrator/components/com_sportsmanagement/assets/js/gmap3.min.js');

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

// Create a StreetViewService to be able to check
// if a given LatLng has a corresponding panorama.
var streetviewService = new google.maps.StreetViewService();
streetviewService.getPanorama({location: {lat:<?PHP echo $latitude; ?>, lng:<?PHP echo $longitude; ?>}, radius: 50}, processSVData);


var fenway2 = {lat: <?PHP echo $latitude; ?>, lng: <?PHP echo $longitude; ?>};
        var map = new google.maps.Map(document.getElementById('map'), {
          center: fenway2,
          mapTypeControl: true,
mapTypeId: 'satellite',
          zoom: 14
        });
function processSVData(data, status) {
  if (status === google.maps.StreetViewStatus.OK) {
    //alert('ok');
        var panorama = new google.maps.StreetViewPanorama(
            document.getElementById('pano'), {
              position: fenway2,
              pov: {
                heading: 34,
                pitch: 10
              }
            });
        map.setStreetView(panorama);    
    
   } else {
    //alert('Street View data not found for this location.');
    //jQuery('#pano').hide();
    jQuery("#pano" ).remove();
    jQuery("#map").css("width", "100%");
    //jQuery("#pano").css("width", "");
    jQuery("#map").css("float", "");
    //jQuery("#pano").css("height", "");
   }
}

});


<?PHP
break;
default:
$map_markes = array();

$zaehler = 1;
$find[] = "'";
$replace[] = " ";
foreach ( $this->allteams as $row )
{

$latitude = $row->latitude;
$longitude = $row->longitude;
	
if ( !empty($latitude) && $latitude != '0.00000000' )
{
$row->team_name= str_replace($find, $replace, $row->team_name);
// logo_big
$map_markes[] = "[".$zaehler.",".$latitude.",".$longitude.",'".$row->team_name."','".$row->logo_big."']";
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
          str += '{ "lat" :"' + locations[i][1] + '","lng" :"' + locations[i][2] + '","data" :"<div class=Your_Class><h4><a href=Your_Link_To_Marker>' + locations[i][3] + 
          '</a></h4><img src=/' + locations[i][4] + ' width=50></div>"},';
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
echo HTMLHelper::_('content.prepare', $params);
  
}
            
            
?>
</div>
</div>
<?php
}
?>
