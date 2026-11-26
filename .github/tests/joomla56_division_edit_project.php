<?php
/**
 * Regression for native division edit project lookup.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\Database {
    final class ParameterType { public const INTEGER = 'integer'; }
}
namespace Diddipoeler\Component\SportsManagement\Administrator\Model {
    class SportsManagementAdminModel {
        /** Inject the isolated model database. @param object $db Query fixture. */
        public function __construct(private object $db) {}
        /** Return the assigned connection. @return object Database fixture. */
        protected function getDatabase(): object { return $this->db; }
    }
}
namespace {
    define('_JEXEC', 1);
    require dirname(__DIR__, 2) . '/admin/src/Model/DivisionModel.php';
    $db = new class {
        public int $queries = 0;
        public array $bound = [];
        public mixed $result = null;
        /** Build a fluent query. @return static Query fixture. */
        public function createQuery(): static { ++$this->queries; return $this; }
        /** Preserve the table or column name. @param string $name SQL identifier. @return string Identifier. */
        public function quoteName(string $name): string { return $name; }
        /** Capture query selection. @param string $columns Selected columns. @return static Query. */
        public function select(string $columns): static { return $this; }
        /** Capture the project table. @param string $table Table identifier. @return static Query. */
        public function from(string $table): static { return $this; }
        /** Accept the bound predicate. @param string $condition SQL predicate. @return static Query. */
        public function where(string $condition): static { return $this; }
        /** Store the typed project ID. @param string $key Placeholder. @param mixed $value Value. @param string $type Parameter type. @return static Query. */
        public function bind(string $key, mixed $value, string $type): static { $this->bound = [$key, $value, $type]; return $this; }
        /** Accept a limited lookup. @param object $query Query fixture. @param int $offset Offset. @param int $limit Limit. @return static Database. */
        public function setQuery(object $query, int $offset, int $limit): static { return $this; }
        /** Return the configured project record. @return mixed Query result. */
        public function loadObject(): mixed { return $this->result; }
    };
    $model = new \Diddipoeler\Component\SportsManagement\Administrator\Model\DivisionModel($db);
    if ($model->getProjectForDivision(0) !== null || $db->queries !== 0) {
        throw new \RuntimeException('Invalid project reached the database.');
    }
    $db->result = (object) ['id' => '7', 'name' => 'Test project'];
    if ($model->getProjectForDivision(7) !== $db->result
        || $db->bound !== [':divisionProjectId', 7, 'integer']) {
        throw new \RuntimeException('Division project lookup lost its typed binding or selected connection.');
    }
    foreach ([null, false, (object) ['id' => 8]] as $invalid) {
        $db->result = $invalid;
        if ($model->getProjectForDivision(7) !== null) {
            throw new \RuntimeException('Unexpected project record accepted.');
        }
    }
    echo "Native division project lookup OK\n";
}
