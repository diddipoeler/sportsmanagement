<?php
/**
 * Joomla 5/6 administrator individual-match table.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;

/** Joomla 5/6 table for an individual match row. */
final class MatchSingleTable extends SportsManagementTable
{
    /**
     * Initialise the native individual-match table with the selected database service.
     * Registers the component storage table and its primary key with the shared table base.
     *
     * @param DatabaseInterface $db Active database connection.
     * @param bool $resolved Whether the connection has already been resolved.
     */
    public function __construct(DatabaseInterface $db, bool $resolved = false)
    {
        parent::__construct('#__sportsmanagement_match_single', 'id', $db, $resolved);
    }

    /**
     * Normalise optional decision scores before the individual match is stored.
     * Retains numeric values and converts missing or nonnumeric values to null,
     * distinguishing an unrecorded decision from a recorded zero score.
     *
     * @return bool True after normalisation.
     */
    public function check(): bool
    {
        if (!is_numeric($this->team1_result_decision ?? null)) {
            $this->team1_result_decision = null;
        }
        if (!is_numeric($this->team2_result_decision ?? null)) {
            $this->team2_result_decision = null;
        }
        return true;
    }
}
