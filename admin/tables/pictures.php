<?php
/**
 * SportsManagement Joomla 5/6 file metadata.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/** Legacy compatibility bridge for the native administrator Pictures table. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\PicturesTable;

if (!class_exists(PicturesTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/PicturesTable.php';
}

if (!class_exists('sportsmanagementTablepictures', false)) {
    class_alias(PicturesTable::class, 'sportsmanagementTablepictures');
}
