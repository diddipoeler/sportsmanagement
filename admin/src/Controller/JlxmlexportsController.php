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

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;

/** Native Joomla 5/6 controller for SportsManagement XML exports. */
final class JlxmlexportsController extends BaseController
{
    public function export(): bool
    {
        // The download includes whole project exports. A valid administrator
        // session alone must not grant access to component data.
        $identity = $this->app->getIdentity();

        if ($identity === null || !$identity->authorise('core.manage', 'com_sportsmanagement')) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $rawProjectId = $this->app->getInput()->get('pid', '', 'raw');

        if (!is_string($rawProjectId) && !is_int($rawProjectId)) {
            throw new \UnexpectedValueException('Invalid export project.', 400);
        }

        $rawProjectId = trim((string) $rawProjectId);
        $projectId = preg_match('/^[1-9][0-9]*$/D', $rawProjectId)
            ? filter_var($rawProjectId, FILTER_VALIDATE_INT, [
                'options' => ['min_range' => 1],
            ])
            : false;

        if ($projectId === false) {
            throw new \UnexpectedValueException('Invalid export project.', 400);
        }

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
