<?php
/**
 * Joomla 5/6 backend round deletion and cascading match ID safety regression.
 *
 * The native RoundModel must never cast malformed selectors into unrelated
 * round/match IDs or forward unvalidated request values to Joomla deletion.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\Database {
    final class ParameterType
    {
        public const INTEGER = 'integer';
    }
}

namespace Diddipoeler\Component\SportsManagement\Administrator\Table {
    class RoundTable
    {
        public int $id = 0;
        public static array $missingIds = [];

        /**
         * Construct an isolated Joomla round table with fake database.
         *
         * @param object $database Selected administrator connection.
         */
        public function __construct(object $database) {}

        /**
         * Simulate fetching a round record for Joomla's delete ACL check.
         *
         * @param int $id Requested round identifier.
         * @return bool Whether the selected round exists.
         */
        public function load(int $id): bool
        {
            $this->id = $id;
            return !in_array($id, self::$missingIds, true);
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Administrator\Model {
    abstract class SportsManagementAdminModel
    {
        public \RoundDeleteTestDatabase $database;
        public int $parentDeletes = 0;
        public array $parentSelection = [];
        public string $error = '';
        public bool $authenticated = true;
        public bool $componentDeleteAllowed = true;
        public array $deniedRoundIds = [];

        /**
         * Return the test database to detect unintended SQL access.
         *
         * @return \RoundDeleteTestDatabase Capturing query connection.
         */
        protected function getDatabase(): \RoundDeleteTestDatabase
        {
            return $this->database;
        }

        /**
         * Provide a minimal Joomla administrator message queue.
         *
         * @return object Test administrator app.
         */
        protected function administratorApplication(): object
        {
            return new class($this) {
                /**
                 * Link the fake application to model ACL configuration.
                 *
                 * @param object $model Shared round model.
                 */
                public function __construct(private object $model) {}

                /**
                 * Return authenticated Joomla identity or simulate absence.
                 *
                 * @return object|null Identity allowed for round deletion.
                 */
                public function getIdentity(): ?object
                {
                    if (!$this->model->authenticated) {
                        return null;
                    }

                    return new class($this->model->componentDeleteAllowed) {
                        public int $id = 42;

                        /**
                         * Simulate Joomla core.delete permission.
                         *
                         * @param string $action Joomla permission.
                         * @param string $asset Component asset.
                         * @return bool Whether component deletion is authorised.
                         */
                        public function authorise(string $action, string $asset): bool
                        {
                            return $this->allowed
                                && $action === 'core.delete'
                                && $asset === 'com_sportsmanagement';
                        }

                        /**
                         * Preserve the simulated permission at construction.
                         *
                         * @param bool $allowed Whether delete is allowed.
                         */
                        public function __construct(private bool $allowed) {}
                    };
                }

                /**
                 * Record no-op Joomla deletion result messages.
                 *
                 * @param string $message Localised description.
                 * @param string $type Message type.
                 * @return void
                 */
                public function enqueueMessage(string $message, string $type = 'message'): void {}
            };
        }

        /**
         * Simulate Joomla's per-record AdminModel::canDelete check.
         *
         * @param object $record Loaded Joomla round table.
         * @return bool Whether the current record can be deleted.
         */
        protected function canDelete($record): bool
        {
            return !in_array((int) $record->id, $this->deniedRoundIds, true);
        }

        /**
         * Record exactly the PKs passed to Joomla after deleting dependants.
         *
         * @param mixed $pks Selected round record IDs.
         * @return bool Parent deletion succeeds for the isolated fixture.
         */
        public function delete(&$pks)
        {
            ++$this->parentDeletes;
            $this->parentSelection = $pks;
            return true;
        }

        /**
         * Capture validation errors instead of emitting application messages.
         *
         * @param string $message Round model write error.
         * @return void
         */
        public function setError(string $message): void { $this->error = $message; }
    }
}

namespace {
    use Diddipoeler\Component\SportsManagement\Administrator\Model\RoundModel;

    define('_JEXEC', 1);

    final class RoundDeleteTestQuery
    {
        public static array $whereSelections = [];

        /**
         * Accept a SELECT/DELETE and associated bind-safe Joomla query fields.
         *
         * @param string $method Query-builder operation.
         * @param array<mixed> $args Call arguments.
         * @return static Fluent native SQL query builder.
         */
        public function __call(string $method, array $args): static
        {
            if ($method === 'whereIn') {
                self::$whereSelections[] = $args[1] ?? [];
            }

            return $this;
        }
    }

    final class RoundDeleteTestDatabase
    {
        public int $queries = 0;
        public int $writes = 0;
        public array $relatedMatches = [];

        /**
         * Return a query mock and count native Joomla queries.
         *
         * @return RoundDeleteTestQuery Fluent SQL query fixture.
         */
        public function createQuery(): RoundDeleteTestQuery
        {
            ++$this->queries;
            return new RoundDeleteTestQuery();
        }

        /**
         * Quote trusted Joomla table and column names.
         *
         * @param string $name SQL identifier.
         * @param string|null $alias Optional alias.
         * @return string Quoted identifier.
         */
        public function quoteName(string $name, ?string $alias = null): string
        {
            return '"' . $name . '"' . ($alias === null ? '' : ' AS "' . $alias . '"');
        }

        /**
         * Accept the constructed match relationship query.
         *
         * @param object $query Native SQL query.
         * @return static Same test database.
         */
        public function setQuery(object $query): static { return $this; }

        /**
         * Supply selected match IDs for the dependent record cleanup.
         *
         * @return array<int,mixed> Test match record identifiers.
         */
        public function loadColumn(): array { return $this->relatedMatches; }

        /**
         * Track dependent DELETE queries issued by the round model.
         *
         * @return bool SQL mutation accepted.
         */
        public function execute(): bool
        {
            ++$this->writes;
            return true;
        }

        /**
         * Return no real changed rows for the simulated database.
         *
         * @return int No affected rows.
         */
        public function getAffectedRows(): int { return 0; }
    }

    /**
     * Compare assertions independent of PHP runtime settings.
     *
     * @param mixed $expected Required normalised IDs or call count.
     * @param mixed $actual Observed model result.
     * @param string $context Failure description.
     * @return void
     */
    function roundDeleteAssert(mixed $expected, mixed $actual, string $context): void
    {
        if ($expected !== $actual) {
            throw new \RuntimeException(
                $context . ': expected ' . var_export($expected, true)
                . ', observed ' . var_export($actual, true)
            );
        }
    }

    require_once dirname(__DIR__, 2) . '/admin/src/Model/RoundModel.php';
    $model = new RoundModel();
    $model->database = new RoundDeleteTestDatabase();
    $parse = new \ReflectionMethod($model, 'normaliseIds');

    roundDeleteAssert([7, 8, PHP_INT_MAX], $parse->invoke($model, [
        7, '0007', ' 0008 ', PHP_INT_MAX, PHP_INT_MAX . '',
        '7junk', '7.5', '7e1', '-7', '+7', '0', 0, null,
        false, true, 7.5, [['7']], [], new \stdClass(),
        PHP_INT_MAX . '0', str_repeat('0', 65) . '7',
    ]), 'Malformed round IDs were coerced into deletable records');

    roundDeleteAssert([], $parse->invoke($model, array_fill(0, 501, 7)),
        'Overlarge deletion batch was not bounded');

    // Malformed-only selections must fail before database use and must not
    // reach parent Joomla AdminModel::delete (which may coerce IDs).
    foreach ([['7junk'], ['7.5'], [true], [['7']], [PHP_INT_MAX . '0'],
        ['0000'], [str_repeat('7', 512)], [new \stdClass()]] as $invalid) {
        $keys = $invalid;
        roundDeleteAssert(false, $model->delete($keys),
            'Malformed round selection triggered delete');
        roundDeleteAssert(0, $model->parentDeletes,
            'Malformed round selection reached Joomla parent deletion');
        roundDeleteAssert(0, $model->database->queries,
            'Malformed round selection touched SQL');
    }

    // Raw malformed mixed values may accompany legitimate IDs. Only
    // canonical positive identifiers are forwarded to the actual delete.
    $keys = ['0007', 8, '7junk', true, ' 8 ', '99999999999999999999999'];
    roundDeleteAssert(true, $model->delete($keys), 'Valid round deletion failed');
    roundDeleteAssert([7, 8], $keys, 'Selection was not canonicalised by reference');
    roundDeleteAssert([7, 8], $model->parentSelection,
        'Joomla parent received raw or malformed round keys');
    roundDeleteAssert(1, $model->parentDeletes,
        'Joomla parent should be called once for valid selection');
    roundDeleteAssert(1, $model->database->writes,
        'Valid round deletion did not clean dependent matches');
    roundDeleteAssert([[7, 8], [7, 8]], RoundDeleteTestQuery::$whereSelections,
        'Round delete SQL did not use validated IDs for matches and cleanup');

    // The explicit dependent-match cleanup endpoint must reject invalid IDs
    // and must use strict validation for IDs returned by its match lookup.
    $queries = $model->database->queries;
    roundDeleteAssert(true, $model->deleteRoundMatches(['7junk', false, []]),
        'Invalid dependent match cleanup was not a safe no-op');
    roundDeleteAssert($queries, $model->database->queries,
        'Invalid round cleanup accessed the database');

    // Authorisation must precede ANY dependent-match write. Joomla's
    // parent delete() is too late to protect the manual child cleanup.
    $baselineQueries = $model->database->queries;
    $baselineWrites = $model->database->writes;
    $baselineParents = $model->parentDeletes;

    $model->componentDeleteAllowed = false;
    $deniedKeys = [7];
    roundDeleteAssert(false, $model->delete($deniedKeys),
        'Denied component delete removed round dependencies');

    $model->componentDeleteAllowed = true;
    $model->authenticated = false;
    $deniedKeys = [7];
    roundDeleteAssert(false, $model->delete($deniedKeys),
        'Anonymous request removed round dependencies');

    $model->authenticated = true;
    $model->deniedRoundIds = [8];
    $deniedKeys = [7, 8];
    roundDeleteAssert(false, $model->delete($deniedKeys),
        'Per-record denied round removed dependencies from earlier records');

    $model->deniedRoundIds = [];
    \Diddipoeler\Component\SportsManagement\Administrator\Table\RoundTable::$missingIds = [8];
    $missingKeys = [7, 8];
    roundDeleteAssert(false, $model->delete($missingKeys),
        'A missing round still deleted dependent match rows');
    \Diddipoeler\Component\SportsManagement\Administrator\Table\RoundTable::$missingIds = [];

    roundDeleteAssert($baselineQueries, $model->database->queries,
        'Denied or missing rounds reached a SQL query');
    roundDeleteAssert($baselineWrites, $model->database->writes,
        'Denied or missing rounds issued a SQL mutation');
    roundDeleteAssert($baselineParents, $model->parentDeletes,
        'Denied or missing rounds reached Joomla parent deletion');

    $model->componentDeleteAllowed = false;
    roundDeleteAssert(false, $model->deleteRoundMatches([7]),
        'Direct match cleanup bypassed round deletion permissions');
    roundDeleteAssert($baselineQueries, $model->database->queries,
        'Denied direct match cleanup queried a database');
    $model->componentDeleteAllowed = true;

    $model->database->relatedMatches = ['00021', '21junk', 22, -1, [23]];
    RoundDeleteTestQuery::$whereSelections = [];
    roundDeleteAssert(true, $model->deleteRoundMatches([7]),
        'Valid dependent-match cleanup failed');
    roundDeleteAssert([[7], [21, 22], [21, 22], [21, 22], [21, 22],
        [21, 22], [21, 22], [21, 22], [7]],
        RoundDeleteTestQuery::$whereSelections,
        'Dependent match deletion accepted unvalidated match IDs');

    echo "Joomla 5/6 administrator round deletion ID boundaries: OK\n";
}
