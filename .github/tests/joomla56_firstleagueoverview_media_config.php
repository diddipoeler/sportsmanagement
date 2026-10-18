<?php
/**
 * Joomla 5/6 first-league federation images, flags and selectors.
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

namespace Joomla\CMS\Uri {
    final class Uri
    {
        public static function root(): string
        {
            return 'https://sports.example/joomla/';
        }
    }
}

namespace Joomla\Registry {
    final class Registry
    {
        public function __construct(private array $values = []) {}

        public function get(string $key, mixed $default = null): mixed
        {
            return $this->values[$key] ?? $default;
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2)
        . '/modules/mod_sportsmanagement_firstleagueoverview/src/Helper/FirstLeagueOverviewHelper.php';

    $helper = new \Diddipoeler\Module\SportsManagementFirstLeagueOverview\Site\Helper\FirstLeagueOverviewHelper();
    $mediaUrl = new ReflectionMethod($helper, 'mediaUrl');
    $selector = new ReflectionMethod($helper, 'databaseSelector');
    $flags = new ReflectionMethod($helper, 'flagHtml');
    $federations = new ReflectionMethod($helper, 'getFederations');

    foreach ([
        'images/fed.png' => 'https://sports.example/joomla/images/fed.png',
        '/images/fed.png' => 'https://sports.example/joomla/images/fed.png',
        'https://cdn.example/uefa.png' => 'https://cdn.example/uefa.png',
        '' => '',
        '//external.example/x.png' => '',
        'javascript:alert(1)' => '',
        'data:image/svg+xml,evil' => '',
        'images/../secret.png' => '',
        'images/%2e%2e/secret.png' => '',
        'images/%252e%252e/secret.png' => '',
        'images/%0dhidden.png' => '',
        'images/flag.png?x=1' => '',
        'images\backslash.png' => '',
        'https://user:password@cdn.example/flag.png' => '',
    ] as $source => $expected) {
        $actual = $mediaUrl->invoke(null, $source);

        if ($actual !== $expected) {
            throw new \RuntimeException(
                'Invalid federation image URL for ' . var_export($source, true)
                . ', actual: ' . var_export($actual, true)
            );
        }
    }

    foreach ([
        [0, 0], [1, 1], [7, 7], ['0007', 7],
        ['000', 0], ['2', 2], [-1, 0], [true, 0],
        [false, 0], [['1'], 0], ['1bad', 0], ['1.5', 0],
        ['999999999999999999999', 0],
    ] as [$value, $expected]) {
        if ($selector->invoke(null, $value) !== $expected) {
            throw new \RuntimeException('Invalid first-league database selector value.');
        }
    }

    $normal = new \Joomla\Registry\Registry(['cfg_flags_css' => 0]);
    $css = new \Joomla\Registry\Registry(['cfg_flags_css' => 1]);

    $safe = $flags->invoke($helper, (object) [
        'country' => 'DEU',
        'alpha2' => 'de',
        'country_label' => 'Germany',
    ], $normal);

    if (!str_contains($safe, 'src="https://sports.example/joomla/images/com_sportsmanagement/database/flags/de.png"')) {
        throw new \RuntimeException('Valid first-league flag path was rejected.');
    }

    $bad = (object) [
        'country' => 'bad<script>',
        'alpha2' => '../evil',
        'country_label' => 'Evil <script>',
        'country_picture' => 'javascript:alert(1)',
    ];

    if ($flags->invoke($helper, $bad, $normal) !== ''
        || $flags->invoke($helper, $bad, $css) !== '') {
        throw new \RuntimeException('Invalid first-league flag markup was rendered.');
    }

    $db = new class implements \Joomla\Database\DatabaseInterface {
        public function createQuery(): object
        {
            return new class {
                public function select(array $cols): self { return $this; }
                public function from(string $table): self { return $this; }
                public function order(string $order): self { return $this; }
            };
        }
        public function quoteName(string $name): string { return $name; }
        public function setQuery(object $query): void {}
        public function loadObjectList(): array
        {
            return [
                (object) ['id' => 1, 'name' => 'UEFA', 'picture' => '../secret.png'],
                (object) ['id' => 2, 'name' => 'FIFA', 'picture' => 'images/fifa.png'],
                (object) ['id' => 3, 'name' => 'CAF', 'picture' => 'https://cdn.example/caf.png'],
            ];
        }
    };

    $rows = $federations->invoke($helper, $db);

    if ($rows[1]->picture_url !== ''
        || $rows[2]->picture_url !== 'https://sports.example/joomla/images/fifa.png'
        || $rows[3]->picture_url !== 'https://cdn.example/caf.png') {
        throw new \RuntimeException('Federation picture URLs were not safely normalized.');
    }

    echo "First-league image and configuration validation passed.\n";
}
