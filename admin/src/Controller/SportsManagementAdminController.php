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
     * SportsManagement historically restricts manual ordering to core.admin.
     */
    public function saveorder()
    {
        if (!$this->app->getIdentity()->authorise('core.admin', $this->option)) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        return parent::saveorder();
    }

    /**
     * Close a modal workflow instead of returning to a regular list view.
     */
    public function cancel(): void
    {
        $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component');
    }
}
