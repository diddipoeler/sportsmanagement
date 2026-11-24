<?php
/**
 * Native Joomla 5/6 administrator Division table.
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

class DivisionTable extends SportsManagementTable
{
    /**
     * Initialise the native Joomla table for competition division records.
     * Registers the storage table and its primary key with the shared table
     * implementation using the injected Joomla database connection.
     * @param DatabaseInterface $db Active SportsManagement database driver.
     */
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct('#__sportsmanagement_division', 'id', $db);
    }

    /**
     * Generate the division's URL-safe alias from its name.
     * Uses Joomla's output filter before persisting the division record.
     * @return bool True after preparing the alias.
     */
    public function check()
    {
        $this->alias = OutputFilter::stringURLSafe((string) $this->name);

        return true;
    }
}
