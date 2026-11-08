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
     * rejects non-string, oversized or control-character-containing IDs,
     * loads the calendar read service when needed and returns the event
     * only if the authenticated Joomla frontend is allowed to read it.
     *
     * @return array<string, mixed>|null Event data or null when missing.
     */
    public function getGCalendar(): ?array
    {
        $app = $this->siteApplication();
        $input = $app->getInput();
        $calendarId = $input->getInt('gcid', 0);
        $rawEventId = $input->get('eventID', '', 'raw');

        // Joomla Input can return an array for a repeated or bracketed
        // eventID parameter. Do not cast arrays or objects into event IDs.
        if ($calendarId <= 0 || !is_string($rawEventId) || strlen($rawEventId) > 512
            || preg_match('/[\x00-\x1F\x7F]/', $rawEventId)) {
            return null;
        }

        $eventId = trim($rawEventId);

        if ($eventId === '') {
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
