<?php
/**
 * Joomla 5/6 date conversion helper for the SportsManagement calendar runtime.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementCalendar\Site\Runtime;

\defined('_JEXEC') or die;

use Joomla\CMS\Date\Date;

final class CalendarDate
{
    /**
     * Convert a timestamp or date input into Joomla's calendar date object.
     * Interprets integer timestamps in the requested timezone and delegates
     * date strings to Joomla Date. Rejects nested values, booleans and
     * oversized date strings instead of casting them to unexpected text.
     * Returns null when conversion fails.
     *
     * @param mixed $value Unix timestamp, numeric timestamp string or date value.
     * @param string $offset Joomla site timezone, defaulting to UTC.
     * @return Date|null Timezone-aware Joomla Date, or null if conversion failed.
     */
    public static function fromValue(mixed $value, string $offset): ?Date
    {
        if ((!is_int($value) && !is_string($value))
            || (is_string($value) && (trim($value) === '' || strlen($value) > 128))) {
            return null;
        }

        try {
            if (is_int($value) || (is_string($value) && ctype_digit($value))) {
                $date = new Date('@' . (int) $value);
                $date->setTimezone(new \DateTimeZone($offset ?: 'UTC'));

                return $date;
            }

            return new Date((string) $value, $offset ?: 'UTC');
        } catch (\Throwable) {
            return null;
        }
    }
}
