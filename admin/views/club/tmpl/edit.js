// http://bl.ocks.org/andyreagan/c81461c8a8ce52d103fc92decf9650b6

var dpjQuery = jQuery.noConflict();
var cities;
			
dpjQuery(document).ready(function(){
	dpjQuery('#jform_address,  #jform_zipcode, #jform_location,  #jform_state').bind('change', function(e) {
		dpjQuery("#jform_geocomplete").val(getAddresString());
		dpjQuery("#jform_geocomplete").trigger("geocode");
		
		
var inp = dpjQuery("#jform_geocomplete").val();
console.log('jform_geocomplete ' + inp );
 var xmlhttp = new XMLHttpRequest();
 var url = "https://nominatim.openstreetmap.org/search?format=json&limit=3&q=" + inp ;
 xmlhttp.onreadystatechange = function()
 {
   if (this.readyState == 4 && this.status == 200)
   {
    var myArr = JSON.parse(this.responseText);
    //console.log(myArr);
    //myFunction(myArr);
   }
 };
 xmlhttp.open("GET", url, true);
 xmlhttp.send();
 
 		
	});
	
	dpjQuery("#jform_geocomplete").geocomplete({
        map: ".map_canvas",
        location: getAddresString(),
        mapOptions: {
              scrollwheel: true,
              mapTypeId: "hybrid"
        },
        markerOptions: {
          draggable: true,
          mapTypeId: google.maps.MapTypeId.SATELLITE,
          icon: new google.maps.MarkerImage(
       "http://maps.google.com/mapfiles/kml/pal4/icon62.png",
       new google.maps.Size(32, 37, "px", "px") )
          
        }
     });
	dpjQuery("#jform_geocomplete").bind("geocode:result", function(event, result){
		//if (dpjQuery("#jform_geocomplete").data('initialized')) {
			setGeoResult(result);
			

		//}
		dpjQuery("#jform_geocomplete").data('initialized', true);
	});
	dpjQuery("#jform_geocomplete").bind("geocode:dragged", function(event, latLng){
		dpjQuery.ajax({
			  url:"//maps.googleapis.com/maps/api/geocode/json?latlng="+latLng.lat()+","+latLng.lng()+"&sensor=true",
			  type: "POST",
			  success:function(res){
				 if(res.results[0].address_components.length){
					 setGeoResult(res.results[0]);
				 }
			  }
			});
    });
});

function getAddresString()
{
	var address = '';
	var street = '';
	var city = '';
	var zip = '';
	var province = '';
	var country = '';
	if(dpjQuery("#jform_address").val()){
		street = dpjQuery("#jform_address").val();
		
	
		
		street += ', ';
	}
	if(dpjQuery("#jform_location").val()){
		city = dpjQuery("#jform_location").val();
		if(dpjQuery("#jform_zipcode").val()){
			city += ' ' + dpjQuery("#jform_zipcode").val();
		}
		
		city += ', ';
	}
	if (dpjQuery("#jform_state").val()) {
		province = dpjQuery("#jform_state").val() + ', ';
	}
	
//  if(dpjQuery("#jform_country").val()){
//		country = dpjQuery("#jform_country").val() + ', ';
//	}
  
//  if(dpjQuery("#jform_country").val()){
//		country = dpjQuery("#jform_country :selected").text() + ', ';
//	}

console.log('street ' + street);
  
	return street + city + province + country;
}

