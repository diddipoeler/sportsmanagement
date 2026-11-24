<?php
/**
 * Native Joomla 5/6 table implementation for prediction administrators.
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

final class PredictionadminTable extends SportsManagementTable
{
    /**
     * Initialise the native Joomla table for tip game administrator assignments.
     * Registers the database table and its primary key with the shared SportsManagement
     * table implementation using the injected database driver.
     * @param DatabaseInterface $db Active database connection.
     */
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct('#__sportsmanagement_prediction_admin', 'id', $db);
    }

    /**
     * Validate the tip game and Joomla user references before saving.
     * Rejects missing, negative, malformed and overflowing identifiers and
     * records a translated validation error when either reference is invalid.
     * @return bool Whether both references are positive integer IDs.
     */
    public function check()
    {
        foreach ([$this->prediction_id ?? null, $this->user_id ?? null] as $id) {
            if ((!is_int($id) && !is_string($id))
                || filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false) {
                $this->setError(Text::_('CHECK FAILED'));

                return false;
            }
        }

        return true;
    }
}
