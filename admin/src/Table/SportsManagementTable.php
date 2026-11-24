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
    /**
     * Initialise a native Joomla table on the effective SportsManagement database.
     * Resolves the configured component connection and retains the injected
     * Joomla driver when resolution fails, before registering the table and key.
     * @param string $table Database table name, including the Joomla prefix token.
     * @param string|array $key Primary key column or composite key columns.
     * @param DatabaseInterface $db Joomla database service used as fallback.
     */
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
     * Rejects structured season identifiers before serialising the list to
     * avoid PHP conversion warnings and accidentally persisted array labels.
     * @param array|object $src Submitted record fields.
     * @param array|string $ignore Fields excluded from binding.
     * @return bool Whether the parent table successfully binds the record.
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
            foreach ($src['season_ids'] as $seasonId) {
                if (!is_int($seasonId) && !is_string($seasonId)) {
                    $this->setError('Invalid season identifier list.');

                    return false;
                }
            }

            $src['season_ids'] = implode(',', $src['season_ids']);
        }

        return parent::bind($src, $ignore);
    }
}
