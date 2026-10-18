<?php
/**
 * Joomla 5/6 country flag image URI and attributes regression coverage.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\CMS\Component {
    final class ComponentHelper
    {
        public static string $placeholder = '';
        public static int $css = 0;

        public static function getParams(string $component): object
        {
            return new class {
                public function get(string $key, mixed $default = null): mixed
                {
                    return match ($key) {
                        'cfg_flags_css' => ComponentHelper::$css,
                        'ph_flags' => ComponentHelper::$placeholder,
                        default => $default,
                    };
                }
            };
        }
    }
}

namespace Joomla\CMS\Language {
    final class Text
    {
        public static function _(string $text): string
        {
            return $text;
        }
    }
}

namespace Joomla\CMS\Uri {
    final class Uri
    {
        public static function root(): string
        {
            return 'https://sports.example/joomla/';
        }
    }
}

namespace Joomla\Database {
    interface DatabaseInterface {}
}

namespace Diddipoeler\Component\SportsManagement\Site\Service {
    final class SportsManagementSiteApplicationResolver
    {
        public static function resolve(): object
        {
            return new class {
                public function getInput(): object
                {
                    return new class {
                        public function getInt(string $key, int $default = 0): int
                        {
                            return $default;
                        }
                    };
                }
            };
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/site/src/Helper/CountryPresentationHelper.php';

    $class = \Diddipoeler\Component\SportsManagement\Site\Helper\CountryPresentationHelper::class;
    $safeSource = new \ReflectionMethod($class, 'safeFlagSource');

    foreach ([
        'images/flags/de.png' => 'https://sports.example/joomla/images/flags/de.png',
        '/images/flags/de.png' => 'https://sports.example/joomla/images/flags/de.png',
        'https://cdn.example/flag.png' => 'https://cdn.example/flag.png',
        '' => '',
        '//evil.example/image.png' => '',
        'javascript:alert(1)' => '',
        'data:text/html,evil' => '',
        'images/../secret.png' => '',
        'images/%2e%2e/secret.png' => '',
        'images/%252e%252e/secret.png' => '',
        'images/%0dlogo.png' => '',
        'images/flag.png?bad=1' => '',
        'images/flag.png#fragment' => '',
        'images\backslash.png' => '',
        'https://user:secret@cdn.example/flag.png' => '',
    ] as $input => $expected) {
        $value = $safeSource->invoke(null, $input);

        if ($value !== $expected) {
            throw new \RuntimeException('Country flag URI invalid for ' . var_export($input, true));
        }
    }

    $cache = new \ReflectionProperty($class, 'countries');
    $cache->setValue(null, [
        '0:DEU' => (object) ['name' => 'Germany', 'alpha2' => 'de', 'picture' => ''],
        '0:TST' => (object) ['name' => 'Test', 'alpha2' => 'invalid-code', 'picture' => 'images/test.png'],
    ]);

    $image = $class::flag('DEU', 'class="country-flag" width="20" onerror="alert(1)" title="A & B"');

    if (!str_contains($image, 'src="https://sports.example/joomla/images/com_sportsmanagement/database/flags/de.png"')
        || !str_contains($image, 'class="country-flag"')
        || !str_contains($image, 'title="A &amp; B"')
        || str_contains($image, 'onerror')
        || !str_contains($image, 'alt="Germany"')) {
        throw new \RuntimeException('Country flag HTML attributes or image source are unsafe.');
    }

    $image = $class::flag('TST');

    if (!str_contains($image, 'src="https://sports.example/joomla/images/test.png"')
        || str_contains($image, 'invalid-code')) {
        throw new \RuntimeException('Invalid ISO alpha2 code was used as a CSS or image path.');
    }

    \Joomla\CMS\Component\ComponentHelper::$css = 1;
    $image = $class::flag('TST');

    if ($image !== '') {
        throw new \RuntimeException('Invalid flag CSS country code was rendered.');
    }

    echo "Joomla 5/6 country flag path and HTML safety passed.\n";
}
