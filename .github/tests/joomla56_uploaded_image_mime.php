<?php
/**
 * Joomla 5/6 frontend image upload MIME validation regression tests.
 *
 * Usage: php .github/tests/joomla56_uploaded_image_mime.php
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\Component {
    class ComponentHelper
    {
        public static function getParams(string $name): object
        {
            return new class {
                public static int $maxSize = 1;
                public function get(string $name, mixed $default = null): mixed
                {
                    return $name === 'image_max_size' ? self::$maxSize : $default;
                }
            };
        }
    }
}

namespace Joomla\CMS\Language {
    class Text
    {
        public static function _(string $key): string { return $key; }
    }
}

namespace Joomla\CMS\Log {
    class Log
    {
        public const WARNING = 'warning';
        public static function add(string $message, string $level, string $category): void {}
    }
}

namespace Joomla\Filesystem {
    class File
    {
        public static function getExt(string $name): string
        {
            return (string) pathinfo($name, PATHINFO_EXTENSION);
        }
    }
}

namespace {
    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);
    require_once $root . '/site/src/Helper/ImageSelectHelper.php';

    $helper = \Diddipoeler\Component\SportsManagement\Site\Helper\ImageSelectHelper::class;

    $png = base64_decode(
        'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Wl7o0kAAAAASUVORK5CYII=',
        true
    );
    $gif = base64_decode('R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=', true);

    if ($png === false || $gif === false) {
        throw new \RuntimeException('Image fixtures cannot be decoded.');
    }

    $tmp = tempnam(sys_get_temp_dir(), 'jsm-img-');

    if ($tmp === false) {
        throw new \RuntimeException('Unable to create image fixture.');
    }

    $check = static function (string $name, string $body, bool $expected, ?int $declaredSize = null) use ($tmp, $helper): void {
        file_put_contents($tmp, $body);
        $actual = $helper::check([
            'name' => $name,
            'tmp_name' => $tmp,
            'size' => $declaredSize ?? strlen($body),
        ]);

        if ($actual !== $expected) {
            throw new \RuntimeException('Image upload MIME acceptance changed for ' . $name);
        }
    };

    try {
        $check('team.png', $png, true);
        $check('TEAM.PNG', $png, true);
        $check('team.gif', $gif, true);
        $check('team.jpg', $png, false); // Extension must match the actual file type.
        $check('team.webp', $png, false);
        $check('team.gif', '<?php echo "not a real image"; ?>', false);
        $check('team.png', '<html><script>alert(1)</script></html>', false);
        $check('team.svg', '<svg xmlns="http://www.w3.org/2000/svg" onload="alert(1)"/>', false);
        $check('team.svg', '<svg xmlns="http://www.w3.org/2000/svg"/>', false);
        $check('team.png', $png, false, 0);
        $check('team.png', $png, false, 2000); // Uploaded metadata over size limit.
        $check('team.png', $png . str_repeat(' ', 1100), false, strlen($png)); // Actual bytes over limit.
        $check('team.png', '', false);
    } finally {
        unlink($tmp);
    }

    echo "Joomla 5/6 raster image uploads and MIME validation OK\n";
}
