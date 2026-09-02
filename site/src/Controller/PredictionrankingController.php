<?php
/**
 * Native Joomla 5/6 controller for SportsManagement prediction ranking.
 *
 * @version    4.24.00
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
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
