<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 frontend Referees model.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\RefereesModel;

if (!class_exists(RefereesModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/RefereesModel.php';
}

if (!class_exists(RefereesModel::class)) {
    throw new \RuntimeException('SportsManagement native Referees model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelReferees', false)) {
    class_alias(RefereesModel::class, 'sportsmanagementModelReferees');
}
