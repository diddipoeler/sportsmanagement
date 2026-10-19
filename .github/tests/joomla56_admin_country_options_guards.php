<?php
/**
 * Joomla 5/6 administrator country options and safe flag regressions.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\Database {
    interface DatabaseInterface {}
}

namespace Joomla\CMS\Component {
    final class ComponentHelper
    {
        public static bool $css = false;

        public static function getParams(string $component): object
        {
            return new class {
                public function get(string $key, mixed $default = null): mixed
                {
                    return $key === 'cfg_flags_css' ? (int) ComponentHelper::$css : $default;
                }
            };
        }
    }
}

namespace Joomla\CMS\HTML {
    final class HTMLHelper
    {
        public static function _(string $task, string $value, string $label): object
        {
            return (object) ['value' => $value, 'text' => $label];
        }
    }
}

namespace Joomla\CMS\Language {
    final class Text
    {
        public static function _(string $key): string { return $key; }
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

namespace Joomla\CMS\Log {
    final class Log
    {
        public const ERROR = 1;
        public static array $errors = [];

        public static function add(string $message, int $level, string $category): void
        {
            self::$errors[] = [$message, $level, $category];
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/admin/src/Helper/CountryOptionsHelper.php';

    final class CountryQuery
    {
        public function select(mixed $v): self { return $this; }
        public function from(mixed $v): self { return $this; }
        public function where(mixed $v): self { return $this; }
    }

    final class CountryDatabase implements \Joomla\Database\DatabaseInterface
    {
        public int $queries = 0;
        public bool $throw = false;
        public mixed $result = 'DEU';
        public array $countries = [
            ['alpha3' => 'DEU', 'name' => 'Germany'],
            ['alpha3' => 'BAD<script>', 'name' => 'Invalid'],
            ['alpha3' => 'FRA', 'name' => 'France'],
        ];
        public ?object $country = null;

        public function createQuery(): CountryQuery
        {
            ++$this->queries;

            if ($this->throw) {
                throw new \RuntimeException('SQLSTATE private password=secret');
            }

            return new CountryQuery();
        }

        public function quoteName(string $name): string { return $name; }
        public function quote(string $s): string { return "'" . $s . "'"; }
        public function setQuery(object $query, int $offset = 0, int $limit = 0): void {}
        public function loadAssocList(): array { return $this->countries; }
        public function loadResult(): mixed { return $this->result; }
        public function loadObject(): ?object { return $this->country; }
    }

    $db = new CountryDatabase();
    $helper = \Diddipoeler\Component\SportsManagement\Administrator\Helper\CountryOptionsHelper::class;
    $safeUrl = new \ReflectionMethod($helper, 'imageUrl');

    foreach ([
        'images/flags/de.png' => 'https://sports.example/joomla/images/flags/de.png',
        '/images/flags/de.png' => 'https://sports.example/joomla/images/flags/de.png',
        'https://cdn.example/de.png' => 'https://cdn.example/de.png',
        '//evil.example/de.png' => '',
        'javascript:alert(1)' => '',
        'data:image/svg+xml,evil' => '',
        'images/../secret.png' => '',
        'images/%2e%2e/secret.png' => '',
        'images/%252e%252e/secret.png' => '',
        'images/%0dsecret.png' => '',
        'images/flag.png?cache=1' => '',
        'images\backslash.png' => '',
        'https://user:secret@cdn.example/de.png' => '',
    ] as $raw => $expected) {
        if ($safeUrl->invoke(null, $raw) !== $expected) {
            throw new \RuntimeException('Unsafe administrator country flag URL: ' . var_export($raw, true));
        }
    }

    $options = $helper::getOptions($db);

    if (array_map(static fn (object $o): string => $o->value, $options) !== ['FRA', 'DEU']) {
        throw new \RuntimeException('Administrator country options include malformed ISO codes or sort incorrectly.');
    }

    if ($helper::iso2To3($db, 'de') !== 'DEU') {
        throw new \RuntimeException('Valid alpha-2 to alpha-3 conversion failed.');
    }

    $queries = $db->queries;
    if ($helper::iso2To3($db, 'D_') !== 'DEU' || $helper::getFlag($db, 'B<script>') !== ''
        || $db->queries !== $queries) {
        throw new \RuntimeException('Malformed country codes unexpectedly queried the database.');
    }

    $db->country = (object) ['alpha2' => 'de', 'name' => 'Germany', 'picture' => ''];
    $flag = $helper::getFlag($db, 'DEU');

    if (!str_contains($flag, 'src="https://sports.example/joomla/images/com_sportsmanagement/database/flags/de.png"')) {
        throw new \RuntimeException('Valid administrator country flag disappeared.');
    }

    $db->country = (object) [
        'alpha2' => 'invalid-code',
        'name' => 'Example <country>',
        'picture' => 'javascript:alert(1)',
    ];

    if ($helper::getFlag($db, 'TST') !== '') {
        throw new \RuntimeException('Administrator country flag accepted an executable URI.');
    }

    \Joomla\CMS\Component\ComponentHelper::$css = true;
    if ($helper::getFlag($db, 'TST') !== '') {
        throw new \RuntimeException('Administrator flag CSS accepted an invalid country code.');
    }

    $db->throw = true;
    $before = count(\Joomla\CMS\Log\Log::$errors);

    if ($helper::getOptions($db) !== [] || $helper::iso2To3($db, 'FR') !== 'DEU'
        || $helper::getFlag($db, 'FRA') !== ''
        || count(\Joomla\CMS\Log\Log::$errors) !== $before + 3) {
        throw new \RuntimeException('Administrator country queries did not recover from failures.');
    }

    foreach (\Joomla\CMS\Log\Log::$errors as $entry) {
        if (!str_contains($entry[0], 'password=secret') || $entry[2] !== 'com_sportsmanagement') {
            throw new \RuntimeException('Failed country queries were not logged privately.');
        }
    }

    echo "Administrator country query and safe flag regressions passed.\n";
}
