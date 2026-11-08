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
     * Interprets integer timestamps in the requested timezone and delegates date strings to Joomla Date, returning null for invalid or unsupported conversions.
     *
     * @param mixed $value Unix timestamp, numeric timestamp string or date value.
     * @param string $offset Joomla site timezone, defaulting to UTC.
     * @return Date|null Timezone-aware Joomla Date, or null if conversion failed.
     */
    public static function fromValue(mixed $value, string $offset): ?Date
    {
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
