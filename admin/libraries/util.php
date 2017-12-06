<?php
/**
 * GCalendar is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * GCalendar is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GCalendar.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package		GCalendar
 * @author		Digital Peak http://www.digital-peak.com
 * @copyright	Copyright (C) 2007 - 2013 Digital Peak. All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

if (!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}

JLoader::import('components.com_sportsmanagement.libraries.dbutil', JPATH_ADMINISTRATOR);
JLoader::import('components.com_sportsmanagement.libraries.GCalendar.GCalendarZendHelper', JPATH_ADMINISTRATOR);
JLoader::import('joomla.environment.browser');

if (!class_exists('Mustache')) {
	JLoader::import('components.com_sportsmanagement.libraries.mustache.Mustache', JPATH_ADMINISTRATOR);
}

class jsmGCalendarUtil 
{

	public static function getComponentParameter($key, $defaultValue = null) 
    {
        $app = JFactory::getApplication();
        
		$params = JComponentHelper::getParams('com_sportsmanagement');
		$value = $params->get($key, $defaultValue);
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' params<br><pre>'.print_r($params,true).'</pre>'),'Notice');


		if ($key == 'timezone' && empty($value)) {
			$user = JFactory::getUser();
			if ($user->get('id')) {
				$value = $user->getParam('timezone');
			}
			if (empty($value)) {
				$value = JFactory::getApplication()->getCfg('offset', 'UTC');
			}
		}
		return $value;
	}

	public static function getFrLanguage() {
		$conf = JFactory::getConfig();
		return $conf->get('config.language');
	}

	public static function getItemId($calendarId, $strict = false) 
    {
        $app = JFactory::getApplication();
        
		$component = JComponentHelper::getComponent('com_sportsmanagement');
		$menu = JFactory::getApplication()->getMenu();
		$items = $menu->getItems('component_id', $component->id);
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' component<br><pre>'.print_r($component,true).'</pre>'),'Notice');
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' menu<br><pre>'.print_r($menu,true).'</pre>'),'Notice');
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' items<br><pre>'.print_r($items,true).'</pre>'),'Notice');

		$default = null;
		if (is_array($items)) {
			foreach($items as $item) {
				$default = $item;
				$paramsItem	= $menu->getParams($item->id);
				$calendarids = $paramsItem->get('calendarids');
				if (empty($calendarids)) {
					$results = jsmGCalendarDBUtil::getAllCalendars();
					if ($results) {
						$calendarids = array();
						foreach ($results as $result) {
							$calendarids[] = $result->id;
						}
					}
				}
				$contains_gc_id = FALSE;
				if ($calendarids) {
					if ( is_array( $calendarids ) ) {
						$contains_gc_id = in_array($calendarId,$calendarids);
					} else {
						$contains_gc_id = $calendarId == $calendarids;
					}
				}
				if ($contains_gc_id) {
					return $item->id;
				}
			}
		}
		if ($strict = true) {
			return null;
		}
		return $default;
	}

	public static function renderEvents(array $events = null, $output, $params = null, $eventParams = array()) 
    {
        $app = JFactory::getApplication();
        
		if ($events === null) {
			$events = array();
		}
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' events<br><pre>'.print_r($events,true).'</pre>'),'Notice');
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' output<br><pre>'.print_r($output,true).'</pre>'),'Notice');
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' params<br><pre>'.print_r($params,true).'</pre>'),'Notice');
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' eventParams<br><pre>'.print_r($eventParams,true).'</pre>'),'Notice');

		JFactory::getLanguage()->load('com_sportsmanagement', JPATH_ADMINISTRATOR.DS.'components'.DS.'com_sportsmanagement');

		$lastHeading = '';

		$configuration = $eventParams;
		$configuration['events'] = array();
		foreach ($events as $event) {
			if (!is_object($event)) {
				continue;
			}
			$variables = array();

			$itemID = jsmGCalendarUtil::getItemId($event->getParam('gcid', null));
			if (!empty($itemID) && JFactory::getApplication()->input->getVar('tmpl', null) != 'component' && $event != null) {
				$component = JComponentHelper::getComponent('com_sportsmanagement');
				$menu = JFactory::getApplication()->getMenu();
				$item = $menu->getItem($itemID);
				if ($item !=null) 
                {
					$backLinkView = $item->query['view'];
					$dateHash = '';
					if ($backLinkView == 'gcalendar') 
                    {
						$day = $event->getStartDate()->format('d', true);
						$month = $event->getStartDate()->format('m', true);
						$year = $event->getStartDate()->format('Y', true);
						$dateHash = '#year='.$year.'&month='.$month.'&day='.$day;
					}
				}
				$variables['calendarLink'] = JRoute::_('index.php?option=com_sportsmanagement&Itemid='.$itemID.$dateHash);
			}

			$itemID = jsmGCalendarUtil::getItemId($event->getParam('gcid'));
            
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' itemID<br><pre>'.print_r($itemID,true).'</pre>'),'Notice');
            
			if (!empty($itemID)) {
				$itemID = '&Itemid='.$itemID;
			}else{
				$menu = JFactory::getApplication()->getMenu();
				$activemenu = $menu->getActive();
				if ($activemenu != null) {
					$itemID = '&Itemid='.$activemenu->id;
				}
			}

			$variables['backlink'] = JRoute::_('index.php?option=com_sportsmanagement&view=event&eventID='.$event->getGCalId().'&gcid='.$event->getParam('gcid').$itemID);

			$variables['link'] = $event->getLink('alternate')->getHref();
			$variables['calendarcolor'] = $event->getParam('gccolor');

			// the date formats from http://php.net/date
			$dateformat = $params->get('event_date_format', 'm.d.Y');
			$timeformat = $params->get('event_time_format', 'g:i a');

			// These are the dates we'll display
			$startDate = $event->getStartDate()->format($dateformat, true);
			$startTime = $event->getStartDate()->format($timeformat, true);
			$endDate = $event->getEndDate()->format($dateformat, true);
			$endTime = $event->getEndDate()->format($timeformat, true);
			$dateSeparator = '-';

			$timeString = $startTime.' '.$startDate.' '.$dateSeparator.' '.$endTime.' '.$endDate;
			$copyDateTimeFormat = 'Ymd\THis';
			$shortStart = $event->getStartDate()->format('Ymd', true);
			$shortEnd = $event->getEndDate()->format('Ymd', true);
			if ($event->isAllDay()) {
				$copyDateTimeFormat = 'Ymd';

				$startTime = '';
				$endTime = '';
				if ($shortStart == $shortEnd) {
					$timeString = $startDate;
					$dateSeparator = '';
				} else {
					$timeString = $startDate.' '.$dateSeparator.' '.$endDate;
				}
			} else if ($shortStart == $shortEnd) {
				$timeString = $startDate.' '.$startTime.' '.$dateSeparator.' '.$endTime;
			}

			$variables['calendarName'] = $event->getParam('gcname');
			$variables['title'] = (string)$event->getTitle();
			if ($params->get('show_event_date', 1) == 1) {
				$variables['date'] = $timeString;
				$variables['startDate'] = $startDate;
				$variables['startTime'] = $startTime;
				$variables['endDate'] = $endDate;
				$variables['endTime'] = $endTime;
				$variables['dateseparator'] = $dateSeparator;

				$variables['month'] = strtoupper($event->getStartDate()->format('M', true));
				$variables['day'] = $event->getStartDate()->format('j', true);
				$variables['year'] = strtoupper($event->getStartDate()->format('Y', true));
				$variables['dayNameShort'] = $event->getStartDate()->format('D', true);
				$variables['dayNameLong'] = $event->getStartDate()->format('l', true);
			}
			$variables['modifieddate'] = $event->getModifiedDate()->format($timeformat, true).' '.$event->getModifiedDate()->format($dateformat, true);

			if (count($event->getWho()) > 0) {
				$variables['hasAttendees'] = true;
				$variables['attendees'] = array();
				foreach ($event->getWho() as $a) {
					$variables['attendees'][] = array('name' => (string)$a->getValueString(), 'email' =>  base64_encode(str_replace('@','#',$a->getEmail())));
				}
			}
			$location = $event->getLocation();
			$variables['location'] = $location;
			if (!empty($location)) {
				$variables['maplink'] = (JBrowser::getInstance()->isSSLConnection() ? 'https' : 'http')."://maps.google.com/?q=".urlencode($location).'&hl='.substr(jsmGCalendarUtil::getFrLanguage(),0,2).'&output=embed';
			}

			$variables['description'] = (string)$event->getContent();
			if ($params->get('event_description_format', 1) == 1) {
				$variables['description'] = preg_replace("@(src|href)=\"https?://@i",'\\1="', $event->getContent());
				$variables['description'] = nl2br(preg_replace("@(((f|ht)tp:\/\/)[^\"\'\>\s]+)@",'<a href="\\1" target="_blank">\\1</a>', $variables['description']));
			}
			if ($params->get('description_length', 0) > 0) {
				$variables['description'] = self::truncateHtml($variables['description'], $params->get('description_length', 0));
			}

			$variables['hasAuthor'] = count($event->getAuthor()) > 0;
			$variables['author'] = array();
			foreach ($event->getAuthor() as $author) {
				$variables['author'][] = array('name' => (string)$author->getName(), 'email' =>  base64_encode(str_replace('@','#',$author->getEmail())));
			}

			$variables['copyGoogleUrl'] = 'http://www.google.com/calendar/render?action=TEMPLATE&text='.urlencode($event->getTitle());
			$variables['copyGoogleUrl'] .= '&dates='.$event->getStartDate()->format($copyDateTimeFormat).'%2F'.$event->getEndDate()->format($copyDateTimeFormat);
			$variables['copyGoogleUrl'] .= '&location='.urlencode($event->getLocation());
			$variables['copyGoogleUrl'] .= '&details='.urlencode($event->getContent());
			$variables['copyGoogleUrl'] .= '&hl='.jsmGCalendarUtil::getFrLanguage().'&ctz=Etc/GMT';
			$variables['copyGoogleUrl'] .= '&sf=true&output=xml';

			$ical_timeString_start =  $startTime.' '.$startDate;
			$ical_timeString_start = strtotime($ical_timeString_start);
			$ical_timeString_end =  $endTime.' '.$endDate;
			$ical_timeString_end = strtotime($ical_timeString_end);
			$loc = $event->getLocation();
			$variables['copyOutlookUrl'] = JRoute::_("index.php?option=com_sportsmanagement&view=ical&format=raw&eventID=".$event->getGCalId().'&gcid='.$event->getParam('gcid'));

			$groupHeading = $event->getStartDate()->format($params->get('grouping', ''), true);
			if ($groupHeading != $lastHeading) {
				$lastHeading = $groupHeading;
				$variables['header'] =  $groupHeading;
			}


			$configuration['events'][] = $variables;
		}

		$configuration['eventLabel'] = JText::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_FIELD_CONFIG_EVENT_LABEL');
		$configuration['calendarLinkLabel'] = JText::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_FIELD_CONFIG_EVENT_LABEL_CALENDAR_BACK_LINK');
		$configuration['calendarNameLabel'] = JText::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_FIELD_CONFIG_EVENT_LABEL_CALENDAR_NAME');
		$configuration['titleLabel'] = JText::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_FIELD_CONFIG_EVENT_LABEL_EVENT_TITLE');
		$configuration['dateLabel'] = JText::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_FIELD_CONFIG_EVENT_LABEL_WHEN');
		$configuration['attendeesLabel'] = JText::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_FIELD_CONFIG_EVENT_LABEL_ATTENDEES');
		$configuration['locationLabel'] = JText::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_FIELD_CONFIG_EVENT_LABEL_LOCATION');
		$configuration['descriptionLabel'] = JText::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_FIELD_CONFIG_EVENT_LABEL_DESCRIPTION');
		$configuration['authorLabel'] = JText::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_FIELD_CONFIG_EVENT_LABEL_AUTHOR');
		$configuration['copyLabel'] = JText::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_FIELD_CONFIG_EVENT_LABEL_COPY');
		$configuration['copyGoogleLabel'] = JText::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_FIELD_CONFIG_EVENT_LABEL_COPY_TO_MY_CALENDAR');
		$configuration['copyOutlookLabel'] = JText::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_FIELD_CONFIG_EVENT_LABEL_COPY_TO_MY_CALENDAR_ICS');
		$configuration['language'] = substr(jsmGCalendarUtil::getFrLanguage(),0,2);

		$configuration['emptyText'] = JText::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_FIELD_CONFIG_EVENT_LABEL_NO_EVENT_TEXT');
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' configuration<br><pre>'.print_r($configuration,true).'</pre>'),'Notice');

		try{
			$m = new Mustache();
			return $m->render($output, $configuration);
		}catch(Exception $e) {
			echo $e->getMessage();
		}
	}

	public static function getFadedColor($color, $percentage = 85) {
		$percentage = 100 - $percentage;
		$rgbValues = array_map( 'hexDec', str_split( ltrim($color, '#'), 2 ) );

		for ($i = 0, $len = count($rgbValues); $i < $len; $i++) {
			$rgbValues[$i] = decHex( floor($rgbValues[$i] + (255 - $rgbValues[$i]) * ($percentage / 100) ) );
		}

		return '#'.implode('', $rgbValues);
	}

	public static function dayToString($day, $abbr = false)
	{
		$name = '';
		switch ($day) {
			case 0:
				$name = $abbr ? JText::_('SUN') : JText::_('SUNDAY');
				break;
			case 1:
				$name = $abbr ? JText::_('MON') : JText::_('MONDAY');
				break;
			case 2:
				$name = $abbr ? JText::_('TUE') : JText::_('TUESDAY');
				break;
			case 3:
				$name = $abbr ? JText::_('WED') : JText::_('WEDNESDAY');
				break;
			case 4:
				$name = $abbr ? JText::_('THU') : JText::_('THURSDAY');
				break;
			case 5:
				$name = $abbr ? JText::_('FRI') : JText::_('FRIDAY');
				break;
			case 6:
				$name = $abbr ? JText::_('SAT') : JText::_('SATURDAY');
				break;
		}
		return addslashes($name);
	}

	public static function monthToString($month, $abbr = false)
	{
		$name = '';
		switch ($month) {
			case 1:
				$name = $abbr ? JText::_('JANUARY_SHORT')	: JText::_('JANUARY');
				break;
			case 2:
				$name = $abbr ? JText::_('FEBRUARY_SHORT')	: JText::_('FEBRUARY');
				break;
			case 3:
				$name = $abbr ? JText::_('MARCH_SHORT')		: JText::_('MARCH');
				break;
			case 4:
				$name = $abbr ? JText::_('APRIL_SHORT')		: JText::_('APRIL');
				break;
			case 5:
				$name = $abbr ? JText::_('MAY_SHORT')		: JText::_('MAY');
				break;
			case 6:
				$name = $abbr ? JText::_('JUNE_SHORT')		: JText::_('JUNE');
				break;
			case 7:
				$name = $abbr ? JText::_('JULY_SHORT')		: JText::_('JULY');
				break;
			case 8:
				$name = $abbr ? JText::_('AUGUST_SHORT')	: JText::_('AUGUST');
				break;
			case 9:
				$name = $abbr ? JText::_('SEPTEMBER_SHORT')	: JText::_('SEPTEMBER');
				break;
			case 10:
				$name = $abbr ? JText::_('OCTOBER_SHORT')	: JText::_('OCTOBER');
				break;
			case 11:
				$name = $abbr ? JText::_('NOVEMBER_SHORT')	: JText::_('NOVEMBER');
				break;
			case 12:
				$name = $abbr ? JText::_('DECEMBER_SHORT')	: JText::_('DECEMBER');
				break;
		}
		return addslashes($name);
	}

	public static function getActions($calendarId = 0) {
		$user  = JFactory::getUser();
		$result  = new JObject;

		if (empty($calendarId)) {
			$assetName = 'com_sportsmanagement';
		}
		else {
			$assetName = 'com_sportsmanagement.jsmcalendar.'.(int) $calendarId;
		}

		$actions = array('core.admin', 'core.manage', 'core.create', 'core.edit', 'core.delete');

		foreach ($actions as $action) {
			$result->set($action, $user->authorise($action, $assetName));
		}

		return $result;
	}

	public static function isJoomlaVersion($version) {
		$j = new JVersion();
		return substr($j->RELEASE, 0, strlen($version)) == $version;
	}

	public static function loadLibrary($libraries = array('jquery' => true)) {
		if (JFactory::getDocument()->getType() != 'html') {
			return ;
		}

		$document = JFactory::getDocument();
		if (self::isJoomlaVersion('2.5')) {
			if (isset($libraries['jquery'])) {
				if (!JFactory::getApplication()->get('jquery', false)) {
					JFactory::getApplication()->set('jquery', true);
					$document->addScript(JURI::root().'components/com_sportsmanagement/libraries/jquery/jquery.min.js');
				}
				$document->addScript(JURI::root().'components/com_sportsmanagement/libraries/jquery/gcalendar/gcNoConflict.js');
			}

			if (isset($libraries['jqueryui'])) {
				$theme = 'bootstrap';
				if (is_string($libraries['jqueryui']) && !empty($libraries['jqueryui']) && $libraries['jqueryui'] != -1) {
					$theme = $libraries['jqueryui'];
				}
				if ($theme == 'bootstrap') {
					$libraries['bootstrap'] = true;
				}
				$document->addStyleSheet(JURI::root().'components/com_sportsmanagement/libraries/jquery/themes/'.$theme.'/jquery-ui.custom.css');
				$document->addScript(JURI::root().'components/com_sportsmanagement/libraries/jquery/ui/jquery-ui.custom.min.js');
			}

			if (isset($libraries['bootstrap'])) {
				$document->addStyleSheet(JURI::root().'components/com_sportsmanagement/libraries/bootstrap/css/bootstrap.min.css');
				if ($libraries['bootstrap'] == 'javscript') {
					$document->addScript(JURI::root().'components/com_sportsmanagement/libraries/bootstrap/js/bootstrap.min.js');
				}
			}

			if (isset($libraries['chosen'])) {
				$document->addScript(JURI::root().'components/com_sportsmanagement/libraries/jquery/ext/jquery.chosen.min.js');
				$document->addStyleSheet(JURI::root().'components/com_sportsmanagement/libraries/jquery/ext/jquery.chosen.css');
			}
		} else {
			if (isset($libraries['jquery'])) {
				JHtml::_('jquery.framework');
				$document->addScript(JURI::root().'components/com_sportsmanagement/libraries/jquery/gcalendar/gcNoConflict.js');
			}

			if (isset($libraries['jqueryui'])) {
				$theme = 'bootstrap';
				if (is_string($libraries['jqueryui']) && !empty($theme) && $theme == -1) {
					$theme = $libraries['jqueryui'];
				} else {
					$libraries['bootstrap'] = true;
				}
				$document->addStyleSheet(JURI::root().'components/com_sportsmanagement/libraries/jquery/themes/'.$theme.'/jquery-ui.custom.css');
				$document->addScript(JURI::root().'components/com_sportsmanagement/libraries/jquery/ui/jquery-ui.custom.min.js');
			}

			if (isset($libraries['bootstrap'])) {
				JHtml::_('bootstrap.framework');
			}

			if (isset($libraries['chosen'])) {
				JHtml::_('formbehavior.chosen', 'select');
			}
		}

		if (isset($libraries['gcalendar'])) {
			$document->addScript(JURI::root().'components/com_sportsmanagement/libraries/gcalendar/gcalendar.js');
			$document->addStyleSheet(JURI::root().'components/com_sportsmanagement/libraries/gcalendar/gcalendar.css');
		}

		if (isset($libraries['maps'])) {
			$document->addScript((JBrowser::getInstance()->isSSLConnection() ? "https" : "http").'://maps.googleapis.com/maps/api/js?sensor=true&language='.self::getGoogleLanguage());
		}

		if (isset($libraries['fullcalendar'])) {
			$document->addScript(JURI::root().'components/com_sportsmanagement/libraries/fullcalendar/fullcalendar.min.js');
			$document->addStyleSheet(JURI::root().'components/com_sportsmanagement/libraries/fullcalendar/fullcalendar.css');
			$document->addScript(JURI::root().'components/com_sportsmanagement/libraries/jquery/gcalendar/jquery.gcalendar-all.min.js');
			$document->addStyleSheet(JURI::root().'components/com_sportsmanagement/libraries/jquery/fancybox/jquery.fancybox-1.3.4.css');
			$document->addStyleSheet(JURI::root().'components/com_sportsmanagement/libraries/jquery/ext/tipTip.css');
			$document->addScript(JURI::root().'components/com_sportsmanagement/libraries/jquery/ext/jquery.tipTip.minified.js');
		}
	}

	public static function getGoogleLanguage() {
		$languages = array('ar', 'bg', 'bn', 'ca', 'cs', 'da', 'de', 'el', 'en', 'en-AU', 'en-GB', 'es', 'eu', 'fa', 'fi', 'fil', 'fr', 'gl', 'gu', 'hi', 'hr', 'hu', 'id', 'it', 'iw', 'ja', 'kn', 'ko', 'lt', 'lv', 'ml', 'mr', 'nl', 'nn', 'no', 'or', 'pl', 'pt', 'pt-BR', 'pt-PT', 'rm', 'ro', 'ru', 'sk', 'sl', 'sr', 'sv', 'tl', 'ta', 'te', 'th', 'tr', 'uk', 'vi','zh-CN', 'zh-TW');
		$lang  = self::getFrLanguage();
		if (!in_array($lang, $languages)) {
			$lang = substr($lang, 0, strpos($lang, '-'));
		}
		if (!in_array($lang, $languages)) {
			$lang = 'en';
		}
		return $lang;
	}

	public static function getDate($date = null, $allDay = null, $tz = null) {
		if (empty($tz)) {
			$tz =  jsmGCalendarUtil::getComponentParameter('timezone');
		}
		$dateObj = JFactory::getDate($date, $tz);

		if (!$allDay) {
			$dateObj->setTimezone(new DateTimeZone(self::getComponentParameter('timezone', 'UTC')));
		}
		return $dateObj;
	}

	public static function getDateFromString($date, $time, $allDay, $timezone, $dateFormat = null, $timeFormat = null) 
    {
        $option = JFactory::getApplication()->input->getCmd('option');
		$app = JFactory::getApplication();
        
		$string = $date;
		if (!empty($time)) {
			$string = $date.($allDay ? '' : ' '.$time);
		}

		$months = array('JANUARY', 'FEBRUARY', 'MARCH', 'APRIL', 'MAY', 'JUNE', 'JULY', 'AUGUST', 'SEPTEMBER', 'OCTOBER', 'NOVEMBER', 'DECEMBER');
		$monthsShort = array('JANUARY_SHORT', 'FEBRUARY_SHORT', 'MARCH_SHORT', 'APRIL_SHORT', 'MAY_SHORT', 'JUNE_SHORT', 'JULY_SHORT', 'AUGUST_SHORT', 'SEPTEMBER_SHORT', 'OCTOBER_SHORT', 'NOVEMBER_SHORT', 'DECEMBER_SHORT');
		$lang = JLanguage::getInstance('en-GB');
		foreach (array_merge($months, $monthsShort) as $month) {
			$string = str_replace(JText::_($month), $lang->_($month), $string);
		}

		if (empty($dateFormat)) {
			$dateFormat = self::getComponentParameter('event_date_format', 'Y-m-d');
		}
		if (empty($timeFormat)) {
			$timeFormat = self::getComponentParameter('event_time_format', 'g:i a');
		}

		$date = DateTime::createFromFormat($dateFormat.($allDay ? '' : ' '.$timeFormat), $string, new DateTimeZone($timezone));
        
//        $app->enqueueMessage(__METHOD__.' '.__FUNCTION__.' dateFormat<br><pre>'.print_r($dateFormat, true).'</pre><br>','Notice');
//        $app->enqueueMessage(__METHOD__.' '.__FUNCTION__.' allDay<br><pre>'.print_r($allDay, true).'</pre><br>','Notice');
//        $app->enqueueMessage(__METHOD__.' '.__FUNCTION__.' timeFormat<br><pre>'.print_r($timeFormat, true).'</pre><br>','Notice');
//        $app->enqueueMessage(__METHOD__.' '.__FUNCTION__.' string<br><pre>'.print_r($string, true).'</pre><br>','Notice');
//        $app->enqueueMessage(__METHOD__.' '.__FUNCTION__.' timezone<br><pre>'.print_r($timezone, true).'</pre><br>','Notice');
//        $app->enqueueMessage(__METHOD__.' '.__FUNCTION__.' date<br><pre>'.print_r($date, true).'</pre><br>','Notice');
        
		$date = jsmGCalendarUtil::getDate($date->format('U'), $allDay);

		return $date;
	}

	public static function sendMessage($message, $error = false, array $data = array()) {
		ob_clean();

		JLoader::import('components.com_languages.helpers.jsonresponse', JPATH_ADMINISTRATOR);
		if (!$error) {
			JFactory::getApplication()->enqueueMessage($message);
			echo new JJsonResponse($data);
		} else {
			JFactory::getApplication()->enqueueMessage($message, 'error');
			echo new JJsonResponse($data);
		}

		JFactory::getApplication()->close();
	}

	/**
	 * truncateHtml can truncate a string up to a number of characters while preserving whole words and HTML tags
	 *
	 * @param string $text String to truncate.
	 * @param integer $length Length of returned string, including ellipsis.
	 * @param string $ending Ending to be appended to the trimmed string.
	 * @param boolean $exact If false, $text will not be cut mid-word
	 * @param boolean $considerHtml If true, HTML tags would be handled correctly
	 *
	 * @return string Trimmed string.
	 */
	public static function truncateHtml($text, $length = 100, $ending = '...', $exact = false, $considerHtml = true) {
		if ($considerHtml) {
			// if the plain text is shorter than the maximum length, return the whole text
			if (strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
				return $text;
			}
			// splits all html-tags to scanable lines
			preg_match_all('/(<.+?>)?([^<>]*)/s', $text, $lines, PREG_SET_ORDER);
			$total_length = strlen($ending);
			$open_tags = array();
			$truncate = '';
			foreach ($lines as $line_matchings) {
				// if there is any html-tag in this line, handle it and add it (uncounted) to the output
				if (!empty($line_matchings[1])) {
					// if it's an "empty element" with or without xhtml-conform closing slash
					if (preg_match('/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is', $line_matchings[1])) {
						// do nothing
						// if tag is a closing tag
					} else if (preg_match('/^<\s*\/([^\s]+?)\s*>$/s', $line_matchings[1], $tag_matchings)) {
						// delete tag from $open_tags list
						$pos = array_search($tag_matchings[1], $open_tags);
						if ($pos !== false) {
							unset($open_tags[$pos]);
						}
						// if tag is an opening tag
					} else if (preg_match('/^<\s*([^\s>!]+).*?>$/s', $line_matchings[1], $tag_matchings)) {
						// add tag to the beginning of $open_tags list
						array_unshift($open_tags, strtolower($tag_matchings[1]));
					}
					// add html-tag to $truncate'd text
					$truncate .= $line_matchings[1];
				}
				// calculate the length of the plain text part of the line; handle entities as one character
				$content_length = strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', ' ', $line_matchings[2]));
				if ($total_length+$content_length> $length) {
					// the number of characters which are left
					$left = $length - $total_length;
					$entities_length = 0;
					// search for html entities
					if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', $line_matchings[2], $entities, PREG_OFFSET_CAPTURE)) {
						// calculate the real length of all entities in the legal range
						foreach ($entities[0] as $entity) {
							if ($entity[1]+1-$entities_length <= $left) {
								$left--;
								$entities_length += strlen($entity[0]);
							} else {
								// no more characters left
								break;
							}
						}
					}
					$truncate .= substr($line_matchings[2], 0, $left+$entities_length);
					// maximum lenght is reached, so get off the loop
					break;
				} else {
					$truncate .= $line_matchings[2];
					$total_length += $content_length;
				}
				// if the maximum length is reached, get off the loop
				if($total_length>= $length) {
					break;
				}
			}
		} else {
			if (strlen($text) <= $length) {
				return $text;
			} else {
				$truncate = substr($text, 0, $length - strlen($ending));
			}
		}
		// if the words shouldn't be cut in the middle...
		if (!$exact) {
			// ...search the last occurance of a space...
			$spacepos = strrpos($truncate, ' ');
			if (isset($spacepos)) {
				// ...and cut the text in this position
				$truncate = substr($truncate, 0, $spacepos);
			}
		}
		// add the defined ending to the text
		$truncate .= $ending;
		if($considerHtml) {
			// close all unclosed html-tags
			foreach ($open_tags as $tag) {
				$truncate .= '</' . $tag . '>';
			}
		}
		return $truncate;
	}
}