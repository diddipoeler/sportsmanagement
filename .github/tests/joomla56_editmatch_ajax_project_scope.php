<?php
/**
 * Regression tests for Joomla 5/6 frontend match AJAX project scoping.
 *
 * Usage: php .github/tests/joomla56_editmatch_ajax_project_scope.php
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\MVC\Controller {
    class BaseController
    {
        public object $input;
        public object $app;
    }
}

namespace Joomla\CMS\Language {
    class Text
    {
        public static function _(string $key): string { return $key; }
    }
}

namespace Joomla\CMS\Factory {
}

namespace Joomla\Database {
    interface DatabaseInterface {}
    final class ParameterType
    {
        public const INTEGER = 'integer';
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Model {
    class ResultsAccessModel
    {
        public static int $project = 0;
        public static int $database = 0;
        public static array $matches = [];
        public static bool $allowed = true;
        public function setProjectId(int $id): void { self::$project = $id; }
        public function setDatabaseSelector(int $selector): void { self::$database = $selector; }
        public function canEditMatches(array $ids): bool
        {
            self::$matches = $ids;
            return self::$allowed;
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Service {
    class SportsManagementDatabaseResolver
    {
        public static array $selectors = [];

        public static function resolve(\Joomla\Database\DatabaseInterface $db, int $selector): \Joomla\Database\DatabaseInterface
        {
            self::$selectors[] = $selector;
            return $db;
        }
    }

    class MatchMutationService
    {
        public function __construct(
            public readonly \Joomla\Database\DatabaseInterface $joomlaDatabase,
            public readonly \Joomla\Database\DatabaseInterface $sportsDatabase,
            public readonly int $userId,
            public readonly string $modified
        ) {}
    }
}

namespace {
    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);

    final class InputBag
    {
        public function __construct(public array $values = []) {}
        public function getInt(string $name, int $default = 0): int
        {
            return (int) ($this->values[$name] ?? $default);
        }
    }

    final class QueryStub
    {
        public array $binds = [];
        public array $where = [];
        public string $table = '';
        public function select(string $value): self { return $this; }
        public function from(string $value): self
        {
            $this->table = $value;
            return $this;
        }
        public function where(string $value): self
        {
            $this->where[] = $value;
            return $this;
        }
        public function bind(string $name, mixed $value, string $type): self
        {
            $this->binds[$name] = $value;
            return $this;
        }
    }

    final class DatabaseStub implements \Joomla\Database\DatabaseInterface
    {
        public int $queryCount = 0;
        public ?QueryStub $query = null;
        public int $parentMatchId = 71;
        public bool $failLookup = false;
        public function createQuery(): QueryStub { return new QueryStub(); }
        public function quoteName(string $value): string { return $value; }
        public function setQuery(QueryStub $query, int $offset = 0, int $limit = 0): self
        {
            $this->query = $query;
            ++$this->queryCount;
            return $this;
        }
        public function loadResult(): int
        {
            if ($this->failLookup) {
                throw new \RuntimeException('simulated DB error');
            }
            return $this->parentMatchId;
        }
    }

    $database = new DatabaseStub();
    class FactoryStub
    {
        public static ?DatabaseStub $database = null;
        public static function getContainer(): object
        {
            return new class {
                public function get(string $id): \Joomla\Database\DatabaseInterface
                {
                    return \FactoryStub::$database;
                }
            };
        }
    }
    \class_alias(FactoryStub::class, 'Joomla\CMS\Factory');

    require_once $root . '/site/src/Controller/MatchesController.php';

    FactoryStub::$database = $database;
    $controller = new \Diddipoeler\Component\SportsManagement\Site\Controller\MatchesController();
    $controller->input = (object) ['post' => new InputBag(['p' => 9, 'matchid' => 71, 'cfg_which_database' => 1])];

    $scope = new \ReflectionMethod($controller, 'assertMatchScope');
    $scope->invoke($controller, 71);
    $access = \Diddipoeler\Component\SportsManagement\Site\Model\ResultsAccessModel::class;
    if ($access::$project !== 9 || $access::$database !== 1 || $access::$matches !== [71]) {
        throw new \RuntimeException('AJAX scope did not check the actual match/project/DB selector.');
    }

    $assertDenied = static function (int $id) use ($scope, $controller): void {
        try {
            $scope->invoke($controller, $id);
        } catch (\RuntimeException $error) {
            if ($error->getCode() === 403) {
                return;
            }
            throw $error;
        }
        throw new \RuntimeException('AJAX write was permitted with an invalid match context.');
    };

    $assertDenied(22);
    $assertDenied(0);
    $controller->input->post->values['p'] = 0;
    $assertDenied(71);
    $controller->input->post->values['p'] = 9;
    $controller->input->post->values['cfg_which_database'] = -9;
    $scope->invoke($controller, 71);
    if ($access::$database !== 0) {
        throw new \RuntimeException('Negative POST database selector was not normalised.');
    }
    $controller->input->post->values['cfg_which_database'] = 1;
    $access::$allowed = false;
    $assertDenied(71);
    $access::$allowed = true;

    $lookup = new \ReflectionMethod($controller, 'parentMatchId');
    if ($lookup->invoke($controller, '#__sportsmanagement_match_event', 40, false) !== 71
        || $database->query?->table !== '#__sportsmanagement_match_event'
        || $database->query?->binds[':childId'] !== 40) {
        throw new \RuntimeException('Event deletion did not resolve the persisted parent match.');
    }
    if ($lookup->invoke($controller, '#__sportsmanagement_match_player', 11, true) !== 71
        || $database->query?->table !== '#__sportsmanagement_match_player'
        || end(\Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver::$selectors) !== 1) {
        throw new \RuntimeException('Substitution deletion did not resolve its persisted parent.');
    }
    $count = $database->queryCount;
    if ($lookup->invoke($controller, '#__sportsmanagement_match_event', -2, false) !== 0
        || $database->queryCount !== $count) {
        throw new \RuntimeException('Invalid child ID triggered a lookup.');
    }
    $database->failLookup = true;
    if ($lookup->invoke($controller, '#__sportsmanagement_match_commentary', 40, false) !== 0) {
        throw new \RuntimeException('Failed parent lookup must deny deletion.');
    }

    // The legacy controller accepts POST data while the URL/user state may
    // specify a conflicting database. Mutation must use the POST value that
    // was checked by ResultsAccessModel and by the substitution parent query.
    $controller->app = new class {
        public function getIdentity(): object { return (object) ['id' => 28]; }
        public function getUserState(string $key, int $default): int { return 99; }
    };
    $service = (new \ReflectionMethod($controller, 'mutationService'))->invoke($controller);
    if (!$service instanceof \Diddipoeler\Component\SportsManagement\Site\Service\MatchMutationService
        || end(\Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver::$selectors) !== 1
        || $service->userId !== 28) {
        throw new \RuntimeException('AJAX mutation did not reuse the POST-authorized database.');
    }

    $source = file_get_contents($root . '/site/src/Controller/MatchesController.php');
    foreach (['saveEvent', 'saveSubstitution', 'removeSubstitution', 'saveComment', 'deleteEvent', 'deleteCommentary'] as $method) {
        if (preg_match('/assertMatchScope\([^;]+;\s+\$result = \$this->mutationService\(\)->' . $method . '\(/s', $source) !== 1) {
            throw new \RuntimeException('Missing pre-mutation scope validation for ' . $method);
        }
    }

    $javascript = file_get_contents($root . '/site/assets/js/editmatch-editing.js');
    $options = file_get_contents($root . '/site/src/View/Editmatch/EditmatchEventViewTrait.php');
    foreach (["payload.set('p',", "payload.set('matchid',", "payload.set('cfg_which_database',"] as $required) {
        if (!str_contains($javascript, $required)) {
            throw new \RuntimeException('Missing editor AJAX request context: ' . $required);
        }
    }
    if (!str_contains($options, "'projectId' =>") || !str_contains($options, "'databaseSelector' =>")) {
        throw new \RuntimeException('Editmatch script options omit project or database context.');
    }

    echo "Joomla 5/6 editmatch AJAX project and child-record scopes OK\n";
}
