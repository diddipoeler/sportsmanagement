<?php
/**
 * Regression checks for Random Player database selectors and safe media URLs.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\Uri {
    final class Uri
    {
        /**
         * Return the installation root used by relative image URL tests.
         *
         * @return string Simulated Joomla subdirectory installation URL.
         */
        public static function root(): string
        {
            return 'https://sports.example.test/subdir/';
        }
    }
}

namespace Joomla\CMS\Component {
    final class ComponentHelper
    {
        public static mixed $placeholder = '';

        /**
         * Provide placeholder component parameters for the media fallback test.
         *
         * @param string $name Requested Joomla component.
         * @return object Registry-like component settings fixture.
         */
        public static function getParams(string $name): object
        {
            return new class {
                /**
                 * Read the simulated player placeholder parameter.
                 *
                 * @param string $key Component configuration key.
                 * @param mixed $default Default when unset.
                 * @return mixed Fixture placeholder, including malformed values.
                 */
                public function get(string $key, mixed $default = null): mixed
                {
                    return ComponentHelper::$placeholder;
                }
            };
        }
    }
}

namespace {
    define('_JEXEC', 1);
    define('JPATH_ROOT', '/nonexistent-joomla-site');

    require dirname(__DIR__, 2)
        . '/modules/mod_sportsmanagement_randomplayer/src/Helper/RandomPlayerHelper.php';

    $class = \Diddipoeler\Module\SportsManagementRandomPlayer\Site\Helper\RandomPlayerHelper::class;
    $helper = new $class();
    $selector = new \ReflectionMethod($class, 'nonNegativeSelector');
    $media = new \ReflectionMethod($class, 'mediaUrl');
    $picture = new \ReflectionMethod($class, 'picture');

    foreach ([
        [0, 0], [7, 7], ['0007', 7], ['000', 0],
        [true, null], [false, null], [['7'], null], ['7bad', null],
        ['-1', null], [PHP_INT_MAX . '0', null],
    ] as [$raw, $expected]) {
        if ($selector->invoke(null, $raw) !== $expected) {
            throw new \RuntimeException('Random Player malformed database selector accepted.');
        }
    }

    foreach ([
        ['images/player logo.png', 'https://sports.example.test/subdir/images/player%20logo.png'],
        ['/images/team.png', 'https://sports.example.test/subdir/images/team.png'],
        ['https://cdn.example.test/player.png', 'https://cdn.example.test/player.png'],
        ['https://user:secret@cdn.example.test/player.png', ''],
        ['https://bad host/player.png', ''],
        ['javascript:alert(1)', ''],
        ['//cdn.example.test/player.png', ''],
        ['images/../private.png', ''],
        ['images/%2e%2e/private.png', ''],
        ['images/player.png?redirect=x', ''],
        ['images/player.png#fragment', ''],
        ['images\\player.png', ''],
    ] as [$raw, $expected]) {
        if ($media->invoke($helper, $raw) !== $expected) {
            throw new \RuntimeException('Random Player unsafe image URL accepted: ' . $raw);
        }
    }

    \Joomla\CMS\Component\ComponentHelper::$placeholder = ['invalid'];
    if ($picture->invoke($helper, '') !== '') {
        throw new \RuntimeException('Random Player invalid image placeholder was accepted.');
    }

    echo "Random Player selector and media regression checks passed.\n";
}
