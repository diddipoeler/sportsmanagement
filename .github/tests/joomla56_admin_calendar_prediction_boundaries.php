<?php
/**
 * Runtime regressions for Joomla 5/6 federation, league and calendar inputs.
 *
 * Uses isolated Joomla field and query stubs to verify fail-closed database
 * filters, cycle-safe federation trees and supported prediction route IDs.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Diddipoeler\Component\SportsManagement\Administrator\Service {
    final class SportsManagementAdministratorApplicationResolver
    {
        public static object $app;

        /**
         * Return a prepared fake Joomla administrator application.
         *
         * @return object Application with input and user state.
         */
        public static function resolve(): object
        {
            return self::$app;
        }
    }
}
namespace Diddipoeler\Component\SportsManagement\Administrator\Field {
    class SportsManagementListField
    {
        /**
         * Supply a fake Joomla database for selected form-field options.
         *
         * @return \JsmSelectorDatabase In-memory database fixture.
         */
        protected function getSportsManagementDatabase(): \JsmSelectorDatabase
        {
            return \JsmSelectorDatabase::$instance;
        }

        /**
         * Return the inherited Joomla selector placeholder option.
         *
         * @return array<int,object> One default field option.
         */
        protected function getOptions(): array
        {
            return [(object) ['value' => '', 'text' => 'DEFAULT']];
        }
    }
}
namespace Diddipoeler\Component\SportsManagement\Site\Helper {
    final class SiteRouteHelper
    {
        public static array $last = [];

        /**
         * Capture a frontend component view without a Joomla router.
         *
         * @param string $view Requested frontend page.
         * @param array<string,mixed> $parameters Query parameters.
         * @return string Fake route path for assertions.
         */
        public static function view(string $view, array $parameters): string
        {
            self::$last = ['view' => $view, 'parameters' => $parameters];
            return '/index.php?view=' . $view;
        }
    }
}
namespace Joomla\Database {
    final class ParameterType
    {
        public const INTEGER = 'integer';
        public const STRING = 'string';
    }
}
namespace {
    final class JsmSelectorQuery
    {
        public array $bindings = [];
        public array $where = [];

        /**
         * Accept selected column expressions in a fluent Joomla query.
         *
         * @param mixed $columns SQL names.
         * @return self Current query.
         */
        public function select(mixed $columns): self { return $this; }

        /**
         * Accept the Joomla table name for a fake query.
         *
         * @param string $table Database table.
         * @return self Current query.
         */
        public function from(string $table): self { return $this; }

        /**
         * Store a WHERE expression for later assertions.
         *
         * @param string $where SQL condition with a bound parameter.
         * @return self Current query.
         */
        public function where(string $where): self
        {
            $this->where[] = $where;
            return $this;
        }

        /**
         * Record the typed parameter used in the Joomla query.
         *
         * @param string $name Named placeholder.
         * @param mixed $value Bound scalar.
         * @param string $type Joomla database binding type.
         * @return self Current query.
         */
        public function bind(string $name, mixed $value, string $type): self
        {
            $this->bindings[$name] = [$value, $type];
            return $this;
        }

        /**
         * Accept the order expression for the selector options.
         *
         * @param mixed $value SQL order clause.
         * @return self Current query.
         */
        public function order(mixed $value): self { return $this; }
    }

    final class JsmSelectorDatabase
    {
        public static self $instance;
        public int $queries = 0;
        public ?JsmSelectorQuery $last = null;
        public array $rows = [];

        /**
         * Create a harmless in-memory Joomla query.
         *
         * @return JsmSelectorQuery New query object.
         */
        public function createQuery(): JsmSelectorQuery { return new JsmSelectorQuery(); }

        /**
         * Create the same fake query through Joomla's legacy API alias.
         *
         * @param bool $new Request a new query builder.
         * @return JsmSelectorQuery New query.
         */
        public function getQuery(bool $new = true): JsmSelectorQuery { return $this->createQuery(); }

        /**
         * Return a quoted field name for SQL fixture assertions.
         *
         * @param string $name SQL name.
         * @param string|null $alias Optional alias.
         * @return string Quoted SQL name.
         */
        public function quoteName(string $name, ?string $alias = null): string
        {
            return '[' . $name . ']' . ($alias === null ? '' : ' AS [' . $alias . ']');
        }

