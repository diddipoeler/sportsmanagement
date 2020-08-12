// http://bl.ocks.org/andyreagan/c81461c8a8ce52d103fc92decf9650b6

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

var yourQuery;	


function getbackendlatlon()
{
dpjQuery("#jform_geocomplete").val(getAddresString());
dpjQuery("#jform_geocomplete").trigger("geocode");
if ( opencagekey != '' )
{
geocode(dpjQuery("#jform_geocomplete").val());	
}
else
{
getlatlonopenstreet(1);
}


}


function geocode(query){
      dpjQuery.ajax({
        url: 'https://api.opencagedata.com/geocode/v1/json',
        method: 'GET',
        data: {
          'key': opencagekey ,
          'q': query,
          'no_annotations': 1
          // see other optional params:
          // https://opencagedata.com/api#forward-opt
        },
        dataType: 'json',
        statusCode: {
          200: function(response){  // success
	console.log('opencagedata');
            console.log(response);
            console.log(response.results[0].formatted);
console.log(response.results[0].geometry.lat);
            console.log(response.results[0].geometry.lng);
            
            console.log(response.results[0].components.county);
            console.log(response.results[0].components.state);
            console.log(response.results[0].components.state_district);	
//state = val.address.state;
dpjQuery("#extended_COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_1_LONG_NAME").val(response.results[0].components.state);
dpjQuery("#jform_state").val(response.results[0].components.state);	
dpjQuery("#extended_COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_2_LONG_NAME").val(response.results[0].components.county);	
dpjQuery("#extended_COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_3_LONG_NAME").val(response.results[0].components.state_district);	
dpjQuery("#jform_latitude").val(response.results[0].geometry.lat);
dpjQuery("#jform_longitude").val(response.results[0].geometry.lng);
addLayer(response.results[0].geometry.lat,response.results[0].geometry.lng);

          },
          402: function(){
            console.log('hit free-trial daily limit');
            console.log('become a customer: https://opencagedata.com/pricing');
          }
          // other possible response codes:
          // https://opencagedata.com/api#codes
        }
      });
    }     


dpjQuery(document).ready(function(){
dpjQuery("#jform_geocomplete").val(getAddresString());
addLayer('0.00000000','0.00000000');
if ( opencagekey != '' )
{
geocode(dpjQuery("#jform_geocomplete").val());	
}
else
{	
getlatlonopenstreet(0);
}

countryleaflet = dpjQuery("#jform_country").val();
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
city = dpjQuery("#jform_location").val();
yourQuery = ( street + ',' + zip + ' ' + city + ',' + countryleafletsearch );

console.log('ready yourQuery ' + yourQuery );
/*
dpjQuery('#jform_address,  #jform_zipcode, #jform_location,  #jform_state, #jform_country').bind('change', function(e) {
dpjQuery("#jform_geocomplete").val(getAddresString());
dpjQuery("#jform_geocomplete").trigger("geocode");
if ( opencagekey != '' )
{
geocode(dpjQuery("#jform_geocomplete").val());	
}
else
{
getlatlonopenstreet(1);
}
	});
    */
});


function getlatlonopenstreet(result)
{
//var inp = dpjQuery("#jform_geocomplete").val();
var inp = encodeURI(dpjQuery("#jform_geocomplete").val());	
console.log('jform_geocomplete ' + inp );
//var xmlhttp = new XMLHttpRequest();
var url = "https://nominatim.openstreetmap.org/search?format=json&addressdetails=1&limit=1&q=" + inp ;
	
var opencageurl = opencage  + dpjQuery("#jform_geocomplete").val();
	
console.log('openstreetmap url ' + url );
console.log('opencage url ' + opencageurl );	
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

console.log('county ' + val.address.county);
console.log('state_district ' + val.address.state_district);
console.log('state ' + val.address.state);
console.log('city_district ' + val.address.city_district);

console.log('postcode ' + val.address.postcode);
console.log('road ' + val.address.road);
console.log('suburb ' + val.address.suburb);
console.log('neighbourhood ' + val.address.neighbourhood);


state = val.address.state;

dpjQuery("#extended_COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_1_LONG_NAME").val(state);
dpjQuery("#jform_state").val(state);	

if ( val.address.county )
{
dpjQuery("#extended_COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_2_LONG_NAME").val(val.address.county);	
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

countryleaflet = dpjQuery("#jform_country").val();
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
console.log("Adding layer");
console.log(lat);
console.log(lng);
    
//Add a marker to show where you clicked.
theMarker = L.marker([lat,lng]).addTo(map);  
map.setView(new L.LatLng(lat, lng), 15);	

}
			
