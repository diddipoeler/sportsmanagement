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

final class JsonfeedModel extends SportsManagementModel
{
    private const MAX_CALENDAR_IDS = 50;
    private const MAX_ID_INPUTS = 200;
    private const MAX_ID_STRING_LENGTH = 4096;
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

        if (is_int($value) || is_string($value)) {
            $value = [$value];
        }

        if (!is_array($value)) {
            return [];
        }

        $ids = [];

        // Bound both the number and size of untrusted selection values
        // before splitting a comma-separated request into calendar IDs.
        foreach (array_slice($value, 0, self::MAX_ID_INPUTS) as $entry) {
            if ((!is_int($entry) && !is_string($entry))
                || (is_string($entry) && strlen($entry) > self::MAX_ID_STRING_LENGTH)) {
                continue;
            }

            $parts = is_int($entry) ? [$entry] : (preg_split('/[\s,;]+/', $entry, -1, PREG_SPLIT_NO_EMPTY) ?: []);

            foreach ($parts as $part) {
                if (is_int($part)) {
                    if ($part > 0) {
                        $ids[$part] = $part;
                        // Integer arrays must obey the same limit as CSV IDs.
                        if (count($ids) >= self::MAX_CALENDAR_IDS) {
                            return array_values($ids);
                        }
                    }
                    continue;
                }

                // Do not turn malformed values like "12abc" into calendar 12.
                if (!preg_match('/^[1-9][0-9]*$/D', $part)) {
                    continue;
                }

                $id = filter_var($part, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
                if ($id !== false) {
                    $ids[$id] = $id;
                }

                if (count($ids) >= self::MAX_CALENDAR_IDS) {
                    return array_values($ids);
                }
            }
        }

        return array_values($ids);
    }
}
