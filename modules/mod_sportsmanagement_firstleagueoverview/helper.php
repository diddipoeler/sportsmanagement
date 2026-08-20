<?php
/**
 * Legacy helper bridge.
 *
 * The active Joomla 5/6 helper lives in src/Helper/FirstLeagueOverviewHelper.php.
 */
\defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementFirstLeagueOverview\Site\Helper\FirstLeagueOverviewHelper;
use Joomla\Registry\Registry;

if (!class_exists(FirstLeagueOverviewHelper::class)) {
    require_once __DIR__ . '/src/Helper/FirstLeagueOverviewHelper.php';
}

if (!class_exists('modjsmfirstleagueoverview', false)) {
    final class modjsmfirstleagueoverview
    {
        public static function getData($params): array
        {
            $registry = $params instanceof Registry ? $params : new Registry((array) $params);

            return (new FirstLeagueOverviewHelper())->getData($registry)['projects'];
        }

        public static function getfederations($params = null): array
        {
            $registry = $params instanceof Registry ? $params : new Registry((array) ($params ?? []));

            return (new FirstLeagueOverviewHelper())->getData($registry)['federations'];
        }
    }
}
