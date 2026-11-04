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
    /**
     * Save shortened team fields from the administrator team list.
     * Checks component edit access, invokes the team model's saveshort()
     * method, reports success or failure and redirects to the team overview.
     *
     * @return void
     */
    public function saveshort(): void
    {
        $this->assertWritePermission();

        $model = $this->getModel();
        if ($model === false || $model === null) {
            throw new \RuntimeException('SportsManagement team model could not be loaded.', 500);
        }

        $ok = $model->saveshort();
        $this->app->enqueueMessage($ok ? Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMS_SAVE') : ($model->getError() ?: Text::_('JERROR_AN_ERROR_HAS_OCCURRED')), $ok ? 'message' : 'warning');
        $this->redirectToList();
    }

    /**
     * Copy the selected team records through the administrator model.
     * Checks core.create permission before delegating the copy operation,
     * then displays a result message and redirects to the current team list.
     *
     * @return void
     */
    public function copysave(): void
    {
        $this->assertWritePermission('core.create');
        $model = $this->getModel();
        if ($model === false || $model === null) {
            throw new \RuntimeException('SportsManagement team model could not be loaded.', 500);
        }

        $ok = $model->copySelected();
        $this->app->enqueueMessage($ok ? Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMS_SAVE') : ($model->getError() ?: Text::_('JERROR_AN_ERROR_HAS_OCCURRED')), $ok ? 'message' : 'warning');
        $this->redirectToList();
    }

    /**
     * Resolve the administrator team model using Joomla's MVC factory.
     * Preserves caller configuration while defaulting to request-independent
     * loading of the team editing model.
     *
     * @param string $name Model name.
     * @param string $prefix Administrator MVC namespace.
     * @param array $config Additional factory settings.
     * @return mixed The resulting Joomla model or parent failure result.
     */
    public function getModel($name = 'Team', $prefix = 'Administrator', $config = [])
    {
        // Preserve the native list default and caller-provided model options.
        return parent::getModel($name, $prefix, array_replace(['ignore_request' => true], $config));
    }

    /**
     * Redirect to the administrator teams view after a write operation.
     * Preserves the optional integer-filtered club identifier in the URL,
     * allowing administrators to return to the previously selected club.
     *
     * @return void
     */
    private function redirectToList(): void
    {
        $clubId = $this->input->getInt('club_id');
        $url = 'index.php?option=com_sportsmanagement&view=teams' . ($clubId > 0 ? '&club_id=' . $clubId : '');
        $this->setRedirect(Route::_($url, false));
    }
}
