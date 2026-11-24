<?php
/**
 * Joomla 5/6 administrator match-staff-statistic table.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;

final class MatchstaffstatisticTable extends SportsManagementTable
{
    /**
     * Initialise the native Joomla table for statistic values recorded for match staff.
     * Registers the storage table and its primary key with the shared table
     * implementation using the injected Joomla database connection.
     * @param DatabaseInterface $db Active SportsManagement database driver.
     */
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct('#__sportsmanagement_match_staff_statistic', 'id', $db);
    }
}
