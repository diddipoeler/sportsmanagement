<?php
/**
 * Verify Joomla 5/6 club-logo fallback, extra-field bounds and quote-log privacy.
 *
 * @version 5.6.0
 * @author SportsManagement contributors
 * @copyright Copyright (C) 2026 SportsManagement contributors
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\CMS\Component {
    /** Standalone component parameter provider. */
    final class ComponentHelper {
        public static mixed $logo = null;
        /**
         * Return test component settings.
         * @param string $name Component identifier.
         * @return object Test settings object.
         */
        public static function getParams(string $name): object {
            return new class {
                /**
                 * Read the configured logo placeholder.
                 * @param string $key Parameter name.
                 * @param mixed $default Default placeholder.
                 * @return mixed Configured placeholder value.
                 */
                public function get(string $key, mixed $default = null): mixed {
                    return ComponentHelper::$logo;
                }
            };
        }
    }
}
namespace Joomla\CMS\HTML {
    /** Standalone Joomla HTML fixture. */
    final class HTMLHelper {
        public static array $attributes = [];
        /**
         * Record image attributes for frontend assertions.
         * @param string $url Image URL.
         * @param string $alt Alternative text.
         * @param array $attributes HTML attributes.
         * @return string Test marker.
         */
        public static function image(string $url, string $alt, array $attributes): string {
            self::$attributes = $attributes;
            return $url;
        }
        /**
         * Construct a Joomla-style select option.
         * @param string $task HTML task.
         * @param string $value Stored value.
         * @param string $label Display label.
         * @return object Option record.
         */
        public static function _(string $task, string $value, string $label): object {
            return (object) ['value' => $value, 'text' => $label];
        }
    }
}
namespace Joomla\Database {
    /** Test constants used for Joomla query bindings. */
    final class ParameterType {
        public const INTEGER = 1;
        public const STRING = 2;
    }
}
namespace Diddipoeler\Component\SportsManagement\Administrator\Field {
    /** Connect the helper to an isolated query fixture. */
    trait SportsManagementDatabaseTrait {
        /**
         * Supply a test database for the isolated option lookup.
         * @return object Fake Joomla query driver.
         */
        private function getSportsManagementDatabase(): object {
            return \TestSelectDatabase::$db;
        }
    }
}
namespace {
    /** Minimal fluent query and option record provider. */
    final class TestSelectDatabase {
        public static self $db;
        public object $row;
        /**
         * Return the fluent query builder fixture.
         * @return object Query builder.
         */
        public function createQuery(): object {
            return new class {
                /**
                 * Accept SQL select arguments.
                 * @param mixed ...$args Query input.
                 * @return self Fluent builder.
                 */
                public function select(mixed ...$args): self { return $this; }
                /**
                 * Accept SQL from arguments.
                 * @param mixed ...$args Query input.
                 * @return self Fluent builder.
                 */
                public function from(mixed ...$args): self { return $this; }
                /**
                 * Accept SQL where arguments.
                 * @param mixed ...$args Query input.
                 * @return self Fluent builder.
                 */
                public function where(mixed ...$args): self { return $this; }
                /**
                 * Accept bound query arguments.
                 * @param mixed ...$args Query input.
                 * @return self Fluent builder.
                 */
                public function bind(mixed ...$args): self { return $this; }
            };
        }
        /**
         * Return quoted column name for a test query.
         * @param string $name SQL column name.
         * @return string Escaped SQL identifier.
         */
        public function quoteName(string $name): string { return '[' . $name . ']'; }
        /**
         * Accept a query without contacting a database.
         * @param mixed $query SQL statement.
         * @param int $offset First selected row.
         * @param int $limit Maximum row count.
         * @return void
         */
        public function setQuery(mixed $query, int $offset=0, int $limit=0): void {}
        /**
         * Return a configured option definition.
         * @return object Legacy select option row.
         */
        public function loadObject(): object { return $this->row; }
    }

    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);
    require_once $root . '/site/src/Helper/ClubLogoHelper.php';
    require_once $root . '/admin/src/Helper/ExtraSelectOptionsHelper.php';

    $logo = \Diddipoeler\Component\SportsManagement\Site\Helper\ClubLogoHelper::class;
    \Joomla\CMS\Component\ComponentHelper::$logo = ['invalid'];
    if ($logo::render() !== '') {
        throw new \RuntimeException('Malformed placeholder must be ignored.');
    }
    \Joomla\CMS\Component\ComponentHelper::$logo = 'images/club.png';
    if ($logo::render('', '', 1, true) !== 'images/club.png') {
        throw new \RuntimeException('Valid logo placeholder no longer renders.');
    }
    $attributes = \Joomla\CMS\HTML\HTMLHelper::$attributes;
    if (isset($attributes['height'])
        || !str_contains($attributes['style'] ?? '', 'height:auto;')
        || !str_contains($attributes['style'] ?? '', 'padding:1px;')) {
        throw new \RuntimeException('Invalid HTML image dimensions generated.');
    }

    TestSelectDatabase::$db = new TestSelectDatabase();
    $helper = new \Diddipoeler\Component\SportsManagement\Administrator\Helper\ExtraSelectOptionsHelper();
    foreach ([['../view', 'field'], ['view ', 'field'], ['view', 'bad name']] as [$view, $field]) {
        if ($helper->getOptions($view, $field) !== []) {
            throw new \RuntimeException('Malformed extra-field identifier accepted.');
        }
    }
    TestSelectDatabase::$db->row = (object) [
        'select_columns' => 'one,two,three',
        'select_values' => "First,Second,Bad\nLabel",
    ];
    $values = $helper->getOptions('club', 'example');
    if (count($values) !== 2 || $values[0]->text !== 'First' || $values[1]->text !== 'Second') {
        throw new \RuntimeException('Malformed select option not filtered.');
    }
    TestSelectDatabase::$db->row = (object) [
        'select_columns' => 'one',
        'select_values' => implode(',', array_fill(0, 257, 'value')),
    ];
    if ($helper->getOptions('club', 'example') !== []) {
        throw new \RuntimeException('Excessive select labels not rejected.');
    }

    $rquotes = file_get_contents($root . '/modules/mod_sportsmanagement_rquotes/src/Helper/RquotesHelper.php');
    if (!is_string($rquotes)
        || !str_contains($rquotes, 'SportsManagement Rquotes query failed (')
        || str_contains($rquotes, "Rquotes query failed: ' . " . '$e->getMessage()')) {
        throw new \RuntimeException('Random quotes logging privacy regressed.');
    }

    echo "Joomla 5/6 club logos, admin extra selects and Rquotes error privacy: OK\n";
}
