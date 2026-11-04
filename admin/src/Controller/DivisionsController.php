<?php
/**
 * Joomla 5/6 administrator controller for divisions list actions.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

class DivisionsController extends SportsManagementAdminController
{
    /**
     * Close the division editor without applying any changes.
     * Redirects to the component's modal close view using the
     * legacy task URL retained for Joomla 5/6 compatibility.
     * @return void
     */
    public function cancel(): void
    {
        $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component');
    }

    /**
     * Create a batch of divisions through the native administrator model.
     * Requires core.create permission, resolves the division write model
     * and redirects to the modal close view with the operation result.
     * @return void
     */
    public function massadd(): void
    {
        $this->assertWritePermission('core.create');

        $model = $this->requireDivisionModel();
        $message = $model->massadd();
        $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component', $message);
    }

    /**
     * Attach divisions to the currently selected project.
     * Verifies creation permission and model availability, performs
     * the model action and returns to the project-scoped division list.
     * @return void
     */
    public function divisiontoproject(): void
    {
        $this->assertWritePermission('core.create');

        $model = $this->requireDivisionModel();
        $message = $model->divisiontoproject();
        $this->setRedirect(
            'index.php?option=com_sportsmanagement&view=divisions&pid=' . $this->getProjectId(),
            $message
        );
    }

    /**
     * Resolve the Division form model with caller-supplied options intact.
     * Ensures request state is ignored when editing while maintaining
     * custom configuration passed in by Joomla MVC consumers.
     * @param string $name Requested division model.
     * @param string $prefix Joomla MVC client namespace.
     * @param array $config Caller-supplied model options.
     * @return mixed Native division model from the parent factory.
     */
    public function getModel($name = 'Division', $prefix = 'Administrator', $config = [])
    {
        // Preserve custom MVC options while disabling request-dependent model state.
        $config['ignore_request'] = true;

        return parent::getModel($name, $prefix, $config);
    }

    /**
     * Save division order submitted by the administrator list view.
     * Requires core.admin permission, collects posted IDs and ordering
     * values and delegates persistence to the native division model.
     * @return void
     */
    public function saveOrder(): void
    {
        $this->assertWritePermission('core.admin');

        $input = $this->app->getInput();
        $model = $this->requireDivisionModel();
        $pks = $input->post->get('cid', [], 'array');
        $order = $input->post->get('order', [], 'array');
        $message = $model->saveorder($pks, $order);

        $this->setRedirect(
            'index.php?option=com_sportsmanagement&view=divisions&pid=' . $this->getProjectId(),
            $message
        );
    }

    /**
     * Save inline division properties from the backend list.
     * Checks component edit permission, requires a native model and
     * redirects back to the selected project's division list with status.
     * @return void
     */
    public function saveshort(): void
    {
        $this->assertWritePermission();

        $model = $this->requireDivisionModel();
        $message = $model->saveshort();

        $this->setRedirect(
            'index.php?option=com_sportsmanagement&view=divisions&pid=' . $this->getProjectId(),
            $message === false ? Text::_('JERROR_AN_ERROR_HAS_OCCURRED') : $message,
            $message === false ? 'warning' : 'message'
        );
    }

    /** Abort administrator list writes when Joomla cannot load the model. */
    private function requireDivisionModel(): object
    {
        $model = $this->getModel();

        if (!is_object($model)) {
            throw new \RuntimeException('SportsManagement division model could not be loaded.', 500);
        }

        return $model;
    }

    /**
     * Read the active project scope for division-list redirects.
     * Uses the Joomla administrator's stored project selection so
     * write actions return to the correct project after completion.
     * @return int Current project identifier or zero.
     */
    private function getProjectId(): int
    {
        $option = $this->app->getInput()->getCmd('option', 'com_sportsmanagement');

        return (int) $this->app->getUserState($option . '.pid', 0);
    }
}
