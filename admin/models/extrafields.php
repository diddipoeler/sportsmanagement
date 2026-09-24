<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Extrafields model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\ExtrafieldsModel;

if (!class_exists(ExtrafieldsModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/ExtrafieldsModel.php';
}

if (!class_exists(ExtrafieldsModel::class)) {
    throw new \RuntimeException('SportsManagement native Extrafields model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelextrafields', false)) {
    class_alias(ExtrafieldsModel::class, 'sportsmanagementModelextrafields');
}
