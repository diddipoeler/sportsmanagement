<?php
/**
 * Joomla 5/6 administrator positions list controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;

final class PositionsController extends SportsManagementAdminController
{
    /**
     * Persist the position-parent assignments submitted by the list editor.
     *
     * Require a valid Joomla POST CSRF token and component edit permission
     * before invoking the position model's bulk update operation. Show a
     * warning when the model cannot save every change, then redirect to the
     * standard positions overview using Joomla's router.
     *
     * @return void Redirects to the positions administrator list.
     * @throws \RuntimeException If the token or edit permission is invalid,
     *     or the required position model cannot be loaded.
     */
    public function saveshort(): void
    {
        $this->assertWritePermission('core.edit');
        $model = $this->getModel();

        if (!is_object($model) || !is_callable([$model, 'saveshort'])) {
            throw new \RuntimeException('SportsManagement position model is unavailable.', 500);
        }

        if (!$model->saveshort()) {
            $this->app->enqueueMessage('Position parent changes could not be saved completely.', 'warning');
        }

        $this->setRedirect(Route::_('index.php?option=com_sportsmanagement&view=positions', false));
    }

    /**
     * Create a position model using the native Joomla administrator MVC factory.
     *
     * Preserve caller-supplied model options while forcing ignore_request,
     * so a model instantiated for a write task cannot acquire unexpected
     * filtering state from untrusted GET or POST input.
     *
     * @param string $name Joomla model name, Position by default.
     * @param string $prefix MVC namespace prefix for the administrator.
     * @param array $config Optional model constructor configuration.
     * @return mixed Model instance returned by the parent controller.
     */
    public function getModel($name = 'Position', $prefix = 'Administrator', $config = [])
    {
        $config = is_array($config) ? $config : [];
        $config['ignore_request'] = true;

        return parent::getModel($name, $prefix, $config);
    }
}
