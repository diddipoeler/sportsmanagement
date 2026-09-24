<?php
/**
 * SportsManagement legacy compatibility bridge for the native Sportstype table.
 *
 * The active Joomla 5/6 implementation lives in admin/src/Table/SportstypeTable.php.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\SportstypeTable;

if (!class_exists(SportstypeTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SportstypeTable.php';
}

if (!class_exists(SportstypeTable::class)) {
    throw new \RuntimeException('SportsManagement native Sportstype table could not be loaded.', 500);
}

if (!class_exists('sportsmanagementTableSportsType', false)) {
    class_alias(SportstypeTable::class, 'sportsmanagementTableSportsType');
}
