<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;

final class JsonfeedModel extends SportsManagementModel
{
    public function getGoogleCalendarFeeds()
    {
        $input = Factory::getApplication()->getInput();
        $startDate = $input->get('start', null, 'raw');
        $endDate = $input->get('end', null, 'raw');
        $calendarIds = $input->get('gcids', null, 'raw');

        if ($calendarIds === null || $calendarIds === '') {
            $calendarIds = $input->get('gcid', null, 'raw');
        } elseif (!is_array($calendarIds)) {
            $calendarIds = array_values(array_filter(array_map('trim', explode(',', (string) $calendarIds)), 'strlen'));
        }

        $results = \jsmGCalendarDBUtil::getCalendars($calendarIds);

        if (empty($results)) {
            return null;
        }

        $calendars = [];

        foreach ($results as $result) {
            if (empty($result->calendar_id)) {
                continue;
            }

            $events = \jsmGCalendarZendHelper::getEvents($result, $startDate, $endDate, 1000);

            if ($events !== null) {
                $calendars[] = $events;
            }
        }

        return $calendars;
    }
}
