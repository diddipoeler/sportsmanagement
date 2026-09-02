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
        $model = $this->getModel();
        $model->saveshort();

        $this->setRedirect(
            Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false)
        );
    }

    /**
     * Resolve the namespaced Club model through Joomla's MVCFactory.
     */
    public function getModel($name = 'Club', $prefix = 'Administrator', $config = [])
    {
        return parent::getModel($name, $prefix, ['ignore_request' => true]);
    }
}
