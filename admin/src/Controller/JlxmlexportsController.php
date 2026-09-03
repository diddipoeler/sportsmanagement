<?php
/**
 * Native Joomla 5/6 controller for SportsManagement XML exports.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;

/** Native Joomla 5/6 controller for SportsManagement XML exports. */
final class JlxmlexportsController extends BaseController
{
    public function export(): bool
    {
        $projectId = $this->app->getInput()->getInt('pid');
        $model = $this->getExportModel();

        if (!method_exists($model, 'exportData')) {
            throw new \RuntimeException('SportsManagement XML export model has no exportData() method.', 500);
        }

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
        $model = $this->app
            ->bootComponent('com_sportsmanagement')
            ->getMVCFactory()
            ->createModel('Jlxmlexports', 'Administrator', ['ignore_request' => true]);

        if ($model === null) {
            throw new \RuntimeException('SportsManagement XML export model not found.', 500);
        }

        return $model;
    }
}
