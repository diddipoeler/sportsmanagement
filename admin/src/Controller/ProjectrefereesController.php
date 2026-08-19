<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;

/**
 * Native Joomla 5/6 administrator controller for project referees.
 */
final class ProjectrefereesController extends SportsManagementAdminController
{
    public function saveshort(): void
    {
        $this->checkToken();

        $input = $this->app->getInput();
        $model = $this->getModel();
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

    public function getModel($name = 'Projectreferee', $prefix = 'Administrator', $config = [])
    {
        return parent::getModel($name, $prefix, ['ignore_request' => true]);
    }
}
