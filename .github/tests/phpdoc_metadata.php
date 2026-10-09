<?php
/**
 * Verify the required file-level PHPDoc metadata in SportsManagement PHP files.
 *
 * Usage: php .github/tests/phpdoc_metadata.php [--all|path ...]
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$areas = ['site/', 'admin/', 'modules/'];
$arguments = array_slice($argv, 1);

if ($arguments === [] || $arguments === ['--all']) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
    );
    $arguments = [];
    foreach ($iterator as $file) {
        if ($file->isFile() && strtolower($file->getExtension()) === 'php') {
            $arguments[] = substr($file->getPathname(), strlen($root) + 1);
        }
    }
}

$errors = [];
$checked = 0;
foreach ($arguments as $path) {
    $path = str_replace('\\', '/', $path);
    if (!str_ends_with(strtolower($path), '.php')
        || !array_filter($areas, static fn(string $area): bool => str_starts_with($path, $area))) {
        continue;
    }
    $absolutePath = realpath($root . '/' . $path);
    if ($absolutePath === false || !str_starts_with($absolutePath, $root . DIRECTORY_SEPARATOR)
        || !is_file($absolutePath)) {
        $errors[] = "$path: PHP file could not be found or is outside the repository";
        continue;
    }
    ++$checked;
    $source = file_get_contents($absolutePath);
    if ($source === false || !preg_match('/^(?:\\xEF\\xBB\\xBF)?<\\?php\\s*\\/\\*\\*(.*?)\\*\\//s', $source, $match)) {
        $errors[] = "$path: missing file-level PHPDoc block";
        continue;
    }
    foreach (['version', 'author', 'copyright', 'license'] as $tag) {
        if (!preg_match('/^\\s*\\*\\s*@' . $tag . '\\s+\\S+/m', $match[1])) {
            $errors[] = "$path: missing or empty @$tag";
        }
    }
}
foreach ($errors as $error) {
    fwrite(STDERR, $error . PHP_EOL);
}
printf("Checked %d PHP files in site/, admin/, modules/: %d problems.\n", $checked, count($errors));
exit($errors === [] ? 0 : 1);
