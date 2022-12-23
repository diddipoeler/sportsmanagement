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
 * https://github.com/nhn/tui.calendar/tree/56f36b0ae0ac2331983fd2335017ae0fc7e24d7b/examples
 * 
 * https://ui.toast.com/tui-calendar
 * https://github.com/nhn/tui.calendar
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
 



<link rel="stylesheet" href="https://uicdn.toast.com/calendar/latest/toastui-calendar.css" />
<script src="https://uicdn.toast.com/calendar/latest/toastui-calendar.ie11.js"></script>
<!-- <script src="https://uicdn.toast.com/calendar/latest/toastui-calendar.js"></script>  -->
  
  
<link rel="stylesheet" href="https://uicdn.toast.com/tui.date-picker/latest/tui-date-picker.css" />
<script src="https://uicdn.toast.com/tui.date-picker/latest/tui-date-picker.js"></script>  
  
<link rel="stylesheet" href="https://uicdn.toast.com/tui.time-picker/latest/tui-time-picker.css">
<script src="https://uicdn.toast.com/tui.time-picker/latest/tui-time-picker.js"></script>  
  
 
  
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.20.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/chance/1.0.13/chance.min.js"></script>
  
  
<?php  
if (version_compare(JVERSION, '4.0.0', 'ge'))
{
HTMLHelper::_('script', 'modules' . DIRECTORY_SEPARATOR . $module->module . DIRECTORY_SEPARATOR . 'assets/js' . DIRECTORY_SEPARATOR . 'calendars' . '.js');    
HTMLHelper::_('script', 'modules' . DIRECTORY_SEPARATOR . $module->module . DIRECTORY_SEPARATOR . 'assets/js' . DIRECTORY_SEPARATOR . 'schedules' . '.js');
}
elseif (version_compare(JVERSION, '3.0.0', 'ge'))
{
$doc->addScript(Uri::base() . 'modules' . DIRECTORY_SEPARATOR . $module->module . DIRECTORY_SEPARATOR . 'assets/js' . DIRECTORY_SEPARATOR . 'calendars' . '.js');    
$doc->addScript(Uri::base() . 'modules' . DIRECTORY_SEPARATOR . $module->module . DIRECTORY_SEPARATOR . 'assets/js' . DIRECTORY_SEPARATOR . 'schedules' . '.js');
}  
  
  
  
  
?>
<div class="container" >
  

