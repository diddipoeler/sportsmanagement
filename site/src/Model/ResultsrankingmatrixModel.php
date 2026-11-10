<?php
/**
 * Native Joomla 5/6 model for combined results, ranking and matrix.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

/**
 * Share one project-bound ranking calculation and match query across panels.
 *
 * Reuses the existing native results/ranking model without introducing a
 * second set of sport-specific points rules or duplicate match retrieval.
 */
final class ResultsrankingmatrixModel extends ResultsrankingModel
{
    /**
     * Prepare standings, results and cross-table from one validated dataset.
     *
     * Validates the selected project before creating the matrix model. The
     * inherited getCombinedData() checks round membership and division
     * ownership before calculating sport standings and loading any matches.
     * This method adds only the project-owned division list necessary for
     * the native matrix layout, avoiding repeated fixture database queries.
     *
     * @param object $project Current native project record.
     * @param array<string,mixed> $config Joomla site template configuration.
     * @param RankingModel|null $rankingModel Optional native ranking model fixture.
     * @param MatrixModel|null $matrixModel Optional native matrix model fixture.
     * @return array{roundid:int,divisionid:int,fixtures:array,standings:array,teams:array,results:array,divisions:array} All three display datasets.
     */
    public function getTripleData(
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
            'divisions' => [],
        ];

        $id = $project->id ?? null;

        if ($this->getProjectId() <= 0
            || (!is_int($id) && (!is_string($id) || preg_match('/^[1-9][0-9]*$/D', $id) !== 1))
            || (int) $id !== $this->getProjectId()) {
            return $empty;
        }

        $matrixModel ??= new MatrixModel();
        $data = parent::getCombinedData($project, $config, $rankingModel, $matrixModel);

        $data['divisions'] = $data['roundid'] > 0 && $data['divisionid'] === 0
            ? $matrixModel->getDivisions()
            : [];

        return $data;
    }
}
