<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Google calendar import model.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\JsmgcalendarimportModel;

if (!class_exists(JsmgcalendarimportModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/JsmgcalendarimportModel.php';
}

if (!class_exists(JsmgcalendarimportModel::class)) {
    throw new \RuntimeException('SportsManagement native Jsmgcalendarimport model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModeljsmgcalendarImport', false)) {
    class_alias(JsmgcalendarimportModel::class, 'sportsmanagementModeljsmgcalendarImport');
}
