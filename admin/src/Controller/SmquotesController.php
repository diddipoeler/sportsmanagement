<?php
/**
 * Native Joomla 5/6 administrator controller for SportsManagement quotes.
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
 * Native Joomla 5/6 administrator controller for SportsManagement quotes.
 */
final class SmquotesController extends SportsManagementAdminController
{
    public function edittxt(): void
    {
        $this->setRedirect(
            Route::_('index.php?option=com_sportsmanagement&view=smquotestxt', false)
        );
    }

    public function getModel($name = 'Smquote', $prefix = 'Administrator', $config = [])
    {
        return parent::getModel($name, $prefix, ['ignore_request' => true]);
    }
}
