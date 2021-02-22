var dpjQuery = jQuery.noConflict();
var cities;
var country;			
var countryleafletsearch;
var countryleaflet;

var address;
var street;
var state;
var municipality;
var city;
var zip;
var province;

var yourQuery;	
	
dpjQuery(document).ready(function(){
dpjQuery("#jform_geocomplete").val(getAddresString());

console.log('latitude ' + dpjQuery("#jform_latitude").val() );  
console.log('longitude ' + dpjQuery("#jform_longitude").val() );  
getlatlonopenstreet(1);
  /*
if ( dpjQuery("#jform_latitude").val() )
{
addLayer(dpjQuery("#jform_latitude").val(),dpjQuery("#jform_longitude").val());
}
else
{
getlatlonopenstreet(1);
}	
*/	

//geocoder = new L.Control.Geocoder.Nominatim();
if (dpjQuery('#jform_address_country').length == 0) {
console.log('Das Element mit der ID jform_address_country ist nicht vorhanden.');	
countryleaflet = dpjQuery("#jform_country").val();	
}
	else
	{
console.log('Das Element mit der ID jform_address_country ist vorhanden.');		
countryleaflet = dpjQuery("#jform_address_country").val();
	}
	
console.log('ready countryleaflet ' + countryleaflet);
var url = 'index.php?option=com_sportsmanagement&format=json&tmpl=component&task=ajax.getCcountryAlpha2&country=' + countryleaflet;
dpjQuery.ajax({
url: url,
dataType: 'json',
async: false,
type : 'POST'
}).done(function(data1) {
console.log(data1);
dpjQuery.each(data1, function (i, val) {
console.log('ready i ' + i);
console.log('ready text ' + val.text);
countryleafletsearch = val.text;
});
});
console.log('ready url ' + url );
console.log('ready countryleafletsearch ' + countryleafletsearch );

street = dpjQuery("#jform_address").val();
zip = dpjQuery("#jform_zipcode").val();
	
if (dpjQuery('#jform_city').length == 0) {
console.log('Das Element mit der ID jform_city ist nicht vorhanden.');
city = dpjQuery("#jform_location").val();
}
else	
{	
city = dpjQuery("#jform_city").val();
}	
yourQuery = ( street + ',' + zip + ' ' + city + ',' + countryleafletsearch );

console.log('ready yourQuery ' + yourQuery );

  
});


function getlatlonopenstreet(result)
{
dpjQuery("#jform_geocomplete").val(getAddresString());
dpjQuery("#jform_geocomplete").trigger("geocode");	

// es muss mindestens die stadt angegeben werden  
if (dpjQuery('#jform_city').length == 0) {
city = dpjQuery("#jform_location").val();
}
else	
{	
city = dpjQuery("#jform_city").val();
}	  
  
if (city.length == 0) {  
dpjQuery("#jform_latitude").val('0.00000000');
dpjQuery("#jform_longitude").val('0.00000000');	
addLayer('0.00000000','0.00000000');  
return '';  
}




	
	
var inp = dpjQuery("#jform_geocomplete").val();
console.log('jform_geocomplete ' + inp );
//var xmlhttp = new XMLHttpRequest();
var url = "https://nominatim.openstreetmap.org/search?format=json&addressdetails=1&limit=1&q=" + inp ;
console.log('openstreetmap url ' + url );
dpjQuery("#extended_COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_1_LONG_NAME").val('');
dpjQuery("#extended_COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_1_SHORT_NAME").val('');
dpjQuery("#extended_COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_2_LONG_NAME").val('');
dpjQuery("#extended_COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_2_SHORT_NAME").val('');
dpjQuery("#extended_COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_3_LONG_NAME").val('');
dpjQuery("#extended_COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_3_SHORT_NAME").val('');
	
dpjQuery.ajax({
url:url,
dataType: 'json',
async: false,
type: "POST",
success:function(res){
console.log('openstreetmap ' + res );

dpjQuery.each(res , function (i, val) {
console.log(i);
console.log(val);

console.log('latitude ' + val.lat);
console.log('longitude ' + val.lon);

console.log('134 county ' + val.address.county);
	
console.log('136 municipality ' + val.address.municipality);
	
console.log('state_district ' + val.address.state_district);
console.log('state ' + val.address.state);
console.log('city_district ' + val.address.city_district);

console.log('postcode ' + val.address.postcode);
console.log('road ' + val.address.road);
console.log('suburb ' + val.address.suburb);
console.log('neighbourhood ' + val.address.neighbourhood);


state = val.address.state;
municipality = val.address.municipality;	

dpjQuery("#extended_COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_1_LONG_NAME").val(state);
dpjQuery("#jform_state").val(municipality);	

if ( val.address.county )
{
dpjQuery("#extended_COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_2_LONG_NAME").val(val.address.county);	
//dpjQuery("#jform_state").val(val.address.county);	
}	
if ( val.address.state_district )
{
dpjQuery("#extended_COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_3_LONG_NAME").val(val.address.state_district);	
}	
	
dpjQuery("#jform_latitude").val(val.lat);
dpjQuery("#jform_longitude").val(val.lon);
if ( result )
{
addLayer(val.lat,val.lon);
}
}); 
}
});

}

