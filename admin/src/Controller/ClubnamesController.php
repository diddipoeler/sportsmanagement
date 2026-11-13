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
     *
     * Requires a valid POST CSRF token and component edit permission before
     * importing aliases. Verifies the native model method is available and
     * returns to the administrator club-name list after processing.
     *
     * @return void
     * @throws \RuntimeException When the model is missing or the caller lacks access.
     */
    public function import(): void
    {
        $this->assertWritePermission();

        $model = $this->getModel();

        if (!is_object($model) || !is_callable([$model, 'import'])) {
            throw new \RuntimeException('SportsManagement club-name import model is unavailable.', 500);
        }

        $model->import();

        $this->setRedirect(
            Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false)
        );
    }

    /**
     * Resolve the namespaced Clubname model through Joomla's MVC factory.
     *
     * Ignores request-derived filtering while preserving explicitly supplied
     * MVC factory configuration, so callers can still inject model services.
     *
     * @param string $name Entity model name, Clubname by default.
     * @param string $prefix Administrator namespace prefix for model discovery.
     * @param array<string,mixed> $config Optional caller configuration.
     * @return object Joomla administrator model instance.
     */
    public function getModel($name = 'Clubname', $prefix = 'Administrator', $config = [])
    {
        $config['ignore_request'] = true;

        return parent::getModel($name, $prefix, $config);
    }
}
