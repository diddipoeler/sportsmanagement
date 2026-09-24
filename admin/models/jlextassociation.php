<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Jlextassociation model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\JlextassociationModel;

if (!class_exists(JlextassociationModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/JlextassociationModel.php';
}

if (!class_exists(JlextassociationModel::class)) {
    throw new \RuntimeException('SportsManagement native Jlextassociation model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModeljlextassociation', false)) {
    class_alias(JlextassociationModel::class, 'sportsmanagementModeljlextassociation');
}
