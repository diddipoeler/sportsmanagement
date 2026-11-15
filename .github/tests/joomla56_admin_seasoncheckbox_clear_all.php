<?php
/**
 * Joomla 5/6 season checkbox field "clear all" and native query regression.
 *
 * Renders the actual field using Joomla form/application/database doubles.
 * Team edits must submit an empty selection, while person forms are unchanged.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\Form {
    class FormField
    {
        protected $type = '';
        protected string $id = 'jform_season_ids';
        protected string $name = 'jform[season_ids]';
        protected array $element = [];

        /**
         * Supply trusted target table and record foreign-key attributes.
         *
         * @param string $table Association table suffix.
         * @param string $key Association record ID column.
         */
        public function __construct(string $table = 'season_team_id', string $key = 'team_id')
        {
            $this->element = ['targettable' => $table, 'targetid' => $key, 'class' => 'inputbox'];
        }
    }
}

namespace Joomla\CMS\Application {
    class AdministratorApplication
    {
        /**
         * Return a Joomla-like input with a saved team identifier.
         *
         * @return object Integer-filtered simulated request.
         */
        public function getInput(): object
        {
            return new class {
                /**
                 * Return the currently edited team ID.
                 *
                 * @param string $key Requested input key.
                 * @param int $default Missing value fallback.
                 * @return int Existing team record.
                 */
                public function getInt(string $key, int $default = 0): int
                {
                    return $key === 'id' ? 5 : $default;
                }
            };
        }
    }
}

namespace Joomla\CMS {
    final class Factory
    {
        /**
         * Provide the administrator application through the service container.
         *
         * @return object Minimal Joomla dependency container.
         */
        public static function getContainer(): object
        {
            return new class {
                /**
                 * Resolve the active Joomla backend application.
                 *
                 * @param string $service Requested PHP class name.
                 * @return \Joomla\CMS\Application\AdministratorApplication Test backend app.
                 */
                public function get(string $service): \Joomla\CMS\Application\AdministratorApplication
                {
                    return new \Joomla\CMS\Application\AdministratorApplication();
                }
            };
        }
    }
}

namespace Joomla\CMS\Component {
    final class ComponentHelper
    {
        /**
         * Supply an empty component preference registry.
         *
         * @param string $option Joomla extension name.
         * @return object Read-only preference stub.
         */
        public static function getParams(string $option): object
        {
            return new class {
                /**
                 * Return a default for every optional component setting.
                 *
                 * @param string $key Requested setting.
                 * @param mixed $fallback Setting default.
                 * @return mixed Default value.
                 */
                public function get(string $key, mixed $fallback = null): mixed { return $fallback; }
            };
        }
    }
}

namespace Joomla\CMS\Language {
    final class Text
    {
        /**
         * Keep translated labels deterministic in HTML fixture checks.
         *
         * @param string $key Joomla translation token.
         * @return string Unchanged label.
         */
        public static function _(string $key): string { return $key; }
    }
}

namespace Joomla\CMS\HTML {
    final class HTMLHelper
    {
        /**
         * Provide a stable placeholder for optional person select inputs.
         *
         * @param string $type Requested HTML helper.
         * @param mixed ...$args Joomla HTML select arguments.
         * @return string Empty select markup placeholder.
         */
        public static function _(string $type, mixed ...$args): string { return ''; }
    }
}

namespace Diddipoeler\Component\SportsManagement\Administrator\Field {
    trait SportsManagementDatabaseTrait
    {
        /**
         * Supply a query-recording native Joomla database to the field.
         *
         * @return \SeasonCheckboxTestDatabase Native query fixture.
         */
        protected function getSportsManagementDatabase(): object
        {
            return new \SeasonCheckboxTestDatabase();
        }
    }
}

namespace {
    use Diddipoeler\Component\SportsManagement\Administrator\Field\SeasoncheckboxField;

    define('_JEXEC', 1);

    final class SeasonCheckboxTestQuery
    {
        /**
         * Return this fluent SQL query for standard Joomla query builders.
         *
         * @param string $name Called SQL query-builder method.
         * @param array<mixed> $arguments Builder options.
         * @return static Same mutable query builder.
         */
        public function __call(string $name, array $arguments): static { return $this; }
    }

