<?php
/**
 * SportsManagement Joomla 5/6 file metadata.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/** Legacy compatibility bridge for the native PredictiontemplateTable. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Table\PredictiontemplateTable;

if (!class_exists(PredictiontemplateTable::class)) {
    $tableFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/PredictiontemplateTable.php';

    if (is_file($tableFile)) {
        require_once $tableFile;
    }
}

if (class_exists(PredictiontemplateTable::class) && !class_exists('sportsmanagementTablePredictionTemplate', false)) {
    class_alias(PredictiontemplateTable::class, 'sportsmanagementTablePredictionTemplate');
}
