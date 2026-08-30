<?php
/** Legacy compatibility bridge for mod_sportsmanagement_eventsranking. */
\defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementEventsRanking\Site\Helper\EventsRankingHelper;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;

if (!class_exists(EventsRankingHelper::class)) {
    require_once __DIR__ . '/src/Helper/EventsRankingHelper.php';
}

if (!class_exists('modSMEventsrankingHelper', false)) {
    final class modSMEventsrankingHelper
    {
        /**
         * Preserve the legacy static helper contract for overrides which include helper.php directly.
         *
         * @return array{project:?object,ranking:array,eventtypes:array,teams:array}
         */
        public static function getData(&$params): array
        {
            $container = Factory::getContainer();
            /** @var SiteApplication $app */
            $app = $container->get(SiteApplication::class);
            /** @var DatabaseInterface $database */
            $database = $container->get(DatabaseInterface::class);
            $data = (new EventsRankingHelper())->getData($params, $app, $database);

            return [
                'project' => $data['project'] ?? null,
                'ranking' => $data['rankings'] ?? [],
                'eventtypes' => $data['eventtypes'] ?? [],
                'teams' => [],
            ];
        }

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

        public static function getTeamLink(object $team, $params = null, $project = null): string
        {
            return (string) ($team->team_url ?? '');
        }

        public static function printName(object $item, $team = null, $params = null, $project = null): void
        {
            $name = htmlspecialchars((string) ($item->display_name ?? ''), ENT_QUOTES, 'UTF-8');
            $url = (string) ($item->player_url ?? '');

            echo $url !== '' ? HTMLHelper::link($url, $name) : $name;
        }

        public static function getEventIcon(object $event): string
        {
            $name = Text::_((string) ($event->name ?? ''));
            $icon = (string) ($event->icon ?? '');

            if ($icon === '' || $icon === 'media/com_sportsmanagement/event_icons/event.gif') {
                return $name;
            }

            return HTMLHelper::_('image', $icon, $name, ['title' => $name, 'width' => 20]);
        }

        public static function getId($params, string $paramName): string
        {
            $value = (string) $params->get($paramName, '');

            if (preg_match('/^(\d+)(?::.*)?$/', $value, $matches)) {
                return $matches[1];
            }

            return $value;
        }
    }
}
