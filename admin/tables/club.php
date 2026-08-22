<?php
/**
 * SportsManagement legacy compatibility bridge.
 *
 * The active Joomla 5/6 implementation lives in admin/src/Table/ClubTable.php.
 */

defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Table\ClubTable;

if (!class_exists(ClubTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/ClubTable.php';
}

if (!class_exists('sportsmanagementTableClub', false)) {
    class_alias(ClubTable::class, 'sportsmanagementTableClub');
}
