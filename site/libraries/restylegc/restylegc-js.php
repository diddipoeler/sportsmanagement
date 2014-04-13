<?php header("Content-type: application/x-javascript");
/*******************************************************************************
 * FILE: restylegc-js.php
 *
 * DESCRIPTION:
 *  Companion file for restylegc.php to edit the javascript file that
 *  generates the Google Calendar.
 *   
 * USAGE:
 *  There are no user-editable parameters.
 *
 * MIT LICENSE:
 * Copyright (c) 2009 Brian Gibson (http://www.restylegc.com/)
 * Full text in restylegc.php
 ******************************************************************************/
// URL for the javascript
$url = "";
if(count($_GET) > 0) {
  $url = "https://www.google.com/calendar/" . $_SERVER['QUERY_STRING'];
}

/* If you would like to freeze the calendar version, download the Javascript
 * file using the same method for downloading the CSS file, as described in
 * the main script.  You can find some previous versions in the archive folder.
 * NOTE: You should use the corresponding CSS file as well.
 *
 * Edit and uncomment the following line to freeze the calendar version.
 */
//$url = "http://myserver.tld/path/to/archive/371be1f592b0b5f982952f457d82fd40embedcompiled__en.js";

// Request the javascript
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
$buffer = curl_exec($ch);
curl_close($ch);

// Fix URLs in the javascript
$pattern = '/this\.[a-zA-Z]{1,2}\+"calendar/';
$replacement = '"https://www.google.com/calendar';
$buffer = preg_replace($pattern, $replacement, $buffer);

// Display the javascript
print $buffer;
?>
