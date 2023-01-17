<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage mod_sportsmanagement_calendar
 * @file       default_tuicalendar.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * https://codesandbox.io/s/toast-ui-calendar-for-vanillajs-wz2s3?file=/index.html:212-246
 * https://codesandbox.io/examples/package/tui-date-picker
 * https://stackoverflow.com/questions/71907057/how-to-send-json-value-to-tui-calendar-using-ajax
 * 
 * https://github.com/nhn/tui.calendar/tree/56f36b0ae0ac2331983fd2335017ae0fc7e24d7b/examples
 * https://github.com/nhn/tui.calendar/tree/56f36b0ae0ac2331983fd2335017ae0fc7e24d7b
 * 
 * https://ui.toast.com/tui-calendar
 * https://github.com/nhn/tui.calendar
 * popup
 * https://codepen.io/AnzilkhaN/pen/GRJqPVK
 *
 *
 *
 *
 *
 *
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;

$app = Factory::getApplication('site');
$post   = Factory::getApplication()->input->post->getArray(array()); 
$doc              = Factory::getDocument();
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
$event .= "category: 'time',";
$event .= "dueDateClass: '',";
$event .= "isReadOnly: 'true',";

$event .= "isAllDay: false, "; 
$event .= "goingDuration: 30, ";
$event .= "comingDuration: 30, ";
$event .= "color: '#ffffff', ";
$event .= "bgColor: '#69BB2D', ";
$event .= "dragBgColor: '#69BB2D',"; 
$event .= "borderColor: '#69BB2D',  "; 
$event .= "customStyle: 'cursor: default;',"; 
$event .= "isPending: false, ";
$event .= "isFocused: false, ";
$event .= "isPrivate: false, ";
$event .= "isVisible: true,";
$event .= "location: '".$row['leaguecountry'] ." ". $row['leaguename']."', ";
$event .= "attendees: '', ";
$event .= "recurrenceRule: '',";

$event .= "title: '".$row['homename'].' - '.$row['awayname'].' '.$row['result']   ."',";
$event .= "start: '".$time."',";
$event .= "end: '".$time."',  }";
$events[] = $event;
}

//echo '<pre>'.print_r($events,true).'</pre>';
$calendeer_events = implode(",",$events);
//echo '<pre>'.print_r($calendeer_events,true).'</pre>';

?>
<body>  
<div class="container">
  
<link rel="stylesheet" type="text/css" href="https://uicdn.toast.com/tui.time-picker/latest/tui-time-picker.css">
<link rel="stylesheet" type="text/css" href="https://uicdn.toast.com/tui.date-picker/latest/tui-date-picker.css">
  
  
<?php
if (version_compare(JVERSION, '4.0.0', 'ge'))
{
//Factory::getDocument()->addStyleSheet("https://uicdn.toast.com/calendar/latest/toastui-calendar.css");  
//Factory::getDocument()->addStyleSheet("https://uicdn.toast.com/calendar/1.12.10/toastui-calendar.css");    
  
//Factory::getDocument()->addStyleSheet("https://uicdn.toast.com/tui-calendar/latest/tui-calendar.css");    
Factory::getDocument()->addStyleSheet(Uri::base() . 'modules' . DIRECTORY_SEPARATOR . $module->module . DIRECTORY_SEPARATOR . 'tuicalendar/dist' . DIRECTORY_SEPARATOR . 'tui-calendar' . '.css');  
Factory::getDocument()->addStyleSheet(Uri::base() . 'modules' . DIRECTORY_SEPARATOR . $module->module . DIRECTORY_SEPARATOR . 'tuicalendar/css' . DIRECTORY_SEPARATOR . 'default' . '.css');
Factory::getDocument()->addStyleSheet(Uri::base() . 'modules' . DIRECTORY_SEPARATOR . $module->module . DIRECTORY_SEPARATOR . 'tuicalendar/css' . DIRECTORY_SEPARATOR . 'icons' . '.css');  
}
elseif (version_compare(JVERSION, '3.0.0', 'ge'))
{
  
//Factory::getDocument()->addStyleSheet("https://uicdn.toast.com/calendar/latest/toastui-calendar.css");  
//Factory::getDocument()->addStyleSheet("https://uicdn.toast.com/calendar/1.12.10/toastui-calendar.css");    
  
//Factory::getDocument()->addStyleSheet("https://uicdn.toast.com/tui-calendar/latest/tui-calendar.css");    
Factory::getDocument()->addStyleSheet(Uri::base() . 'modules' . DIRECTORY_SEPARATOR . $module->module . DIRECTORY_SEPARATOR . 'tuicalendar/dist' . DIRECTORY_SEPARATOR . 'tui-calendar' . '.css');  
Factory::getDocument()->addStyleSheet(Uri::base() . 'modules' . DIRECTORY_SEPARATOR . $module->module . DIRECTORY_SEPARATOR . 'tuicalendar/css' . DIRECTORY_SEPARATOR . 'default' . '.css');
Factory::getDocument()->addStyleSheet(Uri::base() . 'modules' . DIRECTORY_SEPARATOR . $module->module . DIRECTORY_SEPARATOR . 'tuicalendar/css' . DIRECTORY_SEPARATOR . 'icons' . '.css');  
  
}  
  

  
?>
<style>
.calendar {
    position: absolute;
    left: 0;
    right: 0;
    bottom: 5px;
    top: 64px;
}  
  
  
</style>
  
  <!--
