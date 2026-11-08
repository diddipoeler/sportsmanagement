<?php
/**
 * Legacy compatibility facade for the Joomla 5/6 birthday module helper.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Factory;

use Diddipoeler\Component\SportsManagement\Site\Helper\PersonNameFormatter;
use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Diddipoeler\Module\SportsManagementBirthday\Site\Helper\BirthdayHelper;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

$nativeDependencies = [
    PersonNameFormatter::class => JPATH_SITE . '/components/com_sportsmanagement/src/Helper/PersonNameFormatter.php',
    SiteRouteHelper::class => JPATH_SITE . '/components/com_sportsmanagement/src/Helper/SiteRouteHelper.php',
    SportsManagementDatabaseResolver::class => JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementDatabaseResolver.php',
    SportsManagementSiteApplicationResolver::class => JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementSiteApplicationResolver.php',
    BirthdayHelper::class => __DIR__ . '/src/Helper/BirthdayHelper.php',
];

foreach ($nativeDependencies as $class => $file) {
    if (class_exists($class)) {
        continue;
    }

    if (!is_file($file)) {
        throw new \RuntimeException('SportsManagement Birthday dependency file is missing: ' . basename($file), 500);
    }

    require_once $file;
}

foreach ([
    PersonNameFormatter::class,
    SiteRouteHelper::class,
    SportsManagementDatabaseResolver::class,
    SportsManagementSiteApplicationResolver::class,
] as $requiredClass) {
    if (!class_exists($requiredClass)) {
        throw new \RuntimeException(
            'SportsManagement Birthday dependencies could not be loaded: ' . $requiredClass,
            500
        );
    }
}

if (!class_exists(BirthdayHelper::class)) {
    throw new \RuntimeException('SportsManagement native Birthday module helper could not be loaded.', 500);
}

if (!function_exists('jsm_birthday_sort')) {
    /**
     * Sort birthday rows using the native birthday helper's person ordering.
     * Keeps the historical global function available to template overrides without duplicating the Joomla 5/6 sorting logic.
     *
     * @param array $rows Person records to order.
     * @param mixed $arguments Legacy sort arguments or separator.
     * @param bool $keys Whether to preserve the original row keys.
     * @return array Sorted birthday records.
     */
    function jsm_birthday_sort(array $rows, $arguments = '-', $keys = true): array
    {
        return BirthdayHelper::sortPersons($rows, $arguments, (bool) $keys);
    }
}

if (!class_exists('modSportsmanagementBirthdayDataHelper', false)) {
    final class modSportsmanagementBirthdayDataHelper
    {
        /**
         * Load birthday records with explicitly supplied Joomla dependencies.
         * Verifies that the caller uses the site application, resolves the database service and delegates filtering and formatting to the native helper.
         *
         * @param Registry $params Module configuration.
         * @param Registry $componentParams SportsManagement component configuration.
         * @param CMSApplicationInterface $app Current Joomla application.
         * @param DatabaseInterface|null $database Optional database override.
         * @return array Birthday records and presentation data.
         */
        public function getData(Registry $params, Registry $componentParams, CMSApplicationInterface $app, ?DatabaseInterface $database = null): array
        {
            if (!$app->isClient('site')) {
                throw new \RuntimeException('SportsManagement Birthday requires the Joomla site application.', 500);
            }

            $database = self::joomlaDatabase($database);

            return (new BirthdayHelper())->getData($params, $componentParams, $app, $database);
        }

        /**
         * Resolve the database service for the dependency-injected birthday data facade.
         * Uses the supplied database for testing or alternate connections; otherwise retrieves Joomla's typed database service and rejects an invalid service.
         *
         * @param DatabaseInterface|null $database Optional database instance.
         * @return DatabaseInterface Valid Joomla database connection.
         */
        private static function joomlaDatabase(?DatabaseInterface $database = null): DatabaseInterface
        {
            if ($database !== null) {
                return $database;
            }

            $database = Factory::getContainer()->get(DatabaseInterface::class);

            if (!$database instanceof DatabaseInterface) {
                throw new \RuntimeException('SportsManagement Birthday database service is unavailable.', 500);
            }

            return $database;
        }
    }
}

if (!class_exists('modSportsmanagementBirthdayHelper', false)) {
    final class modSportsmanagementBirthdayHelper
    {
        /**
         * Preserve the legacy static birthday helper API for older module layouts.
         * Normalises array parameters to Registry, obtains component settings and the site application, and delegates retrieval to the native Joomla helper.
         *
         * @param Registry|array $params Legacy module parameters.
         * @param DatabaseInterface|null $database Optional database override.
         * @return array Birthday records and presentation data.
         */
        public static function getData($params, ?DatabaseInterface $database = null): array
        {
            $registry = $params instanceof Registry ? $params : new Registry((array) $params);
            $app = self::siteApplication();

            if (!$app->isClient('site')) {
                throw new \RuntimeException('SportsManagement Birthday legacy facade requires the Joomla site application.', 500);
            }

            $database = self::joomlaDatabase($database);

            return (new BirthdayHelper())->getData(
                $registry,
                ComponentHelper::getParams('com_sportsmanagement'),
                $app,
                $database
            );
        }

        /**
         * Find the active Joomla site application through the component resolver.
         * Avoids deprecated global application lookups in Joomla 5/6 module execution.
         *
         * @return CMSApplicationInterface Current Joomla application.
         */
        private static function siteApplication(): CMSApplicationInterface
        {
            return SportsManagementSiteApplicationResolver::resolve();
        }

        /**
         * Obtain a database connection for the legacy static birthday facade.
         * Prefers an explicitly supplied connection and validates the container service before passing it to the native module implementation.
         *
         * @param DatabaseInterface|null $database Optional database instance.
         * @return DatabaseInterface Valid Joomla database connection.
         */
        private static function joomlaDatabase(?DatabaseInterface $database = null): DatabaseInterface
        {
            if ($database !== null) {
                return $database;
            }

            $database = Factory::getContainer()->get(DatabaseInterface::class);

            if (!$database instanceof DatabaseInterface) {
                throw new \RuntimeException('SportsManagement Birthday database service is unavailable.', 500);
            }

            return $database;
        }
    }
}
