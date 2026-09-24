<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 Jlextfederation table.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\JlextfederationTable;

if (!class_exists(JlextfederationTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SportsManagementTable.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/JlextfederationTable.php';
}

if (!class_exists(JlextfederationTable::class)) {
    throw new \RuntimeException('SportsManagement native Jlextfederation table could not be loaded.', 500);
}

if (!class_exists('sportsmanagementTablejlextfederation', false)) {
    class_alias(JlextfederationTable::class, 'sportsmanagementTablejlextfederation');
}
