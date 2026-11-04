<?php
/**
 * Joomla 5/6 compatibility bridge for the SportsManagement Project Map helper.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Diddipoeler\Module\SportsManagementProjectMap\Site\Helper\ProjectMapHelper;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;

$nativeDependencies = [
    SiteRouteHelper::class => JPATH_SITE . '/components/com_sportsmanagement/src/Helper/SiteRouteHelper.php',
    SportsManagementSiteApplicationResolver::class => JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementSiteApplicationResolver.php',
    ProjectMapHelper::class => __DIR__ . '/src/Helper/ProjectMapHelper.php',
];

foreach ($nativeDependencies as $class => $file) {
    if (!class_exists($class, false) && is_file($file)) {
        require_once $file;
    }
}

foreach ([
    SiteRouteHelper::class,
    SportsManagementSiteApplicationResolver::class,
    ProjectMapHelper::class,
] as $requiredClass) {
    if (!class_exists($requiredClass)) {
        throw new \RuntimeException(
            'SportsManagement ProjectMap dependency could not be loaded: ' . $requiredClass,
            500
        );
    }
}

if (!class_exists('modJSMprojectmaphelper', false)) {
    final class modJSMprojectmaphelper
    {
        /**
         * Create the native Joomla 5/6 project map helper.
         *
         * Centralises helper construction for old module templates that
         * still call the historical static project-map facade.
         *
         * @return ProjectMapHelper Native map service.
         */
        private static function helper(): ProjectMapHelper
        {
            return new ProjectMapHelper();
        }

        /**
         * Return configured map options as a JavaScript object body.
         *
         * Delegates retrieval and JavaScript formatting to the native
         * ProjectMapHelper, preserving the legacy template output format.
         *
         * @return string Serialised map configuration for the template.
         */
        public static function getmain_settings(): string
        {
            $helper = self::helper();

            return $helper->toJavascriptObjectBody($helper->getMainSettings());
        }

        /**
         * Load project map markers for the requested seasons.
         *
         * Requires an active Joomla site application, obtains a valid
         * database service and lets the native helper fetch map data
         * for the selected season identifiers.
         *
         * @param mixed $seasonIds Selected season IDs in the legacy format.
         * @param DatabaseInterface|null $database Optional database override.
         * @return array Project map data for the module.
         * @throws \RuntimeException If the client or database is unavailable.
         */
        public static function getData($seasonIds, ?DatabaseInterface $database = null): array
        {
            $app = self::siteApplication();

            if (!$app->isClient('site')) {
                throw new \RuntimeException('SportsManagement ProjectMap legacy helper requires the Joomla site application.', 500);
            }

            $database = self::joomlaDatabase($database);

            return self::helper()->getData($seasonIds, $database);
        }

        /**
         * Aggregate project data by geographic region for the map.
         *
         * Normalises the provided projects to an array and serialises the
         * region data in the legacy JavaScript object-body format.
         *
         * @param array|mixed $projects Source project records.
         * @return string Serialised regional map data.
         */
        public static function createregions($projects): string
        {
            $helper = self::helper();

            return $helper->toJavascriptObjectBody($helper->createRegions((array) $projects));
        }

        /**
         * Prepare state-specific project map data for older templates.
         *
         * Delegates grouping to the native map helper and serialises
         * the resulting values as a JavaScript object body.
         *
         * @param array|mixed $projects Project records to group by state.
         * @return string Serialised state-specific map data.
         */
        public static function createstate_specific($projects): string
        {
            $helper = self::helper();

            return $helper->toJavascriptObjectBody($helper->createStateSpecific((array) $projects));
        }

        /**
         * Resolve the Joomla site application for project-map requests.
         *
         * Returns the shared component application resolver's result.
         * The public data-loading method checks its site-client status.
         *
         * @return CMSApplicationInterface Resolved Joomla application.
         */
        private static function siteApplication(): CMSApplicationInterface
        {
            return SportsManagementSiteApplicationResolver::resolve();
        }

        /**
         * Obtain the Joomla database service for project-map queries.
         *
         * Uses an explicitly provided database when available, otherwise
         * requests DatabaseInterface from Joomla's service container
         * and checks the resulting service before using it.
         *
         * @param DatabaseInterface|null $database Optional database override.
         * @return DatabaseInterface Validated Joomla database service.
         * @throws \RuntimeException If the service cannot be resolved.
         */
        private static function joomlaDatabase(?DatabaseInterface $database = null): DatabaseInterface
        {
            if ($database !== null) {
                return $database;
            }

            $database = Factory::getContainer()->get(DatabaseInterface::class);

            if (!$database instanceof DatabaseInterface) {
                throw new \RuntimeException('SportsManagement ProjectMap database service is unavailable.', 500);
            }

            return $database;
        }
    }
}
