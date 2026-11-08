<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 calendar runtime.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementCalendar\Site\Runtime\CalendarDate;
use Diddipoeler\Module\SportsManagementCalendar\Site\Runtime\CalendarRuntime;

if (!class_exists(CalendarRuntime::class) || !class_exists(CalendarDate::class)) {
    foreach ([
        CalendarDate::class => dirname(__DIR__) . '/src/Runtime/CalendarDate.php',
        CalendarRuntime::class => dirname(__DIR__) . '/src/Runtime/CalendarRuntime.php',
    ] as $class => $file) {
        if (class_exists($class)) {
            continue;
        }

        if (!is_file($file)) {
            throw new \RuntimeException('SportsManagement calendar runtime dependency is missing: ' . basename($file), 500);
        }

        require_once $file;
    }
}

if (!class_exists(CalendarRuntime::class) || !class_exists(CalendarDate::class)) {
    throw new \RuntimeException('SportsManagement calendar runtime classes could not be loaded.', 500);
}

if (!class_exists('JSMCalendar', false)) {
    class_alias(CalendarRuntime::class, 'JSMCalendar');
}

if (!class_exists('modJSMCalendarHelperDate', false)) {
    class_alias(CalendarDate::class, 'modJSMCalendarHelperDate');
}
