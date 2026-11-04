<?php
/**
 * Legacy compatibility bridge for the Joomla 5/6 SportsManagement count record module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Factory;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Diddipoeler\Module\SportsManagementCountRekord\Site\Helper\CountRekordHelper;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

$nativeDependencies = [
    SportsManagementDatabaseResolver::class => JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementDatabaseResolver.php',
    SportsManagementSiteApplicationResolver::class => JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementSiteApplicationResolver.php',
    CountRekordHelper::class => __DIR__ . '/src/Helper/CountRekordHelper.php',
];

foreach ($nativeDependencies as $class => $file) {
    if (!class_exists($class, false) && is_file($file)) {
        require_once $file;
    }
}

foreach ([
    SportsManagementDatabaseResolver::class,
    SportsManagementSiteApplicationResolver::class,
    CountRekordHelper::class,
] as $requiredClass) {
    if (!class_exists($requiredClass)) {
        throw new \RuntimeException('SportsManagement CountRekord dependency could not be loaded: ' . $requiredClass, 500);
    }
}

if (!class_exists('modJSMStatistikRekordHelper', false)) {
    final class modJSMStatistikRekordHelper
    {
        /**
         * Provide the legacy CountRekord module API over the native helper.
         * Converts old module parameters to Joomla Registry, requires the
         * frontend application and forwards the effective database connection
         * to the namespaced Joomla 5/6 CountRekordHelper.
         * @param mixed $params Joomla Registry or legacy configuration.
         * @param mixed $module Current Joomla module record.
         * @param DatabaseInterface|null $database Optional injected connection.
         * @return array Statistics and display data for the module.
         */
        public static function getData($params, $module, ?DatabaseInterface $database = null): array
        {
            $registry = $params instanceof Registry ? $params : new Registry((array) $params);
            $app = self::siteApplication();

            if (!$app->isClient('site')) {
                throw new \RuntimeException('SportsManagement CountRekord legacy helper requires the Joomla site application.', 500);
            }

            $database ??= self::joomlaDatabase();

            return (new CountRekordHelper())->getData($registry, $module, $database);
        }

        /**
         * Retrieve the current Joomla site application for the module bridge.
         * Delegates resolution to the component's native application
         * service to avoid deprecated static Joomla application calls.
         * @return CMSApplicationInterface Active site application.
         */
        private static function siteApplication(): CMSApplicationInterface
        {
            return SportsManagementSiteApplicationResolver::resolve();
        }

        /**
         * Resolve the Joomla-managed database used by the legacy module.
         * Loads DatabaseInterface through Joomla's service container and
         * fails explicitly when the service has the wrong type.
         * @return DatabaseInterface Active Joomla database connection.
         * @throws \RuntimeException If no valid database service exists.
         */
        private static function joomlaDatabase(): DatabaseInterface
        {
            $database = Factory::getContainer()->get(DatabaseInterface::class);

            if (!$database instanceof DatabaseInterface) {
                throw new \RuntimeException('SportsManagement CountRekord database service is unavailable.', 500);
            }

            return $database;
        }
    }
}
