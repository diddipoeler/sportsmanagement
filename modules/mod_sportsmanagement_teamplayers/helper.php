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
use Joomla\CMS\Application\CMSApplicationInterface;
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
        /**
         * Load a team's project and roster data for legacy module layouts.
         *
         * Converts module options into Joomla Registry, verifies that a
         * public site application and valid database are available and
         * delegates the query to the native TeamPlayersHelper.
         *
         * @param Registry|array $params Module settings passed by reference.
         * @param DatabaseInterface|null $database Optional database override.
         * @return array Project and roster records for the template.
         * @throws \RuntimeException If the site or database is unavailable.
         */
        public static function getData(&$params, ?DatabaseInterface $database = null): array
        {
            $registry = $params instanceof Registry ? $params : new Registry((array) $params);
            $app = self::siteApplication();
            $database = self::joomlaDatabase($database);
            $data = (new TeamPlayersHelper())->getData($registry, $database);

            return ['project' => $data['project'], 'roster' => $data['roster']];
        }

        /**
         * Render the legacy player name and profile link.
         *
         * Uses renderPlayer() to escape the display name and profile URL
         * while keeping the historical module template method signature.
         *
         * @param object $item Player row containing display and route data.
         * @param mixed $params Legacy module settings.
         * @param mixed $project Current project context.
         * @param mixed $module Joomla module record.
         * @return string Player name and optional profile link HTML.
         */
        public static function getPlayerLink($item, $params, $project, $module): string
        {
            return self::renderPlayer($item);
        }

        /**
         * Return player display markup together with the country flag.
         *
         * Preserves the name and flag keys expected by existing third-party
         * template overrides without duplicating player link formatting.
         *
         * @param object $item Player record with optional flag markup.
         * @param mixed $params Legacy module configuration.
         * @param mixed $project Current project.
         * @param mixed $module Joomla module record.
         * @return array{name:string,flag:string} Player markup and flag.
         */
        public static function getPlayerLinkAndFlag($item, $params, $project, $module): array
        {
            return [
                'name' => self::renderPlayer($item),
                'flag' => (string) ($item->flag_html ?? ''),
            ];
        }

        /**
         * Return the recorded playing minutes from a roster row.
         *
         * Casts the native helper's minutes_played field to an integer
         * while retaining the legacy five-argument module API.
         *
         * @param object $item Player record containing minutes_played.
         * @param mixed $params Legacy module settings.
         * @param mixed $project Selected project.
         * @param mixed $module Joomla module record.
         * @param mixed $time_for_match Legacy match duration option.
         * @return int Recorded playing minutes, or zero if unspecified.
         */
        public static function getPlayerMinsPlayed($item, $params, $project, $module, $time_for_match): int
        {
            return (int) ($item->minutes_played ?? 0);
        }

        /**
         * Resolve and validate the Joomla frontend application.
         *
         * Uses the component's native site application resolver and
         * rejects administrator clients before reading roster data.
         *
         * @return CMSApplicationInterface Active Joomla site application.
         * @throws \RuntimeException For a non-site application.
         */
        private static function siteApplication(): CMSApplicationInterface
        {
            $app = SportsManagementSiteApplicationResolver::resolve();

            if (!$app->isClient('site')) {
                throw new \RuntimeException('SportsManagement TeamPlayers legacy helper requires the Joomla site application.', 500);
            }

            return $app;
        }

        /**
         * Obtain the effective Joomla database service for roster queries.
         *
         * Accepts a caller-injected database or retrieves the service from
         * Joomla's dependency container, rejecting unexpected instances.
         *
         * @param DatabaseInterface|null $database Optional database override.
         * @return DatabaseInterface Valid Joomla database connection.
         * @throws \RuntimeException If the service cannot be resolved.
         */
        private static function joomlaDatabase(?DatabaseInterface $database = null): DatabaseInterface
        {
            if ($database !== null) {
                return $database;
            }

            $database = Factory::getContainer()->get(DatabaseInterface::class);

            if (!$database instanceof DatabaseInterface) {
                throw new \RuntimeException('SportsManagement TeamPlayers database service is unavailable.', 500);
            }

            return $database;
        }

        /**
         * Build escaped HTML for a player's profile link and flag.
         *
         * Escapes the display name and URL for their HTML contexts,
         * converts line breaks for display and prefixes optional
         * flag markup produced by the native player data helper.
         *
         * @param object $item Player row with display_name and player_url.
         * @return string Legacy player link and flag HTML.
         */
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
