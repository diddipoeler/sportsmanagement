<?php
/**
 * SportsManagement legacy compatibility bridge for the native Allprojectrounds model.
 *
 * @version    4.24.00
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\AllprojectroundsModel;

if (!class_exists(AllprojectroundsModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/AllprojectroundsModel.php';
}

if (!class_exists('sportsmanagementModelallprojectrounds', false)) {
    class_alias(AllprojectroundsModel::class, 'sportsmanagementModelallprojectrounds');
}
