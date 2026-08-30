<?php
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
        $sportsType = trim((string) ($post['filter_sports_type'] ?? ''));

        if ($sportsType === '') {
            return [Text::_('COM_SPORTSMANAGEMENT_ADMIN_INSTALLHELPER_ERROR_1')];
        }

        $db = $this->getDatabase();
        $user = $this->administratorApplication()->getIdentity();
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
