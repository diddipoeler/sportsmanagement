<?php
/**
 * SportsManagement legacy compatibility bridge.
 *
 * The active Joomla 5/6 implementation lives in admin/src/Model/PositionsModel.php.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\PositionsModel;

if (!class_exists(PositionsModel::class)) {
    $nativeModels = [
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php',
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/PositionsModel.php',
    ];

    foreach ($nativeModels as $nativeModel) {
        if (is_file($nativeModel)) {
            require_once $nativeModel;
        }
    }
}

if (!class_exists(PositionsModel::class)) {
    throw new \RuntimeException('SportsManagement native Positions model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelPositions', false)) {
    class_alias(PositionsModel::class, 'sportsmanagementModelPositions');
}
