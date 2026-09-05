<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 frontend Referee model.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\RefereeModel;

if (!class_exists(RefereeModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/PersonModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/RefereeModel.php';
}

if (!class_exists(RefereeModel::class)) {
    throw new \RuntimeException('SportsManagement native Referee model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelReferee', false)) {
    class_alias(RefereeModel::class, 'sportsmanagementModelReferee');
}
