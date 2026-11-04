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
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

$nativeDependencies = [
    SportsManagementSiteApplicationResolver::class => JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementSiteApplicationResolver.php',
    GcalendarHelper::class => __DIR__ . '/src/Helper/GcalendarHelper.php',
];

foreach ($nativeDependencies as $class => $file) {
    if (!class_exists($class, false) && is_file($file)) {
        require_once $file;
    }
}

foreach ([
    SportsManagementSiteApplicationResolver::class,
    GcalendarHelper::class,
] as $requiredClass) {
    if (!class_exists($requiredClass)) {
        throw new \RuntimeException(
            'SportsManagement GCalendar dependency could not be loaded: ' . $requiredClass,
            500
        );
    }
}

if (!class_exists('sportsmanagementModGCalendarHelper', false)) {
    final class sportsmanagementModGCalendarHelper
    {
        /**
         * Provide the legacy static interface for GCalendar module lookups.
         * Converts older parameter arrays into a Joomla Registry, validates
         * the site application and delegates authorised calendar loading to
         * the native namespaced GcalendarHelper implementation.
         * @param mixed $params Module selection settings or Registry instance.
         * @param DatabaseInterface|null $database Optional preselected database.
         * @return array List of calendars available to the current user.
         */
        public static function getCalendars($params, ?DatabaseInterface $database = null): array
        {
            $registry = $params instanceof Registry ? $params : new Registry((array) $params);
            $app = self::siteApplication();

            if (!$app->isClient('site')) {
                throw new \RuntimeException('SportsManagement GCalendar legacy helper requires the Joomla site application.', 500);
            }

            $database = self::joomlaDatabase($database);

            return (new GcalendarHelper())->getCalendars($registry, $app, $database);
        }

        /**
         * Resolve Joomla's active site application for legacy callers.
         * Uses the component's site application resolver rather than the
         * removed global Joomla application factories.
         * @return CMSApplicationInterface Current Joomla site application.
         */
        private static function siteApplication(): CMSApplicationInterface
        {
            return SportsManagementSiteApplicationResolver::resolve();
        }

        /**
         * Resolve the database connection for compatibility calendar queries.
         * Honours an explicitly injected database, otherwise requests Joomla's
         * DatabaseInterface through the service container.
         * @param DatabaseInterface|null $database Optional caller-supplied connection.
         * @return DatabaseInterface Effective calendar database.
         */
        private static function joomlaDatabase(?DatabaseInterface $database = null): DatabaseInterface
        {
            if ($database !== null) {
                return $database;
            }

            /** @var DatabaseInterface $database */
            $database = Factory::getContainer()->get(DatabaseInterface::class);

            return $database;
        }
    }
}
