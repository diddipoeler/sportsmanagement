<?php
/**
 * Native Joomla 5/6 controller for SportsManagement prediction results.
 *
 * @version    4.24.00
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Controller;

\defined('_JEXEC') or die;

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
        $this->loadRouteHelpers();
        $input = $this->getApplication()->getInput();
        $config = $model->getResultsConfig();

        return \JSMPredictionHelperRoute::getPredictionResultsRoute(
            $model->getPredictionGameId(),
            $model->getSelectedRoundId($config),
            $model->getProjectId(),
            $model->getPredictionMemberId(),
            '',
            $model->getGroupId(),
            $input->getInt('cfg_which_database', 0)
        );
    }

    private function loadRouteHelpers(): void
    {
        if (!class_exists('sportsmanagementHelperRoute', false)) {
            require_once JPATH_SITE . '/components/com_sportsmanagement/helpers/route.php';
        }
        if (!class_exists('JSMPredictionHelperRoute', false)) {
            require_once JPATH_SITE . '/components/com_sportsmanagement/helpers/predictionroute.php';
        }
    }
}
