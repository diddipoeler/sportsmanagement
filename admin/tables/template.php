<?php
/** SportsManagement legacy compatibility bridge for the template table. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\TemplateTable;

if (!class_exists(TemplateTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/TemplateTable.php';
}

if (!class_exists('sportsmanagementTableTemplate', false)) {
    class_alias(TemplateTable::class, 'sportsmanagementTableTemplate');
}
