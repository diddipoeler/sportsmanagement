<?php
/**
 * Legacy compatibility bridge for the native Allprojects model.
 *
 * @version    4.24.00
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\AllprojectsModel;

if (!class_exists(AllprojectsModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/AllprojectsModel.php';
}

if (!class_exists('sportsmanagementModelallprojects', false)) {
    class_alias(AllprojectsModel::class, 'sportsmanagementModelallprojects');
}
