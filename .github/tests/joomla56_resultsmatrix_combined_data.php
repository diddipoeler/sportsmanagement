<?php
/**
 * Native Joomla 5/6 combined resultsmatrix project-bound contract.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);
namespace Diddipoeler\Component\SportsManagement\Site\Model {
    class SportsManagementProjectModel {
        protected int $projectId = 0;
        protected int $divisionId = 0;
        /** @param int $project Project ID. @param int $division Division ID. @return void */
        public function choose(int $project, int $division = 0): void {
            $this->projectId = $project; $this->divisionId = $division;
        }
        /** @return int Current project ID. */
        public function getProjectId(): int { return $this->projectId; }
        /** @return int Current division ID. */
        public function getDivisionId(): int { return $this->divisionId; }
    }
    class MatrixModel {
        public static int $roundid = 9;
        public int $projectId = 0;
        public array $calls = [];
        /** @param int $project Project ID. @return void */
        public function setProjectId(int $project): void {
            $this->projectId = $project; $this->calls[] = ['project', $project];
        }
        /** @param int $division Division ID. @return array<int,object> Project teams. */
        public function getProjectTeamsIndexed(int $division): array {
            $this->calls[] = ['teams', $division];
            return [
                2 => (object) ['projectteamid'=>2, 'name'=>'<b>Home</b>'],
                3 => (object) ['projectteamid'=>3, 'name'=>'FC Köln'],
            ];
        }
        /** @param int $project Project ID. @return array<int,object> Project matches. */
        public function getMatrixResults(int $project): array {
            if ($project !== $this->projectId) {
                throw new \RuntimeException('Unscoped results matrix.');
            }
            $this->calls[] = ['results', $project];
            return [(object) [
                'projectteam1_id'=>2, 'projectteam2_id'=>3,
                'e1'=>'02', 'e2'=>'<script>', 'roundname'=>'Final <script>',
            ]];
        }
        /** @return array<int,object> Published divisions. */
        public function getDivisions(): array {
            $this->calls[] = ['divisions'];
            return [(object) ['id'=>1,'name'=>'North']];
        }
    }
}
namespace {
    define('_JEXEC', 1);
    require dirname(__DIR__, 2) . '/site/src/Model/ResultsmatrixModel.php';
    require dirname(__DIR__, 2) . '/site/src/Helper/MatrixPresentationHelper.php';

    set_error_handler(static function(int $severity,string $message): never {
        throw new \ErrorException($message,0,$severity);
    });
    try {
        $m = new \Diddipoeler\Component\SportsManagement\Site\Model\ResultsmatrixModel();
        $db = new \Diddipoeler\Component\SportsManagement\Site\Model\MatrixModel();

        if ($m->getCombinedData($db)['results'] !== [] || $db->calls !== []) {
            throw new \RuntimeException('Empty project triggered matrix query.');
        }
        $m->choose(42, 7);
        $data = $m->getCombinedData($db);
        if ($db->calls !== [['project',42],['teams',7],['results',42]]
            || $data['roundid'] !== 9 || count($data['results']) !== 1) {
            throw new \RuntimeException('Combined model did not scope matrix data.');
        }
        $helper=\Diddipoeler\Component\SportsManagement\Site\Helper\MatrixPresentationHelper::class;
        $fixtures=$helper::fixtures($data['results'],$helper::teams($data['teams']));
        if ($fixtures !== [[
            'home'=>'<b>Home</b>', 'away'=>'FC Köln',
            'score'=>'2:–', 'round'=>'Final <script>',
        ]]) {
            throw new \RuntimeException('Combined fixtures accepted unsafe score data.');
        }
        $m->choose(42);
        $db->calls=[];
        $data=$m->getCombinedData($db);
        if ($db->calls !== [['project',42],['teams',0],['results',42],['divisions']]
            || count($data['divisions']) !== 1) {
            throw new \RuntimeException('Combined project division list was missing.');
        }
    } finally {
        restore_error_handler();
    }
    echo "Joomla 5/6 native combined resultsmatrix model OK\n";
}
