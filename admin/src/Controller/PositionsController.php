<?php
/**
 * Joomla 5/6 administrator positions list controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;

final class PositionsController extends SportsManagementAdminController
{
    public function saveshort(): void
    {
        $model = $this->getModel();

        if (!$model->saveshort()) {
            $this->app->enqueueMessage('Position parent changes could not be saved completely.', 'warning');
        }

        $this->setRedirect(Route::_('index.php?option=com_sportsmanagement&view=positions', false));
    }

    public function getModel($name = 'Position', $prefix = 'Administrator', $config = [])
    {
        return parent::getModel($name, $prefix, ['ignore_request' => true]);
    }
}
