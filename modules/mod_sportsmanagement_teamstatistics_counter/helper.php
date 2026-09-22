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

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Diddipoeler\Module\SportsManagementTeamStatisticsCounter\Site\Helper\TeamStatisticsCounterHelper;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

if (!class_exists(SportsManagementSiteApplicationResolver::class)) {
    $resolverFile = JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementSiteApplicationResolver.php';

    if (is_file($resolverFile)) {
        require_once $resolverFile;
    }
}

if (!class_exists(TeamStatisticsCounterHelper::class)) {
    $nativeHelper = __DIR__ . '/src/Helper/TeamStatisticsCounterHelper.php';

    if (is_file($nativeHelper)) {
        require_once $nativeHelper;
    }
}

if (!class_exists(TeamStatisticsCounterHelper::class)) {
    throw new \RuntimeException('SportsManagement Team Statistics Counter helper could not be loaded.', 500);
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
                $database = $app->getContainer()->get(DatabaseInterface::class);
            }

            return (new TeamStatisticsCounterHelper())->getData($registry, $database);
        }
    }
}
