<?php
/**
 * Standalone tests for Joomla 5/6 playground ticker image URL safety.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\CMS\Application {
    interface CMSApplicationInterface
    {
    }
}

namespace Joomla\Database {
    interface DatabaseInterface
    {
    }
}

namespace Joomla\Registry {
    final class Registry
    {
        public function __construct(private array $values = [])
        {
        }

        public function get(string $name, mixed $default = null): mixed
        {
            return $this->values[$name] ?? $default;
        }
    }
}

namespace Joomla\CMS\Component {
    final class ComponentHelper
    {
        public static \Joomla\Registry\Registry $componentParams;

        public static function getParams(string $extension): \Joomla\Registry\Registry
        {
            return self::$componentParams;
        }
    }
}

namespace Joomla\CMS\Uri {
    final class Uri
    {
        public static function root(): string
        {
            return 'https://site.example.test/';
        }
    }
}

namespace Joomla\CMS\Language {
    final class Text
    {
        public static function _(string $key): string
        {
            return $key;
        }
    }
}

namespace {
    \defined('_JEXEC') || define('_JEXEC', 1);

    require_once dirname(__DIR__, 2)
        . '/modules/mod_sportsmanagement_playground_ticker/src/Helper/PlaygroundTickerHelper.php';

    final class FakeSiteApp implements \Joomla\CMS\Application\CMSApplicationInterface
    {
        public function getInput(): object
        {
            return new class {
                public function getInt(string $key, int $default = 0): int
                {
                    return $default;
                }
            };
        }
    }

    $helper = new \Diddipoeler\Module\SportsManagementPlaygroundTicker\Site\Helper\PlaygroundTickerHelper();
    $params = new \Joomla\Registry\Registry([
        'cfg_which_database' => 1,
        'mode' => 'B',
    ]);
    $app = new FakeSiteApp();

    $servers = [
        ['https://cdn.example.test/media/', 'https://cdn.example.test/media/'],
        ['javascript:alert(1)', 'https://site.example.test/'],
        ['https://user:secret@cdn.example.test/media/', 'https://site.example.test/'],
        ['https://cdn.example.test/media/?token=secret', 'https://site.example.test/'],
        ['https://cdn.example.test/media/#fragment', 'https://site.example.test/'],
        ['https://cdn.example.test/media/' . "\n" . 'invalid', 'https://site.example.test/'],
        ['file:///etc/passwd', 'https://site.example.test/'],
    ];

    foreach ($servers as [$configured, $expected]) {
        \Joomla\CMS\Component\ComponentHelper::$componentParams = new \Joomla\Registry\Registry([
            'cfg_which_database' => 1,
            'cfg_which_database_server' => $configured,
        ]);

        if ($helper->getPictureServer($params, $app) !== $expected) {
            throw new \RuntimeException('Unsafe playground picture server was accepted: ' . $configured);
        }
    }

    $render = static function (string $picture, string $server) use ($params): string {
        $playgrounds = [(object) [
            'playground_name' => 'Test Stadium',
            'picture' => $picture,
        ]];
        $module = (object) ['id' => 5, 'picture_server' => $server];

        ob_start();

        try {
            require dirname(__DIR__, 2)
                . '/modules/mod_sportsmanagement_playground_ticker/tmpl/default.php';

            return (string) ob_get_contents();
        } finally {
            ob_end_clean();
        }
    };

    $placeholder = 'https://site.example.test/images/com_sportsmanagement/database/placeholders/placeholder_150.png';
    $imageCases = [
        ['images/test stadium.png', 'https://cdn.example.test/media/images/test%20stadium.png'],
        ['https://images.example.test/stadium.png', 'https://images.example.test/stadium.png'],
        ['javascript:alert(1)', $placeholder],
        ['data:image/svg+xml,test', $placeholder],
        ['//attacker.example.test/stadium.png', $placeholder],
        ['../private.png', $placeholder],
        ['images/../private.png', $placeholder],
        ['images\..\private.png', $placeholder],
        ['images/x.png?redirect=https://bad.test', $placeholder],
        ['https://user:password@images.example.test/stadium.png', $placeholder],
        ["images/\x00broken.png", $placeholder],
    ];

    foreach ($imageCases as [$picture, $expected]) {
        $html = $render($picture, 'https://cdn.example.test/media/');

        if (!str_contains($html, 'src="' . htmlspecialchars($expected, ENT_QUOTES, 'UTF-8') . '"')) {
            throw new \RuntimeException('Unexpected stadium image URL for ' . json_encode($picture));
        }
    }

    // Invalid module database selections must not issue any SQL.
    $fallbackDb = new class implements \Joomla\Database\DatabaseInterface {};
    $dbSelector = new \ReflectionMethod($helper, 'databaseSelector');
    foreach ([0, '0', '000', 1, '1', '0001'] as $valid) {
        $expected = str_ends_with((string) $valid, '1') ? 1 : 0;
        if ($dbSelector->invoke($helper, new \Joomla\Registry\Registry([
            'cfg_which_database' => $valid,
        ]), $app) !== $expected) {
            throw new \RuntimeException('Valid legacy playground database selector was rejected.');
        }
    }
    foreach ([2, -1, '2', '-1', '1junk', '1.5', true, ['1'],
        '99999999999999999999'] as $invalid) {
        $badParams = new \Joomla\Registry\Registry([
            'p' => 0,
            'cfg_which_database' => $invalid,
        ]);
        if ($helper->getData($badParams, $app, $fallbackDb) !== []) {
            throw new \RuntimeException('Malformed playground database selector was accepted.');
        }
    }

    echo "Playground ticker picture server and image URL contracts OK\n";
}
