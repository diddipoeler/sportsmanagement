<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage mod_sportsmanagement_calendar
 * @file       default_arrobefr.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * https://github.com/ArrobeFr/jquery-calendar
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
  /*
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
  */
  
$event .= "{start: '".$row['timestamp']."',";
$event .= "end: '".$row['timestamp'] + 3600 + 1800 + 900 ." ', ";
$event .= "title: '".$row['homename'].' - '.$row['awayname'].' '.$row['result']   ."',";  
$event .= "content: '".$row['leaguecountry'] ." ". $row['leaguename']."', ";  
$event .= "category: '".$row['leaguename']."',  }";  
$events[] = $event;
  
  
  
  
}

//echo '<pre>'.print_r($events,true).'</pre>';
$calendeer_events = implode(",",$events);
//echo '<pre>'.print_r($calendeer_events,true).'</pre>';

?>


<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.20.1/moment.min.js"></script>  
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.touchswipe/1.6.19/jquery.touchSwipe.js"></script>  
<script src="https://cdn.jsdelivr.net/npm/arrobefr-jquery-calendar-bs4@1.0.3/dist/js/jquery-calendar.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/arrobefr-jquery-calendar-bs4@1.0.3/dist/css/jquery-calendar.min.css">
  
  
<?php

  

  
?>
 <script>
    jQuery(document).ready(function(){
      moment.locale('en');
      var now = moment();
var month = <?php echo $month; ?>;
var year = <?php echo $year; ?>;
var day = <?php echo $day; ?>;

var daterangevon = '';
var daterangebis = '';
var viewName = 'arrobefr';  

var params = <?php echo $params; ?>;
      /**
       * Many events
       */
      var events = [
        <?php echo $calendeer_events; ?>
        ,
      ];

      /**
       * A daynote
       */
      var daynotes = [
        /*
        {
          time: now.startOf('week').add(15, 'h').add(30, 'm').format('X'),
          title: 'Leo\'s holiday',
          content: 'yo',
          category: 'holiday'
        }
        */
      ];

      /**
       * Init the calendar
       */
      var calendar = jQuery('#calendar').Calendar({
        locale: 'en',
        weekday: {
          timeline: {
            intervalMinutes: 30,
            fromHour: 9
          }
        },
        
        defaultView:{
        largeScreen: 'month', 
        smallScreen: 'month'
          },
        events: events,
        daynotes: daynotes
      }).init();


      /**
       * Listening for events
       */

      jQuery('#calendar').on('Calendar.init', function(event, instance, before, current, after){
        console.log('event : Calendar.init');
        console.log(event);
        console.log('instance -> ' + instance);
        console.log('before -> ' + before);
        console.log('current -> ' + current);
        
        console.log('current von -> ' + current[0]);
        console.log('current bis -> ' + current[1]);
        
        console.log('current von -> ' + moment.unix(current[0]).format("YYYY-MM-DD") ); 
        console.log('current bis -> ' + moment.unix(current[1]).format("YYYY-MM-DD") ); 
        
        console.log(current);
        
        request = {
'option' : 'com_ajax',
'module' : 'sportsmanagement_calendar',
'formvaluemonth'   : month,
'formvalueyear'   : year,
'formvalueday'   : day,
'daterangevon'   : moment.unix(current[0]).format("YYYY-MM-DD"),
'daterangebis'   : moment.unix(current[1]).format("YYYY-MM-DD"),
'viewname'   : viewName,
'params'   : params,
'format' : 'raw'
};
  
var responseText = jQuery.ajax({
type   : 'POST',
data   : request,
async: false,
success: function(data) {
        return data;
    }
  
}).responseText;            
        
        console.log('responseText -> ' + responseText);
        calendar.setEvents([  responseText

        ]);
        calendar.init;
        
        
        
        
        
        console.log('after -> ' + after);
      });
      jQuery('#calendar').on('Calendar.daynote-mouseenter', function(event, instance, elem){
        console.log('event : Calendar.daynote-mouseenter');
        console.log(instance);
        console.log(elem);
      });
      jQuery('#calendar').on('Calendar.daynote-mouseleave', function(event, instance, elem){
        console.log('event : Calendar.daynote-mouseleave');
        console.log(instance);
        console.log(elem);
      });
      jQuery('#calendar').on('Calendar.event-mouseenter', function(event, instance, elem){
        console.log('event : Calendar.event-mouseenter');
        console.log(instance);
        console.log(elem);
      });
      jQuery('#calendar').on('Calendar.event-mouseleave', function(event, instance, elem){
        console.log('event : Calendar.event-mouseleave');
        console.log(instance);
        console.log(elem);
      });
      jQuery('#calendar').on('Calendar.daynote-click', function(event, instance, elem, evt){
        console.log('event : Calendar.daynote-click');
        console.log(instance);
        console.log(elem);
        console.log(evt);
      });
      jQuery('#calendar').on('Calendar.event-click', function(event, instance, elem, evt){
        console.log('event : Calendar.event-click');
        console.log(instance);
        console.log(elem);
        console.log(evt);
      });
    });




function eventsaved(response) 
{  
var scriptstring = '';    
console.log('events response: ', response)  
 

scriptstring = 'Calendar.setEvents([' + response + ',]); calendar.init;'  ;


jQuery('<script>' + scriptstring + '</' + 'script>').appendTo(document.body);   

}


  </script>
    <div class="container-fluid px-4">
    <div class="row">
      <div class="col-xs-12">
        
        <div id="calendar" style="width: 100%;"></div>
      </div>
    </div>
  </div>






