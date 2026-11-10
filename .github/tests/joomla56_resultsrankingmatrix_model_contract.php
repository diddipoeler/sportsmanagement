<?php
/**
 * Joomla 5/6 native triple results/ranking/matrix model regression.
 *
 * Uses the preceding native resultsranking model's isolated Joomla stubs so
 * the inherited calculation and the additional matrix share the same tests.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

require __DIR__ . '/joomla56_resultsranking_model_contract.php';
require dirname(__DIR__, 2) . '/site/src/Model/ResultsrankingmatrixModel.php';

$tripleClass = \Diddipoeler\Component\SportsManagement\Site\Model\ResultsrankingmatrixModel::class;
$matrixClass = \Diddipoeler\Component\SportsManagement\Site\Model\MatrixModel::class;
$rankingClass = \Diddipoeler\Component\SportsManagement\Site\Model\RankingModel::class;
$adapterClass = \Diddipoeler\Component\SportsManagement\Site\Legacy\RankingCalculationAdapter::class;
$triple = (new \ReflectionClass($tripleClass))->newInstanceWithoutConstructor();

set_error_handler(static function (int $level, string $message): never {
    throw new \ErrorException($message, 0, $level);
});

try {
    $matrix = new $matrixClass();
    $ranking = new $rankingClass();
    $triple->choose(42, 4);
    $tripleClass::$roundid = 999;
    $tripleClass::$cfg_which_database = 1;

    $data = $triple->getTripleData((object) ['id' => '42'], [], $ranking, $matrix);

    if ($data['roundid'] !== 5 || $data['divisionid'] !== 4
        || count($data['fixtures']) !== 1
        || count($data['standings']) !== 1
        || count($data['teams']) !== 2
        || count($data['results']) !== 1
        || $data['divisions'] !== []
        || $data['fixtures'][0]['score'] !== '2:–'
        || $data['standings'][0]['rows'][0]['points'] !== '10'
        || $matrix->calls !== [['divisions'], ['teams', 4], ['results', 42, 5, 4]]
        || $adapterClass::$calls[array_key_last($adapterClass::$calls)]['round'] !== 5) {
        throw new \RuntimeException('Three-panel display lost the validated round, data or score.');
    }

    // All three views must use a single project-specific result read,
    // rather than independently querying fixtures per panel.
    if (count(array_filter(
        $matrix->calls,
        static fn (array $call): bool => ($call[0] ?? '') === 'results'
    )) !== 1) {
        throw new \RuntimeException('Combined triple view duplicated match queries.');
    }

    // A project-wide table may show its own published division labels, but
    // must still load exactly one match result collection.
    $matrix = new $matrixClass();
    $ranking = new $rankingClass();
    $triple->choose(42, 0);
    $all = $triple->getTripleData((object) ['id' => 42], [], $ranking, $matrix);

    if (count($all['divisions']) !== 1
        || ($all['divisions'][0]->id ?? null) !== 4
        || $matrix->calls !== [['teams', 0], ['results', 42, 5, 0], ['divisions']]) {
        throw new \RuntimeException('Combined triple model lost project divisions or queried matches twice.');
    }

    // Neither an unrelated project nor an invalid division may trigger a
    // sport ranking calculation or access another project's match records.
    foreach ([
        [777, 4],
        [42, 999],
        [[], 4],
    ] as [$rawProjectId, $divisionId]) {
        $matrix = new $matrixClass();
        $ranking = new $rankingClass();
        $triple->choose(42, $divisionId);
        $before = count($adapterClass::$calls);
        $bad = $triple->getTripleData(
            (object) ['id' => $rawProjectId],
            [],
            $ranking,
            $matrix
        );

        if ($bad['roundid'] !== 0 || $bad['fixtures'] !== []
            || $bad['standings'] !== [] || $bad['teams'] !== []
            || $bad['results'] !== [] || $bad['divisions'] !== []
            || count($adapterClass::$calls) !== $before
            || count(array_filter(
                $matrix->calls,
                static fn (array $call): bool => ($call[0] ?? '') === 'results'
            )) !== 0) {
            throw new \RuntimeException('Foreign selection reached triple-view calculation or results.');
        }
    }
} finally {
    restore_error_handler();
}

echo "Joomla 5/6 native triple results/ranking/matrix model and query reuse OK\n";
