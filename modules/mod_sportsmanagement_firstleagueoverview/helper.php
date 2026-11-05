<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 first league overview helper.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Factory;

use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Diddipoeler\Module\SportsManagementFirstLeagueOverview\Site\Helper\FirstLeagueOverviewHelper;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

$nativeDependencies = [
    SiteRouteHelper::class => JPATH_SITE . '/components/com_sportsmanagement/src/Helper/SiteRouteHelper.php',
    SportsManagementDatabaseResolver::class => JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementDatabaseResolver.php',
    SportsManagementSiteApplicationResolver::class => JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementSiteApplicationResolver.php',
    FirstLeagueOverviewHelper::class => __DIR__ . '/src/Helper/FirstLeagueOverviewHelper.php',
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
    FirstLeagueOverviewHelper::class,
] as $requiredClass) {
    if (!class_exists($requiredClass)) {
        throw new \RuntimeException(
            'SportsManagement FirstLeagueOverview dependency could not be loaded: ' . $requiredClass,
            500
        );
    }
}

if (!class_exists('modjsmfirstleagueoverview', false)) {
    final class modjsmfirstleagueoverview
    {
        /**
         * Retrieve current first-league projects for historical module callers.
         *
         * Delegates the request to the shared adapter result() and extracts
         * the projects collection returned by the native Joomla 5/6 helper.
         *
         * @param Registry|array|null $params Module configuration.
         * @param DatabaseInterface|null $database Optional Joomla database.
         * @return array Project records for the overview layout.
         */
        public static function getData($params, ?DatabaseInterface $database = null): array
        {
            return self::result($params, $database)['projects'];
        }

        /**
         * Retrieve the federation entries associated with first leagues.
         *
         * Preserves the legacy static method signature and returns the
         * federation collection from the native helper's data result.
         *
         * @param Registry|array|null $params Module configuration.
         * @param DatabaseInterface|null $database Optional database override.
         * @return array Federation records for the module.
         */
        public static function getfederations($params = null, ?DatabaseInterface $database = null): array
        {
            return self::result($params, $database)['federations'];
        }

        /**
         * Load the combined first-league overview data from Joomla services.
         *
         * Converts legacy parameters to Registry, requires a site application,
         * resolves a validated database service and calls the native helper
         * that loads projects and federations.
         *
         * @param Registry|array|null $params Legacy or modern module options.
         * @param DatabaseInterface|null $database Optional database override.
         * @return array Combined projects and federations keyed collections.
         * @throws \RuntimeException If no valid site or database is available.
         */
        private static function result($params, ?DatabaseInterface $database = null): array
        {
            $registry = $params instanceof Registry ? $params : new Registry((array) ($params ?? []));
            $app = self::siteApplication();
            $database = self::joomlaDatabase($database);

            return (new FirstLeagueOverviewHelper())->getData($registry, $database);
        }

        /**
         * Require the Joomla frontend application for overview data access.
         *
         * Uses the component's application resolver and rejects administrator
         * clients before any public league records are requested.
         *
         * @return CMSApplicationInterface Valid Joomla site application.
         * @throws \RuntimeException When invoked outside the frontend.
         */
        private static function siteApplication(): CMSApplicationInterface
        {
            $app = SportsManagementSiteApplicationResolver::resolve();

            if (!$app->isClient('site')) {
                throw new \RuntimeException('SportsManagement FirstLeagueOverview legacy bridge requires the Joomla site application.', 500);
            }

            return $app;
        }

        /**
         * Resolve the Joomla database service used for first-league queries.
         *
         * Prefers a caller-injected DatabaseInterface; otherwise retrieves
         * the service from Joomla's container and validates its interface.
         *
         * @param DatabaseInterface|null $database Optional database override.
         * @return DatabaseInterface Valid database connection.
         * @throws \RuntimeException If a database service is unavailable.
         */
        private static function joomlaDatabase(?DatabaseInterface $database = null): DatabaseInterface
        {
            if ($database !== null) {
                return $database;
            }

            $database = Factory::getContainer()->get(DatabaseInterface::class);

            if (!$database instanceof DatabaseInterface) {
                throw new \RuntimeException('SportsManagement FirstLeagueOverview database service is unavailable.', 500);
            }

            return $database;
        }
    }
}
