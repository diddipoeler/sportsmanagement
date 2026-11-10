<?php
/**
 * Standalone calendar month-grid regression tests for Joomla 5/6.
 *
 * Checks previous-month placeholder dates across year and leap-year
 * boundaries without loading Joomla or the module's database connectors.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Diddipoeler\Component\SportsManagement\Site\Service {
    /** Minimal site application resolver for the standalone renderer test. */
    final class SportsManagementSiteApplicationResolver
    {
        /** @return object Test site application with a language stub. */
        public static function resolve(): object
        {
            return new class {
                /** @return bool Whether this is the expected frontend client. */
                public function isClient(string $name): bool { return $name === 'site'; }

                /** @return object Minimal language instance. */
                public function getLanguage(): object
                {
                    return new class {
                        /** @return bool Simulate loading calendar translations. */
                        public function load(string $name): bool { return true; }

                        /** @return string Translation key, without markup. */
                        public function _(string $key): string { return $key; }
                    };
                }
            };
        }
    }
}

namespace {
    \defined('_JEXEC') || \define('_JEXEC', 1);

    require_once dirname(__DIR__, 2)
        . '/modules/mod_sportsmanagement_calendar/src/Runtime/CalendarRenderer.php';

    /** Calendar fixture without external connectors or Joomla queries. */
    final class CalendarGridFixture extends
        \Diddipoeler\Module\SportsManagementCalendar\Site\Runtime\CalendarRenderer
    {
        /** @return string Inert navigation URL for test rendering. */
        public function getCalendarLink(int $month, int $year): string { return '#'; }

        /** @return array{hasAction:bool} Disable date actions in this fixture. */
        public function getDateMeta(int $day, int $month, int $year): array
        {
            return ['hasAction' => false];
        }

        /** @return array Empty fixture match list. */
        public function matches_output($month, $year): array { return []; }

        /** @return array Empty fixture team list. */
        public function output_teamlist(): array { return []; }
    }

    set_error_handler(static function (int $level, string $message): never {
        throw new \ErrorException($message, 0, $level);
    });

    try {
        $renderer = new CalendarGridFixture();

        foreach ([
            [1, 2026, ['28', '29', '30', '31']],
            [3, 2024, ['25', '26', '27', '28', '29']],
        ] as [$month, $year, $expected]) {
            $grid = $renderer->getMonthHTML($month, $year, 0);
            $html = $grid['calendar'];

            preg_match_all(
                '/<td class="jlcCalendarDay jlcCalendarDayEmpty ">([^<]+)<\/td>/',
                $html,
                $matches
            );

            if (array_slice($matches[1], 0, count($expected)) !== $expected) {
                throw new \RuntimeException(
                    sprintf('Calendar grid has incorrect preceding dates for %04d-%02d.', $year, $month)
                );
            }

            if ($grid['list'] !== [] || $grid['teamslist'] !== []) {
                throw new \RuntimeException('Calendar module rendered unexpected connector data.');
            }
        }
    } finally {
        restore_error_handler();
    }

    echo "Joomla 5/6 calendar month grid OK\n";
}
