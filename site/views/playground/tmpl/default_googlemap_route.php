<?php
/** SportsManagement ein Programm zur Verwaltung f?r alle Sportarten
 * @version   1.0.05
 * @file      deafault_googlemap_route.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright Copyright: ? 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage playground
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

//$this->document->addScript('https://maps.googleapis.com/maps/api/js?key=AIzaSyCL5lnwcI1WJFThmI-q-hj7kfQPF2XP6mE');
$latitude = $this->playground->latitude;
$longitude = $this->playground->longitude;
?>
<script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCL5lnwcI1WJFThmI-q-hj7kfQPF2XP6mE&callback=initMap">
    </script>
    
<?php echo JText::_('COM_SPORTSMANAGEMENT_PLAYGROUND_GOOGLE_ROUTE'); ?>
<div class="row-fluid">
<div id="map-route" style="width:100%;height:800px;"></div>

<script type="text/javascript">
//var directionsService = new google.maps.DirectionsService;
//var directionsDisplay = new google.maps.DirectionsRenderer;

//jQuery(document).ready(function()  {
//// Create a map and center it on Manhattan.
//        var map = new google.maps.Map(document.getElementById('map-route'), {
//          zoom: 13,
//          center: {lat: <?PHP echo $latitude; ?>, lng: <?PHP echo $longitude; ?>}
//        });
//directionsDisplay.setMap(map);  
//calculateAndDisplayRoute(directionsService, directionsDisplay);      
////getLocation();
//});

function initMap() {
        var map = new google.maps.Map(document.getElementById('map-route'), {
          center: {lat: <?PHP echo $latitude; ?>, lng: <?PHP echo $longitude; ?>},
          zoom: 6
        });
        var infoWindow = new google.maps.InfoWindow({map: map});

        // Try HTML5 geolocation.
        if (navigator.geolocation) {
          navigator.geolocation.getCurrentPosition(function(position) {
            var pos = {
              lat: position.coords.latitude,
              lng: position.coords.longitude
            };

            infoWindow.setPosition(pos);
            infoWindow.setContent('Location found.');
            map.setCenter(pos);
          }, function() {
            handleLocationError(true, infoWindow, map.getCenter());
          });
        } else {
          // Browser doesn't support Geolocation
          handleLocationError(false, infoWindow, map.getCenter());
        }
      }

function handleLocationError(browserHasGeolocation, infoWindow, pos) {
        infoWindow.setPosition(pos);
        infoWindow.setContent(browserHasGeolocation ?
                              'Error: The Geolocation service failed.' :
                              'Error: Your browser doesn\'t support geolocation.');
      }
      
      
function calculateAndDisplayRoute(directionsService, directionsDisplay) {
        directionsService.route({
          origin: 'Gelsenkirchen',
          destination: 'München',
          travelMode: 'DRIVING'
        }, function(response, status) {
          if (status === 'OK') {
            directionsDisplay.setDirections(response);
          } else {
            window.alert('Directions request failed due to ' + status);
          }
        })
       } 
        
function geoSuccess(position) {


}

function geoError() {
console.log("getLocation geoError : "+ "Geocoder failed.");

        }
        
        function getLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(geoSuccess, geoError);
                console.log("getLocation : "+ "Geolocation is supported by this browser.");

            } else {
            console.log("getLocation : "+ "Geolocation is not supported by this browser.");

            }
        }

function calcRoute() {

    var start = new google.maps.LatLng(43.786161, 11.250510);
    var end = new google.maps.LatLng(<?PHP echo $latitude; ?>, <?PHP echo $longitude; ?>);

    createMarker(start, 'start');
    createMarker(end, 'end');

    var request = {
        origin: start,
        destination: end,
        optimizeWaypoints: true,
        travelMode: google.maps.DirectionsTravelMode.WALKING
    };

    directionsService.route(request, function (response, status) {
        if (status == google.maps.DirectionsStatus.OK) {
            directionsDisplay.setDirections(response);
            var route = response.routes[0];
        }
    });
}

function createMarker(latlng, title) {

    var marker = new google.maps.Marker({
        position: latlng,
        title: title,
        map: map
    });

    google.maps.event.addListener(marker, 'click', function () {
        infowindow.setContent(title);
        infowindow.open(map, marker);
    });
}


</script>
<?php 
//$this->document->addScript('https://maps.googleapis.com/maps/api/js?key=AIzaSyCL5lnwcI1WJFThmI-q-hj7kfQPF2XP6mE');
?>





</div>