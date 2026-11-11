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
        // Optional external team links must not emit executable schemes even
        // when the formatted team label itself is properly HTML-escaped.
        foreach ([
            'javascript:alert(1)', 'data:text/html,payload',
            '//evil.example/team', 'https://user:password@host.example/team',
            "index.php\nunsafe=1", 'https://bad host.example/team',
            '%6aavascript%3aalert(1)', '%256aavascript%253aalert(1)',
            '%2f%2fevil.example/team', '%252f%252fevil.example/team',
            '%5c%5cevil.example/team', '%0aevil.example/team',
        ] as $unsafeLink) {
            $unlinked = $helper::formatName($team, 'team', [], false, null, 0, 0, $unsafeLink);

            if ($unlinked !== '<span style="padding:2px;">North &amp; South</span>') {
                throw new \RuntimeException('Unsafe optional team destination created a link.');
            }
        }

        $encodedSpaceLink = $helper::formatName(
            $team, 'team', [], false, null, 0, 0, '/teams/north%20city'
        );
        if (!str_contains($encodedSpaceLink, 'href="/teams/north%20city"')) {
            throw new \RuntimeException('Valid encoded-space team URL was rejected.');
        }

        $validLink = $helper::formatName(
            $team, 'team', [], false, null, 0, 0, 'index.php?view=teaminfo'
        );
        if (!str_contains($validLink, 'href="index.php?view=teaminfo"')) {
            throw new \RuntimeException('Valid Joomla team destination was not linked.');
        }

        // A non-empty array must not activate the team action dropdown
        // through PHP's historical boolean/integer coercion rules.
        $plain = $helper::formatName(
            $team,
            'team',
            ['show_info_link' => ['1'], 'show_team_link' => 1]
        );

        if ($plain !== '<span style="padding:2px;">North &amp; South</span>') {
            throw new \RuntimeException('Malformed action display setting unexpectedly enabled links.');
        }

        foreach ([['1'], new \stdClass(), 'true', 2] as $invalidToggle) {
            $disabled = $helper::formatName(
                $team,
                'team',
                ['show_info_link' => '1', 'show_team_link' => $invalidToggle]
            );

            if ($disabled !== '<span style="padding:2px;">North &amp; South</span>'
                || $helper::renderActions($team, ['show_team_link' => $invalidToggle]) !== '') {
                throw new \RuntimeException('Malformed navigation toggle enabled team actions.');
            }
        }

        // Both layout flags must be explicitly enabled. Nested values
        // must not change the action container into a compact span.
        foreach ([['1'], new \stdClass(), 'enabled'] as $invalidLayout) {
            $markup = $helper::formatName(
                $team,
                'team',
                [
                    'show_info_link' => 1,
                    'show_team_link' => 1,
                    'results_below' => $invalidLayout,
                    'show_logo_small' => 1,
                ]
            );

            if (!str_contains($markup, '<div id=') || str_contains($markup, '<span id=')) {
                throw new \RuntimeException('Malformed layout flag changed team action container.');
            }
        }

        $compactMarkup = $helper::formatName(
            $team,
            'team',
            [
                'show_info_link' => 1,
                'show_team_link' => 1,
                'results_below' => '1',
                'show_logo_small' => true,
            ]
        );

        if (!str_contains($compactMarkup, '<span id=')) {
            throw new \RuntimeException('Valid compact team action layout was lost.');
        }

        $validSetting = $helper::formatName(
            $team,
            'team',
            ['show_info_link' => '1', 'show_team_link' => 1]
        );

        if (!str_contains($validSetting, 'view=roster')) {
            throw new \RuntimeException('Valid Joomla action setting failed to display team links.');
        }

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

        // A non-empty PHP array becomes integer 1 if cast directly. No
        // malformed team/project/club ID may generate a frontend route.
        $team->club_id = ['invalid'];
        if ($helper::renderActions($team, ['show_club_link' => 1]) !== '') {
            throw new \RuntimeException('Malformed club array generated an invalid club route.');
        }

        $team->team_id = ['invalid'];
        if ($helper::renderActions($team, ['show_team_link' => 1]) !== '') {
            throw new \RuntimeException('Malformed team array generated an invalid team route.');
        }
        $team->team_id = 4;
        $team->project_id = ['invalid'];
        if ($helper::renderActions($team, ['show_team_link' => 1]) !== '') {
            throw new \RuntimeException('Malformed project ID generated an invalid team route.');
        }
        $team->project_id = 5;

        $team->club_id = '0009';
        $club = $helper::renderActions($team, ['show_club_link' => 1]);

        if (!str_contains($club, 'view=clubinfo') || !str_contains($club, 'cid=9')) {
            throw new \RuntimeException('Valid club actions disappeared.');
        }
    } finally {
        restore_error_handler();
    }

    echo "Joomla 5/6 team action slug guards passed.\n";
}
