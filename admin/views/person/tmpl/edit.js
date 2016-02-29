var dpjQuery = jQuery.noConflict();

dpjQuery(document).ready(function(){
	dpjQuery('#jform_address,  #jform_zipcode, #jform_location, #jform_address_country, #jform_state').bind('change', function(e) {
		dpjQuery("#jform_geocomplete").val(getAddresString());
		dpjQuery("#jform_geocomplete").trigger("geocode");
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
       "http://maps.google.com/mapfiles/ms/micons/man.png",
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
  
  if(dpjQuery("#jform_address_country").val()){
		country = dpjQuery("#jform_address_country :selected").text() + ', ';
	}
  
	return street + city + province + country;
}

function setGeoResult(result)
{
var street_number = '';
var route = '';	
	dpjQuery('#location-form #details input:not("#jform_title")').removeAttr('value');
	
  
//alert(result.address_components);
  
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
//				dpjQuery("#jform_address_country").val(result.address_components[i].long_name);
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
	

//	if (dpjQuery("#jform_title").val() == '')
//	{
//		dpjQuery("#jform_title").val(result.formatted_address);
//	}
	
	dpjQuery("#jform_geocomplete").val(result.formatted_address);
}
