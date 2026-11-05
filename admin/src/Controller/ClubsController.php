<?php
/**
 * @package     SportsManagement
 * @subpackage  com_sportsmanagement
 *
 * Joomla 5/6 migration.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;

/**
 * List controller for clubs.
 */
class ClubsController extends SportsManagementAdminController
{
    /**
     * Save inline changes for selected clubs in the administrator list.
     *
     * Requires a valid Joomla form token and component edit permission,
     * resolves the native club model and reports failed writes before
     * redirecting to the club overview.
     *
     * @return void
     * @throws \RuntimeException If Joomla cannot resolve the club model.
     */
    public function saveshort(): void
    {
        $this->checkToken();
        $this->assertWritePermission();

        $model = $this->getModel();

        if (!is_object($model)) {
            throw new \RuntimeException('SportsManagement club model could not be loaded.', 500);
        }

        if (!$model->saveshort()) {
            $this->app->enqueueMessage('Club inline changes could not be saved completely.', 'warning');
        }

        $this->setRedirect(
            Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false)
        );
    }

    /**
     * Resolve the native Club editor model using Joomla's MVC factory.
     *
     * Preserves caller options while using request-independent model
     * loading for administrator edit operations.
     *
     * @param string $name Requested club model name.
     * @param string $prefix Administrator model namespace.
     * @param array $config Additional Joomla model settings.
     * @return mixed Model instance or the parent controller's failure result.
     */
    public function getModel($name = 'Club', $prefix = 'Administrator', $config = [])
    {
        // Preserve caller options while forcing request-independent model loading.
        $config['ignore_request'] = true;

        return parent::getModel($name, $prefix, $config);
    }
}
