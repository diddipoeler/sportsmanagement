<?php
/**
 * Joomla 5/6 SportsManagement administrator controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;

/** Native Joomla 5/6 form controller for persons/players. */
final class PlayerController extends SportsManagementFormController
{
    /**
     * Open the administrator upload form for importing player records.
     *
     * Redirect to the existing players upload layout. This navigation
     * action does not accept files or modify database records; import
     * processing is handled by the relevant controller after submission.
     *
     * @return void The upload page redirect is registered.
     */
    public function import(): void
    {
        $this->setRedirect(
            Route::_('index.php?option=com_sportsmanagement&view=players&layout=players_upload', false)
        );
    }

    /**
     * Resolve the player model through Joomla's administrator MVC factory.
     *
     * Forward the requested model name, namespace and configuration to
     * the parent form controller, preserving explicitly supplied settings.
     *
     * @param string $name Player model name.
     * @param string $prefix Administrator MVC model namespace.
     * @param array $config Model construction configuration.
     * @return mixed Model instance resolved by Joomla.
     */
    public function getModel($name = 'Player', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }
}
