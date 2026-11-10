<?php
/**
 * Joomla 5/6 native matrix rendering and safe score regressions.
 *
 * Exercises the real matrix layout with fake team, result and division data.
 * Requires no database connection or Joomla application bootstrap.
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
         * Preserve translation keys for deterministic native template checks.
         *
         * @param string $key Joomla language key.
         * @return string Untranslated key.
         */
        public static function _(string $key): string { return $key; }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Helper {
    final class SiteRouteHelper
    {
        /**
         * Provide a deterministic Joomla route to test encoded link output.
         *
         * @param string $view Current frontend target.
         * @param array<string,mixed> $params Project and team selectors.
         * @return string URL with a deliberately escapable query ampersand.
         */
        public static function view(string $view, array $params = []): string
        {
            return '/index.php?view=' . urlencode($view) . '&p=' . urlencode((string) ($params['p'] ?? ''));
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/site/src/Helper/MatrixPresentationHelper.php';

    $helper = \Diddipoeler\Component\SportsManagement\Site\Helper\MatrixPresentationHelper::class;
    $teams = [
        (object) ['projectteamid' => 11, 'name' => '<script>alert(1)</script>', 'team_slug' => '11:malicious'],
        (object) ['projectteamid' => '12', 'name' => 'FC Köln & Freunde', 'team_slug' => '12:fc-koeln'],
        (object) ['projectteamid' => 11, 'name' => 'Duplicate'],
        (object) ['projectteamid' => [], 'name' => 'Invalid'],
    ];
    $matches = [
        (object) ['projectteam1_id' => 11, 'projectteam2_id' => 12, 'e1' => '02', 'e2' => '0'],
        (object) ['projectteam1_id' => 11, 'projectteam2_id' => 12, 'e1' => '<img onerror=alert(2)>', 'e2' => 3],
        (object) ['projectteam1_id' => 12, 'projectteam2_id' => 11, 'decision' => 1, 'v1' => 4, 'v2' => '3'],
        (object) ['projectteam1_id' => 12, 'projectteam2_id' => 11, 'e1' => 7, 'e2' => 6, 'cancel' => 1],
        (object) ['projectteam1_id' => 11, 'projectteam2_id' => 99, 'e1' => 1, 'e2' => 9],
        [],
    ];

    set_error_handler(static function (int $severity, string $message): never {
        throw new \ErrorException($message, 0, $severity);
    });

    try {
        $normalized = $helper::teams($teams);
        if (count($normalized) !== 2 || $normalized[11]['name'] !== '<script>alert(1)</script>') {
            throw new \RuntimeException('Matrix team normalization dropped or duplicated IDs.');
        }

        $cells = $helper::results($matches, $normalized);
        if ($cells[11][12] !== ['2:0', '–:3'] || $cells[12][11] !== ['4:3', '–']) {
            throw new \RuntimeException('Matrix scores or decision/cancelled matches were changed.');
        }

        $fixture = new class {
            public ?object $project = null;
            public array $config = ['show_matrix' => true, 'link_teams' => true, 'show_help' => true];
            public array $teams = [];
            public array $results = [];
            public array $divisions = [];
            public int $divisionid = 0;
            public int $databaseSelector = 1;
            public string $divclasscontainer = 'container-fluid';

            /**
             * Render the actual Joomla matrix template as a view-local include.
             *
             * @param string $file Absolute path to the native matrix layout.
             * @return string Captured HTML page fragment for assertions.
             */
            public function render(string $file): string
            {
                ob_start();
                try {
                    include $file;
                    return (string) ob_get_clean();
                } catch (\Throwable $error) {
                    ob_end_clean();
                    throw $error;
                }
            }
        };

        $fixture->project = (object) [
            'id' => 42,
            'name' => '<script>alert("project")</script>',
        ];
        $fixture->teams = $teams;
        $fixture->results = $matches;
        $path = dirname(__DIR__, 2) . '/site/tmpl/matrix/default.php';
        $html = $fixture->render($path);

        if (!str_contains($html, '&lt;script&gt;alert(1)&lt;/script&gt;')
            || !str_contains($html, '&lt;script&gt;alert(&quot;project&quot;)&lt;/script&gt;')
            || str_contains($html, '<script>')
            || str_contains($html, '<img onerror')
            || !str_contains($html, '2:0 / –:3')
            || !str_contains($html, '4:3 / –')
            || !str_contains($html, 'view=roster&amp;p=42')
            || !str_contains($html, 'COM_SPORTSMANAGEMENT_MATRIX_HINT')) {
            throw new \RuntimeException('Native matrix failed HTML escaping, scores, links or help rendering.');
        }

        $fixture->config['show_matrix'] = false;
        if (str_contains($fixture->render($path), '<table')) {
            throw new \RuntimeException('Disabled matrix display ignored saved Joomla settings.');
        }

        $fixture->config['show_matrix'] = true;
        $fixture->divisionid = 0;
        $fixture->divisions = [
            (object) ['id' => 5, 'name' => '<svg onload=alert(3)>'],
            (object) ['id' => 6, 'name' => 'Second division'],
        ];
        $fixture->teams = [
            (object) ['projectteamid' => 11, 'name' => 'Team One', 'division_id' => 5],
            (object) ['projectteamid' => 12, 'name' => 'Team Two', 'division_id' => 6],
        ];
        $divisionHtml = $fixture->render($path);

        if (!str_contains($divisionHtml, '&lt;svg onload=alert(3)&gt;')
            || str_contains($divisionHtml, '<svg')
            || str_contains($divisionHtml, '2:0')
            || substr_count($divisionHtml, '<table') !== 2) {
            throw new \RuntimeException('Matrix division rendering leaked markup or cross-division results.');
        }
    } finally {
        restore_error_handler();
    }

    echo "Joomla 5/6 native matrix score, name and division rendering OK\n";
}
