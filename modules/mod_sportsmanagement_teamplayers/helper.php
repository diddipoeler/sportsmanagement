<?php
/**
 * Legacy Joomla 5/6 helper bridge for third-party TeamPlayers template overrides.
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
use Diddipoeler\Module\SportsManagementTeamPlayers\Site\Helper\TeamPlayersHelper;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

$nativeDependencies = [
    SiteRouteHelper::class => JPATH_SITE . '/components/com_sportsmanagement/src/Helper/SiteRouteHelper.php',
    SportsManagementDatabaseResolver::class => JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementDatabaseResolver.php',
    SportsManagementSiteApplicationResolver::class => JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementSiteApplicationResolver.php',
    TeamPlayersHelper::class => __DIR__ . '/src/Helper/TeamPlayersHelper.php',
];

foreach ($nativeDependencies as $class => $file) {
    if (!class_exists($class, false) && is_file($file)) {
        require_once $file;
    }
}

foreach ([
    SiteRouteHelper::class,
    SportsManagementDatabaseResolver::class,
    SportsManagementSiteApplicationResolver::class,
    TeamPlayersHelper::class,
] as $requiredClass) {
    if (!class_exists($requiredClass)) {
        throw new \RuntimeException(
            'SportsManagement TeamPlayers dependency could not be loaded: ' . $requiredClass,
            500
        );
    }
}

if (!class_exists('modSportsmanagementTeamPlayersHelper', false)) {
    final class modSportsmanagementTeamPlayersHelper
    {
        public static function getData(&$params, ?DatabaseInterface $database = null): array
        {
            $registry = $params instanceof Registry ? $params : new Registry((array) $params);
            $app = SportsManagementSiteApplicationResolver::resolve();

            if (!$app->isClient('site')) {
                throw new \RuntimeException('SportsManagement TeamPlayers legacy helper requires the Joomla site application.', 500);
            }

            if ($database === null) {
                /** @var DatabaseInterface $database */
                $database = Factory::getContainer()->get(DatabaseInterface::class);
            }
            $data = (new TeamPlayersHelper())->getData($registry, $database);

            return ['project' => $data['project'], 'roster' => $data['roster']];
        }

        public static function getPlayerLink($item, $params, $project, $module): string
        {
            return self::renderPlayer($item);
        }

        public static function getPlayerLinkAndFlag($item, $params, $project, $module): array
        {
            return [
                'name' => self::renderPlayer($item),
                'flag' => (string) ($item->flag_html ?? ''),
            ];
        }

        public static function getPlayerMinsPlayed($item, $params, $project, $module, $time_for_match): int
        {
            return (int) ($item->minutes_played ?? 0);
        }

        private static function renderPlayer(object $item): string
        {
            $name = nl2br(htmlspecialchars((string) ($item->display_name ?? ''), ENT_QUOTES, 'UTF-8'));
            $url = trim((string) ($item->player_url ?? ''));

            if ($url !== '') {
                $name = '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '">' . $name . '</a>';
            }

            return (string) ($item->flag_html ?? '') . $name;
        }
    }
}
