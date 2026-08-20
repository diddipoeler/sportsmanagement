<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\PlayerModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

/** Native Joomla 5/6 list controller for persons/players. */
final class PlayersController extends SportsManagementAdminController
{
    public function importupload(): void
    {
        $this->requireToken();
        $this->requirePermission('core.create');
        $model = $this->playerModel();
        $ok = $model->importupload($this->app->getInput()->post->getArray());

        if (!$ok && $model->getError()) {
            $this->app->enqueueMessage($model->getError(), 'error');
        } elseif ($ok) {
            $this->app->enqueueMessage(Text::_('JLIB_APPLICATION_SAVE_SUCCESS'), 'message');
        }

        $this->setRedirect('index.php?option=com_sportsmanagement&view=players');
    }

    public function assign(): void
    {
        $this->requireToken();
        $this->requirePermission('core.edit');
        $model = $this->playerModel();
        $ok = $model->storeAssign($this->app->getInput()->post->getArray());

        if (!$ok && $model->getError()) {
            $this->app->enqueueMessage($model->getError(), 'error');
        }

        $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component');
    }

    public function close(): void
    {
        $this->requireToken();
        $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component');
    }

    public function saveshort(): void
    {
        $this->requireToken();
        $this->requirePermission('core.edit');
        $model = $this->playerModel();

        if (!$model->saveshort() && $model->getError()) {
            $this->app->enqueueMessage($model->getError(), 'error');
        }

        $this->setRedirect(Route::_('index.php?option=com_sportsmanagement&view=players', false));
    }

    public function getModel($name = 'Player', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }

    private function playerModel(): PlayerModel
    {
        $model = $this->getModel('Player', 'Administrator', ['ignore_request' => true]);

        if (!$model instanceof PlayerModel) {
            throw new \RuntimeException('PlayerModel is unavailable.', 500);
        }

        return $model;
    }

    private function requireToken(): void
    {
        if (!Session::checkToken('post')) {
            throw new \RuntimeException(Text::_('JINVALID_TOKEN'), 403);
        }
    }

    private function requirePermission(string $action): void
    {
        if (!$this->app->getIdentity()->authorise($action, 'com_sportsmanagement')) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }
    }
}
