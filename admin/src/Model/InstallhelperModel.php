<?php
/**
 * Joomla 5/6 administrator installation-helper model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * Native Joomla 5/6 administrator model for the installation helper.
 */
final class InstallhelperModel extends SportsManagementAdminModel
{
    /**
     * Create the selected sports type.
     *
     * @return string[] Warning messages; an empty array means success.
     */
    public function saveSportstype(array $post = []): array
    {
        $user = $this->administratorApplication()->getIdentity();

        // Other Joomla integrations may call the model directly, bypassing
        // the install-helper controller's CSRF and ACL checks.
        if ($user === null || !$user->authorise('core.admin', 'com_sportsmanagement')) {
            return [Text::_('JERROR_ALERTNOAUTHOR')];
        }

        $rawType = $post['filter_sports_type'] ?? null;

        // A Joomla language-key suffix cannot contain arrays, control
        // characters or unbounded text from an old installer form.
        if (!is_string($rawType)) {
            return [Text::_('COM_SPORTSMANAGEMENT_ADMIN_INSTALLHELPER_ERROR_1')];
        }

        $sportsType = trim($rawType);

        if ($sportsType === ''
            || strlen($sportsType) > 64
            || !preg_match('/^[a-zA-Z][a-zA-Z0-9_-]*$/D', $sportsType)) {
            return [Text::_('COM_SPORTSMANAGEMENT_ADMIN_INSTALLHELPER_ERROR_1')];
        }

        $db = $this->getDatabase();
        $profile = new \stdClass();
        $profile->name = 'COM_SPORTSMANAGEMENT_ST_' . strtoupper($sportsType);
        $profile->modified = Factory::getDate()->toSql();
        $profile->modified_by = (int) $user->id;
        $profile->checked_out = 0;
        $profile->checked_out_time = $db->getNullDate();

        if (!$db->insertObject('#__sportsmanagement_sports_type', $profile)) {
            return [Text::_('JLIB_DATABASE_ERROR_STORE_FAILED')];
        }

        return [];
    }
}
