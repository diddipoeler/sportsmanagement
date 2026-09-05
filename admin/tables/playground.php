<?php
/**
 * SportsManagement legacy compatibility bridge.
 *
 * The active Joomla 5/6 implementation lives in admin/src/Table/PlaygroundTable.php.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\PlaygroundTable;

if (!class_exists(PlaygroundTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/PlaygroundTable.php';
}

if (!class_exists('sportsmanagementTablePlayground', false)) {
    class_alias(PlaygroundTable::class, 'sportsmanagementTablePlayground');
}
