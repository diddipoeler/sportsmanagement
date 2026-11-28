<?php
/**
 * Joomla 5/6 administrator position-statistic relation table.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;

final class PositionstatisticTable extends SportsManagementTable
{
    /**
     * Initialise the native Joomla table for links between positions and statistic definitions.
     * Registers the storage table and its primary key with the shared table
     * implementation using the injected Joomla database connection.
     * @param DatabaseInterface $db Active SportsManagement database driver.
     * @param bool $resolved Whether the caller has already resolved the driver.
     */
    public function __construct(DatabaseInterface $db, bool $resolved = false)
    {
        parent::__construct('#__sportsmanagement_position_statistic', 'id', $db, $resolved);
    }

    /**
     * Validate the required position and statistic references before saving.
     * Rejects missing, negative, overflowing and malformed identifiers rather
     * than coercing arbitrary PHP values into valid database references.
     * @return bool Whether both references are positive integer IDs.
     */
    public function check()
    {
        foreach ([$this->position_id ?? null, $this->statistic_id ?? null] as $id) {
            if ((!is_int($id) && !is_string($id))
                || filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false) {
                $this->setError(Text::_('CHECK FAILED'));

                return false;
            }
        }

        return true;
    }
}
