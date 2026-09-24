<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 Pictures table.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\PicturesTable;

if (!class_exists(PicturesTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SportsManagementTable.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/PicturesTable.php';
}

if (!class_exists(PicturesTable::class)) {
    throw new \RuntimeException('SportsManagement native Pictures table could not be loaded.', 500);
}

if (!class_exists('sportsmanagementTablepictures', false)) {
    class_alias(PicturesTable::class, 'sportsmanagementTablepictures');
}
