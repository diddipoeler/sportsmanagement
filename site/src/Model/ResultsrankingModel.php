<?php
/**
 * Native Joomla 5/6 results ranking model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\CombinedRankingPresentationHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\MatrixPresentationHelper;
use Diddipoeler\Component\SportsManagement\Site\Legacy\RankingCalculationAdapter;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

class ResultsrankingModel extends SportsManagementProjectModel
{
    public static int $divisionid = 0;
    public static int $roundid = 0;
    public static int $projectid = 0;
    public static int $cfg_which_database = 0;
    public static int $show_ranking_reiter = 0;

    /**
     * Initialise results ranking filters from the Joomla site request.
     *
     * Calls the native project model constructor, captures the selected
     * division, round, project, database and ranking tab as legacy static
     * fields, and synchronises the historical project model when loaded.
     *
     * @param array $config Optional Joomla model configuration.
     * @param MVCFactoryInterface|null $factory MVC factory injection.
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);

        $input = $this->siteApplication()->getInput();
        self::$divisionid = $this->divisionId;
        self::$roundid = max(0, $input->getInt('r', 0));
        self::$projectid = $this->projectId;
        self::$cfg_which_database = $input->getInt('cfg_which_database', 0) === 1 ? 1 : 0;
        self::$show_ranking_reiter = $input->getInt('show_ranking_reiter', 0) === 1 ? 1 : 0;

        if (class_exists('sportsmanagementModelProject')) {
            \sportsmanagementModelProject::$projectid = self::$projectid;
            \sportsmanagementModelProject::$cfg_which_database = self::$cfg_which_database;
        }
    }


    /**
     * Load the combined results list and actual sport-specific standings.
     *
     * Rejects a missing or foreign project before constructing query models.
     * Accepts only round IDs from this project's native round list. The
     * validated round, division and database selection apply to both the
     * matrix result query and RankingCalculationAdapter, ensuring the
     * displayed standings are calculated using existing sport rules.
     * Validates selected division membership against published divisions of
     * this project before ranking calculations. Returns the same scoped raw
     * team/match objects as the normalized fixture data so the three-panel
     * results/ranking/matrix view does not repeat its database reads.
     * Names and numeric statistics are normalized to plain display data.
     *
     * @param object $project Active Joomla SportsManagement project record.
     * @param array<string,mixed> $config Current Joomla display configuration.
     * @param RankingModel|null $rankingModel Optional native ranking test double.
     * @param MatrixModel|null $matrixModel Optional native matrix test double.
     * @return array{roundid:int,divisionid:int,fixtures:array,standings:array,teams:array,results:array} Read-only frontend data and the validated matrix source rows.
     */
    public function getCombinedData(
        object $project,
        array $config = [],
        ?RankingModel $rankingModel = null,
        ?MatrixModel $matrixModel = null
    ): array {
        $empty = [
            'roundid' => 0,
            'divisionid' => 0,
            'fixtures' => [],
            'standings' => [],
            'teams' => [],
            'results' => [],
        ];
        $projectId = $this->getProjectId();

        if ($projectId <= 0 || (int) ($project->id ?? 0) !== $projectId) {
            return $empty;
        }

        $rankingModel ??= new RankingModel();
        $rankingModel->setProjectId($projectId);
        $allowedRounds = [];
        foreach ($rankingModel->getRounds('ASC', false) as $round) {
            $id = is_object($round) ? (int) ($round->id ?? 0) : 0;
            if ($id > 0) {
                $allowedRounds[$id] = true;
            }
        }

        if ($allowedRounds === []) {
            return $empty;
        }

        $roundId = self::$roundid;
        if ($roundId <= 0 || !isset($allowedRounds[$roundId])) {
            $current = $rankingModel->getCurrentRound();
            $roundId = $current > 0 && isset($allowedRounds[$current])
                ? $current : (int) array_key_last($allowedRounds);
        }

        $matrixModel ??= new MatrixModel();
        $matrixModel->setProjectId($projectId);
        $divisionId = max(0, $this->getDivisionId());

        // The ranking adapter accepts arbitrary positive division IDs as
        // calculator inputs. Reject IDs outside this selected project's
        // published divisions before any ranking arithmetic or match reads.
        if ($divisionId > 0) {
            $owned = false;

            foreach ($matrixModel->getDivisions() as $division) {
                if (is_object($division) && (int) ($division->id ?? 0) === $divisionId) {
                    $owned = true;
                    break;
                }
            }

            if (!$owned) {
                return $empty;
            }
        }

        $rankingConfig = array_merge($config, $rankingModel->getTemplateConfig('ranking'));
        $rankings = RankingCalculationAdapter::calculate(
            $rankingModel,
            $project,
            $rankingConfig,
            self::$cfg_which_database,
            $roundId,
            0,
            $roundId,
            0,
            0,
            $divisionId
        );

        MatrixModel::$roundid = $roundId;
        MatrixModel::$divisionid = $divisionId;
        $matrixTeams = $matrixModel->getProjectTeamsIndexed($divisionId);
        $matrixResults = $matrixModel->getMatrixResults($projectId);

        return [
            'roundid' => $roundId,
            'divisionid' => $divisionId,
            'teams' => $matrixTeams,
            'results' => $matrixResults,
            'fixtures' => MatrixPresentationHelper::fixtures(
                $matrixResults,
                MatrixPresentationHelper::teams($matrixTeams)
            ),
            'standings' => CombinedRankingPresentationHelper::standings(
                (array) ($rankings['currentRanking'] ?? []),
                $rankingModel->getProjectTeamsIndexed(0)
            ),
        ];
    }
}
