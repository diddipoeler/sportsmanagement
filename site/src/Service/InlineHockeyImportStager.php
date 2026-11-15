<?php
/**
 * Joomla 5/6 safe import staging for the legacy Inline Hockey extension.
 *
 * @version    5.6.0
 * @author     diddipoeler, SportsManagement contributors
 * @copyright  Copyright (C) 2013-2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Diddipoeler\Component\SportsManagement\Site\Service;

\defined('_JEXEC') or die;

/**
 * Copy an uploaded XLS/ICS or one ZIP entry to the historical import target.
 *
 * Unlike the previous Archive::extract() path, no untrusted archive member is
 * extracted to the Joomla public tmp directory. The shared target changes only
 * once a candidate has passed validation and has been fully copied.
 */
final class InlineHockeyImportStager
{
    public const MAX_IMPORT_BYTES = 10485760;
    private const MAX_ARCHIVE_ENTRIES = 500;
    private const ALLOWED_EXTENSIONS = ['xls', 'ics', 'zip'];

    /**
     * Validate an upload extension without trusting the browser's MIME header.
     *
     * Never reuse the user-supplied filename as a filesystem destination.
     *
     * @param mixed $name Raw browser-provided upload filename.
     * @return string Allowed extension, or an empty string for invalid names.
     */
    public static function extension(mixed $name): string
    {
        if (!is_string($name) || $name === '' || strlen($name) > 255
            || str_contains($name, '/') || str_contains($name, '\\')
            || preg_match('/[\x00-\x1f\x7f]/', $name)) {
            return '';
        }

        $extension = strtolower((string) pathinfo($name, PATHINFO_EXTENSION));

        return in_array($extension, self::ALLOWED_EXTENSIONS, true) ? $extension : '';
    }

    /**
     * Stage a validated and already-uploaded file into the legacy import path.
     *
     * Plain files are streamed with a strict size cap. ZIP archives must have
     * exactly one safely named XLS/ICS entry; directory traversal, symlinks,
     * unexpectedly large or ambiguous archives are rejected. The replacement
     * is renamed into place only after the full copy has succeeded.
     *
     * @param string $source Validated upload in Joomla's temporary directory.
     * @param string $extension Validated XLS, ICS or ZIP file extension.
     * @param string $target Existing historical import filename to replace.
     * @return void
     * @throws \RuntimeException For an unsafe or unreadable import package.
     */
    public static function stage(string $source, string $extension, string $target): void
    {
        if (!in_array($extension, self::ALLOWED_EXTENSIONS, true)
            || !is_file($source) || !is_readable($source) || is_link($source)) {
            throw new \RuntimeException('Invalid Inline Hockey import source.');
        }

        $size = filesize($source);

        if ($size === false || $size < 1 || $size > self::MAX_IMPORT_BYTES) {
            throw new \RuntimeException('Inline Hockey import exceeds the 10 MiB upload limit.');
        }

        $directory = dirname($target);

        if (!is_dir($directory) || !is_writable($directory)
            || (file_exists($target) && (is_link($target) || !is_file($target)))) {
            throw new \RuntimeException('Inline Hockey import destination is unavailable.');
        }

        $candidate = tempnam($directory, 'jsm_ishd_');

        if ($candidate === false) {
            throw new \RuntimeException('Could not allocate an Inline Hockey import staging file.');
        }

        try {
            if ($extension === 'zip') {
                self::copyZipEntry($source, $candidate);
            } else {
                $input = @fopen($source, 'rb');

                if ($input === false) {
                    throw new \RuntimeException('Could not read the Inline Hockey import upload.');
                }

                try {
                    self::copyBounded($input, $candidate);
                } finally {
                    fclose($input);
                }
            }

            if (!@rename($candidate, $target)) {
                throw new \RuntimeException('Could not activate the Inline Hockey import file.');
            }
        } finally {
            if (is_file($candidate)) {
                @unlink($candidate);
            }
        }
    }

