<?php
/**
 * Validate the mandatory metadata in every SportsManagement PHP file.
 *
 * Usage: php .github/scripts/check-php-file-headers.php
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$directories = ['site', 'admin', 'modules'];
$requiredTags = ['@version', '@author', '@copyright', '@license'];
$errors = [];
$checked = 0;
$excludedThirdParty = 0;

foreach ($directories as $directory) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(
            $root . DIRECTORY_SEPARATOR . $directory,
            FilesystemIterator::SKIP_DOTS
        )
    );

    foreach ($iterator as $file) {
        if (!$file->isFile() || strtolower($file->getExtension()) !== 'php') {
            continue;
        }

        $path = $file->getPathname();
        $relativePath = str_replace('\\\\', '/', substr($path, strlen($root) + 1));

        // Dompdf is a bundled external library. Preserve its upstream
        // copyright and license headers instead of rewriting vendor code.
        if (str_starts_with($relativePath, 'site/libraries/dompdf/vendor/')) {
            ++$excludedThirdParty;
            continue;
        }

        ++$checked;
        $source = file_get_contents($path);

        if ($source === false) {
            $errors[] = substr($path, strlen($root) + 1) . ': could not read file';
            continue;
        }

        // Metadata must be in the file-level docblock, not in a method comment.
        if (!preg_match('/\\A(?:\\xEF\\xBB\\xBF)?<\\?(?i:php)\\s*\\/\\*\\*(.*?)\\*\\//s', $source, $matches)) {
            $missing = $requiredTags;
        } else {
            $header = $matches[1];
            $missing = array_values(array_filter(
                $requiredTags,
                static fn (string $tag): bool => !preg_match(
                    '/^\\s*\\*\\s*' . preg_quote($tag, '/') . '\\s+\\S/m',
                    $header
                )
            ));
        }

        if ($missing !== []) {
            $errors[] = substr($path, strlen($root) + 1) . ': missing ' . implode(', ', $missing);
        }
    }
}

foreach ($errors as $error) {
    fwrite(STDERR, $error . PHP_EOL);
}

printf("Checked %d first-party PHP files in site, admin and modules; %d vendor files excluded; %d files failed.\n", $checked, $excludedThirdParty, count($errors));

exit($errors === [] ? 0 : 1);
