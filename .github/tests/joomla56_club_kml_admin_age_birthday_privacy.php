<?php
/**
 * Joomla 5/6 regression checks for club KML output, administrator age and birthday log privacy.
 *
 * @version 5.6.0
 * @author SportsManagement contributors
 * @copyright Copyright (C) 2026 SportsManagement contributors
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\CMS\Uri {
    /** Fixed Joomla site URL fixture for KML media paths. */
    final class Uri {
        /**
         * Return the trusted root URL for relative club pictures.
         *
         * @return string Joomla site URL.
         */
        public static function root(): string { return 'https://example.test/'; }
    }
}
namespace Joomla\Filesystem {
    /** Standalone Joomla directory creation fixture. */
    final class Folder {
        /**
         * Create the Joomla fixture tmp directory if needed.
         *
         * @param string $path Directory to create.
         * @return bool Whether the directory is available.
         */
        public static function create(string $path): bool {
            return is_dir($path) || mkdir($path, 0700, true);
        }
    }
    /** Standalone Joomla file writing fixture. */
    final class File {
        /**
         * Write the KML bytes to a test destination.
         *
         * @param string $path Destination file.
         * @param string $data XML content.
         * @return bool Whether writing succeeded.
         */
        public static function write(string $path, string $data): bool {
            return file_put_contents($path, $data) !== false;
        }
    }
}
namespace {
    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);
    $tmp = sys_get_temp_dir() . '/sm-clubkml-' . bin2hex(random_bytes(8));
    if (!mkdir($tmp, 0700)) {
        throw new \RuntimeException('Could not allocate temporary Joomla root.');
    }
    define('JPATH_SITE', $tmp);

    require_once $root . '/site/src/Helper/ClubKmlHelper.php';
    require_once $root . '/admin/src/Helper/PersonAgeHelper.php';

    try {
        $club = \Diddipoeler\Component\SportsManagement\Site\Helper\ClubKmlHelper::class;
        if ($club::write(8, "address\x01invalid", 'Club', '', 51.5, 7.5)
            || $club::write(8, 'Address', "Club\x00invalid", '', 51.5, 7.5)
            || $club::write(0, 'Address', 'Club', '', 51.5, 7.5)
            || $club::write(8, 'Address', 'Club', '', 91.0, 7.5)) {
            throw new \RuntimeException('Club KML allowed invalid XML, ID or coordinates.');
        }

        if (!$club::write(8, 'Address & Town', 'Club <name>', '', 51.5, 7.5)) {
            throw new \RuntimeException('Valid club KML output was rejected.');
        }
        $filename = $tmp . '/tmp/8-club.kml';
        $xml = file_get_contents($filename);
        if (!is_string($xml) || !str_contains($xml, 'Club &lt;name&gt;')
            || !str_contains($xml, 'Address &amp; Town')) {
            throw new \RuntimeException('Club KML did not escape name and address.');
        }

        $target = $tmp . '/untouched';
        file_put_contents($target, 'preserve');
        unlink($filename);
        if (!symlink($target, $filename)) {
            throw new \RuntimeException('Could not create club KML symlink fixture.');
        }
        if ($club::write(8, 'Address', 'Club', '', 51.5, 7.5)
            || file_get_contents($target) !== 'preserve') {
            throw new \RuntimeException('Club KML followed a pre-existing symlink.');
        }
        unlink($filename);

        $age = new \Diddipoeler\Component\SportsManagement\Administrator\Helper\PersonAgeHelper();
        if ($age->calculate('2000-11-12', '2026-11-12') !== 26
            || $age->calculate('2000-11-13', '2026-11-12') !== 25
            || $age->calculate('2000-02-29', '2026-02-28') !== 25) {
            throw new \RuntimeException('Administrator age calculation regressed.');
        }
        foreach ([' 2000-11-12', "2000-11-12\n", '0000-00-00', '2026-02-30', '2000-11-12 25:00:00'] as $bad) {
            if ($age->calculate($bad, '2026-11-12') !== null) {
                throw new \RuntimeException('Malformed birthday was accepted.');
            }
        }

        $birthday = file_get_contents(
            $root . '/modules/mod_sportsmanagement_birthday/src/Helper/BirthdayHelper.php'
        );
        if (!is_string($birthday)
            || !str_contains($birthday, "'SportsManagement Birthday query failed (' . get_class(")
            || str_contains($birthday, "'SportsManagement Birthday query failed: ' . " . '$e->getMessage()')) {
            throw new \RuntimeException('Birthday module error log exposes driver messages.');
        }

        echo "Joomla 5/6 club KML, administrator age and birthday error privacy: OK\n";
    } finally {
        foreach ([$tmp . '/tmp/8-club.kml', $tmp . '/untouched'] as $file) {
            if (is_file($file) || is_link($file)) {
                unlink($file);
            }
        }
        if (is_dir($tmp . '/tmp')) {
            rmdir($tmp . '/tmp');
        }
        rmdir($tmp);
    }
}
