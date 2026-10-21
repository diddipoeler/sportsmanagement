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

use Diddipoeler\Component\SportsManagement\Administrator\Model\ProjectModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

/** Native Joomla 5/6 list controller for projects. */
final class ProjectsController extends SportsManagementAdminController
{
    public function saveshort(): void
    {
        $this->runProjectAction('core.edit', 'saveshort');
    }

    public function setleaguechampion(): void
    {
        $this->runProjectAction('core.edit', 'setleaguechampion');
    }

    public function copy(): void
    {
        $this->runProjectAction('core.create', 'copy');
    }

    public function getModel($name = 'Project', $prefix = 'Administrator', $config = [])
    {
        // Keep the list's default request handling without discarding
        // options passed by Joomla's MVC factory or legacy callers.
        return parent::getModel($name, $prefix, array_replace(['ignore_request' => false], $config));
    }

    private function runProjectAction(string $permission, string $method): void
    {
        if (!Session::checkToken('post')) {
            throw new \RuntimeException(Text::_('JINVALID_TOKEN'), 403);
        }

        $identity = $this->app->getIdentity();

        if ($identity === null || !$identity->authorise($permission, 'com_sportsmanagement')) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $model = $this->getModel();

        if (!$model instanceof ProjectModel) {
            throw new \RuntimeException('ProjectModel is unavailable.', 500);
        }

        $result = $model->{$method}();
        $ok = $result !== false;
        $message = $ok
            ? (is_string($result) ? $result : Text::_('JLIB_APPLICATION_SAVE_SUCCESS'))
            : ($model->getError() ?: Text::_('JERROR_AN_ERROR_HAS_OCCURRED'));
        $this->setRedirect(
            Route::_('index.php?option=com_sportsmanagement&view=projects', false),
            $message,
            $ok ? 'message' : 'warning'
        );
    }
}