<div id="lnb">
        <div class="lnb-new-schedule">
            <button id="btn-new-schedule" type="button" class="btn btn-default btn-block lnb-new-schedule-btn" data-toggle="modal">
                New schedule</button>
        </div>
        <div id="lnb-calendars" class="lnb-calendars">
            <div>
                <div class="lnb-calendars-item">
                    <label>
                        <input class="tui-full-calendar-checkbox-square" type="checkbox" value="all" checked>
                        <span></span>
                        <strong>View all</strong>
                    </label>
                </div>
            </div>
            <div id="calendarList" class="lnb-calendars-d1">
            </div>
        </div>
        <div class="lnb-footer">
            © NHN Corp.
        </div>
    </div>
  -->
<div id="calender-right" class="code-html">
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
        <button type="button" class="btn btn-default btn-sm move-today" data-action="move-today">Today</button>
        <button type="button" class="btn btn-default btn-sm move-day" data-action="move-prev">
          <i class="calendar-icon ic-arrow-line-left" data-action="move-prev"></i>
        </button>
        <button type="button" class="btn btn-default btn-sm move-day" data-action="move-next">
          <i class="calendar-icon ic-arrow-line-right" data-action="move-next"></i>
        </button>
      </span>          
                      
                      
            <span id="renderRange" class="render-range"></span>
<div class="promo_card" id="post">
	<h2>Custom popUp Post </h2>
</div>
<div class="promo_card" id="event">
	<h2>Custom popUp Event </h2>
</div>
<div class="promo_card" id="offer">
	<h2>Custom popUp Offer </h2>
</div>
<div class="promo_card" id="create">
	<h2>Custom Create Schedule popUp </h2>
</div>
                      
        </div>
        <div id="calendar" class="calendar" style="height: 600px;width: 100%;"></div>
    </div>  
  
  
<script type="text/javascript" src="https://uicdn.toast.com/tui.code-snippet/latest/tui-code-snippet.min.js"></script>
<script type="text/javascript" src="https://uicdn.toast.com/tui.dom/v3.0.0/tui-dom.js"></script>
<script type="text/javascript" src="https://uicdn.toast.com/tui.time-picker/latest/tui-time-picker.min.js"></script>
<script type="text/javascript" src="https://uicdn.toast.com/tui.date-picker/latest/tui-date-picker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.20.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/chance/1.0.13/chance.min.js"></script>  
<script src="https://uicdn.toast.com/tui-calendar/latest/tui-calendar.js"></script>

<?php
//$doc->addScript(Uri::base() . 'modules' . DIRECTORY_SEPARATOR . $module->module . DIRECTORY_SEPARATOR . 'tuicalendar/dist' . DIRECTORY_SEPARATOR . 'tui-calendar' . '.js');                      
?>                      
  
