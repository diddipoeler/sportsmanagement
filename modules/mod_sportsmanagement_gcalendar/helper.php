<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 GCalendar helper.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementGcalendar\Site\Helper\GcalendarHelper;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\Registry\Registry;

if (!class_exists(GcalendarHelper::class)) {
    $nativeHelper = __DIR__ . '/src/Helper/GcalendarHelper.php';

    if (is_file($nativeHelper)) {
        require_once $nativeHelper;
    }
}

if (!class_exists(GcalendarHelper::class)) {
    throw new \RuntimeException('SportsManagement native GCalendar module helper could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModGCalendarHelper', false)) {
    final class sportsmanagementModGCalendarHelper
    {
        public static function getCalendars($params): array
        {
            $registry = $params instanceof Registry ? $params : new Registry($params);
            /** @var SiteApplication $app */
            $app = Factory::getContainer()->get(SiteApplication::class);

            return (new GcalendarHelper())->getCalendars($registry, $app);
        }
    }
}
