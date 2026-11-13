<?php
/**
 * Native Joomla 5/6 controller for SportsManagement prediction users.
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
use Diddipoeler\Component\SportsManagement\Site\Model\PredictionusersModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Session\Session;

final class PredictionusersController extends BaseController
{
    /**
     * Select a prediction member and redirect to its canonical page.
     *
     * Checks the Joomla POST token and uses the native prediction users
     * model to preserve game, project, group, round and database context.
     *
     * @return void
     */
    public function select(): void
    {
        $this->checkPostToken();
        $model = $this->getPredictionUsersModel();
        $this->setRedirect($this->buildMemberRoute($model));
    }

    /**
     * Apply a project and round selection to prediction member navigation.
     *
     * Validates the Joomla POST token, resolves the prediction users
     * model and redirects using its currently selected context.
     *
     * @return void
     */
    public function selectprojectround(): void
    {
        $this->checkPostToken();
        $model = $this->getPredictionUsersModel();
        $this->setRedirect($this->buildMemberRoute($model));
    }

    /**
     * Save submitted prediction member details using the native writer model.
     *
     * Validates the POST token, resolves the member and checks whether the
     * current user may edit that specific member. Reports the save result
     * and redirects back to the member editor without leaking write access.
     *
     * @return void
     */
    public function savememberdata(): void
    {
        $this->checkPostToken();
        $model = $this->getPredictionMemberModel();
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

        $this->setRedirect($this->buildEditorRoute($model));
    }

    /**
     * Cancel prediction member editing without modifying stored data.
     *
     * Validates the form token and confirms member-specific edit access
     * before returning to the member editor route. The cancel task cannot
     * be used to navigate directly into an unauthorised member editor.
     *
     * @param string|null $key Unused legacy record key.
     * @return void
     */
    public function cancel($key = null): void
    {
        $this->checkPostToken();
        $model = $this->getPredictionMemberModel();
        $member = $model->getEditableMember();

        if (!$model->canEditMember($member)) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $this->setRedirect($this->buildEditorRoute($model));
    }

    /**
     * Reject prediction member requests with an invalid POST CSRF token.
     *
     * Uses Joomla's session token verification before a form selection
     * or member update is allowed to continue.
     *
     * @return void
     */
    private function checkPostToken(): void
    {
        if (!Session::checkToken('post')) {
            throw new \RuntimeException(Text::_('JINVALID_TOKEN'), 403);
        }
    }

    /**
     * Resolve the native prediction users model for member navigation.
     *
     * Verifies the MVC factory result so route construction cannot
     * proceed with an unavailable or incompatible model.
     *
     * @return PredictionusersModel Validated prediction users model.
     */
    private function getPredictionUsersModel(): PredictionusersModel
    {
        $model = $this->getModel('Predictionusers');
        if (!$model instanceof PredictionusersModel) {
            throw new \RuntimeException('PredictionusersModel is unavailable.', 500);
        }
        return $model;
    }

    /**
     * Resolve the native model responsible for prediction member editing.
     *
     * Ensures member access checks and writes run through the expected
     * Joomla 5/6 component model implementation.
     *
     * @return PredictionmemberModel Validated member model.
     */
    private function getPredictionMemberModel(): PredictionmemberModel
    {
        $model = $this->getModel('Predictionmember');
        if (!$model instanceof PredictionmemberModel) {
            throw new \RuntimeException('PredictionmemberModel is unavailable.', 500);
        }
        return $model;
    }

    /**
     * Build a member page URL from the selected prediction context.
     *
     * Reads game, project, group and round from the model and uses
     * Joomla's integer-filtered selected member and database inputs.
     *
     * @param PredictionusersModel $model Source of selection data.
     * @return string Canonical member URL.
     */
    private function buildMemberRoute(PredictionusersModel $model): string
    {
        $input = $this->input;

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

    /**
     * Build an editor return URL from the selected prediction member.
     *
     * Preserves the member, game, project, group, round and selected
     * SportsManagement database across form submissions and cancellation.
     *
     * @param PredictionmemberModel $model Current member editing model.
     * @return string Canonical member editor URL.
     */
    private function buildEditorRoute(PredictionmemberModel $model): string
    {
        $input = $this->input;

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
