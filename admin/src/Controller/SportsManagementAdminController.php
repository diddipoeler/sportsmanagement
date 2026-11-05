<?php
/**
 * @package     SportsManagement
 * @subpackage  com_sportsmanagement
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * Joomla 5/6 migration.
 */

namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Session\Session;

/**
 * Shared administrator list-controller behaviour for SportsManagement.
 *
 * This is the namespaced replacement for the legacy JSMControllerAdmin class.
 */
abstract class SportsManagementAdminController extends AdminController
{
    /**
     * Retained for compatibility with SportsManagement controllers which use
     * the selected club while processing team-related actions.
     *
     * @var int
     */
    protected $team_club_id = 0;

    /**
     * Save administrator list ordering only for component administrators.
     *
     * Requires core.admin on the component before delegating to Joomla's
     * standard AdminController order-saving workflow.
     *
     * @return mixed Result of Joomla's parent saveorder() operation.
     * @throws \RuntimeException If administrator permission is denied.
     */
    public function saveorder()
    {
        $identity = $this->app->getIdentity();

        if ($identity === null || !$identity->authorise('core.admin', $this->option)) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        return parent::saveorder();
    }

    /**
     * Enforce the Joomla POST form token and component write permission.
     *
     * Protects legacy write actions which bypass the normal Joomla save
     * workflow. Checks both the session token and the requested ACL action
     * on com_sportsmanagement before the caller can modify any records.
     *
     * @param string $action Joomla ACL action, defaulting to core.edit.
     * @return void
     * @throws \RuntimeException If the token or permission is invalid.
     */
    protected function assertWritePermission(string $action = 'core.edit'): void
    {
        if (!Session::checkToken('post')) {
            throw new \RuntimeException(Text::_('JINVALID_TOKEN'), 403);
        }

        $identity = $this->app->getIdentity();

        if ($identity === null || !$identity->authorise($action, 'com_sportsmanagement')) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }
    }

    /**
     * Close a legacy administrator modal without changing stored records.
     *
     * Redirects to the standard component close view in Joomla's
     * component-only layout for popup-based edit workflows.
     *
     * @return void
     */
    public function cancel(): void
    {
        $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component');
    }
}
