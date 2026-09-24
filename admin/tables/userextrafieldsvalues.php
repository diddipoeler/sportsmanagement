<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 Userextrafieldsvalues table.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\UserextrafieldsvaluesTable;

if (!class_exists(UserextrafieldsvaluesTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SportsManagementTable.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/UserextrafieldsvaluesTable.php';
}

if (!class_exists(UserextrafieldsvaluesTable::class)) {
    throw new \RuntimeException('SportsManagement native Userextrafieldsvalues table could not be loaded.', 500);
}

if (!class_exists('sportsmanagementTableuserextrafieldsvalues', false)) {
    class_alias(UserextrafieldsvaluesTable::class, 'sportsmanagementTableuserextrafieldsvalues');
}
