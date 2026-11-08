<?php
/**
 * Legacy compatibility bridge for mod_sportsmanagement_eventsranking.
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
use Diddipoeler\Module\SportsManagementEventsRanking\Site\Helper\EventsRankingHelper;
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
    EventsRankingHelper::class => __DIR__ . '/src/Helper/EventsRankingHelper.php',
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
    EventsRankingHelper::class,
] as $requiredClass) {
    if (!class_exists($requiredClass)) {
        throw new \RuntimeException(
            'SportsManagement EventsRanking dependency could not be loaded: ' . $requiredClass,
            500
        );
    }
}

if (!class_exists('modSMEventsrankingHelper', false)) {
    final class modSMEventsrankingHelper
    {
        /**
         * Preserve the legacy static helper contract for overrides which include helper.php directly.
         *
         * @return array{project:?object,ranking:array,eventtypes:array,teams:array}
         */
        public static function getData(&$params, ?DatabaseInterface $database = null): array
        {
            $registry = $params instanceof Registry ? $params : new Registry((array) $params);
            $app = self::siteApplication();

            if (!$app->isClient('site')) {
                throw new \RuntimeException('SportsManagement EventsRanking legacy helper requires the Joomla site application.', 500);
            }

            $database = self::joomlaDatabase($database);

            $data = (new EventsRankingHelper())->getData($registry, $database);

            return [
                'project' => $data['project'] ?? null,
                'ranking' => $data['rankings'] ?? [],
                'eventtypes' => $data['eventtypes'] ?? [],
                'teams' => [],
            ];
        }

        /**
         * Resolve the active site application for legacy event ranking calls.
         * Lets the facade enforce site-only execution while keeping Joomla's application lookup in one shared service.
         *
         * @return CMSApplicationInterface Current Joomla site application.
         */
        private static function siteApplication(): CMSApplicationInterface
        {
            return SportsManagementSiteApplicationResolver::resolve();
        }

        /**
         * Obtain the database service used for event rankings.
         * Accepts an injected connection for tests or other supported databases; otherwise validates the Joomla container service.
         *
         * @param DatabaseInterface|null $database Optional database override.
         * @return DatabaseInterface Valid Joomla database connection.
         */
        private static function joomlaDatabase(?DatabaseInterface $database = null): DatabaseInterface
        {
            if ($database !== null) {
                return $database;
            }

            $database = Factory::getContainer()->get(DatabaseInterface::class);

            if (!$database instanceof DatabaseInterface) {
                throw new \RuntimeException('SportsManagement EventsRanking database service is unavailable.', 500);
            }

            return $database;
        }

        /**
         * Render a team or country logo supplied by the native event ranking model.
         * Selects the appropriate prepared URL according to the requested logo type and returns no markup when the image is absent.
         *
         * @param object $item Event ranking row containing image URLs.
         * @param int $type Logo selector; 2 chooses the country icon.
         * @return string Image HTML or an empty string.
         */
        public static function getLogo(object $item, int $type = 1): string
        {
            $url = $type === 2
                ? (string) ($item->country_logo_url ?? '')
                : (string) ($item->team_logo_url ?? '');

            if ($url === '') {
                return '';
            }

            return HTMLHelper::_('image', $url, '', ['width' => 20, 'class' => $type === 2 ? 'teamcountry' : 'teamlogo']);
        }

        /**
         * Read the prepared team link from a native event ranking row.
         * Maintains the legacy signature expected by existing module overrides; routing is performed by the native data provider.
         *
         * @param object $team Ranking team with an optional team_url property.
         * @param mixed $params Retained compatibility argument.
         * @param mixed $project Retained compatibility argument.
         * @return string Team URL or an empty string.
         */
        public static function getTeamLink(object $team, $params = null, $project = null): string
        {
            return (string) ($team->team_url ?? '');
        }

        /**
         * Write the safely escaped event participant name and its optional link.
         * Escapes the display text before output and wraps it in a Joomla link only when the native row supplies a URL.
         *
         * @param object $item Event participant row.
         * @param mixed $team Retained compatibility argument.
         * @param mixed $params Retained compatibility argument.
         * @param mixed $project Retained compatibility argument.
         * @return void
         */
        public static function printName(object $item, $team = null, $params = null, $project = null): void
        {
            $name = htmlspecialchars((string) ($item->display_name ?? ''), ENT_QUOTES, 'UTF-8');
            $url = (string) ($item->player_url ?? '');

            echo $url !== '' ? HTMLHelper::link($url, $name) : $name;
        }

        /**
         * Render an event icon or fall back to its translated label.
         * Uses Joomla HTML generation for a configured icon and displays text when the icon is empty or still points to the generic placeholder.
         *
         * @param object $event Event type carrying its label and optional icon.
         * @return string Icon HTML or translated text.
         */
        public static function getEventIcon(object $event): string
        {
            $name = Text::_((string) ($event->name ?? ''));
            $icon = (string) ($event->icon ?? '');

            if ($icon === '' || $icon === 'media/com_sportsmanagement/event_icons/event.gif') {
                return $name;
            }

            return HTMLHelper::_('image', $icon, $name, ['title' => $name, 'height' => 20]);
        }
    }
}
