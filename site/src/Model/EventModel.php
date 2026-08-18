<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;

final class EventModel extends SportsManagementModel
{
    public function getGCalendar()
    {
        $input = Factory::getApplication()->getInput();
        $calendarId = $input->get('gcid', null, 'raw');
        $eventId = $input->get('eventID', null, 'raw');
        $results = \jsmGCalendarDBUtil::getCalendars($calendarId);

        if (empty($results) || $eventId === null || $eventId === '') {
            return null;
        }

        return \jsmGCalendarZendHelper::getEvent($results[0], $eventId);
    }

    protected function populateState()
    {
        $this->setState('params', Factory::getApplication()->getParams());
    }
}
