<?php
/**
 * SportsManagement legacy compatibility bridge for the native administrator Division table.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\DivisionTable;

if (!class_exists(DivisionTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/DivisionTable.php';
}

if (!class_exists('sportsmanagementTableDivision', false)) {
    class_alias(DivisionTable::class, 'sportsmanagementTableDivision');
}
