<?php
/**
 * Legacy compatibility bridge for the native administrator Clubname model.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\ClubnameModel;

if (!class_exists(ClubnameModel::class)) {
    $adminModel = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    $clubnameModel = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/ClubnameModel.php';

    if (is_file($adminModel)) {
        require_once $adminModel;
    }

    if (is_file($clubnameModel)) {
        require_once $clubnameModel;
    }
}

if (!class_exists(ClubnameModel::class)) {
    throw new \RuntimeException('SportsManagement native administrator Clubname model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelclubname', false)) {
    class_alias(ClubnameModel::class, 'sportsmanagementModelclubname');
}
