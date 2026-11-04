<?php
/**
 * Native Joomla 5/6 controller for SportsManagement prediction results.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Controller;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\PredictionRouteHelper;
use Diddipoeler\Component\SportsManagement\Site\Model\PredictionpointsModel;
use Diddipoeler\Component\SportsManagement\Site\Model\PredictionresultsModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Session\Session;

final class PredictionresultsController extends BaseController
{
    /**
     * Apply the selected prediction project and round to the results page.
     *
     * Accepts only requests with a valid Joomla POST CSRF token, resolves the
     * results model and redirects to its canonical results route. The selected
     * game, round, project, prediction member, group and database are preserved
     * by the route builder rather than being copied from unvalidated input.
     *
     * @return void
     * @throws \RuntimeException When the token is invalid or the model is unavailable.
     */
    public function selectprojectround(): void
    {
        if (!Session::checkToken('post')) {
            throw new \RuntimeException(Text::_('JINVALID_TOKEN'), 403);
        }

        $model = $this->getPredictionResultsModel();
        $this->setRedirect($this->buildResultsRoute($model));
    }

    /**
     * Recalculate prediction points for the currently selected results.
     *
     * Verifies the Joomla POST token and the points model's administrative
     * permission before changing stored results. Afterwards, displays the
     * number of updated scores and redirects back to the selected results view.
     *
     * @return void
     * @throws \RuntimeException When the token, permission or model is invalid.
     */
    public function recalculatepoints(): void
    {
        if (!Session::checkToken('post')) {
            throw new \RuntimeException(Text::_('JINVALID_TOKEN'), 403);
        }

        $model = $this->getPredictionPointsModel();
        if (!$model->isAllowedAdmin()) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $updated = $model->recalculatePoints($model->getResultsConfig());
        $this->app->enqueueMessage(
            Text::_('JTOOLBAR_REBUILD') . ': ' . $updated,
            'message'
        );
        $this->setRedirect($this->buildResultsRoute($model));
    }

    /**
     * Resolve and verify the Joomla MVC model for prediction results.
     *
     * Prevents subsequent result-navigation code from operating on a missing
     * or unrelated model returned by the component MVC factory.
     *
     * @return PredictionresultsModel The validated results model.
     * @throws \RuntimeException When the model cannot be resolved.
     */
    private function getPredictionResultsModel(): PredictionresultsModel
    {
        $model = $this->getModel('Predictionresults');
        if (!$model instanceof PredictionresultsModel) {
            throw new \RuntimeException('PredictionresultsModel is unavailable.', 500);
        }
        return $model;
    }

    /**
     * Resolve the model responsible for recalculating prediction scores.
     *
     * Ensures that permission checks and score updates are performed only
     * through the expected native Joomla 5/6 prediction-points model.
     *
     * @return PredictionpointsModel The validated points model.
     * @throws \RuntimeException When the model cannot be resolved.
     */
    private function getPredictionPointsModel(): PredictionpointsModel
    {
        $model = $this->getModel('Predictionpoints');
        if (!$model instanceof PredictionpointsModel) {
            throw new \RuntimeException('PredictionpointsModel is unavailable.', 500);
        }
        return $model;
    }

    /**
     * Build the canonical frontend route for the selected prediction results.
     *
     * Collects the game, selected round, project, member and group from the
     * results model and forwards the selected database ID from Joomla input
     * to the shared prediction route helper.
     *
     * @param PredictionresultsModel $model Model providing the current selection.
     * @return string Route for the prediction results view.
     */
    private function buildResultsRoute(PredictionresultsModel $model): string
    {
        $input = $this->input;
        $config = $model->getResultsConfig();

        return PredictionRouteHelper::results(
            $model->getPredictionGameId(),
            $model->getSelectedRoundId($config),
            $model->getProjectId(),
            $model->getPredictionMemberId(),
            $model->getGroupId(),
            $input->getInt('cfg_which_database', 0)
        );
    }
}
