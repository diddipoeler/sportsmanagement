<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      deafault_googlemap_route.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage playground
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

//$this->document->addScript('https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places');
?>

<?php echo JText::_('COM_SPORTSMANAGEMENT_PLAYGROUND_GOOGLE_ROUTE'); ?>
<div class="row-fluid">
<div id="map-route" style="width:100%;height:800px;"></div>

<script type="text/javascript">
jQuery(document).ready(function()  {
// Create a map and center it on Manhattan.
        var map = new google.maps.Map(document.getElementById('map-route'), {
          zoom: 13,
          center: {lat: 40.771, lng: -73.974}
        });
getLocation();
});

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


</script>
<?php 

?>
</div>