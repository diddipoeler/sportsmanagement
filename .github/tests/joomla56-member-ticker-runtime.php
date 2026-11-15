<?php
/**
 * Joomla 5/6 XML member and playground ticker runtime safety regression.
 *
 * Runs the native administrator and module helpers against isolated database
 * fixtures and treats unexpected PHP warnings as failures on PHP 8.3/8.4.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\Database {
    interface DatabaseInterface {}

    final class ParameterType
    {
        public const INTEGER = 1;
    }
}

namespace Joomla\CMS\Application {
    interface CMSApplicationInterface {}
}

namespace Joomla\CMS\Log {
    final class Log
    {
        public const ERROR = 2;
        public static array $messages = [];

        /**
         * Record a safe diagnostic that must not contain private database data.
         * @param string $message Class-only error description.
         * @param int $severity Joomla log severity.
         * @param string $category Log category.
         * @return void
         */
        public static function add(string $message, int $severity, string $category): void
        {
            self::$messages[] = [$message, $severity, $category];
        }
    }
}

namespace Joomla\Registry {
    final class Registry
    {
        /**
         * Store raw Joomla module options without coercion.
         * @param array<string,mixed> $values Input parameters.
         */
        public function __construct(private array $values = []) {}

        /**
         * Return a configured module option with a Joomla-like default.
         * @param string $name Parameter key.
         * @param mixed $default Value when absent.
         * @return mixed Original parameter value.
         */
        public function get(string $name, mixed $default = null): mixed
        {
            return $this->values[$name] ?? $default;
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Service {
    final class SportsManagementDatabaseResolver
    {
        public static bool $fail = false;
        public static int $calls = 0;

        /**
         * Simulate an external SportsManagement database connection.
         * @param \Joomla\Database\DatabaseInterface $fallback Primary database.
         * @param int $selector Primary or secondary database choice.
         * @return \Joomla\Database\DatabaseInterface Selected connection.
         */
        public static function resolve(
            \Joomla\Database\DatabaseInterface $fallback,
            int $selector
        ): \Joomla\Database\DatabaseInterface {
            ++self::$calls;

            if (self::$fail) {
                throw new \RuntimeException('SECRET_PASSWORD=hidden remote host /private/path');
            }

            if ($selector !== 0 && $selector !== 1) {
                throw new \RuntimeException('Unvalidated database selector used.');
            }

            return $fallback;
        }
    }
}

namespace {
    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);
    require_once $root . '/admin/src/Service/XmlProjectMemberImportService.php';
    require_once $root . '/modules/mod_sportsmanagement_playground_ticker/src/Helper/PlaygroundTickerHelper.php';

    /** Native Joomla database fixture for team player and staff imports. */
    final class MemberImportDatabase implements \Joomla\Database\DatabaseInterface
    {
        public array $inserted = [];
        public mixed $lastId = 41;
        public bool $rejectInsert = false;
        public mixed $columns = [
            'id' => true, 'projectteam_id' => true,
            'person_id' => true, 'project_position_id' => true,
            'published' => true, 'notes' => true, 'shirt_number' => true,
        ];

        /**
         * Return allowed member column names for schema whitelisting.
         * @param string $table Trusted member destination.
         * @return mixed Schema mapping or malformed fixture.
         */
        public function getTableColumns(string $table): mixed
        {
            if (!in_array($table, ['#__sportsmanagement_team_player', '#__sportsmanagement_team_staff'], true)) {
                throw new \RuntimeException('Unexpected member destination table.');
            }

            return $this->columns;
        }

        /**
         * Record the native import row without a database installation.
         * @param string $table Target SportsManagement table.
         * @param object $row Validated member row.
         * @return bool Whether insertion succeeded.
         */
        public function insertObject(string $table, object $row): bool
        {
            if ($this->rejectInsert) {
                return false;
            }

            $this->inserted[] = [$table, clone $row];
            return true;
        }

        /**
         * Return the configured post-insert auto-increment key.
         * @return mixed Driver result, valid or malformed.
         */
        public function insertid(): mixed
        {
            return $this->lastId;
        }
    }

    /** Fluent query fixture for a project-scoped playground ticker. */
    final class PlaygroundTickerQuery
    {
        public array $joins = [];
        public array $bindings = [];

        /**
         * Accept the native selected columns.
         * @param array $columns List of selected playground columns.
         * @return self Fluent query.
         */
        public function select(array $columns): self { return $this; }

        /**
         * Accept a quoted table identifier.
         * @param string $table Playground table name.
         * @return self Fluent query.
         */
        public function from(string $table): self { return $this; }

        /**
         * Record SQL joins used for project-scoped venue discovery.
         * @param string $kind Inner or left join kind.
         * @param string $condition SQL join predicate.
         * @return self Fluent query.
         */
        public function join(string $kind, string $condition): self
        {
            $this->joins[] = [$kind, $condition];
            return $this;
        }

        /**
         * Accept a project restriction predicate.
         * @param string $condition SQL project filter.
         * @return self Fluent query.
         */
        public function where(string $condition): self { return $this; }

        /**
         * Capture the bound project ID for scope assertions.
         * @param string $name SQL placeholder.
         * @param mixed $value Valid project ID.
         * @param mixed $type Joomla parameter type.
         * @return self Fluent query.
         */
        public function bind(string $name, mixed $value, mixed $type): self
        {
            $this->bindings[$name] = $value;
            return $this;
        }
    }

    /** In-memory Joomla playground-query fixture. */
    final class PlaygroundTickerDatabase implements \Joomla\Database\DatabaseInterface
    {
        public int $queries = 0;
        public int $limit = 0;
        public bool $fail = false;
        public mixed $rows = [];
        public ?PlaygroundTickerQuery $current = null;

        /**
         * Return a new Joomla-style query.
         * @return PlaygroundTickerQuery Fluent query fixture.
         */
        public function createQuery(): PlaygroundTickerQuery
        {
            return new PlaygroundTickerQuery();
        }

        /**
         * Return the raw identifier without SQL driver access.
         * @param string $name Database column or table.
         * @param string|null $alias Optional field alias.
         * @return string Query identifier.
         */
        public function quoteName(string $name, ?string $alias = null): string
        {
            return $name;
        }

        /**
         * Record query limits used by the ticker.
         * @param PlaygroundTickerQuery $query Native Joomla query.
         * @param int $offset Result offset.
         * @param int $limit Maximum fetched rows.
         * @return void
         */
        public function setQuery(PlaygroundTickerQuery $query, int $offset = 0, int $limit = 0): void
        {
            ++$this->queries;
            $this->limit = $limit;
            $this->current = $query;
        }

        /**
         * Simulate database rows or a private remote SQL failure.
         * @return mixed Configured playground rows.
         */
        public function loadObjectList(): mixed
        {
            if ($this->fail) {
                throw new \RuntimeException('SECRET_SQL=select * from private_table');
            }

            return $this->rows;
        }
    }

    /** Minimal frontend Joomla application for module settings. */
    final class PlaygroundTickerApplication implements \Joomla\CMS\Application\CMSApplicationInterface
    {
        /**
         * Supply an input object with a default database selector.
         * @return object Joomla-style input object.
         */
        public function getInput(): object
        {
            return new class {
                /**
                 * Return the module's fallback database selector.
                 * @param string $name Parameter name.
                 * @param int $default Default selector.
                 * @return int Configured fallback.
                 */
                public function getInt(string $name, int $default = 0): int
                {
                    return $default;
                }
            };
        }
    }

    set_error_handler(static function (int $level, string $message): never {
        throw new \ErrorException($message, 0, $level);
    });

    try {
        $db = new MemberImportDatabase();
        $service = new \Diddipoeler\Component\SportsManagement\Administrator\Service\XmlProjectMemberImportService($db);
        $player = (object) [
            'id' => '0007',
            'projectteam_id' => '0005',
            'person_id' => '0009',
            'project_position_id' => '0003',
            'shirt_number' => '12',
            'notes' => ['nested'],
            'published' => 0,
            'unknown_column' => 'must not reach SQL',
        ];
        $staff = (object) [
            'id' => 8, 'projectteam_id' => 5, 'person_id' => 9,
            'project_position_id' => 0, 'notes' => 'Trainer',
        ];
        $data = ['teamplayer' => [$player], 'teamstaff' => [$staff]];
        $projectTeams = [5 => '00021'];
        $people = [9 => 31];
        $positions = [3 => '0004'];
        $result = $service->import($data, $projectTeams, $people, $positions, '21');

        if ($result['maps']['_convertTeamPlayerID'] !== [7 => 41]
            || $result['maps']['_convertTeamStaffID'] !== []
            || count($db->inserted) !== 1
            || $db->inserted[0][0] !== '#__sportsmanagement_team_player'
            || $db->inserted[0][1]->projectteam_id !== 21
            || $db->inserted[0][1]->person_id !== 31
            || $db->inserted[0][1]->project_position_id !== 4
            || $db->inserted[0][1]->published !== 1
            || $db->inserted[0][1]->shirt_number !== '12'
            || property_exists($db->inserted[0][1], 'notes')
            || property_exists($db->inserted[0][1], 'unknown_column')
        ) {
            throw new \RuntimeException('Valid team-player import or filtered SQL columns changed.');
        }

        $db->lastId = '00042';
        $step22 = $service->import($data, $projectTeams, $people, $positions, '22');

        if ($step22['maps']['_convertTeamPlayerID'] !== [7 => 42]
            || $step22['maps']['_convertTeamStaffID'] !== [8 => 42]
            || count($db->inserted) !== 3
            || $db->inserted[2][0] !== '#__sportsmanagement_team_staff') {
            throw new \RuntimeException('Valid player/staff step 22 mapping changed.');
        }

        $badPlayerIds = [
            '5junk', ['5'], 5.0, true, false,
            '9999999999999999999999999999999', -1, "5\n",
        ];

        foreach ($badPlayerIds as $bad) {
            $source = clone $player;
            $source->projectteam_id = $bad;
            $writes = count($db->inserted);
            $result = $service->import(['teamplayer' => [$source]], $projectTeams, $people, $positions, '21');

            if (count($db->inserted) !== $writes
                || $result['maps']['_convertTeamPlayerID'] !== []
                || !str_contains($result['messages']['Importing team-player data:'], 'Skipping')) {
                throw new \RuntimeException('Malformed XML project team ID was cast into a valid relation.');
            }
        }

        foreach ([['9'], '9junk', true, 9.0] as $bad) {
            $source = clone $player;
            $source->person_id = $bad;
            $writes = count($db->inserted);
            $service->import(['teamplayer' => [$source]], $projectTeams, $people, $positions, '21');

            if (count($db->inserted) !== $writes) {
                throw new \RuntimeException('Malformed source person ID reached database insertion.');
            }
        }

        foreach ([[5 => ['21']], [5 => '21junk'], [5 => 21.0]] as $badMap) {
            $writes = count($db->inserted);
            $service->import(['teamplayer' => [$player]], $badMap, $people, $positions, '21');

            if (count($db->inserted) !== $writes) {
                throw new \RuntimeException('Malformed mapped project-team ID reached SQL.');
            }
        }

        $db->lastId = ['invalid'];
        $writes = count($db->inserted);

        try {
            $service->import(['teamplayer' => [$player]], $projectTeams, $people, $positions, '21');
            throw new \RuntimeException('Bad post-insert database ID was mapped.');
        } catch (\RuntimeException $exception) {
            if ($exception->getCode() !== 500
                || !str_contains($exception->getMessage(), 'Unable to resolve imported')) {
                throw $exception;
            }
        }

        if (count($db->inserted) !== $writes + 1) {
            throw new \RuntimeException('Insert-ID failure fixture did not perform the attempted insert.');
        }

        $db->lastId = 41;
        $db->rejectInsert = true;

        try {
            $service->import(['teamplayer' => [$player]], $projectTeams, $people, $positions, '21');
            throw new \RuntimeException('Failed SQL member insert was accepted.');
        } catch (\RuntimeException $exception) {
            if ($exception->getCode() !== 500
                || !str_contains($exception->getMessage(), 'Unable to store imported')) {
                throw $exception;
            }
        }

        $db->rejectInsert = false;
        $db->columns = false;

        try {
            $service->import(['teamplayer' => [$player]], $projectTeams, $people, $positions, '21');
            throw new \RuntimeException('Unavailable member table schema was ignored.');
        } catch (\RuntimeException $exception) {
            if ($exception->getCode() !== 500
                || !str_contains($exception->getMessage(), 'Unable to inspect')) {
                throw $exception;
            }
        }

        $module = new \Diddipoeler\Module\SportsManagementPlaygroundTicker\Site\Helper\PlaygroundTickerHelper();
        $app = new PlaygroundTickerApplication();
        $ticker = new PlaygroundTickerDatabase();
        $ticker->rows = [
            (object) ['id_playground' => '00011', 'name' => 'First'],
            (object) ['id_playground' => 11, 'name' => 'Duplicate'],
            (object) ['id_playground' => '12', 'name' => 'Second'],
            (object) ['id_playground' => '12junk', 'name' => 'Invalid'],
            (object) ['id_playground' => ['13'], 'name' => 'Nested'],
            (object) ['id_playground' => '0', 'name' => 'Missing'],
            new \stdClass(),
            ['id_playground' => 14],
        ];
        $params = new \Joomla\Registry\Registry([
            'p' => '0003:project',
            'cfg_which_database' => '0001',
            'limit' => '0005',
        ]);
        $venues = $module->getData($params, $app, $ticker);

        if (count($venues) !== 2 || $venues[0]->name !== 'Duplicate'
            || $venues[1]->name !== 'Second'
            || $ticker->queries !== 1 || $ticker->limit !== 1000
            || $ticker->current?->bindings[':projectId'] !== 3) {
            throw new \RuntimeException('Valid ticker playground rows or project scope regressed.');
        }

        $resolver = \Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver::class;
        $queriesBefore = $ticker->queries;
        $resolver::$fail = true;

        if ($module->getData($params, $app, $ticker) !== []
            || $ticker->queries !== $queriesBefore) {
            throw new \RuntimeException('Failed external playground connection reached SQL.');
        }

        $resolver::$fail = false;
        $ticker->fail = true;

        if ($module->getData($params, $app, $ticker) !== []) {
            throw new \RuntimeException('Failed playground SQL query crashed frontend.');
        }

        $log = \Joomla\CMS\Log\Log::$messages;

        if (count($log) !== 2) {
            throw new \RuntimeException('Expected two class-only logs for failed playground queries.');
        }

        foreach ($log as [$message, $severity, $category]) {
            if ($severity !== \Joomla\CMS\Log\Log::ERROR
                || $category !== 'com_sportsmanagement'
                || !str_contains($message, '(RuntimeException)')
                || str_contains($message, 'SECRET')
                || str_contains($message, '/private/')
            ) {
                throw new \RuntimeException('Private SQL or connection details reached Joomla log.');
            }
        }

        $ticker->fail = false;

        foreach ([['1junk'], [true], [2], [str_repeat('0', 130) . '1']] as [$badSelector]) {
            $before = $resolver::$calls;
            $badParams = new \Joomla\Registry\Registry([
                'p' => 3,
                'cfg_which_database' => $badSelector,
            ]);

            if ($module->getData($badParams, $app, $ticker) !== []
                || $resolver::$calls !== $before) {
                throw new \RuntimeException('Invalid ticker database selector resolved a connection.');
            }
        }
    } finally {
        restore_error_handler();
    }

    echo "Joomla 5/6 XML member and playground ticker runtime regressions passed.\n";
}
