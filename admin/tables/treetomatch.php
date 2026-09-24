<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 Treetomatch table.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\TreetomatchTable;

if (!class_exists(TreetomatchTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SportsManagementTable.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/TreetomatchTable.php';
}

if (!class_exists(TreetomatchTable::class)) {
    throw new \RuntimeException('SportsManagement native Treetomatch table could not be loaded.', 500);
}

if (!class_exists('sportsmanagementTableTreetoMatch', false)) {
    class_alias(TreetomatchTable::class, 'sportsmanagementTableTreetoMatch');
}
