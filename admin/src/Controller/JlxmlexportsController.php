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
        $model = $this->getExportModel();
        $model->exportData();

        $this->setRedirect(
            Route::_(
                'index.php?option=com_sportsmanagement&view=jlxmlexports&pid=' . $projectId,
                false
            )
        );

        return true;
    }

    private function getExportModel(): object
    {
        $model = Factory::getApplication()
            ->bootComponent('com_sportsmanagement')
            ->getMVCFactory()
            ->createModel('Jlxmlexports', 'Administrator', ['ignore_request' => true]);

        if ($model === null) {
            throw new \RuntimeException('SportsManagement XML export model not found.', 500);
        }

        return $model;
    }
}
