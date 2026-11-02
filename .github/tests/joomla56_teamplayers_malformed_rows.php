<?php
/**
 * Regression checks for malformed Team Players roster records.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

define('_JEXEC', 1);
require dirname(__DIR__, 2)
    . '/modules/mod_sportsmanagement_teamplayers/src/Helper/TeamPlayersHelper.php';

$class = \Diddipoeler\Module\SportsManagementTeamPlayers\Site\Helper\TeamPlayersHelper::class;
$helper = new $class();
$normalise = new ReflectionMethod($class, 'normaliseRosterRows');
$format = new ReflectionMethod($class, 'formatName');
$text = new ReflectionMethod($class, 'textValue');

foreach ([['Player', 'Player'], ['', ''], [0, ''], [true, ''],
    [['Player'], ''], [new stdClass(), '']] as [$raw, $expected]) {
    if ($text->invoke(null, $raw) !== $expected) {
        throw new RuntimeException('Invalid Team Players text field was coerced.');
    }
}

$input = [
    (object) [
        'pid' => '0012',
        'playerid' => '0007',
        'firstname' => ['invalid'],
        'nickname' => new stdClass(),
        'lastname' => 'Torres',
        'person_alias' => 'torres',
        'country' => ['DEU'],
        'country_name' => ['invalid'],
        'country_picture' => ['invalid'],
        'picture' => 'images/torres.png',
        'ppic' => null,
        'position_id' => '003',
        'position' => ['invalid'],
    ],
    null,
    ['pid' => 2, 'playerid' => 3],
    (object) ['pid' => '2junk', 'playerid' => 3],
    (object) ['pid' => 8, 'playerid' => true],
    (object) ['pid' => 9, 'playerid' => 10, 'firstname' => 'Mia', 'lastname' => 'Last',
        'position_id' => null, 'country_picture' => new stdClass()],
];

$players = $normalise->invoke($helper, $input);
if (count($players) !== 2
    || $players[0]->person_slug !== '12:torres'
    || $players[0]->playerid !== 7
    || $players[0]->position_id !== 3
    || $players[0]->firstname !== ''
    || $players[0]->country !== ''
    || $players[0]->country_picture !== ''
    || $players[0]->position !== ''
    || $players[0]->picture !== 'images/torres.png'
    || $players[1]->person_slug !== '9:'
    || $players[1]->position_id !== 0
    || $players[1]->ppic !== ''
    || $format->invoke($helper, $players[0], 0) !== 'Torres'
    || $format->invoke($helper, $players[1], 0) !== 'Mia Last') {
    throw new RuntimeException('Team Players malformed roster normalisation regression.');
}

if ($normalise->invoke($helper, (object) ['pid' => 1, 'playerid' => 2]) !== []
    || $normalise->invoke($helper, 'bad data') !== []) {
    throw new RuntimeException('Team Players accepted a non-list roster result.');
}

echo "Team Players malformed roster checks passed.\n";