<div id="calendarList">

  
</div>
 
 
<div id="right">
        <div id="menu">
            <span class="dropdown">
                <button id="dropdownMenu-calendarType" class="btn btn-default btn-sm dropdown-toggle" type="button" data-toggle="dropdown"
                    aria-haspopup="true" aria-expanded="true">
                    <i id="calendarTypeIcon" class="calendar-icon ic_view_month" style="margin-right: 4px;"></i>
                    <span id="calendarTypeName">Dropdown</span>&nbsp;
                    <i class="calendar-icon tui-full-calendar-dropdown-arrow"></i>
                </button>
                <ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu-calendarType">
                    <li role="presentation">
                        <a class="dropdown-menu-title" role="menuitem" data-action="toggle-daily">
                            <i class="calendar-icon ic_view_day"></i>Daily
                        </a>
                    </li>
                    <li role="presentation">
                        <a class="dropdown-menu-title" role="menuitem" data-action="toggle-weekly">
                            <i class="calendar-icon ic_view_week"></i>Weekly
                        </a>
                    </li>
                    <li role="presentation">
                        <a class="dropdown-menu-title" role="menuitem" data-action="toggle-monthly">
                            <i class="calendar-icon ic_view_month"></i>Month
                        </a>
                    </li>
                    <li role="presentation">
                        <a class="dropdown-menu-title" role="menuitem" data-action="toggle-weeks2">
                            <i class="calendar-icon ic_view_week"></i>2 weeks
                        </a>
                    </li>
                    <li role="presentation">
                        <a class="dropdown-menu-title" role="menuitem" data-action="toggle-weeks3">
                            <i class="calendar-icon ic_view_week"></i>3 weeks
                        </a>
                    </li>
                    <li role="presentation" class="dropdown-divider"></li>
                    <li role="presentation">
                        <a role="menuitem" data-action="toggle-workweek">
                            <input type="checkbox" class="tui-full-calendar-checkbox-square" value="toggle-workweek" checked>
                            <span class="checkbox-title"></span>Show weekends
                        </a>
                    </li>
                    <li role="presentation">
                        <a role="menuitem" data-action="toggle-start-day-1">
                            <input type="checkbox" class="tui-full-calendar-checkbox-square" value="toggle-start-day-1">
                            <span class="checkbox-title"></span>Start Week on Monday
                        </a>
                    </li>
                    <li role="presentation">
                        <a role="menuitem" data-action="toggle-narrow-weekend">
                            <input type="checkbox" class="tui-full-calendar-checkbox-square" value="toggle-narrow-weekend">
                            <span class="checkbox-title"></span>Narrower than weekdays
                        </a>
                    </li>
                </ul>
            </span>
            <span id="menu-navi">
              <button id="prevtoday">Heute</button>
 
  <button id="prevBtn"><img height="100%" src="https://nhn.github.io/tui.calendar/latest/examples/images/ic-arrow-line-left.png"/> Prev</button>
  <button id="nextBtn">Next <img height="100%" src="https://nhn.github.io/tui.calendar/latest/examples/images/ic-arrow-line-right.png"/> </button>
            
            
                      <!--
                <button type="button" class="btn btn-default btn-sm move-today" data-action="move-today">Today</button>
                <button type="button" class="btn btn-default btn-sm move-day" data-action="move-prev">
                    <i class="calendar-icon ic-arrow-line-left" data-action="move-prev"></i>
                </button>
                <button type="button" class="btn btn-default btn-sm move-day" data-action="move-next">
                    <i class="calendar-icon ic-arrow-line-right" data-action="move-next"></i>
                </button>
                      -->
            </span>
            <span id="renderRange" class="render-range"></span>
        </div>
        
        <div id="calendar" style="height: 600px;"></div>
        
    </div>
                      
                      
                      
<div id="target_div" style=""></div>  
  
<div class="status" id="status">feld</div>  
  
  
</div>

  
  
  
  
  
  <?php
//$ajax    = $jinput->getVar('ajaxCalMod', 0, 'default', 'POST');
//$ajaxmod = $jinput->getVar('ajaxmodid', 0, 'default', 'POST');
//$year = $jinput->getVar('year', 0, 'default', 'POST');
//$year   = $jinput->getVar('year', '1111');
//$month  = $jinput->getVar('month', '');

//echo "<script>console.log('Debug Objects ajax: + " . $ajax . "' );</script>";
?>
  <script>
  var responseevents = '';
  var month = <?php echo $month; ?>;
var year = <?php echo $year; ?>;
var params = <?php echo $params; ?>;
//var splits = '';

console.log('start month: ' + month );
console.log('start year: ' + year );

const el = document.getElementById("calendar");
const prevBtn = document.getElementById("prevBtn");
const nextBtn = document.getElementById("nextBtn");


var calendarinf;
var infoid = 0;

calendarinf = new CalendarInfo();
infoid += 1;
calendarinf.id = String(infoid);
calendarinf.name = 'My Calendar';
calendarinf.color = '#ffffff';
calendarinf.bgColor = '#9e5fff';
calendarinf.dragBgColor = '#9e5fff';
calendarinf.borderColor = '#9e5fff';
addCalendar(calendarinf);

calendarinf = new CalendarInfo();
infoid += 1;
calendarinf.id = String(infoid);
calendarinf.name = 'Company';
calendarinf.color = '#ffffff';
calendarinf.bgColor = '#00a9ff';
calendarinf.dragBgColor = '#00a9ff';
calendarinf.borderColor = '#00a9ff';
addCalendar(calendarinf);

