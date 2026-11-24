<?php
/**
 * Native Joomla 5/6 table for SportsManagement Google Calendar comments.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

final class JsmgcalendarcommentTable extends SportsManagementTable
{
    /**
     * Initialise the native Joomla table for Google Calendar action-pack comments.
     * Registers the database table and its primary key with the shared SportsManagement
     * table implementation using the injected database driver.
     * @param DatabaseInterface $db Active database connection.
     */
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct('#__sportsmanagement_gcalendarap_comment', 'id', $db);
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
}