    final class SeasonCheckboxTestDatabase
    {
        public static int $nativeQueries = 0;

        /**
         * Construct Joomla 5/6 queries through the supported createQuery API.
         *
         * @return SeasonCheckboxTestQuery Query builder fixture.
         */
        public function createQuery(): SeasonCheckboxTestQuery
        {
            ++self::$nativeQueries;
            return new SeasonCheckboxTestQuery();
        }

        /**
         * Reject the older getQuery(true) entry point under Joomla 6.
         *
         * @param bool $newQuery Whether a new query is requested.
         * @return never This migration test disallows getQuery().
         */
        public function getQuery(bool $newQuery = true): never
        {
            throw new \RuntimeException('Season checkbox used legacy getQuery().');
        }

        /**
         * Return a quoted column or table name, optionally with its alias.
         *
         * @param string $name SQL identifier.
         * @param string|null $alias Optional alias.
         * @return string Quoted SQL identifier.
         */
        public function quoteName(string $name, ?string $alias = null): string
        {
            return '"' . $name . '"' . ($alias !== null ? ' AS "' . $alias . '"' : '');
        }

        /**
         * Accept the query built for season options or assignments.
         *
         * @param object $query Built native Joomla SQL query.
         * @return static Same database fixture.
         */
        public function setQuery(object $query): static { return $this; }

        /**
         * Expose one season checkbox record in the test editor.
         *
         * @return array<int,object> Season records.
         */
        public function loadObjectList(): array
        {
            return [(object) ['value' => 12, 'text' => 'Season 12']];
        }

        /**
         * Return the existing team-season association by its ID.
         *
         * @param string $key Key column requested by Joomla.
         * @return array<int,array<string,string>> Existing season display values.
         */
        public function loadAssocList(string $key): array
        {
            return [12 => ['teamname' => 'First Team', 'season_teamname' => 'Other Team']];
        }

        /**
         * Simulate an empty sports-type name for person forms.
         *
         * @return string No person sports type.
         */
        public function loadResult(): string { return ''; }
    }

    /**
     * Assert actual HTML or counters without relying on zend.assertions.
     *
     * @param bool $condition Whether the field met the checked contract.
     * @param string $message Failure description.
     * @return void
     */
    function seasonFieldAssert(bool $condition, string $message): void
    {
        if (!$condition) {
            throw new \RuntimeException($message);
        }
    }

    require_once dirname(__DIR__, 2) . '/admin/src/Field/SeasoncheckboxField.php';
    $getInput = new \ReflectionMethod(SeasoncheckboxField::class, 'getInput');

    $team = $getInput->invoke(new SeasoncheckboxField());
    seasonFieldAssert(is_string($team), 'Team season form did not render.');
    seasonFieldAssert(str_starts_with($team,
        '<input type="hidden" name="jform[season_ids][]" value="">'),
        'Team checkbox form cannot express removing all seasons.');
    seasonFieldAssert(str_contains($team, 'type="checkbox"')
        && str_contains($team, 'value="12"'),
        'Existing team season checkbox did not render.');
    seasonFieldAssert(str_contains($team, ' checked'),
        'Existing team season assignment was not selected.');

    // Person-season forms share this field but must not acquire the
    // team-specific delete-all hidden control.
    $person = $getInput->invoke(new SeasoncheckboxField('season_person_id', 'person_id'));
    seasonFieldAssert(!str_contains($person, 'type="hidden"'),
        'Person season association form was unexpectedly changed.');

    $invalid = $getInput->invoke(new SeasoncheckboxField('arbitrary_table', 'id'));
    seasonFieldAssert(str_contains($invalid, 'Invalid season assignment target.'),
        'Invalid association table was not rejected.');
    seasonFieldAssert(5, SeasonCheckboxTestDatabase::$nativeQueries,
        'Season checkbox did not use createQuery for each data lookup.');

    echo "Joomla 5/6 team season checkbox clear-all and native SQL: OK\n";
}
