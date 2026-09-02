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
 * List controller for alternative club names.
 */
class ClubnamesController extends SportsManagementAdminController
{
    /**
     * Import alternative club names through the existing model implementation.
     */
    public function import(): void
    {
        $this->getModel()->import();

        $this->setRedirect(
            Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false)
        );
    }

    /**
     * Resolve the namespaced Clubname model through Joomla's MVCFactory.
     */
    public function getModel($name = 'Clubname', $prefix = 'Administrator', $config = [])
    {
        return parent::getModel($name, $prefix, ['ignore_request' => true]);
    }
}
