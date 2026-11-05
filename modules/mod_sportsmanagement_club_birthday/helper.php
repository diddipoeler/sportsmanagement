<?php
/**
 * Legacy compatibility bridge for the Joomla 5/6 club birthday helper.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Diddipoeler\Module\SportsManagementClubBirthday\Site\Helper\ClubBirthdayHelper;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

$nativeDependencies = [
    SiteRouteHelper::class => JPATH_SITE . '/components/com_sportsmanagement/src/Helper/SiteRouteHelper.php',
    SportsManagementDatabaseResolver::class => JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementDatabaseResolver.php',
    SportsManagementSiteApplicationResolver::class => JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementSiteApplicationResolver.php',
    ClubBirthdayHelper::class => __DIR__ . '/src/Helper/ClubBirthdayHelper.php',
];

foreach ($nativeDependencies as $class => $file) {
    if (!class_exists($class, false) && is_file($file)) {
        require_once $file;
    }
}

foreach ([
    SiteRouteHelper::class,
    SportsManagementDatabaseResolver::class,
    SportsManagementSiteApplicationResolver::class,
    ClubBirthdayHelper::class,
] as $requiredClass) {
    if (!class_exists($requiredClass)) {
        throw new \RuntimeException(
            'SportsManagement Club Birthday dependency could not be loaded: ' . $requiredClass,
            500
        );
    }
}

if (!class_exists('modSportsmanagementClubBirthdayHelper', false)) {
    final class modSportsmanagementClubBirthdayHelper
    {
        /**
         * Load club birthday information for legacy module templates.
         *
         * Normalises settings into Joomla Registry, retrieves the public
         * site application and a valid database service, then delegates
         * record lookup to the native Joomla 5/6 ClubBirthdayHelper.
         *
         * @param Registry|array $params Module configuration.
         * @param DatabaseInterface|null $database Optional database service.
         * @return array Birthday records prepared for display.
         * @throws \RuntimeException If the client or database is unavailable.
         */
        public static function getData($params, ?DatabaseInterface $database = null): array
        {
            $registry = $params instanceof Registry ? $params : new Registry((array) $params);
            $app = self::siteApplication();
            $database = self::joomlaDatabase($database);

            return (new ClubBirthdayHelper())->getData($registry, $app, $database);
        }

        /**
         * Sort club birthday records using the native helper's rules.
         *
         * Preserves the public legacy sorting method expected by older
         * Joomla module layouts and third-party integrations.
         *
         * @param array $clubs Club birthday records to sort.
         * @param int $sort Legacy birthday sorting mode.
         * @return array Sorted club birthday records.
         */
        public static function jsm_birthday_sort(array $clubs, int $sort): array
        {
            return ClubBirthdayHelper::sortClubs($clubs, $sort);
        }

        /**
         * Resolve and verify the Joomla frontend application.
         *
         * Loads the active application through the component resolver and
         * rejects non-site clients before requesting public birthday data.
         *
         * @return CMSApplicationInterface Valid site application.
         * @throws \RuntimeException When running outside the frontend.
         */
        private static function siteApplication(): CMSApplicationInterface
        {
            $app = SportsManagementSiteApplicationResolver::resolve();

            if (!$app->isClient('site')) {
                throw new \RuntimeException('SportsManagement Club Birthday legacy helper requires the Joomla site application.', 500);
            }

            return $app;
        }

        /**
         * Retrieve a valid database service for birthday queries.
         *
         * Uses the optional injected database or resolves the service
         * from Joomla's dependency container, verifying its interface
         * before returning it to the native module helper.
         *
         * @param DatabaseInterface|null $database Optional service override.
         * @return DatabaseInterface Joomla database connection.
         * @throws \RuntimeException If the database service is invalid.
         */
        private static function joomlaDatabase(?DatabaseInterface $database = null): DatabaseInterface
        {
            if ($database !== null) {
                return $database;
            }

            $database = Factory::getContainer()->get(DatabaseInterface::class);

            if (!$database instanceof DatabaseInterface) {
                throw new \RuntimeException('SportsManagement Club Birthday database service is unavailable.', 500);
            }

            return $database;
        }
    }
}
