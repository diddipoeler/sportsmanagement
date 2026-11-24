<?php
/**
 * Joomla 5/6 administrator prediction-round table.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;

final class PredictionroundTable extends SportsManagementTable
{
    /**
     * Initialise the native Joomla table for tip game round records.
     * Registers the storage table and primary key with the shared table
     * implementation using the injected Joomla database driver.
     * @param DatabaseInterface $db Active SportsManagement database connection.
     */
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct('#__sportsmanagement_prediction_tippround', 'id', $db);
    }
}
