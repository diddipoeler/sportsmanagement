<?php
/**
 * Regression tests for bounded Joomla 5/6 Inline Hockey upload staging.
 *
 * Usage: php .github/tests/joomla56_inline_hockey_upload_staging.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace {
    \define('_JEXEC', 1);

    require_once dirname(__DIR__, 2) . '/site/src/Service/InlineHockeyImportStager.php';

    /**
     * Assert a failed upload leaves the previously staged file intact.
     *
     * @param callable():void $action Staging operation expected to fail.
     * @param string $reason Failure explanation for the regression log.
     * @return void
     */
    function assertStageRejected(callable $action, string $reason): void
    {
        try {
            $action();
        } catch (\RuntimeException $exception) {
            return;
        }

        throw new \RuntimeException('Unsafe Inline Hockey upload was accepted: ' . $reason);
    }

    /**
     * Create a ZIP test fixture containing only the requested archive names.
     *
     * @param string $path Destination ZIP fixture path.
     * @param array<string,string> $entries Entry names and source contents.
     * @return void
     */
    function createImportZip(string $path, array $entries): void
    {
        $zip = new \ZipArchive();

        if ($zip->open($path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Cannot create ZIP test fixture.');
        }

        try {
            foreach ($entries as $name => $content) {
                if (!$zip->addFromString($name, $content)) {
                    throw new \RuntimeException('Cannot insert ZIP test entry: ' . $name);
                }
            }
        } finally {
            $zip->close();
        }
    }

    if (!extension_loaded('zip')) {
        throw new \RuntimeException('PHP ZIP extension must be installed for ZIP staging regressions.');
    }

    $class = \Diddipoeler\Component\SportsManagement\Site\Service\InlineHockeyImportStager::class;

    foreach (['fixture.XLS' => 'xls', 'event.ics' => 'ics', 'report.zip' => 'zip',
        'script.php' => '', '../source.xls' => '', 'folder\\source.xls' => '',
        "\0source.xls" => '', str_repeat('x', 256) . '.xls' => '',
    ] as $name => $expected) {
        // Arrays are tested separately; PHP cannot use them as array keys.
        if ($class::extension($name) !== $expected) {
            throw new \RuntimeException('Unsafe Inline Hockey upload filename was accepted.');
        }
    }

    foreach ([['bad.xls'], new \stdClass(), null, 'nested/source.xls'] as $name) {
        if ($class::extension($name) !== '') {
            throw new \RuntimeException('Inline Hockey accepted a non-scalar or nested upload filename.');
        }
    }

    $sandbox = sys_get_temp_dir() . '/jsm-ishd-stage-' . bin2hex(random_bytes(8));

    if (!mkdir($sandbox, 0700) && !is_dir($sandbox)) {
        throw new \RuntimeException('Could not allocate import test sandbox.');
    }

    $target = $sandbox . '/ish_bw_import.xls';
    $source = $sandbox . '/source.xls';
    $zipPath = $sandbox . '/archive.zip';

    try {
        file_put_contents($target, 'existing source');
        file_put_contents($source, "BINARY-XLS-DATA\n");

        $class::stage($source, 'xls', $target);

        if (file_get_contents($target) !== "BINARY-XLS-DATA\n") {
            throw new \RuntimeException('Plain XLS staging did not activate its validated payload.');
        }

        file_put_contents($target, 'existing source');
        createImportZip($zipPath, ['README.txt' => 'harmless', 'fixtures.ics' => 'BEGIN:VCALENDAR']);

        $class::stage($zipPath, 'zip', $target);

        if (file_get_contents($target) !== 'BEGIN:VCALENDAR') {
            throw new \RuntimeException('Safe ZIP calendar entry could not be staged.');
        }

        file_put_contents($target, 'existing source');
        createImportZip($zipPath, ['../escape.php' => '<?php echo 1;', 'fixtures.xls' => 'new data']);
        assertStageRejected(static fn (): void => $class::stage($zipPath, 'zip', $target), 'path traversal');

        if (file_exists($sandbox . '/escape.php') || file_get_contents($target) !== 'existing source') {
            throw new \RuntimeException('ZIP traversal overwrote a file or previous import.');
        }

        createImportZip($zipPath, ['one.xls' => 'A', 'two.ics' => 'B']);
        assertStageRejected(static fn (): void => $class::stage($zipPath, 'zip', $target), 'ambiguous ZIP');

        if (file_get_contents($target) !== 'existing source') {
            throw new \RuntimeException('Ambiguous ZIP replaced an existing upload.');
        }

        createImportZip($zipPath, ['nested/fixtures.ics' => 'NESTED']);
        assertStageRejected(static fn (): void => $class::stage($zipPath, 'zip', $target), 'nested source');

        createImportZip($zipPath, ['fixtures.xls' => str_repeat('X', $class::MAX_IMPORT_BYTES + 1)]);
        assertStageRejected(static fn (): void => $class::stage($zipPath, 'zip', $target), 'ZIP decompression bomb');

        if (file_get_contents($target) !== 'existing source') {
            throw new \RuntimeException('Oversized ZIP replaced the previous import.');
        }

        file_put_contents($source, 'plain source');
        assertStageRejected(static fn (): void => $class::stage($source, 'php', $target), 'unsupported type');
        assertStageRejected(static fn (): void => $class::stage($sandbox . '/missing.zip', 'zip', $target), 'missing file');
    } finally {
        foreach (glob($sandbox . '/*') ?: [] as $path) {
            if (is_file($path)) {
                @unlink($path);
            }
        }

        @rmdir($sandbox);
    }

    $controller = file_get_contents(
        dirname(__DIR__, 2) . '/site/extensions/jsminlinehockey/admin/controllers/jsminlinehockey.php'
    );

    if (!is_string($controller)
        || substr_count($controller, '$this->assertImportPermission();') !== 5
        || !str_contains($controller, "authorise('core.manage', 'com_sportsmanagement')")
        || !str_contains($controller, 'is_uploaded_file($tempFilePath)')
        || !str_contains($controller, 'InlineHockeyImportStager::stage(')
        || str_contains($controller, '->extract(')
        || str_contains($controller, "Folder::files($extractDir")) {
        throw new \RuntimeException('Inline Hockey controller lost its POST/ACL or safe upload protections.');
    }

    $template = file_get_contents(
        dirname(__DIR__, 2) . '/site/extensions/jsminlinehockey/admin/views/jsminlinehockey/tmpl/default.php'
    );

    if (!is_string($template)
        || !str_contains($template, 'accept=".xls,.ics,.zip"')
        || !str_contains($template, "HTMLHelper::_('form.token')")
        || !str_contains($template, '$escape($formAction)')
        || str_contains($template, '$this->request_url')) {
        throw new \RuntimeException('Inline Hockey import form lost its safe action or upload controls.');
    }

    echo "Joomla 5/6 Inline Hockey secure XLS/ICS/ZIP import staging passed.\n";
}
