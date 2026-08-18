<?php
namespace Diddipoeler\Component\SportsManagement\Site\Controller;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\PredictionrankingModel;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;

final class PredictionrankingController extends BaseController
{
    public function selectprojectround(): void
    {
        if (!Session::checkToken()) {
            throw new \RuntimeException('Invalid token.', 403);
        }

        $model = $this->getModel('Predictionranking');
        if (!$model instanceof PredictionrankingModel) {
            throw new \RuntimeException('Prediction ranking model not available.', 500);
        }

        $query = Uri::buildQuery([
            'option' => 'com_sportsmanagement',
            'view' => 'predictionranking',
            'cfg_which_database' => $this->input->getInt('cfg_which_database', 0),
            'prediction_id' => $model->getPredictionGameId(),
            'pj' => $model->getProjectId(),
            'r' => $model->getRoundId(),
            'pggroup' => $model->getGroupId(),
            'pggrouprank' => $model->getGroupRanking(),
            'type' => $model->getRankingType(),
            'from' => $model->getFromRoundId(),
            'to' => $model->getToRoundId(),
        ]);

        $this->setRedirect(Route::_('index.php?' . $query, false));
    }
}