<script>  
var responseevents = '';
var month = <?php echo $month; ?>;
var year = <?php echo $year; ?>;
var day = <?php echo $day; ?>;

var daterangevon = '';
var daterangebis = '';

var params = <?php echo $params; ?>;

var CalendarList = [];

function CalendarInfo() {
    this.id = null;
    this.name = null;
    this.checked = true;
    this.color = null;
    this.bgColor = null;
    this.borderColor = null;
    this.dragBgColor = null;
}

function addCalendar(calendar) {
    CalendarList.push(calendar);
}

function findCalendar(id) {
    var found;

    CalendarList.forEach(function(calendar) {
        if (calendar.id === id) {
            found = calendar;
        }
    });

    return found || CalendarList[0];
}

function hexToRGBA(hex) {
    var radix = 16;
    var r = parseInt(hex.slice(1, 3), radix),
        g = parseInt(hex.slice(3, 5), radix),
        b = parseInt(hex.slice(5, 7), radix),
        a = parseInt(hex.slice(7, 9), radix) / 255 || 1;
    var rgba = 'rgba(' + r + ', ' + g + ', ' + b + ', ' + a + ')';

    return rgba;
}

(function() {
    var calendar;
    var id = 0;

    calendar = new CalendarInfo();
    id += 1;
    calendar.id = String(id);
    calendar.name = 'Games';
    calendar.color = '#624AC0';
    calendar.bgColor = '#F0EFF6';
    calendar.dragBgColor = '#F0EFF6';
    calendar.borderColor = '#F0EFF6';
    addCalendar(calendar);

    calendar = new CalendarInfo();
    id += 1;
    calendar.id = String(id);
    calendar.name = 'Birthday';
    calendar.color = '#FF8C1A';
    calendar.bgColor = '#FDF8F3';
    calendar.dragBgColor = '#FDF8F3';
    calendar.borderColor = '#FDF8F3';
    addCalendar(calendar);

    calendar = new CalendarInfo();
    id += 1;
    calendar.id = String(id);
    calendar.name = 'Offer';
    calendar.color = '#578E1C';
    calendar.bgColor = '#EEF8F0';
    calendar.dragBgColor = '#EEF8F0';
    calendar.borderColor = '#EEF8F0';
    addCalendar(calendar);
})();


