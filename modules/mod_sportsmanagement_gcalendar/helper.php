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

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Diddipoeler\Module\SportsManagementGcalendar\Site\Helper\GcalendarHelper;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

$nativeDependencies = [
    SportsManagementSiteApplicationResolver::class => JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementSiteApplicationResolver.php',
];

foreach ($nativeDependencies as $class => $file) {
    if (!class_exists($class, false) && is_file($file)) {
        require_once $file;
    }
}

if (!class_exists(SportsManagementSiteApplicationResolver::class)) {
    throw new \RuntimeException('SportsManagement site application resolver could not be loaded.', 500);
}

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
            $app = SportsManagementSiteApplicationResolver::resolve();

            if (!$app instanceof SiteApplication || !$app->isClient('site')) {
                throw new \RuntimeException('SportsManagement GCalendar legacy helper requires the Joomla site application.', 500);
            }

            /** @var DatabaseInterface $db */
            $db = Factory::getContainer()->get(DatabaseInterface::class);

            return (new GcalendarHelper())->getCalendars($registry, $app, $db);
        }
    }
}
