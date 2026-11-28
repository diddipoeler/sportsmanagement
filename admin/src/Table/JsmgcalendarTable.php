<?php
/**
 * Native Joomla 5/6 table implementation for SportsManagement Google Calendar records.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\CMS\Crypt\Crypt;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

final class JsmgcalendarTable extends SportsManagementTable
{
    /**
     * Initialise the native Joomla table for Google Calendar connection and configuration records.
     * Registers the database table and its primary key with the shared SportsManagement
     * table implementation using the injected database driver.
     * @param DatabaseInterface $db Active database connection.
     * @param bool $resolved Whether the connection has already been resolved.
     */
    public function __construct(DatabaseInterface $db, bool $resolved = false)
    {
        parent::__construct('#__sportsmanagement_gcalendar', 'id', $db, $resolved);
    }

    /**
     * Bind submitted calendar data to the current table record.
     * Serialises array-valued parameters using Joomla Registry before binding.
     * Delegates field assignment to the parent table implementation.
     * @param array|object $array Submitted field values.
     * @param string|array $ignore Fields excluded from binding.
     * @return bool Whether binding succeeds.
     */
    public function bind($array, $ignore = '')
    {
        if (is_object($array)) {
            $array = get_object_vars($array);
        }

        if (is_array($array) && isset($array['params']) && is_array($array['params'])) {
            $params = new Registry();
            $params->loadArray($array['params']);
            $array['params'] = $params->toString();
        }

        return parent::bind($array, $ignore);
    }

    /**
     * Load a calendar record and decrypt its stored password for editing.
     * Decrypts only after the parent table successfully loads a record.
     * @param mixed $keys Primary key or field-value lookup criteria.
     * @param bool $reset Whether to reset properties before loading.
     * @return bool Whether the parent table loaded the record.
     */
    public function load($keys = null, $reset = true)
    {
        $result = parent::load($keys, $reset);

        if ($result && !empty($this->password)) {
            $this->password = (new Crypt())->decrypt((string) $this->password);
        }

        return $result;
    }

    /**
     * Store calendar settings with the password encrypted at rest.
     * Restores the in-memory plaintext after the parent store call, even
     * when persistence throws an exception, to preserve the editing contract.
     * @param bool $updateNulls Whether null-valued fields are persisted.
     * @return bool Whether persistence succeeds.
     */
    public function store($updateNulls = false)
    {
        $plainPassword = (string) ($this->password ?? '');

        if ($plainPassword !== '') {
            $this->password = (new Crypt())->encrypt($plainPassword);
        }

        try {
            return parent::store($updateNulls);
        } finally {
            $this->password = $plainPassword;
        }
    }
}
