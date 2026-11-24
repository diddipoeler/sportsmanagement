<?php
/**
 * Joomla 5/6 administrator GCalendar action-pack table.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\CMS\Access\Rules;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Asset;
use Joomla\Database\DatabaseInterface;

/** Native Joomla 5/6 table for SportsManagement GCalendar action-pack data. */
final class JsmgcalendarapTable extends SportsManagementTable
{
    /**
     * Initialise the native Joomla table for Google Calendar action-pack event records.
     * Registers the database table and its primary key with the shared SportsManagement
     * table implementation using the injected database driver.
     * @param DatabaseInterface $db Active database connection.
     */
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct('#__sportsmanagement_gcalendarap', 'id', $db);
    }

    /**
     * Bind submitted action-pack event data to the current table record.
     * Converts submitted ACL rule arrays into Joomla Rules before binding.
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

        if (is_array($array) && isset($array['rules']) && is_array($array['rules'])) {
            $this->setRules(new Rules($array['rules']));
        }

        return parent::bind($array, $ignore);
    }

    /**
     * Build the historical Joomla ACL asset name for the current event.
     * Uses the table primary key to distinguish individual event assets.
     * @return string Event-specific ACL asset name.
     */
    protected function _getAssetName()
    {
        $key = $this->_tbl_key;

        return 'com_gcalendarap.event.' . (int) $this->{$key};
    }

    /**
     * Resolve the parent component ACL asset from Joomla's local database.
     * Keeps permission metadata separate from any external sports database.
     * @param mixed $table Optional parent table supplied by Joomla, unused here.
     * @param mixed $id Optional asset ID supplied by Joomla, unused here.
     * @return int Component asset ID, or zero if it was not found.
     */
    protected function _getAssetParentId($table = null, $id = null)
    {
        // ACL assets always live in Joomla's own database, even when
        // SportsManagement uses an external database for competition data.
        $joomlaDatabase = Factory::getContainer()->get(DatabaseInterface::class);
        $asset = new Asset($joomlaDatabase);
        $asset->loadByName('com_gcalendarap');

        return (int) $asset->id;
    }
}
