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

use Diddipoeler\Component\SportsManagement\Site\Helper\PredictionRouteHelper;
use Diddipoeler\Component\SportsManagement\Site\Model\PredictionrankingModel;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Session\Session;

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

        $this->setRedirect(PredictionRouteHelper::ranking(
            $model->getPredictionGameId(),
            $model->getProjectId(),
            $model->getRoundId(),
            $model->getGroupId(),
            $model->getGroupRanking(),
            $model->getRankingType(),
            $model->getFromRoundId(),
            $model->getToRoundId(),
            $this->input->getInt('cfg_which_database', 0)
        ));
    }
}
