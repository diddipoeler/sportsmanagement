<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;

/**
 * Native Joomla 5/6 administrator controller for project rounds.
 */
final class RoundsController extends SportsManagementAdminController
{
    protected $view_list = 'rounds';

    public function getModel($name = 'Round', $prefix = 'Administrator', $config = [])
    {
        return parent::getModel($name, $prefix, ['ignore_request' => true]);
    }

    public function massadd(): void
    {
        $this->checkToken();

        $post = $this->app->getInput()->post->getArray();
        $projectId = (int) ($post['project_id'] ?? $this->app->getUserState('com_sportsmanagement.pid', 0));
        $message = $this->getModel()->massadd($post);

        if ($projectId > 0) {
            $this->app->setUserState('com_sportsmanagement.pid', $projectId);
        }

        $this->setRedirect(
            Route::_('index.php?option=com_sportsmanagement&view=rounds&pid=' . $projectId, false),
            $message,
            $message === false ? 'error' : 'message'
        );
    }

    public function deleteRoundMatches(): void
    {
        $this->checkToken();

        $pks = $this->app->getInput()->post->get('cid', [], 'array');
        $model = $this->getModel();
        $success = $model->deleteRoundMatches($pks);
        $message = $success ? '' : $model->getError();

        $this->setRedirect(
            Route::_('index.php?option=com_sportsmanagement&view=rounds', false),
            $message,
            $success ? 'message' : 'error'
        );
    }

    public function saveshort(): void
    {
        $this->checkToken();

        $input = $this->app->getInput();
        $projectId = (int) $this->app->getUserState('com_sportsmanagement.pid', 0);
        $message = $this->getModel()->saveshort(
            $input->get('cid', [], 'array'),
            $input->post->getArray()
        );

        $this->setRedirect(
            'index.php?option=com_sportsmanagement&view=rounds&pid=' . $projectId,
            $message,
            $message === false ? 'error' : 'message'
        );
    }

    public function populate(): void
    {
        $divisionId = $this->app->getInput()->getInt('division_id', 0);
        $projectId = (int) $this->app->getUserState('com_sportsmanagement.pid', 0);

        $this->setRedirect(
            'index.php?option=com_sportsmanagement&view=rounds&layout=populate&pid=' . $projectId . '&division_id=' . $divisionId
        );
    }
}
