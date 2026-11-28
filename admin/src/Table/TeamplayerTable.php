<?php
/**
 * Native Joomla 5/6 administrator table for Teamplayer.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;

final class TeamplayerTable extends SportsManagementTable
{
    /**
     * Initialise the native Joomla table for player roster assignments stored in the season-team-person relation.
     * Registers the underlying storage table and its primary key with the
     * shared table implementation using the injected database service.
     * @param DatabaseInterface $db Active SportsManagement database driver.
     * @param bool $resolved Whether the caller has already resolved the connection.
     */
    public function __construct(DatabaseInterface $db, bool $resolved = false)
    {
        parent::__construct('#__sportsmanagement_season_team_person_id', 'id', $db, $resolved);
    }
}
