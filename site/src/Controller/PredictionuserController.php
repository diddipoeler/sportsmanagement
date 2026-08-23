<?php
namespace Diddipoeler\Component\SportsManagement\Site\Controller;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\PredictionmemberModel;
use Diddipoeler\Component\SportsManagement\Site\Model\PredictionuserModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Session\Session;

final class PredictionuserController extends BaseController
{
    public function save(): void
    {
        $this->assertPostToken();
        $model = $this->getWriterModel();
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
        $this->setRedirect($this->profileRoute($model));
    }

    public function cancel(): void
    {
        $this->assertPostToken();
        $model = $this->getEditorModel();
        $member = $model->getEditableMember();
        if (!$model->canEditMember($member)) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }
        $this->setRedirect($this->profileRoute($model));
    }

    private function assertPostToken(): void
    {
        if (!Session::checkToken('post')) {
            throw new \RuntimeException(Text::_('JINVALID_TOKEN'), 403);
        }
    }

    private function getEditorModel(): PredictionuserModel
    {
        $model = $this->getModel('Predictionuser');
        if (!$model instanceof PredictionuserModel) {
            throw new \RuntimeException('PredictionuserModel is unavailable.', 500);
        }
        return $model;
    }

    private function getWriterModel(): PredictionmemberModel
    {
        $model = $this->getModel('Predictionmember');
        if (!$model instanceof PredictionmemberModel) {
            throw new \RuntimeException('PredictionmemberModel is unavailable.', 500);
        }
        return $model;
    }

    private function profileRoute(PredictionuserModel $model): string
    {
        $this->loadRouteHelper();
        $input = Factory::getApplication()->getInput();
        $memberId = $model->getSelectedMemberNumericId();

        return \JSMPredictionHelperRoute::getPredictionMemberRoute(
            $model->getPredictionGameId(),
            $memberId,
            0,
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
