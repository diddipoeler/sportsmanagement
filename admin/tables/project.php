<?php
/**
 * SportsManagement legacy compatibility bridge.
 *
 * The active Joomla 5/6 implementation lives in admin/src/Table/ProjectTable.php.
 */

defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Table\ProjectTable;

if (!class_exists(ProjectTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/ProjectTable.php';
}

if (!class_exists('sportsmanagementTableProject', false)) {
    class_alias(ProjectTable::class, 'sportsmanagementTableProject');
}
