<?php
/**
 * Joomla 5/6 administrator federation table.
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

final class JlextfederationTable extends SportsManagementTable
{
    /**
     * Initialise the native Joomla table for sports federation reference records.
     * Registers the storage table and its primary key with the shared table
     * implementation using the injected Joomla database connection.
     * @param DatabaseInterface $db Active SportsManagement database driver.
     */
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct('#__sportsmanagement_federations', 'id', $db);
    }

    /**
     * Prepare a default federation alias without replacing a custom alias.
     * Generates a URL-safe alias from the federation name and preserves a
     * distinct existing alias for historical links.
     * @return bool True after preparing the federation record.
     */
    public function check()
    {
        $alias = OutputFilter::stringURLSafe((string) ($this->name ?? ''));

        if (empty($this->alias) || $this->alias === $alias) {
            $this->alias = $alias;
        }

        return true;
    }
}
