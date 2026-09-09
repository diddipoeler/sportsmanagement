<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 extra-field table.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\ExtrafieldTable;

if (!class_exists(ExtrafieldTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SportsManagementTable.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/ExtrafieldTable.php';
}

if (!class_exists(ExtrafieldTable::class)) {
    throw new \RuntimeException('SportsManagement native Extrafield table could not be loaded.', 500);
}

if (!class_exists('sportsmanagementTableExtraField', false)) {
    class_alias(ExtrafieldTable::class, 'sportsmanagementTableExtraField');
}
