<?php
/**
 * Joomla 5/6 project panel bound-query and standard-position regression.
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
    }
}

namespace {
    define('_JEXEC', 1);
    require dirname(__DIR__, 2) . '/admin/src/Service/ProjectPanelService.php';

    final class PanelQuery
    {
        public array $bindings = [];

        /** Accept selected columns for the fake query. @param mixed $columns Selected columns. @return self Query fixture. */
        public function select(mixed $columns): self { return $this; }

        /** Accept the source table. @param string $table SQL table. @return self Query fixture. */
        public function from(string $table): self { return $this; }

        /** Accept a SQL predicate. @param string $condition Predicate text. @return self Query fixture. */
        public function where(string $condition): self { return $this; }

        /**
         * Verify and record typed parameters without connecting to a database.
         * @param string $name Placeholder name.
         * @param mixed $value Bound value.
         * @param string $type Joomla parameter type.
         * @return self Query fixture.
         */
        public function bind(string $name, mixed $value, string $type): self
        {
            if (!is_int($value) || $type !== 'integer') {
                throw new \RuntimeException('Panel did not bind an integer parameter.');
            }
            $this->bindings[$name] = $value;
            return $this;
        }
    }

    final class PanelDatabase implements \Joomla\Database\DatabaseInterface
    {
        public array $counts = [];
        public mixed $positions = [];
        public ?object $project = null;
        public array $queries = [];
        public array $inserted = [];
        public array $transactions = [];
        public ?int $failPosition = null;
        private array $snapshot = [];

        /** Create a query double. @return PanelQuery New query fixture. */
        public function createQuery(): PanelQuery { return new PanelQuery(); }

        /** Preserve a trusted SQL name. @param string $name Table or column name. @return string Same name. */
        public function quoteName(string|array $name): string|array { return $name; }

        /**
         * Record each prepared query for binding assertions.
         * @param PanelQuery $query Prepared query.
         * @return void
         */
        public function setQuery(PanelQuery $query): void { $this->queries[] = $query; }

        /** Return the next simulated COUNT result. @return mixed Raw count. */
        public function loadResult(): mixed { return array_shift($this->counts); }

        /**
         * Load the stored project metadata for the explicit initialization action.
         * @return object|null Project record, or null when missing.
         */
        public function loadObject(): ?object { return $this->project; }

        /** Return simulated sport position IDs. @return mixed Raw position list. */
        public function loadColumn(): mixed { return $this->positions; }

        /**
         * Snapshot rows before starting a simulated database transaction.
         * @return void
         */
        public function transactionStart(): void
        {
            $this->snapshot = $this->inserted;
            $this->transactions[] = 'start';
        }

        /**
         * Record a successful transaction commit.
         * @return void
         */
        public function transactionCommit(): void { $this->transactions[] = 'commit'; }

        /**
         * Restore the initial rows after a simulated insertion failure.
         * @return void
         */
        public function transactionRollback(): void
        {
            $this->inserted = $this->snapshot;
            $this->transactions[] = 'rollback';
        }

        /**
         * Record a standard-position insertion rather than writing to a database.
         * @param string $table Target table.
         * @param object $row Project-position association.
         * @return bool Successful simulated insertion.
         */
        public function insertObject(string $table, object $row): bool
        {
            if ($row->position_id === $this->failPosition) {
                return false;
            }
            $this->inserted[] = [$table, $row->project_id, $row->position_id];
            return true;
        }
    }

    $db = new PanelDatabase();
    $panel = new \Diddipoeler\Component\SportsManagement\Administrator\Service\ProjectPanelService($db);
    $project = (object) ['id' => '0005', 'sports_type_id' => 2, 'project_art_id' => 1];

    foreach ([null, false, [], '2junk', -1, PHP_INT_MAX . '0'] as $badCount) {
        $db->counts = [$badCount];
        try {
            $panel->getCounts($project);
            throw new \RuntimeException('Malformed panel count was accepted.');
        } catch (\UnexpectedValueException) {
            if ($db->inserted !== []) {
                throw new \RuntimeException('Malformed count triggered a standard position insertion.');
            }
        }
    }

    $db->queries = [];
    $invalidProject = clone $project;
    $invalidProject->id = ['5'];
    if (array_sum($panel->getCounts($invalidProject)) !== 0 || $db->queries !== []) {
        throw new \RuntimeException('Malformed project ID reached SQL.');
    }

    $db->counts = [0, 0, 0, 3, 4];
    $counts = $panel->getCounts($project);
    if ($counts !== ['divisions' => 0, 'positions' => 0, 'referees' => 0, 'teams' => 3, 'rounds' => 4]
        || $db->inserted !== [] || count($db->queries) !== 5
        || $db->queries[0]->bindings !== [':panelId' => 5]
        || !$panel::canInitialize($project, 0) || $panel::canInitialize($project, 1)) {
        throw new \RuntimeException('Passive panel display must not initialize standard positions.');
    }

    $db->queries = [];
    $db->project = $project;
    $db->counts = [0, 0, 1];
    $db->positions = ['0007', 7, '8junk', ['9'], 8, false, 0];

    if (!$panel->initializeForProject(5)
        || $db->transactions !== ['start', 'commit']
        || $db->inserted !== [['#__sportsmanagement_project_position', 5, 7]]
        || $db->queries[0]->bindings !== [':panelProject' => 5]
        || $db->queries[1]->bindings !== [':panelId' => 5]
        || $db->queries[2]->bindings !== [':panelSport' => 2]
        || $db->queries[3]->bindings !== [':panelProject' => 5, ':panelPosition' => 7]) {
        throw new \RuntimeException('Explicit standard position initialization or bound parameters regressed.');
    }

    $db->inserted = [];
    $db->counts = [1];
    if ($panel->initializeForProject(5) || $db->inserted !== []) {
        throw new \RuntimeException('Existing project positions were not preserved.');
    }

    $db->project = null;
    if ($panel->initializeForProject(5)) {
        throw new \RuntimeException('Missing project was initialized.');
    }

    $db->project = (object) ['id' => 5, 'sports_type_id' => 2, 'project_art_id' => 3];
    $db->counts = [0];
    if ($panel->initializeForProject(5) || $db->inserted !== []) {
        throw new \RuntimeException('Exempt project type was initialized.');
    }

    $db->project = $project;
    $db->counts = [0, 0, 0];
    $db->positions = [7, 8];
    $db->failPosition = 8;
    $db->transactions = [];
    try {
        $panel->initializeForProject(5);
        throw new \LogicException('Failed insertion was treated as a successful initialization.');
    } catch (\RuntimeException $e) {
        if ($db->transactions !== ['start', 'rollback'] || $db->inserted !== []) {
            throw new \RuntimeException('A partial position setup was not rolled back.');
        }
    }

    $db->failPosition = null;
    $db->counts = [0];
    $db->positions = [];
    if ($panel->initializeForProject(5)) {
        throw new \RuntimeException('Empty position definitions reported a successful setup.');
    }

    $view = file_get_contents(dirname(__DIR__, 2) . '/admin/src/View/Project/HtmlView.php');
    if (!str_contains($view, '$model->getItem()')
        || !str_contains($view, '$model->getForm()')
        || str_contains($view, '$this->get(')
        || substr_count($view, 'LegacyModelDatabaseHelper::selector()') !== 2) {
        throw new \RuntimeException('Project view must use native model access and validated database selection.');
    }

    $controller = file_get_contents(dirname(__DIR__, 2) . '/admin/src/Controller/ProjectController.php');
    $layout = file_get_contents(dirname(__DIR__, 2) . '/admin/tmpl/project/panel.php');
    $dispatcher = file_get_contents(dirname(__DIR__, 2) . '/admin/src/Dispatcher/Dispatcher.php');
    if (!str_contains($controller, "Session::checkToken('post')")
        || !str_contains($controller, "authorise('core.edit', 'com_sportsmanagement')")
        || !str_contains($layout, "HTMLHelper::_('form.token')")
        || !str_contains($layout, 'method="post"')
        || !str_contains($dispatcher, "'project.initializestandardpositions'")) {
        throw new \RuntimeException('Standard position action must use the protected native POST path.');
    }

    echo "Joomla 5/6 read-only project panel and explicit position checks passed.\n";
}
