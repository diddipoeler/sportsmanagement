<?php
/**
 * @package     SportsManagement
 * @subpackage  com_sportsmanagement
 */

namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\DatabaseInterface;

/**
 * Shared Joomla 5/6 list-model base.
 *
 * SportsManagement can be configured to use a database connection different
 * from Joomla's default connection. The MVCFactory injects Joomla's database
 * after creating a model, therefore list models need to re-resolve the
 * SportsManagement connection when setDatabase() is called.
 */
abstract class SportsManagementListModel extends ListModel
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
            $sportsManagementDb = \sportsmanagementHelper::getDBConnection();

            if ($sportsManagementDb instanceof DatabaseInterface) {
                parent::setDatabase($sportsManagementDb);

                return;
            }
        } catch (\Throwable) {
            // Keep Joomla's injected connection as a safe fallback.
        }

        parent::setDatabase($db);
    }
}
