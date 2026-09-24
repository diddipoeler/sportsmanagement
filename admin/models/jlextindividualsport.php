<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Jlextindividualsport model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\JlextindividualsportModel;

if (!class_exists(JlextindividualsportModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/JlextindividualsportModel.php';
}

if (!class_exists(JlextindividualsportModel::class)) {
    throw new \RuntimeException('SportsManagement native Jlextindividualsport model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModeljlextindividualsport', false)) {
    class_alias(JlextindividualsportModel::class, 'sportsmanagementModeljlextindividualsport');
}
