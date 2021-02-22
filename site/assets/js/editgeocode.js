var dpjQuery = jQuery.noConflict();
var cities;
var country;			
var countryleafletsearch;
var countryleaflet;

var address;
var street;
var state;
var city;
var zip;
var province;
var baseurl;
var yourQuery;	


dpjQuery(document).ready(function(){
console.log("editgeocode document.location.origin : "+document.location.origin);  
baseurl = document.location.origin;  
dpjQuery("#geocomplete").val(getAddresString());
getlatlonopenstreet(1);  
  
  

//dpjQuery(this).attr("href");
//console.log('href ' + dpjQuery(this).attr("href") );
  
}); 



	/*
dpjQuery(document).ready(function(){
dpjQuery("#geocomplete").val(getAddresString());

console.log('latitude ' + dpjQuery("#latitude").val() );  
console.log('longitude ' + dpjQuery("#longitude").val() );  
getlatlonopenstreet(1);

//geocoder = new L.Control.Geocoder.Nominatim();
if (dpjQuery('#address_country').length == 0) {
console.log('Das Element mit der ID address_country ist nicht vorhanden.');	
countryleaflet = dpjQuery("#country").val();	
}
	else
	{
console.log('Das Element mit der ID address_country ist vorhanden.');		
countryleaflet = dpjQuery("#address_country").val();
	}
	
console.log('ready countryleaflet ' + countryleaflet);
var url = 'administrator/index.php?option=com_sportsmanagement&format=json&tmpl=component&task=ajax.getCcountryAlpha2&country=' + countryleaflet;
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

street = dpjQuery("#address").val();
zip = dpjQuery("#zipcode").val();
	
if (dpjQuery('#city').length == 0) {
console.log('Das Element mit der ID city ist nicht vorhanden.');
city = dpjQuery("#location").val();
}
else	
{	
city = dpjQuery("#city").val();
}	
yourQuery = ( street + ',' + zip + ' ' + city + ',' + countryleafletsearch );

console.log('ready yourQuery ' + yourQuery );

  
});
*/

function getlatlonopenstreet(result)
{
dpjQuery("#geocomplete").val(getAddresString());
dpjQuery("#geocomplete").trigger("geocode");	
	
	
var inp = dpjQuery("#geocomplete").val();
console.log('geocomplete ' + inp );
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

console.log('121 county ' + val.address.county);
console.log('state_district ' + val.address.state_district);
console.log('state ' + val.address.state);
console.log('city_district ' + val.address.city_district);

console.log('postcode ' + val.address.postcode);
console.log('road ' + val.address.road);
console.log('suburb ' + val.address.suburb);
console.log('neighbourhood ' + val.address.neighbourhood);


state = val.address.state;

dpjQuery("#extended_COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_1_LONG_NAME").val(state);
dpjQuery("#state").val(state);	

if ( val.address.county )
{
dpjQuery("#extended_COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_2_LONG_NAME").val(val.address.county);	
dpjQuery("#state").val(val.address.county);	
}	
if ( val.address.state_district )
{
dpjQuery("#extended_COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_3_LONG_NAME").val(val.address.state_district);	
}	
	
dpjQuery("#latitude").val(val.lat);
dpjQuery("#longitude").val(val.lon);
/*
if ( result )
{
addLayer(val.lat,val.lon);
}
*/
}); 
}
});

}

function getAddresString()
{

	street = '';
	city = '';
	country = '';
	if(dpjQuery("#address").val()){
		street = dpjQuery("#address").val();
		street += ', ';
	}
	
if (dpjQuery('#city').length == 0) {
console.log('Das Element mit der ID city ist nicht vorhanden.');
	if(dpjQuery("#location").val()){
		city = dpjQuery("#location").val();
		if(dpjQuery("#zipcode").val()){
			city += ' ' + dpjQuery("#zipcode").val();
		}
		city += ', ';
	}
}
	else
	{
console.log('Das Element mit der ID city ist vorhanden.');		
	if(dpjQuery("#city").val()){
		city = dpjQuery("#city").val();
		if(dpjQuery("#zipcode").val()){
			city += ' ' + dpjQuery("#zipcode").val();
		}
		city += ', ';
	}
	}
	
	if (dpjQuery("#state").val()) {
		province = dpjQuery("#state").val() + ', ';
	}
	/*
if (dpjQuery('#address_country').length == 0) {
console.log('Das Element mit der ID address_country ist nicht vorhanden.');	
countryleaflet = dpjQuery("#country").val();	
}
	else
	{
console.log('Das Element mit der ID address_country ist vorhanden.');		
countryleaflet = dpjQuery("#address_country").val();
	}
*/
countryleaflet = dpjQuery("#country").val();	
console.log('getAddresString countryleaflet ' + countryleaflet);

var url = baseurl + '/administrator/index.php?option=com_sportsmanagement&format=json&tmpl=component&task=ajax.getCcountryAlpha2&country=' + countryleaflet;
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

var url2 = baseurl +'/administrator/index.php?option=com_sportsmanagement&format=json&tmpl=component&task=ajax.getCcountryName&country=' + countryleaflet;
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
	
	dpjQuery('#location-form #details input:not("#title")').removeAttr('value');
	
	for(var i=0;i<result.address_components.length;i++){
		switch(result.address_components[i].types[0]){

			case 'route':
				dpjQuery("#address").val(result.address_components[i].long_name);
				route = result.address_components[i].long_name;
			break;
			case 'locality':
				dpjQuery("#city").val(result.address_components[i].long_name);
			break;
			case 'street_number':
			street_number = result.address_components[i].long_name;
			break;

		  case 'administrative_area_level_1':
			dpjQuery("#state").val(result.address_components[i].long_name);
      dpjQuery("#extended_COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_1_LONG_NAME").val(result.address_components[i].long_name);
      dpjQuery("#extended_COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_1_SHORT_NAME").val(result.address_components[i].short_name);
			break;
      case 'administrative_area_level_2':
      dpjQuery("#extended_COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_2_LONG_NAME").val(result.address_components[i].long_name);
      dpjQuery("#extended_COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_2_SHORT_NAME").val(result.address_components[i].short_name);
			break;
      
			case 'postal_code':
				dpjQuery("#zipcode").val(result.address_components[i].long_name);
			break;
		}
	}

route += ' ';
route += street_number;
dpjQuery("#address").val(route);
	
	if (typeof result.geometry.location.lat === 'function')
	{
		dpjQuery("#latitude").val(result.geometry.location.lat());
		dpjQuery("#longitude").val(result.geometry.location.lng());
	} else
	{		
		dpjQuery("#latitude").val(result.geometry.location.lat);
		dpjQuery("#longitude").val(result.geometry.location.lng);
	}

var lat = dpjQuery("#latitude").val();
var lng = dpjQuery("#longitude").val();	
console.log('lat ' + lat );
console.log('lng ' + lng );
//addLayer(lat,lng);
	
	dpjQuery("#geocomplete").val(result.formatted_address);
}

/*
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
	*/		
