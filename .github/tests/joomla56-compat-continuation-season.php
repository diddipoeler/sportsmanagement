<?php
/**
 * Verify project-scoped current-season joins and PHP 8-safe imported records.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\Application {
    interface CMSApplicationInterface {}
}

namespace Joomla\CMS\Language {
    final class Text
    {
        /** Keep translated country and federation labels deterministic. */
        public static function _(string $key): string { return $key; }
    }
}

namespace Joomla\CMS\Log {
    final class Log
    {
        public const ERROR = 1;

        /** Reject any accidental query error during this standalone test. */
        public static function add(string $message, int $type, string $category): void
        {
            throw new \RuntimeException('Unexpected current-season database error: ' . $message);
        }
    }
}

namespace Joomla\CMS\Uri {
    final class Uri
    {
        /** Return a fixed root for flag image assertions. */
        public static function root(): string { return 'https://example.org/'; }
    }
}

namespace Joomla\Database {
    interface DatabaseInterface {}
    final class ParameterType { public const INTEGER = 1; }
}

namespace Joomla\Registry {
    final class Registry
    {
        public function __construct(private array $values = []) {}

        /** Retrieve a module configuration value without Joomla bootstrap. */
        public function get(string $key, mixed $default = null): mixed
        {
            return $this->values[$key] ?? $default;
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Service {
    final class SportsManagementDatabaseResolver
    {
        /** Keep the fixture database when the active selector is valid. */
        public static function resolve(
            \Joomla\Database\DatabaseInterface $database,
            int $selector
        ): \Joomla\Database\DatabaseInterface {
            return $database;
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/modules/mod_sportsmanagement_act_season/src/Helper/ActSeasonHelper.php';

    final class ActSeasonQueryFixture
    {
        public array $joins = [];
        public array $filters = [];

        /** Accept a column selection for the query contract test. */
        public function select(array $columns): self { return $this; }

        /** Record the source table without hitting a database. */
        public function from(string $table): self { return $this; }

        /** Track every native Joomla SQL join for project ownership. */
        public function join(string $type, string $condition): self
        {
            $this->joins[] = $condition;
            return $this;
        }

        /** Accept a SQL filter expression without changing the fixture. */
        public function where(string $condition): self { return $this; }

        /** Accept an integer list bound by the Joomla query builder. */
        public function whereIn(string $column, array $values, int $type): self { return $this; }

        /** Accept the requested stable sort order for current projects. */
        public function order(array $columns): self { return $this; }
    }

    final class ActSeasonDatabaseFixture implements \Joomla\Database\DatabaseInterface
    {
        public ?ActSeasonQueryFixture $query = null;

        /** Return a query spy that records project-scoped joins. */
        public function createQuery(): ActSeasonQueryFixture
        {
            return new ActSeasonQueryFixture();
        }

        /** Leave table identifiers readable for ownership checks. */
        public function quoteName(string $identifier, ?string $alias = null): string
        {
            return $identifier;
        }

        /** Store the prepared native query without executing SQL. */
        public function setQuery(ActSeasonQueryFixture $query): void
        {
            $this->query = $query;
        }

        /** Return a mix of valid and malformed legacy import records. */
        public function loadObjectList(): array
        {
            return [
                (object) [
                    'id' => '0005', 'name' => 'Liga', 'project_alias' => 'main',
                    'round_id' => '0012', 'round_alias' => 'final',
                    'country' => 'deu', 'alpha2' => 'de', 'country_name' => 'Germany',
                    'country_picture' => '', 'federation' => '0007', 'federation_name' => 'Europa',
                ],
                (object) [
                    'id' => ['100'], 'name' => 'Invalid', 'federation' => 1,
                ],
                ['id' => 200],
                (object) [
                    'id' => 6, 'name' => ['not text'], 'project_alias' => ['bad'],
                    'round_id' => '12trailing', 'round_alias' => new \stdClass(),
                    'country' => ['invalid'], 'alpha2' => false,
                    'country_name' => ['invalid'], 'country_picture' => new \stdClass(),
                    'federation' => true, 'federation_name' => new \stdClass(),
                ],
            ];
        }
    }

    $database = new ActSeasonDatabaseFixture();
    $params = new \Joomla\Registry\Registry(['cfg_which_database' => 0, 'cfg_flags_css' => 1]);
    $app = new class implements \Joomla\CMS\Application\CMSApplicationInterface {
        public array $messages = [];
        /** Record frontend errors so successful queries can assert none. */
        public function enqueueMessage(string $message, string $type): void
        {
            $this->messages[] = [$message, $type];
        }
    };

    set_error_handler(static function (int $severity, string $message): never {
        throw new \ErrorException($message, 0, $severity);
    });

    try {
        $data = (new \Diddipoeler\Module\SportsManagementActSeason\Site\Helper\ActSeasonHelper())
            ->getData([1], $params, $app, $database);
    } finally {
        restore_error_handler();
    }

    if ($app->messages !== [] || count($data['list']) !== 2
        || $data['list'][0]->project_slug !== '5:main'
        || $data['list'][0]->roundcode !== '12:final'
        || $data['list'][0]->country !== 'DEU'
        || $data['list'][0]->federation !== 7
        || $data['federations'][7]->name !== 'Europa'
        || $data['list'][1]->name !== ''
        || $data['list'][1]->roundcode !== ''
        || $data['list'][1]->federation !== 0
        || $data['list'][1]->country !== ''
        || !isset($data['countriesByFederation'][7]['DEU'])) {
        throw new \RuntimeException('Current-season import rows were not validated consistently.');
    }

    $joinedRound = false;
    foreach ($database->query?->joins ?? [] as $join) {
        if (str_contains($join, '#__sportsmanagement_round')
            && str_contains($join, 'r.project_id')
            && str_contains($join, 'pro.id')) {
            $joinedRound = true;
        }
    }

    if (!$joinedRound) {
        throw new \RuntimeException('A current-round lookup was not scoped to the selected project.');
    }

    echo "Module current-season query ownership and corrupt-row regression passed.\n";
}
