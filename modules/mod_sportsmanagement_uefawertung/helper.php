<?php
/**
 * Compatibility facade for the Joomla 5/6 UEFA ranking module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  (C) 2015-2026
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Diddipoeler\Module\SportsManagementUefaWertung\Site\Helper\UefaWertungHelper;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

$nativeDependencies = [
    SportsManagementDatabaseResolver::class => JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementDatabaseResolver.php',
    SportsManagementSiteApplicationResolver::class => JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementSiteApplicationResolver.php',
    UefaWertungHelper::class => __DIR__ . '/src/Helper/UefaWertungHelper.php',
];

foreach ($nativeDependencies as $class => $file) {
    if (!class_exists($class, false) && is_file($file)) {
        require_once $file;
    }
}

foreach ([
    SportsManagementDatabaseResolver::class,
    SportsManagementSiteApplicationResolver::class,
    UefaWertungHelper::class,
] as $requiredClass) {
    if (!class_exists($requiredClass)) {
        throw new \RuntimeException(
            'SportsManagement UEFA ranking dependency could not be loaded: ' . $requiredClass,
            500
        );
    }
}

if (!class_exists('modJSMUefaWERTUNG', false)) {
    final class modJSMUefaWERTUNG
    {
        /**
         * Return country UEFA ranking totals using the legacy module facade.
         * Converts legacy module parameters to Joomla Registry, delegates to
         * the native helper and exposes only the ranked countries slice.
         * @param mixed $params Registry or legacy settings array.
         * @param DatabaseInterface|null $database Optional Joomla database connection.
         * @return array Country ranking results for the chosen seasons.
         */
        public static function getData($params, ?DatabaseInterface $database = null): array
        {
            return self::result($params, $database)['rankings'];
        }

        /**
         * Return ranking season labels through the legacy module API.
         * Shares the native result-building path with getData() and returns
         * the selected list of season names instead of country points.
         * @param mixed $params Registry or legacy settings array.
         * @param DatabaseInterface|null $database Optional Joomla database connection.
         * @return array<string> Display names of selected ranking seasons.
         */
        public static function getSeasonNames($params, ?DatabaseInterface $database = null): array
        {
            return self::result($params, $database)['seasons'];
        }

        /**
         * Prepare UEFA ranking data with Joomla 5/6 services.
         * Validates the site application, resolves the primary database from
         * Joomla DI unless one is provided, and delegates the ranking query
         * to the native namespaced module helper.
         * @param mixed $params Original module configuration.
         * @param DatabaseInterface|null $database Optional database override.
         * @return array{seasons:array,rankings:array} Ranking module payload.
         */
        private static function result($params, ?DatabaseInterface $database = null): array
        {
            $registry = $params instanceof Registry ? $params : new Registry((array) $params);
            $app = self::siteApplication();

            if (!$app->isClient('site')) {
                throw new \RuntimeException('SportsManagement UEFA ranking legacy helper requires the Joomla site application.', 500);
            }

            $database = self::joomlaDatabase($database);

            return (new UefaWertungHelper())->getData($registry, $app, $database);
        }

        /**
         * Resolve the active Joomla site application for legacy module calls.
         * Uses SportsManagement's namespaced application resolver, avoiding
         * deprecated static getApplication() access and wrong-client requests.
         * @return CMSApplicationInterface Current site application.
         */
        private static function siteApplication(): CMSApplicationInterface
        {
            return SportsManagementSiteApplicationResolver::resolve();
        }

        /**
         * Obtain the Joomla database service or an explicitly supplied instance.
         * Uses Joomla's dependency-injection container when the module does
         * not receive a database from its caller.
         * @param DatabaseInterface|null $database Explicit database when available.
         * @return DatabaseInterface Effective Joomla database connection.
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
