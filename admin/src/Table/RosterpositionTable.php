<?php
/**
 * Joomla 5/6 administrator roster-position table.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Table;
\defined('_JEXEC') or die;
use Joomla\Database\DatabaseInterface;
final class RosterpositionTable extends SportsManagementTable
{
    /**
     * Initialise the native Joomla table for roster position definitions.
     * Registers the storage table and its primary key with the shared table
     * implementation using the injected Joomla database connection.
     * @param DatabaseInterface $db Active SportsManagement database driver.
     */
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct('#__sportsmanagement_rosterposition', 'id', $db);
    }
    /**
     * Use the roster position abbreviation as its alias when available.
     * Preserves the existing alias for empty abbreviations and rejects
     * malformed non-string values before invoking shared table validation.
     * @return bool Whether the record passes validation.
     */
    public function check()
    {
        $shortName = $this->short_name ?? '';

        if (!is_string($shortName)) {
            $this->setError('Invalid roster position abbreviation.');

            return false;
        }

        if ($shortName !== '') {
            $this->alias = $shortName;
        }

        return parent::check();
    }
}