function getAddresString()
{

	street = '';
	city = '';
	country = '';
	if(dpjQuery("#jform_address").val()){
		street = dpjQuery("#jform_address").val();
		street += ', ';
	}
	
if (dpjQuery('#jform_city').length == 0) {
console.log('Das Element mit der ID jform_city ist nicht vorhanden.');
	if(dpjQuery("#jform_location").val()){
		city = dpjQuery("#jform_location").val();
		if(dpjQuery("#jform_zipcode").val()){
			city += ' ' + dpjQuery("#jform_zipcode").val();
		}
		city += ', ';
	}
}
	else
	{
console.log('Das Element mit der ID jform_city ist vorhanden.');		
	if(dpjQuery("#jform_city").val()){
		city = dpjQuery("#jform_city").val();
		if(dpjQuery("#jform_zipcode").val()){
			city += ' ' + dpjQuery("#jform_zipcode").val();
		}
		city += ', ';
	}
	}
	
	if (dpjQuery("#jform_state").val()) {
		province = dpjQuery("#jform_state").val() + ', ';
	}
	
if (dpjQuery('#jform_address_country').length == 0) {
console.log('Das Element mit der ID jform_address_country ist nicht vorhanden.');	
countryleaflet = dpjQuery("#jform_country").val();	
}
	else
	{
console.log('Das Element mit der ID jform_address_country ist vorhanden.');		
countryleaflet = dpjQuery("#jform_address_country").val();
	}
	
console.log('getAddresString countryleaflet ' + countryleaflet);

var url = 'index.php?option=com_sportsmanagement&format=json&tmpl=component&task=ajax.getCcountryAlpha2&country=' + countryleaflet;
dpjQuery.ajax({
url: url,
dataType: 'json',
async: false,
type : 'POST'
}).done(function(data1) {
console.log(data1);

dpjQuery.each(data1, function (i, val) {
console.log(i);
console.log(val.text);

countryleafletsearch = val.text;

});

});	

var url2 = 'index.php?option=com_sportsmanagement&format=json&tmpl=component&task=ajax.getCcountryName&country=' + countryleaflet;
dpjQuery.ajax({
url: url2,
dataType: 'json',
async: false,
type : 'POST'
}).done(function(data2) {
console.log(data2);

dpjQuery.each(data2, function (i, val) {
console.log(i);
console.log(val.text);

country = val.text;

});

});	
	
	
console.log('getAddresString country alpha2 leaflet ' + countryleafletsearch );
console.log('getAddresString country  ' + country );	
console.log('getAddresString city  ' + city );	
console.log('getAddresString street ' + street);
  
	return street + city + country;
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
				dpjQuery("#jform_city").val(result.address_components[i].long_name);
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
	
	dpjQuery("#jform_geocomplete").val(result.formatted_address);
}

function addLayer(lat,lng) {
	
var markerLocation = new L.LatLng(lat,lng);
var marker = new L.Marker(markerLocation);
//console.log(marker);
console.log("Adding layer");


    
//L.marker([lat, lng]).addTo(layerGroup);
//console.log(layerGroup);
//map.removeLayer(layerGroup);
//Add a marker to show where you clicked.
theMarker = L.marker([lat,lng]).addTo(map);  
map.setView(new L.LatLng(lat, lng), 15);	
//L.marker([lat, lng]).addTo(layerGroup);
//layerGroup.addLayer(marker);

}
			
