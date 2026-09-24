<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Positioneventtype model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\PositioneventtypeModel;

if (!class_exists(PositioneventtypeModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/PositioneventtypeModel.php';
}

if (!class_exists(PositioneventtypeModel::class)) {
    throw new \RuntimeException('SportsManagement native Positioneventtype model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelpositioneventtype', false)) {
    class_alias(PositioneventtypeModel::class, 'sportsmanagementModelpositioneventtype');
}
