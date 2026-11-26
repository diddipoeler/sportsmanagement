<?php
/**
 * Regression for optional club visit statistics.
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
namespace Diddipoeler\Component\SportsManagement\Site\Model {
    class SportsManagementProjectModel {
        /** Provide the database used by the native reader. @return object Test driver. */
        protected function getDatabase(): object { return $GLOBALS['clubDb']; }
    }
}
namespace {
    define('_JEXEC', 1);
    require dirname(__DIR__, 2) . '/site/src/Model/ClubinfoViewDataModel.php';
    $db = new class {
        public mixed $row = null;
        public bool $failHit = false;
        public bool $failRead = false;
        public int $writes = 0;
        public int $queries = 0;
        /** Produce the query fixture or simulate a construction failure. @return static Query. */
        public function createQuery(): static {
            ++$this->queries;
            if ($this->failRead || ($this->failHit && $this->queries === 2)) {
                throw new \RuntimeException('Database unavailable');
            }
            return $this;
        }
        /** Quote a field in the isolated query fixture. @param string $name Identifier. @param string|null $alias Alias. @return string Field. */
        public function quoteName(string $name, ?string $alias = null): string { return $name; }
        /** Accept selected fields. @param string $field Projection. @return static Query. */
        public function select(string $field): static { return $this; }
        /** Accept table name. @param string $table Table. @return static Query. */
        public function from(string $table): static { return $this; }
        /** Accept a query predicate. @param string $condition Predicate. @return static Query. */
        public function where(string $condition): static { return $this; }
        /** Accept a bound identifier. @param string $key Placeholder. @param int $id ID. @param string $type Type. @return static Query. */
        public function bind(string $key, int $id, string $type): static { return $this; }
        /** Accept a hit update. @param string $table Table. @return static Query. */
        public function update(string $table): static { return $this; }
        /** Accept the update expression. @param string $expr SQL assignment. @return static Query. */
        public function set(string $expr): static { return $this; }
        /** Accept a prepared query. @param object $query Query. @param int $offset Offset. @param int $limit Limit. @return static Driver. */
        public function setQuery(object $query, int $offset = 0, int $limit = 0): static { return $this; }
        /** Return the selected club. @return mixed Fixture row. */
        public function loadObject(): mixed { return $this->row; }
        /** Count an actual statistics write. @return bool Success. */
        public function execute(): bool { ++$this->writes; return true; }
    };
    $GLOBALS['clubDb'] = $db;
    $model = (new \ReflectionClass(\Diddipoeler\Component\SportsManagement\Site\Model\ClubinfoViewDataModel::class))->newInstanceWithoutConstructor();
    foreach ([null, false, (object) ['id' => 8]] as $bad) {
        $db->row = $bad;
        if ($model->getClubById(7, true) !== null || $db->writes !== 0) {
            throw new \RuntimeException('Missing or mismatched club caused a visit write.');
        }
    }
    $db->row = (object) ['id' => '7'];
    $db->queries = 0;
    $db->failHit = true;
    if ($model->getClubById(7, true) !== $db->row || $db->writes !== 0) {
        throw new \RuntimeException('Optional visit query failure prevented club display.');
    }
    $db->failHit = false;
    if ($model->getClubById(7, true) !== $db->row || $db->writes !== 1) {
        throw new \RuntimeException('Valid visit count was not written.');
    }
    $db->failRead = true;
    if ($model->getClubById(7, true) !== null || $db->writes !== 1) {
        throw new \RuntimeException('Club read construction failure was not isolated.');
    }
    $normalise = new \ReflectionMethod($model, 'normaliseIds');
    foreach ([
        [['0007', 7, 2, true, ['3'], '4junk', '99999999999999999999999999'], [7, 2]],
        ['0004|5,6; 7', [4, 5, 6, 7]],
        [false, []],
        [new \stdClass(), []],
    ] as [$raw, $expected]) {
        if ($normalise->invoke($model, $raw) !== $expected) {
            throw new \RuntimeException('Malformed club season or playground IDs were accepted.');
        }
    }
    $queries = $db->queries;
    if ($model->getPlaygroundsByIds([true, ['1'], '3junk']) !== []
        || $db->queries !== $queries) {
        throw new \RuntimeException('Malformed playground selection reached the database.');
    }
    echo "Club visit counter and identifier guards OK\n";
}
