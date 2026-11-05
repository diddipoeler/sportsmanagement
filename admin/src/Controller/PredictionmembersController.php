<?php
/**
 * Joomla 5/6 SportsManagement administrator controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** Native Joomla 5/6 list controller for prediction-game members. */
final class PredictionmembersController extends SportsManagementAdminController
{
    /**
     * Resolve the native administrator prediction member model.
     * @param string $name Requested prediction member model name.
     * @param string $prefix Joomla administrator MVC prefix.
     * @param array $config Additional model options.
     * @return mixed Model or parent factory failure result.
     */
    public function getModel($name = 'Predictionmember', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }

    /**
     * Save selected members into the current prediction game.
     * Checks the component edit permission and POST token through the
     * shared administrator guard and forwards positive member IDs to the model.
     * @return void
     */
    public function save_memberlist(): void
    {
        $this->assertWritePermission();

        $input = $this->app->getInput();
        $predictionId = $input->post->getInt('cid');
        $memberIds = $this->normaliseIds((array) $input->post->get('prediction_members', [], 'array'));

        if ($predictionId <= 0 || $memberIds === []) {
            $this->setRedirect(
                $this->listUrl($predictionId),
                Text::_('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST'),
                'warning'
            );

            return;
        }

        $model = $this->getModel();
        $errors = $model !== false ? $model->save_memberlist($memberIds, $predictionId) : 1;

        $this->setRedirect(
            Route::_('index.php?option=com_sportsmanagement&view=close&tmpl=component', false),
            $errors > 0
                ? Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $errors, '')
                : '',
            $errors > 0 ? 'error' : 'message'
        );
    }

    /**
     * Open the legacy prediction member selection layout.
     * @return void
     */
    public function editlist(): void
    {
        $this->setRedirect(Route::_(
            'index.php?option=com_sportsmanagement&view=predictionmembers&layout=editlist',
            false
        ));
    }

    /**
     * Send a reminder to the selected prediction game members.
     * Checks administrator edit permission and reads the member selection
     * and prediction game ID from POST before invoking the native model.
     * @return void
     */
    public function reminder(): void
    {
        $this->assertWritePermission();

        $input = $this->app->getInput();
        $ids = $this->normaliseIds((array) $input->post->get('cid', [], 'array'));
        $predictionId = $input->post->getInt('prediction_id');

        if ($predictionId <= 0 || !$ids) {
            $this->setRedirect(
                $this->listUrl(),
                Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBER_CTRL_SELECT_ERROR'),
                'warning'
            );

            return;
        }

        $model = $this->getModel();

        if ($model !== false) {
            $model->sendEmailtoMembers($ids, $predictionId);
        }

        $this->setRedirect($this->listUrl($predictionId));
    }

    /**
     * Approve selected members of the current prediction game.
     * @return void
     */
    public function publish(): void
    {
        $this->setApprovalState(1);
    }

    /**
     * Revoke approval for selected prediction game members.
     * @return void
     */
    public function unpublish(): void
    {
        $this->setApprovalState(0);
    }

    /**
     * Delete selected prediction members and their result rows.
     * Requires component delete permission and removes result records first
     * to avoid leaving orphaned prediction results after membership deletion.
     * @return void
     */
    public function remove(): void
    {
        $this->assertWritePermission('core.delete');

        $input = $this->app->getInput();
        $ids = $this->normaliseIds((array) $input->post->get('cid', [], 'array'));
        $predictionId = $input->post->getInt('prediction_id');

        if ($predictionId <= 0 || !$ids) {
            $this->setRedirect(
                $this->listUrl($predictionId),
                Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBER_CTRL_DEL_ITEM'),
                'warning'
            );

            return;
        }

        $model = $this->getModel();
        $resultsDeleted = $model !== false && $model->deletePredictionResults($ids, $predictionId);
        // Never remove membership rows when deleting their prediction results
        // has failed: that would leave orphaned result records.
        $ok = $resultsDeleted && $model->deletePredictionMembers($ids, $predictionId);

        $this->setRedirect(
            $this->listUrl($predictionId),
            $ok
                ? Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBER_CTRL_DEL_PMEMBERS')
                : Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBER_CTRL_DEL_MSG'),
            $ok ? 'message' : 'error'
        );
    }

    /**
     * Change approval state for selected prediction members.
     * Requires core.edit.state and validates the selected prediction game.
     * @param int $state One to approve members or zero to unapprove.
     * @return void
     */
    private function setApprovalState(int $state): void
    {
        $this->assertWritePermission('core.edit.state');

        $input = $this->app->getInput();
        $ids = $this->normaliseIds((array) $input->post->get('cid', [], 'array'));
        $predictionId = $input->post->getInt('prediction_id');

        if ($predictionId <= 0 || !$ids) {
            $this->setRedirect(
                $this->listUrl($predictionId),
                Text::_($state === 1
                    ? 'COM_SPORTSMANAGEMENT_ADMIN_PMEMBER_CTRL_SEL_MEMBER_APPR'
                    : 'COM_SPORTSMANAGEMENT_ADMIN_PMEMBER_CTRL_SEL_MEMBER_REJECT'
                ),
                'warning'
            );

            return;
        }

        $model = $this->getModel();
        $ok = $model !== false && $model->publishpredmembers($ids, $state, $predictionId);

        $this->setRedirect(
            $this->listUrl($predictionId),
            $ok ? '' : ($model !== false ? $model->getError() : Text::_('JERROR_AN_ERROR_HAS_OCCURRED')),
            $ok ? 'message' : 'error'
        );
    }

    /**
     * Build the Joomla administrator prediction member overview URL.
     * @param int $predictionId Optional prediction game filter identifier.
     * @return string Routed administrator member-list URL.
     */
    private function listUrl(int $predictionId = 0): string
    {
        return Route::_(
            'index.php?option=com_sportsmanagement&view=predictionmembers'
            . ($predictionId > 0 ? '&filter_prediction_id=' . $predictionId : ''),
            false
        );
    }

    /**
     * Validate legacy prediction member identifiers supplied in POST.
     * Accepts positive decimal IDs only, rejects malformed and oversized
     * input and removes duplicates before model write operations.
     * @param array $ids Raw submitted member identifiers.
     * @return array<int> Unique positive member IDs.
     */
    private function normaliseIds(array $ids): array
    {
        $valid = [];

        foreach (array_slice($ids, 0, 500) as $value) {
            if (!is_int($value) && !is_string($value)) {
                continue;
            }

            $raw = trim((string) $value);

            if (strlen($raw) > 20 || preg_match('/^[0-9]+$/D', $raw) !== 1) {
                continue;
            }

            $digits = ltrim($raw, '0');

            if ($digits === '') {
                continue;
            }

            $id = filter_var($digits, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

            if ($id !== false) {
                $valid[$id] = $id;
            }
        }

        return array_values($valid);
    }
}
