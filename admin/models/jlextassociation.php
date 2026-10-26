<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Jlextassociation model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\JlextassociationModel;

if (!class_exists(JlextassociationModel::class)) {
    foreach ([
        __DIR__ . '/../src/Model/SportsManagementAdminModel.php',
        __DIR__ . '/../src/Model/JlextassociationModel.php',
    ] as $dependency) {
        if (!is_file($dependency)) {
            throw new \RuntimeException('SportsManagement administrator model dependency is missing: ' . basename($dependency), 500);
        }

        require_once $dependency;
    }
}

if (!class_exists(JlextassociationModel::class)) {
    throw new \RuntimeException('SportsManagement native Jlextassociation model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModeljlextassociation', false)) {
    class_alias(JlextassociationModel::class, 'sportsmanagementModeljlextassociation');
}
