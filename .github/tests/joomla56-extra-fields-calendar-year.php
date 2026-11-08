<?php
/**
 * Standalone Joomla 5/6 tests for extra-field lookups and calendar year lists.
 *
 * Usage: php .github/tests/joomla56-extra-fields-calendar-year.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\Database {
    /** Minimal parameter types consumed by the Joomla SQL query builder. */
    final class ParameterType
    {
        public const STRING = 'string';
        public const INTEGER = 'integer';
    }

    /** Stub database contract for testing a read service without Joomla. */
    interface DatabaseInterface
    {
        public function createQuery(): object;
        public function quoteName(string $name, ?string $alias = null): string;
        public function setQuery(object $query, int $offset = 0, int $limit = 0): static;
        public function loadResult(): mixed;
        public function loadObjectList(): array;
    }
}

namespace {
    \defined('_JEXEC') || \define('_JEXEC', 1);

    require_once dirname(__DIR__, 2) . '/site/src/Service/PersonExtraFieldReadService.php';
    require_once dirname(__DIR__, 2) . '/modules/mod_sportsmanagement_calendar/src/Runtime/CalendarFunctions.php';
    require_once dirname(__DIR__, 2) . '/admin/src/Helper/ExtendedFormHelper.php';

    use Diddipoeler\Component\SportsManagement\Site\Service\PersonExtraFieldReadService;
    use Diddipoeler\Component\SportsManagement\Administrator\Helper\ExtendedFormHelper;
    use Diddipoeler\Module\SportsManagementCalendar\Site\Runtime\CalendarFunctions;

    /** Fluent query stub which remembers the SQL conditions passed by the service. */
    final class SmTestQuery
    {
        public array $conditions = [];

        /**
         * Retain selected query fragments while allowing the fluent API to proceed.
         *
         * @param mixed ...$arguments Query builder arguments supplied by the reader.
         * @return static Fluent query object.
         */
        public function select(mixed ...$arguments): static { return $this; }

        /**
         * Accept the table name passed by the read service.
         *
         * @param mixed ...$arguments Table and alias data.
         * @return static Fluent query object.
         */
        public function from(mixed ...$arguments): static { return $this; }

        /**
         * Record a query predicate for assertions about exact matches.
         *
         * @param string $where SQL predicate text.
         * @return static Fluent query object.
         */
        public function where(string $where): static { $this->conditions[] = $where; return $this; }

        /**
         * Retain query values without contacting a real database.
         *
         * @param mixed ...$arguments Placeholder and parameter values.
         * @return static Fluent query object.
         */
        public function bind(mixed ...$arguments): static { return $this; }

        /**
         * Accept optional joined tables for the extra-fields query.
         *
         * @param mixed ...$arguments Join type and condition.
         * @return static Fluent query object.
         */
        public function join(mixed ...$arguments): static { return $this; }

        /**
         * Accept deterministic record ordering for the query builder.
         *
         * @param mixed ...$arguments Order clauses.
         * @return static Fluent query object.
         */
        public function order(mixed ...$arguments): static { return $this; }
    }

    /** Fake Joomla database that keeps SQL predicates available to test. */
    final class SmTestDatabase implements \Joomla\Database\DatabaseInterface
    {
        public int $queries = 0;
        public ?SmTestQuery $lastQuery = null;

        /**
         * Create a new inspectable fluent query.
         *
         * @return SmTestQuery Query which stores predicates.
         */
        public function createQuery(): object { ++$this->queries; return new SmTestQuery(); }

        /**
         * Return a quoted identifier and optional alias for predicate inspection.
         *
         * @param string $name SQL identifier name.
         * @param string|null $alias Optional SQL alias.
         * @return string Quoted identifier.
         */
        public function quoteName(string $name, ?string $alias = null): string
        {
            return '"' . $name . '"' . ($alias === null ? '' : ' AS "' . $alias . '"');
        }

        /**
         * Retain the prepared query instead of executing SQL.
         *
         * @param object $query Built SQL query.
         * @param int $offset Optional row offset.
         * @param int $limit Optional row limit.
         * @return static Database stub for fluent calls.
         */
        public function setQuery(object $query, int $offset = 0, int $limit = 0): static
        {
            $this->lastQuery = $query;
            return $this;
        }

        /**
         * Return a fixed result confirming the exact-match query was issued.
         *
         * @return mixed Database lookup result.
         */
        public function loadResult(): mixed { return 1; }

        /**
         * Return no stored extra-field records for a synthetic person.
         *
         * @return array<int,object> Empty records.
         */
        public function loadObjectList(): array { return []; }
    }

    $db = new SmTestDatabase();
    $service = new PersonExtraFieldReadService($db);

    if ($service->hasFields('frontend', 'club%') !== true
        || $db->lastQuery === null
        || !in_array('"ef.template_frontend" = :templateName', $db->lastQuery->conditions, true)) {
        throw new \RuntimeException('Frontend field lookup must use exact template equality.');
    }

    $service->fields(17, 'frontend', 'club_');

    if ($db->lastQuery === null
        || !in_array('"ef.template_frontend" = :templateName', $db->lastQuery->conditions, true)) {
        throw new \RuntimeException('Person extra-field query must not use wildcard matching.');
    }

    $before = $db->queries;

    if ($service->hasFields('frontend', '') !== false
        || $service->fields(17, 'frontend', '') !== []
        || $db->queries !== $before) {
        throw new \RuntimeException('Empty template names must be rejected before query creation.');
    }

    $form = new ExtendedFormHelper();

    foreach (['../users', 'person?x=1', '', 'bad/name'] as $badView) {
        if ($form->load('extended', $badView) !== null) {
            throw new \RuntimeException('Unsafe administrator view was not rejected.');
        }
    }

    foreach ([1970, 9999] as $year) {
        $html = CalendarFunctions::yearList((string) $year);
        preg_match_all('/<option value="([0-9]+)"/', $html, $matches);

        foreach ($matches[1] as $option) {
            if ((int) $option < 1970 || (int) $option > 9999) {
                throw new \RuntimeException('Calendar selector emitted unsupported year ' . $option);
            }
        }
    }

    echo "Joomla 5/6 extra-field, admin form and calendar year regressions passed.\n";
}
