<?php
/**
 * Native Joomla 5/6 controller for SportsManagement prediction entries.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Controller;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\PredictionRouteHelper;
use Diddipoeler\Component\SportsManagement\Site\Model\PredictionentryModel;
use Diddipoeler\Component\SportsManagement\Site\Model\PredictionmembershipModel;
use Diddipoeler\Component\SportsManagement\Site\Model\PredictiontipModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Session\Session;

final class PredictionentryController extends BaseController
{
    /**
     * Apply the selected prediction member and redirect to the entry screen.
     * A valid POST token is required before the entry model resolves the member;
     * the redirect preserves the current prediction game and selection context.
     * @return void
     */
    public function select(): void
    {
        $this->assertPostToken();
        $model = $this->entryModel();
        $model->getEntryMember();
        $this->setRedirect($this->entryRoute($model, $model->getSelectedMemberNumericId()));
    }

    /**
     * Redirect after a prediction project or round selection changes.
     * Uses the entry model's current member and project/round selections to
     * construct a canonical route after checking the POST CSRF token.
     * @return void
     */
    public function selectprojectround(): void
    {
        $this->assertPostToken();
        $model = $this->entryModel();
        $this->setRedirect($this->entryRoute($model, $model->getSelectedMemberNumericId()));
    }

    /**
     * Register the current Joomla user for the prediction game.
     * Calls the membership model, reports translated success or failure messages,
     * and redirects to the entry view without exposing registration exceptions.
     * Logs only the failure exception type, not potentially sensitive SQL,
     * member details or database configuration in exception messages.
     * @return void
     */
    public function register(): void
    {
        $this->assertPostToken();
        $model = $this->membershipModel();
        $app = $this->app;

        try {
            $memberId = $model->registerCurrentUser();
            if ($memberId <= 0) {
                throw new \RuntimeException(Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_CONTROLLER_ERROR_5'));
            }
            $app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_CONTROLLER_MSG_2'), 'message');
            $this->setRedirect($this->entryRoute($model, $memberId, ['s' => 1]));
        } catch (\Throwable $e) {
            Log::add(__METHOD__ . ' failed (' . get_class($e) . ').', Log::ERROR, 'jsmerror');
            $app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_CONTROLLER_ERROR_5'), 'error');
            $this->setRedirect($this->entryRoute($model, 0));
        }
    }

    /**
     * Save the submitted prediction tips for the selected game and member.
     * Delegates validation and persistence to the tip model after CSRF checking,
     * logs failures by exception type and redirects with the corresponding
     * translated status without recording potentially sensitive tip data.
     * @return void
     */
    public function addtipp(): void
    {
        $this->assertPostToken();
        $model = $this->tipModel();
        $app = $this->app;
        $post = $this->input->post->getArray();

        try {
            if (!$model->saveTips($post)) {
                throw new \RuntimeException(Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_CONTROLLER_ERROR_3'));
            }
            $app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_CONTROLLER_MSG_1'), 'message');
            $this->setRedirect($this->entryRoute($model, $model->getSelectedMemberNumericId(), ['eok' => 1]));
        } catch (\Throwable $e) {
            Log::add(__METHOD__ . ' failed (' . get_class($e) . ').', Log::ERROR, 'jsmerror');
            $app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_CONTROLLER_ERROR_3'), 'error');
            $this->setRedirect($this->entryRoute($model, $model->getSelectedMemberNumericId()));
        }
    }

    /**
     * Reject prediction-changing requests without a valid Joomla POST token.
     * All registration, selection and tip-writing actions use this common guard.
     * @return void
     * @throws \RuntimeException If Joomla rejects the CSRF token.
     */
    private function assertPostToken(): void
    {
        if (!Session::checkToken('post')) {
            throw new \RuntimeException(Text::_('JINVALID_TOKEN'), 403);
        }
    }

    /**
     * Resolve the Joomla-native prediction entry model for this controller.
     * Ensures MVCFactory returned the required typed model before it is used.
     * @return PredictionentryModel Validated entry model.
     * @throws \RuntimeException If the model cannot be resolved.
     */
    private function entryModel(): PredictionentryModel
    {
        $model = $this->getModel('Predictionentry');
        if (!$model instanceof PredictionentryModel) {
            throw new \RuntimeException('PredictionentryModel is unavailable.', 500);
        }
        return $model;
    }

    /**
     * Resolve the native model responsible for saving prediction tips.
     * Prevents an unrelated MVC model from receiving tip-write operations.
     * @return PredictiontipModel Validated tip model.
     * @throws \RuntimeException If the model cannot be resolved.
     */
    private function tipModel(): PredictiontipModel
    {
        $model = $this->getModel('Predictiontip');
        if (!$model instanceof PredictiontipModel) {
            throw new \RuntimeException('PredictiontipModel is unavailable.', 500);
        }
        return $model;
    }

    /**
     * Resolve the native model for prediction-game membership.
     * Verifies the model type before handling user registration requests.
     * @return PredictionmembershipModel Validated membership model.
     * @throws \RuntimeException If the model cannot be resolved.
     */
    private function membershipModel(): PredictionmembershipModel
    {
        $model = $this->getModel('Predictionmembership');
        if (!$model instanceof PredictionmembershipModel) {
            throw new \RuntimeException('PredictionmembershipModel is unavailable.', 500);
        }
        return $model;
    }

    /**
     * Construct the prediction entry URL from the selected model state.
     * Keeps game, member, project, group, round and configured database
     * selection in the route while allowing explicitly supplied extra flags.
     * @param PredictionentryModel $model Model holding the selected game context.
     * @param int $memberId Member to display in the prediction entry view.
     * @param array $extra Optional additional route parameters.
     * @return string Canonical Joomla frontend entry URL.
     */
    private function entryRoute(PredictionentryModel $model, int $memberId, array $extra = []): string
    {
        $input = $this->input;

        return PredictionRouteHelper::entry(
            $model->getPredictionGameId(),
            $memberId,
            $model->getProjectId(),
            $model->getGroupId(),
            $model->getRoundId(),
            $input->getInt('cfg_which_database', 0),
            $extra
        );
    }
}
