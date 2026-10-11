<?php
/**
 * Joomla 5/6 regression tests for frontend match selection and ranking team IDs.
 *
 * Usage: php .github/tests/joomla56_match_ranking_id_validation.php
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\MVC\Factory {
    interface MVCFactoryInterface {}
}

namespace Joomla\CMS\MVC\Controller {
    class BaseController {}
}

namespace Joomla\Database {
    final class ParameterType
    {
        public const INTEGER = 'integer';
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Model {
    class SportsManagementProjectModel
    {
        protected int $projectId = 0;
        public static ?object $database = null;
        public function __construct(mixed $config = [], mixed $factory = null) {}
        public function getDatabase(): object
        {
            return self::$database;
        }
        public function getProject(): object
        {
            return (object) ['editorgroup' => 20];
        }
        public function siteApplication(): object
        {
            return new class {
                public function getIdentity(): object
                {
                    return new class {
                        public int $id = 12;
                        public function getAuthorisedGroups(): array { return [20]; }
                        public function authorise(string $name, string $asset): bool { return false; }
                    };
                }
            };
        }
    }
}

namespace {
    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);
    require_once $root . '/site/src/Controller/ResultsController.php';
    require_once $root . '/site/src/Model/ResultsAccessModel.php';
    require_once $root . '/modules/mod_sportsmanagement_ranking/src/Helper/RankingHelper.php';

    final class MatchQueryStub
    {
        public array $whereIn = [];
        public array $binds = [];

        public function select(string $columns): self { return $this; }
        public function from(string $table): self { return $this; }
        public function join(string $kind, string $condition): self { return $this; }
        public function whereIn(string $column, array $ids, string $type): self
        {
            $this->whereIn[$column] = $ids;
            return $this;
        }
        public function where(string $condition): self { return $this; }
        public function bind(string $name, mixed $value, string $type): self
        {
            $this->binds[$name] = $value;
            return $this;
        }
    }

    final class MatchDbStub
    {
        public int $queries = 0;
        public ?MatchQueryStub $query = null;
        public function createQuery(): MatchQueryStub { return new MatchQueryStub(); }
        public function quoteName(string $name): string { return $name; }
        public function setQuery(MatchQueryStub $query): self
        {
            $this->query = $query;
            ++$this->queries;
            return $this;
        }
        public function loadResult(): int
        {
            return count($this->query?->whereIn['m.id'] ?? []);
        }
    }

    $controller = new \Diddipoeler\Component\SportsManagement\Site\Controller\ResultsController();
    $normalise = new \ReflectionMethod($controller, 'validMatchIds');

    foreach ([
        [[2, '9', 2], [2, 9]],
        [['3abc', '4'], []],
        [['3', []], []],
        [[0], []],
        [[null], []],
        [['9999999999999999999999'], []],
        [[], []],
    ] as [$input, $expected]) {
        if ($normalise->invoke($controller, $input) !== $expected) {
            throw new \RuntimeException('Frontend bulk results accepted invalid checkbox IDs.');
        }
    }

    $db = new MatchDbStub();
    \Diddipoeler\Component\SportsManagement\Site\Model\SportsManagementProjectModel::$database = $db;
    $model = new \Diddipoeler\Component\SportsManagement\Site\Model\ResultsAccessModel();
    $model->setProjectId(17);

    foreach ([['3abc'], [3, []], [3, 0], ['9999999999999999999999'], []] as $ids) {
        if ($model->canEditMatches($ids)) {
            throw new \RuntimeException('Frontend ACL authorized an invalid selection.');
        }
    }
    if ($db->queries !== 0) {
        throw new \RuntimeException('Frontend ACL queried the database for invalid match IDs.');
    }

    if (!$model->canEditMatches([2, '9', 2])
        || $db->query?->whereIn['m.id'] !== [2, 9]
        || $db->query?->binds[':selectedProjectId'] !== 17) {
        throw new \RuntimeException('Valid project-scoped match editing selection failed.');
    }

    $ranking = new \Diddipoeler\Module\SportsManagementRanking\Site\Helper\RankingHelper();
    $firstId = new \ReflectionMethod($ranking, 'firstId');

    foreach ([
        [7, 7], ['7', 7], ['7:team-alias', 7],
        [['7:club', '8:other'], 7],
        ['7spoof', 0], ['7:spoof:extra', 0],
        [['7bad'], 0], [[['7']], 0],
        ['9999999999999999999999999', 0], [0, 0],
        [null, 0], [false, 0], [7.5, 0],
    ] as [$value, $expected]) {
        if ($firstId->invoke($ranking, $value) !== $expected) {
            throw new \RuntimeException('Ranking module parsed an invalid visible team ID.');
        }
    }

    echo "Joomla 5/6 match selection, ACL and ranking IDs OK\n";
}
