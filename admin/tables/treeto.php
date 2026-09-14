<?php
/**
 * SportsManagement legacy compatibility bridge for the native Treeto table.
 *
 * The active Joomla 5/6 implementation lives in admin/src/Table/TreetoTable.php.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\TreetoTable;

if (!class_exists(TreetoTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SportsManagementTable.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/TreetoTable.php';
}

if (!class_exists('sportsmanagementTableTreeto', false)) {
    class_alias(TreetoTable::class, 'sportsmanagementTableTreeto');
}
