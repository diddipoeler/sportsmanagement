<?php
/**
 * Legacy compatibility bridge for the native administrator Smimageimport model.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\SmimageimportModel;

if (!class_exists(SmimageimportModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SmimageimportModel.php';
}

if (!class_exists('sportsmanagementModelsmimageimport', false)) {
    class_alias(SmimageimportModel::class, 'sportsmanagementModelsmimageimport');
}
