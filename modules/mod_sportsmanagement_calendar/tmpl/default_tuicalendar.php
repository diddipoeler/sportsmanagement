<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage mod_sportsmanagement_calendar
 * @file       default_tuicalendar.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * https://codesandbox.io/s/toast-ui-calendar-for-vanillajs-wz2s3?file=/index.html:212-246
 * https://codesandbox.io/examples/package/tui-date-picker
 * https://stackoverflow.com/questions/71907057/how-to-send-json-value-to-tui-calendar-using-ajax
 *
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;

$app = Factory::getApplication('site');
$post   = Factory::getApplication()->input->post->getArray(array()); 
// JInput object
$jinput = $app->input;

//echo 'post <pre>'.print_r($post,true).'</pre>';

//$display = ($params->get('update_module') == 1) ? 'block' : 'none';
//$show_teamlist = ($params->get('show_teamslist') == 1) ? 'show' : 'hidden';

$cal       = new JSMCalendar; // This object creates the html for the calendar
$cal::$matches = array();
$matches = $cal::getMatches($month, $year);

//$event_month = $month;
//$event_year = $year;

//echo __LINE__.'<pre>'.print_r($month,true).'</pre>';
//echo __LINE__.'<pre>'.print_r($year,true).'</pre>';

//echo '<pre>'.print_r($cal::$matches,true).'</pre>';

foreach ( $matches as $row )
{
  $event = "";
  //$theStart_date = date(DATE_ATOM, strtotime($row['date']));
  //echo __LINE__.'<pre>'.print_r($theStart_date,true).'</pre>';
  
  //$time = date("c", $row['timestamp']);
  $time = date("Y-m-d\TH:i:s", $row['timestamp']);
  //echo __LINE__.'<pre>'.print_r($time,true).'</pre>';
  
  
  //$row['date'] = preg_replace(' ', 'T', $row['date']);
  
 $event .= "{id: '".$row['matchcode']."',";
    $event .= "calendarId: '1',";
    $event .= "title: '".$row['homename'].$row['awayname'].$row['result']   ."',";
    $event .= "start: '".$time."',";
    $event .= "end: '".$time."',  }";
  $events[] = $event;
}

//echo '<pre>'.print_r($events,true).'</pre>';
$calendeer_events = implode(",",$events);
//echo '<pre>'.print_r($calendeer_events,true).'</pre>';

?>
  <html>
  <body>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
<link rel="stylesheet" href="https://uicdn.toast.com/calendar/latest/toastui-calendar.min.css" />

  
  <script src="https://uicdn.toast.com/calendar/latest/toastui-calendar.ie11.js"></script>
  
  <!-- <script src="https://uicdn.toast.com/tui.code-snippet/v1.5.2/tui-code-snippet.min.js"></script> -->
  
<link rel="stylesheet" href="https://uicdn.toast.com/tui.date-picker/latest/tui-date-picker.css" />
<script src="https://uicdn.toast.com/tui.date-picker/latest/tui-date-picker.js"></script>  
  
<link rel="stylesheet" href="https://uicdn.toast.com/tui.time-picker/latest/tui-time-picker.css">
<script src="https://uicdn.toast.com/tui.time-picker/latest/tui-time-picker.js"></script>  
  
<!-- <script src="https://uicdn.toast.com/tui-calendar/latest/tui-calendar.js"></script>   --->
  
  <div class="container" >
  
 

  
  
<!-- <div id="datepicker-wrapper"></div>  -->

  

  
  

 <div id="calendarMenu">
  <button id="prevtoday">Heute</button>
  
  <button id="prevBtn"><img height="100%" src="https://nhn.github.io/tui.calendar/latest/examples/images/ic-arrow-line-left.png"/> Prev</button>
  <button id="nextBtn">Next <img height="100%" src="https://nhn.github.io/tui.calendar/latest/examples/images/ic-arrow-line-right.png"/> </button>
  
  </div>

  
<div id="calendar" style="height: 600px;"></div>
  
<div id="target_div" style=""></div>  
  
  </div>

  
  <div class="mod_ajax_result">feld</div>
  
  
  
  <?php
