<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** Native Joomla 5/6 list controller for prediction-game members. */
final class PredictionmembersController extends SportsManagementAdminController
{
    public function getModel($name = 'Predictionmember', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }

    public function save_memberlist(): void
    {
        $this->checkToken();

        $input = $this->app->getInput();
        $predictionId = $input->post->getInt('cid');
        $memberIds = (array) $input->post->get('prediction_members', [], 'array');
        $model = $this->getModel();
        $errors = $model !== false ? $model->save_memberlist($memberIds, $predictionId) : 1;

        $this->setRedirect(
            Route::_('index.php?option=com_sportsmanagement&view=close&tmpl=component', false),
            $errors > 0
                ? Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $errors, '')
                : Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBER_CTRL_SAVED'),
            $errors > 0 ? 'error' : 'message'
        );
    }

    public function editlist(): void
    {
        $this->setRedirect(Route::_(
            'index.php?option=com_sportsmanagement&view=predictionmembers&layout=editlist',
            false
        ));
    }

    public function reminder(): void
    {
        $this->checkToken();

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
        $sent = $model !== false ? $model->sendEmailtoMembers($ids, $predictionId) : 0;
        $this->setRedirect(
            $this->listUrl($predictionId),
            $sent > 0 ? Text::plural('COM_SPORTSMANAGEMENT_N_ITEMS_SENT', $sent) : '',
            'message'
        );
    }

    public function publish(): void
    {
        $this->setApprovalState(1);
    }

    public function unpublish(): void
    {
        $this->setApprovalState(0);
    }

    public function remove(): void
    {
        $this->checkToken();

        $input = $this->app->getInput();
        $ids = $this->normaliseIds((array) $input->post->get('cid', [], 'array'));
        $predictionId = $input->post->getInt('prediction_id');

        if (!$ids) {
            $this->setRedirect(
                $this->listUrl($predictionId),
                Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBER_CTRL_DEL_ITEM'),
                'warning'
            );

            return;
        }

        $model = $this->getModel();
        $resultsDeleted = $model !== false && $model->deletePredictionResults($ids, $predictionId);
        $membersDeleted = $model !== false && $model->deletePredictionMembers($ids);
        $ok = $resultsDeleted && $membersDeleted;

        $this->setRedirect(
            $this->listUrl($predictionId),
            $ok
                ? Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBER_CTRL_DEL_PMEMBERS')
                : Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBER_CTRL_DEL_MSG'),
            $ok ? 'message' : 'error'
        );
    }

    private function setApprovalState(int $state): void
    {
        $this->checkToken();

        $input = $this->app->getInput();
        $ids = $this->normaliseIds((array) $input->post->get('cid', [], 'array'));
        $predictionId = $input->post->getInt('prediction_id');

        if (!$ids) {
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

    private function listUrl(int $predictionId = 0): string
    {
        return Route::_(
            'index.php?option=com_sportsmanagement&view=predictionmembers'
            . ($predictionId > 0 ? '&filter_prediction_id=' . $predictionId : ''),
            false
        );
    }

    private function normaliseIds(array $ids): array
    {
        return array_values(array_unique(array_filter(
            array_map('intval', $ids),
            static fn (int $id): bool => $id > 0
        )));
    }
}
