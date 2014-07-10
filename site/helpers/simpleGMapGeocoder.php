<?php
defined('_JEXEC') or die('Restricted access');
jimport('joomla.filesystem.file');

/**
* simpleGMapGeocoder | simpleGMapGeocoder is part of simpleGMapAPI
*                      Heiko Holtkamp, 2010
*
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program; if not, write to the Free Software
* Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA 
*
*
* simpleGMapGeocoder
* is used for geocoding and is part of simpleGMapAPI
*
* @class        simpleGMapGeocoder
* @author       Heiko Holtkamp <heiko@rvs.uni-bielefeld.de>
* @version      0.1.3
* @copyright    2010 HH
*/

/**
 * JSMsimpleGMapGeocoder
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class JSMsimpleGMapGeocoder {

/**
 * JSMsimpleGMapGeocoder::JLgetGeoCoords()
 * 
 * @param mixed $address
 * @return
 */
function JLgetGeoCoords($address)
{
    $coords = array();
   
    // call geoencoding api with param json for output
    $geoCodeURL = "http://maps.google.com/maps/api/geocode/json?address=".
                  urlencode($address)."&sensor=false";
/*    
    $result = json_decode(file_get_contents($geoCodeURL), true);
    echo 'getGeoCoords result<br><pre>';
    print_r($result);
    echo '</pre><br>';
*/
$initial = curl_init();
curl_setopt($initial, CURLOPT_URL, $geoCodeURL);
curl_setopt($initial, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($initial, CURLOPT_CONNECTTIMEOUT, 5);
$file_content = curl_exec($initial);
curl_close($initial);
$result = json_decode($file_content, true);    
    
//    echo 'JLgetGeoCoords result<br><pre>'.print_r($result,true).'</pre><br>';
    
    /*
    $coords['status'] = $result["status"];
    
    if ( isset($result["results"][0]) )
    {        
    $coords['lat'] = $result["results"][0]["geometry"]["location"]["lat"];
    $coords['lng'] = $result["results"][0]["geometry"]["location"]["lng"];
    }
    */
    
    return $result;
}

/**
 * JSMsimpleGMapGeocoder::getGeoCoordsMapQuest()
 * 
 * @param mixed $address_string
 * @return void
 */
function getGeoCoordsMapQuest($address_string)
{

$geoCodeURL = "http://open.mapquestapi.com/geocoding/v1/address?key=Fmjtd%7Cluub2g6r20%2Crl%3Do5-9uaxu4&location=".
                  urlencode($address_string)."&callback=renderGeocode&outFormat=json";    

$initial = curl_init();
curl_setopt($initial, CURLOPT_URL, $geoCodeURL);
curl_setopt($initial, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($initial, CURLOPT_CONNECTTIMEOUT, 5);
$file_content = curl_exec($initial);
curl_close($initial);
$result = json_decode($file_content, true);    
    
echo 'getGeoCoordsMapQuest result<br><pre>'.print_r($result,true).'</pre><br>';
        
}


function genkml3file($id, $address_string, $type, $picture, $name,$latitude = 255,$longitude = 255)
{
$params		 	=	JComponentHelper::getParams('com_sportsmanagement');
$ph_logo_big	=	$params->get('ph_logo_big',0);
    
$lat = '';
$lng = '';

if ( $latitude == 255 )
{ 
$coords = $this->JLgetGeoCoords($address_string);
		
        if ( $coords["status"] == 'OK')
		{
    $lat = $coords["results"][0]["geometry"]["location"]["lat"];
    $lng = $coords["results"][0]["geometry"]["location"]["lng"];
    }
	  else
	  {
	  $osm = $this->getOSMGeoCoords($address_string);
    
    if ( $osm )
    {
    $lat = $osm['lat'];
    $lng = $osm['lng'];
    }
    else
    {
    $mapquest = $this->getGeoCoordsMapQuest($address_string);    
    $lat = '';
    $lng = '';
    }
    
    }
}
else
{
    $lat = $latitude;
$lng = $longitude;
}
// Creates an array of strings to hold the lines of the KML file.
$kml = array('<?xml version="1.0" encoding="UTF-8"?>');
$kml[] = '<kml xmlns="http://earth.google.com/kml/2.1">';
$kml[] = ' <Document>';
$kml[] = ' <Style id="' . $id . 'Style">';
$kml[] = ' <IconStyle id="' . $id . 'Icon">';
$kml[] = ' <Icon>';

//$picturepath = JUri::root().$row->logo_big;
$picturepath = JPATH_SITE.DS.$picture;
if ( !file_exists($picturepath) || !$picture  )
{
$kml[] = ' <href>' . JUri::root().$ph_logo_big . '</href>';    
}
else
{
$kml[] = ' <href>' . JUri::root().$picture . '</href>';    
}

$kml[] = ' </Icon>';
$kml[] = ' </IconStyle>';
$kml[] = ' </Style>';    
if ( $lng )
{
$kml[] = ' <Placemark id="placemark' . $id . '">';
//$kml[] = ' <name>' . htmlentities($row->team_name) . '</name>';
//$kml[] = ' <description>' . htmlentities($row->address_string) . '</description>';
$kml[] = ' <name>' . $name . '</name>';
$kml[] = ' <description>' . $address_string . '</description>';
$kml[] = ' <address>' . $address_string . '</address>';
//$kml[] = ' <styleUrl>#' . ($row->type) .'Style</styleUrl>';
$kml[] = ' <styleUrl>#' . ($id) .'Style</styleUrl>';
$kml[] = ' <Point>';
$kml[] = ' <coordinates>' . $lng . ','  . $lat . '</coordinates>';
$kml[] = ' </Point>';
$kml[] = ' </Placemark>';
}

// End XML file
$kml[] = ' </Document>';
$kml[] = '</kml>';
$kmlOutput = join("\n", $kml);

// mal als test
$xmlfile = $kmlOutput;
$file = JPATH_SITE.DS.'tmp'.DS.$id.'-'.$type.'.kml';
JFile::write($file, $xmlfile);




    
}

/**
 * JSMsimpleGMapGeocoder::genkml3prediction()
 * 
 * @param mixed $project_id
 * @param mixed $allmembers
 * @return void
 */
function genkml3prediction($project_id,$allmembers)
{
$type = 'prediction';


/*
echo 'genkml3prediction allmembers<br><pre>';
    print_r($allmembers);
    echo '</pre><br>';
*/

foreach ( $allmembers as $row )
{
if ( isset($row->latitude) )
{
if ( $row->latitude != 255 )
{
$row->lat = $row->latitude;
$row->lng = $row->longitude;
}
else
{
$row->lat = '';
$row->lng = '';    
}
 
$row->address_string = '';   
}
else
{        
$address_parts = array();
		if (!empty($row->cb_streetaddress))
		{
			$address_parts[] = $row->cb_streetaddress;
		}
		if (!empty($row->cb_state))
		{
			$address_parts[] = $row->cb_state;
		}
		if (!empty($row->cb_city))
		{
			if (!empty($row->cb_zip))
			{
				$address_parts[] = $row->cb_zip. ' ' .$row->cb_city;
			}
			else
			{
				$address_parts[] = $row->cb_city;
			}
		}
		if (!empty($row->cb_country))
		{
			$address_parts[] = JSMCountries::getShortCountryName($row->cb_country);
		}
		$row->address_string = implode(', ', $address_parts);
		$row->type = 'bar';
		$coords = $this->JLgetGeoCoords($row->address_string);
		if ( $coords["status"] == 'OK')
		{
    $row->lat = $coords["results"][0]["geometry"]["location"]["lat"];
    $row->lng = $coords["results"][0]["geometry"]["location"]["lng"];
    }
	  else
	  {
	  $osm = $this->getOSMGeoCoords($row->address_string);
    
    if ( $osm )
    {
    $row->lat = $osm['lat'];
    $row->lng = $osm['lng'];
    }
    else
    {
    $row->lat = '';
    $row->lng = '';
    }
    
    }
}
}

$this->writekml3prediction($allmembers,$project_id,$type);


}
    
/**
 * JSMsimpleGMapGeocoder::genkml3()
 * 
 * @param mixed $project_id
 * @param mixed $allteams
 * @return void
 */
function genkml3($project_id,$allteams)
{
$type = 'ranking';
    
/*
echo 'genkml3 project_id<br><pre>';
    print_r($project_id);
    echo '</pre><br>';
echo 'genkml3 allteams<br><pre>';
    print_r($allteams);
    echo '</pre><br>';
*/

/*
echo 'genkml3 allteams<br><pre>';
    print_r($allteams);
    echo '</pre><br>';
*/

foreach ( $allteams as $row )
{
$address_parts = array();
		if (!empty($row->club_address))
		{
			$address_parts[] = $row->club_address;
		}
		if (!empty($row->club_state))
		{
			$address_parts[] = $row->club_state;
		}
		if (!empty($row->club_location))
		{
			if (!empty($row->club_zipcode))
			{
				$address_parts[] = $row->club_zipcode. ' ' .$row->club_location;
			}
			else
			{
				$address_parts[] = $row->club_location;
			}
		}
		if (!empty($row->club_country))
		{
			$address_parts[] = JSMCountries::getShortCountryName($row->club_country);
		}
        
        
		$row->address_string = implode(', ', $address_parts);
		$row->type = 'bar';
		
        
        if ( $row->latitude == 255 )
		{
		  
        $coords = $this->JLgetGeoCoords($row->address_string);
		if ( $coords["status"] == 'OK')
		{
    $row->lat = $coords["results"][0]["geometry"]["location"]["lat"];
    $row->lng = $coords["results"][0]["geometry"]["location"]["lng"];
    }
	  else
	  {
	  $osm = $this->getOSMGeoCoords($row->address_string);
    
    if ( $osm )
    {
    $row->lat = $osm['lat'];
    $row->lng = $osm['lng'];
    }
    else
    {
    $row->lat = '';
    $row->lng = '';
    }
    
    
    }

}
else
{
    $row->lat = $row->latitude;
    $row->lng = $row->longitude;    
    
}

/*
echo 'genkml3 allteams<br><pre>';
    print_r($coords);
    echo '</pre><br>';		
*/


}

/*
echo 'genkml3 allteams<br><pre>';
    print_r($allteams);
    echo '</pre><br>';
*/

$this->writekml3($allteams,$project_id,$type);
    
}
    
/**
* @function     getGeoCoords
* @param        $address : string
* @returns      -
* @description  Gets GeoCoords by calling the Google Maps geoencoding API
*/
function getGeoCoords($address)
{
    $coords = array();
    
    /*
      OBSOLETE, now using utf8_encode
      
      // replace special characters (eg. German "Umlaute")
      $address = str_replace("�", "ae", $address);
      $address = str_replace("�", "oe", $address);
      $address = str_replace("�", "ue", $address);
      $address = str_replace("�", "Ae", $address);
      $address = str_replace("�", "Oe", $address);
      $address = str_replace("�", "Ue", $address);
      $address = str_replace("�", "ss", $address);
    */
    
    //$address = utf8_encode($address);
    
    //echo 'getGeoCoords address -> '.$address.'<br>';
    
    // call geoencoding api with param json for output
    $geoCodeURL = "http://maps.google.com/maps/api/geocode/json?address=".
                  urlencode($address)."&sensor=false";
    
//    $result = json_decode(file_get_contents($geoCodeURL), true);
$initial = curl_init();
curl_setopt($initial, CURLOPT_URL, $geoCodeURL);
curl_setopt($initial, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($initial, CURLOPT_CONNECTTIMEOUT, 5);
$file_content = curl_exec($initial);
curl_close($initial);
$result = json_decode($file_content, true);
    
    /*
    echo 'getGeoCoords result<br><pre>';
    print_r($result);
    echo '</pre><br>';
    */
    
    $coords['status'] = $result["status"];
    
    if ( isset($result["results"][0]) )
    {        
    $coords['lat'] = $result["results"][0]["geometry"]["location"]["lat"];
    $coords['lng'] = $result["results"][0]["geometry"]["location"]["lng"];
    }
    
    return $coords;
}

/**
* WORK IN PROGRESS...
*
* @function     reverseGeoCode
* @param        $lat : string
* @param        $lng : string
* @returns      -
* @description  Gets Address for the given LatLng by calling the Google Maps geoencoding API
*/
function reverseGeoCode($lat,$lng)
{
    $address = array();
    
    // call geoencoding api with param json for output
    $geoCodeURL = "http://maps.google.com/maps/api/geocode/json?address=$lat,$lng&sensor=false";
    
    $result = json_decode(file_get_contents($geoCodeURL), true);
                
    $address['status'] = $result["status"];
    
    echo $geoCodeURL."<br />";
    print_r($result);
    
    return $address;
}

/**
* @function     getOSMGeoCoords
* @param        $address : string
* @returns      -
* @description  Gets GeoCoords by calling the OpenStreetMap geoencoding API
*/
function getOSMGeoCoords($address)
{
    $coords = array();
        
    //$address = utf8_encode($address);
    
    // call OSM geoencoding api
    // limit to one result (limit=1) without address details (addressdetails=0)
    // output in JSON
    $geoCodeURL = "http://nominatim.openstreetmap.org/search?format=json&limit=1&addressdetails=0&q=".
                  urlencode($address);
    
    $result = json_decode(file_get_contents($geoCodeURL), true);
//    echo 'getOSMGeoCoords result<br><pre>'.print_r($result,true).'</pre><br>';
    
    if ( isset($result[0]) )
    {        
    $coords['lat'] = $result[0]["lat"];
    $coords['lng'] = $result[0]["lon"];
    }
    

    return $coords;
}


function writekml3prediction($allmembers,$project_id,$type)
{
$params		 	=	JComponentHelper::getParams('com_sportsmanagement');
$ph_logo_big	=	$params->get('ph_player',0);
    
// Creates an array of strings to hold the lines of the KML file.
$kml = array('<?xml version="1.0" encoding="UTF-8"?>');
$kml[] = '<kml xmlns="http://earth.google.com/kml/2.1">';
$kml[] = ' <Document>';

/*
$kml[] = ' <Style id="restaurantStyle">';
$kml[] = ' <IconStyle id="restuarantIcon">';
$kml[] = ' <Icon>';
$kml[] = ' <href>http://maps.google.com/mapfiles/kml/pal2/icon49.png</href>';
$kml[] = ' </Icon>';
$kml[] = ' </IconStyle>';
$kml[] = ' </Style>';
$kml[] = ' <Style id="barStyle">';
$kml[] = ' <IconStyle id="barIcon">';
$kml[] = ' <Icon>';
$kml[] = ' <href>http://maps.google.com/mapfiles/kml/pal2/icon49.png</href>';
$kml[] = ' </Icon>';
$kml[] = ' </IconStyle>';
$kml[] = ' </Style>';
*/

foreach ( $allmembers as $row )
{
if ( $row->lng )
{    
// logo_big    
$kml[] = ' <Style id="' . $row->user_id . 'Style">';
$kml[] = ' <IconStyle id="' . $row->user_id . 'Icon">';
$kml[] = ' <Icon>';


$picturepath = JPATH_SITE.DS.$row->avatar;

/*
echo 'writekml3prediction picturepath<br><pre>';
    print_r($picturepath);
    echo '</pre><br>';

echo 'writekml3prediction avatar<br><pre>';
    print_r($row->avatar);
    echo '</pre><br>';
*/

if ( !file_exists($picturepath) || empty($row->avatar) )
{
$kml[] = ' <href>' . JUri::root().$ph_logo_big . '</href>';    
}
else
{
$kml[] = ' <href>' . JUri::root().$row->avatar . '</href>';    
}

$kml[] = ' </Icon>';
$kml[] = ' </IconStyle>';
$kml[] = ' </Style>';  
}  
}    

$kml[] = ' <Folder>';
$kml[] = ' <open>1</open>';
foreach ( $allmembers as $row )
{
if ( $row->lng )
{
$kml[] = ' <Placemark id="placemark' . $row->user_id . '">';
$kml[] = ' <open>1</open>';
//$kml[] = ' <name>' . htmlentities($row->team_name) . '</name>';
//$kml[] = ' <description>' . htmlentities($row->address_string) . '</description>';
$kml[] = ' <name>' . $row->name . '</name>';
$kml[] = ' <description>' . $row->address_string . '</description>';
$kml[] = ' <address>' . $row->address_string . '</address>';
//$kml[] = ' <styleUrl>#' . ($row->type) .'Style</styleUrl>';
$kml[] = ' <styleUrl>#' . ($row->user_id) .'Style</styleUrl>';
$kml[] = ' <Point>';
$kml[] = ' <coordinates>' . $row->lng . ','  . $row->lat . '</coordinates>';
$kml[] = ' </Point>';
$kml[] = ' </Placemark>';
}

}

$kml[] = ' </Folder>';

// End XML file
$kml[] = ' </Document>';
$kml[] = '</kml>';
$kmlOutput = join("\n", $kml);

// mal als test
$xmlfile = $kmlOutput;
$file = JPATH_SITE.DS.'tmp'.DS.$project_id.'-'.$type.'.kml';
JFile::write($file, $xmlfile);

}

function writekml3($allteams,$project_id,$type)
{
$params		 	=	JComponentHelper::getParams('com_sportsmanagement');
$ph_logo_big	=	$params->get('ph_logo_big',0);
    
// Creates an array of strings to hold the lines of the KML file.
$kml = array('<?xml version="1.0" encoding="UTF-8"?>');
$kml[] = '<kml xmlns="http://earth.google.com/kml/2.1">';
$kml[] = ' <Document>';

/*
$kml[] = ' <Style id="restaurantStyle">';
$kml[] = ' <IconStyle id="restuarantIcon">';
$kml[] = ' <Icon>';
$kml[] = ' <href>http://maps.google.com/mapfiles/kml/pal2/icon49.png</href>';
$kml[] = ' </Icon>';
$kml[] = ' </IconStyle>';
$kml[] = ' </Style>';
$kml[] = ' <Style id="barStyle">';
$kml[] = ' <IconStyle id="barIcon">';
$kml[] = ' <Icon>';
$kml[] = ' <href>http://maps.google.com/mapfiles/kml/pal2/icon49.png</href>';
$kml[] = ' </Icon>';
$kml[] = ' </IconStyle>';
$kml[] = ' </Style>';
*/

foreach ( $allteams as $row )
{
// logo_big    
$kml[] = ' <Style id="' . $row->team_id . 'Style">';
$kml[] = ' <IconStyle id="' . $row->team_id . 'Icon">';
$kml[] = ' <Icon>';

//$picturepath = JUri::root().$row->logo_big;
$picturepath = JPATH_SITE.DS.$row->logo_big;
if ( !file_exists($picturepath) )
{
$kml[] = ' <href>' . JUri::root().$ph_logo_big . '</href>';    
}
else
{
$kml[] = ' <href>' . JUri::root().$row->logo_big . '</href>';    
}

$kml[] = ' </Icon>';
$kml[] = ' </IconStyle>';
$kml[] = ' </Style>';    
}    

$kml[] = ' <Folder>';
$kml[] = ' <open>1</open>';
foreach ( $allteams as $row )
{
if ( $row->lng )
{
$kml[] = ' <Placemark id="placemark' . $row->team_id . '">';
$kml[] = ' <open>1</open>';
//$kml[] = ' <name>' . htmlentities($row->team_name) . '</name>';
//$kml[] = ' <description>' . htmlentities($row->address_string) . '</description>';
$kml[] = ' <name>' . $row->team_name . '</name>';
$kml[] = ' <description>' . $row->address_string . '</description>';
$kml[] = ' <address>' . $row->address_string . '</address>';
//$kml[] = ' <styleUrl>#' . ($row->type) .'Style</styleUrl>';
$kml[] = ' <styleUrl>#' . ($row->team_id) .'Style</styleUrl>';
$kml[] = ' <Point>';
$kml[] = ' <coordinates>' . $row->lng . ','  . $row->lat . '</coordinates>';
$kml[] = ' </Point>';
$kml[] = ' </Placemark>';
}

}

$kml[] = ' </Folder>';

// End XML file
$kml[] = ' </Document>';
$kml[] = '</kml>';
$kmlOutput = join("\n", $kml);

// mal als test
$xmlfile = $kmlOutput;
$file = JPATH_SITE.DS.'tmp'.DS.$project_id.'-'.$type.'.kml';
JFile::write($file, $xmlfile);

}


} // end of class

?>