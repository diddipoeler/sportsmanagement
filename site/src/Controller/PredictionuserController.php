<?php
/**
 * Native Joomla 5/6 controller for editing a prediction user.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Controller;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\PredictionRouteHelper;
use Diddipoeler\Component\SportsManagement\Site\Model\PredictionmemberModel;
use Diddipoeler\Component\SportsManagement\Site\Model\PredictionuserModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Session\Session;

final class PredictionuserController extends BaseController
{
    /**
     * Persist edits to the current user's authorised prediction member.
     * Requires a valid CSRF token, loads the native prediction member
     * write model and checks member-specific edit rights before saving.
     * Reports a translated Joomla status and redirects to the profile.
     * @return void
     * @throws \RuntimeException If the member is not editable.
     */
    public function save(): void
    {
        $this->assertPostToken();
        $model = $this->getWriterModel();
        $member = $model->getEditableMember();
        if (!$model->canEditMember($member)) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $app = $this->app;
        $saved = $model->saveMember($this->input->post->getArray());
        $app->enqueueMessage(
            Text::_($saved
                ? 'COM_SPORTSMANAGEMENT_PRED_USERS_CONTROLLER_MSG_1'
                : 'COM_SPORTSMANAGEMENT_PRED_USERS_CONTROLLER_ERROR_3'),
            $saved ? 'message' : 'error'
        );
        $this->setRedirect($this->profileRoute($model));
    }

    /**
     * Cancel prediction profile editing without saving member changes.
     * Checks the POST token and current user's member-level permission
     * then redirects to the member profile using the selection route.
     * @return void
     * @throws \RuntimeException If the user has no edit access.
     */
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

    /**
     * Enforce Joomla POST CSRF validation for prediction profile tasks.
     * Used by both save and cancel so cross-site requests cannot
     * perform user actions without a valid Joomla session token.
     * @return void
     * @throws \RuntimeException When the POST token is invalid.
     */
    private function assertPostToken(): void
    {
        if (!Session::checkToken('post')) {
            throw new \RuntimeException(Text::_('JINVALID_TOKEN'), 403);
        }
    }

    /**
     * Resolve the native prediction-user model for profile display.
     * Verifies the Joomla MVC factory has returned the expected typed
     * model before reading member data or constructing redirects.
     * @return PredictionuserModel Model for member profile state.
     * @throws \RuntimeException When model resolution fails.
     */
    private function getEditorModel(): PredictionuserModel
    {
        $model = $this->getModel('Predictionuser');
        if (!$model instanceof PredictionuserModel) {
            throw new \RuntimeException('PredictionuserModel is unavailable.', 500);
        }
        return $model;
    }

    /**
     * Resolve the native prediction-member model for authorised writes.
     * Ensures member changes are delegated only to the correct model
     * rather than a legacy or unrelated Joomla MVC instance.
     * @return PredictionmemberModel Prediction member write model.
     * @throws \RuntimeException When model resolution fails.
     */
    private function getWriterModel(): PredictionmemberModel
    {
        $model = $this->getModel('Predictionmember');
        if (!$model instanceof PredictionmemberModel) {
            throw new \RuntimeException('PredictionmemberModel is unavailable.', 500);
        }
        return $model;
    }

    /**
     * Create the canonical prediction member profile destination.
     * Combines the selected game, member, project, group, round and
     * database from the model and current request into one Joomla URL.
     * @param PredictionuserModel $model Model containing the selected member context.
     * @return string Joomla route for the prediction member profile.
     */
    private function profileRoute(PredictionuserModel $model): string
    {
        $input = $this->input;
        $memberId = $model->getSelectedMemberNumericId();

        return PredictionRouteHelper::member(
            $model->getPredictionGameId(),
            $memberId,
            null,
            $model->getProjectId(),
            $model->getGroupId(),
            $model->getRoundId(),
            $input->getInt('cfg_which_database', 0)
        );
    }
}
