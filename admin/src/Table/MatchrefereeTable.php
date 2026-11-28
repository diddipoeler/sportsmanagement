<?php
/**
 * Joomla 5/6 administrator match-referee table.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;

/** Native Joomla 5/6 table for match referees. */
final class MatchrefereeTable extends SportsManagementTable
{
    /**
     * Initialise the native Joomla table for referee assignments to matches.
     * Registers the storage table and its primary key with the shared table
     * implementation using the injected Joomla database connection.
     * @param DatabaseInterface $db Active SportsManagement database driver.
     * @param bool $resolved Whether the supplied connection has already been resolved.
     */
    public function __construct(DatabaseInterface $db, bool $resolved = false)
    {
        parent::__construct('#__sportsmanagement_match_referee', 'id', $db, $resolved);
    }

    /**
     * Validate the required match and project referee references before saving.
     * Rejects missing, negative, overflowing and malformed identifiers rather
     * than coercing arbitrary PHP values into valid database references.
     * @return bool Whether both references are positive integer IDs.
     */
    public function check()
    {
        foreach ([$this->match_id ?? null, $this->project_referee_id ?? null] as $id) {
            if ((!is_int($id) && !is_string($id))
                || filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false) {
                $this->setError(Text::_('CHECK FAILED'));

                return false;
            }
        }

        return true;
    }
}
