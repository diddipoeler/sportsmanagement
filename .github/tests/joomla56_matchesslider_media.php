<?php
/**
 * Joomla 5/6 Matches Slider remote image server and logo URL regressions.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\Registry {
    final class Registry
    {
        /**
         * Store fake component configuration values for slider media checks.
         *
         * @param array<string,mixed> $values Raw Joomla module settings.
         */
        public function __construct(private array $values = [])
        {
        }

        /**
         * Return configured slider values or Joomla's requested default.
         *
         * @param string $key Registry option name.
         * @param mixed $default Value for a missing option.
         * @return mixed Configured value or supplied default.
         */
        public function get(string $key, mixed $default = null): mixed
        {
            return $this->values[$key] ?? $default;
        }
    }
}

namespace Joomla\CMS\Component {
    final class ComponentHelper
    {
        public static \Joomla\Registry\Registry $params;

        /**
         * Supply the component's active configuration to Matches Slider.
         *
         * @param string $component Joomla component identifier.
         * @return \Joomla\Registry\Registry Current test configuration.
         */
        public static function getParams(string $component): \Joomla\Registry\Registry
        {
            return self::$params;
        }
    }
}

namespace Joomla\CMS\Uri {
    final class Uri
    {
        /**
         * Return the example Joomla installation root with a subdirectory.
         *
         * @return string Local site base used for internal database images.
         */
        public static function root(): string
        {
            return 'https://site.example.test/subdir/';
        }
    }
}

namespace {
    \defined('_JEXEC') || define('_JEXEC', 1);

    require_once dirname(__DIR__, 2)
        . '/modules/mod_sportsmanagement_matchesslider/src/Helper/MatchesSliderHelper.php';

    $helper = new \Diddipoeler\Module\SportsManagementMatchesSlider\Site\Helper\MatchesSliderHelper();
    $server = new \ReflectionMethod($helper, 'pictureServer');
    $media = new \ReflectionMethod($helper, 'mediaUrl');

    $base = 'https://site.example.test/subdir/';
    foreach ([
        ['https://cdn.example.test/assets/', 'https://cdn.example.test/assets/'],
        ['http://cdn.example.test/assets', 'http://cdn.example.test/assets/'],
        ['https://user:secret@cdn.example.test/images/', ''],
        ['https://user@cdn.example.test/images/', ''],
        ['https://cdn.example.test/images?token=1', ''],
        ['https://cdn.example.test/images#fragment', ''],
        ['javascript:alert(1)', ''],
        ['file:///etc/passwd', ''],
        ['//cdn.example.test/images', ''],
        ["https://cdn.example.test/\nassets", ''],
        ['https://cdn.example.test/images\evil', ''],
        ['', ''],
    ] as [$configured, $expected]) {
        \Joomla\CMS\Component\ComponentHelper::$params = new \Joomla\Registry\Registry([
            'cfg_which_database_server' => $configured,
        ]);
        $actual = $server->invoke($helper, 1);

        if ($actual !== $expected) {
            throw new \RuntimeException(
                'Unexpected remote slider image server for ' . json_encode($configured)
            );
        }
    }

    // Invalid external database image servers must not redirect relative
    // picture paths to local files in a different Joomla database.
    if ($media->invoke($helper, 'images/example.png', '') !== '') {
        throw new \RuntimeException('Invalid external slider server exposed a local image URL.');
    }

    // Internal database mode must not use a configured external server.
    \Joomla\CMS\Component\ComponentHelper::$params = new \Joomla\Registry\Registry([
        'cfg_which_database_server' => 'https://cdn.example.test/assets',
    ]);
    if ($server->invoke($helper, 0) !== $base) {
        throw new \RuntimeException('Internal slider images were redirected to an external server');
    }

    foreach ([
        ['images/home team.png', 'https://cdn.example.test/assets/images/home%20team.png'],
        ['/images/home.png', 'https://cdn.example.test/assets/images/home.png'],
        ['https://images.example.test/team.png', 'https://images.example.test/team.png'],
        ['http://images.example.test/team.png', 'http://images.example.test/team.png'],
        ['', ''],
        ['javascript:alert(1)', ''],
        ['data:image/svg+xml,<svg>', ''],
        ['file:///etc/passwd', ''],
        ['//evil.example.test/logo.png', ''],
        ['../private.png', ''],
        ['images/../private.png', ''],
        ['images/./team.png', ''],
        ['images/%2e%2e/private.png', ''],
        ['images/team.png?redirect=evil', ''],
        ['images\..\private.png', ''],
        ['https://user:secret@images.example.test/logo.png', ''],
        ["https://images.example.test/\nlogo.png", ''],
    ] as [$input, $expected]) {
        $actual = $media->invoke($helper, $input, 'https://cdn.example.test/assets/');

        if ($actual !== $expected) {
            throw new \RuntimeException('Unexpected slider logo URL for ' . json_encode($input));
        }
    }

    echo "Matches Slider server and logo URL safety regressions OK\n";
}
