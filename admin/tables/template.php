<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 Template table.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\TemplateTable;

if (!class_exists(TemplateTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SportsManagementTable.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/TemplateTable.php';
}

if (!class_exists(TemplateTable::class)) {
    throw new \RuntimeException('SportsManagement native Template table could not be loaded.', 500);
}

if (!class_exists('sportsmanagementTableTemplate', false)) {
    class_alias(TemplateTable::class, 'sportsmanagementTableTemplate');
}
