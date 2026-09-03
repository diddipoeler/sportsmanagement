<?php
/**
 * SportsManagement legacy compatibility bridge for the native administrator Eventtypes model.
 *
 * @version    4.24.00
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\EventtypesModel;

if (!class_exists(EventtypesModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/EventtypesModel.php';
}

if (!class_exists('sportsmanagementModelEventtypes', false)) {
    class_alias(EventtypesModel::class, 'sportsmanagementModelEventtypes');
}
