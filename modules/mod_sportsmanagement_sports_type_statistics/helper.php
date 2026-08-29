<?php
/**
 * SportsManagement legacy helper bridge for third-party template overrides.
 */
\defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementSportsTypeStatistics\Site\Helper\SportsTypeStatisticsHelper;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\Registry\Registry;

if (!class_exists(SportsTypeStatisticsHelper::class)) {
    require_once __DIR__ . '/src/Helper/SportsTypeStatisticsHelper.php';
}

if (!class_exists('modJSMSportsHelper', false)) {
    final class modJSMSportsHelper
    {
        public static function getData(&$params): array
        {
            $registry = $params instanceof Registry ? $params : new Registry((array) $params);
            $container = Factory::getContainer();
            /** @var SiteApplication $app */
            $app = $container->get(SiteApplication::class);
            $data = (new SportsTypeStatisticsHelper())->getData($registry, $app);
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
