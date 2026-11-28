<?php
/**
 * Joomla 5/6 administrator match-statistic table.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;

final class MatchstatisticTable extends SportsManagementTable
{
    /**
     * Initialise the native Joomla table for statistic values associated with matches.
     * Registers the storage table and its primary key with the shared table
     * implementation using the injected Joomla database connection.
     * @param DatabaseInterface $db Active SportsManagement database driver.
     * @param bool $resolved Whether the supplied connection has already been resolved.
     */
    public function __construct(DatabaseInterface $db, bool $resolved = false)
    {
        parent::__construct('#__sportsmanagement_match_statistic', 'id', $db, $resolved);
    }
}
