<?php
/**
 * Joomla 5/6 Google Calendar numeric parameter normalization regression.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

define('_JEXEC', 1);
require_once dirname(__DIR__, 2) . '/modules/mod_sportsmanagement_google_calendar/src/Helper/GoogleCalendarHelper.php';

$helper = new \Diddipoeler\Module\SportsManagementGoogleCalendar\Site\Helper\GoogleCalendarHelper();
$method = new ReflectionMethod($helper, 'normalisePositiveLimit');

foreach ([
    [5, 5, 2500, 5],
    ['10', 5, 2500, 10],
    [0, 5, 2500, 1],
    ['0002', 5, 2500, 2],
    [2501, 5, 2500, 2500],
    [20000, 60, 10080, 10080],
    [true, 5, 2500, 5],
    [false, 60, 10080, 60],
    ['2500junk', 5, 2500, 5],
    ['1e3', 5, 2500, 5],
    [' 10', 5, 2500, 5],
    [-1, 5, 2500, 5],
    ['-1', 5, 2500, 5],
    [[], 5, 2500, 5],
    [['5'], 5, 2500, 5],
    ['99999999999999999999999', 5, 2500, 5],
] as [$value, $default, $maximum, $expected]) {
    $actual = $method->invoke($helper, $value, $default, $maximum);

    if ($actual !== $expected) {
        throw new RuntimeException('Unexpected Google Calendar limit for ' . var_export($value, true));
    }
}

echo "Joomla 5/6 Google Calendar numeric limits passed.\n";
