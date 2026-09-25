<?php
/**
 * SportsManagement legacy helper bridge for third-party template overrides.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Factory;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Diddipoeler\Module\SportsManagementSportsTypeStatistics\Site\Helper\SportsTypeStatisticsHelper;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

$nativeDependencies = [
    SportsManagementDatabaseResolver::class => JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementDatabaseResolver.php',
    SportsTypeStatisticsHelper::class => __DIR__ . '/src/Helper/SportsTypeStatisticsHelper.php',
];

foreach ($nativeDependencies as $class => $file) {
    if (!class_exists($class, false) && is_file($file)) {
        require_once $file;
    }
}

foreach ([
    SportsManagementDatabaseResolver::class,
    SportsTypeStatisticsHelper::class,
] as $requiredClass) {
    if (!class_exists($requiredClass)) {
        throw new \RuntimeException(
            'SportsManagement Sports Type Statistics dependency could not be loaded: ' . $requiredClass,
            500
        );
    }
}

if (!class_exists('modJSMSportsHelper', false)) {
    final class modJSMSportsHelper
    {
        public static function getData(&$params, ?DatabaseInterface $database = null): array
        {
            $registry = $params instanceof Registry ? $params : new Registry((array) $params);
            if ($database === null) {
                /** @var DatabaseInterface $database */
                $database = Factory::getContainer()->get(DatabaseInterface::class);
            }

            $data = (new SportsTypeStatisticsHelper())->getData($registry, $database);
            $sportTypeId = (int) $registry->get('sportstypes', 0);
            $legacy = ['sportstype' => []];

            if ($sportTypeId > 0 && !empty($data['sportstype'])) {
                $legacy['sportstype'][$sportTypeId] = $data['sportstype'];
            }

            $map = [
                'projects' => 'projectscount',
                'leagues' => 'leaguescount',
                'seasons' => 'seasonscount',
                'playgrounds' => 'playgroundscount',
                'clubs' => 'clubscount',
                'teams' => 'projectteamscount',
                'players' => 'personscount',
                'divisions' => 'projectdivisionscount',
                'rounds' => 'projectroundscount',
                'matches' => 'projectmatchescount',
                'player_events' => 'projectmatcheseventscount',
                'player_stats' => 'projectmatchesstatscount',
            ];

            foreach ($map as $nativeKey => $legacyKey) {
                if (array_key_exists($nativeKey, $data['counts'] ?? [])) {
                    $legacy[$legacyKey] = (int) $data['counts'][$nativeKey];
                }
            }

            return $legacy;
        }
    }
}
