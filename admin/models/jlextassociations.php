<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Jlextassociations model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\JlextassociationsModel;

if (!class_exists(JlextassociationsModel::class)) {
    foreach ([
        __DIR__ . '/../src/Model/SportsManagementListModel.php',
        __DIR__ . '/../src/Model/JlextassociationsModel.php',
    ] as $dependency) {
        if (!is_file($dependency)) {
            throw new \RuntimeException('SportsManagement administrator model dependency is missing: ' . basename($dependency), 500);
        }

        require_once $dependency;
    }
}

if (!class_exists(JlextassociationsModel::class)) {
    throw new \RuntimeException('SportsManagement native Jlextassociations model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModeljlextassociations', false)) {
    class_alias(JlextassociationsModel::class, 'sportsmanagementModeljlextassociations');
}
