<?php
/**
 * Guard Joomla 5/6 site/admin display input and native module navigation.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$controllers = [
    'site/src/Controller/DisplayController.php',
    'admin/src/Controller/DisplayController.php',
];

foreach ($controllers as $relativePath) {
    $source = file_get_contents($root . '/' . $relativePath);

    if ($source === false
        || !str_contains($source, "trim(\$rawView) !== \$rawView")
        || !str_contains($source, "if (\$view === '')")
        || !str_contains($source, 'throw new \\InvalidArgumentException')
    ) {
        fwrite(STDERR, $relativePath . ": view input validation contract failed.\n");
        exit(1);
    }
}

$modulePath = 'modules/mod_sportsmanagement_navigation_menu/src/Helper/NativeNavigationMenuHelper.php';
$module = file_get_contents($root . '/' . $modulePath);

if ($module === false
    || !str_contains($module, '!is_string($view)')
    || !str_contains($module, "trim(\$view) !== \$view")
    || !str_contains($module, 'switch ($view)')
) {
    fwrite(STDERR, $modulePath . ": navigation view validation contract failed.\n");
    exit(1);
}

echo "Site, administrator and module view validation contracts passed.\n";