    /**
     * Select and stream one safe spreadsheet or calendar entry from a ZIP.
     *
     * Never extracts the archive itself: unrelated entries cannot overwrite
     * Joomla files or be mistaken for a previous user's temporary import.
     *
     * @param string $archiveFile Path to an already validated ZIP upload.
     * @param string $candidate New private candidate file to populate.
     * @return void
     * @throws \RuntimeException For unsupported ZIP contents or file sizes.
     */
    private static function copyZipEntry(string $archiveFile, string $candidate): void
    {
        if (!class_exists(\ZipArchive::class)) {
            throw new \RuntimeException('The PHP ZIP extension is required for Inline Hockey imports.');
        }

        $zip = new \ZipArchive();

        if ($zip->open($archiveFile) !== true) {
            throw new \RuntimeException('The Inline Hockey import ZIP cannot be opened.');
        }

        try {
            if ($zip->numFiles < 1 || $zip->numFiles > self::MAX_ARCHIVE_ENTRIES) {
                throw new \RuntimeException('The Inline Hockey import ZIP has an invalid entry count.');
            }

            $selected = null;
            $expectedSize = 0;

            for ($index = 0; $index < $zip->numFiles; ++$index) {
                $entry = $zip->statIndex($index);
                $name = is_array($entry) ? ($entry['name'] ?? null) : null;

                if (!is_string($name) || $name === ''
                    || preg_match('/[\x00-\x1f\x7f]/', $name)
                    || str_contains($name, '\\')
                    || str_contains($name, '..')
                    || str_starts_with($name, '/')) {
                    throw new \RuntimeException('The Inline Hockey ZIP contains an unsafe entry name.');
                }

                // Do not extract directories, nested paths or irrelevant files.
                if (str_contains($name, '/') || !in_array(
                    strtolower((string) pathinfo($name, PATHINFO_EXTENSION)),
                    ['xls', 'ics'],
                    true
                )) {
                    continue;
                }

                $memberSize = $entry['size'] ?? null;

                if (!is_int($memberSize) || $memberSize < 1
                    || $memberSize > self::MAX_IMPORT_BYTES
                    || preg_match('/^[A-Za-z0-9][A-Za-z0-9_.-]{0,180}\.(?:xls|ics)$/iD', $name) !== 1) {
                    throw new \RuntimeException('The Inline Hockey ZIP entry is invalid or too large.');
                }

                $opsys = 0;
                $attributes = 0;

                if ($zip->getExternalAttributesIndex($index, $opsys, $attributes)
                    && (($attributes >> 16) & 0170000) === 0120000) {
                    throw new \RuntimeException('Inline Hockey ZIP symlinks are not allowed.');
                }

                if ($selected !== null) {
                    throw new \RuntimeException('The Inline Hockey ZIP has more than one import source.');
                }

                $selected = $name;
                $expectedSize = $memberSize;
            }

            if ($selected === null) {
                throw new \RuntimeException('The Inline Hockey ZIP contains no XLS or ICS source.');
            }

            $input = $zip->getStream($selected);

            if ($input === false) {
                throw new \RuntimeException('Could not read the Inline Hockey ZIP entry.');
            }

            try {
                $copied = self::copyBounded($input, $candidate);

                if ($copied !== $expectedSize) {
                    throw new \RuntimeException('The Inline Hockey ZIP entry was not fully extracted.');
                }
            } finally {
                fclose($input);
            }
        } finally {
            $zip->close();
        }
    }

    /**
     * Copy a bounded stream without buffering a complete ZIP entry in memory.
     *
     * An extra byte beyond the cap is an error rather than a silently
     * truncated source. Short writes and read failures also abort staging.
     *
     * @param resource $input Open readable source stream.
     * @param string $candidate Newly allocated destination path.
     * @return int Number of validated bytes copied.
     * @throws \RuntimeException When reading or writing the import fails.
     */
    private static function copyBounded($input, string $candidate): int
    {
        $output = @fopen($candidate, 'wb');

        if ($output === false) {
            throw new \RuntimeException('Could not create Inline Hockey import staging output.');
        }

        try {
            $total = 0;

            while (!feof($input)) {
                $chunk = fread($input, 65536);

                if ($chunk === false) {
                    throw new \RuntimeException('Could not read Inline Hockey import bytes.');
                }

                $length = strlen($chunk);
                $total += $length;

                if ($total > self::MAX_IMPORT_BYTES) {
                    throw new \RuntimeException('Inline Hockey import payload exceeds the 10 MiB limit.');
                }

                if ($length > 0 && fwrite($output, $chunk) !== $length) {
                    throw new \RuntimeException('Could not write the Inline Hockey import staging file.');
                }
            }

            if ($total === 0) {
                throw new \RuntimeException('Inline Hockey import source is empty.');
            }

            return $total;
        } finally {
            fclose($output);
        }
    }
}
