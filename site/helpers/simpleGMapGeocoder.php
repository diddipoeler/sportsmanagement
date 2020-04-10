<?php
defined('_JEXEC') or die('Restricted access');
jimport('joomla.filesystem.file');

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Component\ComponentHelper;

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
 * @class     simpleGMapGeocoder
 * @author    Heiko Holtkamp <heiko@rvs.uni-bielefeld.de>
 * @version   0.1.3
 * @copyright 2010 HH
 */

/**
 * JSMsimpleGMapGeocoder
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class JSMsimpleGMapGeocoder
{

	/**
	 * JSMsimpleGMapGeocoder::getGeoCoordsMapQuest()
	 *
	 * @param   mixed  $address_string
	 *
	 * @return void
	 */
	function getGeoCoordsMapQuest($address_string)
	{

		$geoCodeURL = "http://open.mapquestapi.com/geocoding/v1/address?key=Fmjtd%7Cluub2g6r20%2Crl%3Do5-9uaxu4&location=" .
			urlencode($address_string) . "&callback=renderGeocode&outFormat=json";
		if (function_exists('curl_init'))
		{
			$initial = curl_init();
			curl_setopt($initial, CURLOPT_URL, $geoCodeURL);
			curl_setopt($initial, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($initial, CURLOPT_CONNECTTIMEOUT, 5);
			$file_content = curl_exec($initial);
			curl_close($initial);
			$result = json_decode($file_content, true);
		}

	}

	/**
	 * JSMsimpleGMapGeocoder::genkml3file()
	 *
	 * @param   mixed    $id
	 * @param   mixed    $address_string
	 * @param   mixed    $type
	 * @param   mixed    $picture
	 * @param   mixed    $name
	 * @param   integer  $latitude
	 * @param   integer  $longitude
	 *
	 * @return void
	 */
	function genkml3file($id, $address_string, $type, $picture, $name, $latitude = 255, $longitude = 255)
	{
		$params      = ComponentHelper::getParams('com_sportsmanagement');
		$ph_logo_big = $params->get('ph_logo_big', 0);

		$lat = $latitude;
		$lng = $longitude;

		// Creates an array of strings to hold the lines of the KML file.
		$kml   = array('<?xml version="1.0" encoding="UTF-8"?>');
		$kml[] = '<kml xmlns="http://earth.google.com/kml/2.1">';
		$kml[] = ' <Document>';
		$kml[] = ' <Style id="' . $id . 'Style">';
		$kml[] = ' <IconStyle id="' . $id . 'Icon">';
		$kml[] = ' <Icon>';

		switch ($type)
		{
			case 'playground':
				$kml[] = ' <href>' . 'http://maps.google.com/mapfiles/kml/pal2/icon49.png' . '</href>';
				break;
			default:
				$kml[] = ' <href>' . Uri::root() . $picture . '</href>';
				break;
		}

		$kml[] = ' </Icon>';
		$kml[] = ' </IconStyle>';
		$kml[] = ' </Style>';
		if ($lng)
		{
			$kml[] = ' <Placemark id="placemark' . $id . '">';
			//$kml[] = ' <name>' . htmlentities($row->team_name) . '</name>';
			//$kml[] = ' <description>' . htmlentities($row->address_string) . '</description>';
			$kml[] = ' <name>' . $name . '</name>';
			$kml[] = ' <description>' . $address_string . '</description>';
			$kml[] = ' <address>' . $address_string . '</address>';
			//$kml[] = ' <styleUrl>#' . ($row->type) .'Style</styleUrl>';
			$kml[] = ' <styleUrl>#' . ($id) . 'Style</styleUrl>';
			$kml[] = ' <Point>';
			$kml[] = ' <coordinates>' . $lng . ',' . $lat . '</coordinates>';
			$kml[] = ' </Point>';
			$kml[] = ' </Placemark>';
		}

		// End XML file
		$kml[]     = ' </Document>';
		$kml[]     = '</kml>';
		$kmlOutput = join("\n", $kml);

		// mal als test
		$xmlfile = $kmlOutput;
		$file    = JPATH_SITE . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . $id . '-' . $type . '.kml';
		File::write($file, $xmlfile);


	}

	/**
	 * JSMsimpleGMapGeocoder::genkml3prediction()
	 *
	 * @param   mixed  $project_id
	 * @param   mixed  $allmembers
	 *
	 * @return void
	 */
	function genkml3prediction($project_id, $allmembers)
	{
		$type = 'prediction';

		foreach ($allmembers as $row)
		{
			if (isset($row->latitude))
			{
				if ($row->latitude != 255)
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
						$address_parts[] = $row->cb_zip . ' ' . $row->cb_city;
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
				$row->type           = 'bar';
				$coords              = $this->JLgetGeoCoords($row->address_string);
				if ($coords["status"] == 'OK')
				{
					$row->lat = $coords["results"][0]["geometry"]["location"]["lat"];
					$row->lng = $coords["results"][0]["geometry"]["location"]["lng"];
				}
				else
				{
					$osm = $this->getOSMGeoCoords($row->address_string);

					if ($osm)
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

		$this->writekml3prediction($allmembers, $project_id, $type);


	}

	/**
	 * JSMsimpleGMapGeocoder::JLgetGeoCoords()
	 *
	 * @param   mixed  $address
	 *
	 * @return
	 */
	function JLgetGeoCoords($address)
	{
		$coords = array();
		$result = '';
		// call geoencoding api with param json for output
		$geoCodeURL = "http://maps.google.com/maps/api/geocode/json?address=" .
			urlencode($address) . "&sensor=false";

		if (function_exists('curl_init'))
		{
			$initial = curl_init();
			curl_setopt($initial, CURLOPT_URL, $geoCodeURL);
			curl_setopt($initial, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($initial, CURLOPT_CONNECTTIMEOUT, 5);
			$file_content = curl_exec($initial);
			curl_close($initial);
			$result = json_decode($file_content, true);
		}

		return $result;
	}

	/**
	 * @function     getOSMGeoCoords
	 *
	 * @param        $address  : string
	 *
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
		$geoCodeURL = "http://nominatim.openstreetmap.org/search?format=json&limit=1&addressdetails=0&q=" .
			urlencode($address);
		if (function_exists('curl_init'))
		{
			$initial = curl_init();
			curl_setopt($initial, CURLOPT_URL, $geoCodeURL);
			curl_setopt($initial, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($initial, CURLOPT_CONNECTTIMEOUT, 5);
			$file_content = curl_exec($initial);
			curl_close($initial);
			$result = json_decode($file_content, true);
		}

		if (isset($result[0]))
		{
			$coords['lat'] = $result[0]["lat"];
			$coords['lng'] = $result[0]["lon"];
		}


		return $coords;
	}

	/**
	 * JSMsimpleGMapGeocoder::writekml3prediction()
	 *
	 * @param   mixed  $allmembers
	 * @param   mixed  $project_id
	 * @param   mixed  $type
	 *
	 * @return void
	 */
	function writekml3prediction($allmembers, $project_id, $type)
	{
		$params      = ComponentHelper::getParams('com_sportsmanagement');
		$ph_logo_big = $params->get('ph_player', 0);

		// Creates an array of strings to hold the lines of the KML file.
		$kml   = array('<?xml version="1.0" encoding="UTF-8"?>');
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

		foreach ($allmembers as $row)
		{
			if ($row->lng)
			{
				// logo_big
				$kml[] = ' <Style id="' . $row->user_id . 'Style">';
				$kml[] = ' <IconStyle id="' . $row->user_id . 'Icon">';
				$kml[] = ' <Icon>';


				$picturepath = JPATH_SITE . DIRECTORY_SEPARATOR . $row->avatar;

				if (!file_exists($picturepath) || empty($row->avatar))
				{
					$kml[] = ' <href>' . Uri::root() . $ph_logo_big . '</href>';
				}
				else
				{
					$kml[] = ' <href>' . Uri::root() . $row->avatar . '</href>';
				}

				$kml[] = ' </Icon>';
				$kml[] = ' </IconStyle>';
				$kml[] = ' </Style>';
			}
		}

		$kml[] = ' <Folder>';
		$kml[] = ' <open>1</open>';
		foreach ($allmembers as $row)
		{
			if ($row->lng)
			{
				$kml[] = ' <Placemark id="placemark' . $row->user_id . '">';
				$kml[] = ' <open>1</open>';
				//$kml[] = ' <name>' . htmlentities($row->team_name) . '</name>';
				//$kml[] = ' <description>' . htmlentities($row->address_string) . '</description>';
				$kml[] = ' <name>' . $row->name . '</name>';
				$kml[] = ' <description>' . $row->address_string . '</description>';
				$kml[] = ' <address>' . $row->address_string . '</address>';
				//$kml[] = ' <styleUrl>#' . ($row->type) .'Style</styleUrl>';
				$kml[] = ' <styleUrl>#' . ($row->user_id) . 'Style</styleUrl>';
				$kml[] = ' <Point>';
				$kml[] = ' <coordinates>' . $row->lng . ',' . $row->lat . '</coordinates>';
				$kml[] = ' </Point>';
				$kml[] = ' </Placemark>';
			}

		}

		$kml[] = ' </Folder>';

		// End XML file
		$kml[]     = ' </Document>';
		$kml[]     = '</kml>';
		$kmlOutput = join("\n", $kml);

		// mal als test
		$xmlfile = $kmlOutput;
		$file    = JPATH_SITE . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . $project_id . '-' . $type . '.kml';
		File::write($file, $xmlfile);

	}

	/**
	 * JSMsimpleGMapGeocoder::genkml3()
	 *
	 * @param   mixed  $project_id
	 * @param   mixed  $allteams
	 *
	 * @return void
	 */
	function genkml3($project_id, $allteams)
	{
		$type = 'ranking';

		foreach ($allteams as $row)
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
					$address_parts[] = $row->club_zipcode . ' ' . $row->club_location;
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
			$row->type           = 'bar';

			$row->lat = $row->latitude;
			$row->lng = $row->longitude;


		}


		$this->writekml3($allteams, $project_id, $type);

	}

	/**
	 * JSMsimpleGMapGeocoder::writekml3()
	 *
	 * @param   mixed  $allteams
	 * @param   mixed  $project_id
	 * @param   mixed  $type
	 *
	 * @return void
	 */
	function writekml3($allteams, $project_id, $type)
	{
		$params      = ComponentHelper::getParams('com_sportsmanagement');
		$ph_logo_big = $params->get('ph_logo_big', 0);

		// Creates an array of strings to hold the lines of the KML file.
		$kml   = array('<?xml version="1.0" encoding="UTF-8"?>');
		$kml[] = '<kml xmlns="http://earth.google.com/kml/2.1">';
		$kml[] = ' <Document>';


		foreach ($allteams as $row)
		{
			if ($row->lng != '' && $row->lng != '255.00000000')
			{

				// logo_big
				$kml[] = ' <Style id="' . $row->team_id . 'Style">';
				$kml[] = ' <IconStyle id="' . $row->team_id . 'Icon">';
				$kml[] = ' <Icon>';

				$picturepath = Uri::root() . $row->logo_big;

				$kml[] = ' <href>' . Uri::root() . $row->logo_big . '</href>';
				$kml[] = ' </Icon>';
				$kml[] = ' </IconStyle>';
				$kml[] = ' </Style>';
			}
		}

		$kml[] = ' <Folder>';
		$kml[] = ' <open>1</open>';
		foreach ($allteams as $row)
		{
			if ($row->lng != '' && $row->lng != '255.00000000')
			{
				$kml[] = ' <Placemark id="placemark' . $row->team_id . '">';
				$kml[] = ' <open>1</open>';
				//$kml[] = ' <name>' . htmlentities($row->team_name) . '</name>';
				//$kml[] = ' <description>' . htmlentities($row->address_string) . '</description>';
				$kml[] = ' <name>' . $row->team_name . '</name>';
				$kml[] = ' <description>' . $row->address_string . '</description>';
				$kml[] = ' <address>' . $row->address_string . '</address>';
				//$kml[] = ' <styleUrl>#' . ($row->type) .'Style</styleUrl>';
				$kml[] = ' <styleUrl>#' . ($row->team_id) . 'Style</styleUrl>';
				$kml[] = ' <Point>';
				$kml[] = ' <coordinates>' . $row->lng . ',' . $row->lat . '</coordinates>';
				$kml[] = ' </Point>';
				$kml[] = ' </Placemark>';
			}

		}

		$kml[] = ' </Folder>';

		// End XML file
		$kml[]     = ' </Document>';
		$kml[]     = '</kml>';
		$kmlOutput = join("\n", $kml);

		// mal als test
		$xmlfile = $kmlOutput;
		$file    = JPATH_SITE . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . $project_id . '-' . $type . '.kml';
		File::write($file, $xmlfile);

	}

	/**
	 * @function     getGeoCoords
	 *
	 * @param        $address  : string
	 *
	 * @returns      -
	 * @description  Gets GeoCoords by calling the Google Maps geoencoding API
	 */
	function getGeoCoords($address)
	{
		$coords = array();

		/*
		  OBSOLETE, now using utf8_encode

		  // replace special characters (eg. German "Umlaute")
		  $address = str_replace("ä", "ae", $address);
		  $address = str_replace("ö", "oe", $address);
		  $address = str_replace("ü", "ue", $address);
		  $address = str_replace("Ä", "Ae", $address);
		  $address = str_replace("Ö", "Oe", $address);
		  $address = str_replace("Ü", "Ue", $address);
		  $address = str_replace("ß", "ss", $address);
		*/

		// call geoencoding api with param json for output
		$geoCodeURL = "http://maps.google.com/maps/api/geocode/json?address=" .
			urlencode($address) . "&sensor=false";

		if (function_exists('curl_init'))
		{
			$initial = curl_init();
			curl_setopt($initial, CURLOPT_URL, $geoCodeURL);
			curl_setopt($initial, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($initial, CURLOPT_CONNECTTIMEOUT, 5);
			$file_content = curl_exec($initial);
			curl_close($initial);
			$result = json_decode($file_content, true);
		}

		$coords['status'] = $result["status"];

		if (isset($result["results"][0]))
		{
			$coords['lat'] = $result["results"][0]["geometry"]["location"]["lat"];
			$coords['lng'] = $result["results"][0]["geometry"]["location"]["lng"];
		}

		return $coords;
	}

	/**
	 * WORK IN PROGRESS...
	 *
	 * @function    reverseGeoCode
	 *
	 * @param       $lat  : string
	 * @param       $lng  : string
	 *
	 * @returns     -
	 * @description Gets Address for the given LatLng by calling the Google Maps geoencoding API
	 */
	function reverseGeoCode($lat, $lng)
	{
		$address = array();

		// call geoencoding api with param json for output
		$geoCodeURL = "http://maps.google.com/maps/api/geocode/json?address=$lat,$lng&sensor=false";
		if (function_exists('curl_init'))
		{
			$initial = curl_init();
			curl_setopt($initial, CURLOPT_URL, $geoCodeURL);
			curl_setopt($initial, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($initial, CURLOPT_CONNECTTIMEOUT, 5);
			$file_content = curl_exec($initial);
			curl_close($initial);
			$result = json_decode($file_content, true);
		}
		$address['status'] = $result["status"];
		echo $geoCodeURL . "<br />";

		return $address;
	}


} // end of class

?>
