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

use Joomla\CMS\Router\Route;

/**
 * Native Joomla 5/6 administrator controller for project rounds.
 */
final class RoundsController extends SportsManagementAdminController
{
    protected $view_list = 'rounds';

    /**
     * Resolve the round model through Joomla MVC with the administrator namespace.
     * @param string $name Requested model name.
     * @param string $prefix Administrator model namespace.
     * @param array $config Joomla MVC factory options.
     * @return mixed Model instance or parent failure value.
     */
    public function getModel($name = 'Round', $prefix = 'Administrator', $config = [])
    {
        $config['ignore_request'] = true;

        return parent::getModel($name, $prefix, $config);
    }

    /**
     * Create multiple project rounds from an administrator form submission.
     * Requires the Joomla form token and create permission; delegates
     * the posted data to the round model and returns to the round list.
     * @return void
     */
    public function massadd(): void
    {
        $this->checkToken();
        $this->assertWritePermission('core.create');

        $post = $this->app->getInput()->post->getArray();
        $projectId = (int) ($post['project_id'] ?? $this->app->getUserState('com_sportsmanagement.pid', 0));
        $message = $this->requireRoundModel()->massadd($post);

        if ($projectId > 0) {
            $this->app->setUserState('com_sportsmanagement.pid', $projectId);
        }

        $this->setRedirect(
            Route::_('index.php?option=com_sportsmanagement&view=rounds&pid=' . $projectId, false),
            $message,
            $message === false ? 'error' : 'message'
        );
    }

    /**
     * Delete matches belonging to the selected round records.
     * Checks the CSRF token and delete permission before forwarding
     * POSTed round IDs to the model and redirecting to the overview.
     * @return void
     */
    public function deleteRoundMatches(): void
    {
        $this->checkToken();
        $this->assertWritePermission('core.delete');

        $pks = $this->app->getInput()->post->get('cid', [], 'array');
        $model = $this->requireRoundModel();
        $success = $model->deleteRoundMatches($pks);
        $message = $success ? '' : $model->getError();

        $this->setRedirect(
            Route::_('index.php?option=com_sportsmanagement&view=rounds', false),
            $message,
            $success ? 'message' : 'error'
        );
    }

    /**
     * Persist inline edits to rounds in the administrator list.
     * Checks the form token and write rights, then passes submitted
     * round identifiers and values to the native round model.
     * @return void
     */
    public function saveshort(): void
    {
        $this->checkToken();
        $this->assertWritePermission();

        $input = $this->app->getInput();
        $projectId = (int) $this->app->getUserState('com_sportsmanagement.pid', 0);
        $message = $this->requireRoundModel()->saveshort(
            $input->post->get('cid', [], 'array'),
            $input->post->getArray()
        );

        $this->setRedirect(
            'index.php?option=com_sportsmanagement&view=rounds&pid=' . $projectId,
            $message,
            $message === false ? 'error' : 'message'
        );
    }

    /**
     * Obtain a valid round model before legacy write operations.
     *
     * Rejects unsuccessful Joomla MVC factory results instead of
     * calling round write operations on an invalid model instance.
     *
     * @return object Native round model.
     * @throws \RuntimeException If the model is not available.
     */
    private function requireRoundModel(): object
    {
        $model = $this->getModel();

        if (!is_object($model)) {
            throw new \RuntimeException('SportsManagement round model could not be loaded.', 500);
        }

        return $model;
    }

    /**
     * Open the round population editor for a project and division.
     * Uses integer-filtered request values and the selected project
     * from Joomla session state to construct the editing URL.
     * @return void
     */
    public function populate(): void
    {
        $divisionId = $this->app->getInput()->getInt('division_id', 0);
        $projectId = (int) $this->app->getUserState('com_sportsmanagement.pid', 0);

        $this->setRedirect(
            'index.php?option=com_sportsmanagement&view=rounds&layout=populate&pid=' . $projectId . '&division_id=' . $divisionId
        );
    }
}
