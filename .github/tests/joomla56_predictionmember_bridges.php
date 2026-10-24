<?php
/**
 * Joomla 5/6 prediction member bridge regression checks.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$controllers = [
    'admin/controllers/predictionmember.php' => 'SportsManagementFormController',
    'admin/controllers/predictionmembers.php' => 'SportsManagementAdminController',
];
$errors = [];

foreach ($controllers as $file => $base) {
    $source = @file_get_contents($root . '/' . $file);
    if ($source === false) {
        $errors[] = "$file missing";
        continue;
    }
    foreach (['@version', '@author', '@copyright', '@license'] as $tag) {
        if (!str_contains(substr($source, 0, 600), $tag)) {
            $errors[] = "$file lacks $tag";
        }
    }
    foreach (["__DIR__ . '/../src/Controller/$base.php'", 'if (!is_file($dependency))', 'require_once $dependency;'] as $needle) {
        if (!str_contains($source, $needle)) {
            $errors[] = "$file lacks expected guard: $needle";
        }
    }
}
if ($errors !== []) {
    fwrite(STDERR, implode(PHP_EOL, $errors) . PHP_EOL);
    exit(1);
}
echo "Joomla 5/6 prediction member bridges passed.\n";
