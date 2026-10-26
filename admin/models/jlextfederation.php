<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Jlextfederation model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\JlextfederationModel;

if (!class_exists(JlextfederationModel::class)) {
    foreach ([
        __DIR__ . '/../src/Model/SportsManagementAdminModel.php',
        __DIR__ . '/../src/Model/JlextfederationModel.php',
    ] as $dependency) {
        if (!is_file($dependency)) {
            throw new \RuntimeException('SportsManagement administrator model dependency is missing: ' . basename($dependency), 500);
        }

        require_once $dependency;
    }
}

if (!class_exists(JlextfederationModel::class)) {
    throw new \RuntimeException('SportsManagement native Jlextfederation model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModeljlextfederation', false)) {
    class_alias(JlextfederationModel::class, 'sportsmanagementModeljlextfederation');
}
