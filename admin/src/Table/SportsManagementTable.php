<?php
/**
 * Shared native Joomla 5/6 table base for SportsManagement.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
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
     * Preserve SportsManagement's array-to-registry conversion behaviour while
     * accepting the array|object input supported by Joomla's Table API.
     */
    public function bind($src, $ignore = [])
    {
        if (is_object($src)) {
            $src = get_object_vars($src);
        }

        if (!is_array($src)) {
            return parent::bind($src, $ignore);
        }

        foreach (['extended', 'extendeduser', 'params', 'comp_params'] as $field) {
            if (array_key_exists($field, $src) && is_array($src[$field])) {
                $registry = new Registry();
                $registry->loadArray($src[$field]);
                $src[$field] = $registry->toString();
            }
        }

        if (isset($src['season_ids']) && is_array($src['season_ids'])) {
            $src['season_ids'] = implode(',', $src['season_ids']);
        }

        return parent::bind($src, $ignore);
    }
}
