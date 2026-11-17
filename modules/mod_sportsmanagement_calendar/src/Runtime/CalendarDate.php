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
     * Interprets signed integer timestamps and numeric strings with optional
     * plus/minus signs in the requested timezone, including dates before 1970.
     * Delegates ordinary
     * date strings to Joomla Date. Rejects nested values, booleans and
     * oversized date strings or integer-overflowing timestamps instead of
     * casting them to unexpected values.
     * Returns null when conversion fails. Rejects surrounding whitespace
     * and control bytes (including actual embedded NUL bytes) before invoking
     * PHP 8 date parsing functions. Literal backslash-zero text is distinct
     * from a NUL byte and remains subject to normal date parsing.
     *
     * @param mixed $value Unix timestamp, numeric timestamp string or date value.
     * @param string $offset Joomla site timezone, defaulting to UTC.
     * @return Date|null Timezone-aware Joomla Date, or null if conversion failed.
     */
    public static function fromValue(mixed $value, string $offset): ?Date
    {
        if ((!is_int($value) && !is_string($value))
            || (is_string($value) && (trim($value) === '' || strlen($value) > 128
                || trim($value) !== $value
                || preg_match('/[\\x00-\\x1F\\x7F]/', $value) === 1)))) {
            return null;
        }

        // Signed Unix timestamp strings must behave exactly like integer
        // timestamps. Check both limits before casting: oversized negative
        // strings can otherwise be parsed as dates or saturate to PHP_INT_MIN.
        $timestampString = is_string($value) && preg_match('/^[+-]?[0-9]+$/D', $value) === 1;

        if ($timestampString) {
            $negative = str_starts_with($value, '-');
            $hasSign = $negative || str_starts_with($value, '+');
            $digits = ltrim($hasSign ? substr($value, 1) : $value, '0');
            $digits = $digits === '' ? '0' : $digits;
            $limit = $negative ? substr((string) PHP_INT_MIN, 1) : (string) PHP_INT_MAX;

            if (strlen($digits) > strlen($limit)
                || (strlen($digits) === strlen($limit) && strcmp($digits, $limit) > 0)) {
                return null;
            }
        }

        try {
            if (is_int($value) || $timestampString) {
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
