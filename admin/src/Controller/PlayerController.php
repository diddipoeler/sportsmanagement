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
    public function import(): void
    {
        $this->setRedirect(
            Route::_('index.php?option=com_sportsmanagement&view=players&layout=players_upload', false)
        );
    }

    public function getModel($name = 'Player', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }
}
