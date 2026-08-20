<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Database\DatabaseInterface;

/**
 * Native Joomla 5/6 landing model for the XML import workflow.
 *
 * The actual import operations remain in the singular Jlxmlimport model. This
 * plural model only provides the component database/state expected by the
 * legacy import landing view.
 */
final class JlxmlimportsModel extends BaseDatabaseModel
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
            $sportsDatabase = \sportsmanagementHelper::getDBConnection();

            if ($sportsDatabase instanceof DatabaseInterface) {
                parent::setDatabase($sportsDatabase);

                return;
            }
        } catch (\Throwable) {
            // Fall back to Joomla's injected database if the component helper
            // cannot provide its configured connection yet.
        }

        parent::setDatabase($db);
    }
}