// register templates
  const templates = {
    popupIsAllDay: function() {
      return 'All Day';
    },
    popupStateFree: function() {
      return 'Free';
    },
    popupStateBusy: function() {
      return 'Busy';
    },
    titlePlaceholder: function() {
      return 'Subject';
    },
    locationPlaceholder: function() {
      return 'Location';
    },
    startDatePlaceholder: function() {
      return 'Start date';
    },
    endDatePlaceholder: function() {
      return 'End date';
    },
    popupSave: function() {
      return 'Save';
    },
    popupUpdate: function() {
      return 'Update';
    },
    popupDetailDate: function(isAllDay, start, end) {
      var isSameDate = moment(start).isSame(end);
      var endFormat = (isSameDate ? '' : 'YYYY.MM.DD ') + 'hh:mm a';

      if (isAllDay) {
        return moment(start).format('YYYY.MM.DD') + (isSameDate ? '' : ' - ' + moment(end).format('YYYY.MM.DD'));
      }

      return (moment(start).format('YYYY.MM.DD hh:mm a') + ' - ' + moment(end).format(endFormat));
    },
    popupDetailLocation: function(schedule) {
      return 'Location : ' + schedule.location;
    },
    popupDetailUser: function(schedule) {
      return 'User : ' + (schedule.attendees || []).join(', ');
    },
    popupDetailState: function(schedule) {
      return 'State : ' + schedule.state || 'Busy';
    },
    popupDetailRepeat: function(schedule) {
      return 'Repeat : ' + schedule.recurrenceRule;
    },
    popupDetailBody: function(schedule) {
      return 'Body : ' + schedule.body;
    },
    popupEdit: function() {
      return 'Edit';
    },
    popupDelete: function() {
      return 'Delete';
    }
  };
      
const Calendar = tui.Calendar;
const container = document.getElementById('calendar');
const options = {
  defaultView: 'month',
  useDetailPopup: true,
  useCreationPopup: true,
  template: templates,
  timezone: {
    zones: [
      {
        timezoneName: 'Europe/Berlin',
        displayLabel: 'Berlin',
      },
    ],
  },
  calendars: CalendarList
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
setRenderRangeText(); 


//calendar.setCalendars(CalendarList);
 var calendarList = document.getElementById('calendarList');
    var html = [];
    CalendarList.forEach(function(calendar) {
        html.push('<div class="lnb-calendars-item"><label>' +
            '<input type="checkbox" class="tui-full-calendar-checkbox-round" value="' + calendar.id + '" checked>' +
            '<span style="border-color: ' + calendar.borderColor + '; background-color: ' + calendar.borderColor + ';"></span>' +
            '<span>' + calendar.name + '</span>' +
            '</label></div>'
        );
    });
    calendarList.innerHTML = html.join('\n');


 jQuery(".toastui-calendar-grid-cell-more-events").bind("click", function () {
    console.log('more events angeklickt: ');
     });
     
     
/**
class = toastui-calendar-grid-cell-more-events


*/

prevtoday.addEventListener("click", e => {
  calendar.today();
  month = <?php echo $month;?>;
  year = <?php echo $year;?>;
  
  console.log('month: ' + month );
  console.log('year: ' + year );
  
request = {
'option' : 'com_ajax',
'module' : 'sportsmanagement_calendar',
'formvaluemonth'   : month,
'formvalueyear'   : year,
'params'   : params,
'format' : 'raw'
};
  
jQuery.ajax({
type   : 'POST',
data   : request,
async: false,
success: eventsaved
});  
  
   //calendar.clear();
  
  // splitssplit = splits.split(";",3);          
//              console.log('splitssplit: ' + splitssplit );
//  var arrayLength = splitssplit.length;
//for (var i = 0; i < arrayLength; i++) {
//    console.log(splitssplit[i]);
//    //Do something
//   calendar.createEvents([splitssplit[i]
// ,
//]);
//  } 
//  
//var caloptions = calendar.getOptions();  
//console.log('caloptions: ' + JSON.stringify(caloptions) );  
  
  
 
  
  
  
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
  
request = {
'option' : 'com_ajax',
'module' : 'sportsmanagement_calendar',
'formvaluemonth'   : month,
'formvalueyear'   : year,
'params'   : params,
'format' : 'raw'
};
  
jQuery.ajax({
type   : 'POST',
data   : request,
async: false,
success: eventsaved
});  
  
  /**
  calendar.clear();
  
   splitssplit = splits.split(";",3);          
              console.log('splitssplit: ' + splitssplit );
  var arrayLength = splitssplit.length;
for (var i = 0; i < arrayLength; i++) {
    console.log(splitssplit[i]);
    //Do something
   calendar.createEvents([splitssplit[i]
 ,
]);
  } 
  
var caloptions = calendar.getOptions();  
console.log('caloptions: ' + JSON.stringify(caloptions) );  
*/

  }

);

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
      
  var location = window.location.href ;
    console.log('location: ' + location );

request = {
'option' : 'com_ajax',
'module' : 'sportsmanagement_calendar',
'formvaluemonth'   : month,
'formvalueyear'   : year,
'params'   : params,
'format' : 'raw'
};  
                
jQuery.ajax({
type   : 'POST',
data   : request,
async: false,
success: eventsaved
});  
  
  
//  console.log('danach splits: ' + splits );  
 //console.log('danach splits: ' + JSON.stringify(splits) );  
  //splitsarray = Object.entries(splits);
 // splitsarray = splits.split(";",3);          
  //console.log('splitsarray: ' + splitsarray );
  
  
//  calendar.clear();
  
//  splitssplit = splits.split(";",3);          
//  console.log('splitssplit: ' + splitssplit );
//  var arrayLength = splitssplit.length;
  
  /**
for (var i = 0; i < arrayLength; i++) {
    console.log(splitssplit[i]);
    //Do something
   calendar.createEvents([splitssplit[i]
 ,
]);
  } 
  */
  
//var caloptions = calendar.getOptions();  
//console.log('caloptions: ' + JSON.stringify(caloptions) );  
            
});







