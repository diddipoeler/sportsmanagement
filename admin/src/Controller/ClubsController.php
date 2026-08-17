<?php
/**
 * @package     SportsManagement
 * @subpackage  com_sportsmanagement
 *
 * Joomla 5/6 migration.
 */

namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;

/**
 * List controller for clubs.
 */
class ClubsController extends \JSMControllerAdmin
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
     * Proxy for the legacy Club model while models are migrated separately.
     */
    public function getModel($name = 'Club', $prefix = 'sportsmanagementModel', $config = [])
    {
        return parent::getModel($name, $prefix, ['ignore_request' => true]);
    }
}
