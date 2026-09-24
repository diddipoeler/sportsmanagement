<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Cpanel model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\CpanelModel;

if (!class_exists(CpanelModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/CpanelModel.php';
}

if (!class_exists(CpanelModel::class)) {
    throw new \RuntimeException('SportsManagement native Cpanel model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelcpanel', false)) {
    class_alias(CpanelModel::class, 'sportsmanagementModelcpanel');
}