function eventsaved(response) 
{  
var scriptstring = '';  
splitssplit = response.split(";");   
var arrayLength = splitssplit.length;
for (var i = 0; i < arrayLength; i++) {
   // console.log(splitssplit[i]);
  if ( i === 0)
  {
  scriptstring = splitssplit[i];  
  }
  else
  {
   scriptstring = scriptstring + ',' + splitssplit[i];  
  }
}
  
//scriptstring = 'calendar.clear();calendar.createEvents([' + scriptstring + ',]);'  ;
scriptstring = 'calendar.clear();calendar.createEvents([' + response + ',]);'  ;

jQuery('<script>' + scriptstring + '</' + 'script>').appendTo(document.body);   
setRenderRangeText();  
}



jQuery('#menu-navi').on('click', onClickNavi);
jQuery('.dropdown-menu a[role="menuitem"]').on('click', onClickMenu);

function getDataAction(target) {
        return target.dataset ? target.dataset.action : target.getAttribute('data-action');
    }

function setDropdownCalendarType() {
        var calendarTypeName = document.getElementById('calendarTypeName');
        var calendarTypeIcon = document.getElementById('calendarTypeIcon');
        var options = calendar.getOptions();
        var type = calendar.getViewName();
        var iconClassName;

        if (type === 'day') {
            type = 'Daily';
            iconClassName = 'calendar-icon ic_view_day';
        } else if (type === 'week') {
            type = 'Weekly';
            iconClassName = 'calendar-icon ic_view_week';
        } else if (options.month.visibleWeeksCount === 2) {
            type = '2 weeks';
            iconClassName = 'calendar-icon ic_view_week';
        } else if (options.month.visibleWeeksCount === 3) {
            type = '3 weeks';
            iconClassName = 'calendar-icon ic_view_week';
        } else {
            type = 'Monthly';
            iconClassName = 'calendar-icon ic_view_month';
        }

        calendarTypeName.innerHTML = type;
        calendarTypeIcon.className = iconClassName;
    }

 function setRenderRangeText() {
        var renderRange = document.getElementById('renderRange');
        var options = calendar.getOptions();
        var viewName = calendar.getViewName();
        var html = [];
        if (viewName === 'day') {
            html.push(moment(calendar.getDate().getTime()).format('YYYY.MM.DD'));
        } else if (viewName === 'month' &&
            (!options.month.visibleWeeksCount || options.month.visibleWeeksCount > 4)) {
            html.push(moment(calendar.getDate().getTime()).format('YYYY.MM'));
        } else {
            html.push(moment(calendar.getDateRangeStart().getTime()).format('YYYY.MM.DD'));
            html.push(' ~ ');
            html.push(moment(calendar.getDateRangeEnd().getTime()).format(' MM.DD'));
        }
        renderRange.innerHTML = html.join('');
    }

