<?php
/**
 * Joomla 5/6 RandomPlayer template URL and HTML escaping regressions.
 *
 * Usage: php .github/tests/joomla56_randomplayer_template_urls.php
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
         * Return the raw translation key for isolated template assertions.
         *
         * @param string $key Joomla module translation identifier.
         * @return string Translation key used by the standalone test.
         */
        public static function _(string $key): string
        {
            return $key;
        }
    }
}

namespace {
    \define('_JEXEC', 1);

    /**
     * Mimic Joomla Registry values without loading a complete CMS install.
     */
    final class RandomPlayerTemplateParameters
    {
        /**
         * Store a fixture parameter map.
         * @param array<string,mixed> $values Module options.
         */
        public function __construct(private array $values)
        {
        }

        /**
         * Read one module parameter with its configured fallback.
         * @param string $key Option identifier.
         * @param mixed $default Fallback value.
         * @return mixed Configured module setting.
         */
        public function get(string $key, mixed $default = null): mixed
        {
            return $this->values[$key] ?? $default;
        }
    }

    /**
     * Render the native RandomPlayer template with isolated fixture values.
     *
     * @param array<string,mixed> $values Module layout context.
     * @return string Captured HTML rendering.
     */
    function renderRandomPlayerFixture(array $values): string
    {
        extract($values, EXTR_SKIP);
        ob_start();

        try {
            require dirname(__DIR__, 2) . '/modules/mod_sportsmanagement_randomplayer/tmpl/native.php';
            return (string) ob_get_clean();
        } catch (\Throwable $error) {
            ob_end_clean();
            throw $error;
        }
    }

    /**
     * Fail a regression when a dangerous URL or raw HTML reaches the DOM.
     *
     * @param bool $condition Template safety assertion.
     * @param string $error Failure description.
     * @return void
     */
    function requireRandomPlayerSafety(bool $condition, string $error): void
    {
        if (!$condition) {
            throw new \RuntimeException($error);
        }
    }

    set_error_handler(static function (int $level, string $message): never {
        throw new \ErrorException($message, 0, $level);
    });

    $row = (object) [
        'display_name' => '<img src=x onerror=alert(1)>',
        'player_url' => 'javascript:alert(1)',
        'team_url' => 'data:text/html,unsafe',
        'picture_url' => 'data:image/svg+xml,unsafe',
        'team_picture_url' => '//malicious.example/logo.svg',
        'flag_url' => 'ftp://malicious.example/flag.png',
        'project_name' => '<script>alert(1)</script>',
        'team_name' => 'Safe Club',
        'position_name' => '<b>Forward</b>',
    ];

    foreach (['S', 'L'] as $mode) {
        $html = renderRandomPlayerFixture([
            'list' => ['player' => $row],
            'params' => new RandomPlayerTemplateParameters([
                'mode' => $mode,
                'show_project_name' => true,
                'show_team_name' => true,
                'show_player_link' => true,
                'show_team_link' => true,
                'show_position_name' => true,
                'show_player_flag' => true,
            ]),
        ]);

        requireRandomPlayerSafety(
            !str_contains($html, 'javascript:alert')
                && !str_contains($html, 'data:text/html')
                && !str_contains($html, 'data:image/')
                && !str_contains($html, '//malicious.example')
                && !str_contains($html, 'ftp://malicious')
                && !str_contains($html, '<img src=x')
                && !str_contains($html, '<script>alert')
                && str_contains($html, '&lt;img src=x onerror=alert(1)&gt;'),
            'RandomPlayer ' . $mode . ' layout exposed untrusted links or unescaped player data.'
        );
    }

    $valid = clone $row;
    $valid->display_name = 'Player';
    $valid->player_url = 'index.php?option=com_sportsmanagement&view=player';
    $valid->team_url = 'https://example.org/club';
    $valid->picture_url = 'images/person.png';
    $valid->flag_url = 'https://example.org/flag.png';
    $valid->team_picture_url = 'images/club.png';

    foreach (['S', 'L'] as $mode) {
        $html = renderRandomPlayerFixture([
            'list' => ['player' => $valid],
            'params' => new RandomPlayerTemplateParameters(['mode' => $mode]),
        ]);

        requireRandomPlayerSafety(
            str_contains($html, 'index.php?option=com_sportsmanagement&amp;view=player')
                && str_contains($html, 'https://example.org/club')
                && str_contains($html, 'images/person.png')
                && str_contains($html, 'https://example.org/flag.png'),
            'RandomPlayer ' . $mode . ' layout rejected valid internal or HTTPS URLs.'
        );
    }

    $none = renderRandomPlayerFixture([
        'list' => [],
        'params' => new RandomPlayerTemplateParameters([]),
    ]);

    requireRandomPlayerSafety(str_contains($none, 'NO ITEMS'), 'RandomPlayer missing-data fallback vanished.');
    restore_error_handler();

    echo "Joomla 5/6 RandomPlayer template URL and HTML safety regressions passed.\n";
}
