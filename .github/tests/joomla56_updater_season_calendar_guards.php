<?php
/**
 * Regression contracts for Joomla 5/6 updater, season and calendar guards.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$checks = [
    'site/controllers/updsportsmanagement.php' => [
        'if (!is_file($nativeController))',
        'require_once $nativeController;',
        'class_alias(UpdsportsmanagementController::class',
    ],
    'admin/controllers/season.php' => [
        "__DIR__ . '/../src/Controller/SportsManagementFormController.php'",
        "__DIR__ . '/../src/Controller/SeasonController.php'",
        'if (!is_file($dependency))',
        'class_alias(SeasonController::class',
    ],
    'modules/mod_sportsmanagement_calendar/src/Helper/CalendarHelper.php' => [
        'strlen($layout) > 100',
        'preg_match(\'/^[A-Za-z0-9_-]+$/D\', $layout)',
        "return 'default_jsm';",
    ],
];
$errors = [];
foreach ($checks as $path => $needles) {
    $source = @file_get_contents($root . '/' . $path);
    if ($source === false) {
        $errors[] = "$path cannot be read";
        continue;
    }

    if (!preg_match('/\\A<\\?php\\s*\\/\\*\\*(.*?)\\*\\//s', $source, $matches)) {
        $errors[] = "$path has no file-level PHPDoc";
        continue;
    }

    foreach (['@version', '@author', '@copyright', '@license'] as $tag) {
        if (!preg_match('/^\\s*\\*\\s*' . preg_quote($tag, '/') . '\\s+\\S/m', $matches[1])) {
            $errors[] = "$path lacks $tag";
        }
    }

    foreach ($needles as $needle) {
        if (!str_contains($source, $needle)) {
            $errors[] = "$path lacks expected guard: $needle";
        }
    }
}
if ($errors !== []) {
    fwrite(STDERR, implode(PHP_EOL, $errors) . PHP_EOL);
    exit(1);
}
echo "Joomla 5/6 updater, season and calendar contracts passed.\n";
