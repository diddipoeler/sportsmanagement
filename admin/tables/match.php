<?php
/**
 * SportsManagement legacy compatibility bridge for the native Match table.
 *
 * The active Joomla 5/6 implementation lives in admin/src/Table/MatchTable.php.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\MatchTable;

if (!class_exists(MatchTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/MatchTable.php';
}

if (!class_exists('sportsmanagementTableMatch', false)) {
    class_alias(MatchTable::class, 'sportsmanagementTableMatch');
}
