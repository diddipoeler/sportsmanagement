<?php
/**
 * Validate file-level PHP attribution for the Joomla 5/6 migration.
 *
 * Usage: php .github/scripts/check-php-headers.php site admin modules
 *        php .github/scripts/check-php-headers.php path/to/file.php [...]
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$inputs = array_slice($argv, 1);

if ($inputs === []) {
    fwrite(STDERR, "Usage: php check-php-headers.php <file-or-directory> [...]\n");
    exit(2);
}

$required = ['version', 'author', 'copyright', 'license'];
$failed = 0;
$checked = 0;

$checkFile = static function (string $path) use ($required, &$failed, &$checked): void {
    if (!is_file($path) || strtolower(pathinfo($path, PATHINFO_EXTENSION)) !== 'php') {
        return;
    }

    ++$checked;
    $file = file_get_contents($path);

    if ($file === false) {
        fwrite(STDERR, "$path: cannot read file\n");
        ++$failed;
        return;
    }

    // Only the initial file-level docblock qualifies: tags buried in classes
    // or third-party code must not hide a missing file header.
    $file = preg_replace('/^\xEF\xBB\xBF/', '', $file);
    $hasHeader = preg_match('/^\s*<\?php\s*\/\*\*(.*?)\*\//s', $file, $match) === 1;

    if (!$hasHeader) {
        fwrite(STDERR, "$path: missing file-level PHPDoc header\n");
        ++$failed;
        return;
    }

    foreach ($required as $tag) {
        if (preg_match('/^\s*\*\s*@' . $tag . '\s+\S+/mi', $match[1]) !== 1) {
            fwrite(STDERR, "$path: missing or empty @$tag\n");
            ++$failed;
        }
    }
};

foreach ($inputs as $input) {
    $path = $root . DIRECTORY_SEPARATOR . ltrim($input, '/');
    if (is_file($path)) {
        $checkFile($path);
        continue;
    }

    if (!is_dir($path)) {
        fwrite(STDERR, "$input: file or directory not found\n");
        ++$failed;
        continue;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $entry) {
        if ($entry->isFile()) {
            $checkFile($entry->getPathname());
        }
    }
}

printf("Checked %d PHP files; %d header errors.\n", $checked, $failed);
exit($failed === 0 ? 0 : 1);
