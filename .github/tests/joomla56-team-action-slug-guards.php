<?php
/**
 * Joomla 5/6 team action link and corrupted project-team slug regressions.
 *
 * Runs in isolation without a Joomla installation.
 *
 * Usage: php .github/tests/joomla56-team-action-slug-guards.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\CMS\Language {
    final class Text
    {
        /**
         * Return the requested translation identifier in the test fixture.
         *
         * @param string $key Joomla language constant.
         * @return string Original translation identifier.
         */
        public static function _(string $key): string
        {
            return $key;
        }
    }
}

namespace Joomla\CMS\HTML {
    final class HTMLHelper
    {
        /**
         * Render a placeholder image for the team action list.
         *
         * @param string $src Image path in the component.
         * @param string $alt Accessible image text.
         * @param array<string,mixed> $attributes HTML image options.
         * @return string Fixture image HTML.
         */
        public static function image(string $src, string $alt, array $attributes = []): string
        {
            return '<img />';
        }

        /**
         * Render a simple escaped anchor to inspect route query parameters.
         *
         * @param string $url Joomla route URL.
         * @param string $text Inner icon or team markup.
         * @param array<string,mixed> $attributes Link options.
         * @return string Fixture anchor HTML.
         */
        public static function link(string $url, string $text, array $attributes = []): string
        {
            return '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '">' . $text . '</a>';
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Helper {
    final class SiteRouteHelper
    {
        /**
         * Turn a requested site view into a comparable test URL.
         *
         * @param string $view Target frontend view.
         * @param array<string,mixed> $params Routed component parameters.
         * @return string Simulated URL with encoded route parameters.
         */
        public static function view(string $view, array $params): string
        {
            return 'index.php?' . http_build_query(['view' => $view] + $params);
        }
    }
}

namespace {
    \defined('_JEXEC') || define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/site/src/Helper/TeamPresentationHelper.php';

    $helper = \Diddipoeler\Component\SportsManagement\Site\Helper\TeamPresentationHelper::class;
    $team = (object) [
        'team_id' => 4,
        'project_id' => 5,
        'projectteam_slug' => ['broken'],
        'projectteamid' => '88:valid',
        'name' => 'North & South',
    ];

    set_error_handler(static function (int $severity, string $message): never {
        throw new \ErrorException($message, 0, $severity);
    });

    try {
        $links = $helper::renderActions($team, ['show_team_link' => 1]);

        if (!str_contains($links, 'view=roster')
            || !str_contains($links, 'ptid=88%3Avalid')
            || str_contains($links, 'Array')) {
            throw new \RuntimeException('Malformed project-team slug did not use the valid fallback.');
        }

        $team->projectteamid = ['also broken'];
        $links = $helper::renderActions($team, ['show_team_link' => 1]);

        if (!str_contains($links, 'ptid=') || str_contains($links, 'Array')) {
            throw new \RuntimeException('Invalid alternative project-team ID leaked into the route.');
        }

        if ($helper::renderActions($team, ['show_club_link' => 1]) !== '') {
            throw new \RuntimeException('Missing club ID generated a club action link.');
        }

        $team->club_id = 9;
        $club = $helper::renderActions($team, ['show_club_link' => 1]);

        if (!str_contains($club, 'view=clubinfo') || !str_contains($club, 'cid=9')) {
            throw new \RuntimeException('Valid club actions disappeared.');
        }
    } finally {
        restore_error_handler();
    }

    echo "Joomla 5/6 team action slug guards passed.\n";
}
