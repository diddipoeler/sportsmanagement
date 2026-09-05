<?php
/**
 * Legacy helper bridge for third-party overrides.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementTeamStatisticsCounter\Site\Helper\TeamStatisticsCounterHelper;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

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
        public static function getData($params): array
        {
            $registry = $params instanceof Registry ? $params : new Registry((array) $params);
            /** @var DatabaseInterface $joomlaDatabase */
            $joomlaDatabase = Factory::getContainer()->get(DatabaseInterface::class);

            return (new TeamStatisticsCounterHelper())->getData($registry, $joomlaDatabase);
        }
    }
}
