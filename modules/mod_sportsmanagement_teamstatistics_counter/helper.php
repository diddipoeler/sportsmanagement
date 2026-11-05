<?php
/**
 * Legacy helper bridge for third-party overrides.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\SportsManagementModel;
use Diddipoeler\Component\SportsManagement\Site\Model\SportsManagementProjectModel;
use Diddipoeler\Component\SportsManagement\Site\Model\TeamstatsModel;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Diddipoeler\Module\SportsManagementTeamStatisticsCounter\Site\Helper\TeamStatisticsCounterHelper;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

$nativeDependencies = [
    SportsManagementDatabaseResolver::class => JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementDatabaseResolver.php',
    SportsManagementSiteApplicationResolver::class => JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementSiteApplicationResolver.php',
    SportsManagementModel::class => JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php',
    SportsManagementProjectModel::class => JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php',
    TeamstatsModel::class => JPATH_SITE . '/components/com_sportsmanagement/src/Model/TeamstatsModel.php',
    TeamStatisticsCounterHelper::class => __DIR__ . '/src/Helper/TeamStatisticsCounterHelper.php',
];

foreach ($nativeDependencies as $class => $file) {
    if (!class_exists($class, false) && is_file($file)) {
        require_once $file;
    }
}

foreach ([
    SportsManagementDatabaseResolver::class,
    SportsManagementSiteApplicationResolver::class,
    SportsManagementModel::class,
    SportsManagementProjectModel::class,
    TeamstatsModel::class,
    TeamStatisticsCounterHelper::class,
] as $requiredClass) {
    if (!class_exists($requiredClass)) {
        throw new \RuntimeException(
            'SportsManagement Team Statistics Counter dependency could not be loaded: ' . $requiredClass,
            500
        );
    }
}

if (!class_exists('modJSMTeamStatisticsCounter', false)) {
    final class modJSMTeamStatisticsCounter
    {
        /**
         * Load a team's statistics for legacy module template overrides.
         *
         * Normalises settings to Joomla Registry, checks that the resolved
         * application is the public site, obtains the database service
         * and delegates the query to TeamStatisticsCounterHelper.
         *
         * @param Registry|array $params Module configuration.
         * @param DatabaseInterface|null $database Optional database override.
         * @return array Team statistics prepared by the native helper.
         * @throws \RuntimeException For a missing site or database service.
         */
        public static function getData($params, ?DatabaseInterface $database = null): array
        {
            $registry = $params instanceof Registry ? $params : new Registry((array) $params);
            $app = self::siteApplication();

            if (!$app->isClient('site')) {
                throw new \RuntimeException('SportsManagement Team Statistics Counter legacy helper requires the Joomla site application.', 500);
            }

            $database = self::joomlaDatabase($database);

            return (new TeamStatisticsCounterHelper())->getData($registry, $database);
        }

        /**
         * Resolve the Joomla application for the team statistics adapter.
         *
         * Calls the shared component application resolver. The public
         * data method verifies that the result belongs to the site client.
         *
         * @return CMSApplicationInterface Resolved Joomla application.
         */
        private static function siteApplication(): CMSApplicationInterface
        {
            return SportsManagementSiteApplicationResolver::resolve();
        }

        /**
         * Resolve the Joomla database service for team statistics.
         *
         * Uses a caller-injected database when present, otherwise retrieves
         * DatabaseInterface from Joomla's container and validates the type
         * before passing it to the native module helper.
         *
         * @param DatabaseInterface|null $database Optional database override.
         * @return DatabaseInterface Validated Joomla database service.
         * @throws \RuntimeException If the service is unavailable.
         */
        private static function joomlaDatabase(?DatabaseInterface $database = null): DatabaseInterface
        {
            if ($database !== null) {
                return $database;
            }

            $database = Factory::getContainer()->get(DatabaseInterface::class);

            if (!$database instanceof DatabaseInterface) {
                throw new \RuntimeException('SportsManagement Team Statistics Counter database service is unavailable.', 500);
            }

            return $database;
        }
    }
}
