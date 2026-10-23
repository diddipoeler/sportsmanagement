<?php
/**
 * Regression contract for Joomla 5/6 controller bridges and module dispatcher.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$checks = [
    'site/controllers/predictionranking.php' => [
        'is_file($nativeController)',
        'require_once $nativeController;',
        'class_alias(PredictionrankingController::class',
    ],
    'admin/controllers/githubinstall.php' => [
        '__DIR__ . \'/../src/Controller/GithubinstallController.php\'',
        'is_file($nativeController)',
        'require_once $nativeController;',
        'class_alias(GithubinstallController::class',
    ],
    'modules/mod_sportsmanagement_act_season/src/Dispatcher/Dispatcher.php' => [
        'Factory::getContainer()->get(DatabaseInterface::class)',
        'if (!$database instanceof DatabaseInterface)',
        'throw new \\RuntimeException',
    ],
];

$errors = [];
foreach ($checks as $relativePath => $needles) {
    $source = file_get_contents($root . '/' . $relativePath);
    if ($source === false) {
        $errors[] = "$relativePath: missing or unreadable";
        continue;
    }

    foreach (['@version', '@author', '@copyright', '@license', ...$needles] as $needle) {
        if (!str_contains($source, $needle)) {
            $errors[] = "$relativePath: missing $needle";
        }
    }
}

if ($errors !== []) {
    fwrite(STDERR, implode(PHP_EOL, $errors) . PHP_EOL);
    exit(1);
}

echo "Joomla 5/6 controller/dispatcher dependency contracts passed.\n";
