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

use Diddipoeler\Module\SportsManagementFirstLeagueOverview\Site\Helper\FirstLeagueOverviewHelper;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

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
            /** @var DatabaseInterface $database */
            $database = Factory::getContainer()->get(DatabaseInterface::class);

            return (new FirstLeagueOverviewHelper())->getData($registry, $database);
        }
    }
}
