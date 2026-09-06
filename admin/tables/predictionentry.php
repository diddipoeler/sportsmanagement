<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 PredictionentryTable.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Table\PredictionentryTable;

if (!class_exists(PredictionentryTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SportsManagementTable.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/PredictionentryTable.php';
}

if (!class_exists('sportsmanagementTablePredictionEntry', false)) {
    class_alias(PredictionentryTable::class, 'sportsmanagementTablePredictionEntry');
}
