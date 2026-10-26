<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Jlxmlimport model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\JlxmlimportModel;

if (!class_exists(JlxmlimportModel::class)) {
    foreach ([
        __DIR__ . '/../src/Service/SportsManagementAdministratorApplicationResolver.php',
        __DIR__ . '/../src/Model/JlxmlimportModel.php',
    ] as $nativeFile) {
        if (!is_file($nativeFile)) {
            throw new \RuntimeException('SportsManagement administrator model dependency is missing: ' . basename($nativeFile), 500);
        }

        require_once $nativeFile;
    }
}

if (!class_exists(JlxmlimportModel::class)) {
    throw new \RuntimeException('SportsManagement native Jlxmlimport model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelJLXMLImport', false)) {
    class_alias(JlxmlimportModel::class, 'sportsmanagementModelJLXMLImport');
}
