<?php
/**
 * Joomla 5/6 administrator leagues list controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;

final class LeaguesController extends SportsManagementAdminController
{
    /**
     * Save inline league changes from the Joomla administrator list.
     * Requires component edit permission and a working league model,
     * reports unsuccessful writes and redirects to the league overview.
     * @return void
     * @throws \RuntimeException If no native league model is available.
     */
    public function saveshort(): void
    {
        $this->assertWritePermission();

        $model = $this->getModel();

        if ($model === false || $model === null) {
            throw new \RuntimeException('SportsManagement league model could not be loaded.', 500);
        }

        if (!$model->saveshort()) {
            $this->app->enqueueMessage('League inline changes could not be saved completely.', 'warning');
        }

        $this->setRedirect(Route::_('index.php?option=com_sportsmanagement&view=leagues', false));
    }

    /**
     * Resolve the native league edit model through Joomla MVCFactory.
     * Preserves additional caller settings and forces ignore_request
     * to keep saved data independent of list request filters.
     * @param string $name Requested league model name.
     * @param string $prefix Administrator model namespace.
     * @param array $config Additional caller settings.
     * @return mixed Resolved Joomla league model.
     */
    public function getModel($name = 'League', $prefix = 'Administrator', $config = [])
    {
        $config['ignore_request'] = true;

        return parent::getModel($name, $prefix, $config);
    }
}
