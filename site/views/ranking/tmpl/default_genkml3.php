<?php 
//defined( '_JEXEC',1 ) or die( 'Restricted access' );
define( '_JEXEC', 1 );
define( '_VALID_MOS', 1 );

/*
if ( $this->show_debug_info )
{
echo 'mapconfig<pre>',print_r($this->mapconfig,true),'</pre><br>';
echo 'allteams<pre>',print_r($this->allteams,true),'</pre><br>';
}

VALUES ('Pan Africa Market', '1521 1st Ave, Seattle, WA', '47.608941', '-122.340145', 'restaurant');
VALUES ('Buddha Thai & Bar', '2222 2nd Ave, Seattle, WA', '47.613591', '-122.344394', 'bar');
VALUES ('The Melting Pot', '14 Mercer St, Seattle, WA', '47.624562', '-122.356442', 'restaurant');
VALUES ('Ipanema Grill', '1225 1st Ave, Seattle, WA', '47.606366', '-122.337656', 'restaurant');
VALUES ('Sake House', '2230 1st Ave, Seattle, WA', '47.612825', '-122.34567', 'bar');
VALUES ('Crab Pot', '1301 Alaskan Way, Seattle, WA', '47.605961', '-122.34036', 'restaurant');
VALUES ('Mama\'s Mexican Kitchen', '2234 2nd Ave, Seattle, WA', '47.613975', '-122.345467', 'bar');
VALUES ('Wingdome', '1416 E Olive Way, Seattle, WA', '47.617215', '-122.326584', 'bar');
VALUES ('Piroshky Piroshky', '1908 Pike pl, Seattle, WA', '47.610127', '-122.342838', 'restaurant');
*/

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
$kml[] = ' <styleUrl>#' . ('bar') .'Style</styleUrl>';
$kml[] = ' <Point>';
$kml[] = ' <coordinates>' . '-122.344394' . ','  . '47.613591' . '</coordinates>';
$kml[] = ' </Point>';
$kml[] = ' </Placemark>';

// End XML file
$kml[] = ' </Document>';
$kml[] = '</kml>';
$kmlOutput = join("\n", $kml);
header('Content-type: application/vnd.google-earth.kml+xml');
echo $kmlOutput;



?>