        /**
         * Record a Joomla query without executing external SQL.
         *
         * @param JsmSelectorQuery $query Built query object.
         * @return void
         */
        public function setQuery(JsmSelectorQuery $query): void
        {
            $this->last = $query;
            ++$this->queries;
        }

        /**
         * Return preloaded database objects for league or federation lists.
         *
         * @return array<int,object> Fake database result rows.
         */
        public function loadObjectList(): array { return $this->rows; }
    }

    final class JsmSelectorApp
    {
        public object $input;
        public array $states = [];

        /**
         * Create a request with a mutable POST filter and selected ID.
         */
        public function __construct()
        {
            $this->input = new class {
                public string $view = 'leagues';
                public mixed $id = 1;
                public object $post;

                /**
                 * Initialise Joomla's nested form POST provider.
                 */
                public function __construct()
                {
                    $this->post = new class {
                        public mixed $filter = [];

                        /**
                         * Return the configured POST filter without coercion.
                         *
                         * @param string $name Input field.
                         * @param mixed $default Default filter.
                         * @param string $type Joomla filter type.
                         * @return mixed Raw fixture filter.
                         */
                        public function get(string $name, mixed $default, string $type): mixed
                        {
                            return $this->filter;
                        }
                    };
                }

                /**
                 * Return the requested administrator view.
                 *
                 * @param string $name Joomla input name.
                 * @param string $default Default view.
                 * @return string Configured view.
                 */
                public function getCmd(string $name, string $default = ''): string
                {
                    return $this->view;
                }

                /**
                 * Return the raw record ID as Joomla input data.
                 *
                 * @param string $name Requested field.
                 * @param mixed $default Default value.
                 * @param string $type Input filter name.
                 * @return mixed Selected fixture ID.
                 */
                public function get(string $name, mixed $default, string $type): mixed
                {
                    return $this->id;
                }
            };
        }

        /**
         * Provide input data used by native Joomla administrator fields.
         *
         * @return object Input fixture.
         */
        public function getInput(): object { return $this->input; }

