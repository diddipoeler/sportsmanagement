<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\GoogleCalendarReadService;

final class JsonfeedModel extends SportsManagementModel
{
    /**
     * Return normalized Google Calendar event arrays for the JSON feed.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getGoogleCalendarFeeds(): array
    {
        $app = $this->siteApplication();
        $input = $app->getInput();
        $calendarIds = $this->normalizeCalendarIds($input->get('gcids', null, 'raw'));

        if (!$calendarIds) {
            $calendarIds = $this->normalizeCalendarIds($input->get('gcid', null, 'raw'));
        }

        // An empty request must never expose every configured calendar.
        if (!$calendarIds) {
            return [];
        }

        $start = $input->getInt('start', 0);
        $end = $input->getInt('end', 0);
        $service = new GoogleCalendarReadService($this->getDatabase(), $app);

        return $service->getEvents(
            $calendarIds,
            $start > 0 ? $start : null,
            $end > 0 ? $end : null
        );
    }

    /**
     * @return array<int>
     */
    private function normalizeCalendarIds(mixed $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        if (!is_array($value)) {
            $value = preg_split('/\s*,\s*/', (string) $value, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        }

        return array_values(array_unique(array_filter(array_map('intval', $value))));
    }
}