(function(window, Calendar) {

    var cal, resizeThrottled;
    var useCreationPopup = true;
    var useDetailPopup = true;
    var datePicker, selectedCalendar;

    cal = new Calendar('#calendar', {
        defaultView: 'month',
        useCreationPopup: useCreationPopup,
        useDetailPopup: useDetailPopup,
        calendars: CalendarList,
        template: {
            milestone: function(model) {
                return '<span class="calendar-font-icon ic-milestone-b"></span> <span style="background-color: ' + model.bgColor + '">' + model.title + '</span>';
            },
            allday: function(schedule) {
                return getTimeTemplate(schedule, true);
            },
            time: function(schedule) {
                return getTimeTemplate(schedule, false);
            }
        }
    });

    // event handlers
    cal.on({
        'clickMore': function(e) {
            console.log('clickMore', e);
        },
        'clickSchedule': function(e) {
            // var topValue;
            // var leftValue;
            // topValue = (e.event.clientY/2)+45;
            // leftValue = e.event.clientX;
            // if ( e.schedule.calendarId === '1' ) {
            //     console.log('clickSchedule', e.schedule.calendarId);
            // 				$("#post").fadeIn();
            // $("#offer").fadeOut();
            // $("#event").fadeOut();
            // $("#create").fadeOut();
            //     $(".promo_card").css({
            //         top: topValue,
            //         left: leftValue
            //     })
            //     return;
            // }
            // if ( e.schedule.calendarId === '2' ) {
            //     console.log('clickSchedule', e.schedule.calendarId);
            // 				$("#event").fadeIn();
            // $("#post").fadeOut();
            // $("#offer").fadeOut();
            // $("#create").fadeOut();
            //     $(".promo_card").css({
            //         top: topValue,
            //         left: leftValue
            //     })
            //     return;
            // }
            // if ( e.schedule.calendarId === '3' ) {
            //     console.log('clickSchedule', e.schedule.calendarId);
            // 				$("#offer").fadeIn();
            // $("#post").fadeOut();
            // $("#event").fadeOut();
            // $("#create").fadeOut();
            //     $(".promo_card").css({
            //         top: topValue,
            //         left: leftValue
            //     })
            //     return;
            // }
            // console.log('ada ', e.event.clientX)
            // if( e.event.clientX > (window.windth - 200) ) {
            // }
            // console.log('clickSchedule', e);
        },
        'clickDayname': function(date) {
            console.log('clickDayname', date);
        },
        'beforeCreateSchedule': function(e) {

            // $("#create").fadeIn();
									
            saveNewSchedule(e);
        },
        'beforeUpdateSchedule': function(e) {
            var schedule = e.schedule;
            var changes = e.changes;

            console.log('beforeUpdateSchedule', e);

            cal.updateSchedule(schedule.id, schedule.calendarId, changes);
            refreshScheduleVisibility();
        },
        'beforeDeleteSchedule': function(e) {
            console.log('beforeDeleteSchedule', e);
            cal.deleteSchedule(e.schedule.id, e.schedule.calendarId);
        },
        'afterRenderSchedule': function(e) {
            var schedule = e.schedule;
            // var element = cal.getElement(schedule.id, schedule.calendarId);
            // console.log('afterRenderSchedule', element);
        },
        'clickTimezonesCollapseBtn': function(timezonesCollapsed) {
            console.log('timezonesCollapsed', timezonesCollapsed);

            if (timezonesCollapsed) {
                cal.setTheme({
                    'week.daygridLeft.width': '77px',
                    'week.timegridLeft.width': '77px'
                });
            } else {
                cal.setTheme({
                    'week.daygridLeft.width': '60px',
                    'week.timegridLeft.width': '60px'
                });
            }

            return true;
        }
    });

    function getTimeTemplate(schedule, isAllDay) {
        var html = [];
        var start = moment(schedule.start.toUTCString());
        if (!isAllDay) {
            html.push('<strong>' + start.format('HH:mm') + '</strong> ');
        }
        if (schedule.isPrivate) {
            html.push('<span class="calendar-font-icon ic-lock-b"></span>');
            html.push(' Private');
        } else {
            if (schedule.isReadOnly) {
                html.push('<span class="calendar-font-icon ic-readonly-b"></span>');
            } else if (schedule.recurrenceRule) {
                html.push('<span class="calendar-font-icon ic-repeat-b"></span>');
            } else if (schedule.attendees.length) {
                html.push('<span class="calendar-font-icon ic-user-b"></span>');
            } else if (schedule.location) {
                html.push('<span class="calendar-font-icon ic-location-b"></span>');
            }
            html.push(' ' + schedule.title);
        }

        return html.join('');
    }

  
  
 /**
     * A listener for click the menu
     * @param {Event} e - click event
     */
    function onClickMenu(e) {
        var target = jQuery(e.target).closest('a[role="menuitem"]')[0];
        var action = getDataAction(target);
        var options = cal.getOptions();
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
                viewName = cal.getViewName();

                target.querySelector('input').checked = options.month.narrowWeekend;
                break;
            case 'toggle-start-day-1':
                options.month.startDayOfWeek = options.month.startDayOfWeek ? 0 : 1;
                options.week.startDayOfWeek = options.week.startDayOfWeek ? 0 : 1;
                viewName = cal.getViewName();

                target.querySelector('input').checked = options.month.startDayOfWeek;
                break;
            case 'toggle-workweek':
                options.month.workweek = !options.month.workweek;
                options.week.workweek = !options.week.workweek;
                viewName = cal.getViewName();

                target.querySelector('input').checked = !options.month.workweek;
                break;
            default:
                break;
        }

        cal.setOptions(options, true);
        cal.changeView(viewName, true);

        setDropdownCalendarType();
        setRenderRangeText();
        setSchedules();
        
        
if (viewName === 'day') {
console.log('onClickMenu monat: ' + moment(cal.getDate().getTime()).format('MM') );              
console.log('onClickMenu jahr: ' + moment(cal.getDate().getTime()).format('YYYY') );             
console.log('onClickMenu tag: ' + moment(cal.getDate().getTime()).format('DD') );  
month = moment(cal.getDate().getTime()).format('MM');  
year = moment(cal.getDate().getTime()).format('YYYY');
day = moment(cal.getDate().getTime()).format('DD');              
}  
else if (viewName === 'month' &&
 (!options.month.visibleWeeksCount || options.month.visibleWeeksCount > 4)) {
console.log('onClickMenu monat: ' + moment(cal.getDate().getTime()).format('MM') );              
console.log('onClickMenu jahr: ' + moment(cal.getDate().getTime()).format('YYYY') );             
console.log('onClickMenu tag: ' + moment(cal.getDate().getTime()).format('DD') ); 
month = moment(cal.getDate().getTime()).format('MM');  
year = moment(cal.getDate().getTime()).format('YYYY');  
}
else {            
daterangevon = moment(cal.getDateRangeStart().getTime()).format('YYYY-MM-DD');
daterangebis = moment(cal.getDateRangeEnd().getTime()).format('YYYY-MM-DD');
}
console.log('onClickMenu month: ' + month );
console.log('onClickMenu year: ' + year );
console.log('onClickMenu day: ' + day );
console.log('onClickMenu daterangevon: ' + daterangevon );
console.log('onClickMenu daterangebis: ' + daterangebis );
        
        
request = {
'option' : 'com_ajax',
'module' : 'sportsmanagement_calendar',
'formvaluemonth'   : month,
'formvalueyear'   : year,
'formvalueday'   : day,
'daterangevon'   : daterangevon,
'daterangebis'   : daterangebis,
'viewname'   : viewName,
'params'   : params,
'format' : 'raw'
};
  
jQuery.ajax({
type   : 'POST',
data   : request,
async: false,
success: eventsaved
});        
      
      
    } 
  
  
    function onClickNavi(e) {
        var action = getDataAction(e.target);
var options = cal.getOptions();
var viewName = cal.getViewName();
      console.log('action:  ', action)
  
        switch (action) {
            case 'move-prev':
                cal.prev();
                break;
            case 'move-next':
                cal.next();
                break;
            case 'move-today':
                cal.today();
                break;
            default:
                return;
        }

console.log('onClickNavi viewName: ' + viewName );
console.log('onClickNavi options: ' + JSON.stringify(options) );
if (viewName === 'day') {
console.log('onClickNavi monat: ' + moment(cal.getDate().getTime()).format('MM') );              
console.log('onClickNavi jahr: ' + moment(cal.getDate().getTime()).format('YYYY') );             
console.log('onClickNavi tag: ' + moment(cal.getDate().getTime()).format('DD') );  
month = moment(cal.getDate().getTime()).format('MM');  
year = moment(cal.getDate().getTime()).format('YYYY');
day = moment(cal.getDate().getTime()).format('DD');              
}  
else if (viewName === 'month' &&
 (!options.month.visibleWeeksCount || options.month.visibleWeeksCount > 4)) {
console.log('onClickNavi monat: ' + moment(cal.getDate().getTime()).format('MM') );              
console.log('onClickNavi jahr: ' + moment(cal.getDate().getTime()).format('YYYY') );             
console.log('onClickNavi tag: ' + moment(cal.getDate().getTime()).format('DD') ); 
month = moment(cal.getDate().getTime()).format('MM');  
year = moment(cal.getDate().getTime()).format('YYYY');  
}
else {            
daterangevon = moment(cal.getDateRangeStart().getTime()).format('YYYY-MM-DD');
daterangebis = moment(cal.getDateRangeEnd().getTime()).format('YYYY-MM-DD');
}




console.log('onClickNavi month: ' + month );
console.log('onClickNavi year: ' + year );
console.log('onClickNavi day: ' + day );
console.log('onClickNavi daterangevon: ' + daterangevon );
console.log('onClickNavi daterangebis: ' + daterangebis );

request = {
'option' : 'com_ajax',
'module' : 'sportsmanagement_calendar',
'formvaluemonth'   : month,
'formvalueyear'   : year,
'formvalueday'   : day,
'daterangevon'   : daterangevon,
'daterangebis'   : daterangebis,
'viewname'   : viewName,
'params'   : params,
'format' : 'raw'
};
  
jQuery.ajax({
type   : 'POST',
data   : request,
async: false,
success: eventsaved
});              


        setRenderRangeText();
        //setSchedules();
    }

    function onNewSchedule() {
        var title = jQuery('#new-schedule-title').val();
        var location = jQuery('#new-schedule-location').val();
        var isAllDay = document.getElementById('new-schedule-allday').checked;
        var start = datePicker.getStartDate();
        var end = datePicker.getEndDate();
        var calendar = selectedCalendar ? selectedCalendar : CalendarList[0];

        if (!title) {
            return;
        }

        console.log('calendar.id ', calendar.id)
        cal.createSchedules([{
            id: '1',
            calendarId: calendar.id,
            title: title,
            isAllDay: isAllDay,
            start: start,
            end: end,
            category: isAllDay ? 'allday' : 'time',
            dueDateClass: '',
            color: calendar.color,
            bgColor: calendar.bgColor,
            dragBgColor: calendar.bgColor,
            borderColor: calendar.borderColor,
            raw: {
                location: location
            },
            state: 'Busy'
        }]);

        jQuery('#modal-new-schedule').modal('hide');
    }

    function onChangeNewScheduleCalendar(e) {
        var target = jQuery(e.target).closest('a[role="menuitem"]')[0];
        var calendarId = getDataAction(target);
        changeNewScheduleCalendar(calendarId);
    }

    function changeNewScheduleCalendar(calendarId) {
        var calendarNameElement = document.getElementById('calendarName');
        var calendar = findCalendar(calendarId);
        var html = [];

        html.push('<span class="calendar-bar" style="background-color: ' + calendar.bgColor + '; border-color:' + calendar.borderColor + ';"></span>');
        html.push('<span class="calendar-name">' + calendar.name + '</span>');

        calendarNameElement.innerHTML = html.join('');

        selectedCalendar = calendar;
    }

    function createNewSchedule(event) {
        var start = event.start ? new Date(event.start.getTime()) : new Date();
        var end = event.end ? new Date(event.end.getTime()) : moment().add(1, 'hours').toDate();

        if (useCreationPopup) {
            cal.openCreationPopup({
                start: start,
                end: end
            });
        }
    }
    function saveNewSchedule(scheduleData) {
        console.log('scheduleData ', scheduleData)
        var calendar = scheduleData.calendar || findCalendar(scheduleData.calendarId);
        var schedule = {
            id: '1',
            title: scheduleData.title,
            // isAllDay: scheduleData.isAllDay,
            start: scheduleData.start,
            end: scheduleData.end,
            category: 'allday',
            // category: scheduleData.isAllDay ? 'allday' : 'time',
            // dueDateClass: '',
            color: calendar.color,
            bgColor: calendar.bgColor,
            dragBgColor: calendar.bgColor,
            borderColor: calendar.borderColor,
            location: scheduleData.location,
            // raw: {
            //     class: scheduleData.raw['class']
            // },
            // state: scheduleData.state
        };
        if (calendar) {
            schedule.calendarId = calendar.id;
            schedule.color = calendar.color;
            schedule.bgColor = calendar.bgColor;
            schedule.borderColor = calendar.borderColor;
        }

        cal.createSchedules([schedule]);

        refreshScheduleVisibility();
    }


    function refreshScheduleVisibility() {
        var calendarElements = Array.prototype.slice.call(document.querySelectorAll('#calendarList input'));

        CalendarList.forEach(function(calendar) {
            cal.toggleSchedules(calendar.id, !calendar.checked, false);
        });

        cal.render(true);

        calendarElements.forEach(function(input) {
            var span = input.nextElementSibling;
            span.style.backgroundColor = input.checked ? span.style.borderColor : 'transparent';
        });
    }


    function setRenderRangeText() {
        var renderRange = document.getElementById('renderRange');
        var options = cal.getOptions();
        var viewName = cal.getViewName();
        var html = [];
        if (viewName === 'day') {
            html.push(moment(cal.getDate().getTime()).format('MMM YYYY DD'));
        } else if (viewName === 'month' &&
            (!options.month.visibleWeeksCount || options.month.visibleWeeksCount > 4)) {
            html.push(moment(cal.getDate().getTime()).format('MMM YYYY'));
        } else {
            html.push(moment(cal.getDateRangeStart().getTime()).format('MMM YYYY DD'));
            html.push(' ~ ');
            html.push(moment(cal.getDateRangeEnd().getTime()).format(' MMM DD'));
        }
        renderRange.innerHTML = html.join('');
    }

    function setSchedules() {
        cal.clear();
        var schedules = [
            {id: 489273, 
            title: 'Workout for 2020-04-05', 
            isAllDay: false, 
            start: '2020-03-03T11:30:00+09:00', 
            end: '2020-03-03T12:00:00+09:00', 
            goingDuration: 30, 
            comingDuration: 30, 
            color: '#ffffff', isVisible: true, 
            bgColor: '#69BB2D', 
            dragBgColor: '#69BB2D', 
            borderColor: '#69BB2D', 
            calendarId: '1', 
            category: 'allday', 
            dueDateClass: '', 
            customStyle: 'cursor: default;', 
            isPending: false, 
            isFocused: false, 
            isReadOnly: false, 
            isPrivate: false, 
            location: '', 
            attendees: '', 
            recurrenceRule: '', 
            state: ''},
            {id: 489273, title: 'Workout for 2020-04-05', isAllDay: false, start: '2020-03-11T11:30:00+09:00', end: '2020-03-11T12:00:00+09:00', goingDuration: 30, comingDuration: 30, color: '#ffffff', isVisible: true, bgColor: '#69BB2D', dragBgColor: '#69BB2D', borderColor: '#69BB2D', calendarId: '2', category: 'allday', dueDateClass: '', customStyle: 'cursor: default;', isPending: false, isFocused: false, isReadOnly: false, isPrivate: false, location: '', attendees: '', recurrenceRule: '', state: ''},
            {id: 18073, title: 'completed with blocks', isAllDay: false, start: '2020-03-20T09:00:00+09:00', end: '2020-03-20T10:00:00+09:00', color: '#ffffff', isVisible: true, bgColor: '#54B8CC', dragBgColor: '#54B8CC', borderColor: '#54B8CC', calendarId: '1', category: 'allday', dueDateClass: '', customStyle: '', isPending: false, isFocused: false, isReadOnly: false, isPrivate: false, location: '', attendees: '', recurrenceRule: '', state: ''},
            {id: 18073, title: 'completed with blocks', isAllDay: false, start: '2020-03-25T09:00:00+09:00', end: '2020-03-25T10:00:00+09:00', color: '#ffffff', isVisible: true, bgColor: '#54B8CC', dragBgColor: '#54B8CC', borderColor: '#54B8CC', calendarId: '1', category: 'allday', dueDateClass: '', customStyle: '', isPending: false, isFocused: false, isReadOnly: false, isPrivate: false, location: '', attendees: '', recurrenceRule: '', state: ''},
            {id: 18073, title: 'completed with blocks', isAllDay: false, start: '2020-01-28T09:00:00+09:00', end: '2020-01-28T10:00:00+09:00', color: '#ffffff', isVisible: true, bgColor: '#54B8CC', dragBgColor: '#54B8CC', borderColor: '#54B8CC', calendarId: '1', category: 'allday', dueDateClass: '', customStyle: '', isPending: false, isFocused: false, isReadOnly: false, isPrivate: false, location: '', attendees: '', recurrenceRule: '', state: ''},
            {id: 18073, title: 'completed with blocks', isAllDay: false, start: '2020-03-07T09:00:00+09:00', end: '2020-03-07T10:00:00+09:00', color: '#ffffff', isVisible: true, bgColor: '#54B8CC', dragBgColor: '#54B8CC', borderColor: '#54B8CC', calendarId: '1', category: 'allday', dueDateClass: '', customStyle: '', isPending: false, isFocused: false, isReadOnly: false, isPrivate: false, location: '', attendees: '', recurrenceRule: '', state: ''},
            {id: 18073, title: 'Time Schedule Need BGCOLOR', isAllDay: false, start: '2020-03-28T09:00:00+09:00', end: '2020-03-28T10:00:00+09:00', color: '#ffffff', isVisible: true, bgColor: '#54B8CC', dragBgColor: '#54B8CC', borderColor: '#54B8CC', calendarId: '1', category: 'time', dueDateClass: '', customStyle: '', isPending: false, isFocused: false, isReadOnly: false, isPrivate: false, location: '', attendees: '', recurrenceRule: '', state: ''},
            {id: 18073, title: 'Time Schedule Need BGCOLOR', isAllDay: false, start: '2020-03-17T09:00:00+09:00', end: '2020-03-17T10:00:00+09:00', color: '#ffffff', isVisible: true, bgColor: '#54B8CC', dragBgColor: '#54B8CC', borderColor: '#54B8CC', calendarId: '3', category: 'time', dueDateClass: '', customStyle: '', isPending: false, isFocused: false, isReadOnly: false, isPrivate: false, location: '', attendees: '', recurrenceRule: '', state: ''}
        ];
        // generateSchedule(cal.getViewName(), cal.getDateRangeStart(), cal.getDateRangeEnd());
        cal.createSchedules(schedules);
        // cal.createSchedules(schedules);
        refreshScheduleVisibility();
    }

    function setEventListener() {
        jQuery('#menu-navi').on('click', onClickNavi);
        jQuery('.dropdown-menu a[role="menuitem"]').on('click', onClickMenu);
        //jQuery('#lnb-calendars').on('change', onChangeCalendars);

        jQuery('#btn-save-schedule').on('click', onNewSchedule);
        jQuery('#btn-new-schedule').on('click', createNewSchedule);

        jQuery('#dropdownMenu-calendars-list').on('click', onChangeNewScheduleCalendar);

        window.addEventListener('resize', resizeThrottled);
    }

    function getDataAction(target) {
        return target.dataset ? target.dataset.action : target.getAttribute('data-action');
    }

    resizeThrottled = tui.util.throttle(function() {
        cal.render();
    }, 50);

  function setDropdownCalendarType() {
        var calendarTypeName = document.getElementById('calendarTypeName');
        var calendarTypeIcon = document.getElementById('calendarTypeIcon');
        var options = cal.getOptions();
        var type = cal.getViewName();
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
    
    console.log('type ', type)
      console.log('iconClassName ', iconClassName)

        calendarTypeName.innerHTML = type;
        calendarTypeIcon.className = iconClassName;
    }
  
    window.cal = cal;

     setDropdownCalendarType();
    setRenderRangeText();
    setSchedules();
    setEventListener();
  
//jQuery('#menu-navi').on('click', onClickNavi);
//jQuery('.dropdown-menu a[role="menuitem"]').on('click', onClickMenu);  
  
})(window, tui.Calendar);

// set calendars
(function() {
  /**
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
  */
})();


console.log('events ', <?php echo $calendeer_events; ?>)
  
cal.createSchedules([
  <?php echo $calendeer_events; ?>
 ,
  
]);



function eventsaved(response) 
{  
console.log('events response: ', response)  
  /**
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
*/  
//scriptstring = 'calendar.clear();calendar.createEvents([' + scriptstring + ',]);'  ;
scriptstring = 'cal.clear();cal.createSchedules([' + response + ',]);'  ;
//scriptstring = 'cal.createSchedules([' + response + ',]);'  ;  

jQuery('<script>' + scriptstring + '</' + 'script>').appendTo(document.body);   
//cal.setRenderRangeText();  
}

//jQuery('#menu-navi').on('click', onClickNavi);
//jQuery('.dropdown-menu a[role="menuitem"]').on('click', onClickMenu);


</script>    
  

  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
</div>  
</body>  