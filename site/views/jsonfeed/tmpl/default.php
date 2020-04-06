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
 * @package   GCalendar
 * @author    Digital Peak http://www.digital-peak.com
 * @copyright Copyright (C) 2007 - 2013 Digital Peak. All rights reserved.
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();
use Joomla\CMS\Router\Route;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Component\ComponentHelper;

$document = Factory::getDocument();
$document->setMimeEncoding('application/json');

$dispatcher = JDispatcher::getInstance();
PluginHelper::importPlugin('gcalendar');

$data = array();
$SECSINDAY=86400;
if (!empty($this->calendars)) {
    $itemID = Factory::getApplication()->input->getVar('Itemid', null);
    foreach ($this->calendars as $calendar) {
        if($itemID == null) {
            $itemID = jsmGCalendarUtil::getItemId($calendar->id);
        }
        $params = Factory::getApplication()->getMenu()->getParams($itemID);
        $tmp = clone ComponentHelper::getParams('com_sportsmanagement');
        if (empty($params)) {
            $params = $tmp;
        } else {
            $tmp->merge($params);
            $params = $tmp;
        }
        foreach ($calendar as $event) {
            $dateformat = $params->get('description_date_format', 'm.d.Y');
            $timeformat = $params->get('description_time_format', 'g:i a');

            $params->set('event_date_format', $dateformat);
            $params->set('event_time_format', $timeformat);

            if (!empty($itemID)) {
                $itemID = '&Itemid='.$itemID;
            } else {
                $menu = Factory::getApplication()->getMenu();
                $activemenu = $menu->getActive();
                if($activemenu != null) {
                    $itemID = '&Itemid='.$activemenu->id;
                }
            }

            $params->set('description_length', 100);
            $description = jsmGCalendarUtil::renderEvents(array($event), $params->get('description_format', '{{#events}}<p>{{date}}<br/>{{{description}}}</p>{{/events}}'), $params);

            $eventData = array(
              'id' => $event->getGCalId(),
              'gcid' => $event->getParam('gcid'),
              'title' => $this->compactMode == 0 ? htmlspecialchars_decode($event->getTitle()) : utf8_encode(chr(160)),
              'start' => $event->getStartDate()->format('c', true),
              'end' => $event->getEndDate()->format('c', true),
              'url' => Route::_('index.php?option=com_sportsmanagement&view=event&eventID='.$event->getGCalId().'&gcid='.$event->getParam('gcid').(empty($itemID)?'':$itemID)),
              'color' => jsmGCalendarUtil::getFadedColor($event->getParam('gccolor')),
              'allDay' => $this->compactMode == 0 ? $event->isAllDay() : true,
              'description' => $description
            );

            $dispatcher->trigger('onGCEventBeforeLoad', array($event, &$eventData));
            $data[] = $eventData;
        }
    }
}
ob_clean();
echo json_encode($data);