$ajax    = $jinput->getVar('ajaxCalMod', 0, 'default', 'POST');
$ajaxmod = $jinput->getVar('ajaxmodid', 0, 'default', 'POST');
//$year = $jinput->getVar('year', 0, 'default', 'POST');
//$year   = $jinput->getVar('year', '1111');
//$month  = $jinput->getVar('month', '');

echo "<script>console.log('Debug Objects ajax: + " . $ajax . "' );</script>";
?>
  <script>
  var month = <?php echo $month; ?>;
var year = <?php echo $year; ?>;

console.log('start month: ' + month );
console.log('start year: ' + year );

  const el = document.getElementById("calendar");
  const prevBtn = document.getElementById("prevBtn");
const nextBtn = document.getElementById("nextBtn");
  
const Calendar = tui.Calendar;
const container = document.getElementById('calendar');
const options = {
  defaultView: 'month',
  useDetailPopup: true,
  timezone: {
    zones: [
      {
        timezoneName: 'Europe/Berlin',
        displayLabel: 'Berlin',
      },
    ],
  },
  calendars: [
    {
      id: '1',
      name: 'Spiele',
      backgroundColor: '#03bd9e',
    },
    {
      id: 'cal2',
      name: 'Work',
      backgroundColor: '#00a9ff',
    },
  ],
};

const calendar = new Calendar(container, options); 
/**
start: '2022-12-30T19:30:00',
   end: '2022-12-30T19:30:00'
*/
calendar.createEvents([
  <?php echo $calendeer_events; ?>
 ,
  
]);

prevtoday.addEventListener("click", e => {
  calendar.today();
  month = <?php echo $month;?>;
  year = <?php echo $year;?>;
  
  console.log('month: ' + month );
  console.log('year: ' + year );
   calendar.clear();
  
   var ajax = jlcnewAjax();
  
  ajax.open("POST", window.location.href, true);
	ajax.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	ajax.send('jlcteam=' + 0 + '&year=' + year + '&month=' + month
			+ '&ajaxCalMod=1' + '&ajaxmodid=' + 0);
  ajax.onreadystatechange = function() {
    console.log('readyState: ' + ajax.readyState );
  }
   <?php
     /*
$post   = Factory::getApplication()->input->post->getArray(array());
$year = $app->input->getInt('year', '');
$month = $app->input->getint('month', '');  
  **/
  $cal::$matches = array();
$matches = $cal::getMatches($month, $year);
foreach ( $matches as $row )
{
  $event = "";
  $time = date("Y-m-d\TH:i:s", $row['timestamp']);
 $event .= "{id: '".$row['matchcode']."',";
    $event .= "calendarId: '1',";
    $event .= "title: '".$row['homename'].$row['awayname'].$row['result']   ."',";
    $event .= "start: '".$time."',";
    $event .= "end: '".$time."',  }";
  $events[] = $event;
}

//echo '<pre>'.print_r($events,true).'</pre>';
$calendeer_events = implode(",",$events);  
  
  ?>
   calendar.createEvents([
  <?php echo $calendeer_events; ?>
 ,
  
]); 
 
  
  
  
});

prevBtn.addEventListener("click", e => {
  calendar.prev();
  month = month - 1;
  if (month === 0) {
    month = 12;
    year = year - 1 ;
    }
  console.log('month: ' + month );
  console.log('year: ' + year );
  <?php
    //$event_month--;
  //$month = $event_month;
 
  //echo "console.log('Debug Objects: " . $event_month . "' );";
   
    ?>
  calendar.clear();
  
   var ajax = jlcnewAjax();
  
  ajax.open("POST", window.location.href, true);
	ajax.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	ajax.send('jlcteam=' + 0 + '&year=' + year + '&month=' + month
			+ '&ajaxCalMod=1' + '&ajaxmodid=' + 0);
  ajax.onreadystatechange = function() {
    console.log('readyState: ' + ajax.readyState );
  }
   <?php
     /**
$post   = Factory::getApplication()->input->post->getArray(array());
  
$year = $app->input->getInt('year', '');
$month = $app->input->getint('month', '');  
  */
  $cal::$matches = array();
$matches = $cal::getMatches($month, $year);
foreach ( $matches as $row )
{
  $event = "";
  $time = date("Y-m-d\TH:i:s", $row['timestamp']);
 $event .= "{id: '".$row['matchcode']."',";
    $event .= "calendarId: '1',";
    $event .= "title: '".$row['homename'].$row['awayname'].$row['result']   ."',";
    $event .= "start: '".$time."',";
    $event .= "end: '".$time."',  }";
  $events[] = $event;
}

//echo '<pre>'.print_r($events,true).'</pre>';
$calendeer_events = implode(",",$events);  
  
  ?>
   calendar.createEvents([
  <?php echo $calendeer_events; ?>
 ,
  
]); 
  
  
});

