<?php
/**
 * Native Joomla 5/6 controller for SportsManagement prediction ranking.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Controller;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\PredictionRouteHelper;
use Diddipoeler\Component\SportsManagement\Site\Model\PredictionrankingModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Session\Session;

final class PredictionrankingController extends BaseController
{
    /**
     * Apply a prediction ranking project/round selection and redirect.
     * Requires a valid Joomla POST token, loads the native ranking model
     * and preserves game, project, group, round, ranking options and
     * the selected SportsManagement database in the destination URL.
     * @return void
     * @throws \RuntimeException If CSRF validation or model resolution fails.
     */
    public function selectprojectround(): void
    {
        if (!Session::checkToken('post')) {
            throw new \RuntimeException(Text::_('JINVALID_TOKEN'), 403);
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
