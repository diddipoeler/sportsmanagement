<?php
/**
 * Inventory named PHP functions still lacking PHPDoc in site, admin and modules.
 *
 * The default audit reports outstanding functions without failing. Use --strict
 * to turn it into a complete-tree gate once the migration has finished.
 *
 * Usage: php .github/tests/joomla56_function_docs_inventory.php
 *        php .github/tests/joomla56_function_docs_inventory.php --json=/tmp/docs.json
 *        php .github/tests/joomla56_function_docs_inventory.php --strict
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$areas = ['site', 'admin', 'modules'];
$result = [
    'areas' => [],
    'files' => [],
    'total' => ['files' => 0, 'functions' => 0, 'missing' => 0],
];

foreach ($areas as $area) {
    $directory = $root . DIRECTORY_SEPARATOR . $area;
    $result['areas'][$area] = ['files' => 0, 'functions' => 0, 'missing' => 0];

    if (!is_dir($directory)) {
        fwrite(STDERR, "Missing required component directory: $area\n");
        exit(2);
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if (!$file->isFile() || strtolower($file->getExtension()) !== 'php') {
            continue;
        }

        $path = substr($file->getPathname(), strlen($root) + 1);
        $path = str_replace(DIRECTORY_SEPARATOR, '/', $path);

        if (preg_match('~/(?:vendor|node_modules|third_party)/~i', '/' . $path)) {
            continue;
        }

        $source = file_get_contents($file->getPathname());
        if ($source === false) {
            fwrite(STDERR, "Cannot read PHP file: $path\n");
            exit(2);
        }

        $tokens = token_get_all($source);
        $named = 0;
        $missing = [];

        foreach ($tokens as $index => $token) {
            if (!is_array($token) || $token[0] !== T_FUNCTION) {
                continue;
            }

            $next = $index + 1;

            while (isset($tokens[$next])
                && ((is_array($tokens[$next]) && $tokens[$next][0] === T_WHITESPACE)
                    || $tokens[$next] === '&'
                    || (is_array($tokens[$next]) && in_array($tokens[$next][0], [
                        T_AMPERSAND_FOLLOWED_BY_VAR_OR_VARARG,
                        T_AMPERSAND_NOT_FOLLOWED_BY_VAR_OR_VARARG,
                    ], true)))) {
                ++$next;
            }

            // Anonymous functions and arrow functions are not named methods.
            if (!isset($tokens[$next]) || !is_array($tokens[$next])
                || $tokens[$next][0] !== T_STRING) {
                continue;
            }

            ++$named;
            $previous = $index - 1;

            while ($previous >= 0) {
                $candidate = $tokens[$previous];
                $type = is_array($candidate) ? $candidate[0] : null;

                if ($type === T_WHITESPACE || in_array($type, [
                    T_PUBLIC, T_PROTECTED, T_PRIVATE, T_STATIC,
                    T_FINAL, T_ABSTRACT, T_READONLY,
                ], true)) {
                    --$previous;
                    continue;
                }

                break;
            }

            if ($previous < 0 || !is_array($tokens[$previous])
                || $tokens[$previous][0] !== T_DOC_COMMENT) {
                $missing[] = [
                    'name' => $tokens[$next][1],
                    'line' => $token[2],
                ];
            }
        }

        ++$result['areas'][$area]['files'];
        $result['areas'][$area]['functions'] += $named;
        $result['areas'][$area]['missing'] += count($missing);
        ++$result['total']['files'];
        $result['total']['functions'] += $named;
        $result['total']['missing'] += count($missing);

        if ($missing !== []) {
            $result['files'][] = [
                'path' => $path,
                'functions' => $named,
                'missing' => $missing,
            ];
        }
    }
}

usort(
    $result['files'],
    static fn(array $a, array $b): int => count($b['missing']) <=> count($a['missing'])
        ?: strcmp($a['path'], $b['path'])
);

$summary = [
    '## Joomla 5/6 function PHPDoc inventory',
    '',
    '| Bereich | PHP-Dateien | Benannte Funktionen | Ohne PHPDoc |',
    '| --- | ---: | ---: | ---: |',
];

foreach ($result['areas'] as $area => $count) {
    $summary[] = sprintf(
        '| %s | %d | %d | %d |',
        $area, $count['files'], $count['functions'], $count['missing']
    );
}

$summary[] = sprintf(
    '| **Gesamt** | **%d** | **%d** | **%d** |',
    $result['total']['files'],
    $result['total']['functions'],
    $result['total']['missing']
);
$summary[] = '';
$summary[] = 'Die Kennzahlen prüfen vorhandene PHPDoc-Blöcke, nicht deren inhaltliche Qualität.';
$summary[] = '';
$summary[] = '### Größte offene Dateien';
$summary[] = '';
$summary[] = '| Datei | Methoden ohne PHPDoc |';
$summary[] = '| --- | ---: |';

foreach (array_slice($result['files'], 0, 25) as $item) {
    $summary[] = '| `' . str_replace('|', '&#124;', $item['path'])
        . '` | ' . count($item['missing']) . ' |';
}

$text = implode(PHP_EOL, $summary) . PHP_EOL;
echo $text;

$jsonPath = null;
foreach (array_slice($argv, 1) as $option) {
    if (str_starts_with($option, '--json=')) {
        $jsonPath = substr($option, strlen('--json='));
    }
}

if ($jsonPath !== null && $jsonPath !== '') {
    $json = json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    if ($json === false || file_put_contents($jsonPath, $json . PHP_EOL) === false) {
        fwrite(STDERR, 'Cannot write function PHPDoc inventory JSON.' . PHP_EOL);
        exit(2);
    }
}

if (($stepSummary = getenv('GITHUB_STEP_SUMMARY')) !== false && $stepSummary !== '') {
    file_put_contents($stepSummary, $text, FILE_APPEND);
}

exit(in_array('--strict', $argv, true) && $result['total']['missing'] > 0 ? 1 : 0);
