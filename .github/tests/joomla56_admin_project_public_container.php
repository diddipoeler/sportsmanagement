<?php
/**
 * Regression for public Joomla DI usage by administrator project lookups.
 *
 * The Matches backend view reads project metadata through ProjectModel.
 * Joomla\CMS\Application\CMSApplication::getContainer() is protected, so
 * lookup paths must retrieve the database through Joomla\CMS\Factory.
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
        public const INTEGER = 'integer';
        public const STRING = 'string';
    }

    final class FakeQuery
    {
        public array $conditions = [];
        public array $parameters = [];

        /**
         * Accept fluent Joomla query builder operations used by project reads.
         *
         * @param string $method Joomla SQL builder operation.
         * @param array $args Requested operation parameters.
         * @return self The same query builder.
         */
        public function __call(string $method, array $args): self
        {
            if ($method === 'where') {
                $this->conditions[] = (string) ($args[0] ?? '');
            }

            if ($method === 'bind') {
                $this->parameters[(string) ($args[0] ?? '')] = $args[1] ?? null;
            }

            return $this;
        }
    }

    final class FakeDatabase implements DatabaseInterface
    {
        public ?FakeQuery $query = null;
        public int $reads = 0;

        /**
         * Build a new query for loading project records in the test.
         *
         * @return FakeQuery Test query builder.
         */
        public function createQuery(): FakeQuery
        {
            return new FakeQuery();
        }

        /**
         * Simulate a safely quoted SQL identifier.
         *
         * @param string $identifier Field or table identifier.
         * @param string|null $alias Optional result alias.
         * @return string Quoted identifier for assertions.
         */
        public function quoteName(string $identifier, ?string $alias = null): string
        {
            return $identifier . ($alias !== null ? ' AS ' . $alias : '');
        }

        /**
         * Save the query and its pagination range before executing a read.
         *
         * @param FakeQuery $query Prepared model query.
         * @param int $offset Starting result row.
         * @param int $limit Maximum returned rows.
         * @return void
         */
        public function setQuery(FakeQuery $query, int $offset = 0, int $limit = 0): void
        {
            $this->query = $query;
        }

        /**
         * Return one active fake project to the Matches view lookup.
         *
         * @return object Project metadata record.
         */
        public function loadObject(): object
        {
            ++$this->reads;

            return (object) ['id' => 42, 'name' => 'Fixtures'];
        }

        /**
         * Return a project list for the league and season selector.
         *
         * @return array<object> One project selection.
         */
        public function loadObjectList(): array
        {
            ++$this->reads;

            return [(object) ['value' => 42, 'text' => 'Fixtures']];
        }
    }
}

namespace SportsManagement\ProjectContainerTests {
    final class FakeContainer
    {
        public int $databaseRequests = 0;
        public mixed $database;

        /**
         * Seed the fake Joomla database dependency.
         *
         * @param mixed $database Database or invalid service.
         */
        public function __construct(mixed $database)
        {
            $this->database = $database;
        }

        /**
         * Emulate the only public container lookup a project read requires.
         *
         * @param string $class Service interface identifier.
         * @return mixed Injected service.
         */
        public function get(string $class): mixed
        {
            if ($class !== \Joomla\Database\DatabaseInterface::class) {
                throw new \RuntimeException('Unexpected project database service.');
            }

            ++$this->databaseRequests;

            return $this->database;
        }
    }
}

namespace Joomla\CMS {
    final class Factory
    {
        public static ?\SportsManagement\ProjectContainerTests\FakeContainer $container = null;

        /**
         * Provide the public Joomla dependency container to native model code.
         *
         * @return \SportsManagement\ProjectContainerTests\FakeContainer Database service provider.
         */
        public static function getContainer(): \SportsManagement\ProjectContainerTests\FakeContainer
        {
            return self::$container ?? throw new \RuntimeException('Test container unavailable.');
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Administrator\Service {
    final class SportsManagementAdministratorApplicationResolver
    {
        /**
         * Deliberately reject unrelated application access in static model paths.
         *
         * @return never No application object is needed for the tested reads.
         */
        public static function resolve(): never
        {
            throw new \RuntimeException('Project lookup attempted application container access.');
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Administrator\Helper {
    final class SportsManagementDatabaseResolver
    {
        /**
         * Preserve the selected database interface for default project reads.
         *
         * @param int $selector Requested SportsManagement database selection.
         * @param \Joomla\Database\DatabaseInterface $database Joomla default service.
         * @return \Joomla\Database\DatabaseInterface Same validated database service.
         */
        public function resolve(
            int $selector,
            \Joomla\Database\DatabaseInterface $database
        ): \Joomla\Database\DatabaseInterface {
            return $database;
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Administrator\Model {
    abstract class SportsManagementAdminModel {}
}

namespace {
    use Diddipoeler\Component\SportsManagement\Administrator\Model\ProjectModel;
    use Joomla\CMS\Factory;
    use Joomla\Database\FakeDatabase;
    use SportsManagement\ProjectContainerTests\FakeContainer;

    define('_JEXEC', 1);

    require dirname(__DIR__, 2) . '/admin/src/Model/ProjectModel.php';

    $db = new FakeDatabase();
    $container = new FakeContainer($db);
    Factory::$container = $container;

    $method = new \ReflectionMethod(ProjectModel::class, 'joomlaDatabase');

    if ($method->invoke(null) !== $db || $container->databaseRequests !== 1) {
        throw new \RuntimeException('ProjectModel did not load Joomla DB from the public container.');
    }

    // The real Matches backend view calls getProject() while reading the
    // project title and permissions for the selected fixture list.
    $project = ProjectModel::getProject(42);

    if ($project?->id !== 42 || $db->reads !== 1
        || $container->databaseRequests !== 2
        || ($db->query?->parameters[':projectId'] ?? null) !== 42) {
        throw new \RuntimeException('Backend Matches project lookup failed through Joomla DI.');
    }

    $choices = ProjectModel::getProjectsbyCurrentProjectLeagueSeason(11, 3);

    if (count($choices) !== 1
        || $db->reads !== 2
        || $container->databaseRequests !== 3
        || ($db->query?->parameters[':projectsSeasonId'] ?? null) !== 11
        || ($db->query?->parameters[':projectsLeagueId'] ?? null) !== 3) {
        throw new \RuntimeException('League and season project selections bypass Joomla DI.');
    }

    Factory::$container = new FakeContainer(new \stdClass());

    try {
        $method->invoke(null);
        throw new \RuntimeException('ProjectModel accepted an invalid database service.');
    } catch (\RuntimeException $exception) {
        if ($exception->getMessage() !== 'SportsManagement project database service is unavailable.') {
            throw new \RuntimeException('Invalid DB service did not fail predictably.', 0, $exception);
        }
    }

    echo "Administrator project and Matches lookups: public Joomla database DI OK.\n";
}
