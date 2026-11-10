<?php
/**
 * Native Joomla 5/6 model for the combined results and match matrix view.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

/**
 * Reuse project-scoped matrix data for the historical combined results view.
 *
 * The old resultsmatrix MVC route relied on global model classes and template
 * path registration. This model delegates its read-only data queries to the
 * already migrated native MatrixModel and keeps the same Joomla project and
 * division selection, avoiding a second unscoped legacy result query.
 */
final class ResultsmatrixModel extends SportsManagementProjectModel
{
    /**
     * Load the selected project's teams, fixtures and optional divisions.
     *
     * Rejects absent or invalid project selections before constructing the
     * matrix model or performing any database operations. The delegate's
     * round, division and project IDs originate from the same Joomla site
     * request as this model; the native MatrixModel binds the selected
     * project and ensures round/division data belongs to that project.
     *
     * @param MatrixModel|null $matrix Optional native matrix model for isolated tests.
     * @return array{teams:array,results:array,divisions:array,divisionid:int,roundid:int} Prepared data.
     */
    public function getCombinedData(?MatrixModel $matrix = null): array
    {
        $empty = [
            'teams' => [],
            'results' => [],
            'divisions' => [],
            'divisionid' => 0,
            'roundid' => 0,
        ];
        $projectId = $this->getProjectId();

        if ($projectId <= 0) {
            return $empty;
        }

        $matrix ??= new MatrixModel();
        $matrix->setProjectId($projectId);
        $divisionId = max(0, $this->getDivisionId());

        return [
            'teams' => $matrix->getProjectTeamsIndexed($divisionId),
            'results' => $matrix->getMatrixResults($projectId),
            'divisions' => $divisionId === 0 ? $matrix->getDivisions() : [],
            'divisionid' => $divisionId,
            'roundid' => max(0, MatrixModel::$roundid),
        ];
    }
}
