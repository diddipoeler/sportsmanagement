<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Jlexthandballnet model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\JlexthandballnetModel;

if (!class_exists(JlexthandballnetModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/JlexthandballnetModel.php';
}

if (!class_exists(JlexthandballnetModel::class)) {
    throw new \RuntimeException('SportsManagement native Jlexthandballnet model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModeljlexthandballnet', false)) {
    class_alias(JlexthandballnetModel::class, 'sportsmanagementModeljlexthandballnet');
}