function setGeoResult(result)
{
var street_number = '';
var route = '';
	
	dpjQuery('#location-form #details input:not("#jform_title")').removeAttr('value');
	
	for(var i=0;i<result.address_components.length;i++){
		switch(result.address_components[i].types[0]){

			case 'route':
				dpjQuery("#jform_address").val(result.address_components[i].long_name);
				route = result.address_components[i].long_name;
			break;
			case 'locality':
				dpjQuery("#jform_location").val(result.address_components[i].long_name);
			break;
			case 'street_number':
			street_number = result.address_components[i].long_name;
			break;

		  case 'administrative_area_level_1':
			dpjQuery("#jform_state").val(result.address_components[i].long_name);
      dpjQuery("#extended_COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_1_LONG_NAME").val(result.address_components[i].long_name);
      dpjQuery("#extended_COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_1_SHORT_NAME").val(result.address_components[i].short_name);
			break;
      case 'administrative_area_level_2':
      dpjQuery("#extended_COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_2_LONG_NAME").val(result.address_components[i].long_name);
      dpjQuery("#extended_COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_2_SHORT_NAME").val(result.address_components[i].short_name);
			break;
      
      
      
      
//			case 'country':
//				dpjQuery("#jform_country").val(result.address_components[i].long_name);
//			break;
			case 'postal_code':
				dpjQuery("#jform_zipcode").val(result.address_components[i].long_name);
			break;
		}
	}

route += ' ';
route += street_number;
dpjQuery("#jform_address").val(route);
	
	if (typeof result.geometry.location.lat === 'function')
	{
		dpjQuery("#jform_latitude").val(result.geometry.location.lat());
		dpjQuery("#jform_longitude").val(result.geometry.location.lng());
	} else
	{		
		dpjQuery("#jform_latitude").val(result.geometry.location.lat);
		dpjQuery("#jform_longitude").val(result.geometry.location.lng);
	}

var lat = dpjQuery("#jform_latitude").val();
var lng = dpjQuery("#jform_longitude").val();	
console.log('lat ' + lat );
console.log('lng ' + lng );
addLayer(lat,lng);
// Creating a marker
//var marker = L.marker([lat , lng ]);
// Adding marker to the map
//marker.addTo(map);



//	if (dpjQuery("#jform_title").val() == '')
//	{
//		dpjQuery("#jform_title").val(result.formatted_address);
//	}
	
	dpjQuery("#jform_geocomplete").val(result.formatted_address);
}

function addLayer(lat,lng) {
	
var markerLocation = new L.LatLng(lat,lng);
var marker = new L.Marker(markerLocation);
//console.log(marker);
console.log("Adding layer");


    
//L.marker([lat, lng]).addTo(layerGroup);
console.log(layerGroup);
map.removeLayer(layerGroup);
//Add a marker to show where you clicked.
theMarker = L.marker([lat,lng]).addTo(map);  
//L.marker([lat, lng]).addTo(layerGroup);
//layerGroup.addLayer(marker);



//var mbAttr = 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, ' +
//					'<a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, ' +
//					'Imagery © <a href="http://mapbox.com">Mapbox</a>',
//mbUrl = 'https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token=pk.eyJ1IjoibWFwYm94IiwiYSI6ImNpejY4NXVycTA2emYycXBndHRqcmZ3N3gifQ.rJcFIG214AriISLbB6B5aw';
//var grayscale   = L.tileLayer(mbUrl, {id: 'mapbox.light', attribution: mbAttr}),
//				streets  = L.tileLayer(mbUrl, {id: 'mapbox.streets',   attribution: mbAttr});

//var baseLayers = {
//				"Grayscale": grayscale,
//				"Streets": streets
//			};
//var baseControl = L.control.layers(baseLayers).addTo(map);
			// make a global control variable for the control with the cities layer...
			//var citiesControl;	
			//	console.log("Adding layer");
//				cities = L.layerGroup();
				//L.marker([39.61, -105.02]).bindPopup('This is Littleton, CO.').addTo(cities),
				//L.marker([39.74, -104.99]).bindPopup('This is Denver, CO.').addTo(cities),
				//L.marker([39.73, -104.8]).bindPopup('This is Aurora, CO.').addTo(cities),
				//L.marker([39.77, -105.23]).bindPopup('This is Golden, CO.').addTo(cities);
				//var overlays = {
//					"Cities": cities
				//};
//console.log(cities);
				//map.addLayer(cities);

				// remove the current control panel
				//map.removeControl(baseControl);
				// add one with the cities
				//citiesControl = L.control.layers(baseLayers, overlays).addTo(map);
			}
			