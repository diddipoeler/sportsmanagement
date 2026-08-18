<?php
namespace Diddipoeler\Component\SportsManagement\Site\Controller;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\PredictionusersModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Session\Session;

final class PredictionusersController extends BaseController
{
    public function select(): void
    {
        $this->checkPostToken();
        $model = $this->getPredictionUsersModel();
        $this->setRedirect($this->buildMemberRoute($model));
    }

    public function selectprojectround(): void
    {
        $this->checkPostToken();
        $model = $this->getPredictionUsersModel();
        $this->setRedirect($this->buildMemberRoute($model));
    }

    private function checkPostToken(): void
    {
        if (!Session::checkToken('post')) {
            throw new \RuntimeException(Text::_('JINVALID_TOKEN'), 403);
        }
    }

    private function getPredictionUsersModel(): PredictionusersModel
    {
        $model = $this->getModel('Predictionusers');
        if (!$model instanceof PredictionusersModel) {
            throw new \RuntimeException('PredictionusersModel is unavailable.', 500);
        }
        return $model;
    }

    private function buildMemberRoute(PredictionusersModel $model): string
    {
        $this->loadRouteHelpers();
        $input = Factory::getApplication()->input;

        return \JSMPredictionHelperRoute::getPredictionMemberRoute(
            $model->getPredictionGameId(),
            $input->post->getInt('uid', $model->getSelectedMemberNumericId()),
            null,
            $model->getProjectId(),
            $model->getGroupId(),
            $model->getRoundId(),
            $input->getInt('cfg_which_database', 0)
        );
    }

    private function loadRouteHelpers(): void
    {
        if (!class_exists('sportsmanagementHelperRoute', false)) {
            \JLoader::register(
                'sportsmanagementHelperRoute',
                JPATH_SITE . '/components/com_sportsmanagement/helpers/route.php'
            );
        }
        if (!class_exists('JSMPredictionHelperRoute', false)) {
            \JLoader::register(
                'JSMPredictionHelperRoute',
                JPATH_SITE . '/components/com_sportsmanagement/helpers/predictionroute.php'
            );
        }
    }
}
