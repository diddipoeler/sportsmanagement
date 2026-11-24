<?php
/**
 * Joomla 5/6 administrator prediction-template table.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;

final class PredictiontemplateTable extends SportsManagementTable
{
    /**
     * Initialise the native Joomla table for tip game template configurations.
     * Registers the database table and its primary key with the shared SportsManagement
     * table implementation using the injected database driver.
     * @param DatabaseInterface $db Active database connection.
     */
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct('#__sportsmanagement_prediction_template', 'id', $db);
    }
}
