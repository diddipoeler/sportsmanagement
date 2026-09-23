<?php
/**
 * SportsManagement Joomla 5/6 file metadata.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Table\PositioneventtypeTable;

if (!class_exists(PositioneventtypeTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SportsManagementTable.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/PositioneventtypeTable.php';
}

if (!class_exists('sportsmanagementTablePositioneventtype', false)) {
    class_alias(PositioneventtypeTable::class, 'sportsmanagementTablePositioneventtype');
}
