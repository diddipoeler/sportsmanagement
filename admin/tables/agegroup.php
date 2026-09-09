<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 agegroup table.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\AgegroupTable;

if (!class_exists(AgegroupTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SportsManagementTable.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/AgegroupTable.php';
}

if (!class_exists(AgegroupTable::class)) {
    throw new \RuntimeException('SportsManagement native Agegroup table could not be loaded.', 500);
}

if (!class_exists('sportsmanagementTableagegroup', false)) {
    class_alias(AgegroupTable::class, 'sportsmanagementTableagegroup');
}
