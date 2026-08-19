<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/**
 * Native Joomla 5/6 list controller for project-position assignments.
 */
final class ProjectpositionsController extends SportsManagementAdminController
{
    public function store(): void
    {
        $this->checkToken();

        $post = $this->app->getInput()->post->getArray();
        $model = $this->getModel();
        $success = $model->store($post);

        $this->setRedirect(
            'index.php?option=com_sportsmanagement&view=close&tmpl=component',
            Text::_($success ? 'JLIB_APPLICATION_SAVE_SUCCESS' : 'JLIB_APPLICATION_ERROR_SAVE_FAILED'),
            $success ? 'message' : 'error'
        );
    }

    public function getModel($name = 'Projectposition', $prefix = 'Administrator', $config = [])
    {
        return parent::getModel($name, $prefix, ['ignore_request' => true]);
    }
}
