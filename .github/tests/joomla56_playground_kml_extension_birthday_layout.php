<?php
/**
 * Test Joomla 5/6 KML guards, extension language paths and birthday layout dimensions.
 *
 * Usage: php .github/tests/joomla56_playground_kml_extension_birthday_layout.php
 *
 * @version 5.6.0
 * @author SportsManagement contributors
 * @copyright Copyright (C) 2026 SportsManagement contributors
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Filesystem {
    /** Local directory fixture mirroring Joomla Filesystem. */
    final class Folder {
        /**
         * Create temporary paths needed by the KML exporter.
         * @param string $path Directory to create.
         * @return bool Whether creation succeeded.
         */
        public static function create(string $path): bool {
            return is_dir($path) || mkdir($path, 0777, true);
        }
    }

    /** Local file-writing fixture mirroring Joomla Filesystem. */
    final class File {
        /**
         * Write the KML fixture into the temporary test directory.
         * @param string $path Destination file.
         * @param string $data KML content.
         * @return bool Whether writing succeeded.
         */
        public static function write(string $path, string $data): bool {
            return file_put_contents($path, $data) !== false;
        }
    }
}

namespace Joomla\CMS\Language {
    /** Minimal translation fixture for birthday layout rendering. */
    final class Text {
        /**
         * Echo the key without loading Joomla translation files.
         * @param string $key Joomla translation key.
         * @return string Untranslated key.
         */
        public static function _(string $key): string { return $key; }

        /**
         * Substitute a stable translated message for birthdays.
         * @param string $key Joomla translation key.
         * @param mixed ...$args Message arguments.
         * @return string Untranslated message key.
         */
        public static function sprintf(string $key, mixed ...$args): string { return $key; }
    }
}

namespace {
    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);
    $temporary = sys_get_temp_dir() . '/sm-joomla56-' . bin2hex(random_bytes(8));
    if (!mkdir($temporary, 0777, true)) {
        throw new \RuntimeException('Unable to create temporary Joomla fixture.');
    }
    define('JPATH_SITE', $temporary);

    require_once $root . '/site/src/Helper/PlaygroundKmlHelper.php';
    require_once $root . '/admin/src/Helper/ExtensionLanguageHelper.php';

    try {
        $kml = \Diddipoeler\Component\SportsManagement\Site\Helper\PlaygroundKmlHelper::class;
        if ($kml::write(101, "Street\0Broken", 'Stadium', 51.5, 7.5)
            || $kml::write(101, 'Street', "Stadium\x01Broken", 51.5, 7.5)) {
            throw new \RuntimeException('KML accepted invalid XML control characters.');
        }
        if (!$kml::write(101, 'Street & Town', 'Stadium <Home>', 51.5, 7.5)) {
            throw new \RuntimeException('Valid KML placemark was not exported.');
        }
        $destination = $temporary . '/tmp/101-playground.kml';
        $xml = file_get_contents($destination);
        if (!is_string($xml)
            || !str_contains($xml, 'Stadium &lt;Home&gt;')
            || !str_contains($xml, 'Street &amp; Town')) {
            throw new \RuntimeException('KML did not encode valid XML content.');
        }

        $target = $temporary . '/original.kml';
        file_put_contents($target, 'preserve');
        unlink($destination);
        if (!symlink($target, $destination)) {
            throw new \RuntimeException('Unable to create KML link fixture.');
        }
        if ($kml::write(101, 'Safe', 'Stadium', 51.5, 7.5)
            || file_get_contents($target) !== 'preserve') {
            throw new \RuntimeException('KML exporter followed a pre-existing symlink.');
        }
        unlink($destination);

        $extensions = $temporary . '/components/com_sportsmanagement/extensions';
        mkdir($extensions, 0777, true);
        mkdir($extensions . '/customview');

        $language = \Diddipoeler\Component\SportsManagement\Administrator\Helper\ExtensionLanguageHelper::class;
        if ($language::forView('customview') !== ['customview']
            || $language::forView('../customview') !== []
            || $language::forView('customview/') !== []) {
            throw new \RuntimeException('Extension language folder guard rejected valid or accepted invalid paths.');
        }

        $outside = $temporary . '/external';
        mkdir($outside);
        symlink($outside, $extensions . '/externalview');
        if ($language::forView('externalview') !== []) {
            throw new \RuntimeException('Extension language helper followed an external symlink.');
        }
        unlink($extensions . '/externalview');

        $params = new class {
            /**
             * Return hostile module configuration values for output tests.
             * @param string $key Module configuration key.
             * @param mixed $default Fallback value.
             * @return mixed Value supplied by the test configuration.
             */
            public function get(string $key, mixed $default = null): mixed {
                return match ($key) {
                    'moduleclass_sfx', 'table_class' => ['bad'],
                    'maxdays' => ['bad'],
                    'picture_width' => '999999',
                    'picture_height' => '-500',
                    'show_player_card' => 0,
                    default => $default,
                };
            }
        };

        $module = (object) ['id' => 44];
        $mode = 'B';
        $persons = [[
            'picture_url' => '/player.png',
            'display_name' => 'Athlete',
            'person_link' => '/profile',
            'flag_html' => '',
            'birthday_text' => '12 Nov',
        ]];

        ob_start();
        require $root . '/modules/mod_sportsmanagement_birthday/tmpl/default.php';
        $html = (string) ob_get_clean();
        if (!str_contains($html, 'max-width:250px')
            || str_contains($html, '999999')
            || str_contains($html, 'Array')) {
            throw new \RuntimeException('Birthday module accepted malformed CSS or class settings.');
        }

        $mode = 'L';
        ob_start();
        require $root . '/modules/mod_sportsmanagement_birthday/tmpl/default.php';
        $html = (string) ob_get_clean();
        if (!str_contains($html, 'max-height:50px')
            || str_contains($html, '-500px')) {
            throw new \RuntimeException('Birthday module did not bound picture height.');
        }

        echo "Joomla 5/6 playground KML, language extension paths and birthday module: OK\n";
    } finally {
        foreach ([
            $temporary . '/tmp/101-playground.kml',
            $temporary . '/original.kml',
        ] as $file) {
            if (is_file($file) || is_link($file)) {
                unlink($file);
            }
        }
        foreach ([
            $temporary . '/components/com_sportsmanagement/extensions/customview',
            $temporary . '/components/com_sportsmanagement/extensions',
            $temporary . '/components/com_sportsmanagement',
            $temporary . '/components',
            $temporary . '/external',
            $temporary . '/tmp',
            $temporary,
        ] as $dir) {
            if (is_dir($dir)) {
                rmdir($dir);
            }
        }
    }
}
