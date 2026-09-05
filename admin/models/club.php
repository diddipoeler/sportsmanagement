<?php
/**
 * SportsManagement legacy compatibility bridge.
 *
 * The active Joomla 5/6 implementation lives in admin/src/Model/ClubModel.php.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\ClubModel;

if (!class_exists(ClubModel::class)) {
    $baseModel = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    $nativeModel = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/ClubModel.php';

    if (is_file($baseModel)) {
        require_once $baseModel;
    }

    if (is_file($nativeModel)) {
        require_once $nativeModel;
    }
}

if (!class_exists(ClubModel::class)) {
    throw new \RuntimeException('SportsManagement native Club model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelclub', false)) {
    class_alias(ClubModel::class, 'sportsmanagementModelclub');
}
