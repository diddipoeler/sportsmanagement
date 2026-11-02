<?php
/**
 * Regression checks for AJAX top navigation database and federation settings.
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

namespace Joomla\Registry {
    final class Registry
    {
        /**
         * Create a minimal Joomla registry fixture.
         *
         * @param array<string, mixed> $values Parameter data.
         */
        public function __construct(private array $values = [])
        {
        }

        /**
         * Fetch a module setting or its default.
         *
         * @param string $key Configuration key.
         * @param mixed $default Fallback value.
         * @return mixed Stored parameter.
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
        /**
         * Return the default component settings for selector fallback.
         *
         * @param string $name Joomla component identifier.
         * @return \Joomla\Registry\Registry Component settings.
         */
        public static function getParams(string $name): \Joomla\Registry\Registry
        {
            return new \Joomla\Registry\Registry(['cfg_which_database' => '0']);
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require dirname(__DIR__, 2)
        . '/modules/mod_sportsmanagement_ajax_top_navigation_menu/src/Helper/AjaxTopNavigationHelper.php';

    final class FakeInput
    {
        public array $setCalls = [];

        /**
         * Construct the simulated query value for a database selector.
         *
         * @param mixed $value Request value or null when missing.
         */
        public function __construct(public mixed $value = null)
        {
        }

        /**
         * Return the unfiltered request input when present.
         *
         * @param string $key Requested input key.
         * @param mixed $default Default value.
         * @param string $filter Input filter.
         * @return mixed Simulated input value.
         */
        public function get(string $key, mixed $default = null, string $filter = 'CMD'): mixed
        {
            return $this->value ?? $default;
        }

        /**
         * Record any attempt to replace the request selector.
         *
         * @param string $key Input field name.
         * @param mixed $value Updated value.
         * @return void
         */
        public function set(string $key, mixed $value): void
        {
            $this->setCalls[] = [$key, $value];
        }
    }

    final class FakeApplication implements \Joomla\CMS\Application\CMSApplicationInterface
    {
        /**
         * Provide fake Joomla request input.
         *
         * @param FakeInput $input Request fixture.
         */
        public function __construct(public FakeInput $input)
        {
        }

        /**
         * Identify this fixture as a site application.
         *
         * @param string $client Requested application context.
         * @return bool Whether the site client is requested.
         */
        public function isClient(string $client): bool
        {
            return $client === 'site';
        }

        /**
         * Return the test request input.
         *
         * @return FakeInput Current input fixture.
         */
        public function getInput(): FakeInput
        {
            return $this->input;
        }
    }

    $helper = new \Diddipoeler\Module\SportsManagementAjaxTopNavigationMenu\Site\Helper\AjaxTopNavigationHelper();
    $class = $helper::class;
    $selector = new \ReflectionMethod($class, 'databaseSelector');
    $text = new \ReflectionMethod($class, 'textSetting');
    $federations = new \ReflectionMethod($class, 'federationRows');

    foreach ([
        [0, 0], ['0', 0], ['0007', 7], [7, 7],
        [true, null], [false, null], [['7'], null],
        ['7x', null], ['-7', null], ['7.0', null],
        [PHP_INT_MAX . '0', null], [new \stdClass(), null],
    ] as [$value, $expected]) {
        if ($selector->invoke(null, $value) !== $expected) {
            throw new \RuntimeException('AJAX navigation accepted an invalid database selector.');
        }
    }

    foreach ([
        ['Label', 'Label'], ['', ''], [false, ''],
        [['Label'], ''], [new \stdClass(), ''],
    ] as [$value, $expected]) {
        if ($text->invoke(null, $value) !== $expected) {
            throw new \RuntimeException('AJAX navigation cast malformed text parameters.');
        }
    }

    $valid = (object) ['id' => '0007', 'name' => 'UEFA'];
    $rows = [
        $valid,
        false,
        ['id' => 2, 'name' => 'Fake'],
        (object) ['id' => 0, 'name' => 'Zero'],
        (object) ['id' => true, 'name' => 'Boolean'],
        (object) ['id' => 12, 'name' => ['Bad']],
        (object) ['id' => 13, 'name' => '  '],
        (object) ['id' => PHP_INT_MAX . '0', 'name' => 'Overflow'],
    ];
    if ($federations->invoke(null, $rows) !== [$valid]
        || $federations->invoke(null, (object) ['id' => 7]) !== []) {
        throw new \RuntimeException('AJAX navigation accepted malformed federation rows.');
    }

    foreach ([true, ['2'], '2junk', PHP_INT_MAX . '0', -3] as $bad) {
        $input = new FakeInput($bad);
        $result = $helper->getData(new \Joomla\Registry\Registry(['cfg_which_database' => '2']), (object) [], new FakeApplication($input));

        if ($result !== [] || $input->setCalls !== []) {
            throw new \RuntimeException('AJAX navigation accessed invalid external database selection.');
        }
    }

    $input = new FakeInput();
    if ($helper->getData(new \Joomla\Registry\Registry(['cfg_which_database' => ['2']]), (object) [], new FakeApplication($input)) !== []
        || $input->setCalls !== []) {
        throw new \RuntimeException('AJAX navigation accepted malformed configured database selection.');
    }

    echo "AJAX top navigation parameter validation passed.\n";
}
