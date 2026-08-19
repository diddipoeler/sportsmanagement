<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** Native Joomla 5/6 administrator list controller for tournament trees. */
final class TreetosController extends SportsManagementAdminController
{
    /** Open the node-generation form for one tree. */
    public function genNode(): void
    {
        $input = $this->app->getInput();
        $id = $input->getInt('id');
        $projectId = $input->getInt('pid');

        $this->setRedirect(
            Route::_(
                'index.php?option=com_sportsmanagement&view=treeto&layout=gennode&id=' . $id
                . ($projectId > 0 ? '&pid=' . $projectId : ''),
                false
            )
        );
    }

    /** Preserve the legacy toolbar action that creates a blank tree for the project. */
    public function save(): void
    {
        $this->checkToken();

        $input = $this->app->getInput();
        $projectId = $input->post->getInt('project_id') ?: $input->getInt('pid');
        $model = $this->getModel('Treeto', 'Administrator', ['ignore_request' => false]);
        $result = $projectId > 0 && $model !== false && $model->save(['project_id' => $projectId]);

        $this->setRedirect(
            Route::_(
                'index.php?option=com_sportsmanagement&view=treetos'
                . ($projectId > 0 ? '&pid=' . $projectId : ''),
                false
            ),
            Text::_($result
                ? 'COM_SPORTSMANAGEMENT_ADMIN_TREETO_CTRL_SAVED'
                : 'COM_SPORTSMANAGEMENT_ADMIN_TREETO_CTRL_ERROR_SAVED'
            ),
            $result ? 'message' : 'error'
        );
    }

    public function getModel($name = 'Treeto', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }
}
