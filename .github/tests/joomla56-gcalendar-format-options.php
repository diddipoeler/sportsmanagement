<?php
/**
 * Standalone regression checks for Joomla 5/6 GCalendar format options.
 *
 * Usage: php .github/tests/joomla56-gcalendar-format-options.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

defined('_JEXEC') || define('_JEXEC', 1);

require_once dirname(__DIR__, 2)
    . '/modules/mod_sportsmanagement_gcalendar/src/Helper/GcalendarHelper.php';

$format = new ReflectionMethod(
    \Diddipoeler\Module\SportsManagementGcalendar\Site\Helper\GcalendarHelper::class,
    'safeFormat'
);

foreach ([
    ['M Y', 'M Y'],
    ['ddd, D MMM', 'ddd, D MMM'],
    ['H:i', 'H:i'],
    ['Ä MMMM', 'Ä MMMM'],
    ['', 'M Y'],
    [null, 'M Y'],
    [false, 'M Y'],
    [[], 'M Y'],
    ["M\nY", 'M Y'],
    ["M\x00Y", 'M Y'],
    [str_repeat('M', 129), 'M Y'],
    ["\xC3\x28", 'M Y'],
] as [$configured, $expected]) {
    if ($format->invoke(null, $configured, 'M Y') !== $expected) {
        throw new RuntimeException('GCalendar format parameter validation regressed.');
    }
}

echo "Joomla 5/6 GCalendar format checks passed.\n";
