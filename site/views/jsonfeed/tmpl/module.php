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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;

$document = Factory::getDocument();
$document->setMimeEncoding('application/json');

$tmp = array();
if(!empty($this->calendars)){
	foreach ($this->calendars as $calendar){
		if(empty($calendar)){
			continue;
		}
		foreach ($calendar as $item) {
			$start = clone $item->getStartDate();
			$end = clone $item->getEndDate();

			do {
				$date = $start->format('Y-m-d', true);
				if(!key_exists($date, $tmp)){
					$tmp[$date] = array();
				}
				$tmp[$date][] = $item;
				$start->modify("+1 day");
			} while ($start < $end);
		}
	}
}

$params = clone ComponentHelper::getParams('com_sportsmanagement');
$params->set('show_event_title', 1);
$data = array();
foreach ($tmp as $date => $events){
	$linkIDs = array();
	$itemId = '';
	foreach ($events as $event) {
		$linkIDs[$event->getParam('gcid')] = $event->getParam('gcid');

		$id = jsmGCalendarUtil::getItemId($event->getParam('gcid'), true);
		if(!empty($id))
			$itemId = '&Itemid='.$id;
	}

	$parts = explode('-', $date);
	$day = $parts[2];
	$month = $parts[1];
	$year = $parts[0];
	$url = Route::_('index.php?option=com_sportsmanagement&view=gcalendar&gcids='.implode(',', $linkIDs).$itemId.'#year='.$year.'&month='.$month.'&day='.$day.'&view=agendaDay');

	$data[] = array(
			'id' => $date,
			'title' => utf8_encode(chr(160)), //space only works in IE, empty only in Chrome... sighh
			'start' => $date,
			'url' => $url,
			'allDay' => true,
			'description' => jsmGCalendarUtil::renderEvents($events, sprintf(Text::_('COM_GCALENDAR_JSON_VIEW_EVENT_TITLE'), count($events)).'<ul>{{#events}}<li>{{title}}</li>{{/events}}</ul>', $params)
	);
}
ob_clean();
echo json_encode($data);