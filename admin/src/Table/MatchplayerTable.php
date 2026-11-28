<?php
/**
 * Joomla 5/6 administrator match-player table.
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

/** Native Joomla 5/6 table for match players. */
final class MatchplayerTable extends SportsManagementTable
{
    /**
     * Initialise the native Joomla table for player participation in matches.
     * Registers the underlying storage table and its primary key with the
     * shared table implementation using the injected database service.
     * @param DatabaseInterface $db Active SportsManagement database driver.
     * @param bool $resolved Whether the supplied connection has already been resolved.
     */
    public function __construct(DatabaseInterface $db, bool $resolved = false)
    {
        parent::__construct('#__sportsmanagement_match_player', 'id', $db, $resolved);
    }

    /**
     * Validate the match and team player references before storing the assignment.
     * Rejects missing, negative, overflowing and malformed IDs rather than
     * accepting arbitrary truthy values. Records a translated error on failure.
     * @return bool Whether both references are positive integer IDs.
     */
    public function check()
    {
        foreach ([$this->match_id ?? null, $this->teamplayer_id ?? null] as $id) {
            if ((!is_int($id) && !is_string($id))
                || filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false) {
                $this->setError(Text::_('CHECK FAILED'));

                return false;
            }
        }

        return true;
    }
}
