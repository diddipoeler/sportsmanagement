<?php
/**
 * Joomla 5/6 administrator team-staff table.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;

final class TeamstaffTable extends SportsManagementTable
{
    /**
     * Initialise the native Joomla table for team staff assignments.
     * Registers the underlying storage table and its primary key with the
     * shared table implementation using the injected database service.
     * @param DatabaseInterface $db Active SportsManagement database driver.
     */
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct('#__sportsmanagement_team_staff', 'id', $db);
    }

    /**
     * Preserve the historical team staff deletion eligibility contract.
     * This table hook does not perform dependency or permission checks;
     * callers remain responsible for authorisation and related cleanup.
     * @param mixed $id Staff record identifier supplied by the caller.
     * @param mixed $joins Optional legacy dependency configuration, unused here.
     * @return bool Always true, preserving existing behaviour.
     */
    public function canDelete($id, $joins = null)
    {
        return true;
    }
}
