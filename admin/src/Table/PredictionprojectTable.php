<?php
/**
 * Native Joomla 5/6 table implementation for prediction projects.
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

final class PredictionprojectTable extends SportsManagementTable
{
    /**
     * Initialise the native Joomla table for links between tip games and competition projects.
     * Registers the storage table and primary key with the shared table
     * implementation using the injected Joomla database driver.
     * @param DatabaseInterface $db Active SportsManagement database connection.
     * @param bool $resolved Whether the supplied connection has already been resolved.
     */
    public function __construct(DatabaseInterface $db, bool $resolved = false)
    {
        parent::__construct('#__sportsmanagement_prediction_project', 'id', $db, $resolved);
    }

    /**
     * Validate the required tip game, project references before saving.
     * Rejects missing, negative, malformed or overflowing identifiers instead
     * of coercing them into valid-looking database keys. Records a translated
     * validation error when any required reference is invalid.
     * @return bool Whether all required references are positive integer IDs.
     */
    public function check()
    {
        foreach ([$this->prediction_id ?? null, $this->project_id ?? null] as $id) {
            if ((!is_int($id) && !is_string($id))
                || filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false) {
                $this->setError(Text::_('CHECK FAILED'));

                return false;
            }
        }

        return true;
    }
}
