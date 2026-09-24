<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Jlextdfbkeyimport model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\JlextdfbkeyimportModel;

if (!class_exists(JlextdfbkeyimportModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/JlextdfbkeyimportModel.php';
}

if (!class_exists(JlextdfbkeyimportModel::class)) {
    throw new \RuntimeException('SportsManagement native Jlextdfbkeyimport model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModeljlextDfbkeyimport', false)) {
    class_alias(JlextdfbkeyimportModel::class, 'sportsmanagementModeljlextDfbkeyimport');
}
