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

use Diddipoeler\Component\SportsManagement\Administrator\Model\ProjectrefereeModel;
use Diddipoeler\Component\SportsManagement\Administrator\Service\FinderRelationNotifier;
use Joomla\CMS\Router\Route;

/**
 * Native Joomla 5/6 administrator controller for project referees.
 */
final class ProjectrefereesController extends SportsManagementAdminController
{
    /**
     * Save inline project-referee changes from the administrator list.
     * Checks the Joomla form token and component edit permission before
     * passing posted row identifiers and values to the native model.
     * Redirects to the project referee overview with the operation status.
     * @return void
     */
    public function saveshort(): void
    {
        $this->checkToken();
        $this->assertWritePermission('core.edit');

        $input = $this->app->getInput();
        $model = $this->projectrefereeModel();
        $success = $model->saveshort(
            $input->post->get('cid', [], 'array'),
            $input->post->getArray()
        );

        $this->setRedirect(
            Route::_('index.php?option=com_sportsmanagement&view=projectreferees', false),
            '',
            $success ? 'message' : 'error'
        );
    }

    /**
     * Publish selected project referees and refresh related search indexes.
     * Validates the form token and edit-state permission, records the
     * affected person IDs before delegating publication to the parent
     * controller, then notifies Joomla Finder of affected relations.
     * @return void
     */
    public function publish(): void
    {
        $this->checkToken();
        $this->assertWritePermission('core.edit.state');

        $model = $this->projectrefereeModel();
        $ids = (array) $this->app->getInput()->post->get('cid', [], 'array');
        $notifier = new FinderRelationNotifier($model->getDatabase());
        $personIds = $notifier->projectRefereePeopleForRows($ids);

        parent::publish();

        $notifier->notifyPeople($personIds);
    }

    /**
     * Retrieve the native project referee model with administrator settings.
     * Uses request-independent model loading by default while keeping
     * the remaining configuration and requested model name from the caller.
     * @param string $name Model name.
     * @param string $prefix Administrator MVC model namespace.
     * @param array $config Additional model factory settings.
     * @return mixed Result of the parent MVC factory.
     */
    public function getModel($name = 'Projectreferee', $prefix = 'Administrator', $config = [])
    {
        $config['ignore_request'] = true;

        return parent::getModel($name, $prefix, $config);
    }

    /**
     * Obtain and validate the native project referee model.
     * Ensures that inline updates and related Finder notifications access
     * the expected ProjectrefereeModel implementation.
     * @return ProjectrefereeModel Validated project referee model.
     * @throws \RuntimeException If the model is unavailable.
     */
    private function projectrefereeModel(): ProjectrefereeModel
    {
        $model = $this->getModel();

        if (!$model instanceof ProjectrefereeModel) {
            throw new \RuntimeException('ProjectrefereeModel is unavailable.', 500);
        }

        return $model;
    }
}