nextBtn.addEventListener("click", e => {
  calendar.next();
  month = month + 1;
  
  if (month === 13) {
    month = 1;
    year = year + 1 ;
    }
  console.log('month: ' + month );
  console.log('year: ' + year );
  //console.log(JSON.stringify(calendar.getOptions()));
  <?php
   // $event_month++;
 //$month = $event_month;
  //echo "console.log('Debug Objects: " . $event_month . "' );";
   
    ?>
      calendar.clear();
  var location = window.location.href ;
    console.log('location: ' + location );

  
  // AJAX-Parameter als JavaScript Objekt
    var ajax_params = {
        'option' : 'com_ajax',
        'module' : 'ajax',
        'format' : 'json',
        'formvalue': 'test'
    };
 
    // AJAX Verarbeitung
    jQuery.ajax({
        type   : 'POST',
        data   : ajax_params,
        context: this,
        success: function (response) {
            // AJAX Aufruf erfolgreich
            if(response.success){
                // Verarbeitung von com_ajax erfolgreich
                jQuery('.mod_ajax_result').html(response.data);
            }
            else
            {
                // Fehler in der Verarbeitung von com_ajax
                jQuery('.mod_ajax_result').html('Fehler');
            }
        },
        error :function () {
            // AJAX Aufruf fehlgeschlagen
            jQuery('.mod_ajax_result').html('AJAX Fehler');
        }
    });
  
  
  
  
  
      /**
 var location = window.location.pathname;
var path = location.substring(0, location.lastIndexOf("/"));
var directoryName = path.substring(path.lastIndexOf("/")+1);
  
  console.log('dir: ' + directoryName );
  console.log('path: ' + path );
  */
  
//  var url = location + 'modules/mod_sportsmanagement_calendar/tmpl/functions.php';
  //console.log('url: ' + url );
  
  var ajax = jlcnewAjax();
  
  ajax.open("POST", window.location.href, true);
	ajax.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	ajax.send('jlcteam=' + 0 + '&year=' + year + '&month=' + month
			+ '&ajaxCalMod=1' + '&ajaxmodid=' + 0);
  ajax.onreadystatechange = function() {
    console.log('readyState: ' + ajax.readyState );
    
    console.log('href: ' + window.location.href );
  }
  
  
  <?php
    $year = $jinput->getVar('year', 0);
//$post   = Factory::getApplication()->input->post->getArray(array()); 
//$year = $app->input->getInt('year', '');
//$month = $app->input->getint('month', ''); 
  
//$year = $post['year'];
//$month = $post['month']; 
  /**
$year   = $jinput->getVar('year', '');
$month  = $jinput->getVar('month', '');
  */
  $cal::$matches = array();
$matches = $cal::getMatches($month, $year);
foreach ( $matches as $row )
{
  $event = "";
  $time = date("Y-m-d\TH:i:s", $row['timestamp']);
 $event .= "{id: '".$row['matchcode']."',";
    $event .= "calendarId: '1',";
    $event .= "title: '".$row['homename'].$row['awayname'].$row['result']   ."',";
    $event .= "start: '".$time."',";
    $event .= "end: '".$time."',  }";
  $events[] = $event;
}

//echo '<pre>'.print_r($events,true).'</pre>';
$calendeer_events = implode(",",$events);  
  
  ?>
    
   calendar.createEvents([
  <?php echo $calendeer_events; ?>
 ,
  
]); 
    //console.log('input year: ' + <?php echo $year; ?> );
  
  
  
            
});

  
  
  
  

/**
calendar.on('clickEvent', ({ event }) => {
  const el = document.getElementById('clicked-event');
  el.innerText = event.title;
});
*/

</script>
  
  </body>
  </html>
