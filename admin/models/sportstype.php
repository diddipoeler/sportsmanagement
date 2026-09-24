<?php
/**
 * SportsManagement legacy compatibility bridge for the native Joomla 5/6 sportstype model.
 *
 * The active Joomla 5/6 implementation lives in admin/src/Model/SportstypeModel.php.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\SportstypeModel;

if (!class_exists(SportstypeModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportstypeModel.php';
}

if (!class_exists('sportsmanagementModelsportstype', false)) {
    class_alias(SportstypeModel::class, 'sportsmanagementModelsportstype');
}
