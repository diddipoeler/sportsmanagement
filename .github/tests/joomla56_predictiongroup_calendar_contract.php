<?php
/**
 * Joomla 5/6 prediction-group and calendar input regression contract.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$checks = [
    'admin/controllers/predictiongroup.php' => [
        "__DIR__ . '/../src/Controller/SportsManagementFormController.php'",
        "__DIR__ . '/../src/Controller/PredictiongroupController.php'",
        'if (!is_file($dependency))',
    ],
    'modules/mod_sportsmanagement_calendar/src/Helper/CalendarHelper.php' => [
        'self::normaliseYear($year)',
        '$month = max(1, min(12, $month));',
        '$day = max(0, min(31, $day));',
        'if ($day !== 0 && !checkdate($month, $day, $year))',
        '$day = 0;',
    ],
];

$failures = [];
foreach ($checks as $path => $tokens) {
    $source = @file_get_contents($root . '/' . $path);
    if ($source === false) {
        $failures[] = "$path cannot be read";
        continue;
    }
    foreach (['@version', '@author', '@copyright', '@license'] as $tag) {
        if (!str_contains(substr($source, 0, 500), $tag)) {
            $failures[] = "$path missing $tag";
        }
    }
    foreach ($tokens as $token) {
        if (!str_contains($source, $token)) {
            $failures[] = "$path missing expected code: $token";
        }
    }
}
if ($failures !== []) {
    fwrite(STDERR, implode(PHP_EOL, $failures) . PHP_EOL);
    exit(1);
}
echo "Joomla 5/6 prediction-group and calendar contracts passed.\n";
