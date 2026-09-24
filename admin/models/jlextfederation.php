<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Jlextfederation model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\JlextfederationModel;

if (!class_exists(JlextfederationModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/JlextfederationModel.php';
}

if (!class_exists(JlextfederationModel::class)) {
    throw new \RuntimeException('SportsManagement native Jlextfederation model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModeljlextfederation', false)) {
    class_alias(JlextfederationModel::class, 'sportsmanagementModeljlextfederation');
}
