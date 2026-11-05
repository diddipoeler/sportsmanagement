<?php
/**
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\GoogleCalendarReadService;

final class EventModel extends SportsManagementModel
{
    /**
     * Retrieve one Google Calendar event selected in the frontend request.
     *
     * Reads an integer calendar identifier and the requested event ID,
     * skips requests without either value, loads the calendar read service
     * when necessary and returns its normalised event representation.
     *
     * @return array<string, mixed>|null Event data or null when missing.
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

    /**
     * Initialise the event view's component parameters in Joomla model state.
     *
     * Makes the active site's component configuration available to the
     * event view without requiring the legacy model state bootstrap.
     *
     * @return void
     */
    protected function populateState()
    {
        $this->setState('params', $this->siteApplication()->getParams());
    }
}
