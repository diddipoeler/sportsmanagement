<?php
/**
 * Joomla 5/6 administrator league table.
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

final class LeagueTable extends SportsManagementTable
{
    /**
     * Initialise the native Joomla table for league definitions.
     * Registers the storage table and its primary key with the shared table
     * implementation using the injected Joomla database connection.
     * @param DatabaseInterface $db Active SportsManagement database driver.
     */
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct('#__sportsmanagement_league', 'id', $db);
    }

    /**
     * Generate a URL-safe league alias from its name.
     * Delegates remaining validation to the shared native Joomla table.
     * @return bool Whether the shared table validation succeeds.
     */
    public function check()
    {
        $this->alias = OutputFilter::stringURLSafe((string) $this->name);

        return parent::check();
    }
}
