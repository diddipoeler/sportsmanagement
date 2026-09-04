<?php
/**
 * Legacy compatibility bridge for the Joomla 5/6 SportsManagement calendar module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementCalendar\Site\Helper\CalendarHelper;

if (!class_exists(CalendarHelper::class)) {
    $nativeHelper = __DIR__ . '/src/Helper/CalendarHelper.php';

    if (is_file($nativeHelper)) {
        require_once $nativeHelper;
    }
}

if (!class_exists(CalendarHelper::class)) {
    throw new \RuntimeException('SportsManagement native Calendar module helper could not be loaded.', 500);
}

if (!class_exists('modJSMCalendarHelper', false)) {
    class_alias(CalendarHelper::class, 'modJSMCalendarHelper');
}

if (!class_exists('ModSportsmanagementCalendarHelper', false)) {
    class_alias(CalendarHelper::class, 'ModSportsmanagementCalendarHelper');
}
