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
    /**
     * Save abbreviated project fields from the administrator list.
     *
     * Delegates to the shared project action runner with the core.edit
     * permission and the model's saveshort operation.
     *
     * @return void
     */
    public function saveshort(): void
    {
        $this->runProjectAction('core.edit', 'saveshort');
    }

    /**
     * Mark the selected league champion in the project model.
     *
     * Enforces administrator edit permission and POST token validation
     * through the shared project-action dispatcher.
     *
     * @return void
     */
    public function setleaguechampion(): void
    {
        $this->runProjectAction('core.edit', 'setleaguechampion');
    }

    /**
     * Copy selected SportsManagement projects through the native model.
     *
     * Requires component core.create permission and a valid POST token
     * before invoking ProjectModel::copy().
     *
     * @return void
     */
    public function copy(): void
    {
        $this->runProjectAction('core.create', 'copy');
    }

    /**
     * Resolve a project model with the Joomla administrator MVC factory.
     *
     * Uses the Project model by default, preserves the list controller's
     * request-state handling and allows caller configuration to override
     * the ignore_request default.
     *
     * @param string $name Requested model name.
     * @param string $prefix MVC namespace prefix.
     * @param array $config Additional model options.
     * @return mixed Model instance or the parent's unsuccessful result.
     */
    public function getModel($name = 'Project', $prefix = 'Administrator', $config = [])
    {
        // Keep the list's default request handling without discarding
        // options passed by Joomla's MVC factory or legacy callers.
        return parent::getModel($name, $prefix, array_replace(['ignore_request' => false], $config));
    }

    /**
     * Execute a protected administrator project operation and redirect.
     *
     * Validates the Joomla POST token and the requested component ACL
     * permission, resolves the native ProjectModel, invokes the designated
     * operation and reports its success or failure on the projects page.
     *
     * @param string $permission Joomla ACL action required for the operation.
     * @param string $method Name of the ProjectModel operation to invoke.
     * @return void
     * @throws \RuntimeException If the token, permission or model is invalid.
     */
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