function setSchedules() {
        calendar.clear();
        generateSchedule(calendar.getViewName(), calendar.getDateRangeStart(), calendar.getDateRangeEnd());
        calendar.createSchedules(ScheduleList);
        refreshScheduleVisibility();
    }

function onClickNavi(e) {
        var action = getDataAction(e.target);

        switch (action) {
            case 'move-prev':
                calendar.prev();
                break;
            case 'move-next':
                calendar.next();
                break;
            case 'move-today':
                calendar.today();
                break;
            default:
                return;
        }

        setRenderRangeText();
        setSchedules();
    }

function onClickMenu(e) {
        var target = jQuery(e.target).closest('a[role="menuitem"]')[0];
        var action = getDataAction(target);
        var options = calendar.getOptions();
        var viewName = '';

        console.log(target);
        console.log(action);
        switch (action) {
            case 'toggle-daily':
                viewName = 'day';
                break;
            case 'toggle-weekly':
                viewName = 'week';
                break;
            case 'toggle-monthly':
                options.month.visibleWeeksCount = 0;
                viewName = 'month';
                break;
            case 'toggle-weeks2':
                options.month.visibleWeeksCount = 2;
                viewName = 'month';
                break;
            case 'toggle-weeks3':
                options.month.visibleWeeksCount = 3;
                viewName = 'month';
                break;
            case 'toggle-narrow-weekend':
                options.month.narrowWeekend = !options.month.narrowWeekend;
                options.week.narrowWeekend = !options.week.narrowWeekend;
                viewName = calendar.getViewName();

                target.querySelector('input').checked = options.month.narrowWeekend;
                break;
            case 'toggle-start-day-1':
                options.month.startDayOfWeek = options.month.startDayOfWeek ? 0 : 1;
                options.week.startDayOfWeek = options.week.startDayOfWeek ? 0 : 1;
                viewName = calendar.getViewName();

                target.querySelector('input').checked = options.month.startDayOfWeek;
                break;
            case 'toggle-workweek':
                options.month.workweek = !options.month.workweek;
                options.week.workweek = !options.week.workweek;
                viewName = calendar.getViewName();

                target.querySelector('input').checked = !options.month.workweek;
                break;
            default:
                break;
        }

        calendar.setOptions(options, true);
        calendar.changeView(viewName, true);

        setDropdownCalendarType();
        setRenderRangeText();
        setSchedules();
    }


// event handlers
    calendar.on({
        'clickMore': function(e) {
            console.log('clickMore', e);
        },
        'clickSchedule': function(e) {
            console.log('clickSchedule', e);
        },
        'clickDayname': function(date) {
            console.log('clickDayname', date);
        },
        'beforeCreateSchedule': function(e) {
            console.log('beforeCreateSchedule', e);
            saveNewSchedule(e);
        },
        'beforeUpdateSchedule': function(e) {
            console.log('beforeUpdateSchedule', e);
            e.schedule.start = e.start;
            e.schedule.end = e.end;
            calendar.updateSchedule(e.schedule.id, e.schedule.calendarId, e.schedule);
        },
        'beforeDeleteSchedule': function(e) {
            console.log('beforeDeleteSchedule', e);
            calendar.deleteSchedule(e.schedule.id, e.schedule.calendarId);
        },
        'afterRenderSchedule': function(e) {
            var schedule = e.schedule;
            // var element = cal.getElement(schedule.id, schedule.calendarId);
            // console.log('afterRenderSchedule', element);
        },
        'clickTimezonesCollapseBtn': function(timezonesCollapsed) {
            console.log('timezonesCollapsed', timezonesCollapsed);

            if (timezonesCollapsed) {
                calendar.setTheme({
                    'week.daygridLeft.width': '77px',
                    'week.timegridLeft.width': '77px'
                });
            } else {
                calendar.setTheme({
                    'week.daygridLeft.width': '60px',
                    'week.timegridLeft.width': '60px'
                });
            }

            return true;
        }
    });





















/**
calendar.on('clickEvent', ({ event }) => {
  const el = document.getElementById('clicked-event');
  el.innerText = event.title;
});
*/

</script>
  
 
