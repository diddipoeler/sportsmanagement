<?php
/**
 * Regression for malformed image upload metadata and size settings.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\Component {
    final class ComponentHelper {
        public static mixed $size = 120;
        /** Return the configured size fixture. @param string $component Component name. @return object Settings fixture. */
        public static function getParams(string $component): object {
            return new class {
                /** Read the selected size setting. @param string $name Setting key. @param mixed $default Default value. @return mixed Configured value. */
                public function get(string $name, mixed $default = null): mixed { return ComponentHelper::$size; }
            };
        }
    }
}
namespace Joomla\CMS\Language {
    final class Text {
        /** Return the untranslated key for assertions. @param string $key Language key. @return string Key. */
        public static function _(string $key): string { return $key; }
    }
}
namespace Joomla\CMS\Log {
    final class Log {
        public const WARNING = 1;
        /** Suppress fixture logging. @param string $message Log text. @param int $level Severity. @param string $category Log category. @return void */
        public static function add(string $message, int $level, string $category): void {}
    }
}
namespace Joomla\Filesystem {
    final class File {
        /** Extract the submitted extension. @param string $name Filename. @return string Extension. */
        public static function getExt(string $name): string { return pathinfo($name, PATHINFO_EXTENSION); }
    }
}
namespace {
    define('_JEXEC', 1);
    require dirname(__DIR__, 2) . '/site/src/Helper/ImageSelectHelper.php';
    $helper = \Diddipoeler\Component\SportsManagement\Site\Helper\ImageSelectHelper::class;
    $file = tempnam(sys_get_temp_dir(), 'jsm-image-');
    try {
        file_put_contents($file, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aW1sAAAAASUVORK5CYII='));
        $valid = ['name' => 'test.png', 'tmp_name' => $file, 'size' => filesize($file)];
        set_error_handler(static function (int $severity, string $message): never {
            throw new \RuntimeException($message);
        });
        foreach ([null, true, ['name' => ['test.png']], array_replace($valid, ['tmp_name' => ['x']]),
            array_replace($valid, ['size' => true]), array_replace($valid, ['size' => '2junk']),
            array_replace($valid, ['size' => PHP_INT_MAX . '0'])] as $bad) {
            if ($helper::check($bad)) {
                throw new \RuntimeException('Malformed upload metadata was accepted.');
            }
        }
        foreach ([120, '000120', ['120'], '99999999999999999999999'] as $setting) {
            \Joomla\CMS\Component\ComponentHelper::$size = $setting;
            if (!$helper::check($valid)) {
                throw new \RuntimeException('Valid image rejected by bounded size fallback.');
            }
        }
        $parse = new \ReflectionMethod($helper, 'nonNegativeInteger');
        if ($parse->invoke(null, '0005') !== 5 || $parse->invoke(null, str_repeat('0', 17)) !== null) {
            throw new \RuntimeException('Image size parser regression.');
        }
    } finally {
        restore_error_handler();
        unlink($file);
    }
    echo "Image upload metadata guards OK\n";
}
