<?php
/**
 * Native Joomla 5/6 form controller for Google calendars.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

/** Native Joomla 5/6 form controller for Google calendars. */
final class JsmgcalendarController extends SportsManagementFormController
{
    /**
     * Accept the historic calendar insertion task without bypassing access checks.
     *
     * This compatibility endpoint intentionally performs no insertion: calendar
     * persistence is implemented by the Joomla form model and its save task.
     * Verify the POST form token and the component edit permission even for
     * this no-op endpoint, preventing callers from using an unguarded legacy
     * task. A missing identity is denied rather than dereferenced.
     *
     * @return void No calendar is created by this legacy compatibility task.
     * @throws \RuntimeException If Joomla component editing is not permitted.
     */
    public function insertgooglecalendar(): void
    {
        $this->checkToken('post');
        $identity = $this->app->getIdentity();

        if ($identity === null || !$identity->authorise('core.edit', 'com_sportsmanagement')) {
            throw new \RuntimeException(\Joomla\CMS\Language\Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }
    }
}
