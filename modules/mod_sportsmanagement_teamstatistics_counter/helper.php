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
        public static function getData($params, ?DatabaseInterface $database = null): array
        {
            $registry = $params instanceof Registry ? $params : new Registry((array) $params);
            $app = SportsManagementSiteApplicationResolver::resolve();

            if (!$app->isClient('site')) {
                throw new \RuntimeException('SportsManagement Team Statistics Counter legacy helper requires the Joomla site application.', 500);
            }

            if ($database === null) {
                /** @var DatabaseInterface $database */
                $database = Factory::getContainer()->get(DatabaseInterface::class);
            }

            return (new TeamStatisticsCounterHelper())->getData($registry, $database);
        }
    }
}
