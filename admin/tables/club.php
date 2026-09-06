<?php
/**
 * SportsManagement legacy compatibility bridge for the native administrator Club table.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Table\ClubTable;

if (!class_exists(ClubTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/ClubTable.php';
}

if (!class_exists('sportsmanagementTableClub', false)) {
    class_alias(ClubTable::class, 'sportsmanagementTableClub');
}
