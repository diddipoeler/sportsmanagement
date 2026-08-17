<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;

final class LeaguesController extends SportsManagementAdminController
{
    public function saveshort(): void
    {
        $model = $this->getModel();

        if (!$model->saveshort()) {
            $this->app->enqueueMessage('League inline changes could not be saved completely.', 'warning');
        }

        $this->setRedirect(Route::_('index.php?option=com_sportsmanagement&view=leagues', false));
    }

    public function getModel($name = 'League', $prefix = 'Administrator', $config = [])
    {
        return parent::getModel($name, $prefix, ['ignore_request' => true]);
    }
}
