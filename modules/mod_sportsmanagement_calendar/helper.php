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

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Diddipoeler\Module\SportsManagementCalendar\Site\Helper\CalendarHelper;

$nativeDependencies = [
    SportsManagementSiteApplicationResolver::class => JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementSiteApplicationResolver.php',
    CalendarHelper::class => __DIR__ . '/src/Helper/CalendarHelper.php',
];

foreach ($nativeDependencies as $class => $file) {
    if (!class_exists($class, false) && is_file($file)) {
        require_once $file;
    }
}

foreach ([
    SportsManagementSiteApplicationResolver::class,
    CalendarHelper::class,
] as $requiredClass) {
    if (!class_exists($requiredClass)) {
        throw new \RuntimeException(
            'SportsManagement Calendar dependency could not be loaded: ' . $requiredClass,
            500
        );
    }
}

if (!class_exists('modJSMCalendarHelper', false)) {
    class_alias(CalendarHelper::class, 'modJSMCalendarHelper');
}

if (!class_exists('ModSportsmanagementCalendarHelper', false)) {
    class_alias(CalendarHelper::class, 'ModSportsmanagementCalendarHelper');
}
