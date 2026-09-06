<?php
/**
 * SportsManagement legacy compatibility bridge for the native Round table.
 *
 * The active Joomla 5/6 implementation lives in admin/src/Table/RoundTable.php.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\RoundTable;

if (!class_exists(RoundTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SportsManagementTable.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/RoundTable.php';
}

if (!class_exists('sportsmanagementTableRound', false)) {
    class_alias(RoundTable::class, 'sportsmanagementTableRound');
}
