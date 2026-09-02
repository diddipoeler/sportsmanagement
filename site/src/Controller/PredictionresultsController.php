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
    public function selectprojectround(): void
    {
        if (!Session::checkToken('post')) {
            throw new \RuntimeException(Text::_('JINVALID_TOKEN'), 403);
        }

        $model = $this->getPredictionResultsModel();
        $this->setRedirect($this->buildResultsRoute($model));
    }

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
        $this->getApplication()->enqueueMessage(
            Text::_('JTOOLBAR_REBUILD') . ': ' . $updated,
            'message'
        );
        $this->setRedirect($this->buildResultsRoute($model));
    }

    private function getPredictionResultsModel(): PredictionresultsModel
    {
        $model = $this->getModel('Predictionresults');
        if (!$model instanceof PredictionresultsModel) {
            throw new \RuntimeException('PredictionresultsModel is unavailable.', 500);
        }
        return $model;
    }

    private function getPredictionPointsModel(): PredictionpointsModel
    {
        $model = $this->getModel('Predictionpoints');
        if (!$model instanceof PredictionpointsModel) {
            throw new \RuntimeException('PredictionpointsModel is unavailable.', 500);
        }
        return $model;
    }

    private function buildResultsRoute(PredictionresultsModel $model): string
    {
        $input = $this->getApplication()->getInput();
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
