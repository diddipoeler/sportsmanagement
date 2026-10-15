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

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

final class TeamsController extends SportsManagementAdminController
{
    public function saveshort(): void
    {
        $this->assertWritePermission();

        $model = $this->getModel();
        $ok = $model->saveshort();
        $this->app->enqueueMessage($ok ? Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMS_SAVE') : ($model->getError() ?: Text::_('JERROR_AN_ERROR_HAS_OCCURRED')), $ok ? 'message' : 'warning');
        $this->redirectToList();
    }

    public function copysave(): void
    {
        $this->assertWritePermission('core.create');
        $model = $this->getModel();
        $ok = $model->copySelected();
        $this->app->enqueueMessage($ok ? Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMS_SAVE') : ($model->getError() ?: Text::_('JERROR_AN_ERROR_HAS_OCCURRED')), $ok ? 'message' : 'warning');
        $this->redirectToList();
    }

    public function getModel($name = 'Team', $prefix = 'Administrator', $config = [])
    {
        // Preserve the native list default and caller-provided model options.
        return parent::getModel($name, $prefix, array_replace(['ignore_request' => true], $config));
    }

    private function redirectToList(): void
    {
        $clubId = $this->input->getInt('club_id');
        $url = 'index.php?option=com_sportsmanagement&view=teams' . ($clubId > 0 ? '&club_id=' . $clubId : '');
        $this->setRedirect(Route::_($url, false));
    }
}
