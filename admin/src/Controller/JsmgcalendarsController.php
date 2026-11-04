<?php
/**
 * Native Joomla 5/6 list controller for Google calendars.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\JsmgcalendarsModel;
use Joomla\CMS\Language\Text;

/** Native Joomla 5/6 list controller for Google calendars. */
final class JsmgcalendarsController extends SportsManagementAdminController
{
    /**
     * Provision the configured Google Calendar API integration.
     *
     * Verifies the Joomla form token and component core.admin permission,
     * loads the native Google calendar model and checks API readiness.
     * Redirects to the calendars overview with translated status feedback.
     *
     * @return void
     * @throws \RuntimeException For denied access or an unavailable model.
     */
    public function installgoogleapi(): void
    {
        $this->checkToken();
        $identity = $this->app->getIdentity();

        if ($identity === null || !$identity->authorise('core.admin', 'com_sportsmanagement')) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $model = $this->getModel('Jsmgcalendars', 'Administrator', ['ignore_request' => true]);

        if (!$model instanceof JsmgcalendarsModel) {
            throw new \RuntimeException('Google calendar provisioning model not found.', 500);
        }

        $success = $model->check_google_api();

        $this->setRedirect(
            'index.php?option=com_sportsmanagement&view=jsmgcalendars',
            Text::_($success ? 'COM_SPORTSMANAGEMENT_ADMIN_GOOGLE_API_READY'
                : 'COM_SPORTSMANAGEMENT_ADMIN_GOOGLE_API_UNAVAILABLE'),
            $success ? 'message' : 'error'
        );
    }

    /**
     * Resolve the administrator Google calendar model via Joomla MVC.
     *
     * Preserves caller-supplied factory configuration while using
     * request-independent model loading for the calendar actions.
     *
     * @param string $name Requested calendar model name.
     * @param string $prefix Administrator model namespace.
     * @param array $config Additional Joomla model factory options.
     * @return mixed Resolved model or the parent's failure result.
     */
    public function getModel($name = 'Jsmgcalendar', $prefix = 'Administrator', $config = [])
    {
        $config['ignore_request'] = true;

        return parent::getModel($name, $prefix, $config);
    }
}
