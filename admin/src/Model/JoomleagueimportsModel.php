<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Joomla 5/6 model facade for the remaining historical JoomLeague import engine.
 *
 * The administrator controller and view resolve this namespaced model normally.
 * Only the old table-conversion engine remains behind this explicit boundary.
 */
final class JoomleagueimportsModel extends BaseDatabaseModel
{
    private ?object $legacyModel = null;

    public function check_database()
    {
        return $this->legacy()->check_database();
    }

    public function get_info_fields()
    {
        return $this->legacy()->get_info_fields();
    }

    public function joomleaguesetagegroup()
    {
        return $this->legacy()->joomleaguesetagegroup();
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
