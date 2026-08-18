<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\ProjectteamsModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

final class ProjectteamsController extends SportsManagementAdminController
{
    public function saveshort(): void
    {
        $this->assertPostAndPermission('core.edit');
        $model = $this->model();
        $ok = $model->saveShort();
        $this->messageAndRedirect($model, $ok);
    }

    public function publish(): void { $this->state(1); }
    public function unpublish(): void { $this->state(0); }
    public function archive(): void { $this->state(2); }
    public function trash(): void { $this->state(-2); }
    public function use_table_yes(): void { $this->flag('score', 1); }
    public function use_table_no(): void { $this->flag('score', 0); }
    public function use_table_points_yes(): void { $this->flag('finally', 1); }
    public function use_table_points_no(): void { $this->flag('finally', 0); }

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
        if (!$model instanceof ProjectteamsModel) throw new \RuntimeException('ProjectteamsModel is unavailable.', 500);
        return $model;
    }

    private function assertPostAndPermission(string $permission): void
    {
        if (!Session::checkToken('post')) throw new \RuntimeException(Text::_('JINVALID_TOKEN'), 403);
        if (!$this->app->getIdentity()->authorise($permission, 'com_sportsmanagement')) throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
    }

    private function messageAndRedirect(ProjectteamsModel $model, bool $ok): void
    {
        $this->app->enqueueMessage($ok ? Text::_('JSAVE') : ($model->getError() ?: Text::_('JERROR_AN_ERROR_HAS_OCCURRED')), $ok ? 'message' : 'warning');
        $pid = (int) $model->getContextParams()['pid'];
        $this->setRedirect(Route::_('index.php?option=com_sportsmanagement&view=projectteams&pid=' . $pid, false));
    }
}
