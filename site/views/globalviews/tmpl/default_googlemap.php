<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage globalviews
 * @file       deafault_googlemap.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Leaflet Routing Machine API
 * http://www.liedman.net/leaflet-routing-machine/api/
 * https://github.com/perliedman/leaflet-routing-machine
 *
 * https://github.com/Turistforeningen/leaflet-routing
 *
 * https://github.com/smeijer/leaflet-geosearch
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;

$this->view    = Factory::getApplication()->input->getCmd('view');
$this->showmap = false;
$map_type      = 'http://{s}.google.com/vt/lyrs=s,h&x={x}&y={y}&z={z}';

if ($this->config['use_which_map'])
{
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.6.0/dist/leaflet.css"
  integrity="sha512-xwE/Az9zrjBIphAcBb3F6JVqxf46+CDLwfLMHloNu6KEQCAWi6HcDUbeOfBIptF7tcCzusKFjFw2yuvEpDL9wQ=="
  crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.6.0/dist/leaflet.js"
  integrity="sha512-gZwIG9x3wUXg2hdXF6+rVkLF/0Vi9U8D2Ntg4Ga5I5BZpVkVxlJWbSQtXPSiUTtC0TjtGOmxa1AJPuV0CPthew=="
  crossorigin=""></script>

<?php	
	//$this->document->addScript('https://unpkg.com/leaflet@1.3.4/dist/leaflet.js');
	//$this->document->addStyleSheet('https://unpkg.com/leaflet@1.3.4/dist/leaflet.css');

	$this->document->addScript('https://cdn.jsdelivr.net/npm/leaflet.locatecontrol@0.63.0/dist/L.Control.Locate.min.js');
	$this->document->addStyleSheet('https://cdn.jsdelivr.net/npm/leaflet.locatecontrol@0.63.0/dist/L.Control.Locate.min.css');

	$this->document->addScript('https://cdnjs.cloudflare.com/ajax/libs/leaflet-routing-machine/3.2.12/leaflet-routing-machine.js');
	$this->document->addStyleSheet('https://cdnjs.cloudflare.com/ajax/libs/leaflet-routing-machine/3.2.12/leaflet-routing-machine.css');

	/**
	 * geocoderscript
	 */
	// $this->document->addScript('https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js');
	// $this->document->addStyleSheet('https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css');

	switch ($this->mapconfig['default_map_type'])
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
    <div id="map"
         style="height: <?php echo $this->mapconfig['map_height']; ?>px; margin-top: 50px; position: relative;" itemscope itemtype="http://schema.org/Place">
    </div>
	<?php
	switch ($this->view)
	{
		case 'playground':
			if ($this->playground->latitude && $this->playground->longitude)
			{
				$this->showmap = true;
				?>
                <script>

                    var planes = [
                        ["<?php echo $this->playground->name; ?>",<?php echo $this->playground->latitude; ?>,<?php echo $this->playground->longitude; ?>]
                    ];

                    var map = L.map('map').setView([<?php echo $this->playground->latitude; ?>,<?php echo $this->playground->longitude; ?>], 16);
                    mapLink =
                        '<a href="http://openstreetmap.org">OpenStreetMap</a>';
                    L.tileLayer(
                        '<?php echo $map_type; ?>', {
                            attribution: '&copy; ' + mapLink + ' Contributors',
                            maxZoom: <?php echo $this->mapconfig['map_zoom']; ?>,
                            subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
                        }).addTo(map);
                    var myIcon = L.icon({
                        iconUrl: '<?php echo $this->mapconfig['map_icon']; ?>'
                    });
                    for (var i = 0; i < planes.length; i++) {
                        marker = new L.marker([planes[i][1], planes[i][2]], {icon: myIcon})
                            .bindPopup(planes[i][0])
                            .addTo(map);
                    }
                    //L.Control.geocoder().addTo(map);
                    L.control.locate().addTo(map);

                    jQuery.getJSON('https://ipinfo.io/geo', function (response) {
                        var loc = response.loc.split(',');
                        console.log(response.loc);
                        marker = new L.marker([loc[0], loc[1]]).addTo(map);

                        L.Routing.control({
                            waypoints: [
                                L.latLng(loc[0], loc[1]),
                                L.latLng(<?php echo $this->playground->latitude; ?>,<?php echo $this->playground->longitude; ?>)
                            ]
                        }).addTo(map);


                        console.log(loc);
                        var coords = {
                            latitude: loc[0],
                            longitude: loc[1]
                        };
                        console.log(coords);
                    });

                    jQuery.get("https://ipinfo.io", function (response) {
                        console.log(response.ip, response.country);
                    }, "jsonp");


                </script>
				<?php
			}
			else
			{
				?>
                <script>
                    jQuery("#map").width(50).height(50);
                </script>
				<?php
			}
			break;

		case 'clubinfo':
			if ($this->club->latitude && $this->club->longitude)
			{
				$this->showmap = true;
				?>
                <script>

                    var planes = [
                        ["<?php echo $this->club->name; ?>",<?php echo $this->club->latitude; ?>,<?php echo $this->club->longitude; ?>]
                    ];

                    var map = L.map('map').setView([<?php echo $this->club->latitude; ?>,<?php echo $this->club->longitude; ?>], 16);
                    mapLink =
                        '<a href="http://openstreetmap.org">OpenStreetMap</a>';
                    L.tileLayer(
                        '<?php echo $map_type; ?>', {
                            attribution: '&copy; ' + mapLink + ' Contributors',
                            maxZoom: <?php echo $this->mapconfig['map_zoom']; ?>,
                            subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
                        }).addTo(map);
                    var myIcon = L.icon({
                        iconUrl: '<?php echo $this->mapconfig['map_icon']; ?>'
                    });
                    for (var i = 0; i < planes.length; i++) {
                        marker = new L.marker([planes[i][1], planes[i][2]], {icon: myIcon})
                            .bindPopup(planes[i][0])
                            .addTo(map);
                    }
                    //L.Control.geocoder().addTo(map);
                    L.control.locate().addTo(map);

                    jQuery.getJSON('https://ipinfo.io/geo', function (response) {
                        var loc = response.loc.split(',');
                        console.log(response.loc);
                        marker = new L.marker([loc[0], loc[1]]).addTo(map);

                        L.Routing.control({
                            waypoints: [
                                L.latLng(loc[0], loc[1]),
                                L.latLng(<?php echo $this->club->latitude; ?>,<?php echo $this->club->longitude; ?>)
                            ]
                        }).addTo(map);


                        console.log(loc);
                        var coords = {
                            latitude: loc[0],
                            longitude: loc[1]
                        };
                        console.log(coords);
                    });

                    jQuery.get("https://ipinfo.io", function (response) {
                        console.log(response.ip, response.country);
                    }, "jsonp");


                </script>
				<?php
			}
			else
			{
				?>
                <script>
                    jQuery("#map").width(50).height(50);
                </script>
				<?php
			}
			break;
		case 'ranking':
		case 'resultsranking':
		case 'resultsmatrix':
			$zaehler   = 1;
			$find[]    = "'";
			$replace[] = " ";

			foreach ($this->allteams as $row)
			{
				$latitude  = $row->latitude;
				$longitude = $row->longitude;

				if (!empty($latitude) && $latitude != '0.00000000')
				{
					$row->team_name = str_replace($find, $replace, $row->team_name);

					// Logo_big
					$map_markes[] = "['" . $row->team_name . '<br>' . HTMLHelper::_('image', $row->logo_big, $row->team_name, array('width' => '50')) . "'," . $latitude . "," . $longitude . ",'" . $row->team_name . "','" . Uri::root() . $row->logo_big . "']";
					$map_bounds[] = "[" . $latitude . "," . $longitude . "]";
					$zaehler++;
					$setlatitude  = $row->latitude;
					$setlongitude = $row->longitude;
				}
			}

			$comma_separated = implode(",", $map_markes);
			$comma_bounds    = implode(",", $map_bounds);
			?>
            <script>

                var planes = [
					<?php echo $comma_separated; ?>
                ];

                var map = L.map('map').setView([<?php echo $setlatitude; ?>,<?php echo $setlongitude; ?>], 8);
                mapLink =
                    '<a href="http://openstreetmap.org">OpenStreetMap</a>';
                L.tileLayer(
                    '<?php echo $map_type; ?>', {
                        attribution: '&copy; ' + mapLink + ' Contributors',
                        maxZoom: <?php echo $this->mapconfig['map_zoom']; ?>,
                        subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
                    }).addTo(map);

                for (var i = 0; i < planes.length; i++) {
					<?php
					if ($this->mapconfig['map_ranking_club_icon'])
					{
					?>
                    console.log("wappen : " + planes[i][4]);
                    var myIcon = L.icon({
                        iconUrl: planes[i][4],
                        iconSize: [<?php echo $this->mapconfig['map_ranking_club_icon_width']; ?>, <?php echo $this->mapconfig['map_ranking_club_icon_width']; ?>]
                    });
					<?php
					}
					else
					{
					?>
                    var myIcon = L.icon({
                        iconUrl: '<?php echo $this->mapconfig['map_icon']; ?>'
                    });
					<?php
					}
					?>

                    marker = new L.marker([planes[i][1], planes[i][2]], {icon: myIcon})
                        .bindPopup(planes[i][0])
                        .addTo(map);
                }
                map.fitBounds([<?php echo $comma_bounds; ?>]);
                //L.Control.geocoder().addTo(map);
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
			$icon = 'http://maps.google.com/mapfiles/marker_green.png';
			break;
		case 'clubinfo':
			$latitude  = $this->club->latitude;
			$longitude = $this->club->longitude;
			$icon      = 'http://maps.google.com/mapfiles/kml/pal2/icon49.png';
			break;
		case 'playground':
			$latitude  = $this->playground->latitude;
			$longitude = $this->playground->longitude;
			$icon      = 'http://maps.google.com/mapfiles/kml/pal2/icon39.png';
			break;
	}

	?>

    <div class="<?php echo $this->divclassrow; ?>" id="jsmgooglemap">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <h4>
				<?php echo Text::_('COM_SPORTSMANAGEMENT_GMAP_DIRECTIONS'); ?>
            </h4>

			<?php
			/**
			 * welche joomla version
			 * und ist seo eingestellt
			 */
			if (version_compare(JVERSION, '3.0.0', 'ge'))
			{
				$sef = Factory::getConfig()->get('sef', false);
			}
			else
			{
				$sef = Factory::getConfig()->getValue('config.sef', false);
			}

			if ((!PluginHelper::isEnabled('system', 'plugin_googlemap3')) || (PluginHelper::isEnabled('system', 'plugin_googlemap3') && $sef))
			{
				$this->document->addScript('https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places');
				$this->document->addScript(Uri::root(true) . '/administrator/components/com_sportsmanagement/assets/js/gmap3.min.js');

			switch ($this->view)
			{
				case 'clubinfo':
			case 'playground':
			if ($this->showmap)
			{
				?>
                <div id="map" style="width:50%;height:600px;float: left;"></div>
                <div id="pano" style="width:50%;height:600px;float: left;"></div>
				<?php
			}
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
                    jQuery(document).ready(function () {

                        // Create a StreetViewService to be able to check
                        // if a given LatLng has a corresponding panorama.
                        var streetviewService = new google.maps.StreetViewService();
                        streetviewService.getPanorama({
                            location: {
                                lat:<?PHP echo $latitude; ?>,
                                lng:<?PHP echo $longitude; ?>}, radius: 50
                        }, processSVData);


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
                                jQuery("#pano").remove();
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

					foreach ($this->allteams as $row)
					{
						$latitude  = $row->latitude;
						$longitude = $row->longitude;

						if (!empty($latitude) && $latitude != '0.00000000')
						{
							$row->team_name = str_replace($find, $replace, $row->team_name);

							// Logo_big
							$map_markes[] = "[" . $zaehler . "," . $latitude . "," . $longitude . ",'" . $row->team_name . "','" . $row->logo_big . "']";
							$zaehler++;
						}
					}


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
                    jQuery(document).ready(function () {
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
                                            infowindow = jQuery('#map-canvas').gmap3({get: {name: "infowindow"}});
                                        if (infowindow) {
                                            infowindow.open(map, marker);
                                            infowindow.setContent(context.data);
                                        } else {
                                            jQuery('#map-canvas').gmap3({
                                                infowindow: {
                                                    anchor: marker,
                                                    options: {content: context.data}
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
                                    mapTypeId: google.maps.MapTypeId.HYBRID,
                                    scrollwheel: true,//Make It false To Stop Map Zooming By Scroll
                                    streetViewControl: true
                                },
                            },
                            autofit: {}
                        });
                    });




					<?PHP

					break;
					}
					?>

                </script>
                <style>
                    .gmap3 {
                        width: 100%;
                        height: 570px;
                    }
                </style>


				<?PHP
			}
			else
			{
				$plugin       = PluginHelper::getPlugin('system', 'plugin_googlemap3');
				$paramsPlugin = new Registry($plugin->params);
				$params       = "{mosmap kml[0]='" . 'tmp' . DIRECTORY_SEPARATOR . $this->kmlfile . "'}";
				echo HTMLHelper::_('content.prepare', $params);
			}

			?>
        </div>
    </div>
	<?php
}
