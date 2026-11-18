<?php
/**
 * Regress Joomla 5/6 legacy results facade and safe quote text rendering.
 *
 * Exercises real site and module helpers without a Joomla installation,
 * including malformed imported identifiers, slugs, sorting flags and text.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

define('_JEXEC', 1);

require_once dirname(__DIR__, 2)
    . '/site/src/Helper/ResultsLegacyProjectCompatibility.php';
require_once dirname(__DIR__, 2)
    . '/modules/mod_sportsmanagement_rquotes/src/Helper/QuoteText.php';

$facade = \Diddipoeler\Component\SportsManagement\Site\Helper\ResultsLegacyProjectCompatibility::class;
$quotes = \Diddipoeler\Module\SportsManagementRquotes\Site\Helper\QuoteText::class;
$event1 = (object) ['id' => 1];
$event2 = (object) ['id' => 2];
$substitution = (object) ['id' => 3];
$checks = 0;

set_error_handler(static function (int $level, string $message): never {
    throw new \ErrorException($message, 0, $level);
});

try {
    $facade::sync(
        (object) [
            'id' => '00042',
            'season_id' => '0010',
            'slug' => '42:season-2026',
            'round_slug' => '5:round-5',
            'division_slug' => '1:division',
        ],
        5,
        1,
        [17 => [$event1, $event2]],
        [17 => [$substitution]],
        [3, '0004', ['5'], false, '6junk', 7]
    );

    if ($facade::$projectid !== 42 || $facade::$seasonid !== 10
        || $facade::$cfg_which_database !== 1
        || $facade::$favteams !== [3, 4, 7]
        || $facade::$projectslug !== '42:season-2026'
        || $facade::$roundslug !== '5:round-5') {
        throw new \RuntimeException('Valid legacy results context was not preserved.');
    }
    ++$checks;

    if ($facade::getMatchEvents('00017') !== [$event1, $event2]
        || $facade::getMatchEvents(17, 0, 1) !== [$event2, $event1]
        || $facade::getMatchSubstitutions('0017') !== [$substitution]) {
        throw new \RuntimeException('Valid events, substitutions or descending order regressed.');
    }
    ++$checks;

    foreach ([
        '17junk', '17.0', ' 17', '-17', str_repeat('9', 70),
        17.0, true, false, ['17'], (object) ['id' => 17], null,
    ] as $invalid) {
        if ($facade::getMatchEvents($invalid) !== []
            || $facade::getMatchSubstitutions($invalid) !== []) {
            throw new \RuntimeException('Invalid legacy match ID matched another result.');
        }
        ++$checks;
    }

    foreach ([true, '1trailing', ['1'], 1.0] as $invalidSort) {
        if ($facade::getMatchEvents(17, 0, $invalidSort) !== [$event1, $event2]) {
            throw new \RuntimeException('Malformed legacy ordering flag reversed events.');
        }
        ++$checks;
    }

    $facade::sync(
        (object) [
            'id' => ['42'],
            'season_id' => '8wrong',
            'slug' => ['unexpected'],
            'round_slug' => "5:round\n",
            'division_slug' => (object) ['wrong' => true],
        ],
        -5,
        9,
        [],
        [],
        [true, '42junk', 0, -1]
    );

    if ($facade::$projectid !== 0 || $facade::$seasonid !== 0
        || $facade::$cfg_which_database !== 0
        || $facade::$projectslug !== '0'
        || $facade::$roundslug !== '0'
        || $facade::$divisionslug !== ''
        || $facade::$favteams !== []) {
        throw new \RuntimeException('Malformed legacy project context leaked into a route.');
    }
    ++$checks;

    foreach ([
        ["Hello\0 world", 'Hello world'],
        ["Control\x1Ftext\x7F", 'Controltext'],
        ["First\r\nSecond", "First<br>\nSecond"],
        ["First\rSecond", "First<br>\nSecond"],
        ["A\tB", "A\tB"],
        ["<b>Safe</b><br>next", "Safe<br>\nnext"],
        ['&lt;script&gt;alert(1)&lt;/script&gt;', '&lt;script&gt;alert(1)&lt;/script&gt;'],
        [['bad'], ''],
        [INF, ''],
        [str_repeat('x', 65537), ''],
    ] as [$raw, $expected]) {
        if ($quotes::render($raw) !== $expected) {
            throw new \RuntimeException('Unsafe or incompatible quote text conversion.');
        }
        ++$checks;
    }
} finally {
    restore_error_handler();
}

echo "Joomla 5/6 legacy result and module quote guards: {$checks} assertions passed.\n";
