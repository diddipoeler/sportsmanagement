<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\GoogleCalendarReadService;

final class EventModel extends SportsManagementModel
{
    /**
     * Return one normalized Google Calendar event.
     *
     * @return array<string, mixed>|null
     */
    public function getGCalendar(): ?array
    {
        $app = $this->siteApplication();
        $input = $app->getInput();
        $calendarId = $input->getInt('gcid', 0);
        $eventId = trim((string) $input->get('eventID', '', 'raw'));

        if ($calendarId <= 0 || $eventId === '') {
            return null;
        }

        if (!class_exists(GoogleCalendarReadService::class)) {
            require_once JPATH_SITE . '/components/com_sportsmanagement/src/Service/GoogleCalendarReadService.php';
        }

        return (new GoogleCalendarReadService($this->getDatabase(), $app))->getEvent($calendarId, $eventId);
    }

    protected function populateState()
    {
        $this->setState('params', $this->siteApplication()->getParams());
    }
}
