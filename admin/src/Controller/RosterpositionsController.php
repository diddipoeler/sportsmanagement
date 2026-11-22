<?php
/**
 * Joomla 5/6 administrator controller for roster position navigation.
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
 * Expose legacy home and away roster-position creation links.
 *
 * Navigation tasks redirect into the native Joomla administrator edit form;
 * they do not persist records or bypass the form controller's save checks.
 */
final class RosterpositionsController extends SportsManagementAdminController
{
    /**
     * Open a new roster-position editor with the home position selected.
     *
     * Redirect to the native roster-position edit view and preselect the
     * HOME_POS alias for the new entry without altering database records.
     *
     * @return void Redirect is registered on the administrator application.
     */
    public function addhome(): void
    {
        $this->setRedirect(Route::_(
            'index.php?option=com_sportsmanagement&view=rosterposition&addposition=HOME_POS&layout=edit',
            false
        ));
    }

    /**
     * Open a new roster-position editor with the away position selected.
     *
     * Redirect to the same native editor as addhome(), but select AWAY_POS
     * for the initial form values. This action only changes navigation.
     *
     * @return void Redirect is registered on the administrator application.
     */
    public function addaway(): void
    {
        $this->setRedirect(Route::_(
            'index.php?option=com_sportsmanagement&view=rosterposition&addposition=AWAY_POS&layout=edit',
            false
        ));
    }

    /**
     * Resolve the roster-position editing model through Joomla's MVC factory.
     *
     * Preserve explicitly supplied model configuration, while forcing
     * ignore_request so editing model state cannot be overwritten by
     * unrelated list filters or arbitrary request parameters.
     *
     * @param string $name Requested administrator model name.
     * @param string $prefix Joomla MVC model namespace prefix.
     * @param array $config Additional model creation options.
     * @return mixed Model instance returned by Joomla's parent controller.
     */
    public function getModel($name = 'Rosterposition', $prefix = 'Administrator', $config = [])
    {
        $config = is_array($config) ? $config : [];
        $config['ignore_request'] = true;

        return parent::getModel($name, $prefix, $config);
    }
}
