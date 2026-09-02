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
use Joomla\CMS\Session\Session;

class DivisionsController extends SportsManagementAdminController
{
    public function cancel(): void
    {
        $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component');
    }

    public function massadd(): void
    {
        if (!Session::checkToken()) {
            throw new \RuntimeException(Text::_('JINVALID_TOKEN'), 403);
        }

        $message = $this->getModel()->massadd();
        $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component', $message);
    }

    public function divisiontoproject(): void
    {
        $message = $this->getModel()->divisiontoproject();
        $this->setRedirect(
            'index.php?option=com_sportsmanagement&view=divisions&pid=' . $this->getProjectId(),
            $message
        );
    }

    public function getModel($name = 'Division', $prefix = 'Administrator', $config = [])
    {
        return parent::getModel($name, $prefix, ['ignore_request' => true]);
    }

    public function saveOrder(): void
    {
        $input = $this->app->getInput();
        $model = $this->getModel();
        $pks = $input->get('cid', [], 'array');
        $order = $input->get('order', [], 'array');
        $message = $model->saveorder($pks, $order);

        $this->setRedirect(
            'index.php?option=com_sportsmanagement&view=divisions&pid=' . $this->getProjectId(),
            $message
        );
    }

    public function saveshort(): void
    {
        $message = $this->getModel()->saveshort();

        $this->setRedirect(
            'index.php?option=com_sportsmanagement&view=divisions&pid=' . $this->getProjectId(),
            $message
        );
    }

    private function getProjectId(): int
    {
        $option = $this->app->getInput()->getCmd('option', 'com_sportsmanagement');

        return (int) $this->app->getUserState($option . '.pid', 0);
    }
}
