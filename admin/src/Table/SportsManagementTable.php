<?php
/**
 * @package     SportsManagement
 * @subpackage  com_sportsmanagement
 *
 * Joomla 5/6 migration.
 */

namespace Diddipoeler\Component\SportsManagement\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

/**
 * Shared native table base for SportsManagement.
 */
abstract class SportsManagementTable extends Table
{
    public function __construct($table, $key, DatabaseInterface $db)
    {
        if (!class_exists('sportsmanagementHelper')) {
            \JLoader::register(
                'sportsmanagementHelper',
                JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/helpers/sportsmanagement.php'
            );
        }

        $sportsManagementDb = \sportsmanagementHelper::getDBConnection();

        parent::__construct($table, $key, $sportsManagementDb ?: $db);
    }

    /**
     * Preserve SportsManagement's array-to-registry conversion behaviour.
     */
    public function bind($array, $ignore = '')
    {
        foreach (['extended', 'extendeduser', 'params', 'comp_params'] as $field) {
            if (array_key_exists($field, $array) && is_array($array[$field])) {
                $registry = new Registry();
                $registry->loadArray($array[$field]);
                $array[$field] = $registry->toString();
            }
        }

        if (isset($array['season_ids']) && is_array($array['season_ids'])) {
            $array['season_ids'] = implode(',', $array['season_ids']);
        }

        return parent::bind($array, $ignore);
    }
}
