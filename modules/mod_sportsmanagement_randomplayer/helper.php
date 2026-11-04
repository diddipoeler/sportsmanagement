<?php
/**
 * Legacy compatibility facade for the native Joomla 5/6 random player module helper.
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
use Diddipoeler\Module\SportsManagementRandomPlayer\Site\Helper\RandomPlayerHelper;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

$nativeDependencies = [
    SiteRouteHelper::class => JPATH_SITE . '/components/com_sportsmanagement/src/Helper/SiteRouteHelper.php',
    SportsManagementDatabaseResolver::class => JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementDatabaseResolver.php',
    SportsManagementSiteApplicationResolver::class => JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementSiteApplicationResolver.php',
    RandomPlayerHelper::class => __DIR__ . '/src/Helper/RandomPlayerHelper.php',
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
    RandomPlayerHelper::class,
] as $requiredClass) {
    if (!class_exists($requiredClass)) {
        throw new \RuntimeException('SportsManagement RandomPlayer dependency could not be loaded: ' . $requiredClass, 500);
    }
}

if (!class_exists('modJSMRandomplayerHelper', false)) {
    final class modJSMRandomplayerHelper
    {
        /**
         * Load random-player data for older module templates and integrations.
         *
         * Wraps legacy parameter arrays in Joomla Registry, verifies that
         * the active application is the site client, obtains a Joomla database
         * service and delegates the data retrieval to RandomPlayerHelper.
         *
         * @param Registry|array $params Module settings, passed by reference for compatibility.
         * @param DatabaseInterface|null $database Optional database service override.
         * @return array Player details prepared by the native module helper.
         * @throws \RuntimeException When the site client or database is unavailable.
         */
        public static function getData(&$params, ?DatabaseInterface $database = null): array
        {
            $registry = $params instanceof Registry ? $params : new Registry((array) $params);
            $app = self::siteApplication();
            $database = self::joomlaDatabase($database);

            return (new RandomPlayerHelper())->getData($registry, $database);
        }
        /**
         * Obtain the current Joomla frontend application.
         *
         * Uses the component's shared application resolver and rejects
         * non-site execution contexts before loading public player data.
         *
         * @return CMSApplicationInterface Valid Joomla site application.
         * @throws \RuntimeException If the active client is not the site.
         */
        private static function siteApplication(): CMSApplicationInterface
        {
            $app = SportsManagementSiteApplicationResolver::resolve();

            if (!$app->isClient('site')) {
                throw new \RuntimeException('SportsManagement RandomPlayer legacy helper requires the Joomla site application.', 500);
            }

            return $app;
        }

        /**
         * Resolve the database service used for random-player queries.
         *
         * Accepts an explicitly supplied Joomla database instance or
         * retrieves DatabaseInterface from Joomla's dependency container,
         * rejecting an unexpected service type instead of continuing.
         *
         * @param DatabaseInterface|null $database Optional caller-supplied service.
         * @return DatabaseInterface A valid Joomla database service.
         * @throws \RuntimeException If the service cannot be resolved.
         */
        private static function joomlaDatabase(?DatabaseInterface $database = null): DatabaseInterface
        {
            if ($database !== null) {
                return $database;
            }

            $database = Factory::getContainer()->get(DatabaseInterface::class);

            if (!$database instanceof DatabaseInterface) {
                throw new \RuntimeException('SportsManagement RandomPlayer database service is unavailable.', 500);
            }

            return $database;
        }
    }
}
