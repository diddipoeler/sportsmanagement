<?php
/**
 * @package     SportsManagement
 * @subpackage  com_sportsmanagement
 *
 * Joomla 5/6 migration.
 */

namespace Diddipoeler\Component\SportsManagement\Administrator\Table;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementDatabaseResolver;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;
use Throwable;

/**
 * Shared native table base for SportsManagement.
 */
abstract class SportsManagementTable extends Table
{
    public function __construct($table, $key, DatabaseInterface $db)
    {
        try {
            $sportsManagementDb = (new SportsManagementDatabaseResolver())->resolve(null, $db);
        } catch (Throwable) {
            // Keep Joomla's injected database when custom database resolution fails.
            $sportsManagementDb = $db;
        }

        parent::__construct($table, $key, $sportsManagementDb);
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
