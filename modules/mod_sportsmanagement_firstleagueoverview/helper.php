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

use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Diddipoeler\Module\SportsManagementFirstLeagueOverview\Site\Helper\FirstLeagueOverviewHelper;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

$nativeDependencies = [
    SiteRouteHelper::class => JPATH_SITE . '/components/com_sportsmanagement/src/Helper/SiteRouteHelper.php',
    SportsManagementDatabaseResolver::class => JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementDatabaseResolver.php',
    SportsManagementSiteApplicationResolver::class => JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementSiteApplicationResolver.php',
];

foreach ($nativeDependencies as $class => $file) {
    if (!class_exists($class, false) && is_file($file)) {
        require_once $file;
    }
}

if (!class_exists(FirstLeagueOverviewHelper::class)) {
    $nativeHelper = __DIR__ . '/src/Helper/FirstLeagueOverviewHelper.php';

    if (is_file($nativeHelper)) {
        require_once $nativeHelper;
    }
}

if (!class_exists(FirstLeagueOverviewHelper::class)) {
    throw new \RuntimeException('SportsManagement native FirstLeagueOverview module helper could not be loaded.', 500);
}

if (!class_exists('modjsmfirstleagueoverview', false)) {
    final class modjsmfirstleagueoverview
    {
        public static function getData($params): array
        {
            return self::result($params)['projects'];
        }

        public static function getfederations($params = null): array
        {
            return self::result($params)['federations'];
        }

        private static function result($params): array
        {
            $registry = $params instanceof Registry ? $params : new Registry((array) ($params ?? []));
            $app = SportsManagementSiteApplicationResolver::resolve();
            /** @var DatabaseInterface $database */
            $database = $app->getContainer()->get(DatabaseInterface::class);

            return (new FirstLeagueOverviewHelper())->getData($registry, $database);
        }
    }
}
