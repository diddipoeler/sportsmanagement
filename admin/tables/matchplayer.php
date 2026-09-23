<?php
/**
 * SportsManagement Joomla 5/6 file metadata.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/** Legacy compatibility bridge for the native Matchplayer table. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\MatchplayerTable;
use Diddipoeler\Component\SportsManagement\Administrator\Table\SportsManagementTable;

if (!class_exists(SportsManagementTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SportsManagementTable.php';
}

if (!class_exists(MatchplayerTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/MatchplayerTable.php';
}

if (!class_exists('sportsmanagementTableMatchplayer', false)) {
    class_alias(MatchplayerTable::class, 'sportsmanagementTableMatchplayer');
}
