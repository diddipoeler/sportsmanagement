<?php
/**
 * Legacy compatibility bridge for the Joomla 5/6 Team Stats Ranking module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Diddipoeler\Module\SportsManagementTeamStatsRanking\Site\Helper\TeamStatsRankingHelper;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

$nativeDependencies = [
    SiteRouteHelper::class => JPATH_SITE . '/components/com_sportsmanagement/src/Helper/SiteRouteHelper.php',
    SportsManagementDatabaseResolver::class => JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementDatabaseResolver.php',
    SportsManagementSiteApplicationResolver::class => JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementSiteApplicationResolver.php',
    TeamStatsRankingHelper::class => __DIR__ . '/src/Helper/TeamStatsRankingHelper.php',
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
    TeamStatsRankingHelper::class,
] as $requiredClass) {
    if (!class_exists($requiredClass)) {
        throw new \RuntimeException(
            'SportsManagement Team Stats Ranking dependency could not be loaded: ' . $requiredClass,
            500
        );
    }
}

if (!class_exists('modSportsmanagementTeamStatHelper', false)) {
    final class modSportsmanagementTeamStatHelper
    {
        /**
         * Retrieve the team statistics ranking through the native module helper.
         * Converts legacy parameters to Registry, validates the site application and passes the selected database connection to the namespaced service.
         *
         * @param Registry|array $params Module configuration passed by reference.
         * @param DatabaseInterface|null $database Optional database override.
         * @return array Ranking rows and accompanying display data.
         */
        public static function getData(&$params, ?DatabaseInterface $database = null): array
        {
            $registry = $params instanceof Registry ? $params : new Registry((array) $params);
            self::siteApplication();
            $database = self::joomlaDatabase($database);

            return (new TeamStatsRankingHelper())->getData($registry, $database);
        }

        /**
         * Resolve the current Joomla site application for team statistics.
         * Rejects administrator execution so the legacy facade matches the native frontend module lifecycle.
         *
         * @return CMSApplicationInterface Active site application.
         */
        private static function siteApplication(): CMSApplicationInterface
        {
            $app = SportsManagementSiteApplicationResolver::resolve();

            if (!$app->isClient('site')) {
                throw new \RuntimeException('SportsManagement Team Stats Ranking legacy helper requires the Joomla site application.', 500);
            }

            return $app;
        }

        /**
         * Resolve the database dependency for team statistics rankings.
         * Uses an injected database when supplied and otherwise validates the service retrieved from Joomla's container.
         *
         * @param DatabaseInterface|null $database Optional database override.
         * @return DatabaseInterface Valid Joomla database driver.
         */
        private static function joomlaDatabase(?DatabaseInterface $database = null): DatabaseInterface
        {
            if ($database !== null) {
                return $database;
            }

            $database = Factory::getContainer()->get(DatabaseInterface::class);

            if (!$database instanceof DatabaseInterface) {
                throw new \RuntimeException('SportsManagement Team Stats Ranking database service is unavailable.', 500);
            }

            return $database;
        }

        /**
         * Build team logo markup or a textual country badge for a ranking row.
         * Respects the historical type selector, escapes the country code and returns no markup when the requested image is unavailable.
         *
         * @param object $item Team statistics ranking row.
         * @param int $type 1 for team image or 2 for country code.
         * @return string Safe HTML for the image or badge.
         */
        public static function getLogo(object $item, int $type = 1): string
        {
            if ($type === 1 && !empty($item->logo_big)) {
                return HTMLHelper::_(
                    'image',
                    (string) $item->logo_big,
                    (string) ($item->short_name ?? $item->name ?? ''),
                    ['class' => 'jsm-teamstats-logo', 'loading' => 'lazy']
                );
            }

            if ($type === 2 && !empty($item->country)) {
                $country = htmlspecialchars(strtoupper((string) $item->country), ENT_QUOTES, 'UTF-8');

                return '<span class="badge text-bg-light">' . $country . '</span>';
            }

            return '';
        }

        /**
         * Construct a route for a team, roster, plan or club details page.
         * Normalises legacy module settings, builds the season and project route parameters and rejects unsupported link types.
         *
         * @param object $item Team statistics ranking row.
         * @param Registry|array $params Module link configuration.
         * @param object $project Current project and season identifiers.
         * @return string Joomla route URL, or an empty string if disabled.
         */
        public static function getTeamLink(object $item, $params, object $project): string
        {
            $registry = $params instanceof Registry ? $params : new Registry((array) $params);
            $view = (string) $registry->get('teamlink', '');
            if ($view === '') {
                return '';
            }

            $query = [
                'cfg_which_database' => (int) $registry->get('cfg_which_database', 0),
                's' => (string) ($project->season_slug ?? $project->season_id ?? ''),
                'p' => (string) ($project->slug ?? $project->id ?? ''),
            ];

            if ($view === 'clubinfo') {
                $query['cid'] = (string) ($item->club_slug ?? $item->club_id ?? 0);
            } elseif (in_array($view, ['teaminfo', 'roster', 'teamplan'], true)) {
                $query['tid'] = (string) ($item->team_slug ?? $item->id ?? 0);
                $query['ptid'] = 0;

                if ($view !== 'teaminfo') {
                    $query['division'] = 0;
                }

                if ($view === 'teamplan') {
                    $query['mode'] = 0;
                }
            } else {
                return '';
            }

            return SiteRouteHelper::view($view, $query);
        }

        /**
         * Render the icon associated with a team statistic.
         * Generates an image with its translated title for custom icons, otherwise returns an escaped translated statistic name.
         *
         * @param object $stat Statistic definition including optional icon and name.
         * @return string Image markup or escaped statistic label.
         */
        public static function getStatIcon(object $stat): string
        {
            if (!empty($stat->icon) && $stat->icon !== 'media/com_sportsmanagement/event_icons/event.gif') {
                $title = Text::_((string) ($stat->name ?? ''));

                return HTMLHelper::_(
                    'image',
                    (string) $stat->icon,
                    $title,
                    ['title' => $title, 'class' => 'jsm-teamstats-stat-icon', 'loading' => 'lazy']
                );
            }

            return htmlspecialchars(Text::_((string) ($stat->name ?? '')), ENT_QUOTES, 'UTF-8');
        }
    }
}
