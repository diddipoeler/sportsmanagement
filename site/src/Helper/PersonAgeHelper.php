<?php
/**
 * Calculate person ages for native Joomla 5/6 site views.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Helper;

\defined('_JEXEC') or die;

/**
 * Calculate a person's age while preserving the historical SportsManagement semantics.
 */
final class PersonAgeHelper
{
    /**
     * Calculate completed years between a birth date and a reference date.
     *
     * Preserves the historic zero-date sentinel, which uses today's date
     * when no reference date is provided. Returns '-' for malformed or
     * missing dates and subtracts one year before the birthday occurs.
     *
     * @param string $date Birth date in YYYY-MM-DD format.
     * @param string $secondDate Reference date, or zero-date for today.
     * @return int|string Completed years, or '-' for invalid date input.
     */
    public static function calculate(string $date, string $secondDate = '0000-00-00'): int|string
    {
        $birth = self::parseDate($date);

        if ($birth === null) {
            return '-';
        }

        if ($secondDate === '0000-00-00') {
            $target = [
                (int) date('Y'),
                (int) date('m'),
                (int) date('d'),
            ];
        } else {
            $target = self::parseDate($secondDate);

            if ($target === null) {
                return '-';
            }
        }

        $age = $target[0] - $birth[0];

        if ($birth[1] > $target[1] || ($birth[1] === $target[1] && $birth[2] > $target[2])) {
            --$age;
        }

        return $age;
    }

    /**
     * Parse a SportsManagement date into numeric year, month and day.
     *
     * Rejects the historical zero-date sentinel and strings not matching
     * YYYY-MM-DD. Validate the actual calendar day (including leap years)
     * before returning numeric parts for the historical age calculation.
     *
     * @param string $date Date text to parse.
     * @return array{0:int,1:int,2:int}|null Date parts or null.
     */
    private static function parseDate(string $date): ?array
    {
        if ($date === '0000-00-00'
            || preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/', $date, $matches) !== 1
        ) {
            return null;
        }

        $year = (int) $matches[1];
        $month = (int) $matches[2];
        $day = (int) $matches[3];

        // PHP 8 would otherwise accept impossible calendar dates here and
        // calculate plausible but incorrect ages for imported person records.
        if (!checkdate($month, $day, $year)) {
            return null;
        }

        return [$year, $month, $day];
    }
}
