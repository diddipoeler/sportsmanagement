<?php
/**
 * Joomla 5/6 resultsranking native model and sport calculation regressions.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\MVC\Factory {
    interface MVCFactoryInterface {}
}

namespace Diddipoeler\Component\SportsManagement\Site\Helper {
    final class RankingPresentationHelper {
        /**
         * Simulate sport-specific native ranking statistic formatting.
         *
         * @param object $row Calculated team statistics.
         * @param string $column Known ranking column.
         * @return string Calculated score text or malformed test value.
         */
        public static function value(object $row, string $column): string {
            $key = match ($column) {
                'PLAYED'=>'cnt_matches', 'WINS'=>'cnt_won',
                'TIES'=>'cnt_draw', 'LOSSES'=>'cnt_lost', 'POINTS'=>'sum_points',
                default=>'none',
            };
            return (string) ($row->{$key} ?? '');
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Model {
    class SportsManagementProjectModel {
        protected int $projectId = 0;
        protected int $divisionId = 0;

        /**
         * Set a native project/division without Joomla application bootstrap.
         *
         * @param int $project Selected project ID.
         * @param int $division Selected division ID.
         * @return void
         */
        public function choose(int $project, int $division = 0): void {
            $this->projectId = $project;
            $this->divisionId = $division;
        }

        /** @return int Active project identifier. */
        public function getProjectId(): int { return $this->projectId; }

        /** @return int Selected division identifier. */
        public function getDivisionId(): int { return $this->divisionId; }

        /** @param int $project Project ID. @return void */
        public function setProjectId(int $project): void { $this->projectId = $project; }
    }

    class RankingModel extends SportsManagementProjectModel {
        public array $calls = [];

        /**
         * Return native rounds belonging to this project.
         *
         * @param string $order Requested chronological order.
         * @param bool $slug Whether route slugs should be included.
         * @return array<int,object> Existing project round IDs.
         */
        public function getRounds(string $order = 'ASC', bool $slug = true): array {
            $this->calls[] = ['rounds', $this->projectId];
            return [(object) ['id'=>3], (object) ['id'=>5]];
        }

        /** @return int Valid current round ID of this project. */
        public function getCurrentRound(): int { return 5; }

        /**
         * Return ranking template settings for calculated sport rules.
         *
         * @param string $name Joomla template identifier.
         * @return array<string,mixed> Ranking configuration.
         */
        public function getTemplateConfig(string $name): array {
            $this->calls[] = ['template', $name];
            return ['point_value'=>3];
        }

        /**
         * Provide project-team names keyed by their database IDs.
         *
         * @param int $division Optional team division filter.
         * @return array<int,object> Native team data.
         */
        public function getProjectTeamsIndexed(int $division): array {
            $this->calls[] = ['teams', $division];
            return [
                11=>(object) ['name'=>'FC <script>alert(1)</script>'],
                12=>(object) ['name'=>'Bayern & Berlin'],
            ];
        }
    }

    class MatrixModel extends SportsManagementProjectModel {
        public static int $roundid = 0;
        public static int $divisionid = 0;
        public array $calls = [];

        /**
         * Return the native project teams used to label match results.
         *
         * @param int $division Requested division ID.
         * @return array<int,object> Fixture team IDs and names.
         */
        public function getProjectTeamsIndexed(int $division): array {
            $this->calls[] = ['teams', $division];
            return [
                (object) ['projectteamid'=>11, 'name'=>'FC <script>alert(1)</script>'],
                (object) ['projectteamid'=>12, 'name'=>'Bayern & Berlin'],
            ];
        }

        /**
         * Verify the native fixture query uses the selected project/round.
         *
         * @param int $project Requested project ID.
         * @return array<int,object> Isolated project-owned fixtures.
         */
        public function getMatrixResults(int $project): array {
            if ($project !== $this->projectId || self::$roundid !== 5 || self::$divisionid !== 4) {
                throw new \RuntimeException('Unscoped resultsranking matrix query.');
            }
            $this->calls[] = ['results', $project, self::$roundid, self::$divisionid];
            return [
                (object) [
                    'projectteam1_id'=>11, 'projectteam2_id'=>12,
                    'e1'=>'2', 'e2'=>'<img onerror=alert(1)>',
                    'roundname'=>'Matchday 5',
                ],
            ];
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Legacy {
    final class RankingCalculationAdapter {
        public static array $calls = [];

        /**
         * Capture selected matchday and database flags instead of requiring
         * the historical sport-specific JSMRanking runtime in the fixture.
         *
         * @param \Diddipoeler\Component\SportsManagement\Site\Model\RankingModel $model Project-scoped native model.
         * @param object $project Current project row.
         * @param array $config Ranking calculation options.
         * @param int $database Selected database.
         * @param int $round Selected round.
         * @param int $from Initial round bound.
         * @param int $to Last round bound.
         * @param int $part Season fraction selector.
         * @param int $type Home/away selector.
         * @param int $division Selected project division.
         * @return array<string,mixed> Representative sport ranking results.
         */
        public static function calculate(
            \Diddipoeler\Component\SportsManagement\Site\Model\RankingModel $model,
            object $project, array $config, int $database, int $round,
            int $from, int $to, int $part, int $type, int $division
        ): array {
            self::$calls[] = compact('project', 'config', 'database', 'round', 'from', 'to', 'division');
            return ['currentRanking'=>[
                4=>[
                    11=>(object) ['rank'=>1, 'cnt_matches'=>5, 'cnt_won'=>3, 'cnt_draw'=>1, 'cnt_lost'=>1, 'sum_points'=>10],
                    12=>(object) ['rank'=>2, 'cnt_matches'=>5, 'cnt_won'=>1, 'cnt_draw'=>1, 'cnt_lost'=>3, 'sum_points'=>'<script>'],
                ],
            ]];
        }
    }
}

namespace {
    define('_JEXEC', 1);
    $root=dirname(__DIR__, 2);
    require $root . '/site/src/Helper/MatrixPresentationHelper.php';
    require $root . '/site/src/Helper/CombinedRankingPresentationHelper.php';
    require $root . '/site/src/Model/ResultsrankingModel.php';

    $model=(new \ReflectionClass(
        \Diddipoeler\Component\SportsManagement\Site\Model\ResultsrankingModel::class
    ))->newInstanceWithoutConstructor();
    $ranking=new \Diddipoeler\Component\SportsManagement\Site\Model\RankingModel();
    $matrix=new \Diddipoeler\Component\SportsManagement\Site\Model\MatrixModel();
    $adapter=\Diddipoeler\Component\SportsManagement\Site\Legacy\RankingCalculationAdapter::class;
    $modelClass=\Diddipoeler\Component\SportsManagement\Site\Model\ResultsrankingModel::class;

    set_error_handler(static function(int $level,string $message): never {
        throw new \ErrorException($message,0,$level);
    });

    try {
        $model->choose(42, 4);
        $modelClass::$roundid=999;
        $modelClass::$cfg_which_database=1;

        $empty=$model->getCombinedData((object) ['id'=>777], [], $ranking, $matrix);
        if ($empty['fixtures'] !== [] || $empty['standings'] !== []
            || $ranking->calls !== [] || $matrix->calls !== []) {
            throw new \RuntimeException('Foreign project triggered resultsranking queries.');
        }

        $data=$model->getCombinedData((object) ['id'=>42], ['extra'=>'yes'], $ranking, $matrix);
        if ($data['roundid'] !== 5 || $data['divisionid'] !== 4
            || count($data['fixtures']) !== 1 || count($data['standings']) !== 1
            || $data['fixtures'][0]['score'] !== '2:–'
            || $data['standings'][0]['rows'][0]['name'] !== 'FC <script>alert(1)</script>'
            || $data['standings'][0]['rows'][0]['points'] !== '10'
            || $data['standings'][0]['rows'][1]['points'] !== '–'
            || $matrix->calls !== [['teams',4],['results',42,5,4]]
            || $adapter::$calls[0]['round'] !== 5
            || $adapter::$calls[0]['to'] !== 5
            || $adapter::$calls[0]['database'] !== 1
            || $adapter::$calls[0]['division'] !== 4
            || $adapter::$calls[0]['config']['point_value'] !== 3) {
            throw new \RuntimeException('Resultsranking lost sport ranking or project round context.');
        }
    } finally {
        restore_error_handler();
    }

    echo "Joomla 5/6 resultsranking round, project, division and score safety OK\n";
}
