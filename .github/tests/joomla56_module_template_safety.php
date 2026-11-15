<?php
/**
 * Regression checks for native Joomla 5/6 project and team module layouts.
 *
 * Usage: php .github/tests/joomla56_module_template_safety.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\CMS\HTML {
    final class HTMLHelper
    {
        /**
         * Simulate Joomla form-token output for safe module layout checks.
         * @param string $method Called Joomla helper identifier.
         * @return string Stable HTML token fixture.
         */
        public static function _(string $method): string
        {
            return $method === 'form.token' ? '<input name="token" value="valid">' : '';
        }
    }
}

namespace Joomla\CMS\Language {
    final class Text
    {
        /**
         * Return the fixture translation key for template output assertions.
         * @param string $key Requested Joomla language key.
         * @return string Stable text used by module templates.
         */
        public static function _(string $key): string
        {
            return $key;
        }

        /**
         * Format a translated fixture count without an installed language pack.
         * @param string $key Translation key for the current count.
         * @param mixed $value Value shown in the fixture.
         * @return string Stable formatted template label.
         */
        public static function sprintf(string $key, mixed $value): string
        {
            return $key . ': ' . (string) $value;
        }
    }
}

namespace {
    \define('_JEXEC', 1);

    /**
     * Supply intentionally malformed settings to native Joomla module layouts.
     *
     * The fixtures model Registry::get() without requiring Joomla, making it
     * possible to assert PHP 8 safety around historical array-valued options.
     */
    final class TemplateFixtureParams
    {
        /**
         * Store the settings returned to the module templates.
         * @param array<string,mixed> $options Simulated module configuration.
         */
        public function __construct(private array $options)
        {
        }

        /**
         * Return a configured setting or the caller's default value.
         * @param string $key Module configuration key.
         * @param mixed $default Default value for missing settings.
         * @return mixed Selected setting.
         */
        public function get(string $key, mixed $default = null): mixed
        {
            return $this->options[$key] ?? $default;
        }
    }

    /**
     * Execute a native module template with provided fixture variables.
     *
     * Captures output and restores buffering on exceptions so warnings and
     * errors from malformed legacy values become test failures.
     *
     * @param string $layout Absolute path to a Joomla template.
     * @param array<string,mixed> $variables Variables normally supplied by a dispatcher.
     * @return string Rendered module HTML.
     */
    function renderModuleFixture(string $layout, array $variables): string
    {
        extract($variables, EXTR_SKIP);
        ob_start();

        try {
            require $layout;
            return (string) ob_get_clean();
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }
    }

    /**
     * Check that untrusted template fields do not become executable HTML.
     * @param bool $condition Required safe rendering invariant.
     * @param string $message Failure description for the CI log.
     * @return void
     */
    function assertTemplateSafety(bool $condition, string $message): void
    {
        if (!$condition) {
            throw new \RuntimeException($message);
        }
    }

    set_error_handler(static function (int $severity, string $message): never {
        throw new \ErrorException($message, 0, $severity);
    });

    $root = dirname(__DIR__, 2);
    $module = (object) ['module' => 'mod_sportsmanagement_clubicons', 'id' => 17];

    $newProject = renderModuleFixture(
        $root . '/modules/mod_sportsmanagement_new_project/tmpl/native.php',
        [
            'params' => new TemplateFixtureParams(['moduleclass_sfx' => ['invalid']]),
            'module' => $module,
            'canCreateArticles' => false,
            'list' => [
                null,
                (object) [
                    'flag_url' => 'data:image/svg+xml,unsafe',
                    'project_url' => 'javascript:alert(1)',
                    'name' => '<img src=x onerror=alert(1)>',
                    'league_name' => '<b>Unsafe</b>',
                    'country' => ['DE'],
                ],
                (object) [
                    'flag_url' => 'https://example.org/flag.svg',
                    'project_url' => 'index.php?option=com_sportsmanagement&view=results',
                    'name' => 'Safe project',
                    'league_name' => 'Bundesliga',
                    'country' => 'DE',
                ],
            ],
        ]
    );

    assertTemplateSafety(
        !str_contains($newProject, 'javascript:alert')
        && !str_contains($newProject, 'data:image/')
        && !str_contains($newProject, '<img src=x')
        && str_contains($newProject, '&lt;img src=x onerror=alert(1)&gt;')
        && str_contains($newProject, 'index.php?option=com_sportsmanagement&amp;view=results')
        && str_contains($newProject, 'https://example.org/flag.svg'),
        'New Project layout exposed malformed links or did not escape legacy title fields.'
    );

