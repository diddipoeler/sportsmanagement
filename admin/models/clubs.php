<?php
/**
 * Legacy compatibility bridge for the native administrator Clubs model.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\ClubsModel;

if (!class_exists(ClubsModel::class)) {
    $baseModel = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    $nativeModel = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/ClubsModel.php';

    if (is_file($baseModel)) {
        require_once $baseModel;
    }

    if (is_file($nativeModel)) {
        require_once $nativeModel;
    }
}

if (!class_exists(ClubsModel::class)) {
    throw new \RuntimeException('SportsManagement native Clubs model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelClubs', false)) {
    class_alias(ClubsModel::class, 'sportsmanagementModelClubs');
}
