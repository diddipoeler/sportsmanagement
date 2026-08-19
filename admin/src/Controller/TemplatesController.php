<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;

/** Native Joomla 5/6 administrator list controller for project templates. */
final class TemplatesController extends SportsManagementAdminController
{
    public function changetemplate(): void
    {
        $this->checkToken();

        $input = $this->app->getInput();
        $id = $input->post->getInt('new_id');
        $projectId = $input->post->getInt('pid');

        $this->setRedirect(
            Route::_(
                'index.php?option=com_sportsmanagement&view=template&layout=edit&id=' . $id
                . ($projectId > 0 ? '&pid=' . $projectId : ''),
                false
            )
        );
    }

    public function getModel($name = 'Template', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }
}
