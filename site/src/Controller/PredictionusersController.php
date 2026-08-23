<?php
namespace Diddipoeler\Component\SportsManagement\Site\Controller;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\PredictionmemberModel;
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

    /**
     * Compatibility target for historic predictionusers.savememberdata forms.
     * The write itself is handled by the native, target-validating writer model.
     */
    public function savememberdata(): void
    {
        $this->checkPostToken();
        $model = $this->getPredictionMemberModel();
        $member = $model->getEditableMember();

        if (!$model->canEditMember($member)) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $saved = $model->saveMember(Factory::getApplication()->getInput()->post->getArray());
        Factory::getApplication()->enqueueMessage(
            Text::_($saved
                ? 'COM_SPORTSMANAGEMENT_PRED_USERS_CONTROLLER_MSG_1'
                : 'COM_SPORTSMANAGEMENT_PRED_USERS_CONTROLLER_ERROR_3'),
            $saved ? 'message' : 'error'
        );

        $this->setRedirect($this->buildEditorRoute($model));
    }

    /** Compatibility target for historic predictionusers.cancel forms. */
    public function cancel($key = null): void
    {
        $this->checkPostToken();
        $model = $this->getPredictionMemberModel();
        $this->setRedirect($this->buildEditorRoute($model));
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

    private function getPredictionMemberModel(): PredictionmemberModel
    {
        $model = $this->getModel('Predictionmember');
        if (!$model instanceof PredictionmemberModel) {
            throw new \RuntimeException('PredictionmemberModel is unavailable.', 500);
        }
        return $model;
    }

    private function buildMemberRoute(PredictionusersModel $model): string
    {
        $this->loadRouteHelper();
        $input = Factory::getApplication()->getInput();

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

    private function buildEditorRoute(PredictionmemberModel $model): string
    {
        $this->loadRouteHelper();
        $input = Factory::getApplication()->getInput();

        return \JSMPredictionHelperRoute::getPredictionMemberRoute(
            $model->getPredictionGameId(),
            $model->getSelectedMemberNumericId(),
            null,
            $model->getProjectId(),
            $model->getGroupId(),
            $model->getRoundId(),
            $input->getInt('cfg_which_database', 0)
        );
    }

    private function loadRouteHelper(): void
    {
        if (!class_exists('JSMPredictionHelperRoute', false)) {
            require_once JPATH_SITE . '/components/com_sportsmanagement/helpers/predictionroute.php';
        }
    }
}
