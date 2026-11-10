<?php
/**
 * Standalone Joomla 5/6 calendar team-filter ID regression.
 *
 * Ensures legacy connector IDs are parsed without lossy integer casts,
 * silently creating filter entries for the wrong project teams.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

define('_JEXEC', 1);

/** Inert parent: the addTeam test needs no Joomla or calendar connectors. */
class PHPCalendar
{
}

require dirname(__DIR__, 2)
    . '/modules/mod_sportsmanagement_calendar/src/Runtime/CalendarRuntime.php';

$runtime = \Diddipoeler\Module\SportsManagementCalendar\Site\Runtime\CalendarRuntime::class;
$runtime::$teams = [];
$runtime::$teamslist = [];

set_error_handler(static function (int $severity, string $message): never {
    throw new ErrorException($message, 0, $severity);
});

try {
    foreach ([
        0, -7, null, true, false, 7.5, '0', '0000', '', '   ',
        '7junk', '7.0', '+7', '-7', '7e2', [], [7], (object) ['id' => 7],
        (string) PHP_INT_MAX . '0', str_repeat('0', 65) . '7',
    ] as $invalidId) {
        $runtime::addTeam($invalidId, 'Invalid');
    }

    if ($runtime::$teams !== [] || $runtime::$teamslist !== []) {
        throw new RuntimeException('Malformed calendar team IDs created filter entries.');
    }

    $runtime::addTeam(7, 'Home', 'home.png');
    $runtime::addTeam('0007', 'Duplicate', 'duplicate.png');
    $runtime::addTeam(' 0008 ', 'Away', 'away.png');
    $runtime::addTeam(PHP_INT_MAX, 'Maximum', 'max.png');

    if (array_keys($runtime::$teams) !== [7, 8, PHP_INT_MAX]
        || count($runtime::$teamslist) !== 3
        || $runtime::$teams[7]->value !== 7
        || $runtime::$teams[7]->name !== 'Home'
        || $runtime::$teams[7]->picture !== 'home.png'
        || $runtime::$teams[8]->value !== 8
        || $runtime::$teams[8]->name !== 'Away'
        || $runtime::$teams[PHP_INT_MAX]->value !== PHP_INT_MAX) {
        throw new RuntimeException('Valid and deduplicated calendar team IDs were not preserved.');
    }

    // Regression contract: upstream connectors must not cast imported IDs
    // before addTeam has a chance to validate the original value.
    $connector = file_get_contents(
        dirname(__DIR__, 2)
        . '/modules/mod_sportsmanagement_calendar/connectors/sportsmanagement_j5.php'
    );

    if ($connector === false
        || str_contains($connector, 'parent::addTeam((int)')
        || !str_contains($connector, 'parent::addTeam($row->projectteam1_id')
        || !str_contains($connector, 'parent::addTeam($row->projectteam2_id')) {
        throw new RuntimeException('Calendar connector bypassed project-team ID validation.');
    }
} finally {
    restore_error_handler();
}

echo "Joomla 5/6 calendar team ID validation OK\n";
