<?php
/**
 * Safe single-file ZIP staging for the legacy Joomla 5/6 import controllers.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Service;

\defined('_JEXEC') or die;

/**
 * Read one supported, top-level data file directly from an uploaded ZIP.
 *
 * Never extract the archive to Joomla's shared tmp directory: doing so could
 * overwrite files or accidentally select data left behind by another import.
 */
final class LegacyImportZipService
{
    private const MAX_ARCHIVE_BYTES = 32 * 1024 * 1024;
    private const MAX_IMPORT_BYTES = 25 * 1024 * 1024;
    private const MAX_ENTRIES = 100;

    /**
     * @param string[] $extensions  Allowed data extensions, without a dot.
     */
    public static function stage(string $archivePath, string $targetPath, array $extensions): bool
    {
        if (!class_exists(\ZipArchive::class)
            || !is_file($archivePath)
            || is_link($archivePath)
            || filesize($archivePath) === false
            || filesize($archivePath) > self::MAX_ARCHIVE_BYTES
            || is_link($targetPath)
            || !is_dir(dirname($targetPath))) {
            return false;
        }

        $archive = new \ZipArchive();

        if ($archive->open($archivePath) !== true) {
            return false;
        }

        $source = null;
        $output = null;
        $stagedPath = null;

        try {
            if ($archive->numFiles < 1 || $archive->numFiles > self::MAX_ENTRIES) {
                return false;
            }

            $entry = null;

            for ($i = 0; $i < $archive->numFiles; ++$i) {
                $info = $archive->statIndex($i);

                if (!is_array($info) || !is_string($info['name'] ?? null)) {
                    return false;
                }

                $name = $info['name'];

                // Reject traversal/absolute paths even when that entry is not
                // selected as the import file. Never extract archive contents.
                if ($name === ''
                    || str_contains($name, "\0")
                    || str_contains($name, '\\')
                    || str_starts_with($name, '/')
                    || preg_match('#(?:^|/)\.\.(?:/|$)#', $name)
                    || preg_match('/^[A-Za-z]:/', $name)) {
                    return false;
                }

                if (str_ends_with($name, '/') || str_contains($name, '/')) {
                    // The historical importers only support files in the ZIP
                    // root. Directories cannot be chosen as source data.
                    continue;
                }

                $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));

                if (!in_array($extension, $extensions, true)) {
                    continue;
                }

                // Reject ambiguous archives; selecting the first match could
                // import a different dataset than the administrator intended.
                if ($entry !== null
                    || !is_int($info['size'] ?? null)
                    || $info['size'] < 1
                    || $info['size'] > self::MAX_IMPORT_BYTES) {
                    return false;
                }

                $entry = $name;
                $expectedBytes = $info['size'];
            }

            if ($entry === null) {
                return false;
            }

            $source = $archive->getStream($entry);

            if (!is_resource($source)) {
                return false;
            }

            $stagedPath = tempnam(dirname($targetPath), 'jsm-zip-');

            if ($stagedPath === false) {
                return false;
            }

            $output = @fopen($stagedPath, 'wb');

            if (!is_resource($output)) {
                return false;
            }

            $bytes = 0;

            while (!feof($source)) {
                $buffer = fread($source, 8192);

                if ($buffer === false || ($buffer === '' && !feof($source))) {
                    return false;
                }

                $bytes += strlen($buffer);

                if ($bytes > self::MAX_IMPORT_BYTES
                    || ($buffer !== '' && fwrite($output, $buffer) !== strlen($buffer))) {
                    return false;
                }
            }

            if ($bytes !== $expectedBytes || $bytes === 0) {
                return false;
            }

            fclose($output);
            $output = null;

            // An attacker must not replace a symlink with executable source.
            if (is_link($targetPath) || !@rename($stagedPath, $targetPath)) {
                return false;
            }

            $stagedPath = null;
            return true;
        } catch (\Throwable) {
            return false;
        } finally {
            if (is_resource($source)) {
                fclose($source);
            }

            if (is_resource($output)) {
                fclose($output);
            }

            if (is_string($stagedPath) && is_file($stagedPath)) {
                @unlink($stagedPath);
            }

            $archive->close();
        }
    }
}
