<?php
/**
 * Date conversion helper for native Joomla 5/6 site views.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Helper;

\defined('_JEXEC') or die;

/** Date conversion used by native site templates. */
final class SportsManagementDateHelper
{
    public static function convertDate(string $date, int $direction = 1): string
    {
        $date = trim($date);

        // Preserve the two compact historical input formats (DDMMYY and DDMMYYYY).
        if (preg_match('/^(\d{2})(\d{2})(\d{2}|\d{4})$/D', $date, $parts) === 1) {
            $day = (int) $parts[1];
            $month = (int) $parts[2];
            $year = strlen($parts[3]) === 2 ? substr(date('Y'), 0, 2) . $parts[3] : $parts[3];

            return checkdate($month, $day, (int) $year)
                ? $year . '-' . $parts[2] . '-' . $parts[1]
                : '';
        }

        if ($direction === 1) {
            // Accept SQL DATE and DATETIME values without accidentally including the time in the day.
            if (preg_match('/^(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{2}):(\d{2}):(\d{2}))?$/D', $date, $parts) !== 1
                || !checkdate((int) $parts[2], (int) $parts[3], (int) $parts[1])) {
                return '';
            }

            if (isset($parts[4]) && ((int) $parts[4] > 23
                || (int) $parts[5] > 59 || (int) $parts[6] > 59)) {
                return '';
            }

            return $parts[3] . '-' . $parts[2] . '-' . $parts[1];
        }

        if (preg_match('/^(\d{2})-(\d{2})-(\d{4})$/D', $date, $parts) !== 1
            || !checkdate((int) $parts[2], (int) $parts[1], (int) $parts[3])) {
            return '';
        }

        return $parts[3] . '-' . $parts[2] . '-' . $parts[1];
    }
}
