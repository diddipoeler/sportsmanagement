<?php
/**
 * Joomla 5/6 birthday module database selector and failure-path tests.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

define('_JEXEC', 1);

$path = dirname(__DIR__, 2) . '/modules/mod_sportsmanagement_birthday/src/Helper/BirthdayHelper.php';
require_once $path;

$helper = new \Diddipoeler\Module\SportsManagementBirthday\Site\Helper\BirthdayHelper();
$method = new ReflectionMethod($helper, 'normaliseDatabaseSelector');

// Only the two supported database modes are valid; bad values fail closed
// instead of being coerced to zero or silently selecting external storage.

foreach ([
    [0, 0],
    [1, 1],
    ['0', 0],
    ['1', 1],
    ['0000', 0],
    ['0001', 1],
    [7, null],
    ['7', null],
    [false, null],
    [true, null],
    [-1, null],
    ['-1', null],
    ['1xyz', null],
    ['2.5', null],
    [2.5, null],
    [[], null],
    [[7], null],
    [(object) ['id' => 7], null],
    ['9999999999999999999999999999999', null],
] as [$value, $expected]) {
    $actual = $method->invoke($helper, $value);

    if ($actual !== $expected) {
        throw new RuntimeException('Unexpected birthday database selector for ' . var_export($value, true));
    }
}

$source = file_get_contents($path);
$try = strpos($source, '        try {');
$resolver = strpos($source, '$db = $this->database(');
$teamIds = strpos($source, '$teamIds = $this->resolveTeamIds(');
$query = strpos($source, '$query = $db->createQuery()');
$fetch = strpos($source, '$rows = $db->loadObjectList()');
$catch = strpos($source, 'catch (\\Throwable $e)');

if ($source === false
    || $try === false || $resolver === false || $teamIds === false
    || $query === false || $fetch === false || $catch === false
    || !($try < $resolver && $resolver < $teamIds && $teamIds < $query
        && $query < $fetch && $fetch < $catch)
    || !str_contains(substr($source, $catch), '$rows = [];')
) {
    throw new RuntimeException('Birthday database resolution, query and fetch must share the failure handler.');
}

echo "Birthday database selector and query-failure contracts passed.\n";
