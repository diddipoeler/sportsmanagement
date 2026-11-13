<?php
/**
 * Joomla 5/6 date conversion helpers extracted from sportsmanagementHelper.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;

final class SportsManagementDateHelper
{
    /**
     * Preserve the historical SportsManagement date conversion contract.
     *
     * Direction 1 converts YYYY-MM-DD to DD-MM-YYYY and ignores a trailing
     * time portion in SQL DATETIME values so legacy forms display dates only.
     * Any other direction converts DD-MM-YYYY to YYYY-MM-DD.
     * Compact DDMMYYYY and DDMMYY inputs are converted to YYYY-MM-DD only
     * when they contain digits and form a valid calendar date. The function
     * also validates SQL and display dates, including optional SQL time
     * components, and returns an empty string for malformed values.
     *
     * @param string $date Legacy date text supplied by a component form.
     * @param int $direction Conversion direction; 1 means ISO to display format.
     * @return string Converted date text or empty text for unsupported compact input.
     */
    public static function convertDate(string $date, int $direction = 1): string
    {
        if (!str_contains($date, '-')) {
            // Historical compact dates must contain digits only. Refuse
            // arbitrary eight-character strings before splitting them.
            if (preg_match('/^\d{8}$/D', $date) === 1) {
                if (!checkdate((int) substr($date, 2, 2), (int) substr($date, 0, 2), (int) substr($date, 4, 4))) {
                    return '';
                }

                return substr($date, 4, 4)
                    . '-' . substr($date, 2, 2)
                    . '-' . substr($date, 0, 2);
            }

            if (preg_match('/^\d{6}$/D', $date) === 1) {
                $year = (int) (substr(date('Y'), 0, 2) . substr($date, 4, 2));

                if (!checkdate((int) substr($date, 2, 2), (int) substr($date, 0, 2), $year)) {
                    return '';
                }

                return substr(date('Y'), 0, 2)
                    . substr($date, 4, 2)
                    . '-' . substr($date, 2, 2)
                    . '-' . substr($date, 0, 2);
            }

            return '';
        }

        if ($direction === 1) {
            // Reject malformed SQL dates and time components rather than
            // silently producing a plausible-looking display date.
            if (preg_match('/^(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{2}):(\d{2}):(\d{2}))?$/D', $date, $parts) !== 1
                || !checkdate((int) ($parts[2] ?? 0), (int) ($parts[3] ?? 0), (int) ($parts[1] ?? 0))) {
                return '';
            }

            if (isset($parts[4]) && ((int) $parts[4] > 23
                || (int) $parts[5] > 59 || (int) $parts[6] > 59)) {
                return '';
            }

            return $parts[3] . '-' . $parts[2] . '-' . $parts[1];
        }

        if (preg_match('/^(\d{2})-(\d{2})-(\d{4})$/D', $date, $parts) !== 1
            || !checkdate((int) ($parts[2] ?? 0), (int) ($parts[1] ?? 0), (int) ($parts[3] ?? 0))) {
            return '';
        }

        return $parts[3] . '-' . $parts[2] . '-' . $parts[1];
    }

    /**
     * Normalise a date value for a SQL DATE column.
     *
     * Joomla calendar fields can submit the configured display format while
     * MySQL/MariaDB strict mode only accepts ISO dates for DATE columns.
     * Empty and historical zero-date values become NULL. Recognized date
     * formats are round-trip checked to reject PHP's silent date rollover;
     * unknown input is returned unchanged for upstream validation.
     *
     * @param string|null $date Date text from Joomla forms or database fields.
     * @return string|null ISO-formatted date, original unknown input, or null for empty dates.
     */
    public static function toSqlDate(?string $date): ?string
    {
        $date = trim((string) $date);

        if ($date === '' || $date === '0000-00-00' || $date === '00-00-0000') {
            return null;
        }

        foreach (['Y-m-d', 'd-m-Y', 'd.m.Y', 'd/m/Y'] as $format) {
            $parsed = \DateTimeImmutable::createFromFormat('!' . $format, $date);

            if ($parsed instanceof \DateTimeImmutable && $parsed->format($format) === $date) {
                return $parsed->format('Y-m-d');
            }
        }

        // Preserve an unknown value so Joomla/DB validation can report it
        // instead of silently turning an invalid date into another value.
        return $date;
    }

    /**
     * Preserve getTimestamp() for the no-offset calls used by native code.
     *
     * Treats missing values and legacy database zero-date placeholders,
     * including valid HH:MM:SS suffixes, as the current moment. Rejects
     * corrupt clock components instead of turning them into the current time.
     * Parses other values through PHP before producing a Joomla timestamp;
     * invalid input returns zero.
     *
     * @param string|null $date Date/time value, or null to use the current time.
     * @return int Unix timestamp, or zero when parsing fails.
     */
    public static function getTimestamp(?string $date = null): int
    {
        $date = trim((string) $date);

        // Historical database imports can contain zero dates with different
        // time suffixes. Treat all zero-date placeholders consistently rather
        // than accepting only two hard-coded time values.
        if ($date === '' || $date === '0000-00-00') {
            $date = 'now';
        } elseif (str_starts_with($date, '0000-00-00')) {
            if (preg_match('/^0000-00-00 (\d{2}):(\d{2}):(\d{2})$/D', $date, $clock) !== 1
                || (int) $clock[1] > 23 || (int) $clock[2] > 59 || (int) $clock[3] > 59) {
                return 0;
            }

            $date = 'now';
        }

        $timestamp = strtotime($date);

        if ($timestamp === false) {
            return 0;
        }

        return Factory::getDate($timestamp)->getTimestamp();
    }
}
