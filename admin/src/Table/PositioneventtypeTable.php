<?php
/**
 * Joomla 5/6 administrator position-eventtype relation table.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;

final class PositioneventtypeTable extends SportsManagementTable
{
    /**
     * Initialise the native Joomla table for links between positions and permitted event types.
     * Registers the storage table and its primary key with the shared table
     * implementation using the injected Joomla database connection.
     * @param DatabaseInterface $db Active SportsManagement database driver.
     */
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct('#__sportsmanagement_position_eventtype', 'id', $db);
    }
}
