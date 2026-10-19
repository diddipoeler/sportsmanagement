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
     * Save the short values of the selected clubs.
     */
    public function saveshort(): void
    {
        $this->assertWritePermission();

        $model = $this->getModel();
        if (!$model->saveshort()) {
            $this->app->enqueueMessage('Club inline changes could not be saved completely.', 'warning');
        }

        $this->setRedirect(
            Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false)
        );
    }

    /**
     * Resolve the namespaced Club model through Joomla's MVCFactory.
     */
    public function getModel($name = 'Club', $prefix = 'Administrator', $config = [])
    {
        // Preserve caller options while forcing request-independent model loading.
        $config['ignore_request'] = true;

        return parent::getModel($name, $prefix, $config);
    }
}
