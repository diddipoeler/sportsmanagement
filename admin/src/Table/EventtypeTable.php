<?php
/**
 * Native Joomla 5/6 administrator Eventtype table.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\CMS\Filter\OutputFilter;
use Joomla\Database\DatabaseInterface;

final class EventtypeTable extends SportsManagementTable
{
    /**
     * Initialise the native Joomla table for match event type definitions.
     * Registers the storage table and its primary key with the shared table
     * implementation using the injected Joomla database connection.
     * @param DatabaseInterface $db Active SportsManagement database driver.
     * @param bool $resolved Whether the connection has already been resolved.
     */
    public function __construct(DatabaseInterface $db, bool $resolved = false)
    {
        parent::__construct('#__sportsmanagement_eventtype', 'id', $db, $resolved);
    }

    /**
     * Prepare the event type alias from the existing alias or event name.
     * Uses Joomla's URL-safe filter and delegates remaining table checks.
     * @return bool Whether the shared table validation succeeds.
     */
    public function check()
    {
        $this->alias = OutputFilter::stringURLSafe($this->alias ?: $this->name);

        return parent::check();
    }
}
