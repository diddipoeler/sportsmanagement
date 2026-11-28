<?php
/**
 * Native Joomla 5/6 table for team training data.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;

final class TeamTrainingDataTable extends SportsManagementTable
{
    /**
     * Initialise the native team training data table with the selected database service.
     * Registers the component storage table and its primary key with the shared table base.
     *
     * @param DatabaseInterface $db Active database connection.
     * @param bool $resolved Whether the caller has already resolved the connection.
     */
    public function __construct(DatabaseInterface $db, bool $resolved = false)
    {
        parent::__construct('#__sportsmanagement_team_trainingdata', 'id', $db, $resolved);
    }
}
