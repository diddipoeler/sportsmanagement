<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Database\DatabaseInterface;

abstract class SportsManagementModel extends BaseDatabaseModel
{
    public function setDatabase(DatabaseInterface $db): void
    {
        if (!class_exists('sportsmanagementHelper')) {
            \JLoader::register(
                'sportsmanagementHelper',
                JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/helpers/sportsmanagement.php'
            );
        }

        try {
            $databaseSelector = Factory::getApplication()->getInput()->getInt('cfg_which_database', 0);
            $sportsManagementDb = \sportsmanagementHelper::getDBConnection(true, $databaseSelector);

            if ($sportsManagementDb instanceof DatabaseInterface) {
                parent::setDatabase($sportsManagementDb);
                return;
            }
        } catch (\Throwable) {
            // Keep Joomla's injected database connection as a safe fallback.
        }

        parent::setDatabase($db);
    }
}
