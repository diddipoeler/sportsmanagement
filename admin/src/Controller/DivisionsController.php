<?php
/**
 * @package     SportsManagement
 * @subpackage  com_sportsmanagement
 *
 * Joomla 5/6 migration.
 */

namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;

/**
 * List controller for divisions.
 */
class DivisionsController extends SportsManagementAdminController
{
    /**
     * Close a modal division workflow.
     */
    public function cancel(): void
    {
        $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component');
    }

    /**
     * Add multiple divisions using the existing model implementation.
     */
    public function massadd(): void
    {
        Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

        $message = $this->getModel()->massadd();
        $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component', $message);
    }

    /**
     * Copy a division to a project.
     */
    public function divisiontoproject(): void
    {
        $message = $this->getModel()->divisiontoproject();
        $this->setRedirect(
            'index.php?option=com_sportsmanagement&view=divisions&pid=' . $this->getProjectId(),
            $message
        );
    }

    /**
     * Proxy for the legacy Division model while models are migrated separately.
     */
    public function getModel($name = 'Division', $prefix = 'sportsmanagementModel', $config = [])
    {
        return parent::getModel($name, $prefix, ['ignore_request' => true]);
    }

    /**
     * Save the submitted ordering values.
     */
    public function saveOrder(): void
    {
        $input = Factory::getApplication()->getInput();
        $model = $this->getModel();
        $pks = $input->get('cid', [], 'array');
        $order = $input->get('order', [], 'array');
        $message = $model->saveorder($pks, $order);

        $this->setRedirect(
            'index.php?option=com_sportsmanagement&view=divisions&pid=' . $this->getProjectId(),
            $message
        );
    }

    /**
     * Save the short values of the selected divisions.
     */
    public function saveshort(): void
    {
        $message = $this->getModel()->saveshort();

        $this->setRedirect(
            'index.php?option=com_sportsmanagement&view=divisions&pid=' . $this->getProjectId(),
            $message
        );
    }

    /**
     * Return the current project id without creating dynamic controller properties.
     */
    private function getProjectId(): int
    {
        $app = Factory::getApplication();
        $option = $app->getInput()->getCmd('option', 'com_sportsmanagement');

        return (int) $app->getUserState($option . '.pid', 0);
    }
}
