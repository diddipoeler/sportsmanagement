<?php
/**
 * Joomla 5/6 administrator JoomLeague import facade model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Database\ParameterType;

/**
 * Joomla 5/6 model facade for the remaining historical JoomLeague import engine.
 *
 * Small local-database operations live natively here. Only the old external
 * database/table-conversion engine remains behind the explicit legacy boundary.
 */
final class JoomleagueimportsModel extends BaseDatabaseModel
{
    private ?object $legacyModel = null;

    public function check_database()
    {
        return $this->legacy()->check_database();
    }

    public function get_info_fields(): array
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('info'),
                $db->quoteName('agegroup_id'),
            ])
            ->from($db->quoteName('#__sportsmanagement_team'))
            ->where($db->quoteName('info') . ' <> :emptyInfo')
            ->group([
                $db->quoteName('info'),
                $db->quoteName('agegroup_id'),
            ])
            ->bind(':emptyInfo', '', ParameterType::STRING);
        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    public function joomleaguesetagegroup(): int
    {
        $post = $this->getCurrentUserState('com_sportsmanagement.joomleagueimports.data', []);
        $inputPost = \Joomla\CMS\Factory::getApplication()->getInput()->post->getArray();
        $agegroups = (array) ($inputPost['agegroup'] ?? $post['agegroup'] ?? []);
        $db = $this->getDatabase();
        $updated = 0;

        foreach ($agegroups as $info => $agegroupId) {
            $info = (string) $info;
            $agegroupId = (int) $agegroupId;

            if ($info === '') {
                continue;
            }

            $query = $db->getQuery(true)
                ->update($db->quoteName('#__sportsmanagement_team'))
                ->set($db->quoteName('agegroup_id') . ' = :agegroupId')
                ->where($db->quoteName('info') . ' = :teamInfo')
                ->bind(':agegroupId', $agegroupId, ParameterType::INTEGER)
                ->bind(':teamInfo', $info, ParameterType::STRING);
            $db->setQuery($query);
            $db->execute();
            $updated += max(0, (int) $db->getAffectedRows());
        }

        return $updated;
    }

    public function importjoomleaguenew($importstep = 0, $sportsTypeId = 0)
    {
        return $this->legacy()->importjoomleaguenew($importstep, $sportsTypeId);
    }

    private function legacy(): object
    {
        if ($this->legacyModel !== null) {
            return $this->legacyModel;
        }

        LegacyBootstrap::boot();
        $legacyFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/models/joomleagueimports.php';

        if (!class_exists('sportsmanagementModeljoomleagueimports', false) && is_file($legacyFile)) {
            require_once $legacyFile;
        }

        if (!class_exists('sportsmanagementModeljoomleagueimports', false)) {
            throw new \RuntimeException('Legacy JoomLeague import engine is unavailable.', 500);
        }

        $this->legacyModel = new \sportsmanagementModeljoomleagueimports(['ignore_request' => true]);

        return $this->legacyModel;
    }
}
