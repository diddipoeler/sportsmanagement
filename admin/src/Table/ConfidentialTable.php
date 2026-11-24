<?php
/**
 * Native Joomla 5/6 table for SportsManagement confidential records.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;

final class ConfidentialTable extends SportsManagementTable
{
    /**
     * Initialise the native Joomla table for confidential SportsManagement records.
     * Registers the database table and its primary key with the shared SportsManagement
     * table implementation using the injected database driver.
     * @param DatabaseInterface $db Active database connection.
     */
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct('#__sportsmanagement_confidential', 'id', $db);
    }
}
