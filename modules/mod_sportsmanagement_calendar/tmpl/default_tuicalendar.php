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
 *
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;

//$display = ($params->get('update_module') == 1) ? 'block' : 'none';
//$show_teamlist = ($params->get('show_teamslist') == 1) ? 'show' : 'hidden';

$cal       = new JSMCalendar; // This object creates the html for the calendar
$cal::getMatches($month, $year);

$event_month = $month;
$event_year = $year;

//echo __LINE__.'<pre>'.print_r($month,true).'</pre>';
//echo __LINE__.'<pre>'.print_r($year,true).'</pre>';

//echo '<pre>'.print_r($cal::$matches,true).'</pre>';

foreach ( $cal::$matches as $row )
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
<!-- <script src="https://uicdn.toast.com/calendar/latest/toastui-calendar.min.js"></script> --->
  <script src="https://uicdn.toast.com/calendar/latest/toastui-calendar.ie11.min.js"></script>
  
  
<link rel="stylesheet" href="https://uicdn.toast.com/tui.date-picker/latest/tui-date-picker.css" />
<script src="https://uicdn.toast.com/tui.date-picker/latest/tui-date-picker.js"></script>  
  
<link rel="stylesheet" href="https://uicdn.toast.com/tui.time-picker/latest/tui-time-picker.css">
<script src="https://uicdn.toast.com/tui.time-picker/latest/tui-time-picker.js"></script>  
  
  
  
  <div class="container" >
  
 

  
  
<!-- <div id="datepicker-wrapper"></div>  -->

  

  
  

 <div id="calendarMenu">
  <button id="prevtoday">Heute</button>
  
  <button id="prevBtn"><img height="100%" src="https://nhn.github.io/tui.calendar/latest/examples/images/ic-arrow-line-left.png"/> Prev</button>
  <button id="nextBtn">Next <img height="100%" src="https://nhn.github.io/tui.calendar/latest/examples/images/ic-arrow-line-right.png"/> </button>
  
  </div>

  
<div id="calendar" style="height: 600px;"></div>
  </div>
  
  <script>
  var month = <?php echo $month; ?>;
var year = <?php echo $year; ?>;
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
  
  $.ajax({ 
                type:'POST', 
                url:'functions.php', 
                data:'func=getCalender&year='+year+'&month='+month, 
                success:function(html){ 
                    $('#'+target_div).html(html); 
                } 
            }); 
            
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
      
      $.ajax({ 
                type:'POST', 
                url:'functions.php', 
                data:'func=getCalender&year='+year+'&month='+month, 
                success:function(html){ 
                    $('#'+target_div).html(html); 
                } 
            }); 
            
            
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
