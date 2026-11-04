<?php
/**
 * SportsManagement TrainingsData legacy helper bridge for Joomla 5/6.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) 2015 diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Diddipoeler\Module\SportsManagementTrainingsData\Site\Helper\TrainingsDataHelper;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

$nativeDependencies = [
    SportsManagementDatabaseResolver::class => JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementDatabaseResolver.php',
    SportsManagementSiteApplicationResolver::class => JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementSiteApplicationResolver.php',
    TrainingsDataHelper::class => __DIR__ . '/src/Helper/TrainingsDataHelper.php',
];

foreach ($nativeDependencies as $class => $file) {
    if (!class_exists($class, false) && is_file($file)) {
        require_once $file;
    }
}

foreach ([
    SportsManagementDatabaseResolver::class,
    SportsManagementSiteApplicationResolver::class,
    TrainingsDataHelper::class,
] as $requiredClass) {
    if (!class_exists($requiredClass)) {
        throw new \RuntimeException('SportsManagement TrainingsData dependency could not be loaded: ' . $requiredClass, 500);
    }
}

if (!class_exists('modJSMTrainingsData', false)) {
    final class modJSMTrainingsData
    {
        /**
         * Retrieve the training session rows for legacy module callers.
         *
         * Normalises module settings into Joomla Registry, checks that the
         * active application is the public site, resolves a database service
         * and delegates the query to the native TrainingsDataHelper.
         *
         * @param Registry|array $params Module parameters.
         * @param DatabaseInterface|null $database Optional database override.
         * @return array Training records prepared for the module template.
         * @throws \RuntimeException For an invalid client or database.
         */
        public static function getData($params, ?DatabaseInterface $database = null): array
        {
            $registry = $params instanceof Registry ? $params : new Registry((array) $params);
            $app = self::siteApplication();

            if (!$app->isClient('site')) {
                throw new \RuntimeException('SportsManagement TrainingsData legacy helper requires the Joomla site application.', 500);
            }

            $database = self::joomlaDatabase($database);

            return (new TrainingsDataHelper())->getData($registry, $database);
        }

        /**
         * Resolve the Joomla application for the training-data adapter.
         *
         * Returns the central SportsManagement site application resolver's
         * result, which the caller verifies is the public site client.
         *
         * @return CMSApplicationInterface Current Joomla application.
         */
        private static function siteApplication(): CMSApplicationInterface
        {
            return SportsManagementSiteApplicationResolver::resolve();
        }

        /**
         * Retrieve a valid Joomla database service for training queries.
         *
         * Uses the caller-supplied service when available; otherwise obtains
         * DatabaseInterface from Joomla's dependency injection container
         * and rejects an unexpected service before returning it.
         *
         * @param DatabaseInterface|null $database Optional database override.
         * @return DatabaseInterface Validated Joomla database service.
         * @throws \RuntimeException If the container service is unavailable.
         */
        private static function joomlaDatabase(?DatabaseInterface $database = null): DatabaseInterface
        {
            if ($database !== null) {
                return $database;
            }

            $database = Factory::getContainer()->get(DatabaseInterface::class);

            if (!$database instanceof DatabaseInterface) {
                throw new \RuntimeException('SportsManagement TrainingsData database service is unavailable.', 500);
            }

            return $database;
        }
    }
}
