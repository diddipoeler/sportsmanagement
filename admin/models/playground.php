<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Playground model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\PlaygroundModel;

if (!class_exists(PlaygroundModel::class)) {
    foreach ([
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php',
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/PlaygroundModel.php',
    ] as $nativeFile) {
        if (is_file($nativeFile)) {
            require_once $nativeFile;
        }
    }
}

if (!class_exists(PlaygroundModel::class)) {
    throw new \RuntimeException('SportsManagement native Playground model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelPlayground', false)) {
    class_alias(PlaygroundModel::class, 'sportsmanagementModelPlayground');
}
