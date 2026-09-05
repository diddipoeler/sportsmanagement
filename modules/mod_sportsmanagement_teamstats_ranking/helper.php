<?php
/**
 * Legacy compatibility bridge for the Joomla 5/6 Team Stats Ranking module.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Diddipoeler\Module\SportsManagementTeamStatsRanking\Site\Helper\TeamStatsRankingHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

if (!class_exists(TeamStatsRankingHelper::class)) {
    $nativeHelper = __DIR__ . '/src/Helper/TeamStatsRankingHelper.php';

    if (is_file($nativeHelper)) {
        require_once $nativeHelper;
    }
}

if (!class_exists(TeamStatsRankingHelper::class)) {
    throw new \RuntimeException('SportsManagement native Team Stats Ranking helper could not be loaded.', 500);
}

if (!class_exists('modSportsmanagementTeamStatHelper', false)) {
    final class modSportsmanagementTeamStatHelper
    {
        public static function getData(&$params): array
        {
            $registry = $params instanceof Registry ? $params : new Registry((array) $params);
            /** @var DatabaseInterface $joomlaDatabase */
            $joomlaDatabase = Factory::getContainer()->get(DatabaseInterface::class);

            return (new TeamStatsRankingHelper())->getData($registry, $joomlaDatabase);
        }

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

        public static function getTeamLink(object $item, Registry $params, object $project): string
        {
            $view = (string) $params->get('teamlink', '');
            if ($view === '') {
                return '';
            }

            $query = [
                'cfg_which_database' => (int) $params->get('cfg_which_database', 0),
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
