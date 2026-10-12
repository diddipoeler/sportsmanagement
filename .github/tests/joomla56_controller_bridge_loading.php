<?php
/**
 * Regression contract for the Joomla 5/6 legacy controller bridges.
 *
 * Ensures compatibility aliases still map to an existing native controller,
 * independent of a particular Joomla installation path.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

$root = dirname(__DIR__, 2);

$bridges = [
    'site/controllers/results.php' => ['ResultsController', 'sportsmanagementControllerResults'],
    'site/controllers/treetonode.php' => ['TreetonodeController', 'sportsmanagementControllerTreetonode'],
    'site/controllers/uefawertung.php' => ['UefawertungController', 'sportsmanagementControlleruefawertung'],
    'site/controllers/updsportsmanagement.php' => ['UpdsportsmanagementController', 'sportsmanagementControllerUpdsportsmanagement'],
    'admin/controllers/cpanel.php' => ['CpanelController', 'sportsmanagementControllercpanel'],
    'admin/controllers/databasetool.php' => ['DatabasetoolController', 'sportsmanagementControllerDatabaseTool'],
    'admin/controllers/databasetools.php' => ['DatabasetoolsController', 'sportsmanagementControllerDatabaseTools'],
];

$errors = [];

foreach ($bridges as $path => [$controller, $legacyAlias]) {
    $absolutePath = $root . '/' . $path;
    $source = is_file($absolutePath) ? file_get_contents($absolutePath) : false;

    if ($source === false) {
        $errors[] = "$path: legacy bridge is missing";
        continue;
    }

    $expectedLoader = "__DIR__ . '/../src/Controller/{$controller}.php'";
    if (!str_contains($source, $expectedLoader)) {
        $errors[] = "$path: controller loader must use the local component path";
    }

    $expectedAlias = "class_alias({$controller}::class, '{$legacyAlias}')";
    if (!str_contains($source, $expectedAlias)) {
        $errors[] = "$path: legacy class alias has changed";
    }

    $native = dirname($absolutePath) . '/../src/Controller/' . $controller . '.php';
    $nativeSource = is_file($native) ? file_get_contents($native) : false;

    if ($nativeSource === false || !preg_match('/\\bclass\\s+' . preg_quote($controller, '/') . '\\b/', $nativeSource)) {
        $errors[] = "$path: native controller class is missing";
    }

    if (str_contains($source, 'JPATH_SITE') || str_contains($source, 'JPATH_ADMINISTRATOR')) {
        $errors[] = "$path: legacy loader still depends on Joomla path constants";
    }
}

foreach ([
    'modules/mod_sportsmanagement_eventsranking/helper.php' => 'EventsRanking',
    'modules/mod_sportsmanagement_firstleagueoverview/helper.php' => 'FirstLeagueOverview',
] as $path => $module) {
    $source = is_file($root . '/' . $path) ? file_get_contents($root . '/' . $path) : false;

    if ($source === false || !str_contains($source, 'instanceof DatabaseInterface')) {
        $errors[] = "$path: {$module} database service type guard is missing";
    }
}

foreach ($errors as $error) {
    fwrite(STDERR, $error . PHP_EOL);
}

printf("Checked %d native controller bridges and 2 module helpers: %d problems.\n", count($bridges), count($errors));
exit($errors === [] ? 0 : 1);
