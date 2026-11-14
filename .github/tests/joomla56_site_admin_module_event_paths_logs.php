<?php
/**
 * Joomla 5/6 regression tests for event rendering, save-as-copy paths and private logs.
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
         * Return a predictable translated label in the isolated rendering test.
         *
         * @param string $key Requested translation key.
         * @return string Fixture label without framework dependencies.
         */
        public static function _(string $key): string
        {
            return $key;
        }
    }
}

namespace Joomla\CMS\Uri {
    final class Uri
    {
        /**
         * Supply a deterministic Joomla site root for event icon URLs.
         *
         * @return string Local example URL for rendering fixtures.
         */
        public static function root(): string
        {
            return 'https://example.test/';
        }
    }
}

namespace Joomla\CMS\Log {
    final class Log
    {
        public const ERROR = 1;
        public static array $entries = [];

        /**
         * Capture one error log entry for sensitive message assertions.
         *
         * @param string $message Message logged by the module helper.
         * @param int $level Error severity.
         * @param string $category Joomla log category.
         * @return void
         */
        public static function add(string $message, int $level, string $category): void
        {
            self::$entries[] = [$message, $level, $category];
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Helper {
    final class PersonNameFormatter
    {
        /**
         * Simulate the native name formatter while encoding untrusted input.
         *
         * @param object|null $person Optional person metadata.
         * @param string $first Player given name.
         * @param string $nickname Player nickname.
         * @param string $last Player surname.
         * @param mixed $format Configured name format.
         * @return string HTML-safe name for event and substitution fixtures.
         */
        public static function format(?object $person, string $first, string $nickname, string $last, mixed $format): string
        {
            return htmlspecialchars(trim($first . ' ' . $last), ENT_QUOTES, 'UTF-8');
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require dirname(__DIR__, 2) . '/site/src/Helper/MatchEventPresentationHelper.php';
    require dirname(__DIR__, 2) . '/admin/src/Helper/ExtraFieldsSaveHelper.php';
    require dirname(__DIR__, 2) . '/modules/mod_sportsmanagement_new_project/src/Helper/NewProjectHelper.php';

    $presentation = \Diddipoeler\Component\SportsManagement\Site\Helper\MatchEventPresentationHelper::class;
    $game = (object) ['id' => 7, 'projectteam1_id' => 21, 'projectteam2_id' => 22];
    $events = [
        null,
        ['ptid' => 21, 'firstname1' => 'Corrupt'],
        (object) ['ptid' => 21, 'event_type_id' => 8, 'firstname1' => '<Player>', 'lastname1' => 'One', 'event_time' => 12],
    ];
    $changes = [
        false,
        (object) ['ptid' => 22, 'firstname' => 'New', 'lastname' => 'Player', 'out_firstname' => 'Old', 'out_lastname' => 'Player', 'in_out_time' => '60'],
    ];
    $eventTypes = [
        'malformed',
        (object) ['id' => 0, 'name' => 'Invalid'],
        (object) ['id' => 8, 'name' => 'Goal'],
    ];

    $html = $presentation::render($game, $eventTypes, $events, $changes, [
        'use_tabs_events' => 1,
        'show_event_minute' => 1,
        'name_format' => 0,
    ]);

    if (!str_contains($html, 'nextmatch-events-7-event-8')
        || !str_contains($html, '&lt;Player&gt;')
        || !str_contains($html, 'Old Player')
        || str_contains($html, '<Player>')
        || str_contains($html, 'Corrupt')) {
        throw new \RuntimeException('Malformed event rows must not interrupt safe tab/substitution rendering.');
    }

    $empty = $presentation::render($game, [], [null, false, []], [false], []);

    if ($empty !== '') {
        throw new \RuntimeException('Event rendering must ignore entirely invalid collections.');
    }

    $zeroTeams = $presentation::render(
        (object) ['id' => 7, 'projectteam1_id' => 0, 'projectteam2_id' => 0],
        $eventTypes,
        [(object) ['ptid' => 0, 'event_type_id' => 8, 'firstname1' => 'Zero']],
        [],
        []
    );

    if (str_contains($zeroTeams, 'Zero')) {
        throw new \RuntimeException('Invalid zero project-team IDs must not display unrelated events.');
    }

    $saveHelper = new \Diddipoeler\Component\SportsManagement\Administrator\Helper\ExtraFieldsSaveHelper();
    $increment = new \ReflectionMethod($saveHelper, 'incrementPathSegment');

    foreach ([
        ['root/dir/a/b/c/0007/file', 5, 'root/dir/a/b/c/8/file'],
        ['root/0//b/c/0/', 5, 'root/0//b/c/1/'],
        ['root/dir/a/b/c/5bad/file', 5, 'root/dir/a/b/c/5bad/file'],
        ['root/dir/a/b/c/9223372036854775808/file', 5, 'root/dir/a/b/c/9223372036854775808/file'],
        ['root/dir/a/b/c/9223372036854775807/file', 5, 'root/dir/a/b/c/9223372036854775807/file'],
        ['root/dir', 5, 'root/dir'],
    ] as [$input, $index, $expected]) {
        if ($increment->invoke($saveHelper, $input, $index) !== $expected) {
            throw new \RuntimeException('Copy counter path handling changed unexpectedly: ' . $input);
        }
    }

    $module = \Diddipoeler\Module\SportsManagementNewProject\Site\Helper\NewProjectHelper::class;
    $log = new \ReflectionMethod($module, 'logArticleSaveFailure');
    $log->invoke(null, new \RuntimeException('SQLSTATE secret-password=/internal/db'), 42);
    $entries = \Joomla\CMS\Log\Log::$entries;
    $line = $entries[0][0] ?? '';

    if (count($entries) !== 1 || str_contains($line, 'secret-password')
        || !str_contains($line, 'RuntimeException')
        || !str_contains($line, '42')) {
        throw new \RuntimeException('Article save failures must log a safe exception class and project ID only.');
    }

    echo "Joomla 5/6 site event, admin copy path and module logging regressions: OK\n";
}
