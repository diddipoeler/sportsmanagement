<?php
/**
 * Native Joomla 5/6 controller for SportsManagement prediction users.
 *
 * @version    4.24.00
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Controller;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\PredictionRouteHelper;
use Diddipoeler\Component\SportsManagement\Site\Model\PredictionmemberModel;
use Diddipoeler\Component\SportsManagement\Site\Model\PredictionusersModel;
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

        $app = $this->getApplication();
        $saved = $model->saveMember($app->getInput()->post->getArray());
        $app->enqueueMessage(
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
        $input = $this->getApplication()->getInput();

        return PredictionRouteHelper::member(
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
        $input = $this->getApplication()->getInput();

        return PredictionRouteHelper::member(
            $model->getPredictionGameId(),
            $model->getSelectedMemberNumericId(),
            null,
            $model->getProjectId(),
            $model->getGroupId(),
            $model->getRoundId(),
            $input->getInt('cfg_which_database', 0)
        );
    }
}