        /**
         * Retrieve a stored Joomla administrator filter fallback.
         *
         * @param string $key User state key.
         * @param mixed $default Default value.
         * @return mixed Saved state or supplied default.
         */
        public function getUserState(string $key, mixed $default = null): mixed
        {
            return $this->states[$key] ?? $default;
        }
    }

    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);
    require_once $root . '/admin/src/Field/LeagueField.php';
    require_once $root . '/admin/src/Field/FederationslistField.php';
    require_once $root . '/modules/mod_sportsmanagement_calendar/src/Runtime/CalendarFunctions.php';
    require_once $root . '/site/src/Helper/PredictionRouteHelper.php';

    $app = new JsmSelectorApp();
    $db = new JsmSelectorDatabase();
    JsmSelectorDatabase::$instance = $db;
    \Diddipoeler\Component\SportsManagement\Administrator\Service\SportsManagementAdministratorApplicationResolver::$app = $app;

    $league = new \Diddipoeler\Component\SportsManagement\Administrator\Field\LeagueField();
    $leagueOptions = new ReflectionMethod($league, 'getOptions');
    $country = new ReflectionMethod($league, 'countryFilter');
    $association = new ReflectionMethod($league, 'associationFilter');
    $assertions = 0;

    set_error_handler(static function (int $level, string $message): never {
        throw new ErrorException($message, 0, $level);
    });

    try {
        foreach ([['DEU', 'DEU'], [' Germany ', 'Germany'], ['', ''], [['DEU'], null], ["DEU\n", null], [new stdClass(), null]] as [$raw, $expected]) {
            if ($country->invoke(null, $raw) !== $expected) {
                throw new RuntimeException('Unsafe administrator country selector accepted.');
            }
            ++$assertions;
        }

        foreach ([[0, 0], ['0007', 7], ['0000', 0], ['', 0], ['7junk', null], [true, null], [['7'], null], [-1, null], [str_repeat('9', 70), null]] as [$raw, $expected]) {
            if ($association->invoke(null, $raw) !== $expected) {
                throw new RuntimeException('Unsafe administrator association selector accepted.');
            }
            ++$assertions;
        }

        foreach ([['search_nation' => ['DEU']], ['search_associations_leagues' => '5junk']] as $filter) {
            $db->queries = 0;
            $app->input->post->filter = $filter;
            $result = $leagueOptions->invoke($league);

            if ($db->queries !== 0 || count($result) !== 1 || $result[0]->text !== 'DEFAULT') {
                throw new RuntimeException('Malformed league filters expanded a database query.');
            }
            ++$assertions;
        }

        $app->input->post->filter = ['search_nation' => 'DEU', 'search_associations_leagues' => '0007'];
        $db->rows = [(object) ['value' => 8, 'text' => 'Bundesliga']];
        $db->queries = 0;
        $result = $leagueOptions->invoke($league);

        if ($db->queries !== 1 || count($result) !== 2
            || $db->last?->bindings[':country'] !== ['DEU', 'string']
            || $db->last?->bindings[':association'] !== [7, 'integer']) {
            throw new RuntimeException('Valid league filters are no longer safely bound.');
        }
        ++$assertions;

        $federations = new \Diddipoeler\Component\SportsManagement\Administrator\Field\FederationslistField();
        $recordId = new ReflectionMethod($federations, 'recordId');

        foreach ([[1, 1], ['0002', 2], ['2junk', null], [['2'], null], [true, null], [str_repeat('9', 80), null], [0, null]] as [$raw, $expected]) {
            if ($recordId->invoke(null, $raw) !== $expected) {
                throw new RuntimeException('Malformed federation ID was accepted.');
            }
            ++$assertions;
        }

        if ($recordId->invoke(null, '000', true) !== 0) {
            throw new RuntimeException('Root federation parent ID parsing broke.');
        }
        ++$assertions;

        $db->rows = [
            (object) ['id' => 1, 'parent_id' => 0, 'name' => 'Root'],
            (object) ['id' => '0002', 'parent_id' => 1, 'name' => 'Child'],
            (object) ['id' => 3, 'parent_id' => 2, 'name' => 'Grandchild'],
            (object) ['id' => 1, 'parent_id' => 3, 'name' => 'Cycle'],
            (object) ['id' => '2junk', 'parent_id' => 1, 'name' => 'Bad'],
            (object) ['id' => 4, 'parent_id' => ['bad'], 'name' => 'Bad parent'],
        ];
        $tree = (new ReflectionMethod($federations, 'getTreeOptions'))->invoke($federations);

        if (array_map(static fn (object $row): string => $row->value, $tree) !== ['1', '2', '3']) {
            throw new RuntimeException('Federation cycles or invalid records leaked into tree.');
        }
        ++$assertions;

        $calendar = \Diddipoeler\Module\SportsManagementCalendar\Site\Runtime\CalendarFunctions::class;
        $selection = new ReflectionMethod($calendar, 'calendarSelection');

        foreach ([['0007', 1, 12, 6, 7], ['8junk', 1, 12, 6, 6], [[], 1, 12, 6, 6],
            [true, 1, 12, 6, 6], ["3\n", 1, 12, 6, 6], ['02026', 1970, 9999, 2025, 2026],
            [str_repeat('9', 70), 1970, 9999, 2025, 2025],
        ] as [$raw, $min, $max, $fallback, $expected]) {
            if ($selection->invoke(null, $raw, $min, $max, $fallback) !== $expected) {
                throw new RuntimeException('Calendar accepted an invalid date selector.');
            }
            ++$assertions;
        }

        if (!str_contains($calendar::monthList('0007'), '<option value="07" selected>')) {
            throw new RuntimeException('Calendar month selector lost zero-padded values.');
        }
        ++$assertions;

        $prediction = \Diddipoeler\Component\SportsManagement\Site\Helper\PredictionRouteHelper::class;
        $route = \Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper::class;

        foreach ([[1, 1], [0, 0], [-1, 0], [2, 0], [99, 0]] as [$raw, $expected]) {
            $prediction::entry(10, 0, 0, 0, 0, $raw);
            if (($route::$last['parameters']['cfg_which_database'] ?? null) !== $expected) {
                throw new RuntimeException('Unsupported prediction route database selection leaked.');
            }
            ++$assertions;
        }
    } finally {
        restore_error_handler();
    }

    echo "Joomla 5/6 admin/calendar/prediction input regressions: {$assertions} assertions passed.\n";
}
