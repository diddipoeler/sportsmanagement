<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\ProjectteamModel;
use Diddipoeler\Component\SportsManagement\Administrator\Model\ProjectteamsModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

final class ProjectteamsController extends SportsManagementAdminController
{
    public function saveshort(): void
    {
        $this->assertPostAndPermission('core.edit');
        $model = $this->projectteamModel();
        $ok = $model->saveshort();
        $this->redirectProjectTeams($ok, $model->getError());
    }

    public function set_playground_match(): void
    {
        $this->assertPostAndPermission('core.edit');
        $model = $this->projectteamModel();
        $ok = $model->set_playground_match($this->app->getInput()->post->getArray());
        $this->redirectProjectTeams($ok, $model->getError());
    }

    public function set_playground(): void
    {
        $this->assertPostAndPermission('core.edit');
        $model = $this->projectteamModel();
        $ok = $model->set_playground($this->app->getInput()->post->getArray());
        $this->redirectProjectTeams($ok, $model->getError());
    }

    public function assign(): void
    {
        $this->assertPostAndPermission('core.edit');
        $model = $this->projectteamModel();
        $ok = $model->storeAssign($this->app->getInput()->post->getArray());

        if ($ok) {
            $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component');

            return;
        }

        $this->app->enqueueMessage($model->getError() ?: Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'warning');
        $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component');
    }

    public function matchgroups(): void
    {
        $this->assertPostAndPermission('core.edit');
        $model = $this->projectteamModel();
        $this->redirectProjectTeams($model->matchgroups(), $model->getError());
    }

    public function setseasonid(): void
    {
        $this->assertPostAndPermission('core.edit');
        $model = $this->projectteamModel();
        $this->redirectProjectTeams($model->setseasonid(), $model->getError());
    }

    public function publish(): void
    {
        $this->state(1);
    }

    public function unpublish(): void
    {
        $this->state(0);
    }

    public function archive(): void
    {
        $this->state(2);
    }

    public function trash(): void
    {
        $this->state(-2);
    }

    public function use_table_yes(): void
    {
        $this->flag('score', 1);
    }

    public function use_table_no(): void
    {
        $this->flag('score', 0);
    }

    public function use_table_points_yes(): void
    {
        $this->flag('finally', 1);
    }

    public function use_table_points_no(): void
    {
        $this->flag('finally', 0);
    }

    public function getModel($name = 'Projectteams', $prefix = 'Administrator', $config = [])
    {
        return parent::getModel($name, $prefix, ['ignore_request' => false]);
    }

    private function state(int $value): void
    {
        $this->assertPostAndPermission('core.edit.state');
        $model = $this->model();
        $this->messageAndRedirect($model, $model->setProjectTeamState($value));
    }

    private function flag(string $flag, int $value): void
    {
        $this->assertPostAndPermission('core.edit');
        $model = $this->model();
        $ok = $flag === 'score' ? $model->setScoreFlag($value) : $model->setFinallyFlag($value);
        $this->messageAndRedirect($model, $ok);
    }

    private function model(): ProjectteamsModel
    {
        $model = $this->getModel();

        if (!$model instanceof ProjectteamsModel) {
            throw new \RuntimeException('ProjectteamsModel is unavailable.', 500);
        }

        return $model;
    }

    private function projectteamModel(): ProjectteamModel
    {
        $model = parent::getModel('Projectteam', 'Administrator', ['ignore_request' => true]);

        if (!$model instanceof ProjectteamModel) {
            throw new \RuntimeException('ProjectteamModel is unavailable.', 500);
        }

        return $model;
    }

    private function assertPostAndPermission(string $permission): void
    {
        if (!Session::checkToken('post')) {
            throw new \RuntimeException(Text::_('JINVALID_TOKEN'), 403);
        }

        if (!$this->app->getIdentity()->authorise($permission, 'com_sportsmanagement')) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }
    }

    private function messageAndRedirect(ProjectteamsModel $model, bool $ok): void
    {
        $this->app->enqueueMessage(
            $ok ? Text::_('JSAVE') : ($model->getError() ?: Text::_('JERROR_AN_ERROR_HAS_OCCURRED')),
            $ok ? 'message' : 'warning'
        );
        $pid = (int) $model->getContextParams()['pid'];
        $this->setRedirect(Route::_('index.php?option=com_sportsmanagement&view=projectteams&pid=' . $pid, false));
    }

    private function redirectProjectTeams(bool $ok, string $error = ''): void
    {
        $input = $this->app->getInput();
        $pid = $input->post->getInt('pid', $input->getInt('pid'));
        $division = $input->post->getInt('division', $input->getInt('division'));

        if (!$ok) {
            $this->app->enqueueMessage($error !== '' ? $error : Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'warning');
        }

        $url = 'index.php?option=com_sportsmanagement&view=projectteams&pid=' . $pid;

        if ($division > 0) {
            $url .= '&division=' . $division;
        }

        $this->setRedirect(Route::_($url, false));
    }
}
