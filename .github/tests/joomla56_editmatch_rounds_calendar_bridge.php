<?php
/**
 * Contract tests for Joomla 5/6 edit-match, rounds and calendar runtime.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$checks = [
    'site/controllers/editmatch.php' => [
        'if (!is_file($nativeController))',
        'require_once $nativeController;',
        'class_alias(EditmatchController::class',
    ],
    'admin/controllers/rounds.php' => [
        'if (!is_file($dependency))',
        'require_once $dependency;',
        'class_alias(RoundsController::class',
    ],
    'modules/mod_sportsmanagement_calendar/src/Helper/CalendarHelper.php' => [
        '$calendar->modid = max(0, $moduleId);',
        '$calendar->ajax = $ajax === 1 ? 1 : 0;',
    ],
];

$failures = [];

foreach ($checks as $relative => $tokens) {
    $contents = @file_get_contents($root . '/' . $relative);

    if ($contents === false) {
        $failures[] = "$relative: unreadable";
        continue;
    }

    if (!preg_match('~\\A<\\?php\\s*/\\*\\*(.*?)\\*/~s', $contents, $match)) {
        $failures[] = "$relative: missing file-level docblock";
        continue;
    }

    foreach (['@version', '@author', '@copyright', '@license'] as $tag) {
        if (!preg_match('~^\\s*\\*\\s*' . preg_quote($tag, '~') . '\\s+\\S~m', $match[1])) {
            $failures[] = "$relative: missing $tag";
        }
    }

    foreach ($tokens as $token) {
        if (!str_contains($contents, $token)) {
            $failures[] = "$relative: missing guard $token";
        }
    }
}

if ($failures !== []) {
    fwrite(STDERR, implode(PHP_EOL, $failures) . PHP_EOL);
    exit(1);
}

echo "Joomla 5/6 edit-match, rounds and calendar contracts passed.\n";
