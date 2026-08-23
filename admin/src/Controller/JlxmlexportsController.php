<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;

/** Native Joomla 5/6 controller for SportsManagement XML exports. */
final class JlxmlexportsController extends BaseController
{
    public function export()
    {
        $app = Factory::getApplication();
        $projectId = $app->getInput()->getInt('pid');
        $model = $this->getLegacyExportModel();
        $model->exportData();

        $this->setRedirect(
            Route::_(
                'index.php?option=com_sportsmanagement&view=jlxmlexports&pid=' . $projectId,
                false
            )
        );

        return true;
    }

    private function getLegacyExportModel(): object
    {
        if (!class_exists('sportsmanagementModelJLXMLExports', false)) {
            require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/models/jlxmlexports.php';
        }

        return new \sportsmanagementModelJLXMLExports(['ignore_request' => true]);
    }
}
