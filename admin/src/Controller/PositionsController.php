<?php
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
