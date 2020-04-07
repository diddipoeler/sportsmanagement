<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage rankingalltime
 * @file       default_genkml3.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC', 1) or die('Restricted access');

// Creates an array of strings to hold the lines of the KML file.
$kml = array('<?xml version="1.0" encoding="UTF-8"?>');
$kml[] = '<kml xmlns="http://earth.google.com/kml/2.1">';
$kml[] = ' <Document>';
$kml[] = ' <Style id="restaurantStyle">';
$kml[] = ' <IconStyle id="restuarantIcon">';
$kml[] = ' <Icon>';
$kml[] = ' <href>http://maps.google.com/mapfiles/kml/pal2/icon63.png</href>';
$kml[] = ' </Icon>';
$kml[] = ' </IconStyle>';
$kml[] = ' </Style>';
$kml[] = ' <Style id="barStyle">';
$kml[] = ' <IconStyle id="barIcon">';
$kml[] = ' <Icon>';
$kml[] = ' <href>http://maps.google.com/mapfiles/kml/pal2/icon27.png</href>';
$kml[] = ' </Icon>';
$kml[] = ' </IconStyle>';
$kml[] = ' </Style>';

/*
$kml[] = ' <Placemark id="placemark1">';
$kml[] = ' <name>' . htmlentities('Pan Africa Market') . '</name>';
$kml[] = ' <description>' . htmlentities('1521 1st Ave, Seattle, WA') . '</description>';
$kml[] = ' <styleUrl>#' . ('restaurant') .'Style</styleUrl>';
$kml[] = ' <Point>';
$kml[] = ' <coordinates>' . '-122.340145' . ','  . '47.608941' . '</coordinates>';
$kml[] = ' </Point>';
$kml[] = ' </Placemark>';
*/

$kml[] = ' <Placemark id="placemark1">';
$kml[] = ' <name>' . htmlentities('Buddha Thai & Bar') . '</name>';
$kml[] = ' <description>' . htmlentities('2222 2nd Ave, Seattle, WA') . '</description>';
$kml[] = ' <styleUrl>#' . ('bar') . 'Style</styleUrl>';
$kml[] = ' <Point>';
$kml[] = ' <coordinates>' . '-122.344394' . ',' . '47.613591' . '</coordinates>';
$kml[] = ' </Point>';
$kml[] = ' </Placemark>';

// End XML file
$kml[] = ' </Document>';
$kml[] = '</kml>';
$kmlOutput = join("\n", $kml);
header('Content-type: application/vnd.google-earth.kml+xml');
echo $kmlOutput;



