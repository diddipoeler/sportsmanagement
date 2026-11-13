<?php
/**
 * Joomla 5/6 regression for site ages, administrator person names, and TeamPlayers text.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace {
    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);
    require_once $root . '/site/src/Helper/PersonAgeHelper.php';
    require_once $root . '/admin/src/Helper/NameFormatter.php';
    require_once $root . '/modules/mod_sportsmanagement_teamplayers/src/Helper/TeamPlayersHelper.php';

    $ages = \Diddipoeler\Component\SportsManagement\Site\Helper\PersonAgeHelper::class;
    if ($ages::calculate('2000-11-13', '2026-11-13') !== 26
        || $ages::calculate('2000-11-14', '2026-11-13') !== 25) {
        throw new \RuntimeException('Valid site ages have changed.');
    }
    foreach (["2000-11-13\n", "2000-11-13\r", '2000-02-30', '2000-11-13junk'] as $bad) {
        if ($ages::calculate($bad, '2026-11-13') !== '-') {
            throw new \RuntimeException('Malformed site date generated an age.');
        }
    }
    foreach (["2026-11-13\n", "2026-11-13\r"] as $bad) {
        if ($ages::calculate('2000-11-13', $bad) !== '-') {
            throw new \RuntimeException('Malformed site reference date generated an age.');
        }
    }

    $format = \Diddipoeler\Component\SportsManagement\Administrator\Helper\NameFormatter::class;
    if ($format::format('  Dr.  ', ' Ada ', ' ', ' Lovelace ', 0) !== 'Dr. Ada Lovelace'
        || $format::format(null, ' Ada ', ' ', ' Lovelace ', 8) !== 'A. Lovelace'
        || $format::format(null, 'Ada', '', 'Lovelace', 15) !== 'Lovelace <br /> Ada') {
        throw new \RuntimeException('Administrator name formatting produced duplicate spaces or changed layouts.');
    }

    $roster = new \Diddipoeler\Module\SportsManagementTeamPlayers\Site\Helper\TeamPlayersHelper();
    $textValue = new \ReflectionMethod($roster, 'textValue');
    if ($textValue->invoke(null, str_repeat('x', 4096)) !== str_repeat('x', 4096)
        || $textValue->invoke(null, str_repeat('x', 4097)) !== ''
        || $textValue->invoke(null, ['corrupt']) !== ''
        || $textValue->invoke(null, 'Player One') !== 'Player One') {
        throw new \RuntimeException('TeamPlayers text size and type guards regressed.');
    }

    $normalise = new \ReflectionMethod($roster, 'normaliseRosterRows');
    $rows = $normalise->invoke($roster, [
        ['not a player object'],
        (object) ['playerid' => 12, 'pid' => 5, 'firstname' => str_repeat('a', 4097), 'lastname' => 'Tester',
            'person_alias' => str_repeat('a', 4097), 'position_id' => 3, 'picture' => ['invalid']],
        (object) ['playerid' => 0, 'pid' => 1, 'firstname' => 'ignored'],
    ]);
    if (count($rows) !== 1 || $rows[0]->firstname !== '' || $rows[0]->person_alias !== ''
        || $rows[0]->person_slug !== '5:' || $rows[0]->picture !== ''
        || $rows[0]->lastname !== 'Tester') {
        throw new \RuntimeException('Oversized roster values reached the TeamPlayers view.');
    }

    echo "Joomla 5/6 site age, admin name and TeamPlayers roster text guards: OK\n";
}
