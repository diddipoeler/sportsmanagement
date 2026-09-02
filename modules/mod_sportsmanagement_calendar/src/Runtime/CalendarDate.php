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
