<?php
/**
 * Regression: the New Project article writer must require a published module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\Database {
    interface DatabaseInterface
    {
    }

    final class ParameterType
    {
        public const INTEGER = 'integer';
        public const STRING = 'string';
    }
}

namespace {
    define('_JEXEC', 1);
    require dirname(__DIR__, 2)
        . '/modules/mod_sportsmanagement_new_project/src/Helper/NewProjectHelper.php';

    final class NewProjectQueryStub
    {
        /**
         * Accept selected columns in the minimal module query.
         *
         * @param array<int, string>|string $columns Selected SQL columns.
         * @return self Query test double.
         */
        public function select(array|string $columns): self
        {
            return $this;
        }

        /**
         * Accept the module table used for publication checks.
         *
         * @param string $table Selected SQL table.
         * @return self Query test double.
         */
        public function from(string $table): self
        {
            return $this;
        }

        /**
         * Accept one module selection condition.
         *
         * @param string $condition SQL filter.
         * @return self Query test double.
         */
        public function where(string $condition): self
        {
            return $this;
        }

        /**
         * Accept a bound module ID or module type.
         *
         * @param string $key SQL placeholder.
         * @param mixed $value Bound value.
         * @param string $type Joomla database parameter type.
         * @return self Query test double.
         */
        public function bind(string $key, mixed $value, string $type): self
        {
            return $this;
        }
    }

    final class NewProjectDatabaseStub implements \Joomla\Database\DatabaseInterface
    {
        public mixed $record = null;

        /**
         * Create a query object for the published-module lookup.
         *
         * @return NewProjectQueryStub Fluent query test double.
         */
        public function createQuery(): NewProjectQueryStub
        {
            return new NewProjectQueryStub();
        }

        /**
         * Represent a quoted SQL identifier without a real database.
         *
         * @param string $name SQL identifier.
         * @return string Unchanged identifier for test purposes.
         */
        public function quoteName(string $name): string
        {
            return $name;
        }

        /**
         * Receive the query without sending it to a database.
         *
         * @param NewProjectQueryStub $query Query test double.
         * @param int $offset First result offset.
         * @param int $limit Maximum number of rows.
         * @return void
         */
        public function setQuery(NewProjectQueryStub $query, int $offset = 0, int $limit = 0): void
        {
        }

        /**
         * Return the simulated Joomla module record.
         *
         * @return mixed Mock result of the module lookup.
         */
        public function loadObject(): mixed
        {
            return $this->record;
        }
    }

    $helper = new \Diddipoeler\Module\SportsManagementNewProject\Site\Helper\NewProjectHelper();
    $lookup = new \ReflectionMethod($helper, 'loadPublishedModule');
    $db = new NewProjectDatabaseStub();

    foreach ([1, '1', 0, '0', true, false, ['1'], '1junk', null] as $published) {
        $db->record = (object) ['published' => $published, 'params' => '{}'];
        $result = $lookup->invoke($helper, $db, 12);
        $allowed = $published === 1 || $published === '1';

        if (($result !== null) !== $allowed) {
            fwrite(STDERR, 'New Project published module validation regression.' . PHP_EOL);
            exit(1);
        }
    }

    $db->record = null;
    if ($lookup->invoke($helper, $db, 12) !== null) {
        fwrite(STDERR, 'New Project accepted missing module metadata.' . PHP_EOL);
        exit(1);
    }

    $flags = new \ReflectionMethod($helper, 'countryFlags');
    $malformedCountries = [
        (object) ['country' => ['DEU']],
        (object) ['country' => new \stdClass()],
        (object) ['country' => null],
        null,
    ];

    if ($flags->invoke($helper, $db, $malformedCountries) !== []) {
        fwrite(STDERR, 'New Project accepted malformed country selectors.' . PHP_EOL);
        exit(1);
    }

    echo "New Project published module validation passed.\n";
}
