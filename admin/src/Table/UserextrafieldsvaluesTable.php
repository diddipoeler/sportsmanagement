<?php
/**
 * Native Joomla 5/6 administrator table for user extra-field values.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;

final class UserextrafieldsvaluesTable extends SportsManagementTable
{
    /**
     * Initialise the native Joomla table for stored values of user-defined extra fields.
     * Registers the database table and its primary key with the shared SportsManagement
     * table implementation using the injected database driver.
     * @param DatabaseInterface $db Active database connection.
     */
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct('#__sportsmanagement_user_extra_fields_values', 'id', $db);
    }
}
