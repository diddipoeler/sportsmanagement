<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 confidential table.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\ConfidentialTable;

if (!class_exists(ConfidentialTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SportsManagementTable.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/ConfidentialTable.php';
}

if (!class_exists(ConfidentialTable::class)) {
    throw new \RuntimeException('SportsManagement native Confidential table could not be loaded.', 500);
}

if (!class_exists('sportsmanagementTableconfidential', false)) {
    class_alias(ConfidentialTable::class, 'sportsmanagementTableconfidential');
}
