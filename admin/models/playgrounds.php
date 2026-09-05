<?php
/**
 * Legacy compatibility bridge for the native administrator Playgrounds list model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\PlaygroundsModel;

if (!class_exists(PlaygroundsModel::class)) {
    $baseModel = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    $nativeModel = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/PlaygroundsModel.php';

    if (is_file($baseModel)) {
        require_once $baseModel;
    }

    if (is_file($nativeModel)) {
        require_once $nativeModel;
    }
}

if (!class_exists(PlaygroundsModel::class)) {
    throw new \RuntimeException('SportsManagement native Playgrounds model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelPlaygrounds', false)) {
    class_alias(PlaygroundsModel::class, 'sportsmanagementModelPlaygrounds');
}
