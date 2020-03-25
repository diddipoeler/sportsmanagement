<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      jevents.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   mod_sportsmanagement_calendar
 * @subpackage rules
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;

class JEventsConnector extends JSMCalendar{
  static $xparams;
  static $params;
  static $prefix;
  static $jevent;
  
  function getEntries ($caldates, $params, &$matches) {
    
    if (!JEventsConnector::_checkJEvents()){
      return;
    }
    $year = substr($caldates['start'], 0, 4);
    $month = (substr($caldates['start'], 5, 1)=='0') ? substr($caldates['start'], 6, 1) : substr($caldates['start'], 5, 2);
    
    JEventsConnector::$xparams = $params;
    /**
	 * Gets calendar data for use in main calendar and module
	 *
	 * @param int $year
	 * @param int $month
	 * @param int $day
	 * @param boolean $short - use true for module which only requires knowledge of if dat has an event
	 * @param boolean $veryshort - use true for module which only requires dates and nothing about events
	 * @return array - calendar data array
	 */
    $data = JEventsConnector::$jevent->getCalendarData( $year, $month, 1 );

    $formatted = JEventsConnector::formatEntries($data['dates'], $matches);
    return $formatted;
  }
  function formatEntries( $rows, &$matches ) {
    $newrows = array();
    
    foreach ($rows AS $key => $row) {
      if (!empty($row['events'])){
      
        foreach($row['events'] AS $event) {
          $newrow = array();
          $user = Factory::getUser();
          if ($user->id == 62) {

          }
          $newrow['link'] = JEventsConnector::buildLink ($event, $row['year'], $row['month']);
          $newrow['date'] = strftime('%Y-%m-%d', $row['cellDate']). ' '.strftime('%H:%M', $event->_dtstart);
          $newrow['type'] = 'jevents';
          $newrow['time'] = '';
          if ($event->_alldayevent != 1) {
            $newrow['time'] = strftime('%H:%M', $event->_dtstart);
            $newrow['time'] .= ($event->_dtstart != $event->_dtend AND $event->_noendtime == 0) ? '-'.strftime('%H:%M', $event->_dtend) : '';
          }
          $newrow['headingtitle'] = JEventsConnector::$xparams->get('jevents_text', 'JEvents');
          $newrow['name'] = '';
          $newrow['title'] = $event->_title;
          $newrow['location'] = $event->_location;
          $newrow['color'] = $event->_color_bar;
		      $newrow['matchcode'] = 0;
		      $newrow['project_id'] = 0;
		      $matches[] = $newrow;
		    }
		  }
		}

  } 
  private function _raiseError($message) {
    echo $message;
  }
  private function _checkJEvents() {

    if (file_exists(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_jevents'.DIRECTORY_SEPARATOR.'mod.defines.php')
        AND 
        file_exists(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_jevents'.DIRECTORY_SEPARATOR.'libraries'.DIRECTORY_SEPARATOR.'datamodel.php')
        )
    {
      require_once JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_jevents'.DIRECTORY_SEPARATOR.'mod.defines.php';
      require_once JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_jevents'.DIRECTORY_SEPARATOR.'libraries'.DIRECTORY_SEPARATOR.'datamodel.php';
    }
    else { 
      JEventsConnector::_raiseError('Required files not found! This connector needs JEvents 1.5.2 to be installed');
      return false; 
    }
    if (class_exists('JEventsDataModel')) {
      JEventsConnector::$jevent = new JEventsDataModel();
    }
    else {
      JEventsConnector::_raiseError('Required class not found! This connector needs JEvents 1.5.2 installed');
      return false;
    }
    if (!is_callable(array(JEventsConnector::$jevent, 'getCalendarData'))) {
      JEventsConnector::_raiseError('Required function "getRangeData" is not callable! This connector needs JEvents 1.5.2 installed');
      return false; 
    }

    return true;
  }
  function buildLink (&$event, $year, $month) {
    require_once JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_jevents'.DIRECTORY_SEPARATOR.'router.php';
    $link = 'index.php?option=com_jevents&amp;task=icalrepeat.detail&amp;evid='
    .$event->_eventdetail_id.'&amp;year='.$year.'&amp;month='.$month.'&amp;day='
    .$event->_dup.'&amp;uid='.$event->_uid;
    return Route::_($link);
  }
} 

// jevents returns a list like that:
/*
Array
(
    [countDisplay] => 0
    [monthType] => current
    [month] => 09
    [year] => 2009
    [today] => 
    [d] => 28
    [d0] => 28
    [link] => /jl15/index.php?option=com_jevents&task=day.listevents&year=2009&month=09&day=28&Itemid=58
    [cellDate] => 1254088800
    [events] => Array
        (
            [0] => jIcalEventRepeat Object
                (
                    [_nextRepeat:private] => 
                    [_prevRepeat:private] => 
                    [_icsid] => 1
                    [_repeats] => 
                    [data] => stdClass Object
                        (
                        )

                    [_unixstartdate] => 
                    [_unixenddate] => 
                    [_location] => Ahlen
                    [_contact] => 
                    [_extra_info] => 
                    [_color] => #3333FF
                    [_published] => 1
                    [_multiday] => 0
                    [_noendtime] => 0
                    [_catid] => 34
                    [_rp_id] => 11
                    [_eventid] => 2
                    [_eventdetail_id] => 2
                    [_duplicatecheck] => 6a88b0b55270f07916b4567dd1e8ef94
                    [_startrepeat] => 2009-09-28 00:00:00
                    [_endrepeat] => 2009-09-28 23:59:59
                    [_ev_id] => 2
                    [_uid] => 2961b56f25beee13f61065dfd925bb2b
                    [_refreshed] => 0000-00-00 00:00:00
                    [_created] => 
                    [_created_by] => 62
                    [_created_by_alias] => 
                    [_modified_by] => 62
                    [_rawdata] => 
                    [_recurrence_id] => 
                    [_detail_id] => 2
                    [_state] => 1
                    [_access] => 2
                    [_rr_id] => 2
                    [_freq] => WEEKLY
                    [_until] => 1259535600
                    [_untilraw] => 
                    [_count] => 99999
                    [_rinterval] => 1
                    [_bysecond] => 
                    [_byminute] => 
                    [_byhour] => 
                    [_byday] => MO
                    [_bymonthday] => 
                    [_byyearday] => 
                    [_byweekno] => 
                    [_bymonth] => 
                    [_bysetpos] => 
                    [_wkst] => 
                    [_evdet_id] => 2
                    [_dtstart] => 1254088800
                    [_dtstartraw] => 
                    [_duration] => 0
                    [_durationraw] => 
                    [_dtend] => 1254088799
                    [_dtendraw] => 
                    [_dtstamp] => 
                    [_class] => 
                    [_categories] => 
                    [_description] => 
                    [_geolon] => 0
                    [_geolat] => 0
                    [_priority] => 0
                    [_status] => 
                    [_summary] => test2
                    [_organizer] => 
                    [_url] => 
                    [_sequence] => 0
                    [_hits] => 0
                    [_yup] => 2009
                    [_mup] => 09
                    [_dup] => 28
                    [_ydn] => 2009
                    [_mdn] => 09
                    [_ddn] => 28
                    [_hup] => 0
                    [_minup] => 0
                    [_sup] => 0
                    [_hdn] => 23
                    [_mindn] => 59
                    [_sdn] => 59
                    [_interval] => 1
                    [_content] => 
                    [_title] => test2
                    [_publish_up] => 2009-09-28 00:00:00
                    [_reccurtype] => 0
                    [_reccurday] => 
                    [_reccurweekdays] => 
                    [_reccurweeks] => 
                    [_alldayevent] => 1
                    [_useCatColor] => 0
                    [_color_bar] => #3333FF
                    [_publish_down] => 2009-09-28 23:59:59
                    [_contactlink] => n/a
                    [eventDaysMonth] => Array
                        (
                            [1251756000] => 
                            [1251842400] => 
                            [1251928800] => 
                            [1252015200] => 
                            [1252101600] => 
                            [1252188000] => 
                            [1252274400] => 
                            [1252360800] => 
                            [1252447200] => 
                            [1252533600] => 
                            [1252620000] => 
                            [1252706400] => 
                            [1252792800] => 
                            [1252879200] => 
                            [1252965600] => 
                            [1253052000] => 
                            [1253138400] => 
                            [1253224800] => 
                            [1253311200] => 
                            [1253397600] => 
                            [1253484000] => 
                            [1253570400] => 
                            [1253656800] => 
                            [1253743200] => 
                            [1253829600] => 
                            [1253916000] => 
                            [1254002400] => 
                            [1254088800] => 1
                            [1254175200] => 
                            [1254261600] => 
                        )

                    [_startday] => 1254088800
                    [_endday] => 1254088800
                )

            [1] => jIcalEventRepeat Object
                (
                    [_nextRepeat:private] => 
                    [_prevRepeat:private] => 
                    [_icsid] => 1
                    [_repeats] => 
                    [data] => stdClass Object
                        (
                        )

                    [_unixstartdate] => 
                    [_unixenddate] => 
                    [_location] => 
                    [_contact] => 
                    [_extra_info] => 
                    [_color] => 
                    [_published] => 1
                    [_multiday] => 0
                    [_noendtime] => 1
                    [_catid] => 34
                    [_rp_id] => 21
                    [_eventid] => 3
                    [_eventdetail_id] => 3
                    [_duplicatecheck] => acbdc8b63e5e72eeb47f37760be76d3b
                    [_startrepeat] => 2009-09-28 13:00:00
                    [_endrepeat] => 2009-09-28 15:00:00
                    [_ev_id] => 3
                    [_uid] => a4c6f9280c32058b3ca564705c2f9728
                    [_refreshed] => 0000-00-00 00:00:00
                    [_created] => 
                    [_created_by] => 62
                    [_created_by_alias] => 
                    [_modified_by] => 62
                    [_rawdata] => 
                    [_recurrence_id] => 
                    [_detail_id] => 3
                    [_state] => 1
                    [_access] => 0
                    [_rr_id] => 3
                    [_freq] => WEEKLY
                    [_until] => 1259535600
                    [_untilraw] => 
                    [_count] => 99999
                    [_rinterval] => 1
                    [_bysecond] => 
                    [_byminute] => 
                    [_byhour] => 
                    [_byday] => MO
                    [_bymonthday] => 
                    [_byyearday] => 
                    [_byweekno] => 
                    [_bymonth] => 
                    [_bysetpos] => 
                    [_wkst] => 
                    [_evdet_id] => 3
                    [_dtstart] => 1254135600
                    [_dtstartraw] => 
                    [_duration] => 0
                    [_durationraw] => 
                    [_dtend] => 1254142800
                    [_dtendraw] => 
                    [_dtstamp] => 
                    [_class] => 
                    [_categories] => 
                    [_description] => 
                    [_geolon] => 0
                    [_geolat] => 0
                    [_priority] => 0
                    [_status] => 
                    [_summary] => test3 (no endtime)
                    [_organizer] => 
                    [_url] => 
                    [_sequence] => 0
                    [_hits] => 1
                    [_yup] => 2009
                    [_mup] => 09
                    [_dup] => 28
                    [_ydn] => 2009
                    [_mdn] => 09
                    [_ddn] => 28
                    [_hup] => 13
                    [_minup] => 0
                    [_sup] => 0
                    [_hdn] => 15
                    [_mindn] => 0
                    [_sdn] => 0
                    [_interval] => 1
                    [_content] => 
                    [_title] => test3 (no endtime)
                    [_publish_up] => 2009-09-28 13:00:00
                    [_reccurtype] => 0
                    [_reccurday] => 
                    [_reccurweekdays] => 
                    [_reccurweeks] => 
                    [_alldayevent] => 0
                    [_useCatColor] => 1
                    [_color_bar] => #ffffff
                    [_publish_down] => 2009-09-28 15:00:00
                    [_contactlink] => n/a
                    [eventDaysMonth] => Array
                        (
                            [1251756000] => 
                            [1251842400] => 
                            [1251928800] => 
                            [1252015200] => 
                            [1252101600] => 
                            [1252188000] => 
                            [1252274400] => 
                            [1252360800] => 
                            [1252447200] => 
                            [1252533600] => 
                            [1252620000] => 
                            [1252706400] => 
                            [1252792800] => 
                            [1252879200] => 
                            [1252965600] => 
                            [1253052000] => 
                            [1253138400] => 
                            [1253224800] => 
                            [1253311200] => 
                            [1253397600] => 
                            [1253484000] => 
                            [1253570400] => 
                            [1253656800] => 
                            [1253743200] => 
                            [1253829600] => 
                            [1253916000] => 
                            [1254002400] => 
                            [1254088800] => 1
                            [1254175200] => 
                            [1254261600] => 
                        )

                    [_startday] => 1254088800
                    [_endday] => 1254088800
                )

        )
)
*/
