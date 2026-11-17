<?php
/**
 * Standalone regressions for Joomla 5/6 event, prediction and ranking bridges.
 *
 * Usage: php .github/tests/joomla56_event_prediction_ranking_compat.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\CMS\Component {
    final class ComponentHelper
    {
        /**
         * Provide a minimal component configuration to test legacy menu fallbacks.
         *
         * @param string $component Joomla component name.
         * @return object Test registry exposing the default menu option.
         */
        public static function getParams(string $component): object
        {
            return new class {
                /**
                 * Return the configured default Itemid to the legacy route builder.
                 *
                 * @param string $name Requested component parameter.
                 * @param mixed $default Fallback for an unknown parameter.
                 * @return mixed Configured test menu ID or the fallback.
                 */
                public function get(string $name, mixed $default = null): mixed
                {
                    return $name === 'default_itemid' ? '7' : $default;
                }
            };
        }
    }
}

namespace Joomla\CMS\Uri {
    final class Uri
    {
        /**
         * Emulate Joomla query building without bootstrapping a site instance.
         *
         * @param array<string,mixed> $parts Query arguments.
         * @return string URL-encoded query string.
         */
        public static function buildQuery(array $parts): string
        {
            return http_build_query($parts);
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Helper {
    final class SiteRouteHelper
    {
        /**
         * Retain view and query parameters so routed fragments can be asserted.
         *
         * @param string $view Frontend target view.
         * @param array<string,mixed> $parameters Named route arguments.
         * @return string Simulated frontend component URL.
         */
        public static function view(string $view, array $parameters = []): string
        {
            return 'index.php?view=' . $view . '&' . http_build_query($parameters);
        }
    }
}

namespace Joomla\CMS\Language {
    final class Text
    {
        /**
         * Return the supplied translation key for module HTML assertions.
         *
         * @param string $key Joomla language key.
         * @return string Deterministic fixture translation.
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
     * Check a Joomla 5/6 compatibility invariant and fail with a useful error.
     *
     * @param bool $condition Whether the expected behaviour was observed.
     * @param string $description Context for a failed regression.
     * @return void
     */
    function assertMigrationCompatibility(bool $condition, string $description): void
    {
        if (!$condition) {
            throw new \RuntimeException($description);
        }
    }

    /**
     * Simulate Joomla module parameters without depending on Joomla itself.
     */
    final class RankingModuleParamsFixture
    {
        /**
         * Save module configuration for HTML layout rendering.
         *
         * @param array<string,mixed> $settings Simulated module options.
         */
        public function __construct(private array $settings)
        {
        }

        /**
         * Return an option or its default as Joomla's Registry would do.
         *
         * @param string $name Name of the requested module option.
         * @param mixed $default Fallback when no option exists.
         * @return mixed Test option or default.
         */
        public function get(string $name, mixed $default = null): mixed
        {
            return $this->settings[$name] ?? $default;
        }
    }

    /**
     * Execute the native event ranking layout with fixture variables.
     *
     * Restores the output buffer when rendering fails so warnings do not
     * silently pass as valid HTML in the regression test.
     *
     * @param array<string,mixed> $rankingData Simulated event ranking data.
     * @param RankingModuleParamsFixture $params Module presentation settings.
     * @return string Rendered Joomla module HTML.
     */
    function renderRankingFixture(array $rankingData, RankingModuleParamsFixture $params): string
    {
        $file = dirname(__DIR__, 2) . '/modules/mod_sportsmanagement_eventsranking/tmpl/default.php';
        ob_start();

        try {
            require $file;
            return (string) ob_get_clean();
        } catch (\Throwable $exception) {
            ob_end_clean();
            throw $exception;
        }
    }

    // Treat notices, warnings and deprecations as failures on all supported
    // PHP versions, including unsupported old-style static method calls.
    set_error_handler(
        /**
         * Convert all runtime warnings to actionable regression failures.
         *
         * @param int $severity Native PHP error severity.
         * @param string $message Diagnostic message.
         * @return never Always throws an exception.
         */
        static function (int $severity, string $message): never {
            throw new \ErrorException($message, 0, $severity);
        }
    );

    $root = dirname(__DIR__, 2);
    require_once $root . '/admin/helpers/Event.php';
    require_once $root . '/site/helpers/predictionroute.php';

    $event = new \Event([
        'summary' => '<img src=x onerror=alert(1)>',
        'dtstartTZ' => 'Europe/Berlin',
        'attendee' => ['Alice & Bob', 'Carol'],
        'description' => 'First\nSecond',
    ]);
    $eventHtml = $event->printData();
    assertMigrationCompatibility(
        str_contains($eventHtml, '&lt;img src=x onerror=alert(1)&gt;')
        && !str_contains($eventHtml, '<img src=x')
        && str_contains($eventHtml, 'DTSTART_TZ: Europe/Berlin')
        && str_contains($eventHtml, 'Alice &amp; Bob, Carol')
        && str_contains($eventHtml, "First\nSecond"),
        'Administrator iCalendar event values were not normalised and HTML-escaped.'
    );
    assertMigrationCompatibility(
        (new \Event([]))->printData() === '',
        'An empty calendar event raised missing-property warnings.'
    );

    $resultUrl = \JSMPredictionHelperRoute::getPredictionResultsRoute(15, 1, 2, 3, '#match_42');
    $rankingUrl = \JSMPredictionHelperRoute::getPredictionRankingRoute(15, 2, 1, '#ranking_2');
    $entryUrl = \JSMPredictionHelperRoute::getPredictionTippEntryRoute(15, 3, 1, 2, '#entry_1');
    $badFragmentUrl = \JSMPredictionHelperRoute::getPredictionResultsRoute(15, 1, 2, 3, '&view=administrator');

    assertMigrationCompatibility(
        str_ends_with($resultUrl, '#match_42')
        && str_ends_with($rankingUrl, '#ranking_2')
        && str_ends_with($entryUrl, '#entry_1')
        && !str_contains($badFragmentUrl, '&view=administrator'),
        'Prediction routing lost safe fragments or accepted a query-string suffix.'
    );

    $fallbackQuery = \JSMPredictionHelperRoute::buildQuery(['Itemid' => ['unexpected'], 'view' => 'predictionresults']);
    $validQuery = \JSMPredictionHelperRoute::buildQuery(['Itemid' => '0042', 'view' => 'predictionresults']);
    $invalidQuery = \JSMPredictionHelperRoute::buildQuery(['Itemid' => '42oops']);

    assertMigrationCompatibility(
        str_contains($fallbackQuery, 'Itemid=7')
        && !str_contains($fallbackQuery, 'Itemid%5B')
        && str_contains($validQuery, 'Itemid=42')
        && str_contains($invalidQuery, 'Itemid=7'),
        'Legacy prediction Itemid validation broadened or corrupted menu selection.'
    );

    $settings = new RankingModuleParamsFixture([
        'show_project_name' => 1,
        'show_picture' => 1,
        'show_team' => 1,
        'show_logo' => 1,
        'moduleclass_sfx' => ['invalid suffix'],
    ]);
    $rankingData = [
        'project' => (object) ['name' => '<script>bad</script>', 'sport_type_name' => 'football'],
        'eventtypes' => [
            (object) ['id' => 4, 'name' => '<svg onload=alert(1)>', 'icon_url' => 'data:image/svg+xml,bad'],
            null,
        ],
        'rankings' => [
            4 => [
                (object) [
                    'rank' => 1,
                    'display_name' => '<img src=x onerror=alert(1)>',
                    'team_display_name' => '<b>Bad team</b>',
                    'player_url' => 'javascript:alert(1)',
                    'team_url' => '//bad.example/path',
                    'picture_url' => 'data:image/svg+xml,bad',
                    'team_logo_url' => 'javascript:alert(2)',
                    'p' => 3,
                ],
                null,
                (object) [
                    'rank' => 2,
                    'display_name' => 'Valid player',
                    'team_display_name' => 'Valid team',
                    'player_url' => 'index.php?option=com_sportsmanagement&view=player',
                    'team_url' => 'https://example.org/team',
                    'picture_url' => 'images/person.png',
                    'team_logo_url' => 'images/team.png',
                    'p' => 1,
                ],
            ],
        ],
    ];
    $markup = renderRankingFixture($rankingData, $settings);

    assertMigrationCompatibility(
        !str_contains($markup, 'javascript:')
        && !str_contains($markup, 'data:image/')
        && !str_contains($markup, '//bad.example')
        && !str_contains($markup, '<script>bad</script>')
        && !str_contains($markup, '<svg onload')
        && str_contains($markup, '&lt;img src=x onerror=alert(1)&gt;')
        && str_contains($markup, 'index.php?option=com_sportsmanagement&amp;view=player')
        && str_contains($markup, 'https://example.org/team')
        && str_contains($markup, 'images/person.png'),
        'Native Events Ranking layout rendered unsafe URLs or unescaped database fields.'
    );

    $emptyMarkup = renderRankingFixture(
        ['project' => null, 'eventtypes' => 'invalid', 'rankings' => []],
        $settings
    );
    assertMigrationCompatibility(
        str_contains($emptyMarkup, 'MOD_SPORTSMANAGEMENT_EVENTSRANKING_NO_EVENTS_SELECTED'),
        'Malformed event type lists were not handled gracefully.'
    );

    echo "Joomla 5/6 event, prediction and ranking regressions passed.\n";
}