    $teamPlayers = renderModuleFixture(
        $root . '/modules/mod_sportsmanagement_teamplayers/tmpl/native.php',
        [
            'params' => new TemplateFixtureParams(['moduleclass_sfx' => [], 'template' => 'C']),
            'module' => $module,
            'project' => (object) ['name' => 'Project'],
            'players' => [
                (object) [
                    'flag_html' => '',
                    'display_name' => '<script>alert(1)</script>',
                    'player_url' => 'javascript:alert(1)',
                    'image_url' => 'data:image/svg+xml,bad',
                    'position' => '',
                    'minutes_played' => 12,
                ],
                (object) [
                    'flag_html' => '',
                    'display_name' => 'Valid player',
                    'player_url' => 'https://example.org/player',
                    'image_url' => 'images/player.png',
                    'position' => '',
                    'minutes_played' => 0,
                ],
            ],
        ]
    );

    assertTemplateSafety(
        !str_contains($teamPlayers, '<script>alert(1)</script>')
        && str_contains($teamPlayers, '&lt;script&gt;alert(1)&lt;/script&gt;')
        && !str_contains($teamPlayers, 'javascript:alert')
        && !str_contains($teamPlayers, 'data:image/svg+xml')
        && str_contains($teamPlayers, 'https://example.org/player')
        && str_contains($teamPlayers, 'images/player.png'),
        'TeamPlayers layout did not safely render legacy player names or URLs.'
    );

    // Exercise the separate table layout too: links must remain URL-validated
    // even though this view deliberately does not render roster photographs.
    $teamPlayersTable = renderModuleFixture(
        $root . '/modules/mod_sportsmanagement_teamplayers/tmpl/native.php',
        [
            'params' => new TemplateFixtureParams(['template' => 'L']),
            'module' => $module,
            'project' => (object) ['name' => 'Project'],
            'players' => [
                (object) [
                    'flag_html' => '',
                    'display_name' => '<b>Unsafe player</b>',
                    'player_url' => 'javascript:alert(1)',
                    'position' => '',
                    'minutes_played' => 0,
                ],
                (object) [
                    'flag_html' => '',
                    'display_name' => 'Normal player',
                    'player_url' => 'index.php?option=com_sportsmanagement&view=person',
                    'position' => '',
                    'minutes_played' => 4,
                ],
            ],
        ]
    );

    assertTemplateSafety(
        !str_contains($teamPlayersTable, 'javascript:alert')
        && !str_contains($teamPlayersTable, '<b>Unsafe player</b>')
        && str_contains($teamPlayersTable, '&lt;b&gt;Unsafe player&lt;/b&gt;')
        && str_contains($teamPlayersTable, 'index.php?option=com_sportsmanagement&amp;view=person'),
        'TeamPlayers table layout exposed unsafe links or unescaped player text.'
    );

    $clubIcons = renderModuleFixture(
        $root . '/modules/mod_sportsmanagement_clubicons/tmpl/default.php',
        [
            'params' => new TemplateFixtureParams([
                'moduleclass_sfx' => new \stdClass(),
                'table_class' => ['invalid'],
                'iconpos' => ['invalid'],
                'iconsperrow' => 2,
            ]),
            'module' => $module,
            'ranking' => [
                (object) ['projectteamid' => 101],
                (object) ['projectteamid' => 102],
                (object) ['projectteamid' => 103],
            ],
            'teams' => [
                101 => ['name' => '<img src=x>', 'link' => 'javascript:alert(1)', 'logo_url' => 'data:image/svg+xml,unsafe'],
                102 => ['name' => 'Safe club', 'link' => 'https://example.org/club', 'logo_url' => 'images/club.png'],
                103 => 'bad team data',
            ],
        ]
    );

    assertTemplateSafety(
        !str_contains($clubIcons, 'javascript:alert')
        && !str_contains($clubIcons, 'data:image/')
        && !str_contains($clubIcons, '<img src=x>')
        && str_contains($clubIcons, 'https://example.org/club')
        && str_contains($clubIcons, 'images/club.png'),
        'Clubicons layout emitted unsafe links or used malformed team data.'
    );

    restore_error_handler();
    echo "Joomla 5/6 native module template safety regressions passed.\n";
}
