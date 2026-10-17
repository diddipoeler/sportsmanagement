<?php
/**
 * Regression contract for Joomla 5/6 current-season database failures.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

$file = dirname(__DIR__, 2) . '/modules/mod_sportsmanagement_act_season/src/Helper/ActSeasonHelper.php';
$source = file_get_contents($file);

if ($source === false) {
    fwrite(STDERR, "Cannot read current-season helper.\n");
    exit(1);
}

$try = strpos($source, 'try {');
$database = strpos($source, '$db = $this->database(');
$query = strpos($source, '$query = $db->createQuery()');
$execute = strpos($source, '$db->loadObjectList()');
$catch = strpos($source, 'catch (\\Throwable $e)');

if ($try === false || $database === false || $query === false
    || $execute === false || $catch === false
    || !($try < $database && $database < $query && $query < $execute && $execute < $catch)
    || !str_contains(substr($source, $catch), '$list = [];')
) {
    fwrite(STDERR, "Database resolution, query construction and execution must share the failure handler.\n");
    exit(1);
}

echo "Current-season database failure contract passed.\n";
