<?php
/**
 * Legacy helper bridge.
 *
 * The active Joomla 5/6 helper lives in src/Helper/FirstLeagueOverviewHelper.php.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementFirstLeagueOverview\Site\Helper\FirstLeagueOverviewHelper;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

if (!class_exists(FirstLeagueOverviewHelper::class)) {
    require_once __DIR__ . '/src/Helper/FirstLeagueOverviewHelper.php';
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
