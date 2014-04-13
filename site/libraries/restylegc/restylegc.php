<?php
/*******************************************************************************
 * FILE: restylegc.php
 *
 * DESCRIPTION:
 *  This script is an intermediary between an iframe and Google Calendar that
 *  allows you to override the default style.
 *
 * USAGE:
 *  <iframe src="restylegc.php?src=user%40domain.tld"></iframe>
 *
 *  where user@domain.tld is a valid Google Calendar account.
 *
 * VALID QUERY STRING PARAMETERS:
 *    title:         any valid url encoded string 
 *                   if not present, takes title from first src
 *    showTitle:     0 or 1 (default)
 *    showNav:       0 or 1 (default)
 *    showDate:      0 or 1 (default)
 *    showTabs:      0 or 1 (default)
 *    showCalendars: 0 or 1 (default)
 *    mode:          WEEK, MONTH (default), AGENDA
 *    height:        a positive integer (should be same height as iframe)
 *    wkst:          1 (Sun; default), 2 (Mon), or 7 (Sat)
 *    hl:            en, zh_TW, zh_CN, da, nl, en_GB, fi, fr, de, it, ja, ko, 
 *                   no, pl, pt_BR, ru, es, sv, tr
 *                   if not present, takes language from first src
 *    bgcolor:       url encoded hex color value, #FFFFFF (default)
 *    src:           url encoded Google Calendar account (required)
 *    color:         url encoded hex color value     
 *                   must immediately follow src
 *    
 *    The query string can contain multiple src/color pairs.  It's recommended 
 *    to have these pairs of query string parameters at the end of the query 
 *    string.
 *
 * HISTORY:
 *   03 December 2008 - Original release
 *                      Uses technique from MyGoogleCal2 for all browsers,
 *                      rather than giving IE special treatment.
 *   16 December 2008 - Modified restylegc-js.php so that the regex does a
 *                      general match rather than specifically look for the
 *                      variable 'Ac'.
 *   Mar--Apr    2009 - Added jQuery for modifying the style after page load
 *   23 June     2009 - Replaced jQuery with Dojo since jQuery, Prototype, and
 *                      MooTools are not compatible
 *   03 July     2009 - Fixed bug to remove width style from bubble
 *   05 July     2009 - Rebranded to RESTYLEgc
 *   16 August   2009 - Updated regex in restylegc-js.php
 *   19 December 2009 - Removed MyGoogleCal references
 *                      Updated Dojo version
 *                      Archived additional .js and .css files
 *   13 November 2010 - Changed Google Calendar protocol to https
 *                      Switched back to jQuery
 *                      
 *   
 * ACKNOWLEDGMENTS:
 *   Michael McCall (http://www.castlemccall.com/) for pointing out "htmlembed"
 *   Mike (http://mikahn.com/) for the link to the online CSS formatter
 *   TechTriad.com (http://techtriad.com/) for requesting and funding the 
 *       Javascript code to edit CSS properties and for selflessly letting the
 *       code be published for everyone's use and benefit.
 *   
 *
 * MIT LICENSE:
 * Copyright (c) 2009 Brian Gibson (http://www.restylegc.com/)
 * 
 * Permission is hereby granted, free of charge, to any person
 * obtaining a copy of this software and associated documentation
 * files (the "Software"), to deal in the Software without
 * restriction, including without limitation the rights to use,
 * copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following
 * conditions:
 * 
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
 *
 ******************************************************************************/

/* URL for overriding stylesheet
 * The best way to create this stylesheet is to 
 * 1) Load "http://www.google.com/calendar/embed?src=user%40domain.tld" in a
 *    browser,
 * 2) View the source (e.g., View->Page Source in Firefox),
 * 3) Copy the relative URL of the stylesheet (i.e., the href value of the 
 *    <link> tag), 
 * 4) Load the stylesheet in the browser by pasting the stylesheet URL into 
 *    the address bar so that it reads similar to:
 *    "http://www.google.com/calendar/d003e2eff7c42eebf779ecbd527f1fe0embedcompiled.css"
 * 5) Save the stylesheet (e.g., File->Save Page As in Firefox)
 * Edit this new file to change the style of the calendar.
 *
 * As an alternative method, take the URL you copied in Step 3, and paste it
 * in the URL field at http://mabblog.com/cssoptimizer/uncompress.html.
 * That site will automatically format the CSS so that it's easier to edit.
 */
$cachefile = 'cache/'.basename($_SERVER['SCRIPT_NAME']);
$cachetime = 120 * 60; // 2 hours
// Serve from the cache if it is younger than $cachetime
if (file_exists($cachefile) && (time() - $cachetime < filemtime($cachefile))) {
    include($cachefile);
    echo "<!-- Cached ".date('jS F Y H:i', filemtime($cachefile))." -->";
    exit;
}
ob_start(); // start the output buffer
$stylesheet = 'restylegc.css';

/*******************************************************************************
 * DO NOT EDIT BELOW UNLESS YOU KNOW WHAT YOU'RE DOING
 ******************************************************************************/

// URL for the calendar
$url = "";
if(count($_GET) > 0) {
  $url = "https://www.google.com/calendar/embed?" . $_SERVER['QUERY_STRING'];
}

// Request the calendar
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
$buffer = curl_exec($ch);
curl_close($ch);

// Point stylesheet and javascript to custom versions
$pattern = '/(<link.*>)/';
$replacement = '<link rel="stylesheet" type="text/css" href="' . $stylesheet . '" />';
$buffer = preg_replace($pattern, $replacement, $buffer);

$pattern = '/src="(.*js)"/';
$replacement = 'src="restylegc-js.php?$1"';  
$buffer = preg_replace($pattern, $replacement, $buffer);

// Add a hook to the window onload function
$pattern = '/}\);}<\/script>/';
$replacement = '}); restylegc();}</script>';
$buffer = preg_replace($pattern, $replacement, $buffer);

// Use DHTML to modify the DOM after the calendar loads
$pattern = '/(<\/head>)/';
$replacement = <<<RGC
<script type="text/javascript">
function restylegc() {
    // remove inline style from body so background-color can be set using the stylesheet
    $('body').removeAttr('style');

    // iterate over each bubble and remove the width property from the style attribute
    // so that the width can be set using the stylesheet
    $('.bubble').each(function(){ 
        style = $(this).attr('style').replace(/width: \d+px;?/i, ''); 
        $(this).attr('style', style); 
    });

    // see jQuery documentation for other ways to edit DOM
    // http://docs.jquery.com/
}
</script>
</head>
RGC;
$buffer = preg_replace($pattern, $replacement, $buffer);

// display the calendar
print $buffer;

$fp = fopen($cachefile, 'w'); // open the cache file for writing
fwrite($fp, ob_get_contents()); // save the contents of output buffer to the file
fclose($fp); // close the file
ob_end_flush(); // Send the output to the browser
?>
