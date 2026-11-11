<?php
/**
 * Validate descriptive PHPDoc blocks before named first-party PHP functions.
 *
 * Usage: php .github/scripts/check-php-function-docs.php site admin modules
 *        php .github/scripts/check-php-function-docs.php path/to/file.php [...]
 *
 * Bundled third-party vendor code retains its upstream documentation.
 * Explicit .github/tests and .github/scripts PHP files are also checked.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

/**
 * Check whether a PHPDoc block actually describes the function's purpose.
 *
 * Annotation-only blocks are insufficient: descriptions must contain at least
 * one readable prose line rather than only @param or @return declarations.
 *
 * @param string $comment Complete PHPDoc token.
 * @return bool Whether the block contains explanatory prose.
 */
function smHasFunctionDescription(string $comment): bool
{
    $body = preg_replace('~^/\*\*|\*/$~', '', $comment) ?? '';

    foreach (preg_split('/\R/', $body) ?: [] as $line) {
        $text = trim(preg_replace('/^\s*\*\s?/', '', $line) ?? $line);

        if ($text !== '' && !str_starts_with($text, '@') && !str_starts_with($text, '{@')
            && strlen($text) >= 20 && preg_match('/[A-Za-z]{3}/', $text) === 1) {
            return true;
        }
    }

    return false;
}

/**
 * Find the PHPDoc attached to a method or named global function.
 *
 * Walks backward past modifiers and PHP attributes, but stops at unrelated
 * statements so class comments cannot accidentally document later functions.
 *
 * @param array $tokens Source tokens.
 * @param int $position Position of the T_FUNCTION token.
 * @return string|null Attached documentation, if any.
 */
function smDocForFunction(array $tokens, int $position): ?string
{
    $modifiers = [T_PUBLIC, T_PROTECTED, T_PRIVATE, T_STATIC, T_FINAL, T_ABSTRACT, T_READONLY];

    for ($i = $position - 1; $i >= 0; --$i) {
        $token = $tokens[$i];
        $id = is_array($token) ? $token[0] : null;

        if ($id === T_WHITESPACE || $id === T_COMMENT || in_array($id, $modifiers, true)) {
            continue;
        }

        if ($token === ']') {
            $depth = 1;

            while (--$i >= 0 && $depth > 0) {
                if ($tokens[$i] === ']') {
                    ++$depth;
                } elseif ($tokens[$i] === '[' || (is_array($tokens[$i]) && $tokens[$i][0] === T_ATTRIBUTE)) {
                    --$depth;
                }
            }

            continue;
        }

        return $id === T_DOC_COMMENT ? $token[1] : null;
    }

    return null;
}

/**
 * Report named functions that do not have a descriptive adjacent PHPDoc block.
 *
 * Tokenization avoids mistaking text, comments or anonymous closures for named
 * API methods; findings include line numbers for targeted migration changes.
 *
 * @param string $file Absolute first-party source path.
 * @return array{functions:int,errors:array<int,string>} Audit summary and errors.
 */
function smAuditFunctionDocs(string $file): array
{
    $source = file_get_contents($file);

    if ($source === false) {
        return ['functions' => 0, 'errors' => [$file . ': cannot read source']];
    }

    $tokens = token_get_all($source);
    $errors = [];
    $count = 0;

    foreach ($tokens as $index => $token) {
        if (!is_array($token) || $token[0] !== T_FUNCTION) {
            continue;
        }

        $next = $index + 1;

        while (isset($tokens[$next])) {
            $part = $tokens[$next];
            $type = is_array($part) ? $part[0] : null;

            if ($type === T_WHITESPACE || $part === '&'
                || $type === T_AMPERSAND_FOLLOWED_BY_VAR_OR_VARARG
                || $type === T_AMPERSAND_NOT_FOLLOWED_BY_VAR_OR_VARARG) {
                ++$next;
                continue;
            }

            break;
        }

        if (!isset($tokens[$next]) || !is_array($tokens[$next]) || $tokens[$next][0] !== T_STRING) {
            continue;
        }

        ++$count;
        $doc = smDocForFunction($tokens, $index);

        if ($doc === null || !smHasFunctionDescription($doc)) {
            $errors[] = $file . ':' . $token[2] . ': ' . $tokens[$next][1]
                . '() needs a PHPDoc description of its purpose';
        }
    }

    return ['functions' => $count, 'errors' => $errors];
}

$root = realpath(dirname(__DIR__, 2));
$inputs = array_slice($argv, 1);

if ($root === false || $inputs === []) {
    fwrite(STDERR, "Usage: php check-php-function-docs.php <file-or-directory> [...]\n");
    exit(2);
}

$files = [];
$errors = [];

foreach ($inputs as $input) {
    $path = realpath($root . DIRECTORY_SEPARATOR . ltrim($input, '/'));

    if ($path === false || !str_starts_with($path, $root . DIRECTORY_SEPARATOR)) {
        $errors[] = $input . ': not found inside the repository';
        continue;
    }

    $candidates = is_dir($path)
        ? new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS))
        : [$path];

    foreach ($candidates as $candidate) {
        $file = (string) $candidate;
        $relative = str_replace(DIRECTORY_SEPARATOR, '/', substr($file, strlen($root) + 1));

        // GitHub regression fixtures supplied explicitly to this script
        // must be audited as well as source files: do not silently report
        // success for zero-function test inputs.
        $isFirstParty = preg_match('~^(?:site|admin|modules)/.*\\.php$~i', $relative) === 1;
        $isFixture = preg_match('~^\\.github/(?:tests|scripts)/[^/]+\\.php$~i', $relative) === 1;

        if (($isFirstParty || $isFixture)
            && !str_starts_with($relative, 'site/libraries/dompdf/vendor/')
            && is_file($file)) {
            $files[$file] = true;
        }
    }
}

if ($files === []) {
    fwrite(STDERR, "No eligible PHP files were found for the requested paths.\n");
    exit(2);
}

$count = 0;

foreach (array_keys($files) as $file) {
    $result = smAuditFunctionDocs($file);
    $count += $result['functions'];

    foreach ($result['errors'] as $error) {
        $errors[] = $error;
    }
}

foreach ($errors as $error) {
    fwrite(STDERR, $error . PHP_EOL);
}

printf(
    "Checked %d named functions in %d PHP files; %d documentation errors.\n",
    $count,
    count($files),
    count($errors)
);

exit($errors === [] ? 0 : 1);
