<?php
/**
 * Joomla 5/6 administrator agegroup table.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package     SportsManagement
 * @subpackage  com_sportsmanagement
 */

namespace Diddipoeler\Component\SportsManagement\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\CMS\Filter\OutputFilter;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

class AgegroupTable extends SportsManagementTable
{
    /**
     * Initialise the native Joomla table for age group definitions and their extended configuration.
     * Registers the storage table and its primary key with the shared table
     * implementation using the injected Joomla database connection.
     * @param DatabaseInterface $db Active SportsManagement database driver.
     */
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct('#__sportsmanagement_agegroup', 'id', $db);
    }

    /**
     * Generate the age group URL alias from its existing alias or name.
     * Uses Joomla's URL-safe output filter before the record is stored.
     * @return bool True after the alias has been prepared.
     */
    public function check()
    {
        $this->alias = OutputFilter::stringURLSafe((string) ($this->alias ?: $this->name));

        return true;
    }

    /**
     * Load an age group and decode its extended configuration for editing.
     * Delegates record lookup to Joomla and converts the stored registry
     * string into an array after the record was successfully loaded.
     * @param mixed $pk Primary key or key mapping used to locate the record.
     * @param bool $reset Whether to reset table properties before loading.
     * @return bool Whether the record was loaded successfully.
     */
    public function load($pk = null, $reset = true)
    {
        if (!parent::load($pk, $reset)) {
            return false;
        }

        $registry = new Registry();
        $registry->loadString((string) $this->extended);
        $this->extended = $registry->toArray();

        return true;
    }
}
