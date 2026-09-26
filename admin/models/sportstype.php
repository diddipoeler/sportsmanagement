<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Sportstype model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\SportstypeModel;

if (!class_exists(SportstypeModel::class)) {
    foreach ([
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php',
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportstypeModel.php',
    ] as $nativeModel) {
        if (is_file($nativeModel)) {
            require_once $nativeModel;
        }
    }
}

if (!class_exists(SportstypeModel::class)) {
    throw new \RuntimeException('SportsManagement native Sportstype model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelsportstype', false)) {
    class_alias(SportstypeModel::class, 'sportsmanagementModelsportstype');
}
