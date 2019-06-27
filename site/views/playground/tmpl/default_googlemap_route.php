<?php
/** SportsManagement ein Programm zur Verwaltung f?r alle Sportarten
 * @version   1.0.05
 * @file      deafault_googlemap_route.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: ? 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage playground
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\CMS\Language\Text;
$this->document->addScript('https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=true');
$latitude = $this->playground->latitude;
$longitude = $this->playground->longitude;
?>


<?php echo Text::_('COM_SPORTSMANAGEMENT_PLAYGROUND_GOOGLE_ROUTE'); ?>
<div class="row-fluid">


<div id="divlos" >
<button id="los">Los!</button></div>
<div id="divausgabe" ></div>

<div id="map-route" style="width:100%;height:800px;"></div>

<script type="text/javascript">
// https://gist.github.com/stevenzeiler/3660644
//https://wiki.selfhtml.org/wiki/JavaScript/Geolocation
            
jQuery(document).ready(function()  {
// Create a map and center it on Manhattan.
        var map = new google.maps.Map(document.getElementById('map-route'), {
          zoom: 13,
          center: {lat: <?PHP echo $latitude; ?>, lng: <?PHP echo $longitude; ?>}
        });

var button =document.getElementById('los'); 
button.addEventListener ('click', ermittlePosition);
var ausgabe = document.getElementById('divausgabe');
  
//get_location();

});

function ermittlePosition() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(zeigePosition, zeigeFehler);
    } else { 
        ausgabe.innerHTML = 'Ihr Browser unterstützt keine Geolocation.';
    }
}

function zeigePosition(position) {
    ausgabe.innerHTML = "Ihre Koordinaten sind:<br> Breite: " + position.coords.latitude + 
    "<br>Länge: " + position.coords.longitude;	
}


function zeigeFehler(error) {
console.log("error code : "+ error.code);
    switch(error.code) {
        case error.PERMISSION_DENIED:
            document.getElementById('divausgabe').innerHTML = "Benutzer lehnte Standortabfrage ab.";
            console.log("error text : "+ "Benutzer lehnte Standortabfrage ab.");
            break;
        case error.POSITION_UNAVAILABLE:
            document.getElementById('divausgabe').innerHTML = "Standortdaten sind nicht verfügbar."
            console.log("error text : "+ "Standortdaten sind nicht verfügbar.");
            break;
        case error.TIMEOUT:
            document.getElementById('divausgabe').innerHTML = "Die Standortabfrage dauerte zu lange (Time-out)."
            console.log("error text : "+ "Die Standortabfrage dauerte zu lange (Time-out).");
            break;
        case error.UNKNOWN_ERROR:
            document.getElementById('divausgabe').innerHTML = "unbekannter Fehler."
            console.log("error text : "+ "unbekannter Fehler.");
            break;
    }
}




function get_location() {
  if ( supports_geolocation() ) {
  console.log("getLocation : "+ "Geolocation is supported by this browser.");
    navigator.geolocation.getCurrentPosition(show_map, handle_error);
  } else {
    // no native support;
	console.log("getLocation : "+ "Geolocation is not supported by this browser.");
  }
}

function handle_error(err) {
console.log("error code : "+ err.code);
  if (err.code == 1) {
    // user said no!
    console.log("error text : "+ "You chose not to share your location.");
  }
}

function supports_geolocation() {
  return !!navigator.geolocation;
}

function show_map(position) {
	var latitude = position.coords.latitude;
	var longitude = position.coords.longitude;
	
console.log("getLocation : "+ latitude);
	
	// let's show a map or do something interesting!
	
}	

</script>
<?php 
//$this->document->addScript('https://maps.googleapis.com/maps/api/js?key=AIzaSyCL5lnwcI1WJFThmI-q-hj7kfQPF2XP6mE');
?>





</div>