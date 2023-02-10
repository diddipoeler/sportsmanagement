<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage mod_sportsmanagement_calendar
 * @file       default_bootstrap.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;

//$display = ($params->get('update_module') == 1) ? 'block' : 'none';
//$show_teamlist = ($params->get('show_teamslist') == 1) ? 'show' : 'hidden';

$cal       = new JSMCalendar; // This object creates the html for the calendar
$cal::getMatches($month, $year);

//echo '<pre>'.print_r($cal::$matches,true).'</pre>';

?>
  
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment-with-locales.min.js"></script>  
  
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.touchswipe/1.6.19/jquery.touchSwipe.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/arrobefr-jquery-calendar-bs4@1.0.3/dist/js/jquery-calendar.min.js"></script>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/arrobefr-jquery-calendar-bs4@1.0.3/dist/css/jquery-calendar.min.css">

  <script>
jQuery(document).ready(function($){
      moment.locale('fr');
      var now = moment();
  
   /**
       * Many events
       */
      var events = [
        {
          start: now.startOf('week').add(9, 'h').format('X'),
          end: now.startOf('week').add(10, 'h').format('X'),
          title: '1',
          content: 'Hello World! <br> <p>Foo Bar</p>',
          category:'Professionnal'
        }];
  
  /**
       * A daynote
       */
      var daynotes = [
        {
          time: now.startOf('week').add(15, 'h').add(30, 'm').format('X'),
          title: 'Leo\'s holiday',
          content: 'yo',
          category: 'holiday'
        }
      ];
  
     /**
       * Init the calendar
       */
      var calendar = $('#calendar').Calendar({
        locale: 'en',
        defaultView: {
         
            largeScreen: 'month',
            smallScreen: 'month'
          
        },
        
       // defaultView.largeScreen: 'month',
        //defaultView.smallScreen: 'month',
        weekday: {
          timeline: {
            intervalMinutes: 60,
            fromHour: 9
          }
        },
        events: events,
        daynotes: daynotes
      }).init();
  
  $('#calendar').on('Calendar.init', function(event, instance, before, current, after){
        console.log('event : Calendar.init');
        console.log(instance);
        console.log(before);
        console.log(current);
        console.log(after);
      });
      $('#calendar').on('Calendar.daynote-mouseenter', function(event, instance, elem){
        console.log('event : Calendar.daynote-mouseenter');
        console.log(instance);
        console.log(elem);
      });
      $('#calendar').on('Calendar.daynote-mouseleave', function(event, instance, elem){
        console.log('event : Calendar.daynote-mouseleave');
        console.log(instance);
        console.log(elem);
      });
      $('#calendar').on('Calendar.event-mouseenter', function(event, instance, elem){
        console.log('event : Calendar.event-mouseenter');
        console.log(instance);
        console.log(elem);
      });
      $('#calendar').on('Calendar.event-mouseleave', function(event, instance, elem){
        console.log('event : Calendar.event-mouseleave');
        console.log(instance);
        console.log(elem);
      });
      $('#calendar').on('Calendar.daynote-click', function(event, instance, elem, evt){
        console.log('event : Calendar.daynote-click');
        console.log(instance);
        console.log(elem);
        console.log(evt);
      });
      $('#calendar').on('Calendar.event-click', function(event, instance, elem, evt){
        console.log('event : Calendar.event-click');
        console.log(instance);
        console.log(elem);
        console.log(evt);
      });
  
    });  
  </script>
    <!--
  <div class="container-fluid ">
    <div class="row">
      <div class="col-xs-12">
        <h1>Wow ! That calendar works !</h1>
        <div id="calendar"></div>
      </div>
    </div>
  </div>
  
  -->
  
         <div id="calendar" style="width: 100%; height: 400px;"></div>
  
