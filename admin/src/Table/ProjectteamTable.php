<?php
/**
 * Native Joomla 5/6 table for project teams.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;

final class ProjectteamTable extends SportsManagementTable
{
    /**
     * Initialise the project-team membership table on the selected database.
     * Registers the project-team storage table and its primary identifier.
     * @param DatabaseInterface $db Active SportsManagement database connection.
     * @param bool $resolved Whether the connection has already been resolved.
     */
    public function __construct(DatabaseInterface $db, bool $resolved = false)
    {
        parent::__construct('#__sportsmanagement_project_team', 'id', $db, $resolved);
    }
}
