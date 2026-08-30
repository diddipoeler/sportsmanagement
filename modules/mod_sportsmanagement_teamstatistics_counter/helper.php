<?php
/**
 * Legacy helper bridge for third-party overrides.
 */
\defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementTeamStatisticsCounter\Site\Helper\TeamStatisticsCounterHelper;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

if (!class_exists(TeamStatisticsCounterHelper::class)) {
    require_once __DIR__ . '/src/Helper/TeamStatisticsCounterHelper.php';
}

if (!class_exists('modJSMTeamStatisticsCounter', false)) {
    final class modJSMTeamStatisticsCounter
    {
        public static function getData($params): array
        {
            $registry = $params instanceof Registry ? $params : new Registry((array) $params);
            /** @var DatabaseInterface $joomlaDatabase */
            $joomlaDatabase = Factory::getContainer()->get(DatabaseInterface::class);

            return (new TeamStatisticsCounterHelper())->getData($registry, $joomlaDatabase);
        }
    }
}
