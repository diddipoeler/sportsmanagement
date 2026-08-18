<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\TeamplayersModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

final class TeamplayersController extends SportsManagementAdminController
{
    public function saveshort(): void
    {
        $this->assertPostAndPermission('core.edit');
        $model = $this->model();
        $ok = $model->saveShort();
        $this->app->enqueueMessage($ok ? Text::_('JSAVE') : ($model->getError() ?: Text::_('JERROR_AN_ERROR_HAS_OCCURRED')), $ok ? 'message' : 'warning');
        $this->redirectToList($model);
    }

    public function publish(): void { $this->state(1); }
    public function unpublish(): void { $this->state(0); }
    public function archive(): void { $this->state(2); }
    public function trash(): void { $this->state(-2); }

    public function getModel($name = 'Teamplayers', $prefix = 'Administrator', $config = [])
    {
        return parent::getModel($name, $prefix, ['ignore_request' => false]);
    }

    private function state(int $value): void
    {
        $this->assertPostAndPermission('core.edit.state');
        $model = $this->model();
        $ok = $model->setRelationState($value);
        $this->app->enqueueMessage($ok ? Text::_('JLIB_APPLICATION_SUCCESS_BATCH') : Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), $ok ? 'message' : 'warning');
        $this->redirectToList($model);
    }

    private function model(): TeamplayersModel
    {
        $model = $this->getModel();
        if (!$model instanceof TeamplayersModel) {
            throw new \RuntimeException('TeamplayersModel is unavailable.', 500);
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

    private function redirectToList(TeamplayersModel $model): void
    {
        $params = array_filter($model->getContextParams(), static fn($value) => (int) $value !== 0);
        $query = http_build_query(array_merge(['option' => 'com_sportsmanagement', 'view' => 'teamplayers'], $params));
        $this->setRedirect(Route::_('index.php?' . $query, false));
    }
}
