<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/**
 * Native Joomla 5/6 administrator form controller for project referees.
 */
final class ProjectrefereeController extends SportsManagementFormController
{
    public function remove(): void
    {
        $this->checkToken();

        $pks = $this->app->getInput()->post->get('cid', [], 'array');
        $model = $this->getModel();
        $success = $model->delete($pks);

        $this->setRedirect(
            'index.php?option=com_sportsmanagement&view=projectreferees',
            Text::_($success ? 'JLIB_APPLICATION_DELETE_SUCCESS' : 'JLIB_APPLICATION_ERROR_DELETE_FAILED'),
            $success ? 'message' : 'error'
        );
    }
}
